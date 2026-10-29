<?= $this->extend('layout/guide_template'); ?>

<?= $this->section('guide_content'); ?>

<div class="section-title">1. Gambaran Umum & Lokasi Menu</div>
<p>
  Modul <strong>Barang Masuk (Incoming Goods)</strong> digunakan untuk mencatat setiap aktivitas penerimaan barang, bahan baku (benang), bahan pembantu, aksesoris, hingga barang/kain hasil produksi ke dalam gudang pabrik PT. Citra Jaya Knitting. Modul ini dilengkapi dengan <strong>1 Form Header Dinamis</strong> dan <strong>2 Tabel Pengisian Terintegrasi</strong> (Tabel Hasil Produksi/SO/WO & Tabel Bahan Baku Masuk) untuk memastikan mutasi stok tercatat akurat pada kartu persediaan (<em>Stock Card</em>).
</p>
<div class="step-card">
  <strong>📍 Lokasi Menu di Aplikasi:</strong><br>
  Sidebar Kiri &rarr; <strong>Persediaan & Produksi</strong> &rarr; <strong>Barang Masuk</strong> (URL: <span class="badge-code">https://citraknitt.com/trans/incoming-goods</span>).
</div>

<div class="section-title">2. Kategori & Sifat Dinamis Form Penerimaan</div>
<p>
  Form Barang Masuk bekerja secara <strong>cerdas dan adaptif</strong>. Tampilan field input dan tabel yang muncul akan otomatis disesuaikan berdasarkan <strong>Tipe (Kategori)</strong> penerimaan yang dipilih oleh operator:
</p>

<table class="guide-table">
  <thead>
    <tr>
      <th width="24%">Tipe / Kategori Masuk</th>
      <th width="38%">Perilaku Form & Tabel yang Aktif</th>
      <th width="38%">Penjelasan & Dokumen Terkait</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Hasil Produksi / In-House & Penerimaan dari CMT</strong></td>
      <td>
        • Membuka field <strong>Proses</strong>, <strong>CMT</strong>, dan <strong>Mesin</strong>.<br>
        • Mengaktifkan <strong>TABEL 1 (Hasil Produksi / SO)</strong> lengkap dengan fitur <em>Scan Barcode</em>, <em>Timbangan Digital</em>, dan rujukan <em>SPK/Transfer</em>.<br>
        • Menyembunyikan Tabel Bahan Baku.
      </td>
      <td>Penerimaan hasil rajut, jahit, steam, atau packing dari lini internal pabrik maupun pengembalian kain/baju jadi dari CMT makloon luar.</td>
    </tr>
    <tr>
      <td><strong>Retur Penjualan</strong></td>
      <td>
        • Membuka pencarian <strong>Buyer / Konsumen</strong>.<br>
        • Mengaktifkan <strong>TABEL 2 (Bahan Baku / Detail)</strong>.
      </td>
      <td>Penerimaan kembali barang atau pakaian jadi dari konsumen/buyer akibat klaim, retur perbaikan, atau penyesuaian pesanan.</td>
    </tr>
    <tr>
      <td><strong>Penerimaan Transfer Gudang</strong></td>
      <td>
        • Mengaktifkan input rujukan <strong>No. Transfer (TRF...)</strong>.<br>
        • Mengaktifkan <strong>TABEL 2 (Bahan Baku / Benang)</strong>.
      </td>
      <td>Penerimaan bahan baku/benang yang dimutasi dari gudang lain (misal: kiriman dari Gudang Utama ke Gudang Rajut).</td>
    </tr>
    <tr>
      <td><strong>Pembelian (PO) & Lain-Lain</strong></td>
      <td>• Mengaktifkan <strong>TABEL 2 (Bahan Baku / Benang)</strong>.</td>
      <td>Penerimaan bahan baku langsung dari supplier luar atau penyesuaian saldo fisik awal.</td>
    </tr>
  </tbody>
</table>

<div class="section-title">3. Penjelasan Rinci Struktur Form & 2 Tabel Pengisian</div>

<!-- BAGIAN HEADER -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">A</span> Bagian Header (Informasi Utama Transaksi Penerimaan)
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Header memuat identitas transaksi, gudang tujuan penyimpanan, dan parameter mesin/proses:
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
        <td>Nomor unik transaksi yang digenerate otomatis oleh sistem (format: <span class="badge-code">BTM260800001</span> atau <span class="badge-code">BTMC...</span> untuk P2) sebagai kode arsip penerimaan.</td>
      </tr>
      <tr>
        <td><strong>Date (Tanggal)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Tanggal fisik saat barang/bahan tiba dan diterima di area gudang pabrik. <strong>Tidak boleh melebihi tanggal hari ini</strong>.</td>
      </tr>
      <tr>
        <td><strong>Tipe (Kategori)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Pilihan jenis penerimaan (<em>Hasil Produksi, Penerimaan CMT, Penerimaan Transfer Gudang, Retur Penjualan, Pembelian PO, atau Lain-Lain</em>). Menentukan form input dinamis selanjutnya.</td>
      </tr>
      <tr>
        <td><strong>No. Transfer (Rujukan TRF)</strong></td>
        <td><span class="badge-code">Kondisional / Pop-up</span></td>
        <td>
          Memilih nomor rujukan transfer pengirim (<span class="badge-code">TRF...</span>).<br>
          <em>*<strong>Otomatisasi Sistem:</strong> Saat No. Transfer dipilih, data <strong>Proses</strong> dan <strong>CMT</strong> otomatis tersinkron dan langsung <strong>terkunci (disabled)</strong> untuk menjamin integritas alur pengiriman.</em>
        </td>
      </tr>
      <tr>
        <td><strong>Warehouse (Gudang)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Gudang fisik tempat barang akan disimpan dan bertambah saldonya (misal: <em>Gudang Utama, Gudang Bahan Baku, Gudang Jadi</em>). Otomatis menyesuaikan gudang milik perusahaan login.</td>
      </tr>
      <tr>
        <td><strong>Perusahaan</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Entitas badan usaha pemilik stok persediaan (PT. Citra Jaya Knitting / Citra Knitt).</td>
      </tr>
      <tr>
        <td><strong>Buyer / Konsumen</strong></td>
        <td><span class="badge-code">Khusus Retur</span></td>
        <td>Aktif otomatis saat memilih Tipe Retur Penjualan untuk memilih nama pelanggan pemesan.</td>
      </tr>
      <tr>
        <td><strong>Proses & CMT</strong></td>
        <td><span class="badge-code">Khusus Produksi</span></td>
        <td>Memilih tahapan proses produksi (<em>Rajut, Lingking, Obras, Washing, Steam, Jahit Label, QC & Packing</em>) dan nama vendor CMT pengirim barang jika tanpa rujukan No. Transfer.</td>
      </tr>
      <tr>
        <td><strong>Nomor Mesin, Nilai, & Jam Mesin</strong></td>
        <td><span class="badge-code">Khusus Produksi</span></td>
        <td>Pencatatan nomor mesin rajut/jahit, nilai utilitas, dan total jam kerja mesin untuk monitoring operasional produksi internal.</td>
      </tr>
      <tr>
        <td><strong>Desc (Keterangan)</strong></td>
        <td><span class="badge-code">Opsional</span></td>
        <td>Catatan nomor surat jalan supplier/CMT, nama pengantar/sopir, plat kendaraan, atau kondisi fisik kemasan.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- TABEL 1 -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">B</span> TABEL 1 : Rincian Produk / Sales Order / Kain Hasil Produksi Masuk
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Tabel pertama ini aktif pada tipe <strong>Hasil Produksi</strong> atau <strong>Penerimaan CMT</strong> untuk mencatat kain rajut atau pakaian jadi:
  </p>
  
  <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:6px; padding:10px; margin-bottom:12px; font-size:9.5pt;">
    <strong>Fitur Cepat Tabel 1:</strong><br>
    - <strong>Scan Barcode / QR:</strong> Kotak input untuk memindai barcode/QR label hasil produksi secara cepat.<br>
    - <strong>Tombol <span class="badge-code" style="background:#0284c7; color:#fff;">⚖️ Timbangan</span>:</strong> Mengambil berat aktual (Kg) secara real-time dari timbangan digital serial (Yaohua YH-T3) ke baris terpilih.<br>
    - <strong>Tombol <span class="badge-code" style="background:#06b6d4; color:#fff;">+</span> Ref Transfer/PO:</strong> Membuka popup daftar item dari nomor transfer rujukan untuk dimasukkan ke tabel.
  </div>

  <table class="guide-table">
    <thead>
      <tr>
        <th width="20%">Kolom Tabel 1</th>
        <th width="18%">Metode Input</th>
        <th width="62%">Fungsi & Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Tipe</strong></td>
        <td><span class="badge-code" style="background:#e0e7ff; color:#3730a3;">Dropdown Baris</span></td>
        <td>
          Klasifikasi pengerjaan barang:<br>
          • <span class="badge-code" style="background:#22c55e; color:#fff;">PRODUKSI</span> : Barang hasil produksi reguler.<br>
          • <span class="badge-code" style="background:#eab308; color:#000;">PERBAIKAN</span> : Barang hasil pengerjaan ulang (re-work/repair).
        </td>
      </tr>
      <tr>
        <td><strong>No. SO</strong></td>
        <td><span class="badge-code">Otomatis Rujukan</span></td>
        <td>Nomor Sales Order pesanan pelanggan yang diproduksi.</td>
      </tr>
      <tr>
        <td><strong>Style</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Kode model / style pakaian.</td>
      </tr>
      <tr>
        <td><strong>Deskripsi</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Uraian spesifikasi nama barang / produk.</td>
      </tr>
      <tr>
        <td><strong>Buyer</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Nama pelanggan / buyer pemesan barang.</td>
      </tr>
      <tr>
        <td><strong>Colour (Warna)</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Varian warna barang yang diterima.</td>
      </tr>
      <tr>
        <td><strong>Tgl Transaksi</strong></td>
        <td><span class="badge-code">Date Picker Baris</span></td>
        <td>Tanggal kedatangan fisik per item/baris kain. Dapat disesuaikan jika pengiriman bertahap beda hari.</td>
      </tr>
      <tr>
        <td><strong>Qty Kirim</strong></td>
        <td><span class="badge-code">Otomatis Terkunci</span></td>
        <td>Sisa kuota barang yang dikirim dari dokumen Transfer asal yang sah untuk diterima.</td>
      </tr>
      <tr>
        <td><strong>Qty BTM (Qty Terima)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>
          Jumlah fisik (Pcs/Lembar/Ikat) yang diterima saat ini.<br>
          <em>*Pada tahap <strong>Approval</strong>, sistem memvalidasi bahwa Qty BTM tidak boleh melebihi Qty Kirim.</em>
        </td>
      </tr>
      <tr>
        <td><strong>Berat (Kg)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Timbangan / Edit</span></td>
        <td>Berat timbangan aktual (Kg). Dapat diisi otomatis via tombol <strong>⚖️ Timbangan Digital</strong>.</td>
      </tr>
      <tr>
        <td><strong>Ukuran</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Kode ukuran pakaian (contoh: <code>S</code>, <code>M</code>, <code>L</code>, <code>XL</code>, <code>ALL</code>).</td>
      </tr>
      <tr>
        <td><strong>Harga</strong></td>
        <td><span class="badge-code">Dapat Diedit</span></td>
        <td>Biaya jasa pengerjaan per unit (Rp) dari CMT/proses terkait.</td>
      </tr>
      <tr>
        <td><strong>Amount</strong></td>
        <td><span class="badge-code">Otomatis Hitung</span></td>
        <td>Total nilai finansial baris ($\mathbf{Qty\;BTM \times Harga}$). Digunakan untuk dasar rekap Faktur Penerimaan.</td>
      </tr>
      <tr>
        <td><strong>Keterangan</strong></td>
        <td><span class="badge-code">Dapat Diedit</span></td>
        <td>Catatan baris khusus (misal: <em>Karung No. 2, Grade A, dll.</em>).</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- TABEL 2 -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">C</span> TABEL 2 : Rincian Detail Bahan Baku / Benang / Item Masuk
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Tabel kedua ini digunakan untuk mencatat penerimaan bahan baku benang, aksesoris, atau bahan pembantu lainnya:
  </p>
  
  <p style="font-size: 10pt; margin-bottom: 8px;">
    Klik tombol <span class="badge-code" style="background:#0284c7; color:#fff;">+</span> pada Tabel 2 untuk membuka modal input detail item:
  </p>

  <table class="guide-table">
    <thead>
      <tr>
        <th width="24%">Kolom / Field Tabel 2</th>
        <th width="18%">Sifat Input</th>
        <th width="58%">Fungsi & Petunjuk Pengisian</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>ITEM CODE & DESCRIPTION</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Dipilih</span></td>
        <td>Pilih nama benang/material dari master katalog data (contoh: <span class="badge-code">CTN 32/2 MARUN</span>, <code>Poly 20s</code>, <code>Kain Rib</code>).</td>
      </tr>
      <tr>
        <td><strong>UNIT (Satuan)</strong></td>
        <td><span class="badge-code">Otomatis / Readonly</span></td>
        <td>Satuan kuantitas barang (<code>Kg</code>, <code>Roll</code>, <code>Pcs</code>, <code>Yard</code>, <code>Cones</code>). Terisi otomatis dari master barang.</td>
      </tr>
      <tr>
        <td><strong>QTY (Qty Receive)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>
          Jumlah kuantitas fisik yang diterima di gudang:
          <ul style="margin: 2px 0 0 0; padding-left: 18px;">
            <li><strong>Input Manual:</strong> Ketik langsung kuantitas angka.</li>
            <li><strong>Tombol <span class="badge-code" style="background:#0284c7; color:#fff;">⚖️ Timbangan</span>:</strong> Mengambil berat aktual dari timbangan serial digital COM Port secara langsung.</li>
          </ul>
        </td>
      </tr>
      <tr>
        <td><strong>PRICE (Harga Satuan)</strong></td>
        <td><span class="badge-code">Kondisional</span></td>
        <td>Harga perolehan beli per unit (Rp/Kg). Terisi otomatis dari PO atau diisi sesuai faktur supplier.</td>
      </tr>
      <tr>
        <td><strong>LOT NO</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Sangat Penting</span></td>
        <td>Nomor batch / lot produksi benang pabrik untuk menjamin konsistensi warna rajutan dan penelusuran riwayat lot.</td>
      </tr>
      <tr>
        <td><strong>PACK (Kemasan)</strong></td>
        <td><span class="badge-code">Opsional</span></td>
        <td>Pilihan jenis kemasan fisik (<em>Karung, Dus, Roll, Plastik, Pallet</em>) untuk memudahkan stock opname fisik gudang.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- BAB 4: ATURAN VALIDASI BISNIS -->
<div class="section-title">4. Aturan Validasi Bisnis & Kontrol Kuota Penerimaan</div>

<div class="step-card" style="border-left: 4px solid #0284c7;">
  <div class="step-header">
    <span class="step-num">1</span> Validasi Tanggal Transaksi (Pencegahan Tanggal Maju)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 0;">
    Sistem secara ketat memeriksa tanggal pada form Header (<code>Date</code>). Tanggal transaksi penerimaan <strong>tidak boleh melebihi tanggal hari ini (future date dilarang)</strong> demi menjaga akurasi urutan kronologis kartu persediaan (<em>Stock Card</em>).
  </p>
</div>

<div class="step-card" style="border-left: 4px solid #059669;">
  <div class="step-header">
    <span class="step-num">2</span> Validasi Kuota Penerimaan Hasil Produksi ($Qty\;BTM \le Qty\;Kirim$)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 8px;">
    Pada transaksi penerimaan hasil produksi atau pengembalian dari CMT luar berdasarkan rujukan Transfer:
  </p>
  <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px;">
    <strong style="color: #166534;">Aturan Pengesahan (Approval):</strong><br>
    <span style="font-size: 9.5pt; color: #1e293b;">
      $$\mathbf{Qty\;BTM\;(Kuantitas\;Terima)} \le \mathbf{Qty\;Kirim\;(Sisa\;Kuota\;Transfer)}$$
      • Jika <strong>Qty BTM > Qty Kirim</strong>, sistem akan menolak Approval dengan pesan: <em>"Terdapat item dengan Qty Terima yang melebihi Sisa Qty Transfer! Harap sesuaikan Qty atau simpan sebagai Draft."</em><br>
      • <strong>Simpan Draft Tetap Diizinkan:</strong> Pembatasan kuota ini tidak menghalangi penyimpanan sebagai <strong>Draft</strong>, sehingga operator gudang tetap dapat mencatat data sementara sambil menunggu verifikasi fisik.<br>
      • <em>Pengecualian: Aturan pembatasan Qty Kirim ini dikecualikan untuk entitas Perusahaan 2 (Citra Knitt) atau supervisor gudang berwenang (Role 5).</em>
    </span>
  </div>
</div>

<div class="step-card" style="border-left: 4px solid #f59e0b;">
  <div class="step-header">
    <span class="step-num">3</span> Validasi Baris Rincian Tidak Boleh Kosong
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 0;">
    Sistem mewajibkan minimal terdapat <strong>1 baris rincian barang/produksi</strong> yang valid sebelum transaksi dapat disimpan maupun di-Approve.
  </p>
</div>

<div class="page-break"></div>

<div class="section-title">5. Alur Status & Eksekusi Mutasi Stok</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">A</span> Simpan (Draft) vs Approval (Eksekusi Penambahan Stok)
  </div>
  <table class="guide-table">
    <thead>
      <tr>
        <th width="20%">Tombol / Aksi</th>
        <th width="20%">Status</th>
        <th width="60%">Dampak Terhadap Persediaan Gudang</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Simpan</strong></td>
        <td><span class="badge-code" style="background:#64748b; color:#fff;">Draft (0)</span></td>
        <td>Dokumen tersimpan sementara. <strong>Stok gudang belum bertambah</strong>. Data masih dapat diedit/disesuaikan dan tidak diblokir oleh batas kuota sisa transfer.</td>
      </tr>
      <tr>
        <td><strong>Approval</strong></td>
        <td><span class="badge-code" style="background:#0284c7; color:#fff;">Approved (1)</span></td>
        <td>Dokumen disahkan & dikunci permanen. <strong>Stok di gudang tujuan resmi bertambah</strong> pada kartu stok persediaan (Stock Card) serta mencatat kemajuan tracking SPK/WO produksi. Validasi batas kuota wajib terpenuhi.</td>
      </tr>
      <tr>
        <td><strong>Batalkan Approve</strong></td>
        <td><span class="badge-code" style="background:#ef4444; color:#fff;">Batal Approve</span></td>
        <td>Khusus Superadmin/Admin: Mengembalikan status dokumen ke Draft, membalik penambahan saldo kartu persediaan, serta mengembalikan kuota tracking produksi ke posisi semula.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">B</span> Cetak Dokumen Penerimaan & Faktur
  </div>
  <p style="margin: 4px 0 0 0;">
    Pada tabel daftar transaksi utama, klik menu dropdown <strong>Aksi</strong> pada baris terkait untuk mencetak:
  </p>
  <ul style="margin: 6px 0 0 0; padding-left: 20px; font-size: 10pt;">
    <li><strong>Print Surat Masuk Barang:</strong> Bukti fisik tanda terima resmi barang masuk gudang untuk arsip mandor gudang & supir pengantar (A4 Landscape).</li>
    <li><strong>Print Faktur Penerimaan:</strong> Lembar rekap finansial yang memuat rincian item, kuantitas, harga, dan total amount untuk verifikasi bagian keuangan/akuntansi (A4 Portrait).</li>
  </ul>
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
      <td><strong>Filter Perusahaan</strong></td>
      <td>Menampilkan transaksi penerimaan khusus per unit usaha (PT Citra Jaya Knitting / Citra Knitt).</td>
    </tr>
    <tr>
      <td><strong>Filter Tanggal (From - To)</strong></td>
      <td>Membatasi data penerimaan berdasarkan rentang periode tanggal kedatangan barang.</td>
    </tr>
    <tr>
      <td><strong>Export Excel</strong></td>
      <td>Mengunduh laporan rekapitulasi data barang masuk secara terstruktur ke spreadsheet Microsoft Excel.</td>
    </tr>
    <tr>
      <td><strong>Pencarian Cepat (Live Search)</strong></td>
      <td>Ketikkan No. Transaksi, No. Transfer, Nama Gudang, CMT, atau Nama Barang pada kotak pencarian instan.</td>
    </tr>
  </tbody>
</table>

<div class="alert-tip">
  💡 <strong>Tips & Best Practice Operasional Gudang:</strong><br>
  - Selalu pastikan fisik barang yang datang telah dicek kualitasnya dan ditimbang sebelum mengklik tombol <strong>Approval</strong>.<br>
  - Penggunaan fitur <strong>Timbangan Digital (COM Port)</strong> sangat direkomendasikan pada penerimaan kain hasil rajut untuk memastikan gramasi fisik sesuai dengan catatan CMT pengirim.<br>
  - Jika dokumen pengiriman dari pihak asal belum lengkap, simpan transaksi sebagai <strong>Draft</strong> terlebih dahulu agar pekerjaan fisik gudang tetap berjalan lancar.
</div>

<?= $this->endSection('guide_content'); ?>
