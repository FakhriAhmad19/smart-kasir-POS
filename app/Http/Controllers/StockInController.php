<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $query = StockIn::with(['product.category', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            })->orWhere('supplier_name', 'like', "%{$search}%");
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $stockIns = $query->latest()->paginate(15);

        return view('stock_ins.index', compact('stockIns'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stock_ins.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'app_product_id' => 'required|exists:app_products,id',
            'supplier_name' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'update_hpp' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->app_product_id);
            $qty = (int) $request->quantity;
            $price = (float) $request->purchase_price;
            $total = $qty * $price;

            StockIn::create([
                'app_product_id' => $product->id,
                'app_user_id' => auth()->id(),
                'supplier_name' => $request->supplier_name,
                'quantity' => $qty,
                'purchase_price' => $price,
                'total_price' => $total,
                'notes' => $request->notes,
            ]);

            // Increment product stock
            $product->increment('stock', $qty);

            // Optionally update product purchase price HPP
            if ($request->boolean('update_hpp')) {
                $product->update(['purchase_price' => $price]);
            }
        });

        return redirect()->route('stock-ins.index')->with('success', 'Barang masuk berhasil dicatat & stok produk bertambah!');
    }
}
