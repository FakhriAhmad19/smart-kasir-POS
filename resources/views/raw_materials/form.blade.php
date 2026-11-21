<x-app-layout>
    <x-slot name="title">{{ isset($material) ? 'Edit' : 'Tambah' }} Bahan Baku - Smart Kasir</x-slot>
    <x-slot name="header">{{ isset($material) ? 'Edit' : 'Tambah' }} Bahan Baku</x-slot>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card card-glass border-0"><div class="card-body p-4">
        <form method="POST" action="{{ isset($material) ? route('raw-materials.update', $material) : route('raw-materials.store') }}">
            @csrf @if(isset($material)) @method('PUT') @endif
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Nama Bahan Baku *</label><input name="name" class="form-control" value="{{ old('name', $material->name ?? '') }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Kategori *</label><select name="app_category_id" class="form-select" required><option value="">-- Pilih --</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('app_category_id', $material->app_category_id ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label fw-semibold">SKU</label><input name="sku" class="form-control" value="{{ old('sku', $material->sku ?? '') }}" placeholder="Auto-generate jika kosong"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Satuan *</label><select name="unit" class="form-select" required>@foreach(['gram','ml','pcs','kg','liter','pack'] as $unit)<option value="{{ $unit }}" @selected(old('unit', $material->unit ?? 'pcs') === $unit)>{{ $unit }}</option>@endforeach</select></div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><label class="form-label fw-semibold">HPP per Satuan *</label><input type="number" name="purchase_price" class="form-control" min="0" value="{{ old('purchase_price', $material->purchase_price ?? 0) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Stok Awal *</label><input type="number" name="stock" class="form-control" min="0" value="{{ old('stock', $material->stock ?? 0) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Minimum Stok *</label><input type="number" name="min_stock" class="form-control" min="0" value="{{ old('min_stock', $material->min_stock ?? 0) }}" required></div>
            </div>
            <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $material->is_active ?? true))><label class="form-check-label" for="is_active">Bahan baku aktif</label></div>
            <div class="d-flex justify-content-end gap-2"><a href="{{ route('raw-materials.index') }}" class="btn btn-light">Batal</a><button class="btn btn-primary fw-bold">Simpan</button></div>
        </form>
    </div></div></div></div>
</x-app-layout>
