<x-app-layout>
    <x-slot name="title">Tutup Shift Kasir - Smart Kasir</x-slot>
    <x-slot name="header">Tutup Shift & Audit Laci Kasir (Close Shift)</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-glass border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">Ringkasan Penjualan Shift Ini</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Modal Awal Laci</small>
                                <h4 class="fw-bold text-dark m-0">Rp{{ number_format($activeShift->starting_cash, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Total Penjualan Tunai Shift Ini</small>
                                <h4 class="fw-bold text-success m-0">+Rp{{ number_format($cashSales, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Total Penjualan QRIS (Non-Tunai)</small>
                                <h4 class="fw-bold text-info m-0">Rp{{ number_format($qrisSales, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary">
                                <small class="text-primary fw-semibold d-block mb-1">Uang Fisik Wajib Ada di Laci</small>
                                <h3 class="fw-extrabold text-primary m-0">Rp{{ number_format($expectedEndingCash, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('shifts.close') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="actual_ending_cash" class="form-label fw-bold fs-6">Hitung & Masukkan Total Uang Fisik Laci (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="actual_ending_cash" id="actual_ending_cash" class="form-control form-control-lg @error('actual_ending_cash') is-invalid @enderror" min="0" placeholder="Hitung uang fisik di laci kasir..." value="{{ old('actual_ending_cash', $expectedEndingCash) }}" required autofocus>
                            <small class="text-muted">Masukkan jumlah uang kertas + koin fisik di laci saat ini.</small>
                            @error('actual_ending_cash')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Catatan Penutupan Shift / Alasan Selisih</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Catatan tambahan jika ada selisih atau catatan shift"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-right me-1"></i> Selesaikan & Tutup Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
