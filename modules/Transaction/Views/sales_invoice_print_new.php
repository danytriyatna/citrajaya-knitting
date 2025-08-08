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
            background-position: center center;
            background-repeat: no-repeat;
            background-size: 75%; /* sesuaikan ukuran watermark */
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
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        th {
            /* background-color: #f2f2f2; */
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
        $path = FCPATH . 'assets/images/LogoPrintHeader.jpg';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $dataHeader = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataHeader);

        $pathFooter = FCPATH . 'assets/images/LogoPrintFooter.jpg';
        $typeFooter = pathinfo($pathFooter, PATHINFO_EXTENSION);
        $dataFooter = file_get_contents($pathFooter);
        $base64Footer = 'data:image/' . $typeFooter . ';base64,' . base64_encode($dataFooter);

        $pathTTD = FCPATH . 'assets/images/ttdEdwin.jpg';
        $typeTTD = pathinfo($pathTTD, PATHINFO_EXTENSION);
        $dataTTD = file_get_contents($pathTTD);
        $base64TTD = 'data:image/' . $typeTTD . ';base64,' . base64_encode($dataTTD);
    ?>
    <div class="header">
        <div style="padding-bottom: 5px;"><img width = "250px" src="<?= $base64 ?>" alt="My image" /></div>
        <div class="address">Jl. Terusan Panyileukan, Kav No. 4, Bandung</div>
    </div>
    <hr style="border: none; border-top: 1px solid #000;" />
    <div class="invoice-title"><span style="border-bottom: 2px solid black; padding-bottom: 2px;">INVOICE ORDER</span></div>
    
    <div class="invoice-info" style="text-align: center;">
        <div><strong>No. <?= !empty($data->kode_invoice) ? $data->kode_invoice : "-" ?></strong></div>
        <div>Date <?= !empty($detail[0]->tgl_transaksi) ? date('d F, Y', strtotime($detail[0]->tgl_transaksi)) : "-" ?></div>
        <div>Deadline <?= !empty($detail[0]->tgl_deadline) ? date('d F, Y', strtotime($detail[0]->tgl_deadline)) : "-" ?></div>
    </div>
    
    <div class="to-section">
        <div><strong>To :</strong> <?= !empty($detail[0]->alamat_buyer) ? $detail[0]->alamat_buyer : "-" ?> Ph : <?= !empty($detail[0]->no_hp_buyer) ? $detail[0]->no_hp_buyer : "-" ?></div>
        <div><strong>Up :</strong> <?= !empty($detail[0]->buyer) ? $detail[0]->buyer : "-" ?></div>
    </div>
    
    <div>Dengan ini pesanan sebagai berikut :</div>
    
    <table>
        <thead>
            <?php
                    $sizes = $ukuran;
                    $qtyToDisplay = [];
                    foreach ($detail_so as $index => $row) {
                         $rowsToDisplay = [];
                        foreach ($sizes as $ind => $size) {
                            if (!empty($row->$size)) {
                                $qty = (int)$row->$size;
                                if ($size == "all_") {
                                    $size = "all";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else if ($size == "sm") {
                                    $size = "s / m";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else if ($size == "ml") {
                                    $size = "m / l";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else if ($size == "lxl") {
                                    $size = "l / xl";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else if ($size == "xxxxl") {
                                    $size = "4xl";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else if ($size == "xxxxxl") {
                                    $size = "5xl";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else if ($size == "xxxxxxl") {
                                    $size = "6xl";
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                else {
                                    $qtyToDisplay[$index.strtoupper($size)] = $qty;
                                }
                                $rowsToDisplay[] = ['size' => strtoupper($size), 'qty' => $qty];
                                
                            }
                        }
                        
                    }
                ?>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Deskripsi</th>
                <th rowspan="2">Warna</th>
                <th colspan="<?= count($rowsToDisplay) ?>">Size</th>
                <th rowspan="2">Harga Unit / Pc (Rp)</th>
                <th rowspan="2">Harga Total /Pc (Rp)</th>
            </tr>
            <tr>
                <?php
                    foreach ($rowsToDisplay as $indexs => $rows) {

                        echo "<th>{$rows['size']}</th>";
                    }
                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 5%;"></td>
                <td style="width: 20%;"><?= !empty($detail[0]->deskripsi) ? $detail[0]->deskripsi : "-" ?></td>
                <td style="width: 25%;">
                    <?php
                        foreach ($detail_so as $index => $row) {
                            echo "$row->colour <br>";
                        }
                    ?>
                </td>
                <?php
                    foreach ($rowsToDisplay as $indexs => $rows) {

                        echo "<td style='text-align: center;'>";
                        for ($i=0; $i < count($detail_so); $i++) { 
                            echo "{$qtyToDisplay[$i.$rows['size']]} <br>";
                        }
                        echo "</td>";
                    }
                ?>
                <td style="text-align: right;">
                    <?php
                        foreach ($detail_so as $index => $row) {
                            $allQty = 0;
                            foreach ($rowsToDisplay as $indexs => $rows) {
                                $allQty += $qtyToDisplay[$index.$rows['size']];
                            }
                            $format = number_format($row->total_harga/$allQty, 0, ',', '.');
                            echo "$format <br>";
                        }
                    ?>
                </td>
                <td style="text-align: right;">
                    <?php
                    $subTotal = 0;
                        foreach ($detail_so as $index => $row) {
                            $subTotal += $row->total_harga;
                            $format = number_format($row->total_harga, 0, ',', '.');
                            echo "$format <br>";
                        }
                    ?>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <?php 

            $colspan = count($rowsToDisplay) + 4; // 4 for No, Deskripsi, Warna, Harga Unit
            $formatSubTotal = number_format($subTotal, 0, ',', '.');
            $total = !empty($detail[0]->uang_dp) ? $subTotal - $detail[0]->uang_dp : $subTotal;
            $formatTotal = number_format($total, 0, ',', '.');
            ?>
            <tr>
                <td colspan="<?= $colspan ?>"><strong>SUB TOTAL</strong></td>
                <td style="text-align: right;"><?= $formatSubTotal ?></td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>"><strong>DP INVOICE (<?= !empty($detail[0]->tgl_dp) ? date('d F Y', strtotime($detail[0]->tgl_dp)) : "-" ?>)</strong></td>
                <td style="text-align: right;"><?= !empty($detail[0]->uang_dp) ? number_format($detail[0]->uang_dp, 0, ',', '.') : 0 ?></td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>"><strong>TOTAL</strong></td>
                <td style="text-align: right;"><?= $formatTotal ?></td>
            </tr>
            <tr>
                <th>Notes</th>
                <td colspan="<?= $colspan ?>"><strong><?= $data->keterangan ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <table style="border: none;">
        <tbody>
            <tr style="border: none;">
                <td style="border: none; text-align: left;">Harga</td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;">Thanks & Regards, CJK</td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;">Confirmed & Accepted</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;" class="signature-placeholder"><img width = "130px" src="<?= $base64TTD ?>" alt="My image" /></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;" class="signature-placeholder"></td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;">Edwin Ferdiansyah</td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer-logo">
        <img width = "100px" src="<?= $base64Footer ?>" alt="My image" />
    </div>
</body>
</html>