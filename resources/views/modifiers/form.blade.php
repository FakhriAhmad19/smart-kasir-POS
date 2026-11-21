@php
    $isEdit = $group->exists;
    $oldOptions = old('options', $options);
    $selectionType = old('selection_type', $group->max_select === 1 ? 'single' : 'multiple');
    $selected = collect(old('product_ids', $selectedProductIds))->map(fn ($id) => (int) $id)->all();
@endphp
<x-app-layout>
    <x-slot name="title">{{ $isEdit ? 'Edit' : 'Tambah' }} Grup Modifier - Smart Kasir</x-slot>
    <x-slot name="header">{{ $isEdit ? 'Edit Grup: ' . $group->name : 'Tambah Grup Varian / Modifier' }}</x-slot>

    <div class="mb-4">
        <a href="{{ route('modifiers.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Varian & Modifier
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $isEdit ? route('modifiers.update', $group) : route('modifiers.store') }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card card-glass border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Pengaturan Grup</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Nama Grup <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $group->name) }}" placeholder="Contoh: Ukuran, Level Gula, Topping" required maxlength="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $group->sort_order ?? 0) }}" min="0">
                            </div>
                        </div>

                        <label class="form-label">Cara Memilih</label>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <input type="radio" class="btn-check" name="selection_type" id="selSingle" value="single" {{ $selectionType === 'single' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary w-100 text-start p-3" for="selSingle">
                                    <i class="bi bi-ui-radios me-1"></i> <b>Pilih satu</b>
                                    <small class="d-block fw-normal opacity-75">Untuk varian: ukuran, panas/dingin, level gula</small>
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <input type="radio" class="btn-check" name="selection_type" id="selMulti" value="multiple" {{ $selectionType === 'multiple' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary w-100 text-start p-3" for="selMulti">
                                    <i class="bi bi-ui-checks me-1"></i> <b>Boleh pilih banyak</b>
                                    <small class="d-block fw-normal opacity-75">Untuk add-on: topping, extra shot, sirup</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="isRequired" {{ old('is_required', $group->is_required) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="isRequired">Wajib dipilih (kasir tidak bisa lanjut sebelum memilih)</label>
                        </div>

                        <div class="row g-3" id="multiLimits">
                            <div class="col-sm-6" id="minWrap">
                                <label class="form-label">Minimal dipilih</label>
                                <input type="number" name="min_select" class="form-control" value="{{ old('min_select', max(1, $group->min_select)) }}" min="1" max="20">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Maksimal dipilih</label>
                                <input type="number" name="max_select" class="form-control" value="{{ old('max_select', $group->max_select === 1 ? 0 : $group->max_select) }}" min="0" max="20">
                                <small class="text-muted">Isi 0 untuk tanpa batas.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-glass border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Daftar Opsi</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddOption">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Opsi
                            </button>
                        </div>
                        <small class="text-muted d-block mb-3">Harga tambahan diisi 0 jika gratis. Opsi <b>default</b> otomatis terpilih di kasir.</small>

                        <div class="d-none d-md-flex row g-2 small text-muted fw-semibold mb-1 px-1">
                            <div class="col-md-5">Nama Opsi</div>
                            <div class="col-md-3">Harga Tambahan (Rp)</div>
                            <div class="col-md-3">Status</div>
                        </div>

                        <div id="optionRows">
                            @foreach($oldOptions as $i => $opt)
                                <div class="row g-2 align-items-center mb-2 option-row">
                                    <input type="hidden" name="options[{{ $i }}][id]" value="{{ $opt['id'] ?? '' }}">
                                    <div class="col-12 col-md-5">
                                        <input type="text" name="options[{{ $i }}][name]" class="form-control" value="{{ $opt['name'] ?? '' }}" placeholder="Contoh: Large" required maxlength="100">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <input type="number" name="options[{{ $i }}][price]" class="form-control" value="{{ (float) ($opt['price'] ?? 0) }}" min="0" step="500" required>
                                    </div>
                                    <div class="col-5 col-md-3 d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="options[{{ $i }}][is_default]" value="1" id="def{{ $i }}" {{ !empty($opt['is_default']) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="def{{ $i }}">Default</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="options[{{ $i }}][is_active]" value="1" id="act{{ $i }}" {{ !empty($opt['is_active']) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="act{{ $i }}">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="col-1 text-end">
                                        <button type="button" class="btn btn-sm text-danger p-1 btn-remove-option" title="Hapus opsi">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card card-glass border-0 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold mb-1">Pakai di Menu</h5>
                        <small class="text-muted mb-3">Centang menu yang memakai grup ini. Bisa juga diatur dari halaman edit produk.</small>

                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="productFilter" placeholder="Cari menu...">
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted"><span id="selectedCount">{{ count($selected) }}</span> menu dipilih</span>
                            <span>
                                <a href="#" id="selectAllVisible" class="text-decoration-none">Pilih semua</a> ·
                                <a href="#" id="clearAll" class="text-decoration-none text-danger">Kosongkan</a>
                            </span>
                        </div>

                        <div class="border rounded-3 overflow-auto flex-grow-1" style="max-height: 460px;">
                            @forelse($products as $product)
                                <label class="d-flex align-items-center gap-2 px-3 py-2 border-bottom product-check-row" data-name="{{ strtolower($product->name . ' ' . ($product->category->name ?? '')) }}">
                                    <input type="checkbox" class="form-check-input mt-0 product-check" name="product_ids[]" value="{{ $product->id }}" {{ in_array($product->id, $selected, true) ? 'checked' : '' }}>
                                    <span class="flex-grow-1 min-w-0">
                                        <span class="d-block text-truncate fw-semibold small">{{ $product->name }}</span>
                                        <small class="text-muted">{{ $product->category->name ?? '-' }}</small>
                                    </span>
                                </label>
                            @empty
                                <div class="p-3 text-muted small">Belum ada produk.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('modifiers.index') }}" class="btn btn-light px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Grup' }}</button>
        </div>
    </form>

    <template id="optionRowTemplate">
        <div class="row g-2 align-items-center mb-2 option-row">
            <input type="hidden" data-field="id" value="">
            <div class="col-12 col-md-5">
                <input type="text" data-field="name" class="form-control" placeholder="Nama opsi" required maxlength="100">
            </div>
            <div class="col-6 col-md-3">
                <input type="number" data-field="price" class="form-control" value="0" min="0" step="500" required>
            </div>
            <div class="col-5 col-md-3 d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" data-field="is_default" value="1">
                    <label class="form-check-label small">Default</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" data-field="is_active" value="1" checked>
                    <label class="form-check-label small">Aktif</label>
                </div>
            </div>
            <div class="col-1 text-end">
                <button type="button" class="btn btn-sm text-danger p-1 btn-remove-option" title="Hapus opsi">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </template>

    @push('scripts')
    <script>
        (function () {
            const rows = document.getElementById('optionRows');
            const tpl = document.getElementById('optionRowTemplate');
            let nextIndex = rows.querySelectorAll('.option-row').length;

            function isSingle() { return document.getElementById('selSingle').checked; }

            // Selection-type UI: limits only matter for "multiple".
            function syncSelectionUi() {
                const single = isSingle();
                const required = document.getElementById('isRequired').checked;
                document.getElementById('multiLimits').classList.toggle('d-none', single);
                document.getElementById('minWrap').classList.toggle('d-none', !required);
            }
            document.querySelectorAll('input[name="selection_type"], #isRequired')
                .forEach(el => el.addEventListener('change', syncSelectionUi));
            syncSelectionUi();

            // In single-choice groups only one option can be the default.
            rows.addEventListener('change', function (e) {
                if (!e.target.name?.endsWith('[is_default]') || !e.target.checked || !isSingle()) return;
                rows.querySelectorAll('input[name$="[is_default]"]').forEach(cb => {
                    if (cb !== e.target) cb.checked = false;
                });
            });

            document.getElementById('btnAddOption').addEventListener('click', function () {
                const node = tpl.content.firstElementChild.cloneNode(true);
                const i = nextIndex++;
                node.querySelectorAll('[data-field]').forEach(input => {
                    const field = input.dataset.field;
                    input.name = `options[${i}][${field}]`;
                    if (input.type === 'checkbox') {
                        input.id = `${field}${i}`;
                        input.nextElementSibling.setAttribute('for', input.id);
                    }
                });
                rows.appendChild(node);
                node.querySelector('[data-field="name"]').focus();
            });

            rows.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-remove-option');
                if (!btn) return;
                if (rows.querySelectorAll('.option-row').length <= 1) {
                    alert('Minimal harus ada 1 opsi.');
                    return;
                }
                btn.closest('.option-row').remove();
            });

            // Product picker
            const filter = document.getElementById('productFilter');
            const countEl = document.getElementById('selectedCount');
            const productRows = document.querySelectorAll('.product-check-row');
            const updateCount = () => countEl.innerText = document.querySelectorAll('.product-check:checked').length;

            filter.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                productRows.forEach(r => r.classList.toggle('d-none', !r.dataset.name.includes(q)));
            });
            document.querySelectorAll('.product-check').forEach(cb => cb.addEventListener('change', updateCount));
            document.getElementById('selectAllVisible').addEventListener('click', function (e) {
                e.preventDefault();
                productRows.forEach(r => { if (!r.classList.contains('d-none')) r.querySelector('input').checked = true; });
                updateCount();
            });
            document.getElementById('clearAll').addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelectorAll('.product-check').forEach(cb => cb.checked = false);
                updateCount();
            });
        })();
    </script>
    @endpush
</x-app-layout>
