# Roadmap Pengembangan Smart Kasir untuk Kafe

Saat ini Smart Kasir masih berupa POS ritel: pilih barang → bayar → stok berkurang. Untuk kafe, ada beberapa kebutuhan inti yang belum ada. Dokumen ini merangkum fitur yang perlu ditambahkan, diurutkan berdasarkan prioritas, dengan mempertimbangkan modul yang sudah ada (produk, kategori, shift, transaksi, stok masuk, pengeluaran, setting).

**Alur kafe (dikonfirmasi):** pelanggan pesan di kasir → **bayar di depan atau bayar di akhir (open bill)** → mendapat nomor antrean → barista/kitchen membuat pesanan → **pesanan diantar ke meja**.

---

## Ringkasan Progres (per 21 Nov 2026)

| # | Fitur | Status |
|---|---|---|
| 0 | Gambar produk (form, katalog, kasir, keranjang) | ✅ Selesai |
| 1 | Varian & modifier menu | ✅ Selesai |
| 2 | Tipe pesanan, meja, nama pelanggan & nomor antrean | ✅ Selesai |
| 3 | Tiket station Barista & Kitchen | ✅ Selesai |
| 4 | Pajak (PB1) & service charge | ✅ Selesai |
| 5 | Stok bahan baku | 🟡 Master selesai, resep belum |
| 6 | Open bill & split bill | ✅ Selesai |
| 7 | Role Barista & Kitchen | ✅ Selesai |
| 8 | Void & refund dengan PIN supervisor | ✅ Selesai (transaksi lunas) |
| 9 | Laporan khusus kafe | 🟡 Inti selesai, pemakaian bahan menunggu resep |
| — | Desain ulang UI/UX bernuansa kafe | ✅ Selesai |

**Kode:** branch `feature/cafe-pos` di GitHub (belum di-merge ke `main`). Pengembangan sekarang berjalan langsung di localhost (PHP + MySQL), tanpa Docker — lihat `README.md`.

### Menjalankan setelah update
```bash
php artisan migrate
php artisan storage:link            # agar foto produk tampil
php artisan db:seed --class=ModifierSeeder    # opsional: contoh varian & modifier
php artisan db:seed --class=CafeTableSeeder   # opsional: contoh meja
npm run build
```
Atur **PIN Supervisor**, tarif pajak dan service charge di menu **Pengaturan** sebelum dipakai.

---

## Desain UI/UX "Kedai" — ✅ Selesai (21 Nov 2026)
- **Palet kafe**: latar krem susu, aksen espresso & karamel, hijau sage (berhasil), terakota (bahaya). Variabel Bootstrap ditimpa di `resources/css/app.css`, sehingga semua halaman ikut berubah.
- **Tipografi**: *Plus Jakarta Sans* untuk teks, *Fraunces* untuk judul, nama brand, dan angka total.
- **Layout**: sidebar cokelat gelap dengan menu berbahasa sehari-hari (Beranda, Kasir, Daftar Menu, Tim & Akses), header dengan sapaan sesuai jam perangkat dan avatar inisial.
- **Login**: tampilan dua kolom dengan panel suasana kafe, tombol lihat kata sandi, dan akun demo.
- **Beranda**: banner sambutan gelap berisi ringkasan transaksi & omzet hari ini.
- **Kasir**:
  - Kartu menu tanpa garis kaku; menu tanpa foto memakai ilustrasi sesuai jenis (minuman / makanan / barang umum); badge "Sisa n" / "Habis".
  - Kategori sebagai chip dengan jumlah menu, pencarian berbentuk pill, pesan "menu tidak ditemukan".
  - Panel **Pesanan** bergaya nota: tipe pesanan berupa *segmented control*, baris item dengan stepper jumlah.
  - Tombol **Bayar Nanti** dan **Bayar · Rp total** selalu terlihat; detail pembayaran (diskon, tunai/QRIS, uang cepat, kembalian) pindah ke **lembar pembayaran** — sebelumnya tombol bayar tersembunyi di bawah layar.
  - Kartu menu bisa dipilih dengan keyboard (Enter/Spasi); Enter di kolom uang langsung menyelesaikan pembayaran.

---

## Tahap 0 — Fondasi Tampilan

### 0. Gambar Produk — ✅ Selesai (21 Nov 2026)
- Upload foto (JPG/PNG/WEBP, maks. 2 MB) dengan preview langsung; opsi **hapus foto** di halaman edit.
- Foto tampil di katalog produk, kartu menu kasir, dan thumbnail keranjang; menu tanpa foto memakai ikon pengganti.
- Wajib menjalankan `php artisan storage:link` sekali di setiap server.

---

## Tahap 1 — Wajib (agar bisa dipakai di kafe)

### 1. Varian & Modifier Menu (prioritas utama) — ✅ Selesai (21 Nov 2026)
Hampir setiap minuman kafe punya pilihan. Dibutuhkan:
- **Varian** yang mengubah harga: ukuran (Regular / Large), panas / dingin.
- **Modifier pilihan**: level gula (normal / less / no sugar), level es, jenis susu (fresh milk / oat milk +Rp), shot espresso.
- **Add-on berbayar**: extra shot, whipped cream, boba, sirup.
- Aturan per grup modifier: wajib pilih satu, boleh pilih banyak, batas maksimal.
- **Catatan per item**, misalnya "es dipisah" atau "jangan terlalu manis".
- Item yang sama dengan modifier berbeda tampil sebagai baris terpisah di keranjang dan struk.

### 2. Tipe Pesanan, Meja, Nama Pelanggan & Nomor Antrean — ✅ Selesai (21 Nov 2026)
- Tipe pesanan: **Dine-in / Take Away / Online** (GoFood, GrabFood, ShopeeFood).
- Input **nama pelanggan** (untuk dipanggil / ditulis di cup) atau **nomor meja** sederhana (opsional).
- **Nomor antrean** otomatis yang reset setiap hari, dicetak di struk.
- Harga berbeda untuk pesanan online (karena potongan komisi aplikasi) — opsional.

### 3. Layar Barista / Tiket Pesanan — ✅ Selesai (21 Nov 2026)
- Setelah pembayaran, pesanan otomatis masuk ke **layar tiket** dan dipisahkan per **station**: Barista (minuman) atau Kitchen (makanan).
- Station ditentukan otomatis dari kategori: **Minuman → Barista**, **Makanan & Snack → Kitchen**; kategori lain dapat diatur manual dari Master Produk. Item bahan baku tidak masuk POS/tiket.
- Tersedia halaman Tiket Barista dan Tiket Kitchen dengan refresh manual, menampilkan nomor antrean, meja/nama, modifier, dan catatan item.
- Barista/Kitchen dapat menandai setiap item "menunggu" → "dikerjakan" → "siap", lalu nomor antrean/nama pelanggan dipanggil.
- Opsional: **layar antrean untuk pelanggan** (menampilkan nomor yang sedang dibuat & siap diambil).
- Alternatif lebih murah: **cetak tiket/stiker cup** otomatis (nama, menu, modifier) ke printer bar. Fitur printer Bluetooth sudah ada dan bisa dikembangkan.

### 4. Pajak & Service Charge — ✅ Selesai (21 Nov 2026)
- PB1 (umumnya 10%) dan service charge (opsional, 5–10%) yang dapat diatur di menu Setting, termasuk opsi harga sudah termasuk pajak atau belum.
- Tarif disimpan di Setting, dihitung server-side saat pembayaran, disimpan ke transaksi, dan dicetak di struk.

---

## Tahap 2 — Penting untuk Operasional & Profit

### 5. Stok Bahan Baku — ✅ Master selesai (21 Nov 2026)
Kafe menjual minuman racikan, bukan barang jadi. Untuk tahap ini tersedia:
- Master **bahan baku**: biji kopi, susu, sirup, cup, sedotan, dll., dengan satuan gram / ml / pcs.
- Stok masuk dan stock opname dapat mencatat bahan baku tanpa menampilkannya sebagai menu kasir.
- Resep dan pemotongan stok otomatis **belum dikerjakan**, sesuai scope saat ini.
- Dibutuhkan pada tahap lanjutan:
- **Resep per menu dan per varian**, contoh: Es Kopi Susu Large = 18 g espresso + 150 ml susu + 30 ml gula aren + 1 cup L.
- **Modifier ikut memotong stok**, misalnya extra shot = +9 g kopi, oat milk menggantikan fresh milk.
- Stok bahan berkurang otomatis saat terjual, sehingga **HPP dihitung dari resep**.
- Menu otomatis ditandai "habis" jika bahan tidak mencukupi.
- Modul stok masuk dan penyesuaian stok yang sudah ada dapat digunakan ulang untuk bahan baku.

### 6. Open Bill (bayar nanti) — ✅ Selesai (21 Nov 2026)
- Tombol **Bayar Nanti** menyimpan pesanan sebagai bill terbuka per meja (dine-in) atau per nama pelanggan (take away / online). Stok langsung berkurang dan nomor antrean keluar.
- Satu meja hanya boleh punya satu bill terbuka; memilih meja yang terisi menawarkan **tambah pesanan ke bill tersebut**.
- Tombol **Bill Terbuka** menampilkan semua bill (meja, total, lama duduk) untuk tambah pesanan, cetak bill sementara, bayar, atau batalkan (alasan wajib, stok dikembalikan).
- **Split bill**: bayar sebagian item/jumlah; sisanya tetap terbuka.
- Omzet dihitung saat **dibayar** (`paid_at`), dan uang masuk ke shift kasir yang menerima pembayaran.

### 7. Role Pengguna Baru — ✅ Selesai (21 Nov 2026)
- **Barista**: hanya mengakses layar tiket Barista.
- **Kitchen**: hanya mengakses layar tiket Kitchen.
- **Kasir**: input pesanan dan menerima pembayaran.
- Owner/Admin dapat melihat kedua station dan mengatur akun pengguna.
- Spatie Permission sudah terpasang, sehingga cukup menambahkan role dan permission baru.

### 8. Void & Refund dengan Otorisasi — ✅ Selesai (21 Nov 2026)
- Void item atau refund penuh setelah dibayar wajib menggunakan **PIN supervisor** dan dicatat alasannya.
- Stok dikembalikan otomatis; transaksi refund tidak lagi masuk laporan penjualan.

### 9. Laporan Khusus Kafe — ✅ Core selesai (21 Nov 2026)
- Menu terlaris dan modifier paling sering dipilih.
- Penjualan per jam, jumlah item/cup terjual, dan tipe pesanan dine-in/take away/online.
- Omset, jumlah transaksi, diskon, laba, dan beban tetap tersedia.
- Laporan void/refund tercatat melalui status dan alasan transaksi/item.
- Pemakaian bahan baku vs penjualan masih menunggu fitur resep.

---

## Tahap 3 — Nilai Tambah

- **Member & Loyalty**: poin per pembelian atau stempel digital ("beli 10 gratis 1") — sangat umum di kafe.
- **Promo**: happy hour, paket bundling (kopi + pastry), voucher, diskon member.
- **QR Order di Meja / Self Order**: pelanggan scan QR, melihat menu bergambar (memanfaatkan fitur gambar produk), memilih modifier, lalu memesan sendiri.
- **Integrasi QRIS dinamis**: saat ini masih menggunakan QR statis sebagai placeholder.
- **Customer display**: layar kedua yang menampilkan pesanan & total ke pelanggan saat kasir input.

---

## Urutan Pengerjaan

| Urutan | Fitur | Status |
|---|---|---|
| 1 | Varian & modifier (#1) | ✅ |
| 2 | Tipe pesanan, meja, nama & nomor antrean (#2) | ✅ |
| 3 | Open bill per meja & split bill (#6) — dinaikkan karena kafe melayani bayar di depan *dan* di akhir | ✅ |
| 4 | Pajak & service charge (#4) | ✅ |
| 5 | Tiket station Barista & Kitchen (#3) + role (#7) | ✅ |
| 6 | Void & refund (#8), laporan kafe (#9) | ✅ |
| 7 | Desain ulang UI/UX bernuansa kafe | ✅ |
| 8 | Resep & pemakaian bahan baku (#5 lanjutan) | ⏳ |
| 9 | Hapus/void item pada bill terbuka dengan PIN | ⏳ |

---

## Batasan yang Masih Ada

- **Item di bill terbuka belum bisa dihapus/diubah satu per satu.** Jika salah input, saat ini harus membatalkan seluruh bill. Void dengan PIN baru berlaku untuk transaksi yang sudah lunas (dari halaman Laporan).
- **Membatalkan bill terbuka belum memerlukan PIN supervisor**, hanya alasan yang tercatat.
- **Stok masih dihitung per menu**, belum dari resep bahan baku.
- **QRIS masih statis** (placeholder), belum terhubung ke payment gateway.
- Tiket station memakai **refresh manual**, belum otomatis/real-time.
- **Timezone aplikasi masih UTC.** Laporan "hari ini" dan tanggal transaksi dihitung dengan jam UTC, sehingga penjualan pukul 00.00–07.00 WIB tercatat di tanggal sebelumnya. Perlu diputuskan sebelum dipakai produksi (ubah ke `Asia/Jakarta` + sesuaikan data lama).

---

## Keputusan yang Sudah Diambil

1. **Alur bayar:** keduanya — bayar di depan dan bayar di akhir (open bill).
2. **Penyajian:** pesanan **diantar ke meja**, sehingga dine-in wajib memilih meja.
3. **Menu makanan:** ada; tiket dipisah antara station Barista dan Kitchen.
4. **Outlet:** belum diputuskan — jika ada rencana multi-cabang, struktur data perlu disiapkan sebelum fitur berikutnya.
