<x-app-layout>
    <x-slot name="title">Beranda - Smart Kasir</x-slot>
    <x-slot name="header">Ringkasan Hari Ini</x-slot>

    <!-- Welcome Banner -->
    @php
        $hasShift = auth()->user()->can('process-transaction')
            && \App\Models\Shift::where('app_user_id', auth()->id())->where('status', 'open')->exists();
    @endphp
    <div class="hero-cafe p-4 p-md-5 mb-4">
        <div class="position-relative" style="max-width: 640px; z-index: 1;">
            <span class="chip mb-3" style="background: rgba(245, 248, 255,.1); border-color: rgba(245, 248, 255,.18); color: #bfdbfe;">
                <i class="bi bi-cup-hot-fill"></i> {{ \App\Models\Setting::get('store_name', 'Smart Kasir') }}
            </span>
            <h2 class="mb-2" style="font-size: clamp(1.5rem, 3vw, 2.1rem);">
                <span class="js-greeting">Halo</span>, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' (') }}!
            </h2>
            <p class="text-muted mb-4">
                Hari ini sudah <b style="color:#f5f8ff">{{ $todayTransactionsCount }} transaksi</b> dengan omzet
                <b style="color:#f5f8ff">Rp{{ number_format($todayRevenue, 0, ',', '.') }}</b>. Semoga kedainya ramai ☕
            </p>
            <div class="d-flex flex-wrap gap-2">
                @can('process-transaction')
                    @if ($hasShift)
                        <a href="{{ route('pos.index') }}" class="btn btn-warning btn-touch px-3 gap-2">
                            <i class="bi bi-cup-straw"></i> Mulai Melayani
                        </a>
                    @else
                        <a href="{{ route('shifts.open_form') }}" class="btn btn-warning btn-touch px-3 gap-2">
                            <i class="bi bi-clock-history"></i> Buka Shift
                        </a>
                    @endif
                @endcan
                @can('manage-shifts')
                    <a href="{{ route('shifts.index') }}" class="btn btn-touch px-3 gap-2" style="background: rgba(245, 248, 255,.1); color: #f5f8ff; border: 1px solid rgba(245, 248, 255,.2);">
                        <i class="bi bi-clock-history"></i> Shift & Laci
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Top Key Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat bg-white p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem; letter-spacing: 0.06em;">Omset Hari Ini</span>
                        <h3 class="fw-extrabold text-dark m-0 tracking-tight">
                            Rp{{ number_format($todayRevenue, 0, ',', '.') }}</h3>
                    </div>
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 54px; height: 54px; background: var(--accent-100); color: var(--brand-600);">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                </div>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                    <span>Total Trx: <strong class="text-dark">{{ $todayTransactionsCount }} Trx</strong></span>
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill"><i
                            class="bi bi-arrow-up-right me-1"></i> Realtime</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat bg-white p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem; letter-spacing: 0.06em;">Estimasi Laba Bersih</span>
                        <h3
                            class="fw-extrabold {{ $todayNetProfit >= 0 ? 'text-success' : 'text-danger' }} m-0 tracking-tight">
                            Rp{{ number_format($todayNetProfit, 0, ',', '.') }}</h3>
                    </div>
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 54px; height: 54px; background: #e6e8ff; color: #4338ca;">
                        <i class="bi bi-graph-up-arrow fs-3"></i>
                    </div>
                </div>
                <div class="pt-2 border-top d-flex justify-content-between text-muted small">
                    <span>Kotor: <strong
                            class="text-dark">Rp{{ number_format($todayGrossProfit, 0, ',', '.') }}</strong></span>
                    <span>Beban: <strong
                            class="text-danger">Rp{{ number_format($todayExpenses, 0, ',', '.') }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat bg-white p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem; letter-spacing: 0.06em;">Penerimaan Tunai</span>
                        <h3 class="fw-extrabold text-dark m-0 tracking-tight">
                            Rp{{ number_format($todayCashRevenue, 0, ',', '.') }}</h3>
                    </div>
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 54px; height: 54px; background: #dbeafe; color: #1d4ed8;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                </div>
                <div class="pt-2 border-top text-muted small">
                    Uang fisik wajib di laci kasir
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-stat bg-white p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem; letter-spacing: 0.06em;">Penerimaan QRIS</span>
                        <h3 class="fw-extrabold text-info m-0 tracking-tight">
                            Rp{{ number_format($todayQrisRevenue, 0, ',', '.') }}</h3>
                    </div>
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center shadow-sm"
                        style="width: 54px; height: 54px; background: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-qr-code-scan fs-3"></i>
                    </div>
                </div>
                <div class="pt-2 border-top text-muted small">
                    Transfer masuk rekening bank
                </div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Section -->
    <div class="row g-4 mb-4">
        <!-- Low Stock Alert Panel -->
        <div class="col-lg-6">
            <div class="card card-glass border-0 h-100 shadow-sm">
                <div
                    class="card-header bg-white border-0 py-3.5 px-4 d-flex justify-content-between align-items-center border-bottom border-light">
                    <h6 class="fw-bold mb-0 text-danger d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i> Peringatan Stok Menipis
                        ({{ $lowStockProducts->count() }})
                    </h6>
                    <a href="{{ route('products.index', ['low_stock' => 1]) }}"
                        class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase fw-bold text-secondary"
                                style="font-size: 0.725rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4">Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Sisa Stok</th>
                                    <th class="text-center pe-4">Aksi Restock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts->take(5) as $product)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark fs-6 text-ellipsis">{{ $product->name }}
                                            </div>
                                            <small class="text-muted font-monospace text-ellipsis">SKU:
                                                {{ $product->sku }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1">
                                                {{ $product->category->name }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fw-extrabold fs-6 px-3 py-1.5 rounded-pill">
                                                {{ $product->stock }} pcs
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('stock-ins.create') }}"
                                                class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3 fw-semibold"
                                                title="Tambah Stok Supplier">
                                                + Restock
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-success fw-semibold">
                                            <i
                                                class="bi bi-shield-check fs-1 d-block mb-2 text-success opacity-75"></i>
                                            Semua stok produk toko dalam kondisi aman.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Products Panel -->
        <div class="col-lg-6">
            <div class="card card-glass border-0 h-100 shadow-sm">
                <div
                    class="card-header bg-white border-0 py-3.5 px-4 d-flex justify-content-between align-items-center border-bottom border-light">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill text-primary fs-5"></i> Top 5 Produk Terlaris (Best Seller)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-1 rounded-pill fw-semibold">Bulan
                        Ini</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase fw-bold text-secondary"
                                style="font-size: 0.725rem; letter-spacing: 0.05em;">
                                <tr>
                                    <th class="ps-4" width="8%">Rank</th>
                                    <th width="45%">Produk</th>
                                    <th class="text-center" width="20%">Terjual</th>
                                    <th class="text-end pe-4" width="27%">Total Omset</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSellingProducts as $index => $item)
                                    <tr>
                                        <td class="ps-4">
                                            @if ($index == 0)
                                                <span
                                                    class="badge bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                    style="width: 28px; height: 28px;">🥇</span>
                                            @elseif($index == 1)
                                                <span
                                                    class="badge bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                    style="width: 28px; height: 28px;">🥈</span>
                                            @elseif($index == 2)
                                                <span
                                                    class="badge bg-danger bg-opacity-75 text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold shadow-sm"
                                                    style="width: 28px; height: 28px;">🥉</span>
                                            @else
                                                <span
                                                    class="badge bg-light text-muted border rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                                                    style="width: 28px; height: 28px;">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6 text-ellipsis">
                                                {{ $item->product->name ?? 'Produk' }}</div>
                                            <small
                                                class="text-muted text-ellipsis">{{ $item->product->category->name ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-bold">
                                                {{ $item->total_qty }} pcs
                                            </span>
                                        </td>
                                        <td class="text-end pe-4 fw-extrabold text-primary">
                                            Rp{{ number_format($item->total_sales, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-bar-chart fs-1 d-block mb-2 opacity-50"></i>
                                            Belum ada data penjualan produk terlaris.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table Panel -->
    <div class="card card-glass border-0 shadow-sm">
        <div
            class="card-header bg-white border-0 py-3.5 px-4 d-flex justify-content-between align-items-center border-bottom border-light">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary fs-5"></i> Riwayat Transaksi Terakhir
            </h6>
            <a href="{{ route('reports.index') }}"
                class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">Lihat Semua Laporan</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fw-bold text-secondary"
                        style="font-size: 0.725rem; letter-spacing: 0.05em;">
                        <tr>
                            <th class="ps-4" width="18%">No Invoice</th>
                            <th width="15%">Waktu Transaksi</th>
                            <th width="15%">Kasir</th>
                            <th width="15%">Metode Bayar</th>
                            <th class="text-end" width="22%">Total Belanja</th>
                            <th class="text-center pe-4" width="15%">Cetak Struk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $trx)
                            <tr>
                                <td class="ps-4 fw-bold text-primary font-monospace fs-6 text-ellipsis">
                                    {{ $trx->invoice_number }}</td>
                                <td class="text-muted small">{{ ($trx->paid_at ?? $trx->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="fw-semibold text-dark">{{ $trx->user->name ?? 'Kasir' }}</td>
                                <td>
                                    @if ($trx->payment_method === 'cash')
                                        <span
                                            class="badge bg-primary-subtle text-primary-emphasis px-3 py-1 rounded-pill fw-bold">TUNAI</span>
                                    @else
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">QRIS</span>
                                    @endif
                                </td>
                                <td class="text-end fw-extrabold text-dark fs-6">
                                    Rp{{ number_format($trx->final_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('pos.receipt', $trx->id) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3 fw-semibold"
                                        title="Cetak Struk Thermal">
                                        <i class="bi bi-printer me-1"></i> Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i
                                        class="bi bi-receipt-cutoff display-4 text-secondary opacity-50 d-block mb-2"></i>
                                    Belum ada transaksi penjualan tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
