<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($titlehead) ?></title>
  <style>
    @page {
      size: A4 <?= ($printType == 1) ? 'portrait' : 'landscape' ?>;
      margin: 15mm 10mm 15mm 10mm;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      color: #333;
      margin: 15px;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    .header-table td {
      padding: 3px 6px;
      font-size: 11px;
    }
    .title {
      font-size: 15px;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
      margin-bottom: 3px;
      letter-spacing: 0.5px;
    }
    .sub-title {
      font-size: 12px;
      text-align: center;
      margin-bottom: 15px;
      color: #555;
      font-weight: bold;
    }
    .content-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }
    .content-table th, .content-table td {
      border: 1px solid #444;
      padding: 5px 6px;
      font-size: 10.5px;
    }
    .content-table th {
      background-color: #EAECEF !important;
      text-align: center;
      font-weight: bold;
      text-transform: uppercase;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-bold { font-weight: bold; }
    .signatures {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
      page-break-inside: avoid;
    }
    .signatures td {
      text-align: center;
      vertical-align: top;
      font-size: 11px;
    }
    .sign-space {
      height: 65px;
    }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      font-size: 10px;
      font-weight: bold;
      border-radius: 3px;
    }
    .total-row {
      background-color: #F8F9FA !important;
      font-weight: bold;
    }
    @media print {
      body { margin: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">

  <!-- Tombol Aksi Layar (Tidak Tercetak) -->
  <div class="no-print" style="margin-bottom: 15px; background: #eef2f7; padding: 10px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
    <div>
      <b>Mode Cetak:</b> <?= ($printType == 1) ? '<span style="color:#007bff; font-weight:bold;">SPK Penugasan Lembur</span>' : '<span style="color:#28a745; font-weight:bold;">Laporan Realisasi & Perhitungan Jam Lembur</span>' ?>
    </div>
    <div>
      <a href="?type=spk" style="padding: 5px 12px; background: <?= ($printType == 1) ? '#007bff' : '#6c757d' ?>; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; margin-right: 5px;">
        🖨️ Cetak SPK Penugasan
      </a>
      <a href="?type=rekap" style="padding: 5px 12px; background: <?= ($printType == 2) ? '#28a745' : '#6c757d' ?>; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; margin-right: 10px;">
        🖨️ Cetak Rekap Realisasi
      </a>
      <button onclick="window.print()" style="padding: 5px 12px; background: #343a40; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
        Print Sekarang
      </button>
    </div>
  </div>

  <?php if ($printType == 1): ?>
  <!-- ========================================================================= -->
  <!-- 1. CETAK 1: SURAT PERINTAH KERJA (SPK) PENUGASAN LEMBUR                   -->
  <!-- ========================================================================= -->
  <div class="title">SURAT PERINTAH KERJA (SPK) PENUGASAN LEMBUR</div>
  <div class="sub-title"><?= esc($resData->nama_perusahaan ?: 'PT CITRA JAYA KNITTING') ?></div>

  <table class="header-table">
    <tr>
      <td style="width: 16%;" class="font-bold">No. Dokumen SPK</td>
      <td style="width: 34%;">: <b><?= esc($resData->kode_lembur) ?></b></td>
      <td style="width: 16%;" class="font-bold">Status Dokumen</td>
      <td style="width: 34%;">: 
        <?php 
          if ($resData->status == 0) echo "Draft";
          else echo "Disetujui Penugasan";
        ?>
      </td>
    </tr>
    <tr>
      <td class="font-bold">Tanggal Lembur</td>
      <td>: <b><?= fdate_eng_to_ind($resData->tanggal) ?></b></td>
      <td class="font-bold">Tanggal Cetak</td>
      <td>: <?= date('d/m/Y H:i') ?></td>
    </tr>
    <tr>
      <td class="font-bold">Keterangan / Kegiatan</td>
      <td colspan="3">: <?= esc($resData->keterangan) ?: '-' ?></td>
    </tr>
  </table>

  <table class="content-table">
    <thead>
      <tr>
        <th style="width: 5%;">NO</th>
        <th style="width: 15%;">NIP</th>
        <th style="width: 25%;">NAMA KARYAWAN</th>
        <th style="width: 20%;">POSISI / JABATAN</th>
        <th style="width: 20%;">PEKERJAAN / TUGAS</th>
        <th style="width: 15%;">TARGET</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $no = 1;
        $totTarget = 0;
        if (!empty($resDetail)) :
          foreach ($resDetail as $row) :
            $totTarget += (float) $row->target;
      ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td class="text-center"><?= esc($row->nip) ?: '-' ?></td>
          <td><b><?= esc($row->full_name) ?></b></td>
          <td><?= esc($row->posisi) ?: '-' ?></td>
          <td><?= esc($row->pekerjaan) ?: '-' ?></td>
          <td class="text-right"><?= number_format($row->target, 0, ',', '.') ?> <?= esc($row->satuan_target) ?></td>
        </tr>
      <?php 
          endforeach;
        else: 
      ?>
        <tr>
          <td colspan="6" class="text-center">Belum ada daftar karyawan yang ditugaskan.</td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="total-row">
        <th colspan="5" class="text-center">TOTAL KARYAWAN: <?= count($resDetail ?? []) ?> ORANG</th>
        <th class="text-right"><?= number_format($totTarget, 0, ',', '.') ?></th>
      </tr>
    </tfoot>
  </table>

  <table class="signatures">
    <tr>
      <td style="width: 50%;">
        Dibuat Oleh,<br>
        <div class="sign-space"></div>
        <b>( ..................................................... )</b><br>
        <span>Supervisor / Pemohon</span>
      </td>
      <td style="width: 50%;">
        Disetujui Penugasan,<br>
        <div class="sign-space"></div>
        <b>( <?= !empty($resData->approver1_name) ? esc($resData->approver1_name) : '.....................................................' ?> )</b><br>
        <span>Kepala Bagian / HRD</span>
      </td>
    </tr>
  </table>

  <?php else: ?>
  <!-- ========================================================================= -->
  <!-- 2. CETAK 2: LAPORAN REALISASI & PERHITUNGAN JAM LEMBUR                    -->
  <!-- ========================================================================= -->
  <div class="title">LAPORAN REALISASI & PERHITUNGAN JAM LEMBUR</div>
  <div class="sub-title"><?= esc($resData->nama_perusahaan ?: 'PT CITRA JAYA KNITTING') ?></div>

  <table class="header-table">
    <tr>
      <td style="width: 14%;" class="font-bold">No. Lembur</td>
      <td style="width: 36%;">: <b><?= esc($resData->kode_lembur) ?></b></td>
      <td style="width: 14%;" class="font-bold">Status Dokumen</td>
      <td style="width: 36%;">: 
        <?php 
          if ($resData->status == 2) echo "<b style='color:green;'>Disetujui Jam Lembur</b>";
          else if ($resData->status == 1) echo "Disetujui Penugasan";
          else echo "Draft";
        ?>
      </td>
    </tr>
    <tr>
      <td class="font-bold">Tanggal Lembur</td>
      <td>: <b><?= fdate_eng_to_ind($resData->tanggal) ?></b></td>
      <td class="font-bold">Tanggal Cetak</td>
      <td>: <?= date('d/m/Y H:i') ?></td>
    </tr>
    <tr>
      <td class="font-bold">Keterangan / Tugas</td>
      <td colspan="3">: <?= esc($resData->keterangan) ?: '-' ?></td>
    </tr>
  </table>

  <table class="content-table">
    <thead>
      <tr>
        <th style="width: 3%;">NO</th>
        <th style="width: 15%;">NAMA KARYAWAN</th>
        <th style="width: 12%;">PEKERJAAN / TUGAS</th>
        <th style="width: 7%;">TARGET</th>
        <th style="width: 7%;">REALISASI</th>
        <th style="width: 6%;">% CAPAI</th>
        <th style="width: 6%;">MASUK</th>
        <th style="width: 6%;">PULANG</th>
        <th style="width: 10%;">STATUS LEMBUR</th>
        <th style="width: 8%;">JAM ABSEN</th>
        <th style="width: 9%;">JAM DIHITUNG</th>
        <th style="width: 11%;">CATATAN</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $no = 1;
        $totTarget = 0;
        $totRealisasi = 0;
        $totJamAbsen = 0;
        $totJamHitung = 0;
        if (!empty($resDetail)) :
          foreach ($resDetail as $d) : 
            $totTarget += (float) $d->target;
            $totRealisasi += (float) $d->realisasi;
            $totJamAbsen += (float) $d->jam_lembur_absen;
            $totJamHitung += (float) $d->jam_lembur_dihitung;

            $stLemburText = '-';
            if ($d->status_lembur == 1 || $d->status_lembur === '1' || $d->status_lembur === 'Lembur Weekday') {
                $stLemburText = 'Lembur Weekday';
            } else if ($d->status_lembur == 2 || $d->status_lembur === '2' || $d->status_lembur === 'Lembur Weekend/Hari Libur') {
                $stLemburText = 'Lembur Weekend';
            }
      ?>
      <tr>
        <td class="text-center"><?= $no++ ?></td>
        <td>
          <b><?= esc($d->full_name) ?></b><br>
          <small style="color: #666;"><?= esc($d->nip) ?> (<?= esc($d->posisi) ?>)</small>
        </td>
        <td><?= esc($d->pekerjaan) ?: '-' ?></td>
        <td class="text-right"><?= number_format($d->target, 0, ',', '.') ?> <?= esc($d->satuan_target) ?></td>
        <td class="text-right"><?= number_format($d->realisasi, 0, ',', '.') ?> <?= esc($d->satuan_target) ?></td>
        <td class="text-center font-bold"><?= number_format($d->persentase, 1, ',', '.') ?>%</td>
        <td class="text-center"><?= !empty($d->jam_masuk) ? esc($d->jam_masuk) : '-' ?></td>
        <td class="text-center"><?= !empty($d->jam_keluar) ? esc($d->jam_keluar) : '-' ?></td>
        <td class="text-center"><?= $stLemburText ?></td>
        <td class="text-right"><?= number_format($d->jam_lembur_absen, 2, ',', '.') ?> Jam</td>
        <td class="text-right font-bold" style="color: #0056b3;"><?= number_format($d->jam_lembur_dihitung, 2, ',', '.') ?> Jam</td>
        <td><?= esc($d->catatan) ?: '-' ?></td>
      </tr>
      <?php 
          endforeach;
        else: 
      ?>
      <tr>
        <td colspan="12" class="text-center">Tidak ada data karyawan.</td>
      </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="total-row">
        <th colspan="3" class="text-center">TOTAL KESELURUHAN (<?= count($resDetail ?? []) ?> ORANG)</th>
        <th class="text-right"><?= number_format($totTarget, 0, ',', '.') ?></th>
        <th class="text-right"><?= number_format($totRealisasi, 0, ',', '.') ?></th>
        <th class="text-center">-</th>
        <th colspan="3" class="text-center">-</th>
        <th class="text-right"><?= number_format($totJamAbsen, 2, ',', '.') ?> Jam</th>
        <th class="text-right" style="color: #0056b3;"><?= number_format($totJamHitung, 2, ',', '.') ?> Jam</th>
        <th></th>
      </tr>
    </tfoot>
  </table>

  <table class="signatures">
    <tr>
      <td style="width: 33.33%;">
        Dibuat Oleh,<br>
        <div class="sign-space"></div>
        <b>( ..................................................... )</b><br>
        <span>Supervisor / Pemohon</span>
      </td>
      <td style="width: 33.33%;">
        Disetujui Penugasan,<br>
        <div class="sign-space"></div>
        <b>( <?= !empty($resData->approver1_name) ? esc($resData->approver1_name) : '.....................................................' ?> )</b><br>
        <span>Kepala Bagian</span>
      </td>
      <td style="width: 33.33%;">
        Disetujui Jam Lembur,<br>
        <div class="sign-space"></div>
        <b>( <?= !empty($resData->approver2_name) ? esc($resData->approver2_name) : '.....................................................' ?> )</b><br>
        <span>HRD / Pimpinan</span>
      </td>
    </tr>
  </table>
  <?php endif; ?>

</body>
</html>
