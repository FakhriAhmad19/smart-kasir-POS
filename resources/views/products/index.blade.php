<x-app-layout>
    <x-slot name="title">Master Produk - Smart Kasir</x-slot>
    <x-slot name="header">Katalog & Stok Barang</x-slot>

    <div class="card card-glass border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Katalog Produk Toko</h5>
                    <small class="text-muted">Kelola informasi barang, harga beli (HPP), harga jual, dan kontrol stok</small>
                </div>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-touch px-3.5 shadow-sm fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
                </a>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="{{ route('products.index') }}" class="row g-3 mb-4 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama produk, SKU, atau barcode..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch ms-1">
                        <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="lowStockCheck" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label small fw-bold text-danger" for="lowStockCheck">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Menipis
                        </label>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                </div>
            </form>

            <!-- Product Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fw-bold text-secondary" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4" width="15%">SKU / Barcode</th>
                            <th width="30%">Nama Produk</th>
                            <th width="15%">Kategori</th>
                            <th width="12%">Harga Beli (HPP)</th>
                            <th width="12%">Harga Jual</th>
                            <th class="text-center" width="8%">Sisa Stok</th>
                            <th class="text-center" width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="{{ $product->isLowStock() ? 'table-warning bg-opacity-25' : '' }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark font-monospace text-ellipsis">{{ $product->sku }}</div>
                                    <small class="text-muted text-ellipsis"><i class="bi bi-barcode me-1"></i>{{ $product->barcode ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-secondary border shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded-3 w-100 h-100 object-fit-cover" loading="lazy">
                                            @else
                                                <i class="bi bi-box-seam fs-4 text-primary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6 text-ellipsis">{{ $product->name }}</div>
                                            @if($product->isLowStock())
                                                <small class="badge bg-danger text-white">Stok Menipis (Min: {{ $product->min_stock }})</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5 text-ellipsis">
                                        {{ $product->category->name }}
                                    </span>
                                </td>
                                <td class="text-muted fw-semibold">Rp{{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                                <td class="fw-extrabold text-primary">Rp{{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $product->isLowStock() ? 'bg-danger' : 'bg-success bg-opacity-10 text-success' }} fw-bold fs-6 px-3 py-1.5">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Edit Produk">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="js-confirm-delete d-inline" data-confirm-title="Hapus Produk?" data-confirm-text="Apakah Anda yakin ingin menghapus produk {{ $product->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Hapus Produk">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 text-secondary opacity-50 d-block mb-2"></i>
                                    Tidak ada produk yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
