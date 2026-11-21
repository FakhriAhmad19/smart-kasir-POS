<?php

namespace App\Http\Controllers;

use App\Models\ModifierGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModifierGroupController extends Controller
{
    public function index()
    {
        $groups = ModifierGroup::with('options')
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modifiers.index', compact('groups'));
    }

    public function create()
    {
        $group = new ModifierGroup(['is_required' => false, 'min_select' => 0, 'max_select' => 1]);
        $products = Product::with('category')->orderBy('name')->get();

        return view('modifiers.form', [
            'group' => $group,
            'options' => [['id' => null, 'name' => '', 'price' => 0, 'is_default' => false, 'is_active' => true]],
            'products' => $products,
            'selectedProductIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $group = ModifierGroup::create($data['group']);
            $this->syncOptions($group, $data['options']);
            $group->products()->sync($data['product_ids']);
        });

        return redirect()->route('modifiers.index')->with('success', 'Grup modifier berhasil ditambahkan.');
    }

    public function edit(ModifierGroup $modifier)
    {
        $modifier->load('options');
        $products = Product::with('category')->orderBy('name')->get();

        return view('modifiers.form', [
            'group' => $modifier,
            'options' => $modifier->options->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'price' => (float) $o->price,
                'is_default' => $o->is_default,
                'is_active' => $o->is_active,
            ])->all(),
            'products' => $products,
            'selectedProductIds' => $modifier->products()->pluck('app_products.id')->all(),
        ]);
    }

    public function update(Request $request, ModifierGroup $modifier)
    {
        $data = $this->validated($request, $modifier);

        DB::transaction(function () use ($modifier, $data) {
            $modifier->update($data['group']);
            $this->syncOptions($modifier, $data['options']);
            $modifier->products()->sync($data['product_ids']);
        });

        return redirect()->route('modifiers.index')->with('success', 'Grup modifier berhasil diperbarui.');
    }

    public function destroy(ModifierGroup $modifier)
    {
        // Past transactions keep a JSON snapshot of chosen modifiers,
        // so deleting master data never alters historical receipts.
        $modifier->delete();

        return redirect()->route('modifiers.index')->with('success', 'Grup modifier berhasil dihapus.');
    }

    /**
     * @return array{group: array, options: array, product_ids: array}
     */
    private function validated(Request $request, ?ModifierGroup $group = null): array
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'is_required' => 'nullable|boolean',
            'selection_type' => 'required|in:single,multiple',
            'min_select' => 'nullable|integer|min:0|max:20',
            'max_select' => 'nullable|integer|min:0|max:20',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'options' => 'required|array|min:1|max:30',
            'options.*.id' => 'nullable|integer',
            'options.*.name' => 'required|string|max:100',
            'options.*.price' => 'required|numeric|min:0|max:100000000',
            'options.*.is_default' => 'nullable|boolean',
            'options.*.is_active' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:app_products,id',
        ], [
            'options.required' => 'Minimal harus ada 1 opsi.',
            'options.*.name.required' => 'Nama opsi wajib diisi.',
        ]);

        $isRequired = $request->boolean('is_required');

        if ($request->selection_type === 'single') {
            $max = 1;
            $min = $isRequired ? 1 : 0;
        } else {
            $max = (int) ($request->max_select ?? 0); // 0 = no upper limit
            $min = (int) ($request->min_select ?? 0);
        }

        if ($isRequired && $min < 1) {
            $min = 1;
        }
        if (! $isRequired) {
            $min = 0;
        }

        $options = collect($request->options)->values()->map(fn ($o) => [
            'id' => isset($o['id']) && $o['id'] !== '' ? (int) $o['id'] : null,
            'name' => trim($o['name']),
            'price' => (float) $o['price'],
            'is_default' => ! empty($o['is_default']),
            'is_active' => ! empty($o['is_active']),
        ]);

        $errors = [];
        $activeCount = $options->where('is_active', true)->count();

        if ($max > 0 && $min > $max) {
            $errors['min_select'] = 'Minimal pilihan tidak boleh lebih besar dari maksimal pilihan.';
        }
        if ($activeCount < max($min, 1)) {
            $errors['options'] = "Jumlah opsi aktif ({$activeCount}) kurang dari minimal pilihan ({$min}).";
        }
        $defaults = $options->where('is_default', true)->count();
        if ($max > 0 && $defaults > $max) {
            $errors['options'] = "Opsi default ({$defaults}) melebihi maksimal pilihan ({$max}).";
        }

        // Option ids must belong to this group, otherwise a crafted request
        // could re-parent options from another group.
        $existingIds = $group ? $group->options()->pluck('id')->all() : [];
        foreach ($options as $o) {
            if ($o['id'] !== null && ! in_array($o['id'], $existingIds, true)) {
                $errors['options'] = 'Data opsi tidak valid. Muat ulang halaman lalu coba lagi.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return [
            'group' => [
                'name' => trim($request->name),
                'is_required' => $isRequired,
                'min_select' => $min,
                'max_select' => $max,
                'sort_order' => (int) ($request->sort_order ?? 0),
            ],
            'options' => $options->all(),
            'product_ids' => $request->input('product_ids', []),
        ];
    }

    private function syncOptions(ModifierGroup $group, array $options): void
    {
        $keptIds = [];

        foreach ($options as $index => $o) {
            $attrs = [
                'name' => $o['name'],
                'price' => $o['price'],
                'is_default' => $o['is_default'],
                'is_active' => $o['is_active'],
                'sort_order' => $index,
            ];

            if ($o['id']) {
                $group->options()->whereKey($o['id'])->update($attrs);
                $keptIds[] = $o['id'];
            } else {
                $keptIds[] = $group->options()->create($attrs)->id;
            }
        }

        $group->options()->whereNotIn('id', $keptIds)->delete();
    }
}
