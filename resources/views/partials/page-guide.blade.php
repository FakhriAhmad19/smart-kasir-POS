@php
    $routeName = request()->route()?->getName() ?? '';
    $guide = [
        'title' => 'Panduan Halaman',
        'intro' => 'Gunakan bagian ini untuk memahami fungsi halaman yang sedang dibuka.',
        'steps' => [],
        'tips' => [],
    ];

    if (request()->routeIs('dashboard')) {
        $guide = ['title' => 'Panduan Beranda', 'intro' => 'Beranda merangkum kondisi operasional kafe hari ini.', 'steps' => ['Periksa omzet, transaksi, dan laba hari ini.', 'Lihat peringatan stok menipis lalu lakukan restock bila diperlukan.', 'Gunakan pintasan Buka Kasir atau menu laporan untuk tindakan lanjutan.'], 'tips' => ['Angka penjualan hanya menghitung transaksi yang sudah dibayar.']];
    } elseif (request()->routeIs('pos.*')) {
        $guide = ['title' => 'Panduan Kasir', 'intro' => 'Gunakan POS untuk menerima pesanan, pembayaran, dan open bill.', 'steps' => ['Pilih tipe pesanan: Dine-in, Take Away, atau Online.', 'Klik menu, pilih varian/modifier, lalu tambahkan catatan item bila perlu.', 'Isi diskon dan pembayaran, lalu pilih Bayar Sekarang atau Bayar Nanti.', 'Cetak struk setelah transaksi berhasil.'], 'tips' => ['Dine-in wajib memilih meja. Open bill dapat ditambah pesanan susulan dan dibayar dengan split bill.']];
    } elseif (request()->routeIs('tickets.*')) {
        $station = request()->route('station') === 'kitchen' ? 'Kitchen' : 'Barista';
        $guide = ['title' => "Panduan Tiket {$station}", 'intro' => "Halaman ini hanya menampilkan item untuk station {$station}.", 'steps' => ['Baca nomor antrean, meja/nama pelanggan, dan detail item.', 'Klik Mulai saat item sedang dikerjakan.', 'Klik Siap setelah item selesai dibuat.', 'Gunakan Kembalikan bila item perlu dikerjakan ulang.'], 'tips' => ['Station menu ditentukan dari kategori Minuman atau Makanan & Snack.']];
    } elseif (request()->routeIs('shifts.*')) {
        $guide = ['title' => 'Panduan Shift & Laci', 'intro' => 'Shift mencatat kasir yang bertugas dan uang awal/akhir di laci.', 'steps' => ['Buka shift sebelum memakai POS.', 'Masukkan uang awal dan catat transaksi selama bertugas.', 'Tutup shift dengan menghitung uang fisik dan cocokkan dengan sistem.'], 'tips' => ['Kasir tidak dapat membuka POS sebelum memiliki shift aktif.']];
    } elseif (request()->routeIs('products.*')) {
        $guide = ['title' => 'Panduan Daftar Menu', 'intro' => 'Kelola menu yang dapat dijual melalui POS.', 'steps' => ['Buat menu dengan kategori, harga beli, harga jual, dan stok.', 'Pilih station: Minuman untuk Barista atau Makanan untuk Kitchen.', 'Tambahkan varian/modifier dari halaman produk bila diperlukan.', 'Nonaktifkan menu yang sementara tidak dijual.'], 'tips' => ['Bahan baku dikelola di menu Bahan Baku, bukan sebagai menu kasir.']];
    } elseif (request()->routeIs('modifiers.*')) {
        $guide = ['title' => 'Panduan Varian & Modifier', 'intro' => 'Atur pilihan ukuran, gula, es, susu, dan add-on.', 'steps' => ['Buat grup modifier, misalnya Ukuran atau Level Gula.', 'Atur jumlah pilihan minimum dan maksimum.', 'Tambahkan harga tambahan pada opsi berbayar.', 'Hubungkan grup modifier ke menu di form produk.'], 'tips' => ['Modifier wajib akan otomatis divalidasi saat kasir checkout.']];
    } elseif (request()->routeIs('categories.*')) {
        $guide = ['title' => 'Panduan Kategori', 'intro' => 'Kategori membantu mengelompokkan menu dan menentukan station utama.', 'steps' => ['Buat kategori dengan nama yang jelas.', 'Gunakan kategori Minuman untuk tiket Barista.', 'Gunakan kategori Makanan & Snack untuk tiket Kitchen.']];
    } elseif (request()->routeIs('tables.*')) {
        $guide = ['title' => 'Panduan Meja', 'intro' => 'Kelola meja untuk pesanan Dine-in.', 'steps' => ['Tambahkan nama atau nomor meja.', 'Isi area dan kapasitas bila diperlukan.', 'Nonaktifkan meja yang sedang tidak tersedia.'], 'tips' => ['Meja aktif akan muncul di POS saat tipe pesanan Dine-in dipilih.']];
    } elseif (request()->routeIs('raw-materials.*')) {
        $guide = ['title' => 'Panduan Bahan Baku', 'intro' => 'Catat bahan baku dan stok operasional tanpa menampilkannya di POS.', 'steps' => ['Buat bahan dengan satuan gram, ml, pcs, kg, liter, atau pack.', 'Isi HPP, stok awal, dan batas minimum.', 'Gunakan Barang Masuk untuk restock dan Stock Opname untuk koreksi fisik.'], 'tips' => ['Resep dan pemotongan otomatis bahan baku belum digunakan.']];
    } elseif (request()->routeIs('stock-ins.*')) {
        $guide = ['title' => 'Panduan Barang Masuk', 'intro' => 'Gunakan halaman ini saat menerima pasokan dari supplier.', 'steps' => ['Pilih menu atau bahan baku.', 'Isi supplier, jumlah, dan harga beli satuan.', 'Centang Perbarui HPP bila harga master perlu mengikuti harga terbaru.', 'Simpan untuk menambah stok.']];
    } elseif (request()->routeIs('adjustments.*')) {
        $guide = ['title' => 'Panduan Stock Opname', 'intro' => 'Cocokkan stok sistem dengan stok fisik di rak/gudang.', 'steps' => ['Pilih item dan alasan penyesuaian.', 'Masukkan jumlah fisik sebenarnya.', 'Tulis catatan bila ada barang rusak, hilang, atau kedaluwarsa.', 'Simpan agar stok sistem diperbarui dan histori tercatat.']];
    } elseif (request()->routeIs('reports.*')) {
        $guide = ['title' => 'Panduan Laporan', 'intro' => 'Gunakan filter tanggal untuk membaca performa kafe.', 'steps' => ['Pilih rentang tanggal dan filter metode pembayaran/kasir.', 'Baca menu terlaris, modifier, penjualan per jam, dan tipe pesanan.', 'Gunakan CSV/PDF untuk menyimpan atau membagikan laporan.', 'Owner/Admin dapat melakukan void atau refund dengan PIN supervisor.'], 'tips' => ['Laporan bahan baku berdasarkan resep belum tersedia karena fitur resep masih ditunda.']];
    } elseif (request()->routeIs('expenses.*')) {
        $guide = ['title' => 'Panduan Pengeluaran', 'intro' => 'Catat biaya operasional kafe untuk menghitung laba bersih.', 'steps' => ['Masukkan tanggal, kategori, nominal, dan keterangan pengeluaran.', 'Periksa kembali data sebelum menyimpan.', 'Nilai pengeluaran akan masuk ke ringkasan laporan.']];
    } elseif (request()->routeIs('users.*')) {
        $guide = ['title' => 'Panduan Tim & Akses', 'intro' => 'Kelola akun dan batas akses setiap petugas.', 'steps' => ['Buat akun dengan email dan password.', 'Pilih role Kasir, Barista, Kitchen, Admin, atau Owner.', 'Barista hanya melihat Tiket Barista; Kitchen hanya melihat Tiket Kitchen.', 'Nonaktifkan akun yang tidak lagi digunakan.']];
    } elseif (request()->routeIs('settings.*')) {
        $guide = ['title' => 'Panduan Pengaturan', 'intro' => 'Atur identitas toko, struk, pajak, dan keamanan refund.', 'steps' => ['Isi nama, alamat, kontak, dan footer struk.', 'Atur PB1, service charge, dan apakah harga sudah termasuk pajak.', 'Buat PIN Supervisor untuk mengizinkan void dan refund.', 'Simpan perubahan lalu uji satu transaksi bila mengubah pajak.']];
    } elseif (request()->routeIs('profile.*')) {
        $guide = ['title' => 'Panduan Profil', 'intro' => 'Perbarui data akun yang sedang digunakan.', 'steps' => ['Ubah nama atau email bila diperlukan.', 'Gunakan password baru untuk mengganti kata sandi.', 'Simpan perubahan setelah semua data benar.']];
    }

    $guide['steps'] ??= [];
    $guide['tips'] ??= [];
@endphp

<button type="button" class="page-guide-trigger" data-bs-toggle="modal" data-bs-target="#pageGuideModal" aria-label="Buka panduan halaman" title="Panduan halaman">
    <i class="bi bi-question-circle"></i><span>Panduan</span>
</button>

<div class="modal fade" id="pageGuideModal" tabindex="-1" aria-labelledby="pageGuideTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-0 pb-0"><div><div class="small text-primary fw-bold text-uppercase">Bantuan Smart Kasir</div><h5 class="modal-title fw-bold" id="pageGuideTitle">{{ $guide['title'] }}</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body p-4">
            <p class="text-muted">{{ $guide['intro'] }}</p>
            @if($guide['steps'])
                <h6 class="fw-bold">Cara menggunakan</h6>
                <ol class="ps-3">@foreach($guide['steps'] as $step)<li class="mb-2">{{ $step }}</li>@endforeach</ol>
            @endif
            @if($guide['tips'])
                <div class="alert alert-primary-subtle border-0 small mb-0"><i class="bi bi-lightbulb me-1"></i><strong>Tips:</strong> {{ implode(' ', $guide['tips']) }}</div>
            @endif
        </div>
    </div></div>
</div>
