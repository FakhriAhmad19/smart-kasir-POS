<x-app-layout>
    <x-slot name="title">Beban Operasional - Smart Kasir</x-slot>
    <x-slot name="header">Pencatatan Beban Operasional Toko</x-slot>

    <div class="row g-4">
        <!-- Input Expense Form -->
        <div class="col-lg-4">
            <div class="card card-glass border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">Tambah Beban Operasional</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('expenses.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="category" class="form-label fw-semibold">Kategori Beban <span class="text-danger">*</span></label>
                            <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" min="1" placeholder="Contoh: 150000" value="{{ old('amount') }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expense_date" class="form-label fw-semibold">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Keterangan / Rincian</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Contoh: Pembelian token listrik toko / Kantong plastik 10 pack">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-touch w-100 py-2.5 fw-bold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Simpan Pengeluaran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Expense Table & Metrics -->
        <div class="col-lg-8">
            <div class="card card-stat bg-white p-4 border-0 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-semibold d-block mb-1">Total Beban Operasional Terfilter</small>
                        <h2 class="fw-extrabold text-danger m-0">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</h2>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-wallet2 fs-2"></i>
                    </div>
                </div>
            </div>

            <div class="card card-glass border-0">
                <div class="card-header bg-white border-0 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <h5 class="fw-bold m-0 text-dark">Daftar Pengeluaran Operasional</h5>
                    <form method="GET" action="{{ route('expenses.index') }}" class="d-flex gap-2">
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Nominal (Rp)</th>
                                    <th>Pencatat</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $item)
                                    <tr>
                                        <td class="ps-4 text-muted small fw-semibold">{{ $item->expense_date->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1 text-ellipsis">{{ $item->category }}</span></td>
                                        <td class="text-end fw-extrabold text-danger">Rp{{ number_format($item->amount, 0, ',', '.') }}</td>
                                        <td><small class="text-muted text-ellipsis">{{ $item->user->name ?? '-' }}</small></td>
                                        <td class="text-muted small text-ellipsis">{{ $item->notes ?: '-' }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('expenses.destroy', $item->id) }}" class="js-confirm-delete" data-confirm-text="Yakin ingin menghapus pengeluaran ini? Data yang dihapus tidak dapat dikembalikan.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-receipt display-4 text-secondary opacity-50 d-block mb-2"></i>
                                            Belum ada pencatatan beban operasional.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 p-3">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
