<x-app-layout>
    <x-slot name="title">Edit Produk - Smart Kasir</x-slot>
    <x-slot name="header">Edit Produk: {{ $product->name }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Produk
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-glass border-0">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <h5 class="fw-bold mb-4">Informasi Produk & Stok</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kategori Produk <span class="text-danger">*</span></label>
                                <select name="app_category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('app_category_id', $product->app_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kode SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kode Barcode / EAN</label>
                                <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h5 class="fw-bold mb-4">Harga & Persediaan (Stok)</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Harga Beli / HPP (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price) }}" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Harga Jual (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price) }}" min="0" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Jumlah Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Batas Peringatan Stok Menipis (Min Stock) <span class="text-danger">*</span></label>
                                <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock) }}" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Ganti Foto Produk (Opsional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            @if($product->image)
                                <div class="mt-2 text-muted small">
                                    Foto saat ini: <a href="{{ asset('storage/' . $product->image) }}" target="_blank">Lihat Gambar</a>
                                </div>
                            @endif
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="is_active">Status Produk Aktif</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
