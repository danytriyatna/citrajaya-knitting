<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kartu Produksi Citra Knitt</title>
<style>
  body {
    font-family: "Times New Roman", serif;
    background: #fff; /* putih polos */
    display: flex;
    justify-content: center;
    padding: 30px;
  }

  /* Tambahkan container agar bisa wrap per baris */
  .container {
    display: flex;
    flex-wrap: wrap; /* agar jika lebih dari 3, turun ke bawah */
    gap: 20px; /* jarak antar kartu */
    justify-content: center;
    max-width: 1000px; /* batas maksimal lebar baris */
  }

  .card {
    width: 300px;
    border: 1px solid #000;
    padding: 10px 15px;
    background: #fff; /* putih juga */
    box-sizing: border-box;
  }

  .header {
    text-align: center;
    margin-bottom: 10px;
  }

  .header .ck {
    border: 2px solid black;
    border-radius: 50%;
    display: inline-block;
    width: 60px;
    height: 60px;
    line-height: 60px;
    font-weight: bold;
    vertical-align: middle;
    text-align: center;
    font-size: 27px;
  }

  .header h3, .header h4 {
    margin: 0;
    font-weight: normal;
  }

  .row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
  }

  .warna {
    margin-top: 8px;
    margin-bottom: 10px;
  }

  .warna-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2px;
  }

  .footer {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
  }

  .footer div {
    text-align: center;
  }

  .kotak-merah {
    border: 1px solid black;
    width: 45px;
    height: 55px;
    margin: 3px auto 0;
  }

  /* Print-specific styles */
    @media print {
  @page {
    size: A4 portrait;
    margin: 6mm;
  }

  body {
    margin: 0;
    padding: 0;
  }

  .container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    max-width: 1000px;
  }

  .card {
    width: 300px;
    border: 1px solid #000;
    padding: 10px 15px;
    background: #fff;
    box-sizing: border-box;
    page-break-inside: avoid; /* ⛔ Cegah kartu terpotong */
    break-inside: avoid;       /* untuk browser modern */
  }

  /* Setiap 3 kartu per baris, pastikan baris utuh di satu halaman */
  .card:nth-child(3n+1) {
    page-break-before: auto;
  }

  .card:nth-child(3n+3) {
    page-break-after: auto;
  }

  /* Jika mau pastikan tidak pecah di tengah baris */
  .container {
    page-break-inside: avoid;
    break-inside: avoid;
  }
}

</style>
</head>
<body>

<div class="container">
<?php 
    for ($i = 0; $i < $data['qtyp']; $i++) {  // coba lebih dari 3 untuk test
    ?>
    <div class="card">
      <div class="header">
        <table style="width: 100%;">
            <tbody>
                <tr style=" font-size: 14px;">
                    <td style=" width: 20%; text-align: left;">
                        <div class="ck">CK</div>
                    </td>
                    <td style=" width: 60%; text-align: center;">
                        <strong>
                          <span style="font-size: 20px;">Kartu Produksi</span><br>
                          <span style="font-size: 19px;">Citra Knitt</span>
                        </strong>
                    </td>
                    <td style="text-align: center;  width: 20%;">
                        <strong>
                          <span style="font-size: 14px;">Mesin</span>
                        </strong>
                        <div class="kotak-merah"></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border-bottom: 2px solid black;"></td>
                </tr>
            </tbody>
        </table>
      </div>

      <table style="width: 100%;">
        <tr>
          <td style=" width: 10%;">No.</td>
          <td style=" width: 5%;">:</td>
          <td style=" width: 10%;">__________</td>
          <td style=" width: 10%;"></td>
          <td style=" width: 10%;">Style</td>
          <td style=" width: 5%;">:</td>
          <td style=" width: 60%;"><?= !empty($data['style']) ? $data['style'] : '__________' ?></td>
        </tr>
        <tr>
          <td>Size</td><td>:</td><td><?= !empty($data['ukuran_text']) ? $data['ukuran_text'] : '__________' ?></td>
          <td></td><td>Qty</td><td>:</td><td><?= !empty($data['qty']) ? $data['qty'] : '__________' ?></td>
        </tr>
        <tr>
          <td>Desc.</td><td>:</td><td colspan="4"><?= !empty($data['desc']) ? $data['desc'] : '__________' ?></td>
        </tr>
      </table>

      <br>
      <table style="width: 100%;">
        <tr><td colspan="6"><strong>Warna :</strong></td></tr>
        <?php
        foreach ($data['data_warna'] as $key => $value) {
          $huruf = chr(65 + $key);
        ?>
            <tr>
              <td style=" width: 5%;"></td>
              <td style=" width: 5%; font-size: 14px;"><?= !empty($huruf) ? $huruf.". " : null ?></td>
              <td style=" width: 50%; font-size: 12px;"><?= !empty($value) ? $value : '__________' ?></td>

              <td style=" width: 2%; font-size: 14px;">Lot.</td>
              <td style=" width: 10%;">________</td>
            </tr>
        <?php 
            }
        ?>
      </table>

      <br>
      <table style="width: 100%; text-align: center;">
        <tr>
          <th><img height="115px" width="115px" src="<?= base_url(); ?>/uploads/media/qrcode/<?= $fileName; ?>" alt="QR Code" width="160px" height="160px"></th>
        </tr>
        <tr>
          <th><?= $data['kode_qr'] ?></th>
        </tr>
        <!-- <tr><th>Admin</th><th>Montir Kepala</th></tr> -->
        <!-- <tr><td><br><br>.....................</td><td><br><br>.....................</td></tr> -->
      </table>
    </div>
<?php 
    }
?>
</div>

<script>
  window.print();
</script>
</body>
</html>
