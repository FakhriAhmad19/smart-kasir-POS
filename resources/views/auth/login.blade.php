<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk · {{ \App\Models\Setting::get('store_name', 'Smart Kasir') }}</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="auth-shell">
        <!-- Brand / mood panel -->
        <section class="auth-visual">
            <span class="ring" style="width: 420px; height: 420px; right: -140px; top: -120px;"></span>
            <span class="ring" style="width: 260px; height: 260px; right: -40px; top: -20px;"></span>
            <span class="ring" style="width: 520px; height: 520px; left: -260px; bottom: -300px;"></span>

            <div class="d-flex align-items-center gap-2 position-relative">
                <span class="brand-mark"><i class="bi bi-cup-hot-fill"></i></span>
                <div>
                    <div class="brand-name">{{ \App\Models\Setting::get('store_name', 'Smart Kasir') }}</div>
                    <div class="brand-tag" style="color: rgba(255,246,234,.6);">Café POS</div>
                </div>
            </div>

            <div class="position-relative my-4 my-lg-0" style="max-width: 460px;">
                <h1 class="mb-3">Seduh pesanan,<br>bukan kerumitan.</h1>
                <p class="mb-0 auth-extra">Kasir, open bill per meja, tiket barista &amp; kitchen, sampai laporan harian — semuanya di satu tempat yang hangat.</p>
            </div>

            <div class="d-grid gap-3 position-relative auth-extra">
                <div class="auth-feature"><i class="bi bi-cup-straw"></i> Varian &amp; modifier: ukuran, gula, topping</div>
                <div class="auth-feature"><i class="bi bi-receipt"></i> Bayar di depan atau open bill per meja</div>
                <div class="auth-feature"><i class="bi bi-fire"></i> Tiket otomatis ke barista &amp; kitchen</div>
            </div>
        </section>

        <!-- Form -->
        <section class="auth-form-wrap">
            <div class="auth-card">
                <div class="mb-4">
                    <p class="text-muted fw-semibold small mb-1 js-greeting">Selamat datang kembali</p>
                    <h2 class="fw-semibold mb-1" style="font-size: 2rem;">Masuk ke kasir</h2>
                    <p class="text-secondary small mb-0">Gunakan akun yang diberikan pemilik kafe.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger small mb-4">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}@if(!$loop->last)<br>@endif
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@kafe.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Kata sandi</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                            <button type="button" class="btn btn-sm border-0 position-absolute top-50 end-0 translate-middle-y me-1 text-muted" id="togglePassword" aria-label="Tampilkan kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-secondary" for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-touch w-100 fw-bold" style="height: 50px;">
                        Masuk <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top">
                    <small class="text-muted d-block mb-2 fw-semibold">Akun demo</small>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="demo-chip" onclick="fillCreds('owner@smartkasir.com')"><i class="bi bi-person-badge me-1"></i>Owner</button>
                        <button type="button" class="demo-chip" onclick="fillCreds('admin@smartkasir.com')"><i class="bi bi-person-gear me-1"></i>Admin</button>
                        <button type="button" class="demo-chip" onclick="fillCreds('kasir@smartkasir.com')"><i class="bi bi-cup-straw me-1"></i>Kasir</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        function fillCreds(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
        (function () {
            const h = new Date().getHours();
            const el = document.querySelector('.js-greeting');
            if (el) el.textContent = (h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 18 ? 'Selamat sore' : 'Selamat malam') + ' 👋';
            const btn = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            btn.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        })();
    </script>
</body>
</html>
