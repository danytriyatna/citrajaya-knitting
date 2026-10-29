<?= $this->extend('layout/guide_template'); ?>

<?= $this->section('guide_content'); ?>

<div class="section-title">1. Gambaran Umum & Lokasi Menu</div>
<p>
  Modul <strong>Target & Realisasi Harian</strong> dirancang khusus untuk memonitor produktivitas harian tenaga kerja internal pabrik (QC, Finishing, Mesin Rajut, Packing, Lingking, dll.). Sistem ini menggantikan pencatatan kertas fisik dengan <em>Matrix Grid</em> interaktif per jam yang otomatis menghitung total output, persentase capaian target, dan breakdown per jenis pekerjaan.
</p>
<div class="step-card">
  <strong>📍 Lokasi Menu di Aplikasi:</strong><br>
  Sidebar Kiri &rarr; <strong>Persediaan & Produksi</strong> &rarr; <strong>Target & Realisasi Harian</strong> (Tepat di atas menu <em>Work Order</em>).
</div>

<div class="section-title">2. Alur Kerja Harian (Daily Workflow)</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">1</span> Pagi Hari: Penugasan Target Harian (Draft)
  </div>
  <p style="margin: 4px 0 0 0;">
    Admin / Kepala Bagian membuka form <strong>Tambah Target</strong>, sistem otomatis memuat 32 pegawai Non-CMT aktif. Admin mengisi angka target (misal: <span class="badge-code">100</span> Pcs) pada kolom <strong>Target (Qty)</strong> untuk setiap operator yang bertugas hari itu. Status dokumen dibiarkan <strong>Draft / Berjalan</strong> lalu klik <strong>Simpan Data Matrix</strong>.
  </p>
</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">2</span> Siang s/d Sore: Input Realisasi Jam-jaman (Hourly Output)
  </div>
  <p style="margin: 4px 0 0 0;">
    Admin / QC menginput output produksi pada kolom jam kerja yang bersangkutan (misal: <span class="badge-code">08.00-09.00</span>, <span class="badge-code">09.00-10.00</span>, dst.). Sistem mendukung pengetikan multi-proses cepat.
  </p>
</div>

<div class="step-card">
  <div class="step-header">
    <span class="step-num">3</span> Akhir Shift: Finalisasi & Cetak Laporan
  </div>
  <p style="margin: 4px 0 0 0;">
    Setelah seluruh jam kerja terisi dan diverifikasi, ubah status dokumen menjadi <strong>Final / Selesai Shift</strong>, kemudian simpan. Dokumen berstatus final akan terkunci dari penghapusan dan siap dicetak/diarsipkan.
  </p>
</div>

<div class="page-break"></div>

<div class="section-title">3. Aturan Format Input Multi-Proses (Sangat Penting)</div>
<p>
  Seorang operator dalam 1 jam dapat mengerjakan beberapa aktivitas berbeda (misal: QC, Packing, Jahit). Kolom jam-jaman dilengkapi <strong>Smart Parser</strong> yang otomatis menjumlahkan nominalnya dengan aturan berikut:
</p>

<table class="guide-table">
  <thead>
    <tr>
      <th width="30%">Format Pengetikan</th>
      <th width="35%">Contoh Input di Sel Jam</th>
      <th width="35%">Hasil Perhitungan Sistem</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Angka Tunggal</strong></td>
      <td><span class="badge-code">50</span></td>
      <td>Total Realisasi = <strong>50</strong> (Output biasa)</td>
    </tr>
    <tr>
      <td><strong>Satu Proses</strong></td>
      <td><span class="badge-code">K=10</span> atau <span class="badge-code">QC:25</span> atau <span class="badge-code">P-30</span></td>
      <td>Total Realisasi = <strong>10</strong> / <strong>25</strong> / <strong>30</strong></td>
    </tr>
    <tr>
      <td><strong>Multi-Proses (Pemisah Koma)</strong></td>
      <td><span class="badge-code">K=10, P=20, Qc=26</span></td>
      <td>Total Realisasi = <strong>56</strong><br><small style="color: #64748b;">Breakdown: K=10 | P=20 | QC=26</small></td>
    </tr>
  </tbody>
</table>

<div class="alert-tip">
  💡 <strong>Tips Kecepatan Input (Keyboard Navigation):</strong><br>
  Saat mengisi tabel matrix, Anda dapat menekan tombol <strong>Enter</strong> atau tanda panah <strong>&darr; (Bawah)</strong> dan <strong>&uarr; (Atas)</strong> pada keyboard untuk langsung berpindah antar baris operator tanpa perlu menggunakan mouse!
</div>

<div class="section-title">4. Fitur Cerdas & Visual Indikator</div>
<ul>
  <li><strong>Live Realtime Sum & % Capaian:</strong> Total realisasi dan % capaian terhitung seketika tanpa perlu reload halaman.</li>
  <li><strong>Warna Badge Capaian:</strong>
    <ul>
      <li>🟢 <strong>Hijau (&ge; 100%):</strong> Target tercapai atau melebihi target.</li>
      <li>🟡 <strong>Kuning (80% - 99%):</strong> Mendekati target.</li>
      <li>🔴 <strong>Merah (&lt; 80%):</strong> Belum memenuhi target minimal shift.</li>
    </ul>
  </li>
  <li><strong>Panel Rekapitulasi Shift (Bawah):</strong> Menampilkan ringkasan total produksi per jenis kode (contoh: <span class="badge-code">QC: 450 Pcs</span>, <span class="badge-code">Packing: 380 Pcs</span>, <span class="badge-code">Jahit: 230 Pcs</span>).</li>
</ul>

<div class="section-title">5. Cetak / Export Dokumen PDF</div>
<p>
  Untuk mencetak lembar kerja fisik atau menyimpan arsip digital:
</p>
<ol>
  <li>Klik tombol <strong>🖨️ Cetak / PDF</strong> (tersedia di form input maupun di dropdown aksi halaman list).</li>
  <li>Sistem akan membuka format resmi lembar kerja pabrik (A4 Landscape).</li>
  <li>Karyawan yang targetnya bernilai <strong>0 / kosong otomatis disembunyikan</strong> dari lembar cetak agar tampilan pas dan rapi.</li>
  <li>Klik tombol <strong>Cetak / Simpan PDF</strong> di pojok kanan atas preview untuk mencetak ke printer atau menyimpan sebagai PDF.</li>
</ol>

<?= $this->endSection('guide_content'); ?>
