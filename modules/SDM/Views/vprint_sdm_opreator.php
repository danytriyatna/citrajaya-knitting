<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citra Jaya Knitting</title>
  <style>
    html {
      margin: 10px 32px;
      font-size: 13px;
    }

    body * {
      font-family: "Arial", "Calibri", sans-serif !important;
    }

    .d-inline-block {
      display: inline-block;
    }

    .w-100 {
      width: 100%;
    }

    .w-50 {
      width: 50%;
    }

    .my-0 {
      margin-bottom: 0px;
      margin-top: 0px;
    }

    .mb-0 {
      margin-bottom: 0px;
    }

    .mb-6 {
      margin-bottom: 6px;
    }

    .mt-0 {
      margin-top: 0px !important;
    }

    .pt-8 {
      padding-top: 8px !important;
    }

    .pr-16 {
      padding-right: 16px !important;
    }

    .py-4 {
      padding-top: 4px !important;
      padding-bottom: 4px !important;
    }

    .uppercase {
      text-transform: uppercase;
    }

    .text-center {
      text-align: center;
    }

    .text-justify {
      text-align: justify;
    }

    .text-left {
      text-align: left;
    }

    .text-right {
      text-align: right;
    }

    .valign-top {
      vertical-align: top;
    }

    .text-sm {
      font-size: 13px;
    }

    .text-md {
      font-size: 16px;
    }

    .text-lg {
      font-size: 22px;
    }

    .break-word {
      word-break: break-word;
    }

    .table-bordered {
      border-spacing: unset;
    }

    .border-spacing-0 {
      border-spacing: 0px;
    }

    .table-bordered > thead > tr > th,
    .table-bordered > tbody > tr > th,
    .table-bordered > thead > tr > td,
    .table-bordered > tbody > tr > td {
      border: 1px solid #333;
      padding: 1px 6px;
      font-size: 12px;
    }

    .kop-surat img {
      height: 100px;
    }

    .kop-surat  div  h1 {
      text-transform: uppercase;
      margin-bottom: 10px;
      margin-top: 10px;
      font-size: 18px;
    }

    .kop-surat div p {
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="kop-surat">
    <table class="w-100">
      <tbody>
        <tr>
          <td style="width: 120px;">
            <img src="https://i.ibb.co.com/twN7mWBk/logo-citraknitt-text-dark.png" alt="Logo Text CJK" width="270px" style="height: auto;">
            &nbsp;
          </td>
        </tr>
        <tr>
          <td class="text-left" style="width: 50%;">
            <p class="text-sm">
              CV CITRA KNITT<br>
              Jl. Terusan Panyileukan Kav. No. 4<br>
              Bandung
            </p>
          </td>
          <td class="text-right" style="width: 50%;">
            <h3><b><u>Slip Gaji Karyawan</u></b></h3>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <hr>
  <br>

  <table class="w-100">
    <tbody>
      <tr>
        <td style="width: 50%; vertical-align: top;">
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Nama</span> : <?= !empty($detail) ? $row->nama_operator : ''?></p>
        </td>
        <td style="width: 50%; vertical-align: top;">
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Tanggal Awal</span> :  <?= !empty($xrow) ? ($xrow['tgl_awal']) : ''?></p>
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Tanggal Akhir</span> : <?= !empty($xrow) ? ($xrow['tgl_akhir']) : ''?></p>
          <!-- <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Kode Karyawan</span> : .......................</p> -->
        </td>
      </tr>
    </tbody>
  </table>

  <br>
  <hr>
  <br>

  <table class="w-100">
    <thead>
      <tr>
        <th class="text-left" style="width: 75%;"><Style></Style></th>
        <th class="text-left" style="width: 25%;">Harga</th>
        <th class="text-left" style="width: 25%;">Qty</th>
        <th class="text-left" style="width: 25%;">Harga Total</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $harga = 0;
        $qty = 0;
        $harga_total = 0;
        foreach ($detail as $r) { 
        $harga = $harga + $r->harga;
        $qty = $qty + $r->qty;
        $harga_total =  $harga_total + $r->harga_total;
        ?> 
        <tr>
          <td><?= $r->keterangan_style ?></td>
          <td>Rp <?= format_angka($r->harga, 2) ?></td>
          <td>Rp <?= ($r->qty) ?></td>
          <td>Rp <?= format_angka($r->harga_total, 2) ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  
  <hr>

  <br>

  <br>

  <table class="w-100">
    <tbody>
      <tr>
        <td class="text-center" style="width: 60%;">&nbsp;</td>
        <td class="text-center" style="width: 40%;">&nbsp;</td>
      </tr>
      <tr>
        <td class="text-center">
          <p><b>Mengetahui</b></p><br>
          <p>( HRD )</p>
        </td>
        <td class="text-left">
          <p>
            Pembayaran gaji telah dilakukan<br>
            oleh perusahaan secara transfer<br>
            ke rek. karyawan<br>
            BNI (no. rek) (nama pemilik rek)
          </p>
        </td>
      </tr>
    </tbody>
  </table>

  <script>
    window.print()
  </script>
</body>

</html>