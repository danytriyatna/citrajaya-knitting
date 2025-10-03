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
            <img src="<?= base_url() ?>/assets/images/img_1.png" alt="Logo Text CJK" style="height: auto;width:60px;">
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
            <h3><b><u>Slip Gaji Karyawan Periode <?= $row->periode_awal ?> s/d <?= $row->periode_akhir ?></u></b></h3>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <hr>
  <br>

  <br>

  <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="width: 10%; border: 1px solid black; padding: 8px; text-align: left;">NIK</th>
            <th style="width: 15%; border: 1px solid black; padding: 8px; text-align: left;">NAMA</th>
            <th style="width: 10%; border: 1px solid black; padding: 8px; text-align: left;">POSISI</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">HADIR</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">IZIN</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">SAKIT</th>
            <!-- <th style="width: 10%; border: 1px solid black; padding: 8px; text-align: left;">ROLLING SHIFT</th> -->
            <th style="width: 10%; border: 1px solid black; padding: 8px; text-align: left;">JAM KERJA</th>
            <th style="width: 10%; border: 1px solid black; padding: 8px; text-align: left;">GAJI/UPAH</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">SAMPLE/PERBAIKAN</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">LEMBUR HK</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">LEMBUR HL</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">PREMI KEHADIRAN</th>

            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">LEMBUR</th>
           
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">PENAMBAHAN</th>
            <th style="width: 7%; border: 1px solid black; padding: 8px; text-align: left;">POTONGAN</th>
            <th style="width: 10%; border: 1px solid black; padding: 8px; text-align: left;">GAJI/UPAH</th>
        </tr>
    </thead>

    <tbody>
        <?php 
            $jam_kerja = 0;
            $gaji_harian = 0;
            $sample = 0;
            $lembur = 0;
            $lembur_we = 0;
            $uang_lembur = 0;
            $bonus = 0;
            $potongan = 0;
            $total_pendapatan_total = 0;
            $premi = 0;
            foreach ($detail as $r) { ?>
            <?php 
                $gaji = !empty($r) ? $r->gaji_harian : 0;
                $lembur = !empty($r) ? $r->uang_lembur  : 0;
                $bonus = !empty($r) ? $r->bonus  : 0;
                

                $jml_pendapatan = $gaji + $lembur + $bonus + $r->premi + $r->jml_sample;
                $potongan = !empty($r) ? $r->potongan  : 0;
                $total_pendapatan = $jml_pendapatan - $potongan;
                

                $jam_kerja = $jam_kerja + $r->jam_kerja;
                $gaji_harian = $gaji_harian + $r->gaji_harian;
                $sample = $sample + $r->jml_sample;
                $lembur = $lembur + $r->lembur;
                $lembur_we = $lembur_we + $r->lembur_we;
                $uang_lembur = $uang_lembur + $r->uang_lembur;
                $bonus = $bonus + $r->bonus;
                $potongan = $potongan + $r->potongan;
                $total_pendapatan_total = $total_pendapatan_total + $total_pendapatan;
                $premi = $premi + $r->premi;
            ?>
            <tr>
                <td style="border: 1px solid black; padding: 8px;"><?= $r->nip; ?></td>
                <td style="border: 1px solid black; padding: 8px;"><?= $r->full_name; ?></td>
                <td style="border: 1px solid black; padding: 8px;"><?= $r->posisi; ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->hadir; ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->izin; ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->sakit; ?></td>

                <!-- <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->alpha; ?></td> -->

                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->jam_kerja; ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($r->gaji_harian); ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= format_angka($r->jml_sample); ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->lembur; ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $r->lembur_we; ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($r->premi); ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($r->uang_lembur); ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($r->bonus); ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($r->potongan); ?></td>
                <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($total_pendapatan); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
          <td style="border: 1px solid black; padding: 8px;" colspan="6"> Total</td>
          <!-- <td style="border: 1px solid black; padding: 8px;"></td> -->
          <!-- <td style="border: 1px solid black; padding: 8px;"></td> -->
          <!-- <td style="border: 1px solid black; padding: 8px;"></td> -->
          <!-- <td style="border: 1px solid black; padding: 8px;"></td> -->
          <!-- <td style="border: 1px solid black; padding: 8px;"></td> -->
          <!-- <td style="border: 1px solid black; padding: 8px;"></td> -->
          <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= ($jam_kerja); ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($gaji_harian); ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= format_angka($sample); ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $lembur; ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:center;"><?= $lembur_we; ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= $premi; ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($uang_lembur); ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($bonus); ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($potongan); ?></td>
          <td style="border: 1px solid black; padding: 8px;text-align:right;"><?= format_angka($total_pendapatan_total); ?></td>
      </tr>
    </tfoot>
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
          
        </td>
        <td class="text-center">
          <p><b>Owner</b></p><br>
          <p>( ....................... )</p>
        </td>
      </tr>
    </tbody>
  </table>

  <script>
    window.print()
  </script>
</body>

</html>