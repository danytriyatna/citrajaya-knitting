<?= $this->extend('layout/guide_template'); ?>

<?= $this->section('guide_content'); ?>

<div class="section-title">1. Gambaran Umum & Lokasi Menu</div>
<p>
  Modul <strong>Barang Keluar (Outgoing Goods / BTK)</strong> digunakan untuk mencatat, mengontrol, dan mengotorisasi setiap aktivitas pengeluaran fisik barang, bahan baku (benang), bahan pembantu, hingga aksesoris dari gudang pabrik PT. Citra Jaya Knitting. Setiap dokumen yang disahkan (<em>Approved</em>) akan <strong>memotong saldo stok pada kartu persediaan (Stock Card)</strong> secara otomatis dan real-time.
</p>
<div class="step-card">
  <strong>📍 Lokasi Menu di Aplikasi:</strong><br>
  Sidebar Kiri &rarr; <strong>Persediaan & Produksi</strong> &rarr; <strong>Barang Keluar</strong> (URL: <span class="badge-code">https://citraknitt.com/trans/outgoing-goods</span>).
</div>

<div class="alert-tip">
  ⭐ <strong>Dampak Finansial & Biaya Produksi (Laporan Laba Rugi):</strong><br>
  Transaksi Barang Keluar dengan tipe <strong>Pemakaian Barang dan Bahan (Kategori 5)</strong> yang berstatus <em>Approved</em> menjadi sumber data utama perhitungan <strong>Biaya Bahan Baku / HPP Pemakaian</strong> pada Laporan Laba Rugi dan Dashboard Keuangan CJK (berlaku mulai cut-off 1 Agustus 2026).
</div>

<div class="section-title">2. Kategori & Sifat Dinamis Form Pengeluaran</div>
<p>
  Form Barang Keluar bekerja secara <strong>adaptif dan kondisional</strong> sesuai kategori pengeluaran yang dipilih operator:
</p>

<table class="guide-table">
  <thead>
    <tr>
      <th width="26%">Tipe / Kategori Keluar</th>
      <th width="37%">Perilaku Form & Kontrol Dinamis</th>
      <th width="37%">Tujuan Alur Operasional</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Pemakaian Barang & Bahan (Kategori 5)</strong></td>
      <td>
        • Membuka pencarian <strong>No. Work Order (SPK)</strong>.<br>
        • Menyembunyikan tombol tambah item bebas (karena bahan ditarik terstruktur dari alokasi WO).<br>
        • Membuka baris kontrol <strong>Total Pemakaian (Kg)</strong> & <strong>BTM (Kg)</strong>.<br>
        • Memuat <strong>Tabel 1 (Produk SO)</strong> & <strong>Tabel 2 (Detail Bahan Baku WO)</strong>.
      </td>
      <td>Pengeluaran bahan baku benang untuk proses rajut/jahit pada SPK. Nilai nominalnya masuk ke perhitungan HPP/Laba Rugi perusahaan.</td>
    </tr>
    <tr>
      <td><strong>Retur Pembelian (Vendor)</strong></td>
      <td>
        • Membuka pencarian <strong>Nama Vendor / Supplier</strong>.<br>
        • Mengaktifkan tombol tambah item manual dari stok gudang terpilih.
      </td>
      <td>Pengembalian barang/benang cacat, rusak, atau klaim ke pihak supplier/pabrik benang asal.</td>
    </tr>
    <tr>
      <td><strong>Pengeluaran Transfer Gudang</strong></td>
      <td>
        • Membuka input rujukan <strong>No. Transfer (TRF...)</strong>.<br>
        • Mengaktifkan tombol tambah item manual.
      </td>
      <td>Pengeluaran fisik barang yang didasarkan pada dokumen Surat Jalan Transfer Antar Gudang.</td>
    </tr>
    <tr>
      <td><strong>Penjualan, Afval / Scrap, & Lain-Lain</strong></td>
      <td>
        • Menyembunyikan No. WO dan Vendor.<br>
        • Mengaktifkan tombol tambah item manual langsung dari saldo stok gudang asal.
      </td>
      <td>Pengeluaran kain/produk jadi untuk pengiriman ke buyer, pembuangan afval/sisa produksi, atau penyesuaian stok lainnya.</td>
    </tr>
  </tbody>
</table>

<div class="section-title">3. Penjelasan Rinci Struktur Form & 2 Tabel Terintegrasi</div>

<!-- BAGIAN HEADER -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">A</span> Bagian Header (Informasi Utama Transaksi Pengeluaran)
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Header memuat informasi dokumen pengeluaran, gudang asal pemotongan stok, dan referensi SPK/Vendor:
  </p>
  
  <table class="guide-table">
    <thead>
      <tr>
        <th width="26%">Nama Kolom / Field</th>
        <th width="16%">Sifat Input</th>
        <th width="58%">Fungsi & Petunjuk Pengisian</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Transaction No.</strong></td>
        <td><span class="badge-code">Otomatis / Readonly</span></td>
        <td>Nomor unik transaksi yang digenerate otomatis oleh sistem (format: <span class="badge-code">BTK260800001</span>) sebagai kode registrasi arsip bukti keluar.</td>
      </tr>
      <tr>
        <td><strong>Date (Tanggal)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Tanggal fisik saat barang/bahan dikeluarkan dari gudang. Default adalah tanggal hari ini.</td>
      </tr>
      <tr>
        <td><strong>Tipe (Kategori Keluar)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Pilihan kategori pengeluaran: <strong>Pemakaian Barang & Bahan</strong>, <strong>Retur Pembelian</strong>, <strong>Transfer Gudang</strong>, <strong>Penjualan</strong>, dll. Menentukan perilaku form selanjutnya.</td>
      </tr>
      <tr>
        <td><strong>No. Work Order (No. WO)</strong></td>
        <td><span class="badge-code">Wajib (Kategori 5)</span></td>
        <td>Aktif pada tipe <em>Pemakaian Barang & Bahan</em>. Klik ikon pencarian <i class="fa fa-search"></i> untuk memilih nomor SPK (<span class="badge-code">WRD...</span>). Sistem otomatis memuat rincian pesanan ke Tabel 1 dan alokasi bahan ke Tabel 2.</td>
      </tr>
      <tr>
        <td><strong>No. Transfer</strong></td>
        <td><span class="badge-code">Kondisional</span></td>
        <td>Aktif pada tipe <em>Pengeluaran Transfer Gudang</em> untuk memilih nomor dokumen <em>Item Transfer</em> (<span class="badge-code">TRF...</span>).</td>
      </tr>
      <tr>
        <td><strong>Vendor</strong></td>
        <td><span class="badge-code">Wajib (Kategori Retur)</span></td>
        <td>Aktif pada tipe <em>Retur Pembelian</em> untuk memilih nama supplier/vendor penerima barang retur.</td>
      </tr>
      <tr>
        <td><strong>Warehouse (Gudang Asal)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Gudang fisik tempat barang dikeluarkan (saldo stok pada gudang ini akan <strong>dipotong berkurang</strong> saat dokumen Approved). Default otomatis ke <em>Gudang Utama</em>.</td>
      </tr>
      <tr>
        <td><strong>Desc (Keterangan)</strong></td>
        <td><span class="badge-code">Opsional</span></td>
        <td>Catatan uraian keperluan pengeluaran, nama supir/pengambil barang, nomor kendaraan, atau instruksi operasional lainnya.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- TABEL 1 -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">B</span> TABEL 1 : Rincian Produk / Data Sales Order / SPK Terkait
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Tabel pertama ini memuat informasi rincian produk pesanan pada Work Order (SPK) terpilih sebagai rujukan target pengerjaan:
  </p>
  
  <table class="guide-table">
    <thead>
      <tr>
        <th width="20%">Kolom Tabel 1</th>
        <th width="18%">Sifat Data</th>
        <th width="62%">Fungsi & Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>No. SO</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Nomor Sales Order rujukan pemesanan pelanggan.</td>
      </tr>
      <tr>
        <td><strong>Style</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Kode model / style pakaian yang diproduksi.</td>
      </tr>
      <tr>
        <td><strong>Deskripsi</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Nama spesifikasi barang jadi / kain rajut.</td>
      </tr>
      <tr>
        <td><strong>Buyer</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Nama pelanggan / konsumen pemesan.</td>
      </tr>
      <tr>
        <td><strong>Colour (Warna)</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Varian warna pakaian/kain yang diproses.</td>
      </tr>
      <tr>
        <td><strong>Qty</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Target kuantitas unit (Pcs/Lembar/Ikat) pakaian yang dikerjakan pada SPK ini.</td>
      </tr>
      <tr>
        <td><strong>Amount</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Total nilai nominal pesanan (Rp) sebagai kontrol finansial produksi.</td>
      </tr>
    </tbody>
  </table>

  <!-- KOTAK KONTROL PEMAKAIAN -->
  <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; padding:12px; margin-top:12px; font-size:9.5pt;">
    <strong style="color:#0369a1; font-size:10pt;">📊 Baris Kontrol Kalkulasi Pemakaian Bahan (Kg):</strong><br>
    <div style="margin-top:6px;">
      - <strong>Total Pemakaian Barang (Kg):</strong> Input kuantitas riil total benang yang dikeluarkan pada transaksi BTK saat ini.<br>
      - <strong>BTM (Kg):</strong> Menampilkan <strong>Sisa Berat (Kg) Kain Hasil Produksi</strong> yang telah diterima di Barang Masuk namun <em>belum dibukukan pemakaiannya pada transaksi BTK sebelumnya</em>, ditambah <strong>Total Retur Bahan</strong> (Rumus: <code>Total BTM Masuk - Total BTK Approved Sebelumnya + Total Retur</code>). Ini menjadi acuan presisi saat transaksi dilakukan secara parsial / bertahap.
    </div>
  </div>
</div>

<div class="page-break"></div>

<!-- TABEL 2 -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">C</span> TABEL 2 : Rincian Detail Bahan Baku / Benang Keluar (Tracking Snapshot Sisa Bahan)
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Tabel kedua ini mencatat fisik bahan baku benang yang dikeluarkan dari gudang asal, lengkap dengan tracking snapshot sisa alokasi WO:
  </p>

  <table class="guide-table">
    <thead>
      <tr>
        <th width="24%">Kolom Tabel 2</th>
        <th width="18%">Sifat Input</th>
        <th width="58%">Fungsi & Rumus Kalkulasi Kolom</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Aksi</strong></td>
        <td><span class="badge-code">Edit & Hapus</span></td>
        <td>Tombol untuk mengubah kuantitas atau menghapus baris bahan (hanya aktif selagi dokumen berstatus Draft).</td>
      </tr>
      <tr>
        <td><strong>NO WORKORDER</strong></td>
        <td><span class="badge-code">Otomatis dari WO</span></td>
        <td>Nomor SPK / Work Order rujukan pemakaian bahan.</td>
      </tr>
      <tr>
        <td><strong>ITEM CODE & DESCRIPTION</strong></td>
        <td><span class="badge-code">Otomatis / Pilih</span></td>
        <td>Kode dan nama benang/material yang dikeluarkan (contoh: <span class="badge-code">CTN 32/2 MARUN</span>).</td>
      </tr>
      <tr>
        <td><strong>QTY TERIMA</strong></td>
        <td><span class="badge-code">Otomatis Sistem</span></td>
        <td>Total kuantitas benang yang pernah diterima/ditransfer untuk nomor WO tersebut.</td>
      </tr>
      <tr>
        <td><strong>QTY RETUR</strong></td>
        <td><span class="badge-code">Otomatis Sistem</span></td>
        <td>Total kuantitas bahan yang pernah diretur/dikembalikan ke gudang asal.</td>
      </tr>
      <tr>
        <td><strong>PEMAKAIAN SEBELUMNYA</strong></td>
        <td><span class="badge-code">Otomatis Sistem</span></td>
        <td>Akumulasi kuantitas benang yang sudah pernah dikeluarkan pada dokumen BTK sebelumnya untuk WO yang sama.</td>
      </tr>
      <tr>
        <td><strong>QTY SISA</strong></td>
        <td><span class="badge-code" style="background:#e0f2fe; color:#0369a1;">Kalkulasi Otomatis</span></td>
        <td>
          Sisa kuantitas bahan yang masih tersedia untuk dialokasikan pada SPK ini.<br>
          <em>Rumus:</em> $\mathbf{QTY\;Terima - QTY\;Retur - Pemakaian\;Sebelumnya}$.
        </td>
      </tr>
      <tr>
        <td><strong>QTY (Qty Keluar Saat Ini)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Jumlah kuantitas fisik bahan yang dikeluarkan pada transaksi ini. <strong>Tidak boleh melebihi QTY SISA</strong>.</td>
      </tr>
      <tr>
        <td><strong>UNIT (Satuan)</strong></td>
        <td><span class="badge-code">Otomatis</span></td>
        <td>Satuan ukuran persediaan (<code>Kg</code>, <code>Roll</code>, <code>Pcs</code>, <code>Yard</code>).</td>
      </tr>
      <tr>
        <td><strong>PRICE (Harga Satuan)</strong></td>
        <td><span class="badge-code" style="background:#e2e8f0; color:#334155;">Readonly / Terkunci</span></td>
        <td>Harga perolehan beli per unit (Rp/Kg) terkunci otomatis mengikuti Lot asal dari kartu stok. Petugas dilarang merubah harga manual agar HPP Laba Rugi tetap presisi.</td>
      </tr>
      <tr>
        <td><strong>LOT NO & PACK NAME</strong></td>
        <td><span class="badge-code">Otomatis dari Stok</span></td>
        <td>Nomor batch Lot benang dan jenis kemasan fisik (Karung, Dus, Roll) untuk menjamin konsistensi lot warna dan jejak fisik.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- BAB 4: ATURAN VALIDASI BISNIS -->
<div class="section-title">4. Aturan Validasi Bisnis & Kontrol Kuota Pemakaian Bahan</div>

<div class="step-card" style="border-left: 4px solid #ef4444;">
  <div class="step-header">
    <span class="step-num">1</span> Validasi Kewajiban No. WO & Total Pemakaian (Kategori 5)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 0;">
    Untuk transaksi dengan tipe <strong>Pemakaian Barang & Bahan (Kategori 5)</strong>, sistem secara ketat memvalidasi:<br>
    1. <strong>No. WO Wajib Dipilih:</strong> Transaksi tidak dapat disimpan tanpa rujukan SPK Work Order yang sah.<br>
    2. <strong>Total Pemakaian Wajib Diisi:</strong> Input <em>Total Pemakaian Barang (Kg)</em> atau akumulasi Qty pada tabel rincian bahan wajib bernilai $> 0$.
  </p>
</div>

<div class="step-card" style="border-left: 4px solid #0284c7;">
  <div class="step-header">
    <span class="step-num">2</span> Validasi Batas Kuota: Total Pemakaian $\le$ Total Qty Sisa
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 8px;">
    Sistem mencegah terjadinya pengeluaran benang melebihi sisa alokasi yang pernah dikirim ke Work Order tersebut:
  </p>
  <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px;">
    <strong style="color: #166534;">Aturan Kuota Sistem:</strong><br>
    <span style="font-size: 9.5pt; color: #1e293b;">
      $$\mathbf{Total\;Pemakaian\;(Validasi)} \le \mathbf{Total\;QTY\;Sisa\;(Alokasi\;WO)} + 0.01$$
      • <strong>Kontrol Header:</strong> Jika <em>Total Pemakaian</em> yang diinput melebihi akumulasi <em>Total QTY Sisa</em> pada tabel rincian bahan, sistem akan memunculkan notifikasi error dan memblokir simpan/approval.<br>
      • <strong>Kontrol Per Baris Detail:</strong> Kuantitas keluar pada setiap baris bahan juga diperiksa: $\mathbf{Qty\;Baris \le Qty\;Sisa\;Baris}$. Tidak boleh ada baris bahan yang dikeluarkan melebihi sisa Lot benang terkait.
    </span>
  </div>
</div>

<div class="step-card" style="border-left: 4px solid #f59e0b;">
  <div class="step-header">
    <span class="step-num">3</span> Integritas HPP: Perlindungan Harga Terkunci (Price Readonly)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 0;">
    Field <strong>Price</strong> pada form modal detail bahan baku sengaja dikunci (<em>readonly</em>). Nilai harga per unit selalu diambil secara otomatis dari harga saldo Lot kartu persediaan saat barang diterima. Hal ini menjamin nilai rupiah pengeluaran bahan baku di Laporan Keuangan/Laba Rugi 100% akurat dan tidak dapat dimanipulasi manual.
  </p>
</div>

<div class="page-break"></div>

<div class="section-title">5. Alur Status & Eksekusi Mutasi Stok</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">A</span> Simpan (Draft) vs Approval (Eksekusi Pemotongan Stok)
  </div>
  <table class="guide-table">
    <thead>
      <tr>
        <th width="20%">Tombol / Aksi</th>
        <th width="20%">Status</th>
        <th width="60%">Dampak Terhadap Persediaan & Biaya Produksi</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Simpan</strong></td>
        <td><span class="badge-code" style="background:#64748b; color:#fff;">Draft (0)</span></td>
        <td>Dokumen disimpan sebagai draf sementara. <strong>Stok fisik belum berkurang</strong>. Data masih dapat diedit dan disesuaikan.</td>
      </tr>
      <tr>
        <td><strong>Approval</strong></td>
        <td><span class="badge-code" style="background:#0284c7; color:#fff;">Approved (1)</span></td>
        <td>Dokumen disahkan & dikunci permanen. <strong>Stok gudang asal resmi dipotong</strong> pada kartu stok persediaan (Stock Card), dan nilai rupiahnya resmi dibukukan sebagai biaya bahan baku di Laporan Laba Rugi.</td>
      </tr>
      <tr>
        <td><strong>Batalkan Approve</strong></td>
        <td><span class="badge-code" style="background:#ef4444; color:#fff;">Batal Approve</span></td>
        <td>Khusus Superadmin/Admin: Mengembalikan status dokumen ke Draft dan memulihkan saldo kartu stok ke posisi semula secara tertib.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">B</span> Cetak Surat Pengeluaran Barang / Bukti Keluar
  </div>
  <p style="margin: 4px 0 0 0;">
    Klik tombol <strong>Cetak / Print</strong> pada baris transaksi di halaman daftar utama atau langsung dari form edit dokumen untuk mencetak <em>Surat Bukti Pengeluaran Barang / Bon Pengeluaran Bahan Baku</em> resmi (A4 Landscape) sebagai bukti fisik serah terima bagian gudang dan mandor pabrik.
  </p>
</div>

<div class="section-title">6. Fitur Filter, Pencarian & Export Excel</div>
<table class="guide-table">
  <thead>
    <tr>
      <th width="30%">Fitur Kontrol</th>
      <th width="70%">Fungsi & Cara Penggunaan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Filter Tanggal (From - To)</strong></td>
      <td>Membatasi data pengeluaran berdasarkan rentang periode tanggal keluar.</td>
    </tr>
    <tr>
      <td><strong>Export Excel</strong></td>
      <td>Mengunduh laporan rekapitulasi data barang keluar ke format spreadsheet Microsoft Excel.</td>
    </tr>
    <tr>
      <td><strong>Pencarian Cepat (Live Search)</strong></td>
      <td>Ketikkan No. Transaksi, No. WO, Nama Barang, Nama Gudang, atau Kategori pada kotak pencarian instan.</td>
    </tr>
  </tbody>
</table>

<div class="alert-tip">
  💡 <strong>Tips Operasional Gudang:</strong><br>
  - Selalu pastikan bahan baku yang dikeluarkan ditimbang secara presisi sebelum menekan tombol <strong>Approval</strong>.<br>
  - Perhatikan kolom <strong>QTY SISA</strong> pada Tabel 2 agar tidak terjadi pengeluaran bahan melebihi alokasi yang telah dikirim ke lantai produksi.<br>
  - Manfaatkan baris kontrol <strong>BTM (Kg)</strong> untuk mengecek apakah seluruh kain hasil rajut dari WO terkait sudah selesai diterima di gudang sebelum menutup transaksi pemakaian benang.
</div>

<?= $this->endSection('guide_content'); ?>
