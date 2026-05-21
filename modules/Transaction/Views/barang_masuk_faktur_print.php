<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" type="image/png" sizes="64x64" href="assets/images/favicon.png"> -->
    <title>Citra Jaya Knitting</title>
    <style>
        html {
            margin: 10px 32px;
            font-size: 11px;
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

        .table-bordered>thead>tr>th,
        .table-bordered>tbody>tr>th,
        .table-bordered>tfoot>tr>th,
        .table-bordered>thead>tr>td,
        .table-bordered>tbody>tr>td,
        .table-bordered>thead>tr>td {
            border: 1px solid #333;
            padding: 1px 6px;
            font-size: 11px;
        }

        .kop-surat img {
            height: 100px;
        }

        .kop-surat div h1 {
            text-transform: uppercase;
            margin-bottom: 10px;
            margin-top: 10px;
            font-size: 18px;
        }

        .kop-surat div p {
            margin-top: 10px;
        }

        .font-footer {
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="kop-surat">
        <table class="w-100">
            <tbody>
                <tr>
                    <!-- <td style="width: 120px;">
                        &nbsp;
                    </td> -->
                    <td class="text-center" style="width: auto;">
                        <div>
                              <h1>FAKTUR TAGIHAN</h1>
                        </div>
                    </td>
                    <!-- <td style="width: 300px;">
                        <table class="table-bordered border-spacing-0 w-100 mb-6">
                            <tbody>
                                <tr>
                                    <th style="width: 50px;"><small>TGL</small></th>
                                    <td><?= formatTanggalIndonesia($data->tanggal) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table-bordered border-spacing-0 w-100">
                            <tbody>
                                <tr>
                                    <th><small>KEPADA YTH</small></th>
                                </tr>
                                <tr>
                                    <td><?= $data->nama ?></td>
                                </tr>
                                <tr>
                                    <th><small>GUDANG</small></th>
                                </tr>
                                <tr>
                                    <td><?= $data->nama_gudang ?></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </td> -->
                </tr>
            </tbody>
        </table>
    </div>

    <hr>

    <!-- <h1 class="uppercase text-lg mb-6 mt-0">BARANG MASUK</h1> -->
    <table class="mb-6 font-footer" style="width:100%;">
        <tr>
            <td style="width: 152px; text-align: left; vertical-align: top;">Nama CMT</td>
            <td style="width: 8px; text-align: center; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $data->nama_operator ?></td>
        </tr>
        <tr>
            <td style="text-align: left; vertical-align: top;">Alamat</td>
            <td style="text-align: center; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><?= $data->alamat_cmt ?></td>
        </tr>
    </table>

    <br>

    <table class="table-bordered w-100">
        <thead>
            <tr>
                <th colspan="5" style="border:0;"></th>
                <th colspan="2" style="border:0;">Nomor Faktur :</th>
                <th colspan="2" style="border:0;text-align:left;"><?= $data->kode_transaksi ?></th>
            </tr>
            <tr>
                <th class="text-center" style="width: 3%;">No.</th>
                <th class="text-center" style="width: 14%;">No. SO</th>
                <th class="text-center" style="width: 9%;">Style</th>
                <th class="text-center" style="width: 11%;">Tanggal</th>
                <th class="text-center" style="width: 13%;">Proses</th>
                <th class="text-center" style="width: 7%;">Qty</th>
                <th class="text-center" style="width: 8%;">Qty Kirim</th>
                <th class="text-center" style="width: 12%;">Price</th>
                <th class="text-center" style="width: 14%;">Amount</th>
                <th class="text-center" style="width: 9%;">Ket.</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Pastikan $dataSO tidak null sebelum masuk ke pengecekan
            $dataSO = $dataSO ?? []; 

            if (!empty($dataSO)) : 
                $i = 1;
                $qty = 0;
                $qty_kirim = 0;
                $harga = 0;
                $amount = 0;
                $scanned = 0;

                foreach ($dataSO as $row) : ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row->kode_sales_order ?></td>
                        <td><?= !empty($row->style) ? $row->style : $row->deskripsi ?></td>
                        <td class="text-left"><?= formatTanggalIndonesia($row->tgl_transaksi, false) ?></td>
                        <td class="text-center"><?= $data->proses ?></td>
                        <td class="text-right"><?= $row->qty ?></td>
                        <td class="text-right"><?= $row->qty_kirim ?></td>
                        <td class="text-right"><?= !empty($row->harga) ? "Rp" . number_format(round($row->harga)) : "Rp0" ?></td>
                        <td class="text-right"><?= !empty($row->amount) ? "Rp" . number_format(round($row->amount)) : "Rp0" ?></td>
                        <td class="text-right"><?= $row->keterangan ?></td>
                    </tr>
                    <?php 
                    // Update total accumulator
                    $qty += $row->qty;
                    $qty_kirim += $row->qty_kirim;
                    $harga += !empty($row->harga) ? $row->harga : 0;
                    $amount += !empty($row->amount) ? $row->amount : 0;
                    $scanned += !empty($row->total_scanned) ? $row->total_scanned : 0;
                endforeach; 
            else : ?>
                <tr>
                    <td colspan="9" class="text-center">Data tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <!-- <tr>
                <th colspan="5">Sub Total</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr> -->
             <tr>
                <th colspan="5">Total</th>
                <th class="text-right"><?= !empty($qty) ? $qty : 0 ?></th>
                <th class="text-right"><?= !empty($qty_kirim) ? $qty_kirim : 0 ?></th>
                <th class="text-right"><?= !empty($harga) ? "Rp" . number_format(round($harga)) : "Rp0" ?></th>
                <th class="text-right"><?= !empty($amount) ? "Rp" . number_format(round($amount)) : "Rp0" ?></th>
                <th class="text-right"><?= !empty($scanned) ? $scanned.' Ikat' : 0 ?></th>
            </tr>
        </tfoot>
    </table>

    <br>
    <table class="w-100">
        <tr>
            <td style="vertical-align:top; width:100px;"><b>Note:</b></td>
            <td><?= !empty($data->keterangan) ? nl2br(htmlspecialchars($data->keterangan)) : '-' ?></td>
        </tr>
    </table>
    <br>

    <table class="w-100 font-footer">
        <tbody>
            <tr>
                <td class="w-50 text-center">&nbsp;</td>
                <td class="w-50 text-center">&nbsp;</td>
                <td class="w-50 text-center">&nbsp;</td>
            </tr>
            <tr>
                <td class="text-center">
                    <br>
                    <p><b>Pembuat,</b></p><br><br>
                    <p>( ....................... )</p>
                </td>
                <td class="text-center">
                    <br>
                    <p><b>Penerima,</b></p><br><br>
                    <p>( ....................... )</p>
                </td>
                <td class="text-center">
                    <p>
                         <?= "Bandung" ?>, <?= isset($data->tanggal) ? formatTanggalIndonesia($data->tanggal) : '' ?><br><br>
                        <b>Mengetahui,</b>
                       
                    </p><br>
                    <p>( ....................... )</p>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- <script>
        window.print()
    </script> -->
</body>

</html>