<x-app-layout>
    <x-slot name="title">Stock Opname & Barang Rusak - Smart Kasir</x-slot>
    <x-slot name="header">Audit Inventory & Stock Opname</x-slot>

    <div class="card card-glass border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold m-0 text-dark">Histori Stock Opname & Penyesuaian Stok</h5>
                    <small class="text-muted">Pencatatan hasil audit stok fisik, barang rusak, hilang, & kadaluarsa</small>
                </div>
                <a href="{{ route('adjustments.create') }}" class="btn btn-primary btn-touch px-3 shadow-sm">
                    <i class="bi bi-sliders me-1"></i> Form Stock Opname Baru
                </a>
            </div>

            <!-- Filter -->
            <form method="GET" action="{{ route('adjustments.index') }}" class="row g-2 mb-4">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama produk atau SKU..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Jenis Alasan</option>
                        <option value="opname" {{ request('type') === 'opname' ? 'selected' : '' }}>Stock Opname (Hitung Fisik)</option>
                        <option value="damaged" {{ request('type') === 'damaged' ? 'selected' : '' }}>Barang Rusak</option>
                        <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>Kadaluarsa (Expired)</option>
                        <option value="lost" {{ request('type') === 'lost' ? 'selected' : '' }}>Hilang / Susut</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    @if(request()->anyFilled(['search', 'type']))
                        <a href="{{ route('adjustments.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Nama Produk</th>
                            <th>Jenis Penyesuaian</th>
                            <th class="text-center">Stok Sebelum</th>
                            <th class="text-center">Selisih Dityesuaikan</th>
                            <th class="text-center">Stok Akhir</th>
                            <th>Petugas</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $item)
                            <tr>
                                <td class="ps-3 text-muted small fw-semibold">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-bold text-dark text-ellipsis">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                    <small class="text-muted text-ellipsis">SKU: {{ $item->product->sku ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($item->type === 'opname')
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1">STOCK OPNAME</span>
                                    @elseif($item->type === 'damaged')
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1">BARANG RUSAK</span>
                                    @elseif($item->type === 'expired')
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1">KADALUARSA</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1">HILANG</span>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold">{{ $item->quantity_before }} pcs</td>
                                <td class="text-center fw-bold">
                                    @if($item->quantity_adjusted > 0)
                                        <span class="text-success">+{{ $item->quantity_adjusted }} pcs</span>
                                    @elseif($item->quantity_adjusted < 0)
                                        <span class="text-danger">{{ $item->quantity_adjusted }} pcs</span>
                                    @else
                                        <span class="text-muted">0 (Pas)</span>
                                    @endif
                                </td>
                                <td class="text-center fw-extrabold text-dark">{{ $item->quantity_after }} pcs</td>
                                <td><small class="text-muted text-ellipsis">{{ $item->user->name ?? '-' }}</small></td>
                                <td class="text-muted small text-ellipsis">{{ $item->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    Belum ada catatan penyesuaian stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
