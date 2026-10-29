<?= $this->extend('layout/guide_template'); ?>

<?= $this->section('guide_content'); ?>

<div class="section-title">1. Gambaran Umum & Lokasi Menu</div>
<p>
  Modul <strong>Item Transfer</strong> berfungsi untuk mengelola dan mencatat proses mutasi/pemindahan barang antar gudang dalam ekosistem PT. Citra Jaya Knitting. Modul ini dilengkapi dengan <strong>1 Form Header</strong> dan <strong>2 Tabel Pengisian Terintegrasi</strong> (Tabel Hasil Produksi/WO & Tabel Bahan Baku Benang/Aksesoris) agar pemindahan barang jadi maupun bahan baku tercatat rapi pada kartu persediaan (<em>Stock Card</em>).
</p>
<div class="step-card">
  <strong>📍 Lokasi Menu di Aplikasi:</strong><br>
  Sidebar Kiri &rarr; <strong>Persediaan & Produksi</strong> &rarr; <strong>Transfer Barang / Item Transfer</strong> (URL: <span class="badge-code">https://citraknitt.com/trans/item-transfer</span>).
</div>

<div class="section-title">2. Kategori & Tipe Transfer Gudang</div>
<div class="row" style="margin-bottom: 15px;">
  <div class="col-md-6">
    <div class="step-card" style="border-left: 4px solid #0284c7; height: 100%;">
      <strong style="color: #0369a1; font-size: 11pt;">🏢 1. Transfer NON-CMT (Internal Pabrik)</strong>
      <p style="margin: 6px 0 0 0; font-size: 10pt; color: #475569;">
        Pemindahan barang antar departemen internal pabrik, misalnya dari <strong>Gudang Bahan Baku (Benang)</strong> ke <strong>Gudang Produksi</strong>, atau pengiriman kain hasil rajut ke gudang finishing/gudang jadi.
      </p>
    </div>
  </div>
  <div class="col-md-6">
    <div class="step-card" style="border-left: 4px solid #f59e0b; height: 100%;">
      <strong style="color: #b45309; font-size: 11pt;">🚚 2. Transfer CMT (Makloon Eksternal)</strong>
      <p style="margin: 6px 0 0 0; font-size: 10pt; color: #475569;">
        Pengiriman bahan baku/kain ke vendor makloon (CMT Luar) untuk pengerjaan rajut/jahit/steam. Dilengkapi pencatatan operator CMT dan no. referensi pesanan.
      </p>
    </div>
  </div>
</div>

<div class="section-title">3. Penjelasan Rinci Struktur Form & 2 Tabel Pengisian</div>

<!-- BAGIAN HEADER -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">A</span> Bagian Header (Informasi Utama Transaksi Transfer)
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Header memuat informasi umum rute transfer dan jenis proses operasional:
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
        <td><strong>Trans No. (No. Transaksi)</strong></td>
        <td><span class="badge-code">Otomatis / Readonly</span></td>
        <td>Nomor unik dokumen transfer (format: <span class="badge-code">TRF260800001</span> untuk P1 atau <span class="badge-code">TRFC...</span> untuk P2) yang dibuat otomatis oleh sistem sebagai nomor registrasi arsip.</td>
      </tr>
      <tr>
        <td><strong>Date (Tanggal)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Tanggal pelaksanaan fisik mutasi barang antar gudang. Default adalah tanggal hari ini.</td>
      </tr>
      <tr>
        <td><strong>Transfer From (Gudang Asal)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>
          Gudang sumber tempat barang dikeluarkan (stok fisik pada gudang ini akan <strong>berkurang</strong> saat Approved).<br>
          <em>*Sistem secara cerdas memilih gudang default sesuai perusahaan login pengguna (P1: Gudang Utama, P2: CITRA KNITT), namun tetap bebas diganti jika diperlukan.</em>
        </td>
      </tr>
      <tr>
        <td><strong>Transfer To (Gudang Tujuan)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Gudang penerima tempat barang dituju (stok fisik pada gudang ini akan <strong>bertambah</strong> saat Approved).</td>
      </tr>
      <tr>
        <td><strong>Proses</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Memilih tahapan proses produksi terkait (misal: <em>Rajut DBTK, Lingking, Obras, Washing & Drying, Steam, Jahit Label, QC & Packing</em>).</td>
      </tr>
      <tr>
        <td><strong>CMT (Operator / Vendor)</strong></td>
        <td><span class="badge-code">Kondisional</span></td>
        <td>Aktif otomatis jika Gudang Tujuan bertipe CMT untuk memilih nama vendor/operator makloon penerima bahan (contoh: <em>EDW / RAJUT PANYILEUKAN, UJU, EDW LINGKING, HENI, dll.</em>).</td>
      </tr>
      <tr>
        <td><strong>Proses Produksi (Checkbox)</strong></td>
        <td><span class="badge-code">Opsi Form</span></td>
        <td>Jika dicentang, sistem memfokuskan transfer pada tabel produk/hasil produksi; jika uncheck, tabel detail bahan baku akan aktif secara penuh.</td>
      </tr>
      <tr>
        <td><strong>Desc (Keterangan)</strong></td>
        <td><span class="badge-code">Opsional</span></td>
        <td>Catatan uraian keperluan transfer, nama supir pengantar, nomor kendaraan, atau instruksi khusus untuk gudang penerima.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- TABEL 1 -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">B</span> TABEL 1 : Rincian Produk / Sales Order / Kain Hasil Produksi
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Tabel pertama ini digunakan untuk mencatat kain/produk hasil rajut atau pakaian jadi berdasarkan pesanan Sales Order / SPK yang ditransfer:
  </p>
  
  <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:6px; padding:10px; margin-bottom:12px; font-size:9.5pt;">
    <strong>Fitur Cepat Tabel 1:</strong><br>
    - <strong>Scan Barcode / QR:</strong> Kotak input untuk memindai label barcode kain/produk hasil produksi secara instan.<br>
    - <strong>Tombol <span class="badge-code" style="background:#0284c7; color:#fff;">⚖️ Timbangan</span>:</strong> Mengambil pembacaan berat aktual (Kg) secara otomatis dari Timbangan Digital Serial (Yaohua YH-T3) ke kolom <em>Berat (Kg)</em>.<br>
    - <strong>Tombol <span class="badge-code" style="background:#06b6d4; color:#fff;">+</span> List SO:</strong> Membuka popup daftar Sales Order / Sample untuk ditarik ke dalam tabel transfer.
  </div>

  <table class="guide-table">
    <thead>
      <tr>
        <th width="22%">Kolom Tabel 1</th>
        <th width="18%">Metode Input</th>
        <th width="60%">Fungsi & Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Tipe</strong></td>
        <td><span class="badge-code" style="background:#e0e7ff; color:#3730a3;">Dropdown Pilihan</span></td>
        <td>
          Klasifikasi alur transfer barang:<br>
          • <span class="badge-code" style="background:#22c55e; color:#fff;">PRODUKSI</span> : Alur proses produksi normal pesanan.<br>
          • <span class="badge-code" style="background:#eab308; color:#000;">PERBAIKAN</span> : Alur pengerjaan ulang (re-work / repair).
        </td>
      </tr>
      <tr>
        <td><strong>No. SO</strong></td>
        <td><span class="badge-code">Otomatis / Tarik SO</span></td>
        <td>Nomor Sales Order rujukan pemesanan pelanggan.</td>
      </tr>
      <tr>
        <td><strong>Style</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Kode model / style pakaian yang diproduksi.</td>
      </tr>
      <tr>
        <td><strong>Deskripsi</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Uraian spesifikasi nama barang / produk jadi.</td>
      </tr>
      <tr>
        <td><strong>Colour (Warna)</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Varian warna produk yang dipindahkan.</td>
      </tr>
      <tr>
        <td><strong>Qty Ref (Sisa Kuota)</strong></td>
        <td><span class="badge-code">Otomatis Terkunci</span></td>
        <td>Jumlah sisa kuota yang sah dan valid untuk ditransfer pada proses tersebut (mencegah over-transfer).</td>
      </tr>
      <tr>
        <td><strong>Qty (Jumlah Transfer)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>
          Jumlah unit fisik (Pcs/Lembar/Ikat) produk yang ditransfer saat ini.<br>
          <em>*Pada tahap <strong>Approval</strong>, sistem memvalidasi bahwa Qty tidak boleh melebihi Qty Ref (kecuali dicentang Status Reject). Pada tahap <strong>Draft (Simpan)</strong>, data tetap diizinkan tersimpan untuk keperluan penyusunan draf.</em>
        </td>
      </tr>
      <tr>
        <td><strong>Berat (Kg)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Timbangan / Edit</span></td>
        <td>Berat timbangan fisik aktual (dalam satuan Kg). Bisa diisi langsung via tombol <strong>⚖️ Timbangan Digital</strong>.</td>
      </tr>
      <tr>
        <td><strong>Ukuran</strong></td>
        <td><span class="badge-code">Otomatis dari SO</span></td>
        <td>Kode ukuran pakaian (contoh: <code>S</code>, <code>M</code>, <code>L</code>, <code>XL</code>, <code>ALL</code>).</td>
      </tr>
      <tr>
        <td><strong>Keterangan</strong></td>
        <td><span class="badge-code">Dapat Diedit</span></td>
        <td>Catatan baris khusus (misal: <em>2 Ikat, 1 Karung, dll.</em>).</td>
      </tr>
      <tr>
        <td><strong>Status Reject</strong></td>
        <td><span class="badge-code" style="background:#fee2e2; color:#991b1b;">Checkbox Opsi</span></td>
        <td>
          Dicentang jika barang yang dipindahkan berstatus rusak / BS / reject yang perlu dikembalikan/ditransfer khusus.<br>
          <strong>Bypass Validasi:</strong> Jika dicentang, pembatasan kuota <em>Qty &le; Qty Ref</em> otomatis <strong>di-bypass (dilewati)</strong> saat Approval sehingga retur atau mutasi barang reject tidak tertahan sisa pesanan.
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- TABEL 2 -->
<div class="step-card">
  <div class="step-header">
    <span class="step-num">C</span> TABEL 2 : Rincian Detail Bahan Baku / Benang / Material Keluar
  </div>
  <p style="margin: 4px 0 10px 0; font-size: 10pt; color: #475569;">
    Tabel kedua ini digunakan untuk mencatat pengeluaran bahan baku benang/aksesoris yang dipindahkan dari Gudang Asal ke Gudang Tujuan:
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
        <td><strong>No. Workorder (WO)</strong></td>
        <td><span class="badge-code">Popup Rujukan WO</span></td>
        <td>Memilih nomor SPK / Work Order (<span class="badge-code">WRD...</span>) agar pengeluaran benang terhubung ke perintah kerja terkait.</td>
      </tr>
      <tr>
        <td><strong>ITEM CODE & DESCRIPTION</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Dipilih</span></td>
        <td>Memilih jenis benang/material yang tersedia pada stok Gudang Asal (contoh: <span class="badge-code">CTN 32/2 MARUN</span>, <code>Poly 20s</code>).</td>
      </tr>
      <tr>
        <td><strong>QTY Remain / Exist</strong></td>
        <td><span class="badge-code">Otomatis / Panduan</span></td>
        <td>Menampilkan sisa stok aktual yang ada di Gudang Asal untuk barang & Lot terpilih, mencegah stok minus.</td>
      </tr>
      <tr>
        <td><strong>QTY (Qty Transfer)</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Wajib Diisi</span></td>
        <td>Jumlah kuantitas bahan yang dikeluarkan/ditransfer. <strong>Tidak boleh melebihi QTY Remain</strong>.</td>
      </tr>
      <tr>
        <td><strong>UNIT (Satuan)</strong></td>
        <td><span class="badge-code">Otomatis / Readonly</span></td>
        <td>Satuan kuantitas bahan baku (<code>Kg</code>, <code>Roll</code>, <code>Pcs</code>, <code>Yard</code>, <code>Cones</code>).</td>
      </tr>
      <tr>
        <td><strong>LOT NO</strong></td>
        <td><span class="badge-code" style="background:#fef08a; color:#854d0e;">Sangat Penting</span></td>
        <td>Nomor batch Lot spesifik benang. Menjamin konsistensi warna rajutan dan akurasi riwayat batch.</td>
      </tr>
      <tr>
        <td><strong>PACK NAME</strong></td>
        <td><span class="badge-code">Otomatis dari Lot</span></td>
        <td>Jenis kemasan fisik bahan (contoh: <em>Karung, Dus, Roll, Cones</em>).</td>
      </tr>
      <tr>
        <td><strong>KETERANGAN</strong></td>
        <td><span class="badge-code">Opsional</span></td>
        <td>Uraian catatan baris pengeluaran bahan baku.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<!-- BAB 4: ATURAN VALIDASI & PERHITUNGAN QTY REF -->
<div class="section-title">4. Aturan Validasi Bisnis & Rumus Perhitungan Kuota (Qty Ref)</div>

<p>
  Nilai <strong>Qty Ref (Kuantitas Referensi)</strong> adalah batas maksimal barang yang boleh ditransfer pada satu baris transaksi. Sistem secara otomatis menghitung kuota ini untuk memastikan tidak terjadi transfer ganda (<em>over-transfer</em>) dan menjamin akurasi aliran barang antar entitas perusahaan.
</p>

<div class="step-card" style="border-left: 4px solid #059669;">
  <div class="step-header">
    <span class="step-num">1</span> Aturan Validasi untuk Perusahaan 1 (PT Citra Jaya Knitting / P1)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 8px;">
    Berlaku ketika <strong>Transfer From</strong> adalah <strong>Gudang Utama</strong> atau gudang milik PT CJK:
  </p>

  <div style="margin-bottom: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px;">
    <strong style="color: #166534;">A. Jika Proses yang Dipilih adalah RAJUT (Proses Awal):</strong><br>
    <span style="font-size: 9.5pt; color: #1e293b;">
      Sistem memvalidasi sisa kuota Sales Order yang belum dikirim ke CMT Rajut:<br>
      $$\mathbf{Qty\;Ref} = \mathbf{Total\;Pesanan\;SO} - \mathbf{Total\;Transfer\;P1\;(TRF...)\;yang\;Sudah\;Dibuat}$$
      <em>Contoh: Pesanan SO = 36 pcs. Sudah dibuat TRF ke Citra Knitt 36 pcs. Maka Qty Ref = 36 - 36 = <strong>0 pcs</strong> (kuota SO sudah habis dialokasikan).</em>
    </span>
  </div>

  <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px;">
    <strong style="color: #166534;">B. Jika Proses yang Dipilih adalah SELAIN RAJUT (Proses Lanjutan: Lingking, Obras, Washing, Steam, dll.):</strong><br>
    <span style="font-size: 9.5pt; color: #1e293b;">
      Sistem memvalidasi stok fisik hasil rajut yang <strong>sudah sah diterima di gudang</strong> melalui dokumen Barang Masuk (BTM) yang berstatus Approved:<br>
      $$\mathbf{Qty\;Ref} = \mathbf{Total\;BTM\;Rajut\;(Approved)\;di\;Gudang\;P1} - \mathbf{Total\;Transfer\;P1\;Lanjutan\;yang\;Sudah\;Dibuat}$$
      <em>Contoh: Hasil rajut yang sudah diterima di gudang via BTM = 12 pcs. Sudah ditransfer ke CMT Lingking = 12 pcs. Maka Qty Ref = 12 - 12 = <strong>0 pcs</strong> (stok hasil rajut di gudang sudah habis disalurkan ke tahap berikutnya).</em>
    </span>
  </div>
</div>

<div class="step-card" style="border-left: 4px solid #d97706;">
  <div class="step-header">
    <span class="step-num">2</span> Aturan Validasi untuk Perusahaan 2 (Citra Knitt / P2)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 8px;">
    Berlaku ketika <strong>Transfer From</strong> adalah <strong>CITRA KNITT</strong> (Gudang P2). Berlaku <strong>1 Kondisi Tunggal untuk SEMUA Proses</strong>:
  </p>

  <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; padding: 10px;">
    <strong style="color: #92400e;">Rumus Sisa Barang di Citra Knitt yang Berhak Dikirim Balik ke CJK:</strong><br>
    <span style="font-size: 9.5pt; color: #1e293b;">
      $$\mathbf{Qty\;Ref\;P2} = \mathbf{Total\;Barang\;Masuk\;di\;Citra\;Knitt\;(BTMC\;Approved)} - \mathbf{Total\;Transfer\;Keluar\;(TRFC)\;dari\;Citra\;Knitt}$$
      <em>*Catatan: Jika dokumen penerimaan BTMC belum sempat di-input, sistem memiliki fallback otomatis untuk membaca total Surat Jalan Transfer (TRF...) yang dikirim P1 ke Citra Knitt.</em><br><br>
      <strong>Contoh Kasus Riil:</strong><br>
      - Citra Knitt menerima kiriman order rajut dari P1: <strong>36 pcs</strong>.<br>
      - Citra Knitt sudah merajut dan mengirim balik ke CJK: <strong>12 pcs</strong> (via dokumen <code>TRFC260800047</code>).<br>
      - Sisa barang yang masih ada di pabrik Citra Knitt dan berhak ditransfer balik ke CJK adalah: $36 - 12 = \mathbf{24\;pcs}$.
    </span>
  </div>
</div>

<div class="step-card" style="border-left: 4px solid #ef4444;">
  <div class="step-header">
    <span class="step-num">3</span> Pengecualian Validasi: Barang Rusak / Cacat (Status Reject)
  </div>
  <p style="font-size: 10pt; color: #334155; margin-bottom: 0;">
    Jika barang yang ditransfer merupakan produk BS/cacat/reject yang perlu diretur atau dipindahkan antar bagian, centang kolom <strong>Status Reject</strong> pada baris terkait di Tabel 1.<br>
    <strong>Aturan Sistem:</strong> Baris dengan <em>Status Reject = ON</em> akan secara otomatis <strong>melewati (bypass)</strong> batas kuota $Qty \le Qty\;Ref$ saat dokumen di-Approval. Hal ini memastikan proses mutasi barang retur/reject tidak terhalang sisa alokasi pesanan reguler.
  </p>
</div>

<div class="step-card" style="border-left: 4px solid #6366f1;">
  <div class="step-header">
    <span class="step-num">4</span> Tanya Jawab Validasi & Troubleshooting Lapangan (FAQ)
  </div>
  <table class="guide-table">
    <thead>
      <tr>
        <th width="35%">Pertanyaan / Kendala</th>
        <th width="65%">Penyebab & Solusi Tindakan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Kenapa Qty Ref bernilai 0.00 saat ingin transfer ke Lingking/Obras (P2)?</strong></td>
        <td>
          1. <strong>Cek BTM Hasil Rajut:</strong> Pastikan CMT Rajut sudah mengirim barang dan pihak Gudang sudah membuat & meng-<strong>Approve</strong> dokumen BTM-nya.<br>
          2. <strong>Cek Dropdown Proses:</strong> Pastikan dropdown <em>Proses</em> di header form sudah diubah ke <strong>LINGKING - OBRAS</strong> (jangan dibiarkan <em>RAJUT DBTK</em>).
        </td>
      </tr>
      <tr>
        <td><strong>Kenapa di modal pemilihan SO kolom Qty awal tidak ditampilkan lagi?</strong></td>
        <td>
          Tampilan disederhanakan agar operator fokus pada <strong>Qty Ref (Sisa Kuota yang Siap Kirim)</strong> dan tidak keliru mengira total order awal sebagai jumlah yang akan ditransfer hari ini.
        </td>
      </tr>
      <tr>
        <td><strong>Apakah gudang asal bisa diganti jika default-nya tidak sesuai?</strong></td>
        <td>
          <strong>Bisa.</strong> Dropdown <em>Transfer From</em> tetap aktif dan terbuka. Jika operator ingin memindahkan barang dari gudang lain, cukup pilih nama gudang yang diinginkan pada dropdown.
        </td>
      </tr>
    </tbody>
  </table>
</div>

<div class="page-break"></div>

<div class="section-title">5. Alur Status Dokumen & Eksekusi Mutasi Stok</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">A</span> Simpan (Draft) vs Approval (Eksekusi Mutasi Stok)
  </div>
  <table class="guide-table">
    <thead>
      <tr>
        <th width="20%">Tombol / Aksi</th>
        <th width="20%">Status</th>
        <th width="60%">Dampak Terhadap Persediaan & Sistem</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Simpan</strong></td>
        <td><span class="badge-code" style="background:#64748b; color:#fff;">Draft (0)</span></td>
        <td>Dokumen disimpan sebagai draf sementara. <strong>Stok fisik belum berpindah</strong>. Data masih dapat diedit dan disesuaikan. Validasi kuota <em>Qty &le; Qty Ref</em> tidak mengunci tahap Draft.</td>
      </tr>
      <tr>
        <td><strong>Simpan & Cetak</strong></td>
        <td><span class="badge-code" style="background:#d97706; color:#fff;">Draft (0) + Cetak</span></td>
        <td>Shortcut praktis untuk menyimpan dokumen sebagai draf sekaligus langsung membuka halaman cetak Surat Jalan dalam tab baru tanpa perlu kembali ke daftar utama.</td>
      </tr>
      <tr>
        <td><strong>Approval</strong></td>
        <td><span class="badge-code" style="background:#0284c7; color:#fff;">Approved (1)</span></td>
        <td>Dokumen disahkan dan terkunci permanen. <strong>Stok gudang asal otomatis dipotong dan stok gudang tujuan otomatis bertambah</strong> pada kartu persediaan (Stock Card). Validasi kuota <em>Qty &le; Qty Ref</em> wajib lolos (kecuali baris Reject).</td>
      </tr>
      <tr>
        <td><strong>Batalkan Approve</strong></td>
        <td><span class="badge-code" style="background:#ef4444; color:#fff;">Batal Approve</span></td>
        <td>Khusus Superadmin/Admin: Mengembalikan status dokumen ke Draft dan mengembalikan mutasi saldo kartu persediaan ke posisi semula secara tertib.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">B</span> Cetak Surat Jalan / Bukti Transfer
  </div>
  <p style="margin: 4px 0 0 0;">
    Klik tombol <strong>Print</strong> pada baris transaksi di halaman utama untuk mencetak <em>Surat Jalan Pengantar Barang / Bukti Mutasi Antar Gudang</em> resmi (A4 Landscape) sebagai dokumen fisik tanda terima sopir dan mandor gudang.
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
      <td><strong>Filter Perusahaan</strong></td>
      <td>Memfilter data transaksi berdasarkan kepemilikan gudang asal/tujuan per unit perusahaan.</td>
    </tr>
    <tr>
      <td><strong>Rentang Tanggal (Date Range)</strong></td>
      <td>Masukkan <em>From Date</em> dan <em>To Date</em> untuk membatasi periode laporan transaksi.</td>
    </tr>
    <tr>
      <td><strong>Export: Type Monitoring</strong></td>
      <td>Menghasilkan rekapitulasi ringkas per nomor dokumen transfer (Header Level).</td>
    </tr>
    <tr>
      <td><strong>Export: Type Transaksi</strong></td>
      <td>Menghasilkan rincian detail lengkap per item, kode warna, nomor lot, qty, dan harga satuan (Item Detail Level).</td>
    </tr>
    <tr>
      <td><strong>Live Search</strong></td>
      <td>Ketikkan No. Transaksi, Nama Gudang, atau Nama CMT untuk pencarian instan pada tabel.</td>
    </tr>
  </tbody>
</table>

<div class="alert-tip">
  💡 <strong>Aturan Keamanan & Best Practice:</strong><br>
  - Pastikan fisik barang sudah diperiksa dan ditimbang sebelum menekan tombol <strong>Approval</strong>.<br>
  - Penggunaan fitur <strong>Timbangan Digital (COM Port)</strong> sangat direkomendasikan pada penimbangan kain hasil rajut di Tabel 1 untuk menghindari selisih gramasi.<br>
  - Untuk transaksi antar perusahaan (P1 & P2), pastikan penerima barang selalu melakukan input dokumen <strong>Barang Masuk (BTM/BTMC)</strong> agar aliran stok kedua belah pihak tetap seimbang dan sinkron.
</div>

<?= $this->endSection('guide_content'); ?>
