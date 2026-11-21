<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_raw_material', true)->latest();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }
        $materials = $query->paginate(15)->withQueryString();
        return view('raw_materials.index', compact('materials'));
    }

    public function create()
    {
        return view('raw_materials.form', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sku'] = ($data['sku'] ?? null) ?: 'BB-' . strtoupper(Str::random(6));
        $data['barcode'] = null;
        $data['selling_price'] = 0;
        $data['is_raw_material'] = true;
        $data['is_active'] = $request->boolean('is_active');
        Product::create($data);
        return redirect()->route('raw-materials.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(Product $rawMaterial)
    {
        abort_unless($rawMaterial->is_raw_material, 404);
        return view('raw_materials.form', ['material' => $rawMaterial, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Product $rawMaterial)
    {
        abort_unless($rawMaterial->is_raw_material, 404);
        $data = $this->validated($request, $rawMaterial);
        $data['is_active'] = $request->boolean('is_active');
        $rawMaterial->update($data);
        return redirect()->route('raw-materials.index')->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(Product $rawMaterial)
    {
        abort_unless($rawMaterial->is_raw_material, 404);
        if ($rawMaterial->transactionDetails()->exists()) {
            $rawMaterial->update(['is_active' => false]);
            return redirect()->route('raw-materials.index')->with('success', 'Bahan baku dinonaktifkan karena memiliki riwayat.');
        }
        $rawMaterial->delete();
        return redirect()->route('raw-materials.index')->with('success', 'Bahan baku berhasil dihapus.');
    }

    private function validated(Request $request, ?Product $material = null): array
    {
        $skuRule = 'nullable|string|max:100|unique:app_products,sku' . ($material ? ',' . $material->id : '');
        $request->validate([
            'app_category_id' => 'required|exists:app_categories,id',
            'sku' => $skuRule,
            'name' => 'required|string|max:255',
            'unit' => 'required|in:gram,ml,pcs,kg,liter,pack',
            'purchase_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        return $request->only(['app_category_id', 'sku', 'name', 'unit', 'purchase_price', 'stock', 'min_stock']);
    }
}
