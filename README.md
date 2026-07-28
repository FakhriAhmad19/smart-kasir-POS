# Smart Kasir / POS System UMKM

Aplikasi Point of Sale (POS) modern, cepat, dan intuitif untuk UMKM berbasis **Laravel 13**, **Bootstrap 5**, **Laravel Fortify**, **Spatie Laravel-Permission**, dan basis data **MySQL 8.0**.

---

## 🚀 Panduan Memulai dengan Docker & MySQL

Aplikasi telah dilengkapi dengan konfigurasikasi **Docker Compose** lengkap (PHP 8.3-FPM, Nginx, dan MySQL 8.0).

### 1. Menjalankan Container Docker

Jalankan perintah berikut di terminal:

```bash
docker-compose up -d --build
```

Ini akan menyalakan 3 service container:
- `smart-kasir-app` (PHP 8.3-FPM)
- `smart-kasir-webserver` (Nginx di Port `8060`)
- `smart-kasir-db` (MySQL 8.0 di Port `3306`)

---

### 2. Jalankan Migrasi Basis Data & Seeder (Di Dalam Docker)

Setelah container berjalan, jalankan migrasi dan seeder `app_` di dalam container `smart-kasir-app`:

```bash
docker-compose exec app php artisan migrate:fresh --seed
```

Secara otomatis, tabel ber-prefix `app_` (`app_users`, `app_categories`, `app_products`, `app_transactions`, `app_transaction_details`, `app_roles`, `app_permissions`, dsb.) akan dibuat di MySQL beserta data akun bawaan.

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
docker-compose exec app php artisan test
```

### Menghentikan Container Docker
```bash
docker-compose down
```
