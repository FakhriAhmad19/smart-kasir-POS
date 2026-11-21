<x-app-layout>
    <x-slot name="title">Rekonsiliasi Kasir - Smart Kasir</x-slot>
    <x-slot name="header">Rekonsiliasi Harian Kasir (Close Shift)</x-slot>

    <!-- Filter Card -->
    <div class="card card-glass border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.reconciliation') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-secondary">Tanggal Shift Kasir</label>
                    <input type="date" name="date" class="form-control bg-light" value="{{ $date }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-secondary">Pilih Kasir</label>
                    <select name="user_id" class="form-select bg-light">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $cashier)
                            <option value="{{ $cashier->id }}" {{ $userId == $cashier->id ? 'selected' : '' }}>
                                {{ $cashier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 btn-touch">
                        <i class="bi bi-search me-1"></i> Tampilkan Rekonsiliasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reconciliation Financial Split Cards -->
    <div class="row g-4 mb-4">
        <!-- Physical Cash Box Split -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Total Penerimaan TUNAI</h5>
                        <small class="text-muted">Uang Fisik Wajib Ada di Laci Kasir</small>
                    </div>
                </div>
                <h2 class="fw-extrabold text-warning m-0">Rp{{ number_format($totalCash, 0, ',', '.') }}</h2>
                <div class="mt-3 pt-3 border-top text-muted small">
                    Penerimaan dari {{ $transactions->where('payment_method', 'cash')->count() }} transaksi tunai.
                </div>
            </div>
        </div>

        <!-- QRIS Bank Mutation Split -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bi bi-qr-code-scan fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Total Penerimaan QRIS</h5>
                        <small class="text-muted">Mutasi Rekening Bank / Merchant QRIS</small>
                    </div>
                </div>
                <h2 class="fw-extrabold text-info m-0">Rp{{ number_format($totalQris, 0, ',', '.') }}</h2>
                <div class="mt-3 pt-3 border-top text-muted small">
                    Penerimaan dari {{ $transactions->where('payment_method', 'qris')->count() }} transaksi QRIS.
                </div>
            </div>
        </div>
    </div>

    <!-- Total Income Banner -->
    <div class="card bg-primary text-white border-0 rounded-4 p-4 shadow-sm mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="text-white-50 small fw-semibold text-uppercase">Total Rekap Omset Tanggal {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}</span>
                <h2 class="fw-extrabold text-white m-0">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</h2>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
                    {{ $totalTrxCount }} Total Transaksi
                </span>
            </div>
        </div>
    </div>

    <!-- Audit List -->
    <div class="card card-glass border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold mb-0 text-dark">Rincian Audit Shift Kasir</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No Invoice</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th>Metode Bayar</th>
                        <th>Paid Amount</th>
                        <th>Change</th>
                        <th class="text-end pe-4">Nominal Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td class="ps-4 fw-bold text-primary text-ellipsis">{{ $trx->invoice_number }}</td>
                            <td class="text-muted small text-ellipsis">{{ ($trx->paid_at ?? $trx->created_at)->format('H:i:s') }}</td>
                            <td class="text-ellipsis">{{ $trx->user->name ?? 'Kasir' }}</td>
                            <td>
                                @if($trx->payment_method === 'cash')
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1">TUNAI</span>
                                @else
                                    <span class="badge bg-info bg-opacity-10 text-info px-2.5 py-1">QRIS</span>
                                @endif
                            </td>
                            <td class="text-muted small">Rp{{ number_format($trx->paid_amount, 0, ',', '.') }}</td>
                            <td class="text-muted small">Rp{{ number_format($trx->change_amount, 0, ',', '.') }}</td>
                            <td class="text-end pe-4 fw-bold text-dark">Rp{{ number_format($trx->final_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Belum ada transaksi pada tanggal shift ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
