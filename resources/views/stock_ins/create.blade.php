<x-app-layout>
    <x-slot name="title">Catat Barang Masuk - Smart Kasir</x-slot>
    <x-slot name="header">Form Pencatatan Barang Masuk (Restock)</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-glass border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">Input Barang Masuk dari Supplier</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('stock-ins.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="app_product_id" class="form-label fw-semibold">Pilih Produk / Bahan Baku <span class="text-danger">*</span></label>
                            <select name="app_product_id" id="app_product_id" class="form-select @error('app_product_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}" {{ old('app_product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} (SKU: {{ $product->sku }} | Stok: {{ $product->stock }} {{ $product->unit }})
                                    </option>
                                @endforeach
                            </select>
                            @error('app_product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="supplier_name" class="form-label fw-semibold">Nama Supplier / PT</label>
                            <input type="text" name="supplier_name" id="supplier_name" class="form-control @error('supplier_name') is-invalid @enderror" placeholder="Contoh: PT Sumber Makmur / Mas Budi" value="{{ old('supplier_name') }}">
                            @error('supplier_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="quantity" class="form-label fw-semibold">Jumlah Masuk (Pcs/Unit) <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1" value="{{ old('quantity', 1) }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="purchase_price" class="form-label fw-semibold">Harga Beli Satuan (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" min="0" value="{{ old('purchase_price', 0) }}" required>
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4 bg-light p-3 rounded-3">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="update_hpp" value="1" id="update_hpp" checked>
                            <label class="form-check-label fw-semibold" for="update_hpp">
                                Perbarui Harga Beli (HPP) Master Produk dengan harga satuan baru ini.
                            </label>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Keterangan / No. Nota Supplier</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Catatan tambahan misal No. Surat Jalan / Nota Supplier">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-1"></i> Simpan Barang Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('app_product_id');
            const priceInput = document.getElementById('purchase_price');

            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                if (price) {
                    priceInput.value = price;
                }
            });
        });
    </script>
</x-app-layout>
