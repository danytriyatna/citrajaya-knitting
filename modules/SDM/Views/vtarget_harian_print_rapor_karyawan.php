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
      font-size: 9.5px;
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
      .page-break {
        page-break-after: always;
        break-after: page;
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
      margin-bottom: 8px;
      color: #333;
      font-weight: bold;
    }
    .emp-card {
      background: #f8fafc;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      padding: 6px 10px;
      margin-bottom: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 10px;
    }
    .matrix-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .matrix-table th, .matrix-table td {
      border: 1px solid #333;
      padding: 4px 3px;
      font-size: 9px;
      vertical-align: middle;
    }
    .matrix-table th {
      background-color: #0284c7 !important;
      color: #fff;
      font-weight: bold;
      text-align: center;
    }
    .matrix-table .th-proc {
      background-color: #38bdf8 !important;
      color: #082f49 !important;
      font-weight: bold;
      font-size: 8px;
      padding: 3px 1px;
      text-align: center;
      white-space: nowrap;
    }
    .matrix-table .th-summary {
      background-color: #e2e8f0 !important;
      color: #000;
      font-weight: bold;
      text-align: center;
    }
    .cell-num {
      text-align: right !important;
      padding-right: 5px !important;
      font-weight: 600;
      white-space: nowrap;
    }
    .cell-zero {
      text-align: center;
      color: #94a3b8;
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
      padding: 6px 10px;
      border-radius: 4px;
      background: #f8fafc;
      margin-bottom: 12px;
      font-size: 9.5px;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }
    .ttd-table td {
      text-align: center;
      font-size: 10px;
      padding: 4px;
    }
    .badge-grade {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: bold;
      color: #fff;
    }
  </style>
</head>
<body>

  <div class="no-print">
    <div>
      <strong>Rapor Kinerja Karyawan:</strong> Periode <?= esc($from_raw) ?> s/d <?= esc($to_raw) ?>
      (<?= count($reports) ?> Karyawan)
    </div>
    <div>
      <a href="<?= base_url('sdm/target-harian') ?>" class="btn-back">&larr; Kembali</a>
      <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>
  </div>

  <?php if (!empty($reports)): ?>
    <?php 
      $daysIndo = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
      ];
    ?>

    <?php foreach ($reports as $rIdx => $rep): ?>
      <?php 
        $kar   = $rep['karyawan'];
        $days  = $rep['days'];
        $pList = $rep['proses_list'];
        $pTot  = $rep['proses_totals'];
        $totT  = $rep['total_target'];
        $totR  = $rep['total_real'];
        $avgP  = $rep['avg_pct'];

        $gradeText = ($avgP >= 100) ? 'SANGAT BAIK (MELEBIHI TARGET)' : (($avgP >= 80) ? 'STANDARD / CUKUP' : 'DI BAWAH TARGET (PERLU PEMBINAAN)');
        $gradeBg = ($avgP >= 100) ? '#047857' : (($avgP >= 80) ? '#d97706' : '#b91c1c');
      ?>

      <div class="<?= ($rIdx < count($reports) - 1) ? 'page-break' : '' ?>" style="margin-bottom: 25px;">
        
        <table class="header-table">
          <tr>
            <td width="30%" style="font-weight: bold; font-size: 13px;">CITRA JAYA KNITTING</td>
            <td width="40%" class="title">RAPOR KINERJA OUTPUT HARIAN KARYAWAN</td>
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

        <!-- Employee Bio Banner -->
        <div class="emp-card">
          <div>
            Nama Pegawai: <strong style="font-size: 12px; color: #0369a1;"><?= esc($kar->full_name) ?></strong>
            &nbsp;|&nbsp; NIP: <strong><?= esc($kar->nip) ?></strong>
          </div>
          <div>
            Posisi / Bagian: <span class="badge bg-secondary text-dark" style="background:#e2e8f0; padding: 2px 6px; border-radius: 3px; font-weight: bold;"><?= esc($kar->posisi) ?></span>
          </div>
          <div>
            Total Hari Aktif: <strong><?= count($days) ?> Hari</strong>
          </div>
        </div>

        <table class="matrix-table">
          <thead>
            <tr>
              <th rowspan="2" width="4%">NO</th>
              <th rowspan="2" width="16%">HARI / TANGGAL</th>
              <th colspan="<?= count($pList) ?>" class="th-proc" style="background-color: #0284c7 !important; color: #fff !important; font-size: 9px;">
                OUTPUT PER JENIS PROSES PRODUKSI (PCS)
              </th>
              <th colspan="3" class="th-summary">CAPAIAN HARIAN</th>
            </tr>
            <tr>
              <?php foreach ($pList as $p): ?>
                <th class="th-proc" title="<?= esc($p->nama_proses) ?>">
                  <?= esc($p->kode_proses) ?>
                </th>
              <?php endforeach; ?>
              <th width="8%" class="th-summary" style="color: #047857;">REALISASI</th>
              <th width="8%" class="th-summary">TARGET</th>
              <th width="7%" class="th-summary">% CAPAIAN</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($days)): ?>
              <?php foreach ($days as $dIdx => $d): ?>
                <?php 
                  $dayName = date('l', strtotime($d->tanggal));
                  $hariIndo = $daysIndo[$dayName] ?? $dayName;
                  $tglIndo  = date('d/m/Y', strtotime($d->tanggal));
                  $pctDay   = (float)$d->persentase;
                  $colorDay = ($pctDay >= 100) ? '#047857' : (($pctDay >= 80) ? '#b45309' : '#b91c1c');
                ?>
                <tr>
                  <td style="text-align: center;"><?= $dIdx + 1 ?></td>
                  <td style="font-weight: bold; text-align: left; padding-left: 6px;">
                    <?= $hariIndo ?>, <?= $tglIndo ?>
                    <span style="font-size: 7.5px; color: #64748b; font-weight: normal;">(<?= esc($d->kode_transaksi) ?>)</span>
                  </td>

                  <?php foreach ($pList as $p): ?>
                    <?php $qVal = $d->proses[$p->kode_proses] ?? 0; ?>
                    <?php if ($qVal > 0): ?>
                      <td class="cell-num"><?= (float)$qVal ?></td>
                    <?php else: ?>
                      <td class="cell-zero">-</td>
                    <?php endif; ?>
                  <?php endforeach; ?>

                  <td class="cell-num" style="color: #047857; font-weight: bold; background: #f0fdf4;">
                    <?= (float)$d->total_realisasi ?>
                  </td>
                  <td class="cell-num" style="background: #fefce8;">
                    <?= (float)$d->target_qty ?>
                  </td>
                  <td class="cell-num" style="color: <?= $colorDay ?>; font-weight: bold; background: #f0f9ff;">
                    <?= $pctDay ?>%
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= count($pList) + 5 ?>" style="text-align: center; padding: 20px; color: #64748b;">
                  Belum ada catatan aktivitas target harian untuk karyawan ini pada periode yang dipilih.
                </td>
              </tr>
            <?php endif; ?>

            <!-- FOOTER TOTAL PERIODE -->
            <tr class="row-total-footer">
              <td colspan="2" style="text-align: right; font-weight: bold; padding-right: 8px; font-size: 9.5px; background: #e2e8f0;">
                TOTAL AKUMULASI PERIODE (PCS)
              </td>
              <?php foreach ($pList as $p): ?>
                <?php $tVal = $pTot[$p->kode_proses] ?? 0; ?>
                <td class="cell-num" style="background: #e0f2fe; font-size: 9px;">
                  <?= $tVal > 0 ? (float)$tVal : '-' ?>
                </td>
              <?php endforeach; ?>

              <td class="cell-num" style="color: #047857; font-size: 10.5px; background: #dcfce7;">
                <?= (float)$totR ?>
              </td>
              <td class="cell-num" style="font-size: 10.5px; background: #fef08a;">
                <?= (float)$totT ?>
              </td>
              <?php 
                $colorAvg = ($avgP >= 100) ? '#047857' : (($avgP >= 80) ? '#b45309' : '#b91c1c');
              ?>
              <td class="cell-num" style="color: <?= $colorAvg ?>; font-size: 10.5px; background: #bae6fd;">
                <?= $avgP ?>%
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Summary & Evaluasi Kinerja -->
        <div class="summary-box">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <strong>Akumulasi Realisasi:</strong> <span style="color: #047857; font-weight: bold; font-size: 11px;"><?= number_format($totR) ?> Pcs</span>
              &nbsp;|&nbsp; <strong>Akumulasi Target:</strong> <?= number_format($totT) ?> Pcs
              &nbsp;|&nbsp; <strong>Rata-Rata Capaian:</strong> <strong style="color: <?= $colorAvg ?>; font-size: 11px;"><?= $avgP ?>%</strong>
            </div>
            <div>
              Evaluasi Kinerja: <span class="badge-grade" style="background: <?= $gradeBg ?>;"><?= $gradeText ?></span>
            </div>
          </div>
        </div>

        <!-- Tanda Tangan -->
        <table class="ttd-table">
          <tr>
            <td width="33%">Karyawan Bersangkutan,<br><br><br><br><strong>( <?= esc($kar->full_name) ?> )</strong></td>
            <td width="34%">Diperiksa Oleh,<br><br><br><br><strong>( Kepala Bagian / QC )</strong></td>
            <td width="33%">Disetujui Oleh,<br><br><br><br><strong>( Pimpinan / HRD )</strong></td>
          </tr>
        </table>

      </div>
    <?php endforeach; ?>

  <?php else: ?>
    <div style="text-align: center; padding: 40px; color: #64748b; font-size: 13px;">
      Data karyawan tidak ditemukan untuk dicetak.
    </div>
  <?php endif; ?>

</body>
</html>
