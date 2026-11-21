# Smart Kasir / POS System UMKM

Aplikasi Point of Sale (POS) modern, cepat, dan intuitif untuk UMKM berbasis **Laravel 13**, **Bootstrap 5**, **Laravel Fortify**, **Spatie Laravel-Permission**, dan basis data **MySQL 8.0**.

---

## 🚀 Panduan Menjalankan di Localhost (Tanpa Docker)

### Prasyarat
- PHP 8.3+ (ekstensi: pdo_mysql, mbstring, bcmath, gd, zip, exif)
- Composer
- Node.js & npm
- MySQL 8.0+ (misal via Homebrew: `brew install mysql && brew services start mysql`)

### 1. Siapkan Basis Data MySQL

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS smart_kasir;
CREATE USER IF NOT EXISTS 'kasir_user'@'localhost' IDENTIFIED BY 'kasir_password';
GRANT ALL PRIVILEGES ON smart_kasir.* TO 'kasir_user'@'localhost';
FLUSH PRIVILEGES;"
```

---

### 2. Instalasi, Migrasi & Seeder

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
```

Secara otomatis, tabel ber-prefix `app_` (`app_users`, `app_categories`, `app_products`, `app_transactions`, `app_transaction_details`, `app_roles`, `app_permissions`, dsb.) akan dibuat di MySQL beserta data akun bawaan.

Jalankan server:

```bash
php artisan serve --port=8060
```

> Untuk pengembangan front-end dengan hot reload, jalankan juga `npm run dev` di terminal terpisah.

---

### 3. Akses Aplikasi & Akun Demo

Buka peramban di: **`http://localhost:8060`**

Gunakan salah satu akun demo berikut:

| Peran (Role) | Alamat Email | Kata Sandi | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Owner** | `owner@smartkasir.com` | `password` | Akses Penuh (Dashboard, Kasir, Produk, Laporan, Rekonsiliasi) |
| **Admin** | `admin@smartkasir.com` | `password` | Manajemen Katalog, Stok, Kasir, & Laporan Penjualan |
| **Kasir** | `kasir@smartkasir.com` | `password` | Halaman Kasir (POS) & Rekonsiliasi Shift |

---

## 🛠️ Perintah Berguna

### Menjalankan Testing
```bash
php artisan test
```

### Menghentikan Server
Tekan `Ctrl+C` di terminal tempat `php artisan serve` berjalan.
