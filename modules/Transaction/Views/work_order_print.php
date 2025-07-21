<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>PO PRODUKSI</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 30px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      border: 1px solid #000;
      padding: 4px 6px;
      text-align: center;
    }
    .no-border {
      border: none;
    }
    .header, .footer {
      margin-bottom: 10px;
    }
    .title {
      text-align: center;
      font-weight: bold;
      font-size: 16px;
      margin-bottom: 10px;
    }
    .signature td {
      height: 80px;
      vertical-align: top;
      text-align: center;
      border: none;
    }
    .notes {
      padding: 5px;
      height: 60px;
      border-top: 2px solid black;
    }
  </style>
</head>
<body>
    <?php
        function formatTanggalIndonesiaNow($tanggal, $type = null)
        {
            $hari = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];

            $bulan = [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember'
            ];

            $timestamp = strtotime($tanggal); // pastikan $tanggal formatnya YYYY-MM-DD atau valid lainnya
            $hariNama = $hari[date('l', $timestamp)];
            $tgl = date('d', $timestamp);
            $bln = $bulan[date('m', $timestamp)];
            $thn = date('Y', $timestamp);

            if ($type == 1) {
                return "$hariNama, $tgl $bln $thn";
            }
            else {
                return "$tgl $bln $thn";
            }
            
        }
    ?>
  <div class="title">WO PRODUKSI</div>

  <div class="header">
    <p style="text-align: right;">Hari/Tgl: <?= !empty($sales_order->tgl_transaksi) ? formatTanggalIndonesiaNow($sales_order->tgl_transaksi, 1) : date('Y-m-d') ?></strong></p>
    <p>Kepada: <strong>TIM PRODUKSI</strong></p>
    <p>Surat tembusan ini berisi detail PO yang harus dibuatkan dengan rincian sbb:</p>
  </div>

  <table>
    <tr>
        <th rowspan="5" colspan="2">
            <?php 
                $path = FCPATH . 'assets/images/placeholder.png';
                if (!empty($sales_order->file_name)) {
                    $path = FCPATH . 'uploads/sales_order/' . $sales_order->file_name;
                    if (!file_exists($path)) {
                        $path = FCPATH . 'uploads/sample/' . $sales_order->file_name;
                        if (!file_exists($path)) {
                            $path = FCPATH . 'assets/images/placeholder.png';
                        } 
                    } 
                }
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                echo '<img width = "90px" src="'.$base64.'" alt="My image" />';
            ?>
        </th>
      <th style="text-align: left;">SO :</th>
      <td  style="text-align: left;" colspan="5"><?= !empty($sales_order->kode_sales_order) ? $sales_order->kode_sales_order : $sales_order->kode_sample ; ?></td>
    </tr>
    <tr>
      <th  style="text-align: left;">BUYER :</th>
      <td  style="text-align: left;" colspan="5"><?= !empty($sales_order->nama) ? $sales_order->nama : null ; ?></td>
    </tr>
    <tr>
      <th  style="text-align: left;">TANGGAL SO :</th>
      <td  style="text-align: left;" colspan="5"><?= !empty($sales_order->tgl_transaksi) ? formatTanggalIndonesiaNow($sales_order->tgl_transaksi) : null ; ?></td>
    </tr>
    <tr>
      <th  style="text-align: left;">DEADLINE :</th>
      <td  style="text-align: left;" colspan="5"><?= !empty($sales_order->tgl_deadline) ? formatTanggalIndonesiaNow($sales_order->tgl_deadline) : null ; ?></td>
    </tr>
    <tr>
      <th  style="text-align: left;">DESK :</th>
      <td  style="text-align: left;" colspan="5"><?= !empty($sales_order->deskripsi) ? $sales_order->deskripsi : null ; ?></td>
    </tr>

    <tr>
        <th>NO</th>
        <th>WARNA</th>
        <th>SIZE</th>
        <th>QTY</th>
        <th>TOTAL</th>
        <th colspan="3">QTY KIRIM</th>
    </tr>

    <?php
        $data = $sales_order_det; // array of stdClass dari database
        $no = 1;
        $sizes = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
        $grandTotalQty = 0;

        foreach ($data as $item) {
            $rowsToDisplay = [];
            $totalQty = 0;

            // Cek apakah data ALL
            if (!empty($item->all_)) {
                $qty = (int)$item->all_;
                $rowsToDisplay[] = ['size' => 'ALL', 'qty' => $qty];
                $totalQty = $qty;
            } else {
                foreach ($sizes as $size) {
                    if (!empty($item->$size) && $item->$size > 0) {
                        $qty = (int)$item->$size;
                        $rowsToDisplay[] = ['size' => strtoupper($size), 'qty' => $qty];
                        $totalQty += $qty;
                    }
                }
            }
            $grandTotalQty += $totalQty;
            $rowspan = count($rowsToDisplay);

            foreach ($rowsToDisplay as $index => $row) {
                echo "<tr>";
                if ($index == 0) {
                    echo "<td rowspan='{$rowspan}' style='width: 6%;'>{$no}</td>";
                    echo "<td rowspan='{$rowspan}' style='width: 30%;'>{$item->colour}</td>";
                }

                echo "<td style='width: 16%;'>{$row['size']}</td>";
                echo "<td style='width: 11%;'>{$row['qty']}</td>";

                if ($index == 0) {
                    echo "<td rowspan='{$rowspan}' style='width: 11%;'>{$totalQty}</td>";
                }
                echo "<td></td>";
                echo "<td></td>";
                echo "<td></td>";
                echo "</tr>";
            }
            $no++;
        }
    ?>

    <tfoot>
      <tr>
        <th colspan="4">TOTAL QTY</th>
        <th><?= $grandTotalQty; ?></th>
        <th></th>
        <th></th>
        <th></th>
      </tr>
    </tfoot>
  </table>

  <table>
    <thead>
        <tr>
            <th style="width: 6%;" rowspan="2">NO</th>
            <th colspan="3">JENIS BENANG</th>
            <th></th>
        </tr>
        <tr>
            <th style="width: 27%;">WARNA</th>
            <th style="width: 15%;">Kebutuhan (QTY/KG)</th>
            <th>LOT</th>
            <th style="width: 35%;">KIRIM SBB (QTY/KG)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalKg = 0;
        foreach ($walk_order_det as $i => $item): 
            $qty = $item->qty/$item->kg; // pastikan float untuk penjumlahan
            $totalKg += $qty;
        ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $item->kode_warna ?></td>
                <td><?= number_format($qty, 2, ',', '.') ?></td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
        
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">TOTAL JUMLAH</th>
            <th><?= number_format($totalKg, 2, ',', '.') ?></th>
            <th></th>
            <th></th>
        </tr>
    </tfoot>
  </table>

  <table class="signature" style="border: none;">
    <tr>
      <td style="width: 20%;">Pengirim</td>
      <td style="width: 20%;">Penerima</td>
      <td style="width: 20%;">Montir</td>
      <td style="width: 20%;">Kepala Rajut</td>
      <td style="width: 20%;">Mengetahui,</td>
    </tr>
  </table>

  <div class="notes">
    <strong>Notes:</strong><br>
  </div>

</body>
</html>
