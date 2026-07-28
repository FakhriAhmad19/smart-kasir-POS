<x-app-layout>
    <x-slot name="title">Shift Kasir & Audit Laci - Smart Kasir</x-slot>
    <x-slot name="header">Shift Kasir & Rekonsiliasi Modal Laci</x-slot>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card card-glass border-0">
                <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="fw-bold m-0 text-dark">Status Shift Kasir Anda Saat Ini</h5>
                    @if($activeShift)
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">
                            <i class="bi bi-clock-history me-1"></i> SHIFT AKTIF BERJALAN
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 rounded-pill fw-bold">
                            <i class="bi bi-dash-circle me-1"></i> TIDAK ADA SHIFT AKTIF
                        </span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($activeShift)
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Waktu Buka Shift:</small>
                                <strong class="fs-6 text-dark">{{ $activeShift->start_time->format('d/m/Y H:i:s') }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Modal Awal Laci Kasir:</small>
                                <strong class="fs-5 text-primary">Rp{{ number_format($activeShift->starting_cash, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('pos.index') }}" class="btn btn-primary px-4 btn-touch shadow-sm">
                                <i class="bi bi-calculator me-1"></i> Masuk Layar POS
                            </a>
                            <a href="{{ route('shifts.close_form') }}" class="btn btn-warning px-4 btn-touch fw-bold">
                                <i class="bi bi-box-arrow-right me-1"></i> Tutup Shift Kasir (Close Shift)
                            </a>
                        </div>
                    @else
                        <p class="text-muted mb-4">Anda belum membuka shift kasir hari ini. Buka shift kasir dan masukkan modal awal laci untuk mulai melayani transaksi.</p>
                        <a href="{{ route('shifts.open_form') }}" class="btn btn-success btn-touch px-4 fw-bold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Buka Shift Kasir Baru (Open Shift)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Shift History Table -->
    <div class="card card-glass border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold m-0 text-dark">Riwayat Shift Kasir & Rekonsiliasi Laci</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">Kasir</th>
                            <th>Waktu Buka</th>
                            <th>Waktu Tutup</th>
                            <th class="text-end">Modal Awal</th>
                            <th class="text-end">Tunai Masuk</th>
                            <th class="text-end">Wajib Laci</th>
                            <th class="text-end">Fisik Laci</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            <tr>
                                <td class="ps-4 fw-bold text-dark text-ellipsis">{{ $shift->user->name ?? 'Kasir' }}</td>
                                <td class="text-muted small">{{ $shift->start_time->format('d/m/Y H:i') }}</td>
                                <td class="text-muted small">{{ $shift->end_time ? $shift->end_time->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-end fw-semibold">Rp{{ number_format($shift->starting_cash, 0, ',', '.') }}</td>
                                <td class="text-end text-success fw-bold">Rp{{ number_format($shift->total_cash_sales, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp{{ number_format($shift->expected_ending_cash, 0, ',', '.') }}</td>
                                <td class="text-end fw-extrabold text-primary">
                                    {{ $shift->actual_ending_cash !== null ? 'Rp' . number_format($shift->actual_ending_cash, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end fw-bold">
                                    @if($shift->cash_difference === null)
                                        -
                                    @elseif($shift->cash_difference == 0)
                                        <span class="badge bg-success bg-opacity-10 text-success">PAS (Rp0)</span>
                                    @elseif($shift->cash_difference > 0)
                                        <span class="badge bg-info bg-opacity-10 text-info">+Rp{{ number_format($shift->cash_difference, 0, ',', '.') }}</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">-Rp{{ number_format(abs($shift->cash_difference), 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($shift->status === 'open')
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1">SELESAI</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('reports.shift_pdf', $shift->id) }}" target="_blank" class="btn btn-sm btn-outline-danger py-1 px-2" title="Unduh PDF Audit Shift">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    Belum ada riwayat shift kasir tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 p-3">
            {{ $shifts->links() }}
        </div>
    </div>
</x-app-layout>
