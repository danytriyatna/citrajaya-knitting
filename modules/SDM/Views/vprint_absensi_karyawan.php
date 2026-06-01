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
            font-size: 14pt;
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
            font-size: 10pt;
        }
        table.data-table th, 
        table.data-table td {
            border: 1px solid #000000;
            padding: 6px 8px;
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
    </style>
</head>
<body>

    <div class="page-container">
        
        <div class="header-title">Laporan Rekap Absensi Karyawan</div>

        <table class="meta-table">
            <tr>
                <td class="label">NAMA</td>
                <td class="colon">:</td>
                <td><strong><?php echo !empty($karyawan->full_name) ? $karyawan->full_name : '-'; ?></strong></td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td class="colon">:</td>
                <td><?php echo !empty($karyawan->nip) ? $karyawan->nip : '-'; ?></td>
            </tr>
            <tr>
                <td class="label">PERIODE</td>
                <td class="colon">:</td>
                <td><?php echo !empty($karyawan->periode_awal) ? $karyawan->periode_awal : '-'; ?> - <?php echo !empty($karyawan->periode_akhir) ? $karyawan->periode_akhir : '-'; ?></td>
            </tr>
        </table>

        <div class="section-title">Rekap Absensi</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 60%;">Keterangan</th>
                    <th class="text-right" style="width: 40%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Hadir</td><td class="text-center"><?php echo !empty($rekap_absensi->total_hadir_tepat_waktu) ? $rekap_absensi->total_hadir_tepat_waktu : '0'; ?> Hari</td></tr>
                <tr><td>Telat</td><td class="text-center"><?php echo !empty($rekap_absensi->total_telat) ? $rekap_absensi->total_telat : '0'; ?> Hari</td></tr>
                <tr><td>Total Menit Telat</td><td class="text-center"><?php echo !empty($rekap_absensi->total_menit_terlambat) ? (float)$rekap_absensi->total_menit_terlambat : '0'; ?> Menit</td></tr>
                <tr><td>Izin</td><td class="text-center"><?php echo !empty($rekap_absensi->total_izin) ? $rekap_absensi->total_izin : '0'; ?> Hari</td></tr>
                <tr><td>Sakit</td><td class="text-center"><?php echo !empty($rekap_absensi->total_sakit) ? $rekap_absensi->total_sakit : '0'; ?> Hari</td></tr>
                <tr><td>Cuti</td><td class="text-center"><?php echo !empty($rekap_absensi->total_cuti) ? $rekap_absensi->total_cuti : '0'; ?> Hari</td></tr>
                <tr><td>Tanpa Keterangan</td><td class="text-center"><?php echo !empty($rekap_absensi->total_alpa) ? $rekap_absensi->total_alpa : '0'; ?> Hari</td></tr>
            </tbody>
        </table>

        <div class="section-title">Detail Absensi Harian</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Tanggal</th>
                    <th style="width: 20%;">Scan Masuk</th>
                    <th style="width: 20%;">Scan Pulang</th>
                    <th style="width: 20%;">Terlambat (Menit)</th>
                    <th style="width: 20%;">Status</th>
                </tr>
            </thead>
            <tbody>
              <?php foreach ($detail_absensi as $absen) : ?>
                <tr>
                    <td class="text-center"><?php echo !empty($absen->tgl_absen) ? $absen->tgl_absen : '-'; ?></td>
                    <td class="text-center"><?php echo !empty($absen->jam_masuk) ? date('H:i:s', strtotime($absen->jam_masuk)) : ''; ?></td>
                    <td class="text-center"><?php echo !empty($absen->jam_keluar) ? date('H:i:s', strtotime($absen->jam_keluar)) : ''; ?></td>
                    <td class="text-center"><?php echo !empty($absen->terlambat) ? $absen->terlambat : '0'; ?></td>
                    <?php 
                      $statusClass = '';
                      if ($absen->status_text == 'Hadir') {
                          $statusClass = 'status-hadir';
                      } elseif ($absen->status_text == 'Telat') {
                          $statusClass = 'status-telat';
                      } elseif ($absen->status_text == 'Sakit') {
                          $statusClass = 'status-sakit';
                      }
                      else if ($absen->status_text == 'Izin') {
                          $statusClass = 'status-izin';
                      }
                      else if ($absen->status_text == 'Tanpa Keterangan') {
                          $statusClass = 'status-alpa';
                      }
                    ?>
                    <td class="text-center <?php echo $statusClass; ?>"><?php echo !empty($absen->status_text) ? $absen->status_text : '-'; ?></td>
                </tr>
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