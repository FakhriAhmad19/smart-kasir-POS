<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_raw_material', false)->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('app_category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $modifierGroups = ModifierGroup::with('options')->orderBy('sort_order')->orderBy('name')->get();
        return view('products.create', compact('categories', 'modifierGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'app_category_id' => 'required|exists:app_categories,id',
            'sku' => 'nullable|string|max:100|unique:app_products,sku',
            'barcode' => 'nullable|string|max:100|unique:app_products,barcode',
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'station' => 'nullable|in:barista,kitchen',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'modifier_group_ids' => 'nullable|array',
            'modifier_group_ids.*' => 'integer|exists:app_modifier_groups,id',
        ]);

        $sku = $request->sku ?: 'SKU-' . strtoupper(Str::random(6));
        $barcode = $request->barcode ?: '899' . rand(100000000, 999999999);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $category = Category::findOrFail($request->app_category_id);
        $product = Product::create([
            'app_category_id' => $request->app_category_id,
            'sku' => $sku,
            'barcode' => $barcode,
            'name' => $request->name,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
            'image' => $imagePath,
            'is_active' => $request->has('is_active') ? true : false,
            'station' => $this->stationForCategory($category, $request->input('station')),
        ]);

        $product->modifierGroups()->sync($this->modifierSyncData($request));

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        abort_unless(! $product->is_raw_material, 404);
        $categories = Category::orderBy('name')->get();
        $modifierGroups = ModifierGroup::with('options')->orderBy('sort_order')->orderBy('name')->get();
        $product->load('modifierGroups');
        return view('products.edit', compact('product', 'categories', 'modifierGroups'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'app_category_id' => 'required|exists:app_categories,id',
            'sku' => 'required|string|max:100|unique:app_products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:app_products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'station' => 'nullable|in:barista,kitchen',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image' => 'nullable|boolean',
            'modifier_group_ids' => 'nullable|array',
            'modifier_group_ids.*' => 'integer|exists:app_modifier_groups,id',
        ]);

        if ($request->hasFile('image')) {
            // Remove the previous image to avoid orphaned files in storage.
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $product->image = $imagePath;
        } elseif ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $product->image = null;
        }

        $product->app_category_id = $request->app_category_id;
        $product->sku = $request->sku;
        $product->barcode = $request->barcode;
        $product->name = $request->name;
        $product->purchase_price = $request->purchase_price;
        $product->selling_price = $request->selling_price;
        $product->stock = $request->stock;
        $product->min_stock = $request->min_stock;
        $product->station = $this->stationForCategory($product->category, $request->input('station', $product->station));
        $product->is_active = $request->has('is_active');
        $product->save();

        $product->modifierGroups()->sync($this->modifierSyncData($request));

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->transactionDetails()->count() > 0) {
            // Soft deactivate if has history
            $product->is_active = false;
            $product->save();
            return redirect()->route('products.index')->with('success', 'Produk telah dinonaktifkan karena terdapat riwayat transaksi.');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Pivot payload keeping the order in which groups were ticked.
     */
    private function modifierSyncData(Request $request): array
    {
        return collect($request->input('modifier_group_ids', []))
            ->values()
            ->mapWithKeys(fn ($id, $i) => [(int) $id => ['sort_order' => $i]])
            ->all();
    }

    private function stationForCategory(Category $category, ?string $requested): string
    {
        return match ($category->slug) {
            'minuman' => 'barista',
            'makanan-snack' => 'kitchen',
            default => $requested ?: 'barista',
        };
    }
}
