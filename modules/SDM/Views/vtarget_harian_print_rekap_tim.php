<?php helper('utils_helper'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($titlehead) ?></title>
  <style>
    @page {
      size: A4 landscape;
      margin: 6mm 6mm 6mm 6mm;
    }
    *, *::before, *::after {
      box-sizing: border-box;
    }
    html, body {
      width: 100%;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 8.5px;
      color: #111;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .print-container {
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
      padding-right: 1.5px;
    }
    .no-print {
      margin-bottom: 12px;
      padding: 8px 15px;
      background: #f1f5f9;
      border-radius: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-print {
      background: #0284c7;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      font-size: 12px;
    }
    .btn-back {
      background: #64748b;
      color: #fff;
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 12px;
    }
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        margin: 0 !important;
        padding: 0 !important;
      }
      .print-container {
        width: 100% !important;
        padding-right: 1.5px !important;
      }
      .matrix-table th:last-child, .matrix-table td:last-child {
        border-right: 1.5px solid #000 !important;
      }
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .header-table td {
      padding: 2px 2px;
      font-size: 10.5px;
    }
    .title {
      font-size: 13.5px;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
      margin-bottom: 2px;
      letter-spacing: 0.5px;
    }
    .sub-title {
      font-size: 10.5px;
      text-align: center;
      margin-bottom: 6px;
      color: #333;
      font-weight: bold;
    }
    .matrix-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: auto;
      margin-bottom: 8px;
      border: 1px solid #000;
    }
    .matrix-table th, .matrix-table td {
      border: 1px solid #1e293b;
      padding: 2.5px 1.5px;
      font-size: 8px;
      vertical-align: middle;
    }
    .matrix-table th:last-child, .matrix-table td:last-child {
      border-right: 1.5px solid #000 !important;
    }
    .matrix-table th {
      background-color: #f1b500 !important;
      color: #000;
      font-weight: bold;
      text-align: center;
    }
    .matrix-table .th-proc {
      background-color: #fef08a !important;
      font-weight: bold;
      font-size: 7.5px;
      padding: 2px 0px;
      text-align: center;
      white-space: nowrap;
      letter-spacing: -0.3px;
    }
    .matrix-table .th-summary {
      background-color: #e2e8f0 !important;
      font-weight: bold;
      text-align: center;
      font-size: 8px;
      padding: 2px 1px;
      white-space: nowrap;
    }
    .cell-num {
      text-align: right !important;
      padding-right: 2px !important;
      font-weight: 600;
      white-space: nowrap;
      font-size: 7.5px;
    }
    .cell-zero {
      text-align: center;
      color: #94a3b8;
      font-size: 7.5px;
    }
    .row-total-footer {
      background-color: #f8fafc;
      font-weight: bold;
    }
    .row-total-footer td {
      border-top: 2px solid #000;
      font-weight: bold;
    }
    .summary-box {
      border: 1px solid #475569;
      padding: 5px 8px;
      border-radius: 4px;
      background: #f8fafc;
      margin-bottom: 10px;
      font-size: 9px;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }
    .ttd-table td {
      text-align: center;
      font-size: 9.5px;
      padding: 2px;
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <strong>Laporan Rekapitulasi Output Tim:</strong> Periode <?= esc($from_raw) ?> s/d <?= esc($to_raw) ?>
    </div>
    <div>
      <a href="<?= base_url('sdm/target-harian') ?>" class="btn-back">&larr; Kembali</a>
      <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>
  </div>

  <div class="print-container">
  <table class="header-table">
    <tr>
      <td width="30%" style="font-weight: bold; font-size: 13px;">CITRA JAYA KNITTING</td>
      <td width="40%" class="title">REKAPITULASI OUTPUT PRODUKSI NON-CMT</td>
      <td width="30%" style="text-align: right; font-weight: bold;">
        Divisi: ALL NON-CMT
      </td>
    </tr>
    <tr>
      <td style="color: #555;">Pabrik Rajut & Finishing</td>
      <td class="sub-title">Periode: <?= \fdate_eng_to_ind($from_date) ?> s/d <?= \fdate_eng_to_ind($to_date) ?></td>
      <td style="text-align: right; color: #555;">
        Dicetak: <?= date('d/m/Y H:i') ?>
      </td>
    </tr>
  </table>

  <table class="matrix-table">
    <thead>
      <tr>
        <th rowspan="2" width="2.5%">NO</th>
        <th rowspan="2" width="11%">NAMA PEGAWAI</th>
        <th rowspan="2" width="6.5%">POSISI</th>
        <th colspan="<?= count($proses_list) ?>" style="background-color: #eab308 !important;">
          OUTPUT PER JENIS PROSES PRODUKSI (PCS)
        </th>
        <th colspan="3" class="th-summary">TOTAL CAPAIAN PERIODE</th>
      </tr>
      <tr>
        <?php foreach ($proses_list as $p): ?>
          <th class="th-proc" title="<?= esc($p->nama_proses) ?>">
            <?= esc($p->kode_proses) ?>
          </th>
        <?php endforeach; ?>
        <th width="4.5%" class="th-summary" style="color: #047857;">TOTAL REAL</th>
        <th width="4.5%" class="th-summary">TARGET</th>
        <th width="4%" class="th-summary">% CAPAIAN</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($pegawai_list)): ?>
        <?php foreach ($pegawai_list as $idx => $emp): ?>
          <?php 
            $pct = (float)$emp->persentase;
            $colorPct = ($pct >= 100) ? '#047857' : (($pct >= 80) ? '#b45309' : '#b91c1c');
          ?>
          <tr>
            <td style="text-align: center;"><?= $idx + 1 ?></td>
            <td style="font-weight: bold; text-align: left; padding-left: 5px;">
              <?= esc($emp->full_name) ?>
              <div style="font-size: 7.5px; color: #64748b; font-weight: normal;"><?= esc($emp->nip) ?></div>
            </td>
            <td style="text-align: left; padding-left: 5px; font-size: 8.5px; color: #334155;">
              <?= esc($emp->posisi) ?>
            </td>

            <?php foreach ($proses_list as $p): ?>
              <?php 
                $val = $emp->proses[$p->kode_proses] ?? 0;
              ?>
              <?php if ($val > 0): ?>
                <td class="cell-num"><?= (float)$val ?></td>
              <?php else: ?>
                <td class="cell-zero">-</td>
              <?php endif; ?>
            <?php endforeach; ?>

            <td class="cell-num" style="color: #047857; font-weight: bold; font-size: 10px; background: #f0fdf4;">
              <?= (float)$emp->total_realisasi ?>
            </td>
            <td class="cell-num" style="font-weight: bold; font-size: 10px; background: #fefce8;">
              <?= (float)$emp->total_target ?>
            </td>
            <td class="cell-num" style="color: <?= $colorPct ?>; font-weight: bold; font-size: 9.5px; background: #f0f9ff;">
              <?= $pct ?>%
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="<?= count($proses_list) + 6 ?>" style="text-align: center; padding: 20px; color: #64748b;">
            Tidak ada transaksi target/realisasi output pada periode tanggal ini.
          </td>
        </tr>
      <?php endif; ?>

      <!-- FOOTER TOTAL PER PROSES -->
      <tr class="row-total-footer">
        <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 8px; font-size: 9.5px; background: #e2e8f0;">
          TOTAL PER PROSES (PCS)
        </td>
        <?php foreach ($proses_list as $p): ?>
          <?php 
            $totP = $proses_totals[$p->kode_proses] ?? 0;
          ?>
          <td class="cell-num" style="background: #fef9c3; font-size: 9px;">
            <?= $totP > 0 ? (float)$totP : '-' ?>
          </td>
        <?php endforeach; ?>

        <td class="cell-num" style="color: #047857; font-size: 10.5px; background: #dcfce7;">
          <?= (float)$grand_real ?>
        </td>
        <td class="cell-num" style="font-size: 10.5px; background: #fef08a;">
          <?= (float)$grand_target ?>
        </td>
        <?php 
          $colorGrandPct = ($grand_pct >= 100) ? '#047857' : (($grand_pct >= 80) ? '#b45309' : '#b91c1c');
        ?>
        <td class="cell-num" style="color: <?= $colorGrandPct ?>; font-size: 10px; background: #bae6fd;">
          <?= $grand_pct ?>%
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Ringkasan Eksekutif -->
  <div class="summary-box">
    <div style="display: flex; justify-content: space-between;">
      <div>
        <strong>Total Pegawai Aktif Periode Ini:</strong> <?= count($pegawai_list) ?> Orang
      </div>
      <div>
        <strong>Grand Total Realisasi:</strong> <span style="color: #047857; font-weight: bold;"><?= number_format($grand_real) ?> Pcs</span>
      </div>
      <div>
        <strong>Grand Total Target:</strong> <?= number_format($grand_target) ?> Pcs
      </div>
      <div>
        <strong>Rata-Rata Capaian Pabrik:</strong> <strong style="color: <?= $colorGrandPct ?>;"><?= $grand_pct ?>%</strong>
      </div>
    </div>
  </div>

  <!-- Tanda Tangan -->
  <table class="ttd-table">
    <tr>
      <td width="33%">Dibuat Oleh,<br><br><br><br><strong>( Admin Produksi / SDM )</strong></td>
      <td width="34%">Diperiksa Oleh,<br><br><br><br><strong>( Kepala Bagian / QC )</strong></td>
      <td width="33%">Disetujui Oleh,<br><br><br><br><strong>( Pimpinan / Manajer )</strong></td>
    </tr>
  </table>
  </div>

</body>
</html>
