# Roadmap Pengembangan Smart Kasir untuk Kafe

Saat ini Smart Kasir masih berupa POS ritel: pilih barang → bayar → stok berkurang. Untuk kafe, ada beberapa kebutuhan inti yang belum ada. Dokumen ini merangkum fitur yang perlu ditambahkan, diurutkan berdasarkan prioritas, dengan mempertimbangkan modul yang sudah ada (produk, kategori, shift, transaksi, stok masuk, pengeluaran, setting).

**Alur kafe (dikonfirmasi):** pelanggan pesan di kasir → **bayar di depan atau bayar di akhir (open bill)** → mendapat nomor antrean → barista membuat pesanan → **pesanan diantar ke meja**.

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

### 3. Layar Barista / Tiket Pesanan
- Setelah pembayaran, pesanan otomatis masuk ke **layar barista** (tablet di bar), dipisahkan per **station** (bar minuman / dapur makanan).
- Barista menandai pesanan "sedang dibuat" → "siap", lalu nomor antrean/nama pelanggan dipanggil.
- Opsional: **layar antrean untuk pelanggan** (menampilkan nomor yang sedang dibuat & siap diambil).
- Alternatif lebih murah: **cetak tiket/stiker cup** otomatis (nama, menu, modifier) ke printer bar. Fitur printer Bluetooth sudah ada dan bisa dikembangkan.

### 4. Pajak & Service Charge
- PB1 (umumnya 10%) dan service charge (opsional, 5–10%) yang dapat diatur di menu Setting, termasuk opsi harga sudah termasuk pajak atau belum.
- Kolom `tax_amount` sudah tersedia di tabel transaksi, namun saat ini selalu bernilai 0.

---

## Tahap 2 — Penting untuk Operasional & Profit

### 5. Resep & Stok Bahan Baku
Kafe menjual minuman racikan, bukan barang jadi. Dibutuhkan:
- Master **bahan baku**: biji kopi, susu, sirup, cup, sedotan, dll., dengan satuan gram / ml / pcs.
- **Resep per menu dan per varian**, contoh: Es Kopi Susu Large = 18 g espresso + 150 ml susu + 30 ml gula aren + 1 cup L.
- **Modifier ikut memotong stok**, misalnya extra shot = +9 g kopi, oat milk menggantikan fresh milk.
- Stok bahan berkurang otomatis saat terjual, sehingga **HPP dihitung dari resep**.
- Menu otomatis ditandai "habis" jika bahan tidak mencukupi.
- Modul stok masuk dan penyesuaian stok yang sudah ada dapat digunakan ulang untuk bahan baku.

### 6. Open Bill (bayar nanti) — ✅ Selesai (21 Nov 2026)
Tidak sepenting di rumah makan, tapi berguna untuk pelanggan yang duduk lama:
- Simpan pesanan sebagai bill terbuka per meja / nama, lalu tambah pesanan susulan.
- Bayar di akhir, termasuk **split bill** sederhana.
- **Hold order**: simpan sementara pesanan yang belum selesai, lalu lanjutkan melayani pelanggan berikutnya.

### 7. Role Pengguna Baru
- **Barista**: hanya mengakses layar barista.
- **Kasir**: input pesanan dan menerima pembayaran.
- Spatie Permission sudah terpasang, sehingga cukup menambahkan role dan permission baru.

### 8. Void & Refund dengan Otorisasi
- Pembatalan item / refund setelah dibayar wajib menggunakan **PIN supervisor** dan dicatat alasannya, untuk mencegah kecurangan.

### 9. Laporan Khusus Kafe
- Menu terlaris dan modifier paling sering dipilih (mis. % pelanggan pilih less sugar / oat milk).
- Penjualan per jam (analisis jam sibuk: pagi, makan siang, sore).
- Rata-rata nilai transaksi dan jumlah cup terjual per hari.
- Perbandingan dine-in vs take away vs online.
- Pemakaian bahan baku vs penjualan (deteksi selisih/pemborosan).
- Laporan void, refund, dan diskon.

---

## Tahap 3 — Nilai Tambah

- **Member & Loyalty**: poin per pembelian atau stempel digital ("beli 10 gratis 1") — sangat umum di kafe.
- **Promo**: happy hour, paket bundling (kopi + pastry), voucher, diskon member.
- **QR Order di Meja / Self Order**: pelanggan scan QR, melihat menu bergambar (memanfaatkan fitur gambar produk), memilih modifier, lalu memesan sendiri.
- **Integrasi QRIS dinamis**: saat ini masih menggunakan QR statis sebagai placeholder.
- **Customer display**: layar kedua yang menampilkan pesanan & total ke pelanggan saat kasir input.

---

## Rekomendasi Urutan Pengerjaan

| Urutan | Fitur | Alasan |
|---|---|---|
| 1 ✅ | Varian & modifier (#1) | Kebutuhan utama kafe; mengubah struktur item pesanan |
| 2 ✅ | Tipe pesanan, meja, nama & nomor antrean (#2) | Fondasi alur pesanan kafe |
| 3 ✅ | Open bill per meja & split bill (#6) | **Dinaikkan**: kafe melayani bayar di depan *dan* bayar di akhir |
| 4 | Pajak & service charge (#4) | Mempengaruhi perhitungan total |
| 5 | Layar barista / tiket cup (#3) | Bergantung pada data pesanan & modifier |
| 6 | Resep & bahan baku (#5) | Bergantung pada data menu, varian & modifier |

Poin #1, #2, dan #4 sebaiknya dikerjakan lebih dulu karena mengubah struktur data inti (tabel pesanan, item pesanan, modifier). Jika ditunda, proses migrasi data akan lebih rumit.

---

## Pertanyaan yang Perlu Dijawab Sebelum Mulai

1. **Alur bayar**: selalu bayar di depan, atau ada pelanggan dine-in yang bayar di akhir? (Menentukan seberapa penting open bill.)
2. **Pesanan diantar atau diambil?** Diantar ke meja (perlu nomor meja) atau dipanggil di counter (cukup nama / nomor antrean).
3. **Ada menu makanan berat?** Jika ya, layar barista perlu dipisah dengan station dapur.
4. **Satu outlet atau multi-cabang?** Jika ada rencana multi-cabang, struktur data sebaiknya disiapkan sejak awal.
