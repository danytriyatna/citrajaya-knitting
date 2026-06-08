<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Absensi Karyawan</title>
    <style>
        /* Pengaturan Ukuran Kertas A4 dan Margin Cetak */
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm; /* Atas, Kanan, Bawah, Kiri */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Wrapper utama agar ukuran di layar monitor dan cetakan presisi A4 */
        .page-container {
            width: 100%;
            max-width: 180mm; /* 210mm (lebar A4) - 30mm (total margin kanan-kiri) */
            margin: 0 auto;
        }

        /* Header Style */
        .header-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 12%;
        }
        .meta-table td.colon {
            width: 2%;
        }

        /* Table Base Style */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 7pt;
        }
        table.data-table th, 
        table.data-table td {
            border: 1px solid #000000;
            padding: 3px 3px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #D9E1F2 !important; 
            font-weight: bold;
            text-align: center;
        }

        /* Status Warna (Wajib memakai !important untuk cetak browser) */
        .status-hadir {
            background-color: #00B050 !important;
            color: black;
        }
        .status-telat {
            background-color: #FFFF00 !important;
            color: black;
        }
        .status-sakit {
            background-color: #00B0F0 !important;
            color: black;
        }
        .status-alpa {
            background-color: #FFC000 !important;
            color: red;
        }
        .row-empty {
            background-color: #D9D9D9 !important;
        }

        /* Alignments */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* Optimasi Pemisahan Halaman Jika Data Sangat Banyak */
        @media print {
            body { background: none; }
            .page-container { width: 100%; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }

         .badge {
            display: inline-block;
            font-family: 'IBM Plex Mono', monospace;
            border-radius: 3px;
            padding-left: 3px;
            padding-right: 3px;
        }
        .badge-hadir { background: #d4edda; color: #155724; }
        .badge-telat { background: #fff3cd; color: #856404; }
        .badge-zero  { background: #ececea; color: #4a4845; }
        .badge-izin  { background: #cce5ff; color: #004085; }
        .badge-sakit { background: #cce5ff; color: #004085; }
        .badge-cuti  { background: #cce5ff; color: #004085; }
        .badge-alpa  { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php
        // Helper badge
        function badge(int $val, string $unit, string $activeClass = 'badge-hadir'): string {
            $cls = $val > 0 ? $activeClass : 'badge-zero';
            return "<span class=\"badge {$cls}\">{$val} {$unit}</span>";
        }
        ?>
    <div class="page-container">
        
        <div class="header-title">Laporan Rekap Absensi Karyawan<br><?php echo !empty($periode_awal) ? $periode_awal : '-'; ?> - <?php echo !empty($periode_akhir) ? $periode_akhir : '-'; ?></div>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 9%;">NIP</th>
                    <th class="text-left" style="width: 25%;">NAMA</th>
                    <th class="text-left" style="width: 8%;">HADIR</th>
                    <th class="text-left" style="width: 8%;">TELAT</th>
                    <th class="text-left" style="width: 8%;">MENIT TELAT</th>
                    <th class="text-left" style="width: 8%;">IZIN</th>
                    <th class="text-left" style="width: 8%;">SAKIT</th>
                    <th class="text-left" style="width: 8%;">CUTI</th>
                    <th class="text-left" style="width: 8%;">TANPA KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekap_absensi as $rekap) : 
                    $hadir = (int)($rekap->total_hadir_tepat_waktu            ?? 0);
                    $telat = (int)($rekap->total_telat            ?? 0);
                    $menit = (int)($rekap->total_menit_terlambat      ?? 0);
                    $izin  = (int)($rekap->total_izin             ?? 0);
                    $sakit = (int)($rekap->total_sakit            ?? 0);
                    $cuti  = (int)($rekap->total_cuti             ?? 0);
                    $tanpa = (int)($rekap->total_alpa ?? 0);?>
                    <tr>
                        <td class="text-left"><?php echo !empty($rekap->nip) ? $rekap->nip : '-'; ?></td>
                        <td class="text-left"><?php echo !empty($rekap->full_name) ? $rekap->full_name : '-'; ?></td>
                        <td class="text-center"><?= badge($hadir, 'Hari', 'badge-hadir') ?></td>
                        <td class="text-center"><?= badge($telat, 'Hari', 'badge-telat') ?></td>
                        <td class="text-center"><?= badge($menit, 'Menit', 'badge-telat') ?></td>
                        <td class="text-center"><?= badge($izin,  'Hari', 'badge-izin') ?></td>
                        <td class="text-center"><?= badge($sakit, 'Hari', 'badge-sakit') ?></td>
                        <td class="text-center"><?= badge($cuti,  'Hari', 'badge-cuti') ?></td>
                        <td class="text-center"><?= badge($tanpa, 'Hari', 'badge-alpa') ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>