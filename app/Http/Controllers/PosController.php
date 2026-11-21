<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderException;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function __construct()
    {
        // Shift enforcement moved to named route middleware `ensure.shift.open`.
    }
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $products = Product::with(['category', 'modifierGroups.activeOptions'])
            ->where('is_active', true)
            ->where('is_raw_material', false)
            ->orderBy('name')
            ->get();

        // Modifier definitions per product for the selection modal, keyed
        // by product id. Only products that actually have groups are sent.
        $productModifiers = $products
            ->filter(fn ($p) => $p->modifierGroups->isNotEmpty())
            ->mapWithKeys(fn ($p) => [$p->id => $p->modifierGroups
                ->filter(fn ($g) => $g->activeOptions->isNotEmpty())
                ->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'min' => $g->requiredMin(),
                    'max' => $g->max_select,
                    'label' => $g->selectionLabel(),
                    'options' => $g->activeOptions->map(fn ($o) => [
                        'id' => $o->id,
                        'name' => $o->name,
                        'price' => (float) $o->price,
                        'is_default' => $o->is_default,
                    ])->values(),
                ])->values()]);

        $tables = CafeTable::where('is_active', true)->ordered()->get(['id', 'name', 'area', 'capacity']);

        return view('pos.index', compact('categories', 'products', 'productModifiers', 'tables'));
    }

    public function search(Request $request)
    {
        $query = Product::with('category')->where('is_active', true)->where('is_raw_material', false);

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
        $stocks = Product::where('is_active', true)->where('is_raw_material', false)->get(['id', 'stock', 'min_stock']);

        return response()->json([
            'success' => true,
            'stocks' => $stocks,
        ]);
    }

    /** Pay-now checkout (bayar di depan). */
    public function checkout(Request $request, OrderService $orders)
    {
        $request->validate(
            $this->itemRules() + $this->orderInfoRules() + $this->paymentRules(),
            $this->messages()
        );

        return $this->respond(fn () => $orders->create(
            $request->items,
            $this->orderInfo($request),
            $this->payment($request),
            auth()->id(),
            $request->notes ?? null,
        ), 'Transaksi berhasil diproses!', withReceipt: true);
    }

    /** List open bills (also used to show which tables are occupied). */
    public function openBills()
    {
        $bills = Transaction::open()
            ->withCount('details')
            ->orderBy('created_at')
            ->get(['id', 'invoice_number', 'order_type', 'online_platform', 'app_table_id', 'table_name', 'customer_name', 'queue_number', 'total_amount', 'created_at']);

        return response()->json(['success' => true, 'bills' => $bills]);
    }

    public function showBill(Transaction $bill)
    {
        abort_unless($bill->isOpen(), 404);

        return response()->json([
            'success' => true,
            'bill' => $bill->load(['details.product:id,name,image', 'opener:id,name']),
        ]);
    }

    /** Save the cart as a new open bill (bayar nanti). */
    public function storeBill(Request $request, OrderService $orders)
    {
        $request->validate($this->itemRules() + $this->orderInfoRules(), $this->messages());

        $info = $this->orderInfo($request);
        if ($info['order_type'] !== 'dine_in' && ! $info['customer_name']) {
            throw ValidationException::withMessages([
                'customer_name' => 'Isi nama pelanggan agar bill take away / online mudah dikenali.',
            ]);
        }

        return $this->respond(
            fn () => $orders->create($request->items, $info, null, auth()->id(), $request->notes ?? null),
            'Bill berhasil dibuka.'
        );
    }

    public function addBillItems(Request $request, Transaction $bill, OrderService $orders)
    {
        $request->validate($this->itemRules(), $this->messages());

        return $this->respond(fn () => $orders->addItems($bill, $request->items), 'Pesanan ditambahkan ke bill.');
    }

    public function payBill(Request $request, Transaction $bill, OrderService $orders)
    {
        $request->validate($this->paymentRules() + [
            'lines' => 'nullable|array|min:1',
            'lines.*.detail_id' => 'required|integer',
            'lines.*.quantity' => 'required|integer|min:0',
        ], $this->messages());

        $lines = $request->has('lines')
            ? collect($request->lines)->map(fn ($l) => ['detail_id' => (int) $l['detail_id'], 'quantity' => (int) $l['quantity']])->all()
            : null;

        return $this->respond(
            fn () => $orders->pay($bill, $this->payment($request), auth()->id(), $lines),
            'Pembayaran berhasil!',
            withReceipt: true
        );
    }

    public function cancelBill(Request $request, Transaction $bill, OrderService $orders)
    {
        $request->validate(['reason' => 'required|string|min:3|max:255'], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        return $this->respond(
            fn () => $orders->cancel($bill, auth()->id(), trim($request->reason)),
            'Bill dibatalkan dan stok dikembalikan.'
        );
    }

    // ------------------------------------------------------------------

    private function respond(callable $action, string $message, bool $withReceipt = false)
    {
        try {
            $trx = $action();
        } catch (OrderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Log the real SQL error so genuine failures are diagnosable
            // instead of hidden behind the generic user-facing message.
            \Illuminate\Support\Facades\Log::error('POS order failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal disimpan karena gangguan sistem. Silakan coba lagi.',
            ], 422);
        }

        $trx->refresh()->load(['details.product', 'user']);
        $payload = ['success' => true, 'message' => $message, 'transaction' => $trx];
        if ($withReceipt) {
            $payload['receipt_html'] = view('pos.receipt_content', ['transaction' => $trx, 'is_modal' => true])->render();
        }

        return response()->json($payload);
    }

    private function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:app_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.modifier_option_ids' => 'nullable|array|max:50',
            'items.*.modifier_option_ids.*' => 'integer',
            'items.*.notes' => 'nullable|string|max:255',
        ];
    }

    private function orderInfoRules(): array
    {
        return [
            // Older clients that send no order info are treated as take-away.
            'order_type' => 'nullable|in:' . implode(',', array_keys(Transaction::ORDER_TYPES)),
            'online_platform' => 'nullable|required_if:order_type,online|in:' . implode(',', Transaction::ONLINE_PLATFORMS),
            'table_id' => [
                'nullable',
                'required_if:order_type,dine_in',
                'integer',
                Rule::exists('app_tables', 'id')->where('is_active', true),
            ],
            'customer_name' => 'nullable|string|max:100',
        ];
    }

    private function paymentRules(): array
    {
        return [
            'payment_method' => 'required|in:cash,qris',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ];
    }

    private function messages(): array
    {
        return [
            'table_id.required_if' => 'Pesanan dine-in wajib memilih meja.',
            'table_id.exists' => 'Meja yang dipilih tidak tersedia.',
            'online_platform.required_if' => 'Pilih platform pesanan online.',
        ];
    }

    private function orderInfo(Request $request): array
    {
        $orderType = $request->order_type ?: 'take_away';
        $table = $orderType === 'dine_in' ? CafeTable::find($request->table_id) : null;

        return [
            'order_type' => $orderType,
            'online_platform' => $orderType === 'online' ? $request->online_platform : null,
            'app_table_id' => $table?->id,
            'table_name' => $table?->name,
            'customer_name' => $request->filled('customer_name') ? trim($request->customer_name) : null,
        ];
    }

    private function payment(Request $request): array
    {
        return [
            'method' => $request->payment_method,
            'discount' => (float) ($request->discount_amount ?? 0),
            'paid_amount' => (float) $request->paid_amount,
        ];
    }

    public function showReceipt(Transaction $transaction)
    {
        // Prevent IDOR: only the cashier who owns the transaction, or a user
        // allowed to view financial reports (owner/admin), may view the receipt.
        // Open bills can be printed by any cashier (shared tables).
        if (! $transaction->isOpen()
            && $transaction->app_user_id !== auth()->id()
            && $transaction->opened_by !== auth()->id()
            && ! auth()->user()->can('view-financial-reports')) {
            abort(403);
        }

        $transaction->load(['details.product', 'user']);
        return view('pos.receipt_content', compact('transaction'));
    }
}
