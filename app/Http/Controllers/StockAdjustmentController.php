<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['product.category', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $adjustments = $query->latest()->paginate(15);

        return view('stock_adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stock_adjustments.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'app_product_id' => 'required|exists:app_products,id',
            'type' => 'required|in:opname,damaged,expired,lost',
            'quantity_actual' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->app_product_id);

            $quantityBefore = $product->stock;
            $quantityActual = (int) $request->quantity_actual;
            $quantityAdjusted = $quantityActual - $quantityBefore;

            StockAdjustment::create([
                'app_product_id' => $product->id,
                'app_user_id' => auth()->id(),
                'type' => $request->type,
                'quantity_before' => $quantityBefore,
                'quantity_adjusted' => $quantityAdjusted,
                'quantity_after' => $quantityActual,
                'notes' => $request->notes,
            ]);

            // Update product stock to actual counted quantity
            $product->update(['stock' => $quantityActual]);
        });

        return redirect()->route('adjustments.index')->with('success', 'Penyesuaian stok / stock opname berhasil disimpan!');
    }
}
