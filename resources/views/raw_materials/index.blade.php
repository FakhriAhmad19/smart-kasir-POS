<x-app-layout>
    <x-slot name="title">Master Bahan Baku - Smart Kasir</x-slot>
    <x-slot name="header">Master Bahan Baku</x-slot>

    <div class="card card-glass border-0">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold m-0">Daftar Bahan Baku</h5>
                    <small class="text-muted">Kelola stok kopi, susu, sirup, cup, dan bahan operasional lainnya.</small>
                </div>
                <a href="{{ route('raw-materials.create') }}" class="btn btn-primary btn-touch"><i class="bi bi-plus-lg me-1"></i> Tambah Bahan Baku</a>
            </div>
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-6"><input name="search" class="form-control" placeholder="Cari nama atau SKU..." value="{{ request('search') }}"></div>
                <div class="col-md-3"><button class="btn btn-secondary w-100"><i class="bi bi-search me-1"></i> Cari</button></div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr><th>Bahan Baku</th><th>Kategori</th><th>Satuan</th><th class="text-end">Stok</th><th class="text-end">HPP</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td><div class="fw-bold">{{ $material->name }}</div><small class="text-muted">SKU: {{ $material->sku }}</small></td>
                            <td>{{ $material->category->name ?? '-' }}</td>
                            <td>{{ $material->unit }}</td>
                            <td class="text-end fw-bold {{ $material->isLowStock() ? 'text-danger' : '' }}">{{ number_format($material->stock, 0, ',', '.') }}</td>
                            <td class="text-end">Rp{{ number_format($material->purchase_price, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $material->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $material->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-end text-nowrap"><a href="{{ route('raw-materials.edit', $material) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('raw-materials.destroy', $material) }}" onsubmit="return confirm('Hapus bahan baku ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Belum ada bahan baku.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $materials->links() }}
        </div>
    </div>
</x-app-layout>
