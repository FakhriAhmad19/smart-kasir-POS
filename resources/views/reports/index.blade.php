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

    <!-- Cafe Breakdown -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-4">
            <div class="card card-glass border-0 h-100 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-cup-hot me-2 text-primary"></i>Operasional Kafe</h6>
                <div class="d-flex justify-content-between border-bottom py-2"><span class="text-muted">Cup/item terjual</span><strong>{{ number_format($cafeBreakdown['cupsSold'], 0, ',', '.') }}</strong></div>
                <h6 class="fw-bold mt-4 mb-2">Tipe Pesanan</h6>
                @forelse($cafeBreakdown['orderTypes'] as $type)
                    <div class="d-flex justify-content-between small py-1"><span>{{ $type['label'] }} ({{ $type['transactions'] }} trx)</span><strong>Rp{{ number_format($type['revenue'], 0, ',', '.') }}</strong></div>
                @empty <small class="text-muted">Belum ada data.</small> @endforelse
            </div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card card-glass border-0 h-100 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Menu Terlaris</h6>
                @forelse($cafeBreakdown['topProducts'] as $name => $product)
                    <div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-truncate me-2">{{ $name }}</span><strong>{{ $product['quantity'] }} item</strong></div>
                @empty <small class="text-muted">Belum ada data.</small> @endforelse
            </div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card card-glass border-0 h-100 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-sliders me-2 text-info"></i>Modifier Terpilih</h6>
                @forelse($cafeBreakdown['topModifiers'] as $modifier => $count)
                    <div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-truncate me-2">{{ $modifier }}</span><strong>{{ $count }}×</strong></div>
                @empty <small class="text-muted">Belum ada modifier.</small> @endforelse
            </div></div>
        </div>
    </div>

    <div class="card card-glass border-0 shadow-sm mb-4"><div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Penjualan per Jam</h6>
        <div class="d-flex flex-wrap gap-2">
            @forelse($cafeBreakdown['hourlySales'] as $hour => $data)
                <div class="bg-light border rounded-3 px-3 py-2"><div class="fw-bold">{{ $hour }}</div><small class="text-muted">{{ $data['transactions'] }} trx · Rp{{ number_format($data['revenue'], 0, ',', '.') }}</small></div>
            @empty <span class="text-muted small">Belum ada transaksi pada periode ini.</span> @endforelse
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
                                <td class="ps-4">
                                    <div class="fw-bold text-primary font-monospace text-ellipsis">{{ $trx->invoice_number }}</div>
                                    <small class="text-muted text-ellipsis">
                                        @if($trx->queue_number)#{{ $trx->queue_number }} · @endif{{ $trx->orderTypeLabel() }}{{ $trx->table_name ? ' · ' . $trx->table_name : '' }}{{ $trx->online_platform ? ' · ' . $trx->online_platform : '' }}{{ $trx->customer_name ? ' · ' . $trx->customer_name : '' }}
                                    </small>
                                </td>
                                <td class="text-muted small">{{ ($trx->paid_at ?? $trx->created_at)->format('d/m/Y H:i:s') }}</td>
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
                                    @can('manage-refunds')
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill py-1 px-3 mt-1" data-bs-toggle="modal" data-bs-target="#refundModal{{ $trx->id }}">Refund</button>
                                    @endcan
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

    @can('manage-refunds')
    @foreach($transactions as $trx)
        <div class="modal fade" id="refundModal{{ $trx->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg"><div class="modal-content rounded-4 border-0 shadow"><form method="POST" action="{{ route('transactions.refund', $trx) }}">@csrf
                <div class="modal-header"><h5 class="modal-title fw-bold">Void / Refund {{ $trx->invoice_number }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-warning small">Refund penuh mengembalikan seluruh stok. Untuk void sebagian, gunakan tombol pada item.</div>
                    @foreach($trx->details as $detail)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2"><span>{{ $detail->quantity }}× {{ $detail->product->name ?? 'Produk' }}</span><button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#voidModal{{ $detail->id }}">Void item</button></div>
                    @endforeach
                    <label class="form-label mt-3">Alasan refund *</label><input name="reason" class="form-control" required minlength="3" maxlength="255">
                    <label class="form-label mt-3">PIN Supervisor *</label><input name="supervisor_pin" type="password" inputmode="numeric" class="form-control" required>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Konfirmasi Refund Penuh</button></div>
            </form></div></div>
        </div>
        @foreach($trx->details as $detail)
            <div class="modal fade" id="voidModal{{ $detail->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content rounded-4 border-0 shadow"><form method="POST" action="{{ route('transaction-details.void', $detail) }}">@csrf
                <div class="modal-header"><h5 class="modal-title fw-bold">Void {{ $detail->product->name ?? 'Item' }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><label class="form-label">Jumlah (maks. {{ $detail->quantity }})</label><input type="number" name="quantity" class="form-control" min="1" max="{{ $detail->quantity }}" value="1" required><label class="form-label mt-3">Alasan *</label><input name="reason" class="form-control" required minlength="3"><label class="form-label mt-3">PIN Supervisor *</label><input name="supervisor_pin" type="password" inputmode="numeric" class="form-control" required></div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning">Konfirmasi Void</button></div>
            </form></div></div></div>
        @endforeach
    @endforeach
    @endcan
</x-app-layout>
