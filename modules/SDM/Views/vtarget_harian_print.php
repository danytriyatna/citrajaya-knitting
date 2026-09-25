<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= esc($titlehead) ?></title>
  <style>
    @page {
      size: A4 landscape;
      margin: 10mm 8mm 10mm 8mm;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 10px;
      color: #111;
      margin: 0;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .no-print {
      margin-bottom: 15px;
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
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .header-table td {
      padding: 2px 4px;
      font-size: 11px;
    }
    .title {
      font-size: 14px;
      font-weight: bold;
      text-align: center;
      text-transform: uppercase;
      margin-bottom: 2px;
      letter-spacing: 0.5px;
    }
    .sub-title {
      font-size: 11px;
      text-align: center;
      margin-bottom: 10px;
      color: #444;
      font-weight: bold;
    }
    .matrix-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .matrix-table th, .matrix-table td {
      border: 1px solid #333;
      padding: 4px 3px;
      text-align: center;
      font-size: 9.5px;
      vertical-align: middle;
    }
    .matrix-table th {
      background-color: #e2e8f0;
      font-weight: bold;
    }
    .matrix-table .th-op {
      background-color: #f1f5f9;
      font-weight: bold;
      white-space: nowrap;
      max-width: 75px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .cell-val {
      font-size: 9px;
      font-weight: 600;
      white-space: nowrap;
    }
    .row-total {
      background-color: #f8fafc;
      font-weight: bold;
    }
    .row-target {
      background-color: #fef3c7;
      font-weight: bold;
    }
    .row-pct {
      background-color: #e0f2fe;
      font-weight: bold;
    }
    .row-total td, .row-target td, .row-pct td {
      text-align: right !important;
      padding-right: 6px !important;
    }
    .row-breakdown {
      background-color: #ffffff;
      vertical-align: top;
    }
    .cell-breakdown {
      font-size: 8px;
      line-height: 1.2;
      padding: 3px 2px !important;
      vertical-align: top !important;
      background-color: #ffffff;
    }
    .table-mini-breakdown {
      width: 100%;
      border-collapse: collapse;
      border: none !important;
      font-size: 8px;
    }
    .table-mini-breakdown td {
      border: none !important;
      padding: 1px 1px !important;
      font-size: 8px !important;
    }
    .summary-box {
      border: 1px solid #475569;
      padding: 6px 10px;
      border-radius: 4px;
      background: #f8fafc;
      margin-bottom: 15px;
      font-size: 10px;
    }
    .badge-proc {
      display: inline-block;
      padding: 2px 6px;
      border: 1px solid #0284c7;
      background: #e0f2fe;
      color: #0369a1;
      border-radius: 4px;
      font-weight: bold;
      margin-right: 6px;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .ttd-table td {
      text-align: center;
      font-size: 10px;
      padding: 4px;
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <strong>Preview Cetak Dokumen Output Harian:</strong> <?= esc($header->kode_transaksi) ?>
    </div>
    <div>
      <a href="<?= base_url('sdm/target-harian') ?>" class="btn-back">&larr; Kembali</a>
      <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>
  </div>

  <table class="header-table">
    <tr>
      <td width="33%" style="font-weight: bold; font-size: 13px;">CITRA JAYA KNITTING</td>
      <td width="34%" class="title">LEMBAR CATATAN OUTPUT HARIAN OPERATOR</td>
      <td width="33%" style="text-align: right; font-weight: bold;">
        No. Dokumen: <?= esc($header->kode_transaksi) ?>
      </td>
    </tr>
    <tr>
      <td>Divisi: <?= esc($header->divisi) ?></td>
      <td class="sub-title">Hari/Tanggal: <?= \fdate_eng_to_ind($header->tanggal) ?></td>
      <td style="text-align: right;">
        Status: <?= ($header->status == 1) ? 'FINAL (SELESAI SHIFT)' : 'DRAFT' ?>
      </td>
    </tr>
  </table>

  <!-- Filter hanya karyawan yang memiliki target (> 0) atau realisasi -->
  <?php 
    $activeEmployees = array_values(array_filter($details, function($emp) {
        return (float)$emp->target_qty > 0 || (float)$emp->total_realisasi > 0;
    }));

    // Jika belum ada yang diisi target/realisasi sama sekali, fallback ke semua
    if (empty($activeEmployees)) {
        $activeEmployees = $details;
    }
  ?>

  <table class="matrix-table">
    <thead>
      <tr>
        <th rowspan="2" width="7%">HARI / TGL</th>
        <th rowspan="2" width="8%">JAM</th>
        <th colspan="<?= count($activeEmployees) ?>" style="text-transform: uppercase;">
          OPERATOR & PEGAWAI NON-CMT
        </th>
      </tr>
      <tr>
        <?php foreach ($activeEmployees as $emp): ?>
          <th class="th-op" title="<?= esc($emp->full_name) ?>">
            <?= esc(explode(' ', trim($emp->full_name))[0]) ?>
            <div style="font-size: 7.5px; color: #555; font-weight: normal;"><?= esc($emp->posisi) ?></div>
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php 
        $dayName = date('l', strtotime($header->tanggal));
        $daysIndo = [
          'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
          'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hariIndo = isset($daysIndo[$dayName]) ? $daysIndo[$dayName] : $dayName;
        $tglFmt = date('d/m/y', strtotime($header->tanggal));

        $globalBreakdown = [];
        $empBreakdown = [];
      ?>

      <?php foreach ($time_slots as $idx => $slot): ?>
        <tr>
          <?php if ($idx === 0): ?>
            <td rowspan="<?= count($time_slots) + 1 ?>" style="font-weight: bold; font-size: 11px; background: #fff;">
              <?= $hariIndo ?><br>
              <span style="font-weight: normal; font-size: 9.5px;"><?= $tglFmt ?></span>
            </td>
          <?php endif; ?>

          <td style="font-weight: bold; background: #f8fafc;"><?= $slot ?></td>

          <?php foreach ($activeEmployees as $emp): ?>
            <?php 
              $val = isset($hourlyMap[$emp->id_karyawan][$slot]) ? $hourlyMap[$emp->id_karyawan][$slot] : '';
              if ($val === '-') $val = '';

              // Collect breakdown
              if (!empty($val)) {
                $items = explode(',', $val);
                foreach ($items as $it) {
                  $it = trim($it);
                  if (preg_match('/[=:\-]/', $it)) {
                    $m = preg_split('/[=:\-]+/', $it);
                    $c = strtoupper(trim($m[0]));
                    $v = isset($m[1]) ? (float)$m[1] : 0;
                    if ($v > 0) {
                      $globalBreakdown[$c] = ($globalBreakdown[$c] ?? 0) + $v;
                      $empBreakdown[$emp->id_karyawan][$c] = ($empBreakdown[$emp->id_karyawan][$c] ?? 0) + $v;
                    }
                  } elseif (is_numeric($it)) {
                    $v = (float)$it;
                    if ($v > 0) {
                      $globalBreakdown['OUTPUT'] = ($globalBreakdown['OUTPUT'] ?? 0) + $v;
                      $empBreakdown[$emp->id_karyawan]['OUTPUT'] = ($empBreakdown[$emp->id_karyawan]['OUTPUT'] ?? 0) + $v;
                    }
                  }
                }
              }
            ?>
            <td class="cell-val" style="<?= (trim($val) !== '' && is_numeric(trim($val))) ? 'text-align: right !important; padding-right: 5px !important;' : '' ?>"><?= esc($val) ?></td>
          <?php endforeach; ?>
        </tr>

        <?php if ($slot === '11.00-12.00'): ?>
          <tr style="background: #f1f5f9; font-weight: bold; font-size: 8.5px;">
            <td style="color: #64748b;">12.00 - 13.00</td>
            <td colspan="<?= count($activeEmployees) ?>" style="color: #64748b; letter-spacing: 2px;">
              - - - [ I S T I R A H A T ] - - -
            </td>
          </tr>
        <?php endif; ?>

      <?php endforeach; ?>

      <!-- BARIS TOTAL REALISASI -->
      <tr class="row-total">
        <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL REALISASI</td>
        <?php foreach ($activeEmployees as $emp): ?>
          <td style="font-size: 10px; font-weight: bold; color: #047857; text-align: right !important; padding-right: 6px !important;">
            <?= (float)$emp->total_realisasi ?>
          </td>
        <?php endforeach; ?>
      </tr>

      <!-- BARIS TARGET -->
      <tr class="row-target">
        <td colspan="2" style="text-align: right; font-weight: bold;">TARGET HARIAN</td>
        <?php foreach ($activeEmployees as $emp): ?>
          <td style="font-size: 10px; font-weight: bold; text-align: right !important; padding-right: 6px !important;">
            <?= (float)$emp->target_qty ?>
          </td>
        <?php endforeach; ?>
      </tr>

      <!-- BARIS % PENCAPAIAN -->
      <tr class="row-pct">
        <td colspan="2" style="text-align: right; font-weight: bold;">% PENCAPAIAN</td>
        <?php foreach ($activeEmployees as $emp): ?>
          <?php 
            $pct = (float)$emp->persentase;
            $color = ($pct >= 100) ? '#047857' : (($pct >= 80) ? '#b45309' : '#b91c1c');
          ?>
          <td style="font-size: 9.5px; font-weight: bold; color: <?= $color ?>; text-align: right !important; padding-right: 6px !important;">
            <?= $pct ?>%
          </td>
        <?php endforeach; ?>
      </tr>

      <!-- BARIS TOTAL PER PROSES -->
      <tr class="row-breakdown">
        <td colspan="2" style="text-align: right; font-weight: bold; font-size: 9px; vertical-align: top; padding-top: 4px;">
          TOTAL PER PROSES
        </td>
        <?php foreach ($activeEmployees as $emp): ?>
          <td class="cell-breakdown">
            <?php if (!empty($empBreakdown[$emp->id_karyawan])): ?>
              <table class="table-mini-breakdown">
                <?php foreach ($empBreakdown[$emp->id_karyawan] as $pCode => $pQty): ?>
                  <tr>
                    <td style="text-align: left; font-weight: bold; color: #0369a1;"><?= esc($pCode) ?>:</td>
                    <td style="text-align: right; font-weight: bold; color: #1e293b;"><?= (float)$pQty ?></td>
                  </tr>
                <?php endforeach; ?>
              </table>
            <?php else: ?>
              <span style="color: #94a3b8; font-size: 8px;">-</span>
            <?php endif; ?>
          </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>

  <!-- Ringkasan Rekap Output Per Proses -->
  <div class="summary-box">
    <strong>Rekapitulasi Total Output Shift Ini:</strong>&nbsp;&nbsp;
    <?php if (!empty($globalBreakdown)): ?>
      <?php foreach ($globalBreakdown as $pCode => $pQty): ?>
        <span class="badge-proc"><?= esc($pCode) ?>: <?= number_format($pQty) ?></span>
      <?php endforeach; ?>
    <?php else: ?>
      <span style="color: #64748b;">- Tidak ada catatan output proses -</span>
    <?php endif; ?>

    <?php if (!empty($header->keterangan)): ?>
      <div style="margin-top: 5px; font-style: italic; color: #334155;">
        <strong>Catatan Khusus:</strong> <?= esc($header->keterangan) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Tanda Tangan -->
  <table class="ttd-table">
    <tr>
      <td width="33%">Dibuat Oleh,<br><br><br><br><strong>( Operator / Admin )</strong></td>
      <td width="34%">Diperiksa Oleh,<br><br><br><br><strong>( Kepala Bagian / QC )</strong></td>
      <td width="33%">Disetujui Oleh,<br><br><br><br><strong>( Pimpinan / Manajer )</strong></td>
    </tr>
  </table>

</body>
</html>