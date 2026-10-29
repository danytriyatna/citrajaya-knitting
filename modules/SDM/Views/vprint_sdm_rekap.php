<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Penggajian - Citra Jaya Knitting</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Calibri', 'Arial', sans-serif;
    }
    body {
      margin: 15px 25px;
      font-size: 11pt;
      color: #000;
    }
    .container {
      width: 100%;
      display: flex;
      gap: 15px;
      align-items: flex-start;
    }
    .col-half {
      flex: 1;
      width: 50%;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    th, td {
      border: 1px solid #7f7f7f;
      padding: 3px 6px;
      font-size: 10.5pt;
    }
    .th-upah {
      background-color: #D9E1F2;
      font-weight: bold;
      text-align: center;
      font-size: 11pt;
    }
    .th-kasbon {
      background-color: #FCE4D6;
      font-weight: bold;
      text-align: center;
      font-size: 11pt;
    }
    .checkbox-col {
      width: 28px;
      text-align: center;
      vertical-align: middle;
    }
    .checkbox-box {
      display: inline-block;
      width: 13px;
      height: 13px;
      border: 1.5px solid #333;
      border-radius: 1px;
      margin: 0 auto;
    }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: bold; }
    .row-total {
      font-weight: bold;
    }
    .no-print {
      margin-bottom: 15px;
    }
    .btn-print {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 6px 14px;
      font-size: 10pt;
      border-radius: 3px;
      cursor: pointer;
    }
    .btn-print:hover {
      background-color: #0056b3;
    }
    @media print {
      body { margin: 5mm 10mm; }
      .no-print { display: none; }
      @page {
        size: A4 portrait;
        margin: 5mm 10mm;
      }
    }
  </style>
</head>
<body>

  <div class="no-print" style="text-align: right;">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen</button>
  </div>

  <div class="container">
    <!-- KOLOM KIRI: UPAH HARIAN & MESIN -->
    <div class="col-half">
      <table>
        <thead>
          <tr>
            <th class="checkbox-col th-upah"><div class="checkbox-box"></div></th>
            <th class="th-upah text-center" style="letter-spacing: 0.5px;">UPAH HARIAN & MESIN</th>
            <th class="th-upah text-right" style="width: 110px;"><?= date('d-m-y') ?></th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $totalGaji = 0;
            if (!empty($detail)) :
              foreach ($detail as $d) :
                $gaji = (float)($d->gaji ?? 0);
                if ($gaji <= 0) continue;
                $totalGaji += $gaji;
          ?>
            <tr>
              <td class="checkbox-col"><div class="checkbox-box"></div></td>
              <td class="text-left"><?= strtoupper(!empty($d->full_name) ? $d->full_name : $d->nip) ?></td>
              <td class="text-right"><?= number_format($gaji, 0, ',', '.') ?></td>
            </tr>
          <?php 
              endforeach;
            endif; 
          ?>
          <tr class="row-total">
            <td class="checkbox-col"><div class="checkbox-box"></div></td>
            <td class="text-left fw-bold">TOTAL</td>
            <td class="text-right fw-bold"><?= number_format($totalGaji, 0, ',', '.') ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- KOLOM KANAN: PEMBAYARAN KASBON -->
    <div class="col-half">
      <table>
        <thead>
          <tr>
            <th class="checkbox-col th-kasbon"><div class="checkbox-box"></div></th>
            <th class="th-kasbon text-center" style="letter-spacing: 0.5px;">PEMBAYARAN KASBON</th>
            <th class="th-kasbon text-right" style="width: 110px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $totalKasbon = 0;
            if (!empty($detail)) :
              foreach ($detail as $d) :
                $kasbon = (float)($d->potongan ?? 0);
                if ($kasbon <= 0) continue;
                $totalKasbon += $kasbon;
          ?>
            <tr>
              <td class="checkbox-col"><div class="checkbox-box"></div></td>
              <td class="text-left"><?= strtoupper(!empty($d->full_name) ? $d->full_name : $d->nip) ?></td>
              <td class="text-right"><?= number_format($kasbon, 0, ',', '.') ?></td>
            </tr>
          <?php 
              endforeach;
            endif; 
          ?>
          <tr class="row-total">
            <td class="checkbox-col"><div class="checkbox-box"></div></td>
            <td class="text-left fw-bold">TOTAL TRANSFER</td>
            <td class="text-right fw-bold"><?= number_format($totalKasbon, 0, ',', '.') ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>