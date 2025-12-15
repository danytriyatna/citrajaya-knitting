<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Order</title>
    <style>

        <?php 
            $pathWatermark = FCPATH . 'assets/images/watermark.png'; // Pastikan PNG sudah transparan
            $typeWatermark = pathinfo($pathWatermark, PATHINFO_EXTENSION);
            $dataWatermark = file_get_contents($pathWatermark);
            $base64Watermark = 'data:image/' . $typeWatermark . ';base64,' . base64_encode($dataWatermark);
        ?>

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
            background-image: url('<?= $base64Watermark ?>');
            background-repeat: no-repeat;
            background-size: 65%; /* sesuaikan ukuran watermark */

            background-position: center 40%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
            background-color: #eee;
            display: inline-block;
        }
        
        .address {
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .invoice-title {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .to-section {
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }
        
        th, td {
            /* border: 1px solid #808080; */
            padding: 8px;
            text-align: center;
        }
        
        .notes {
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .signature {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-placeholder {
            width: 150px;
            height: 80px;
            /* background-color: #eee; */
            margin: 0 auto 10px;
        }
        
        .footer-logo {
            text-align: center;
            margin-top: 30px;
        }
        
        .footer-logo img {
            width: 100px;
            height: auto;
            background-color: #eee;
        }
    </style>
</head>
<body>
    <?php 
        $path = FCPATH . 'assets/images/logoHeader.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $dataHeader = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataHeader);

        $pathFooter = FCPATH . 'assets/images/LogoPrintFooter.jpg';
        $typeFooter = pathinfo($pathFooter, PATHINFO_EXTENSION);
        $dataFooter = file_get_contents($pathFooter);
        $base64Footer = 'data:image/' . $typeFooter . ';base64,' . base64_encode($dataFooter);

        $pathTTD = FCPATH . 'assets/images/ttdEdwin.png';
        $typeTTD = pathinfo($pathTTD, PATHINFO_EXTENSION);
        $dataTTD = file_get_contents($pathTTD);
        $base64TTD = 'data:image/' . $typeTTD . ';base64,' . base64_encode($dataTTD);
    ?>
    <table>
        <thead>
            <tr style="border: none;">
                <th style="width: 100%; border: none;">
                    <img width = "250px" src="<?= $base64 ?>" alt="My image" />
                </th>
            </tr>
            <tr style="border: none;">
                <td style="border: none;font-size: 16px; border-bottom: 1px solid black; border-top: none; border-left: none; border-right: none;">
                    <strong style="font-size: 21px;">Knitting a Legacy of Quality</strong>
                    <br>
                    Jalan Terusan Panyileukan Kavling No.1 Cipadung Kidul, Kota Bandung, Jawa Barat
                </td>
            </tr>
        </thead>
    </table>
    
    <table>
        <tbody>
            <tr style="border: none; font-size: 14px;">
                <td style="border: none; width: 50%; text-align: left;">
                    <strong>KEPADA:
                    <br>
                    <?= !empty($detail[0]->buyer) ? $detail[0]->buyer : "-" ?>
                    </strong>
                    <br>
                    <?= !empty($detail[0]->alamat_buyer) ? $detail[0]->alamat_buyer : "-" ?>
                </td>
                <th style="text-align: center; border: none; vertical-align: top;">
                    <span style="font-size: 30px;">INVOICE ORDER</span>
                    <br>
                    <span style="font-size: 14px;">NO. INVOICE: <?= !empty($data->kode_invoice) ? $data->kode_invoice : "-" ?></span>
                    <br>
                    <span style="font-size: 14px;">TANGGAL: <?= !empty($data->tgl_transaksi) ? date('d/m/Y', strtotime($data->tgl_transaksi)) : "-" ?></span>
                </th>
            </tr>
        </tbody>
    </table>
    
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="border: 1px solid #808080;">NO</th>
                <th rowspan="2" style="border: 1px solid #808080;">DESKRIPSI</th>
                <th rowspan="2" style="width: 25%; border: 1px solid #808080;">WARNA</th>
                <th colspan="<?= count($ukuran) ?>" style="border: 1px solid #808080;">SIZE</th>
                <th rowspan="2" style="width: 15%; border: 1px solid #808080;">HARGA UNI/PCS (Rp)</th>
                <th rowspan="2" style="border: 1px solid #808080;">TOTAL (Rp)</th>
            </tr>
            <tr>
                <?php foreach ($ukuran as $rows): ?>
                    <th style="border: 1px solid #808080;"><?= $rows ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $nomor = 1;
        foreach ($data_detail as $key => $value):
            foreach ($value as $w => $color):
                echo "<tr>";

                // Kolom NO & DESKRIPSI hanya di baris pertama warna pertama
                if ($w === array_key_first($value)) {
                    // Baris pertama → tampil normal
                    echo "<td style='text-align:center; vertical-align: top; border-left:1px solid #808080; border-right:1px solid #808080; border-top:1px solid #808080;'>{$nomor}</td>";
                    echo "<td style='text-align:left; vertical-align: top; border-left:1px solid #808080; border-right:1px solid #808080; border-top:1px solid #808080;'>
                            {$key}<br>{$deskripsi[$nomor-1]}
                        </td>";
                } 
                else {
                    // Baris berikutnya → kosong, tapi border-top dihilangkan
                    echo "<td style='border-top:none; border-left:1px solid #808080; border-right:1px solid #808080;'></td>";
                    echo "<td style='border-top:none; border-left:1px solid #808080; border-right:1px solid #808080;'></td>";
                }
                    

                // Kolom warna
                 echo "<td style='text-align:left; vertical-align: top; border:1px solid #808080;'>{$color['keterangan']}</td>";

                // Kolom ukuran
                foreach ($ukuran as $sizeName) {
                    $qty = 0;
                    foreach ($color['ukuran'] as $size) {
                        if ($size['size'] == $sizeName) {
                            $qty = $size['qty'];
                            break;
                        }
                    }
                    echo "<td style='text-align:center; vertical-align: center; border: 1px solid #808080;'>{$qty}</td>";
                }

                // Harga unit & total harga (per warna)
                $hargaUnit = number_format($color['harga_satuan'], 0, ',', '.');
                $totalHarga = number_format($color['total_harga'], 0, ',', '.');

                echo "<td style='text-align:right; vertical-align: center; border: 1px solid #808080;'>{$hargaUnit}</td>";
                echo "<td style='text-align:right; vertical-align: center; border: 1px solid #808080;'>{$totalHarga}</td>";

                echo "</tr>";
            endforeach;

            $nomor++;
        endforeach;
        ?>
        </tbody>

        <tfoot>
            <?php 

            $colspan = count($ukuran) + 3; // 4 for No, Deskripsi, Warna, Harga Unit
            $formatSubTotal = number_format($sub_total, 0, ',', '.');
            $total = !empty($total_dp) ? $sub_total - $total_dp : $sub_total;
            $formatTotal = number_format($total, 0, ',', '.');
            ?>
            <tr>
                <td colspan="<?= $colspan ?>" style="text-align: left; vertical-align: top; border-top:1px solid #808080; border-left:1px solid #808080; border-right:1px solid #808080;"><strong style="font-size: 13px;">NOTES: </strong></td>
                <td style="border:1px solid #808080;"><strong>SUB TOTAL</strong></td>
                <td style="text-align: right; border:1px solid #808080;"><?= $formatSubTotal ?></td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>" style='border-top:none; border-left:1px solid #808080; border-right:1px solid #808080;'><?= $data->keterangan ?></td>
                <td style="border:1px solid #808080;"><strong>DP (<?= !empty($detail[0]->tgl_dp) ? date('d/m/Y', strtotime($detail[0]->tgl_dp)) : "-" ?>)</strong></td>
                <td style="text-align: right; border:1px solid #808080;"><?= !empty($total_dp) ? number_format($total_dp, 0, ',', '.') : 0 ?></td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>" style='border-top:none; border-left:1px solid #808080; border-right:1px solid #808080;'></td>
                <td style="border:1px solid #808080;"><strong>PENGIRIMAN</strong></td>
                <td style="text-align: right; border:1px solid #808080;"><?= !empty($total_pengiriman) ? number_format($total_pengiriman, 0, ',', '.') : 0 ?></td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>" style='border-top:none; border-left:1px solid #808080; border-right:1px solid #808080; border-bottom:1px solid #808080;'></td>
                <td style="border:1px solid #808080;"><strong>TOTAL</strong></td>
                <td style="text-align: right; border:1px solid #808080;"><?= $formatTotal ?></td>
            </tr>
        </tfoot>
    </table>

    <table style="border: none;">
        <tbody>
            <tr style="border: none;">
                <td style="border: none; font-size: 14px; text-align: left; width: 40%;" class="signature-placeholder">
                    <strong>PEMBAYARAN:</strong>
                    <br>
                    Bank Central Asia
                    <br>
                    Atas Nama : Edwin Ferdiansyah
                    <br>
                    No. Rekening : 2831218331
                </td>
                <th style="border: none; font-size: 14px;" class="signature-placeholder"><img width = "180px" src="<?= $base64TTD ?>" alt="My image" />
                <br>
                EDWIN FERDIANSYAH
                </th>
            </tr>
        </tbody>
    </table>
</body>
</html>