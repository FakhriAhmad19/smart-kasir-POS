<x-app-layout>
    <x-slot name="title">Laporan Penjualan - Smart Kasir</x-slot>
    <x-slot name="header">Laporan Transaksi & Penjualan</x-slot>

    <!-- Filter Card -->
    <div class="card card-glass border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-secondary">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control bg-light" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-secondary">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control bg-light" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small text-secondary">Metode Bayar</label>
                    <select name="payment_method" class="form-select bg-light">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS UMKM</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small text-secondary">Kasir</label>
                    <select name="user_id" class="form-select bg-light">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $cashier)
                            <option value="{{ $cashier->id }}" {{ $userId == $cashier->id ? 'selected' : '' }}>
                                {{ $cashier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2.5">
                    <button type="submit" class="btn btn-primary w-100 btn-touch fw-bold" title="Filter Data"><i class="bi bi-filter me-1"></i> Filter</button>
                    <a href="{{ route('reports.export_csv', request()->all()) }}" class="btn btn-success btn-touch px-3" title="Unduh CSV Excel">
                        <i class="bi bi-file-earmark-excel"></i>
                    </a>
                    <a href="{{ route('reports.export_pdf', request()->all()) }}" target="_blank" class="btn btn-danger btn-touch px-3" title="Unduh PDF Resmi">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat bg-white p-3 border-0 shadow-sm">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem;">Total Omset</small>
                <h5 class="fw-extrabold text-primary m-0">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</h5>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat bg-white p-3 border-0 shadow-sm">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem;">Transaksi</small>
                <h5 class="fw-extrabold text-dark m-0">{{ $totalTransactions }} Trx</h5>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat bg-white p-3 border-0 shadow-sm">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem;">Total Diskon</small>
                <h5 class="fw-extrabold text-danger m-0">Rp{{ number_format($totalDiscount, 0, ',', '.') }}</h5>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat bg-white p-3 border-0 shadow-sm">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem;">Laba Kotor</small>
                <h5 class="fw-extrabold text-success m-0">Rp{{ number_format($totalProfit, 0, ',', '.') }}</h5>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat bg-white p-3 border-0 shadow-sm">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem;">Total Beban</small>
                <h5 class="fw-extrabold text-danger m-0">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</h5>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stat bg-white p-3 border-0 shadow-sm">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem;">Laba Bersih</small>
                <h5 class="fw-extrabold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} m-0">Rp{{ number_format($netProfit, 0, ',', '.') }}</h5>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card card-glass border-0 shadow-sm">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-receipt me-2 text-primary"></i>Rincian Transaksi Penjualan</h6>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fw-bold text-secondary" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4" width="15%">No Invoice</th>
                            <th width="15%">Waktu Transaksi</th>
                            <th width="12%">Kasir</th>
                            <th width="28%">Detail Item</th>
                            <th width="10%">Metode</th>
                            <th class="text-end" width="10%">Diskon</th>
                            <th class="text-end" width="10%">Total Akhir</th>
                            <th class="text-center pe-4" width="10%">Struk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                            <tr>
                                <td class="ps-4 fw-bold text-primary font-monospace text-ellipsis">{{ $trx->invoice_number }}</td>
                                <td class="text-muted small">{{ $trx->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="fw-semibold text-dark">{{ $trx->user->name ?? 'Kasir' }}</td>
                                <td>
                                    <small class="text-dark text-ellipsis">
                                        {{ $trx->details->pluck('product.name')->filter()->implode(', ') ?: 'Produk' }}
                                        <span class="text-muted">({{ $trx->details->sum('quantity') }} items)</span>
                                    </small>
                                </td>
                                <td>
                                    @if($trx->payment_method === 'cash')
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1">TUNAI</span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-1">QRIS</span>
                                    @endif
                                </td>
                                <td class="text-end text-muted small">Rp{{ number_format($trx->discount_amount, 0, ',', '.') }}</td>
                                <td class="text-end fw-extrabold text-dark">Rp{{ number_format($trx->final_amount, 0, ',', '.') }}</td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('pos.receipt', $trx->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3" title="Cetak Struk Thermal">
                                        <i class="bi bi-printer me-1"></i> Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x display-4 text-secondary opacity-50 d-block mb-2"></i>
                                    Tidak ditemukan data transaksi pada rentang filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
