<x-app-layout>
    <x-slot name="title">Form Stock Opname - Smart Kasir</x-slot>
    <x-slot name="header">Form Stock Opname & Penyesuaian Stok</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-glass border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">Form Audit Stok Produk</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('adjustments.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="app_product_id" class="form-label fw-semibold">Pilih Produk <span class="text-danger">*</span></label>
                            <select name="app_product_id" id="app_product_id" class="form-select @error('app_product_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-stock="{{ $product->stock }}" {{ old('app_product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} (SKU: {{ $product->sku }} | Stok Sistem: {{ $product->stock }} {{ $product->unit }})
                                    </option>
                                @endforeach
                            </select>
                            @error('app_product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label fw-semibold">Jenis Alasan Penyesuaian <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="opname" {{ old('type') == 'opname' ? 'selected' : '' }}>Stock Opname (Hitung Fisik Rutin)</option>
                                <option value="damaged" {{ old('type') == 'damaged' ? 'selected' : '' }}>Barang Rusak / Pecah</option>
                                <option value="expired" {{ old('type') == 'expired' ? 'selected' : '' }}>Kadaluarsa (Expired)</option>
                                <option value="lost" {{ old('type') == 'lost' ? 'selected' : '' }}>Hilang / Susut</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Stok Sistem Saat Ini</label>
                                <input type="text" id="display_stock_system" class="form-control bg-light" value="0" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="quantity_actual" class="form-label fw-semibold">Jumlah Stok Fisik Sebenarnya <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_actual" id="quantity_actual" class="form-control @error('quantity_actual') is-invalid @enderror" min="0" value="{{ old('quantity_actual', 0) }}" required>
                                <small class="text-muted">Masukkan total fisik riil di rak/gudang toko.</small>
                                @error('quantity_actual')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Keterangan / Catatan Audit</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Alasan penyesuaian misal bungkus rusak digigit tikus, barang expired bulan lalu"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('adjustments.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-1"></i> Simpan Penyesuaian Stok
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
            const stockDisplay = document.getElementById('display_stock_system');
            const actualInput = document.getElementById('quantity_actual');

            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const stock = selectedOption.getAttribute('data-stock');
                if (stock !== null) {
                    stockDisplay.value = stock + ' ' + (selectedOption.textContent.match(/\| Stok Sistem: \d+ (\w+)/)?.[1] || 'unit');
                    actualInput.value = stock;
                } else {
                    stockDisplay.value = '0 pcs';
                }
            });
        });
    </script>
</x-app-layout>
