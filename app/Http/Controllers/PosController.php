<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function __construct()
    {
        // Shift enforcement moved to named route middleware `ensure.shift.open`.
    }
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function search(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku', '=', $keyword)
                  ->orWhere('barcode', '=', $keyword)
                  ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('app_category_id', $request->category_id);
        }

        $products = $query->take(30)->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * Lightweight endpoint returning current stock for all active products.
     * Polled by the POS screen so the remaining-stock figures stay live
     * (e.g. after this cashier's sale or a concurrent sale on another device).
     */
    public function stocks()
    {
        $stocks = Product::where('is_active', true)->get(['id', 'stock', 'min_stock']);

        return response()->json([
            'success' => true,
            'stocks' => $stocks,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:app_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $items = $request->items;
        $paymentMethod = $request->payment_method;
        $discountAmount = (float) ($request->discount_amount ?? 0);
        $paidAmountInput = (float) $request->paid_amount;

        // Active shift is guaranteed by the ensure.shift.open middleware,
        // but fetch defensively so the transaction is always tied to a shift.
        $activeShift = \App\Models\Shift::where('app_user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        // Retry the whole transaction if the generated invoice number collides
        // with a concurrent checkout (invoice_number unique constraint violation).
        $maxAttempts = 5;

        for ($attempt = 1; ; $attempt++) {
            try {
                $transaction = DB::transaction(function () use ($items, $paymentMethod, $discountAmount, $paidAmountInput, $request, $activeShift) {
                $subtotalSum = 0;
                $detailsToInsert = [];

                foreach ($items as $item) {
                    // Lock product for update to prevent race conditions
                    $product = Product::where('id', $item['id'])->lockForUpdate()->first();

                    if (!$product || !$product->is_active) {
                        throw new \Exception("Produk '{$item['name']}' tidak ditemukan atau non-aktif.");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok produk '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Diminta: {$item['quantity']}).");
                    }

                    $lineSubtotal = (float) $product->selling_price * (int) $item['quantity'];
                    $subtotalSum += $lineSubtotal;

                    // Deduct stock atomically
                    $product->decrement('stock', (int) $item['quantity']);

                    $detailsToInsert[] = [
                        'app_product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => (int) $item['quantity'],
                        'purchase_price' => $product->purchase_price,
                        'selling_price' => $product->selling_price,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $finalAmount = max(0, $subtotalSum - $discountAmount);

                if ($paymentMethod === 'qris') {
                    $paidAmount = $finalAmount;
                    $changeAmount = 0;
                } else {
                    $paidAmount = $paidAmountInput;
                    if ($paidAmount < $finalAmount) {
                        throw new \Exception("Nominal pembayaran Tunai (Rp" . number_format($paidAmount, 0, ',', '.') . ") kurang dari total belanja (Rp" . number_format($finalAmount, 0, ',', '.') . ").");
                    }
                    $changeAmount = $paidAmount - $finalAmount;
                }

                // Generate Invoice Number.
                // Lock today's transaction rows for update so concurrent checkouts
                // serialize on the sequence calculation and cannot produce the same
                // number. Format is preserved so existing receipts/tests stay valid.
                $todayStr = now()->format('Ymd');
                $latestTransaction = Transaction::whereDate('created_at', now()->today())
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();
                $seq = $latestTransaction ? ((int) substr($latestTransaction->invoice_number, -4)) + 1 : 1;
                $invoiceNumber = 'INV/' . $todayStr . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

                $trx = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'app_user_id' => auth()->id(),
                    'app_shift_id' => $activeShift?->id,
                    'total_amount' => $subtotalSum,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => 0,
                    'final_amount' => $finalAmount,
                    'payment_method' => $paymentMethod,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'notes' => $request->notes ?? null,
                ]);

                foreach ($detailsToInsert as $detail) {
                    TransactionDetail::create([
                        'app_transaction_id' => $trx->id,
                        'app_product_id' => $detail['app_product_id'],
                        'quantity' => $detail['quantity'],
                        'purchase_price' => $detail['purchase_price'],
                        'selling_price' => $detail['selling_price'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                }

                return $trx->load(['details.product', 'user']);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil diproses!',
                    'transaction' => $transaction,
                    'receipt_html' => view('pos.receipt_content', ['transaction' => $transaction, 'is_modal' => true])->render()
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                // Duplicate invoice number from a concurrent checkout: loop back
                // to regenerate the sequence and retry, up to $maxAttempts.
                $isDuplicateInvoice = str_contains($e->getMessage(), 'invoice_number');
                if ($isDuplicateInvoice && $attempt < $maxAttempts) {
                    continue;
                }

                // Log the real SQL error so genuine failures (missing columns,
                // constraint issues, etc.) are diagnosable instead of hidden
                // behind the generic user-facing message.
                \Illuminate\Support\Facades\Log::error('POS checkout failed', [
                    'user_id' => auth()->id(),
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi gagal disimpan karena gangguan sistem. Silakan coba lagi.',
                ], 422);
            } catch (\Exception $e) {
                // Business rule violation (stock/payment) — surface the message.
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
        }
    }

    public function showReceipt(Transaction $transaction)
    {
        // Prevent IDOR: only the cashier who owns the transaction, or a user
        // allowed to view financial reports (owner/admin), may view the receipt.
        if ($transaction->app_user_id !== auth()->id()
            && !auth()->user()->can('view-financial-reports')) {
            abort(403);
        }

        $transaction->load(['details.product', 'user']);
        return view('pos.receipt_content', compact('transaction'));
    }
}
