<?php

namespace App\Services;

use App\Exceptions\OrderException;
use App\Models\CafeTable;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Creates, extends, pays, splits and cancels orders. Used both for
 * "pay now" checkout and for open bills (pay later), so pricing, stock and
 * numbering rules live in exactly one place.
 */
class OrderService
{
    private const MAX_INVOICE_ATTEMPTS = 5;

    /**
     * Create a new order. With $payment it is settled immediately; without,
     * it stays an open bill.
     *
     * @param  array  $items  validated cart lines
     * @param  array  $orderInfo  order_type, online_platform, app_table_id, table_name, customer_name
     * @param  array|null  $payment  method, discount, paid_amount
     */
    public function create(array $items, array $orderInfo, ?array $payment, int $userId, ?string $notes = null): Transaction
    {
        return $this->withInvoiceRetry(function () use ($items, $orderInfo, $payment, $userId, $notes) {
            return DB::transaction(function () use ($items, $orderInfo, $payment, $userId, $notes) {
                if ($payment === null && $orderInfo['app_table_id']) {
                    // Serialise on the table row so two cashiers cannot open
                    // two bills on the same table at the same moment.
                    CafeTable::whereKey($orderInfo['app_table_id'])->lockForUpdate()->first();
                    $existing = Transaction::open()->where('app_table_id', $orderInfo['app_table_id'])->first();
                    if ($existing) {
                        throw new OrderException("{$orderInfo['table_name']} sudah punya bill terbuka ({$existing->invoice_number}). Tambahkan pesanan ke bill tersebut.");
                    }
                }

                [$lines, $subtotal] = $this->buildLines($items);
                [$invoice, $queue] = $this->nextNumbers();

                $trx = Transaction::create([
                    'invoice_number' => $invoice,
                    'status' => Transaction::STATUS_OPEN,
                    'app_user_id' => $userId,
                    'opened_by' => $userId,
                    ...$orderInfo,
                    'queue_number' => $queue,
                    'total_amount' => $subtotal,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'final_amount' => $subtotal,
                    'paid_amount' => 0,
                    'change_amount' => 0,
                    'notes' => $notes,
                ]);
                $this->persistLines($trx, $lines);

                if ($payment !== null) {
                    $this->settle($trx, $payment, $userId);
                }

                return $trx;
            });
        });
    }

    /** Append more items to an open bill. */
    public function addItems(Transaction $bill, array $items): Transaction
    {
        return DB::transaction(function () use ($bill, $items) {
            $bill = $this->lockOpenBill($bill);
            [$lines] = $this->buildLines($items);
            $this->persistLines($bill, $lines);
            $this->recalculate($bill);

            return $bill;
        });
    }

    /**
     * Pay an open bill. When $splitLines is given and does not cover the
     * whole bill, only those quantities are moved to a new paid transaction
     * and the rest of the bill stays open.
     *
     * @param  array<int, array{detail_id: int, quantity: int}>|null  $splitLines
     * @return Transaction the paid transaction
     */
    public function pay(Transaction $bill, array $payment, int $userId, ?array $splitLines = null): Transaction
    {
        return $this->withInvoiceRetry(function () use ($bill, $payment, $userId, $splitLines) {
            return DB::transaction(function () use ($bill, $payment, $userId, $splitLines) {
                $bill = $this->lockOpenBill($bill);

                $target = $splitLines !== null && ! $this->coversWholeBill($bill, $splitLines)
                    ? $this->splitOff($bill, $splitLines, $userId)
                    : $bill;

                $this->settle($target, $payment, $userId);

                return $target;
            });
        });
    }

    /** Cancel an open bill and return its stock. */
    public function cancel(Transaction $bill, int $userId, string $reason): Transaction
    {
        return DB::transaction(function () use ($bill, $userId, $reason) {
            $bill = $this->lockOpenBill($bill);

            foreach ($bill->details as $detail) {
                Product::whereKey($detail->app_product_id)->increment('stock', $detail->quantity);
            }

            $bill->update([
                'status' => Transaction::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancel_reason' => $reason,
            ]);

            return $bill;
        });
    }

    // ------------------------------------------------------------------

    /**
     * Lock products, validate stock and modifiers, deduct stock.
     * Must run inside a DB transaction.
     *
     * @return array{0: array<int, array>, 1: float}
     */
    private function buildLines(array $items): array
    {
        $lines = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $product = Product::whereKey($item['id'])->lockForUpdate()->first();
            $qty = (int) $item['quantity'];

            if (! $product || ! $product->is_active) {
                $label = $item['name'] ?? ('ID ' . $item['id']);
                throw new OrderException("Produk '{$label}' tidak ditemukan atau non-aktif.");
            }
            if ($product->stock < $qty) {
                throw new OrderException("Stok produk '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Diminta: {$qty}).");
            }

            // Price modifiers from master data, never from the client.
            [$snapshot, $modifierTotal] = $this->resolveModifiers($product, $item['modifier_option_ids'] ?? []);
            $unitPrice = (float) $product->selling_price + $modifierTotal;
            $lineSubtotal = $unitPrice * $qty;
            $subtotal += $lineSubtotal;

            $product->decrement('stock', $qty);

            $notes = isset($item['notes']) ? trim((string) $item['notes']) : '';
            $lines[] = [
                'app_product_id' => $product->id,
                'quantity' => $qty,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $unitPrice,
                'modifiers' => $snapshot ?: null,
                'modifier_total' => $modifierTotal,
                'notes' => $notes !== '' ? $notes : null,
                'subtotal' => $lineSubtotal,
            ];
        }

        return [$lines, $subtotal];
    }

    private function persistLines(Transaction $trx, array $lines): void
    {
        foreach ($lines as $line) {
            $trx->details()->create($line);
        }
    }

    private function recalculate(Transaction $trx): void
    {
        $total = (float) $trx->details()->sum('subtotal');
        $trx->update([
            'total_amount' => $total,
            'final_amount' => max(0, $total - (float) $trx->discount_amount),
        ]);
    }

    /** Apply discount + payment and mark the transaction as paid. */
    private function settle(Transaction $trx, array $payment, int $userId): void
    {
        $this->recalculate($trx);
        $total = (float) $trx->total_amount;
        $discount = min((float) ($payment['discount'] ?? 0), $total);
        $final = max(0, $total - $discount);

        if ($payment['method'] === 'qris') {
            $paid = $final;
            $change = 0;
        } else {
            $paid = (float) $payment['paid_amount'];
            if ($paid < $final) {
                throw new OrderException('Nominal pembayaran Tunai (Rp' . number_format($paid, 0, ',', '.') . ') kurang dari total belanja (Rp' . number_format($final, 0, ',', '.') . ').');
            }
            $change = $paid - $final;
        }

        // Money goes into the drawer of the cashier who takes the payment.
        $shift = Shift::where('app_user_id', $userId)->where('status', 'open')->first();

        $trx->update([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now(),
            'app_user_id' => $userId,
            'app_shift_id' => $shift?->id,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'payment_method' => $payment['method'],
            'paid_amount' => $paid,
            'change_amount' => $change,
        ]);
    }

    private function lockOpenBill(Transaction $bill): Transaction
    {
        $locked = Transaction::whereKey($bill->id)->lockForUpdate()->first();
        if (! $locked || ! $locked->isOpen()) {
            throw new OrderException('Bill ini sudah ditutup (dibayar atau dibatalkan). Muat ulang daftar bill.');
        }

        return $locked->load('details');
    }

    private function coversWholeBill(Transaction $bill, array $splitLines): bool
    {
        $requested = collect($splitLines)->groupBy('detail_id')->map(fn ($g) => $g->sum('quantity'));

        return $bill->details->every(fn ($d) => (int) ($requested[$d->id] ?? 0) === $d->quantity);
    }

    /**
     * Move the requested quantities to a new transaction (split bill).
     */
    private function splitOff(Transaction $bill, array $splitLines, int $userId): Transaction
    {
        $details = $bill->details->keyBy('id');
        $requested = collect($splitLines)->groupBy('detail_id')->map(fn ($g) => (int) $g->sum('quantity'))->filter();

        if ($requested->isEmpty()) {
            throw new OrderException('Pilih minimal satu item untuk dibayar.');
        }
        foreach ($requested as $detailId => $qty) {
            $detail = $details->get($detailId);
            if (! $detail || $qty < 1 || $qty > $detail->quantity) {
                throw new OrderException('Jumlah item yang dibayar tidak valid. Muat ulang bill.');
            }
        }

        [$invoice] = $this->nextNumbers();
        $part = Transaction::create([
            'invoice_number' => $invoice,
            'status' => Transaction::STATUS_OPEN,
            'app_user_id' => $userId,
            'opened_by' => $bill->opened_by,
            'split_from_id' => $bill->id,
            'order_type' => $bill->order_type,
            'online_platform' => $bill->online_platform,
            'app_table_id' => $bill->app_table_id,
            'table_name' => $bill->table_name,
            'customer_name' => $bill->customer_name,
            'queue_number' => $bill->queue_number,
            'total_amount' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'final_amount' => 0,
            'paid_amount' => 0,
            'change_amount' => 0,
        ]);

        foreach ($requested as $detailId => $qty) {
            /** @var TransactionDetail $detail */
            $detail = $details->get($detailId);

            if ($qty === $detail->quantity) {
                $detail->update(['app_transaction_id' => $part->id]);
            } else {
                $detail->update([
                    'quantity' => $detail->quantity - $qty,
                    'subtotal' => (float) $detail->selling_price * ($detail->quantity - $qty),
                ]);
                $part->details()->create([
                    'app_product_id' => $detail->app_product_id,
                    'quantity' => $qty,
                    'purchase_price' => $detail->purchase_price,
                    'selling_price' => $detail->selling_price,
                    'modifiers' => $detail->modifiers,
                    'modifier_total' => $detail->modifier_total,
                    'notes' => $detail->notes,
                    'subtotal' => (float) $detail->selling_price * $qty,
                ]);
            }
        }

        $this->recalculate($bill);

        return $part;
    }

    /**
     * Next invoice number and daily queue number. Locks today's rows so
     * concurrent orders serialise on the sequence. Must run in a transaction.
     *
     * @return array{0: string, 1: int}
     */
    private function nextNumbers(): array
    {
        $today = now()->today();
        $latest = Transaction::whereDate('created_at', $today)->lockForUpdate()->latest('id')->first();
        $seq = $latest ? ((int) substr($latest->invoice_number, -4)) + 1 : 1;
        $invoice = 'INV/' . now()->format('Ymd') . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

        // MAX, not the latest row: split parts reuse their parent's queue number.
        $queue = ((int) Transaction::whereDate('created_at', $today)->max('queue_number')) + 1;

        return [$invoice, $queue];
    }

    /**
     * Retry when a concurrent order grabbed the same invoice number.
     */
    private function withInvoiceRetry(callable $fn): Transaction
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $fn();
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), 'invoice_number') && $attempt < self::MAX_INVOICE_ATTEMPTS) {
                    continue;
                }
                throw $e;
            }
        }
    }

    /**
     * Validate chosen modifier options against the product's groups and
     * return [snapshot, total price].
     *
     * @return array{0: array<int, array{group: string, option: string, price: float}>, 1: float}
     */
    private function resolveModifiers(Product $product, array $optionIds): array
    {
        $optionIds = array_values(array_unique(array_map('intval', $optionIds)));
        $groups = $product->modifierGroups()->with('activeOptions')->get();

        // Every requested id must be an active option of a group attached
        // to this product (rejects stale or crafted ids).
        $validIds = $groups->flatMap(fn ($g) => $g->activeOptions->pluck('id'))->all();
        if (array_diff($optionIds, $validIds)) {
            throw new OrderException("Pilihan modifier untuk {$product->name} sudah tidak tersedia. Muat ulang halaman kasir.");
        }

        $snapshot = [];
        $total = 0.0;

        foreach ($groups as $group) {
            $chosen = $group->activeOptions->whereIn('id', $optionIds)->values();

            // Nothing picked for a required group: fall back to its default
            // option(s) — the same ones the POS modal pre-selects.
            if ($chosen->isEmpty() && $group->requiredMin() > 0) {
                $chosen = $group->activeOptions->where('is_default', true)->values();
            }

            $count = $chosen->count();
            if ($count < $group->requiredMin()) {
                throw new OrderException("Pilihan '{$group->name}' untuk {$product->name} wajib diisi.");
            }
            if ($group->max_select > 0 && $count > $group->max_select) {
                throw new OrderException("Pilihan '{$group->name}' untuk {$product->name} maksimal {$group->max_select}.");
            }

            foreach ($chosen as $option) {
                $total += (float) $option->price;
                $snapshot[] = ['group' => $group->name, 'option' => $option->name, 'price' => (float) $option->price];
            }
        }

        return [$snapshot, $total];
    }
}
