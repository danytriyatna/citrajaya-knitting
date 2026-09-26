<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Absensi Karyawan</title>
    <style>
        /* Pengaturan Ukuran Kertas A4 Landscape dan Margin Cetak */
        /* Landscape dipakai supaya kolom NIP-NAMA-HADIR-...-TOTAL SKOR cukup lebar
           dan tidak perlu wrap ke baris kedua. */
        @page {
            size: A4 landscape;
            margin: 15mm 12mm 15mm 12mm; /* Atas, Kanan, Bawah, Kiri */
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

        /* Wrapper utama agar ukuran di layar monitor dan cetakan presisi A4 Landscape */
        .page-container {
            width: 100%;
            max-width: 273mm; /* 297mm (lebar A4 landscape) - 24mm (total margin kanan-kiri) */
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
            width: auto;
            min-width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 7pt;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #000000;
            padding: 3px 6px;
            vertical-align: middle;
            white-space: nowrap; /* cegah teks wrap ke baris ke-2, lebar kolom mengikuti isi data */
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
        .badge-dispen { background: #e2d9f3; color: #4b2e83; }
        .skor-total { font-weight: bold; }
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
                    <th class="text-center">NO</th>
                    <th class="text-left">NIP</th>
                    <th class="text-left">NAMA</th>
                    <th class="text-center">HARI KERJA</th>
                    <th class="text-left">HADIR</th>
                    <th class="text-left">DISPEN</th>
                    <th class="text-left">TELAT</th>
                    <th class="text-left">MENIT TELAT</th>
                    <th class="text-left">IZIN</th>
                    <th class="text-left">SAKIT</th>
                    <th class="text-left">CUTI</th>
                    <th class="text-left">TANPA KETERANGAN</th>
                    <th class="text-left">SKOR</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $kelompokList = [
                    'STAFF KHUSUS' => array_filter($rekap_absensi ?? [], fn($r) => ((int)($r->kategori_posisi ?? 2)) == 3),
                    'MESIN'        => array_filter($rekap_absensi ?? [], fn($r) => ((int)($r->kategori_posisi ?? 2)) == 1),
                    'NON MESIN'    => array_filter($rekap_absensi ?? [], fn($r) => ((int)($r->kategori_posisi ?? 2)) == 2 || ((int)($r->kategori_posisi ?? 2)) > 3),
                ];
                ?>
                <?php foreach ($kelompokList as $namaKelompok => $dataKelompok) : ?>
                    <?php if (!empty($dataKelompok)) : ?>
                        <?php
                        if ($namaKelompok === 'STAFF KHUSUS') {
                            $jumlahHari = (int)($jumlah_hari_kerja_staff_khusus ?? ($jumlah_hari_kerja_non_mesin ?? 24));
                            $bgHeader = '#fff3cd'; // Amber / gold pastel
                            $colorHeader = '#856404';
                            $iconHeader = '⭐';
                        } elseif ($namaKelompok === 'MESIN') {
                            $jumlahHari = (int)($jumlah_hari_kerja_mesin ?? 23);
                            $bgHeader = '#d9e8fb'; // Blue pastel
                            $colorHeader = '#0d47a1';
                            $iconHeader = '⚙️';
                        } else {
                            $jumlahHari = (int)($jumlah_hari_kerja_non_mesin ?? 24);
                            $bgHeader = '#e9ecef'; // Gray pastel
                            $colorHeader = '#333';
                            $iconHeader = '📋';
                        }

                        // Urutkan data per kelompok dari SKOR tertinggi ke terendah (Descending)
                        usort($dataKelompok, function ($a, $b) use ($jumlahHari) {
                            $skorA = $jumlahHari > 0 ? ((float)($a->skor_periode ?? 0) / $jumlahHari) : 0;
                            $skorB = $jumlahHari > 0 ? ((float)($b->skor_periode ?? 0) / $jumlahHari) : 0;
                            if ($skorB == $skorA) {
                                return strcmp($a->nip ?? '', $b->nip ?? '');
                            }
                            return ($skorB < $skorA) ? -1 : 1;
                        });
                        ?>
                        <tr style="background-color: <?= $bgHeader ?> !important;">
                            <td colspan="13" style="padding: 6px 10px; font-weight: bold; font-size: 8pt; text-align: left; background-color: <?= $bgHeader ?> !important; color: <?= $colorHeader ?>; text-transform: uppercase; border-top: 2px solid #000;">
                                <?= $iconHeader ?> KELOMPOK: <?= $namaKelompok ?> (<?= count($dataKelompok) ?> Karyawan) — <?= $jumlahHari ?> Hari Kerja
                            </td>
                        </tr>
                        <?php $no = 1; foreach ($dataKelompok as $rekap) :
                            $hadirFisik = (int)($rekap->total_hadir_tepat_waktu ?? 0);
                            $dispen = (int)($rekap->total_dispen ?? 0);
                            $hadir = $hadirFisik + $dispen; // Kolom Hadir = Hadir + Dispen
                            $telat = (int)($rekap->total_telat ?? 0);
                            $menit = (int)($rekap->total_menit_terlambat ?? 0);
                            $izin  = (int)($rekap->total_izin ?? 0);
                            $sakit = (int)($rekap->total_sakit ?? 0);
                            $cuti  = (int)($rekap->total_cuti ?? 0);
                            $tanpa = (int)($rekap->total_alpa ?? 0);
                            $earlyBird = (int)($rekap->total_early_bird ?? 0);
                            $skorTotal = isset($rekap->skor_periode) && $rekap->skor_periode !== null ? (float)$rekap->skor_periode : 0;
                            $skor = number_format($jumlahHari > 0 ? ($skorTotal / $jumlahHari) : 0, 2, ',', '');
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="text-left"><?php echo !empty($rekap->nip) ? $rekap->nip : '-'; ?></td>
                                <td class="text-left"><?php echo !empty($rekap->full_name) ? $rekap->full_name : '-'; ?></td>
                                <td class="text-center"><?php echo $jumlahHari; ?> Hari</td>
                                <td class="text-center">
                                    <?= badge($hadir, 'Hari', 'badge-hadir') ?>
                                    <?php if ($earlyBird > 0): ?>
                                        <span title="<?= $earlyBird ?>x Terawal (+<?= $earlyBird * 0.5 ?> Poin)" style="display:inline-block; font-size:6.5pt; font-weight:bold; color:#b45309; background:#fef3c7; border:1px solid #fcd34d; border-radius:2px; padding:0 3px; margin-left:2px;">⭐<?= $earlyBird ?>x</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= badge($dispen, 'Hari', 'badge-dispen') ?></td>
                                <td class="text-center"><?= badge($telat, 'Hari', 'badge-telat') ?></td>
                                <td class="text-center"><?= badge($menit, 'Menit', 'badge-telat') ?></td>
                                <td class="text-center"><?= badge($izin,  'Hari', 'badge-izin') ?></td>
                                <td class="text-center"><?= badge($sakit, 'Hari', 'badge-sakit') ?></td>
                                <td class="text-center"><?= badge($cuti,  'Hari', 'badge-cuti') ?></td>
                                <td class="text-center"><?= badge($tanpa, 'Hari', 'badge-alpa') ?></td>
                                <td class="text-right skor-total"><?php echo $skor; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
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