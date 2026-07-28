<x-app-layout>
    <x-slot name="title">Kategori Barang - Smart Kasir</x-slot>
    <x-slot name="header">Manajemen Kategori Barang</x-slot>

    <div class="card card-glass border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Daftar Kategori Produk</h5>
                    <small class="text-muted">Kelola pengelompokan produk toko Anda secara terstruktur</small>
                </div>
                <button type="button" class="btn btn-primary btn-touch px-3.5 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fw-bold text-secondary" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th width="25%">Nama Kategori</th>
                            <th width="20%">Slug Keyword</th>
                            <th width="30%">Keterangan</th>
                            <th class="text-center" width="10%">Total Produk</th>
                            <th class="text-end pe-4" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                            <tr>
                                <td class="ps-4 text-muted font-monospace">{{ $categories->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-6 text-ellipsis">{{ $category->name }}</div>
                                </td>
                                <td><code class="bg-light text-primary px-2 py-1 rounded border text-ellipsis">{{ $category->slug }}</code></td>
                                <td class="text-muted small text-ellipsis">{{ $category->description ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-bold">
                                        {{ $category->products_count }} Produk
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditCategory{{ $category->id }}" title="Edit Kategori">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="js-confirm-delete d-inline" data-confirm-title="Hapus Kategori?" data-confirm-text="Apakah Anda yakin ingin menghapus kategori {{ $category->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Hapus Kategori">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="modalEditCategory{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('categories.update', $category) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Deskripsi / Keterangan</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x display-4 text-secondary opacity-50 d-block mb-2"></i>
                                    Belum ada kategori barang. Silakan tambah kategori baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Add Category -->
    <div class="modal fade" id="modalAddCategory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Minuman Cold Brew" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi / Keterangan</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Keterangan singkat kategori..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
