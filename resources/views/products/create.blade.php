<x-app-layout>
    <x-slot name="title">Tambah Produk Baru - Smart Kasir</x-slot>
    <x-slot name="header">Tambah Produk Baru</x-slot>

    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Produk
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card card-glass border-0">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h5 class="fw-bold mb-4">Informasi Produk & Stok</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kategori Produk <span class="text-danger">*</span></label>
                                <select name="app_category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('app_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Es Teh Manis Jumbo" value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kode SKU (Opsional, Auto-Generate jika kosong)</label>
                                <input type="text" name="sku" class="form-control" placeholder="Contoh: MNM-001" value="{{ old('sku') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Kode Barcode / EAN (Opsional)</label>
                                <input type="text" name="barcode" class="form-control" placeholder="Scan atau input barcode..." value="{{ old('barcode') }}">
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h5 class="fw-bold mb-4">Harga & Persediaan (Stok)</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Harga Beli / HPP (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="purchase_price" class="form-control" placeholder="0" value="{{ old('purchase_price', 0) }}" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Harga Jual (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="selling_price" class="form-control" placeholder="0" value="{{ old('selling_price', 0) }}" min="0" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Jumlah Stok Awal <span class="text-danger">*</span></label>
                                <input type="number" name="stock" class="form-control" placeholder="0" value="{{ old('stock', 10) }}" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Batas Peringatan Stok Menipis (Min Stock) <span class="text-danger">*</span></label>
                                <input type="number" name="min_stock" class="form-control" placeholder="5" value="{{ old('min_stock', 5) }}" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Station Tiket <span class="text-danger">*</span></label>
                            <select name="station" class="form-select" required>
                                <option value="barista" @selected(old('station', 'barista') === 'barista')>Barista (minuman)</option>
                                <option value="kitchen" @selected(old('station') === 'kitchen')>Kitchen (makanan)</option>
                            </select>
                            <small class="text-muted">Menu akan tampil di tiket station yang dipilih.</small>
                        </div>

                        @include('products._image-field')

                        @include('products._modifier-field')

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold small" for="is_active">Status Produk Aktif (Dapat dijual di Kasir)</label>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
