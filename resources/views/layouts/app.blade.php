<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Kasir POS UMKM' }}</title>
    
    @include('partials.head-assets')
</head>
<body class="bg-light">
    @php
        $authUser = auth()->user();
        $roleName = $authUser->getRoleNames()->first() ?? 'user';
        $initials = collect(preg_split('/\s+/', trim(preg_replace('/\(.*?\)/', '', $authUser->name))))
            ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
        $hasShift = $authUser->can('process-transaction')
            && \App\Models\Shift::where('app_user_id', $authUser->id)->where('status', 'open')->exists();
    @endphp
    <div class="d-flex min-vh-100">
        <!-- Sidebar Backdrop (mobile/tablet only) -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar Navigation -->
        <aside class="app-sidebar p-3 d-flex flex-column" id="appSidebar">
            <div class="sidebar-brand d-flex align-items-center gap-2">
                <span class="brand-mark"><i class="bi bi-cup-hot-fill"></i></span>
                <div class="min-w-0">
                    <div class="brand-name">{{ \App\Models\Setting::get('store_name', 'Smart Kasir') }}</div>
                    <div class="brand-tag">Café POS</div>
                </div>
            </div>

            <nav class="sidebar-nav nav flex-column flex-grow-1 overflow-y-auto py-1">
                <span class="sidebar-section">Harian</span>

                @can('view-dashboard')
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-heart"></i> <span>Beranda</span>
                </a>
                @endcan

                @can('process-transaction')
                    @if($hasShift)
                        <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                            <i class="bi bi-cup-straw"></i> <span>Kasir</span>
                        </a>
                    @else
                        <a href="{{ route('shifts.open_form') }}" class="sidebar-link is-disabled" title="Buka shift kasir dahulu">
                            <i class="bi bi-cup-straw"></i> <span>Kasir <small class="opacity-75">· buka shift</small></span>
                        </a>
                    @endif
                @endcan

                @can('view-barista-tickets')
                <a href="{{ route('tickets.index', 'barista') }}" class="sidebar-link {{ request()->routeIs('tickets.*') && request()->route('station') === 'barista' ? 'active' : '' }}">
                    <i class="bi bi-cup-hot"></i> <span>Tiket Barista</span>
                </a>
                @endcan
                @can('view-kitchen-tickets')
                <a href="{{ route('tickets.index', 'kitchen') }}" class="sidebar-link {{ request()->routeIs('tickets.*') && request()->route('station') === 'kitchen' ? 'active' : '' }}">
                    <i class="bi bi-fire"></i> <span>Tiket Kitchen</span>
                </a>
                @endcan

                @can('manage-shifts')
                <a href="{{ route('shifts.index') }}" class="sidebar-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> <span>Shift & Laci</span>
                </a>
                @endcan

                @canany(['manage-categories', 'manage-products', 'manage-stock-in', 'manage-adjustments'])
                <span class="sidebar-section">Menu & Stok</span>
                @endcanany

                @can('manage-products')
                <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-richtext"></i> <span>Daftar Menu</span>
                </a>
                <a href="{{ route('modifiers.index') }}" class="sidebar-link {{ request()->routeIs('modifiers.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders2"></i> <span>Varian & Modifier</span>
                </a>
                @endcan

                @can('manage-categories')
                <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> <span>Kategori</span>
                </a>
                @endcan

                @can('manage-products')
                <a href="{{ route('tables.index') }}" class="sidebar-link {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i> <span>Meja</span>
                </a>
                <a href="{{ route('raw-materials.index') }}" class="sidebar-link {{ request()->routeIs('raw-materials.*') ? 'active' : '' }}">
                    <i class="bi bi-droplet-half"></i> <span>Bahan Baku</span>
                </a>
                @endcan

                @can('manage-stock-in')
                <a href="{{ route('stock-ins.index') }}" class="sidebar-link {{ request()->routeIs('stock-ins.*') ? 'active' : '' }}">
                    <i class="bi bi-box-arrow-in-down"></i> <span>Barang Masuk</span>
                </a>
                @endcan

                @can('manage-adjustments')
                <a href="{{ route('adjustments.index') }}" class="sidebar-link {{ request()->routeIs('adjustments.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i> <span>Stock Opname</span>
                </a>
                @endcan

                @canany(['manage-expenses', 'view-financial-reports', 'manage-users', 'manage-settings'])
                <span class="sidebar-section">Keuangan & Toko</span>
                @endcanany

                @can('view-financial-reports')
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> <span>Laporan Penjualan</span>
                </a>
                <a href="{{ route('reports.reconciliation') }}" class="sidebar-link {{ request()->routeIs('reports.reconciliation') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i> <span>Rekonsiliasi Kasir</span>
                </a>
                @endcan

                @can('manage-expenses')
                <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i> <span>Pengeluaran</span>
                </a>
                @endcan

                @can('manage-users')
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> <span>Tim & Akses</span>
                </a>
                @endcan

                @can('manage-settings')
                <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> <span>Pengaturan</span>
                </a>
                @endcan
            </nav>

            <div class="sidebar-user d-flex align-items-center justify-content-between gap-2">
                <a href="{{ route('profile.edit') }}" class="d-flex align-items-center gap-2 min-w-0 text-decoration-none">
                    <span class="avatar">{{ $initials ?: 'U' }}</span>
                    <span class="min-w-0">
                        <span class="d-block fw-semibold text-truncate" style="font-size: .85rem; color: #fff6ea;">{{ $authUser->name }}</span>
                        <span class="d-block text-capitalize" style="font-size: .7rem; color: var(--sidebar-muted);">{{ $roleName }}</span>
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-1 border-0 js-logout-btn" style="color: #f0b3a8;" title="Keluar" aria-label="Keluar">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-grow-1 d-flex flex-column min-w-0">
            <header class="app-topbar px-3 px-md-4 py-3 d-flex align-items-center justify-content-between gap-3 sticky-top">
                <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0">
                    <button type="button" class="sidebar-toggle-btn d-lg-none flex-shrink-0" id="sidebarToggleBtn" aria-label="Buka menu navigasi" aria-controls="appSidebar" aria-expanded="false">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div class="min-w-0">
                        <div class="topbar-greeting d-none d-sm-block"><span class="js-greeting">Halo</span>, {{ \Illuminate\Support\Str::of($authUser->name)->before(' (')->before(' ') }} ☕</div>
                        <h1 class="topbar-title text-truncate">{{ $header ?? 'Beranda' }}</h1>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="chip d-none d-xl-inline-flex"><i class="bi bi-calendar3"></i> <span class="js-today">{{ now()->translatedFormat('d M Y') }}</span></span>
                    @can('process-transaction')
                        @if($hasShift)
                            <a href="{{ route('pos.index') }}" class="btn btn-primary btn-touch gap-2 text-nowrap">
                                <i class="bi bi-cup-straw"></i> <span class="d-none d-md-inline">Buka Kasir</span>
                            </a>
                        @else
                            <a href="{{ route('shifts.open_form') }}" class="btn btn-outline-primary btn-touch gap-2 text-nowrap" title="Buka shift kasir terlebih dahulu">
                                <i class="bi bi-clock-history"></i> <span class="d-none d-md-inline">Buka Shift</span>
                            </a>
                        @endif
                    @endcan

                    <div class="dropdown">
                        <button class="user-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun">
                            <span class="avatar">{{ $initials ?: 'U' }}</span>
                            <span class="fw-semibold small d-none d-sm-inline text-truncate" style="max-width: 140px;">{{ \Illuminate\Support\Str::of($authUser->name)->before(' (') }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end mt-2 p-2" style="min-width: 220px;">
                            <li class="px-2 py-2 mb-1 border-bottom">
                                <div class="fw-bold small">{{ $authUser->name }}</div>
                                <div class="text-muted" style="font-size: .75rem;">{{ $authUser->email }}</div>
                                <span class="badge bg-primary-subtle text-primary-emphasis mt-1 text-capitalize">{{ $roleName }}</span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 small fw-semibold {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i> Profil Saya
                                </a>
                            </li>
                            @can('manage-settings')
                            <li>
                                <a class="dropdown-item py-2 small fw-semibold" href="{{ route('settings.index') }}">
                                    <i class="bi bi-gear me-2"></i> Pengaturan Toko
                                </a>
                            </li>
                            @endcan
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-danger small fw-bold js-logout-btn">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
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
                    confirmButtonColor: '#8b5a3c',
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
                    confirmButtonColor: '#8b5a3c',
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
                        title: 'Keluar dari akun?',
                        text: 'Sesi {{ auth()->user()->name }} akan diakhiri.',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, keluar',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#b84a3a',
                        cancelButtonColor: '#8c7765',
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

    <!-- Greeting from the device clock (server runs in UTC) -->
    <script>
        (function () {
            const h = new Date().getHours();
            const text = h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 18 ? 'Selamat sore' : 'Selamat malam';
            document.querySelectorAll('.js-greeting').forEach(el => el.textContent = text);
            const today = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            document.querySelectorAll('.js-today').forEach(el => el.textContent = today);
        })();
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
