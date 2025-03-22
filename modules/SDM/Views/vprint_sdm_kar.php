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
          <td style="width: 10%;">
            <img src="<?= base_url() ?>/assets/images/img_1.png" alt="Logo Text CJK" style="height: auto;width:60px;">
            &nbsp;
          </td>
          <td class="text-left" style="width: 70%;">
            <p class="text-sm">
              CV CITRA KNITT<br>
              Jl. Terusan Panyileukan Kav. No. 4<br>
              Bandung
            </p>
          </td>
          <td class="text-right" style="width: 20%;">
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
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Nama</span> : <?= !empty($detail) ? $detail[0]->full_name : ''?></p>
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">NIP</span> : <?= !empty($detail) ? $detail[0]->nip : ''?></p>
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Jabatan</span> : <?= !empty($detail) ? $detail[0]->posisi : ''?></p>
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Status</span> : .......................</p>
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Alamat</span> : <?= !empty($detail) ? $detail[0]->alamat : ''?></p>
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Telepon</span> : <?= !empty($detail) ? $detail[0]->no_hp : ''?></p>
        </td>
        <td style="width: 50%; vertical-align: top;">
          <p class="text-sm my-0"><span class="d-inline-block" style="width: 95px;">Tanggal</span> : <?= !empty($row) ? ($row->periode_awal) : ''?></p>
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
        <th class="text-left" style="width: 75%;">Keterangan</th>
        <th class="text-left" style="width: 25%;">Jumlah</th>
      </tr>
    </thead>
    <?php 
      $gaji = !empty($detail) ? $detail[0]->gaji_harian : 0;
      $lembur = !empty($detail) ? $detail[0]->uang_lembur  : 0;
      $bonus = !empty($detail) ? $detail[0]->bonus  : 0;
      $bonus_keterangan = !empty($detail) ? $detail[0]->bonus_keterangan  : null;
      $premi = !empty($detail) ? $detail[0]->premi  : 0;

      $jml_pendapatan = $gaji + $lembur + $bonus + $premi;
      $potongan = !empty($detail) ? $detail[0]->potongan  : 0;
      $total_pendapatan = $jml_pendapatan - $potongan;
    ?>
    <tbody>
      <tr>
        <td>Gaji</td>
        <td>Rp <?= format_angka($gaji, 2) ?></td>
      </tr>
      <tr>
        <td>Lembur</td>
        <td>Rp <?= format_angka($lembur, 2) ?></td>
      </tr>
      <tr>
        <td>Premi Harian</td>
        <td>Rp <?= format_angka($premi, 2) ?></td>
      </tr>
      <tr>
        <td>Penambahan dan lain-lain - <?= $bonus_keterangan ?></td>
        <td>Rp <?= format_angka($bonus, 2) ?></td>
      </tr>
    </tbody>
  </table>
  
  <hr>

  <table class="w-100">
    <tbody>
      <tr>
        <td style="width: 75%;">Jumlah Pendapatan</td>
        <td style="width: 25%;">Rp <?= format_angka($jml_pendapatan, 2) ?></td>
      </tr>
      <tr>
        <td>Potongan Kasbon</td>
        <td>Rp <?= format_angka($potongan, 2) ?></td>
      </tr>
    </tbody>
  </table>

  <br>

  <table class="w-100">
    <tbody>
      <tr>
        <th class="text-right pt-8 pr-16" style="width: 75%;">Jumlah Total Pendapatan Yang Diterima</th>
        <td class="pt-8" style="width: 25%; border-top: 2px solid #888;">Rp <?= format_angka($total_pendapatan, 2) ?></td>
      </tr>
    </tbody>
  </table>

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
            Pembayaran gaji dilakukan<br>
            melalui transfer ke rekening karyawan BNI terdaftar.<br>
            Jika rekening belum terdaftar,<br>
             maka pembayaran dilakukan secara tunai (cash).

            <!-- Pembayaran gaji telah dilakukan<br>
            oleh perusahaan secara transfer<br>
            ke rek. karyawan<br>
            BNI (no. rek) (nama pemilik rek) / <br>
            bisa dilkaukan pembayaran Cash -->
          </p>
        </td>
      </tr>
    </tbody>
  </table>

  <br>

  <table class="w-100">
    <tbody>
      <tr>
        <td class="text-center" style="width: 60%;">&nbsp;</td>
        <td class="text-center" style="width: 40%;">&nbsp;</td>
      </tr>
      <tr>
        <td class="text-center">
          <p><b>Karyawan</b></p><br>
          <p>( <?= !empty($detail) ? $detail[0]->full_name : '.......................'?> )</p>
        </td>
        <td class="text-center">
          <p><b>Menyetujui</b></p><br>
          <p>( Pimpinan )</p>
        </td>
        <td class="text-center">
          <p><b>Bagian Keuangan</b></p><br>
          <p>(...................)</p>
        </td>
      </tr>
    </tbody>
  </table>

  <script>
    window.print()
  </script>
</body>

</html>