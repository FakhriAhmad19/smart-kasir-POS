<x-app-layout>
    <x-slot name="title">Riwayat Barang Masuk - Smart Kasir</x-slot>
    <x-slot name="header">Histori & Restock Barang Masuk</x-slot>

    <div class="card card-glass border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold m-0 text-dark">Riwayat Pasokan Barang Masuk</h5>
                    <small class="text-muted">Pencatatan barang dari supplier & penambahan stok otomatis</small>
                </div>
                <a href="{{ route('stock-ins.create') }}" class="btn btn-primary btn-touch px-3 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Catat Barang Masuk
                </a>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="{{ route('stock-ins.index') }}" class="row g-2 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama produk, SKU, atau supplier..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Mulai Tanggal">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="Sampai Tanggal">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    @if(request()->anyFilled(['search', 'start_date', 'end_date']))
                        <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">Tanggal & Waktu</th>
                            <th>Nama Produk</th>
                            <th>Supplier</th>
                            <th class="text-center">Jumlah Masuk</th>
                            <th class="text-end">Harga Beli Beli (HPP)</th>
                            <th class="text-end">Total Nilai</th>
                            <th>Pencatat</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockIns as $item)
                            <tr>
                                <td class="ps-3 text-muted small fw-semibold">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-bold text-dark text-ellipsis">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                    <small class="text-muted text-ellipsis">SKU: {{ $item->product->sku ?? '-' }}</small>
                                </td>
                                <td class="text-ellipsis">{{ $item->supplier_name ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">
                                        +{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">Rp{{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-extrabold text-primary">Rp{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $item->user->name ?? '-' }}</span></td>
                                <td class="text-muted small text-ellipsis">{{ $item->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-arrow-in-right display-4 text-secondary opacity-50 d-block mb-2"></i>
                                    Belum ada data barang masuk tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $stockIns->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
