<x-app-layout>
    <x-slot name="title">Buka Shift Kasir - Smart Kasir</x-slot>
    <x-slot name="header">Buka Shift Kasir Baru (Open Shift)</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card card-glass border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">Form Modal Awal Laci Kasir</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('shifts.open') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kasir Bertugas</label>
                            <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="starting_cash" class="form-label fw-semibold">Modal Awal Laci Kasir (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="starting_cash" id="starting_cash" class="form-control form-control-lg @error('starting_cash') is-invalid @enderror" min="0" placeholder="Contoh: 200000" value="{{ old('starting_cash', 200000) }}" required autofocus>
                            <small class="text-muted">Masukkan jumlah uang fisik di laci kasir untuk uang kembalian pembeli.</small>
                            @error('starting_cash')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Catatan Awal Shift</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Catatan opsional misal pecahan uang 10rb 10 lembar, 5rb 20 lembar"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">
                                <i class="bi bi-check-circle me-1"></i> Mulai Shift Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
