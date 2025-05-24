<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citraknitt QR Code</title>
  <style>
    /* Global styles */
    body {
      font-family: sans-serif;
      margin: 0;
      padding: 0;
    }

    .mt-0 {
      margin-top: 0px;
    }

    .mb-0 {
      margin-bottom: 0px;
    }

    .text-left {
      text-align: left;
    }

    .text-right {
      text-align: right;
    }

    .sheet {
      width: 100%;
      height: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr; /* Membagi menjadi dua kolom */
      grid-template-rows: auto auto; /* Membagi menjadi dua baris */
      gap: 2.5mm; /* Jarak antar elemen */
      box-sizing: border-box;
      padding: 0mm;
    }

    .item {
      border: 1px solid #ccc;
      padding: 8px; /* Ruang di dalam setiap item */
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      text-align: center;
    }

    img {
      display: block;
      margin-bottom: 2px; /* Memberi jarak antara gambar dan teks */
    }

    .details {
      font-size: 12px;
      text-align: center;
      line-height: 1.5; /* Jarak antar baris teks */
    }

    .details b {
      font-size: 16px;
      margin-bottom: 5px; /* Jarak bawah nama sample */
    }

    .details em {
      font-size: 14px;
    }

    /* Print-specific styles */
    @media print {
      @page {
        size: A4 portrait; /* Mengatur mode potrait */
        margin: 6mm; /* Margin halaman */
      }

      body {
        margin: 0;
        padding: 0;
      }

      .sheet {
        page-break-after: always; /* Pisahkan halaman untuk setiap kertas penuh */
      }
    }
  </style>
</head>
<body>
  <div class="sheet">
    <?php 
    for ($i = 0; $i < $data['qtyp']; $i++) { 
    ?>
      <div class="item">
        <h3 class="mt-0 mb-0"><?= $data['style'] ?: '-' ?></h3>
        <h4 class="mt-0 mb-0"><?= $data['deskripsi'] ?: '-' ?></h4>
        <table>
          <tbody>
            <tr>
              <td class="text-left" style="width: 50%;">
                <p class="mt-0 mb-0"><h3><?= $data['warna'] ?: '-' ?></h3></p>
                <p class="mt-0 mb-0"><h3><?= $data['warna_2'] ?: '-' ?></h3></p>
              </td>
              <td class="text-right" style="width: 50%;">
                <h4 class="mt-0 mb-0"><?= strtoupper($data['ukuran']) ?: '-' ?>;<?= $data['qty']; ?></h4>
              </td>
            </tr>
          </tbody>
        </table>
        <img src="<?= base_url(); ?>/uploads/media/qrcode/<?= $fileName; ?>" alt="QR Code" width="160px" height="160px">
        <div class="details">
          <h2 class="mt-0 mb-0"><?= $data['noSample'] ?></h2>
          <!-- <em><?= $data['deskripsi'] ?: '-' ?></em> | <em><?= strtoupper($data['ukuran']) ?: '-' ?></em> | <em><?= $data['warna'] ?: '-' ?></em> | <em><?= $data['qty'] ?: '-' ?></em> -->
        </div>
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
