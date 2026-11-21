<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Kasir POS UMKM' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="d-flex min-vh-100">
        <!-- Sidebar Backdrop (mobile/tablet only) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar Navigation -->
        <aside class="app-sidebar p-3 d-flex flex-column" id="appSidebar">
            <!-- Brand Logo -->
            <div class="sidebar-brand d-flex align-items-center gap-2 mb-2">
                <div class="d-flex align-items-center justify-content-center" style="background: #1d4ed8; color: #fff; border-radius: 8px; width: 40px; height: 40px;">
                    <i class="bi bi-shop fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold m-0" style="font-size: 1.05rem; color: #111827;">Smart Kasir</h5>
                    <small class="fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.06em; color: #9ca3af; text-transform: uppercase;">POS UMKM</small>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="nav flex-column flex-grow-1 overflow-y-auto py-2">
                <small class="text-uppercase fw-bold text-muted px-3 mb-2" style="font-size: 0.65rem; letter-spacing: 0.08em; color: #64748b !important;">Main Menu</small>
                
                @can('view-dashboard')
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
                </a>
                @endcan

                @can('process-transaction')
                    @php
                        $hasShift = \App\Models\Shift::where('app_user_id', auth()->id())->where('status', 'open')->exists();
                    @endphp
                    @if($hasShift)
                        <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                            <i class="bi bi-calculator-fill"></i> <span>Kasir (POS)</span>
                        </a>
                    @else
                        <a href="{{ route('shifts.open_form') }}" class="sidebar-link text-muted" title="Buka shift kasir dahulu">
                            <i class="bi bi-calculator-fill"></i> <span>Kasir (POS) - Buka Shift</span>
                        </a>
                    @endif
                @endcan

                @can('manage-shifts')
                <a href="{{ route('shifts.index') }}" class="sidebar-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> <span>Shift Kasir & Laci</span>
                </a>
                @endcan

                <small class="text-uppercase fw-bold text-muted px-3 mt-3 mb-2" style="font-size: 0.65rem; letter-spacing: 0.08em; color: #64748b !important;">Katalog & Stok</small>

                @can('manage-categories')
                <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i> <span>Kategori Barang</span>
                </a>
                @endcan

                @can('manage-products')
                <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill"></i> <span>Master Produk</span>
                </a>
                <a href="{{ route('raw-materials.index') }}" class="sidebar-link {{ request()->routeIs('raw-materials.*') ? 'active' : '' }}">
                    <i class="bi bi-droplet-half"></i> <span>Master Bahan Baku</span>
                </a>
                @can('process-transaction')
                <a href="{{ route('tickets.index', 'barista') }}" class="sidebar-link {{ request()->routeIs('tickets.*') && request()->route('station') === 'barista' ? 'active' : '' }}">
                    <i class="bi bi-cup-hot-fill"></i> <span>Tiket Barista</span>
                </a>
                <a href="{{ route('tickets.index', 'kitchen') }}" class="sidebar-link {{ request()->routeIs('tickets.*') && request()->route('station') === 'kitchen' ? 'active' : '' }}">
                    <i class="bi bi-fire"></i> <span>Tiket Kitchen</span>
                </a>
                @endcan
                <a href="{{ route('modifiers.index') }}" class="sidebar-link {{ request()->routeIs('modifiers.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i> <span>Varian & Modifier</span>
                </a>
                <a href="{{ route('tables.index') }}" class="sidebar-link {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap-fill"></i> <span>Meja</span>
                </a>
                @endcan

                @can('manage-stock-in')
                <a href="{{ route('stock-ins.index') }}" class="sidebar-link {{ request()->routeIs('stock-ins.*') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-in-down-left"></i> <span>Barang Masuk (Restock)</span>
                </a>
                @endcan

                @can('manage-adjustments')
                <a href="{{ route('adjustments.index') }}" class="sidebar-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i> <span>Stock Opname & Audit</span>
                </a>
                @endcan

                <small class="text-uppercase fw-bold text-muted px-3 mt-3 mb-2" style="font-size: 0.65rem; letter-spacing: 0.08em; color: #64748b !important;">Keuangan & Pengaturan</small>

                @can('manage-expenses')
                <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i> <span>Beban Operasional</span>
                </a>
                @endcan

                @can('view-financial-reports')
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line-fill"></i> <span>Laporan Penjualan</span>
                </a>
                <a href="{{ route('reports.reconciliation') }}" class="sidebar-link {{ request()->routeIs('reports.reconciliation') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i> <span>Rekonsiliasi Kasir</span>
                </a>
                @endcan

                @can('manage-users')
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> <span>Manajemen Pengguna</span>
                </a>
                @endcan

                @can('manage-settings')
                <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i> <span>Pengaturan Toko</span>
                </a>
                @endcan

                <!-- Logout Link in Nav -->
                <small class="text-uppercase fw-bold text-muted px-3 mt-3 mb-2" style="font-size: 0.65rem; letter-spacing: 0.08em; color: #64748b !important;">Sistem</small>
                <a href="#" class="sidebar-link text-danger hover-bg-danger js-logout-btn">
                    <i class="bi bi-box-arrow-right text-danger"></i> <span class="text-danger fw-bold">Keluar / Logout</span>
                </a>
            </nav>

            <!-- User Footer Info -->
            <div class="pt-3 mt-auto" style="border-top: 1px solid var(--border-color);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px; background: #eef2fb;">
                            <i class="bi bi-person-fill" style="color: #1d4ed8; font-size: 1.1rem;"></i>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-semibold text-truncate" style="font-size: 0.85rem; color: #111827;">{{ auth()->user()->name }}</div>
                            <span class="text-uppercase" style="font-size: 0.65rem; color: #6b7280; letter-spacing: 0.03em;">
                                {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border-0 p-1 js-logout-btn" style="color: #dc2626;" title="Keluar / Logout">
                        <i class="bi bi-power fs-5"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-grow-1 d-flex flex-column min-w-0">
            <!-- Top Navbar -->
            <header class="bg-white border-bottom px-3 px-md-4 py-3 d-flex align-items-center justify-content-between sticky-top" style="background-color: #ffffff;">
                <div class="d-flex align-items-center gap-2 gap-md-3" style="min-width: 0;">
                    <button type="button" class="sidebar-toggle-btn d-lg-none flex-shrink-0" id="sidebarToggleBtn" aria-label="Buka menu navigasi" aria-controls="appSidebar" aria-expanded="false">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <h4 class="fw-bold m-0 text-dark text-truncate" style="font-size: clamp(1rem, 4vw, 1.35rem); min-width: 0;">{{ $header ?? 'Dashboard' }}</h4>
                </div>
                <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
                    <span class="text-secondary small d-none d-xl-inline bg-light px-3 py-1.5 rounded-pill border text-nowrap">
                        <i class="bi bi-calendar3 me-1.5 text-primary"></i> {{ date('d M Y') }}
                    </span>
                    @can('process-transaction')
                        @php
                            $hasShift = \App\Models\Shift::where('app_user_id', auth()->id())->where('status', 'open')->exists();
                        @endphp
                        @if($hasShift)
                            <a href="{{ route('pos.index') }}" class="btn btn-primary btn-touch px-2.5 px-md-3.5 gap-2 shadow-sm fw-bold text-nowrap">
                                <i class="bi bi-calculator fs-5"></i> <span class="d-none d-lg-inline">Buka Kasir POS</span>
                            </a>
                        @else
                            <a href="{{ route('shifts.open_form') }}" class="btn btn-outline-primary btn-touch px-2.5 px-md-3.5 gap-2 shadow-sm fw-bold text-nowrap" title="Buka shift kasir terlebih dahulu">
                                <i class="bi bi-clock-history fs-5"></i> <span class="d-none d-lg-inline">Buka Shift</span>
                            </a>
                        @endif
                    @endcan

                    <!-- Header Profile Dropdown with Logout -->
                    <div class="dropdown">
                        <button class="btn btn-light bg-white border dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1.5 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-primary"></i>
                            <span class="fw-bold text-dark small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0 mt-2 p-2" style="min-width: 200px;">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark small">{{ auth()->user()->name }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                                <span class="badge bg-primary bg-opacity-10 text-primary mt-1 text-uppercase" style="font-size: 0.65rem;">
                                    {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                                </span>
                            </li>
                            <li>
                                <a class="dropdown-menu-item dropdown-item rounded-2 py-2 text-dark small fw-semibold {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-circle me-2 text-secondary"></i> Profil Saya
                                </a>
                            </li>
                            @can('manage-settings')
                            <li>
                                <a class="dropdown-menu-item dropdown-item rounded-2 py-2 text-dark small fw-semibold" href="{{ route('settings.index') }}">
                                    <i class="bi bi-gear me-2 text-secondary"></i> Pengaturan Toko
                                </a>
                            </li>
                            @endcan
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <button type="button" class="dropdown-item rounded-2 py-2 text-danger small fw-bold d-flex align-items-center js-logout-btn">
                                    <i class="bi bi-box-arrow-right me-2 fs-6"></i> Keluar / Logout
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="p-3 p-md-4 flex-grow-1 overflow-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Hidden Logout Form (submitted after SweetAlert confirmation) -->
    <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="d-none">
        @csrf
    </form>

    <!-- Flash Messages via SweetAlert -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'OK',
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: @json(session('error')),
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'OK',
                });
            });
        </script>
    @endif

    <!-- Logout Confirmation via SweetAlert -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-logout-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Keluar Aplikasi',
                        text: 'Apakah Anda yakin ingin keluar dari sesi akun {{ auth()->user()->name }}?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Keluar Sesi',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            document.getElementById('logoutForm').submit();
                        }
                    });
                });
            });
        });
    </script>

    <!-- Mobile/Tablet Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            if (!sidebar || !backdrop || !toggleBtn) return;

            function openSidebar() {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
                document.body.classList.add('sidebar-open');
                toggleBtn.setAttribute('aria-expanded', 'true');
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.classList.remove('sidebar-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
            }

            toggleBtn.addEventListener('click', function () {
                sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
            });

            backdrop.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('a.sidebar-link').forEach(function (link) {
                link.addEventListener('click', closeSidebar);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSidebar();
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) closeSidebar();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
