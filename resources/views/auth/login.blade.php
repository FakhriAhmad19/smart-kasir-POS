<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Kasir POS UMKM</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js'])

    <style>
        body {
            background: #f6f7f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, 0.08), 0 1px 2px rgba(16, 24, 40, 0.04);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="login-card mx-auto p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; background: #1d4ed8; border-radius: 10px;">
                    <i class="bi bi-shop fs-3"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Smart Kasir UMKM</h3>
                <p class="text-muted small">Silakan masuk ke akun kasir / pengelola Anda</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger rounded-3 border-0 small mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-secondary">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="email@contoh.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small text-secondary">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Ingat saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-touch w-100 py-2.5 fw-bold shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                </button>
            </form>

            <div class="border-top pt-3 text-center">
                <small class="text-muted d-block mb-2 fw-semibold">Demo Akses Cepat:</small>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="fillCreds('owner@smartkasir.com')">Owner</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="fillCreds('admin@smartkasir.com')">Admin</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="fillCreds('kasir@smartkasir.com')">Kasir</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>
