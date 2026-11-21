<x-app-layout>
    <x-slot name="title">Meja - Smart Kasir</x-slot>
    <x-slot name="header">Manajemen Meja</x-slot>

    @if($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-glass border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Daftar Meja</h5>
                    <small class="text-muted">Pesanan dine-in wajib memilih meja agar bisa diantar.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-touch fw-bold" data-bs-toggle="modal" data-bs-target="#modalBulkTable">
                        <i class="bi bi-collection me-1"></i> Tambah Banyak
                    </button>
                    <button type="button" class="btn btn-primary btn-touch shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTable" data-mode="create">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Meja
                    </button>
                </div>
            </div>

            @if($tables->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-grid-3x3-gap display-4 text-secondary opacity-50 d-block mb-2"></i>
                    <p class="fw-semibold mb-1">Belum ada meja.</p>
                    <small>Gunakan <b>Tambah Banyak</b> untuk membuat "Meja 1" sampai "Meja 10" sekaligus.</small>
                </div>
            @else
                @foreach($tables->groupBy(fn ($t) => $t->area ?: 'Tanpa Area') as $area => $areaTables)
                    <h6 class="text-uppercase text-muted fw-bold small mt-3 mb-2">{{ $area }} <span class="fw-normal">({{ $areaTables->count() }})</span></h6>
                    <div class="row g-2 mb-2">
                        @foreach($areaTables as $table)
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                <div class="border rounded-3 p-3 h-100 bg-white text-center {{ $table->is_active ? '' : 'opacity-50' }}">
                                    <i class="bi bi-grid-3x3-gap-fill fs-4 text-primary d-block mb-1"></i>
                                    <div class="fw-bold text-truncate">{{ $table->name }}</div>
                                    <small class="text-muted d-block mb-2">
                                        {{ $table->capacity ? $table->capacity . ' kursi' : '-' }}
                                        @unless($table->is_active) · <span class="text-danger">Nonaktif</span> @endunless
                                    </small>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#modalTable" data-mode="edit"
                                                data-action="{{ route('tables.update', $table) }}"
                                                data-name="{{ $table->name }}" data-area="{{ $table->area }}"
                                                data-capacity="{{ $table->capacity }}" data-sort="{{ $table->sort_order }}"
                                                data-active="{{ $table->is_active ? 1 : 0 }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('tables.destroy', $table) }}" method="POST" class="js-confirm-delete d-inline" data-confirm-title="Hapus Meja?" data-confirm-text="Hapus {{ $table->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <datalist id="areaList">
        @foreach($areas as $area)
            <option value="{{ $area }}">
        @endforeach
        <option value="Indoor"><option value="Outdoor"><option value="Smoking Area"><option value="Lantai 2">
    </datalist>

    <!-- Modal Create / Edit -->
    <div class="modal fade" id="modalTable" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('tables.store') }}" class="modal-content border-0 rounded-4" id="tableForm">
                @csrf
                <input type="hidden" name="_method" value="POST" id="tableMethod">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="tableModalTitle">Tambah Meja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Nama / Nomor Meja <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="tName" class="form-control" placeholder="Contoh: Meja 1, A3, Sofa 2" required maxlength="50">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" id="tArea" class="form-control" list="areaList" placeholder="Indoor" maxlength="50">
                        </div>
                        <div class="col-3">
                            <label class="form-label">Kursi</label>
                            <input type="number" name="capacity" id="tCapacity" class="form-control" min="1" max="50">
                        </div>
                        <div class="col-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" id="tSort" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tActive" checked>
                        <label class="form-check-label small fw-semibold" for="tActive">Aktif (bisa dipilih di kasir)</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Bulk Create -->
    <div class="modal fade" id="modalBulkTable" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('tables.bulk') }}" class="modal-content border-0 rounded-4">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Tambah Banyak Meja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Awalan Nama</label>
                            <input type="text" name="prefix" class="form-control" value="Meja" required maxlength="40">
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label">Dari</label>
                            <input type="number" name="from" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label">Sampai</label>
                            <input type="number" name="to" class="form-control" value="10" min="1" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" list="areaList" placeholder="Indoor" maxlength="50">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Kursi</label>
                            <input type="number" name="capacity" class="form-control" min="1" max="50" value="4">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Nama yang sudah ada akan dilewati.</small>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Buat Meja</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('modalTable').addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            const form = document.getElementById('tableForm');
            const edit = btn && btn.dataset.mode === 'edit';

            document.getElementById('tableModalTitle').innerText = edit ? 'Edit Meja' : 'Tambah Meja';
            form.action = edit ? btn.dataset.action : @json(route('tables.store'));
            document.getElementById('tableMethod').value = edit ? 'PUT' : 'POST';
            document.getElementById('tName').value = edit ? btn.dataset.name : '';
            document.getElementById('tArea').value = edit ? (btn.dataset.area || '') : '';
            document.getElementById('tCapacity').value = edit ? (btn.dataset.capacity || '') : '';
            document.getElementById('tSort').value = edit ? (btn.dataset.sort || 0) : 0;
            document.getElementById('tActive').checked = edit ? btn.dataset.active === '1' : true;
        });
    </script>
    @endpush
</x-app-layout>
