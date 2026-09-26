<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontrak Kerja Sama - <?= htmlspecialchars($karyawan->full_name ?? 'Karyawan') ?></title>
    <style>
        @page {
            margin: 1.2cm 1.8cm 1.2cm 1.8cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.8pt;
            line-height: 1.3;
            color: #111;
        }
        .page-break {
            page-break-after: always;
        }
        .watermark {
            position: fixed;
            top: 28%;
            left: 15%;
            width: 480px;
            opacity: 0.08;
            z-index: -1000;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header-logo {
            height: 48px;
            display: block;
            margin: 0 auto;
        }
        .header-address {
            text-align: center;
            font-size: 8pt;
            color: #333;
            margin-top: 3px;
        }
        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 6px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .pasal-title {
            text-align: center;
            font-weight: bold;
            font-size: 9.8pt;
            margin-top: 8px;
            margin-bottom: 1px;
        }
        .pasal-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 8.8pt;
            margin-bottom: 4px;
        }
        .ayat-title {
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 2px;
        }
        .p-text {
            text-align: justify;
            margin-bottom: 4px;
        }
        .data-table {
            width: 100%;
            margin-top: 2px;
            margin-bottom: 4px;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 8.8pt;
        }
        .ol-nested {
            margin: 2px 0 4px 18px;
            padding: 0;
        }
        .ol-nested li {
            margin-bottom: 2px;
            text-align: justify;
        }
        .ul-nested {
            margin: 2px 0 4px 18px;
            padding: 0;
            list-style-type: disc;
        }
        .ul-nested li {
            margin-bottom: 2px;
            text-align: justify;
        }
        /* TABEL TANDA TANGAN */
        .sig-container {
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .sig-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-box {
            height: 65px;
            vertical-align: middle;
            text-align: center;
        }
        .sig-img {
            max-height: 60px;
            max-width: 160px;
            display: inline-block;
        }
        .sig-placeholder {
            height: 55px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 2px;
            margin-bottom: 1px;
            font-size: 8.8pt;
        }
        .sig-role {
            font-size: 8.2pt;
            color: #222;
        }
    </style>
</head>
<body>

    <?php if (!empty($b64Watermark)): ?>
        <img src="<?= $b64Watermark ?>" class="watermark" alt="Watermark" />
    <?php endif; ?>

    <!-- ========================================== -->
    <!-- HALAMAN 1 -->
    <!-- ========================================== -->
    <div class="header-table">
        <?php if (!empty($b64Logo)): ?>
            <div style="text-align: center;">
                <img src="<?= $b64Logo ?>" class="header-logo" alt="CV. Citra Jaya Knitting" />
            </div>
        <?php else: ?>
            <div style="text-align: center; font-size: 13pt; font-weight: bold;">CV. CITRA JAYA KNITTING</div>
        <?php endif; ?>
        <div class="header-address">
            Jln Terusan Panyileukan Kavling No.3, Cipadung Kidul, Panyileukan, Kota Bandung, Jawa Barat.
        </div>
    </div>

    <div class="doc-title">KONTRAK KERJASAMA</div>

    <div class="p-text">Yang bertanda tangan di bawah ini:</div>

    <table class="data-table">
        <tr>
            <td style="width: 20px;">1.</td>
            <td style="width: 130px;">Nama</td>
            <td style="width: 10px;">:</td>
            <td style="font-weight: bold;"><?= htmlspecialchars($p1Nama) ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan</td>
            <td>:</td>
            <td><?= htmlspecialchars($p1Jabatan) ?></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" class="p-text" style="padding-top: 2px; padding-bottom: 6px;">
                Dalam hal ini bertindak untuk dan atas nama CV. Citra Jaya Knitting. Selanjutnya disebut <strong>PIHAK PERTAMA</strong>.
            </td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Nama</td>
            <td>:</td>
            <td style="font-weight: bold;"><?= htmlspecialchars($karyawan->full_name ?? '-') ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Tempat, tgl lahir</td>
            <td>:</td>
            <td><?= htmlspecialchars(($karyawan->tempat_lahir ?? '-') . ', ' . $ttlIndo) ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td><?= ($karyawan->jenis_kelamin == 1) ? 'Laki-Laki' : 'Perempuan' ?></td>
        </tr>
        <tr>
            <td></td>
            <td>Alamat</td>
            <td>:</td>
            <td><?= htmlspecialchars($karyawan->alamat ?? '-') ?></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" class="p-text" style="padding-top: 2px;">
                Dalam hal ini bertindak untuk dan atas nama diri pribadi dan selanjutnya disebut <strong>PIHAK KEDUA</strong>.
            </td>
        </tr>
    </table>

    <div class="pasal-title">PASAL 1</div>
    <div class="pasal-subtitle">MASA KERJA</div>
    <div class="p-text">
        PIHAK PERTAMA menyatakan menerima PIHAK KEDUA sebagai karyawan dengan masa kontrak selama <?= $durasi ?> (<?= $durasiTerbilang ?>) bulan (terhitung mulai tanggal <?= $tglMulaiIndo ?> sampai dengan <?= $tglSelesaiIndo ?>) di CV. Citra Jaya Knitting yang berkedudukan di Bandung dan PIHAK KEDUA dengan ini menyatakan kesediaannya.
    </div>

    <div class="pasal-title">PASAL 2</div>
    <div class="pasal-subtitle">TATA TERTIB PERUSAHAAN</div>
    <div class="p-text">
        PIHAK KEDUA menyatakan kesediannya untuk mematuhi serta mentaati seluruh peraturan tata tertib yang telah ditetapkan PIHAK PERTAMA.
    </div>

    <div class="ayat-title">Ayat (1) Jam Kerja</div>
    <ul class="ul-nested">
        <li>Senin – Kamis masuk pukul 08.00 – 17.00. istirahat pukul 12.00 – 13.00</li>
        <li>Jum’at masuk pukul 08.00 – 17.30. istirahat pukul 11.30 – 13.00</li>
        <li>Sabtu masuk pukul 08.00 – 17.00. istirahat pukul 12.00 – 13.00</li>
        <li>Batas absensi dari pukul 07.00 - 08.00</li>
        <li>Batas pulang mulai dari jam 17.00</li>
        <li>
            Apabila lembur minimal 2 jam ada waktu istirahat selama ½ jam, dengan ketentuan sebagai berikut :
            <ul style="margin: 2px 0 2px 15px; padding: 0; list-style-type: circle;">
                <li>Kepulangan Ke-1 pukul 17.00</li>
                <li>Istirahat pukul 18.00 – 18.30</li>
                <li>Kepulangan Ke-2 pukul 19.30 atau</li>
                <li>Untuk waktu kepulangan di sesuaikan dengan kebutuhan yang diperlukan .</li>
            </ul>
        </li>
        <li>Untuk Karyawan Borongan diperbolehkan pulang di awal waktu jika persediaan barang yang harus diselesaikan tidak ada (habis) dan harus ada persetujuan dari Kepala Bagian Produksi (Ibu Wanti).</li>
        <li>Jika diperlukan untuk lembur harus siap dengan catatan tidak ada upah untuk lembur</li>
        <li>
            Untuk Operator Mesin Jam Kerja 12 Jam tidak ada lembur kecuali atas Intruksi dari Atasan:
            <ul style="margin: 2px 0 2px 15px; padding: 0; list-style-type: circle;">
                <li>Shift Pagi batas absensi dari pukul 07.00 - 08.00, batas pulang mulai dari jam 20.00</li>
                <li>Shift Malam batas absensi dari pukul 19.00 - 20.00, batas pulang mulai dari jam 08.00</li>
            </ul>
        </li>
    </ul>

    <!-- ========================================== -->
    <!-- HALAMAN 2 -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <div class="ayat-title" style="margin-top: 0;">Ayat (2) Peraturan</div>
    <div class="p-text">Peraturan yang harus diikuti oleh karyawan:</div>
    <ol class="ol-nested">
        <li>Seluruh karyawan datang ke kantor/pabrik melakukan absensi sidik jari sebanyak 4 kali (masuk kerja, keluar istirahat, masuk kembali setelah istirahat, dan pulang kerja) dengan aturan yang berlaku, serta ditambah 2 kali absensi lembur (masuk lembur dan pulang lembur).</li>
        <li>Karyawan yang datang melewati pukul 08.00 WIB dinyatakan terlambat dan tidak berhak mendapatkan premi kehadiran Rp5.000 per hari dalam periode 2 (dua) minggu, meskipun keterlambatan hanya terjadi 1 (satu) kali.</li>
        <li>Jika mengabaikan kewajiban absensi maka akan dianggap mangkir / tidak hadir, kecuali ada penjelasan sakit.</li>
        <li>Apabila tidak masuk kerja karena sakit wajib membawa Surat Keterangan Dokter asli dan diserahkan ke HRD saat kembali bekerja. Surat berlaku maksimal 3 (tiga) hari, dan jika melebihi wajib melampirkan kembali dari dokter yang sama.</li>
        <li>Seluruh karyawan diharuskan masuk kerja lembur jika terdapat pekerjaan mendesak (urgent).</li>
        <li>Seluruh Karyawan Wanita disarankan berhijab, tidak menggunakan perhiasan dan makeup berlebihan.</li>
        <li>Seluruh karyawan Laki-Laki diwajibkan berpakaian rapi dan rambut rapi.</li>
        <li>Masuk dan Keluar Jam Kerja sesuai dengan Bell yang berbunyi.</li>
        <li>Jam istirahat siang digunakan untuk sholat dzuhur dan makan.</li>
        <li>Jam istirahat lembur digunakan untuk sholat dan makan.</li>
        <li>Tidak membuat kegaduhan pada saat jam kerja.</li>
        <li>Tidak merokok pada saat jam kerja dan di dalam area Produksi.</li>
        <li>Tidak menggunakan serta mengedarkan Minuman Keras dan Obat-obatan Terlarang.</li>
        <li>Menjaga hubungan baik antar karyawan.</li>
        <li>Dilarang mangkir / tidak masuk kerja tanpa alasan.</li>
        <li>Dilarang meninggalkan tugas / pekerjaan atau tempat kerja tanpa izin atasan.</li>
        <li>Dilarang tidur di waktu jam kerja.</li>
        <li>Dilarang MENGOPERASIONALKAN HANDPHONE (HP) saat kerja tanpa izin atasan.</li>
        <li>Dilarang berlama-lama di toilet selama jam kerja.</li>
        <li>Dilarang memanfaatkan toilet tidak sesuai fungsinya.</li>
        <li>Dilarang dengan sengaja merusak alat-alat kerja.</li>
        <li>Dilarang berada di tempat lain yang bukan area kerja tanpa izin atasan.</li>
        <li>Dilarang menolak perintah kerja atau bekerja tidak sesuai petunjuk atasan.</li>
        <li>Dilarang melakukan penganiayaan, berkelahi atau bertengkar.</li>
        <li>Dilarang melakukan PENIPUAN, PENCURIAN atau PENGGELAPAN barang dan/atau uang milik perusahaan atau pihak lain.</li>
        <li>Dilarang membocorkan rahasia perusahaan yang seharusnya dirahasiakan.</li>
        <li>Dilarang berjualan di lingkungan perusahaan tanpa izin pada saat waktu jam kerja.</li>
    </ol>

    <div class="ayat-title">Ayat (3) Sanksi</div>
    <div class="p-text">Pelanggaran terhadap peraturan-peraturan diatas dapat mengakibatkan Karyawan dijatuhi sanksi berupa:</div>
    <div style="margin-left: 18px; margin-bottom: 5px;">
        Peringatan Berupa:
        <ol style="margin: 2px 0 0 15px; padding: 0;">
            <li>Lisan</li>
            <li>Tulisan</li>
            <li>Pemotongan Upah</li>
            <li>Skorsing</li>
            <li>Pemutusan Hubungan Kerja (PHK).</li>
        </ol>
    </div>

    <div class="pasal-title">PASAL 3</div>
    <div class="pasal-subtitle">PENEMPATAN, TUGAS, DAN TANGGUNG JAWAB</div>
    <div class="p-text">PIHAK KEDUA bekerja untuk di bagian <strong><?= htmlspecialchars($karyawan->posisi ?? '-') ?></strong>.</div>
    <div class="p-text">PIHAK KEDUA menyatakan kesediaan dan berkewajiban untuk melakukan aktivitas sebagaimana jabatan yang telah ditentukan tersebut.</div>
    <div class="p-text">PIHAK PERTAMA berhak menempatkan PIHAK KEDUA dalam melaksanakan tugas dan pekerjaan lain yang oleh PIHAK PERTAMA dianggap lebih cocok serta sesuai dengan keahlian yang dimiliki PIHAK KEDUA.</div>

    <div class="pasal-title">PASAL 4</div>
    <div class="pasal-subtitle">GAJI DAN UPAH LEMBUR</div>
    <div class="p-text">PIHAK PERTAMA harus memberikan gaji kepada PIHAK KEDUA sebesar <strong>Rp. <?= $upahHarian ?>/hari</strong>.</div>
    <div class="p-text">Pembayaran Gaji dilakukan setiap 2 minggu sekali.</div>
    <div class="p-text">Pembayaran gantungan/sisa upah akan dibayarkan secara transfer atau cash setelah administrasi tuntas.</div>

    <div class="pasal-title">PASAL 5</div>
    <div class="pasal-subtitle">CUTI</div>
    <div class="ayat-title">Ayat (1) Ketentuan Umum Cuti</div>
    <div class="p-text">PIHAK KEDUA berhak mendapatkan cuti tahunan yang dibayar sebanyak 12 (dua belas) hari dalam 1 (satu) tahun kerja. Hak cuti tersebut hanya berlaku bagi PIHAK KEDUA yang telah bekerja minimal 1 (satu) tahun di CV. Citra Jaya Knitting. Pengajuan cuti wajib dilakukan paling lambat 3 (tiga) hari sebelum pelaksanaan cuti dengan mengajukan surat izin cuti kepada atasan. Persetujuan cuti ditentukan oleh PIHAK PERTAMA berdasarkan kondisi operasional di lapangan. Apabila dalam 1 (satu) tahun PIHAK KEDUA tidak mengambil cuti atau masih memiliki sisa cuti, maka sisa cuti tersebut dapat diuangkan sesuai dengan kebijakan perusahaan.</div>

    <div class="ayat-title">Ayat (2) Jenis-Jenis Cuti</div>
    <div class="p-text">PIHAK KEDUA berhak mengajukan cuti untuk keperluan sebagai berikut: Cuti menikah selama 3 (tiga) hari, cuti menikahkan anak selama 2 (dua) hari, cuti khitanan anak selama 2 (dua) hari, cuti istri melahirkan/keguguran selama 2 (dua) hari, cuti anggota keluarga meninggal dunia selama 1 (satu) hari, cuti anggota keluarga serumah meninggal dunia selama 2 (dua) hari, serta cuti melahirkan selama 45 (empat puluh lima) hari.</div>

    <div class="ayat-title">Ayat (3) Ketentuan Pembayaran Cuti</div>
    <div class="p-text">Cuti sebagaimana dimaksud pada Ayat (2) akan tetap dibayarkan apabila masih termasuk dalam jatah cuti tahunan sebanyak 12 (dua belas) hari. Apabila jumlah cuti yang diambil melebihi jatah cuti tahunan, maka kelebihan cuti tersebut tidak dibayarkan.</div>

    <div class="ayat-title">Ayat (4) Cuti Sakit</div>
    <div class="p-text">Cuti sakit yang dibayarkan di luar jatah cuti tahunan hanya berlaku bagi PIHAK KEDUA yang mengalami sakit hingga memerlukan perawatan medis (rawat inap). PIHAK KEDUA wajib melampirkan Surat Keterangan Dokter atau bukti rawat inap dari rumah sakit sebagai syarat pengajuan cuti sakit yang dibayarkan (sesuai Upah), dengan ketentuan maksimal 3 (tiga) hari.</div>

    <!-- ========================================== -->
    <!-- HALAMAN 3 -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <div class="pasal-title" style="margin-top: 0;">PASAL 6</div>
    <div class="pasal-subtitle">PEMUTUSAN HUBUNGAN KERJA (PHK)</div>
    <div class="p-text">Jika terjadi Pemutusan Hubungan Kerja (PHK), maka PIHAK KEDUA diharuskan mengembalikan barang-barang yang selama itu dipercayakan padanya.</div>
    <div class="p-text">PIHAK KEDUA juga diharuskan menyelesaikan hal-hal yang berhubungan dengan administrasi keuangan, seperti hutang atau pinjaman tersebut.</div>

    <div class="pasal-title">PASAL 7</div>
    <div class="pasal-subtitle">PENGUNDURAN DIRI</div>
    <div class="p-text">Pengunduran diri secara baik-baik diperlihatkan dengan cara-cara sebagai berikut:</div>
    <ol class="ol-nested">
        <li>PIHAK KEDUA telah mengajukan surat permohonan pengunduran diri maksimal 1 Bulan sebelumnya.</li>
        <li>PIHAK KEDUA tetap melaksanakan tugas dan kewajibannya hingga batas waktu pengunduran dirinya berlaku.</li>
        <li>PIHAK KEDUA telah menyerahkan barang-barang yang dipercayakan kepadanya dan juga telah menyelesaikan administrasi keuangan yang harus diselesaikan.</li>
    </ol>

    <div class="pasal-title">PASAL 8</div>
    <div class="pasal-subtitle">PENURUNAN ORDER</div>
    <div class="p-text">Apabila terjadi penurunan order selama masa kerja, PIHAK KEDUA bersedia diistirahatkan dirumah untuk sementara waktu sampai order masuk kembali dan PIHAK PERTAMA tidak wajib membayar upah kepada PIHAK KEDUA selama diistirahatkan.</div>

    <div class="pasal-title">PASAL 9</div>
    <div class="pasal-subtitle">PENYELESAIAN PERSELISIHAN</div>
    <div class="p-text">Apabila terjadi perselisihan antara kedua belah pihak, akan diselesaikan secara musyawarah untuk mencapai mufakat.</div>
    <div class="p-text">Apabila dengan cara diatas tidak tercapai kata sepakat, maka kedua belah pihak sepakat untuk menyelesaikan permasalahan tersebut melalui prosedur hukum, dengan memilih kedudukan hukum di Pengadilan setempat.</div>

    <div class="pasal-title">PASAL 10</div>
    <div class="pasal-subtitle">PENUTUP</div>
    <div class="p-text">Demikianlah perjanjian ini dibuat, disetujui, dan ditandatangani dengan keadaan sadar dan tanpa paksaan dari pihak manapun.</div>

    <div style="text-align: right; margin-top: 8px; margin-bottom: 10px;">
        Bandung, <?= $tglTtd ?>
    </div>

    <!-- BLOK TANDA TANGAN (SEMUA DI HALAMAN 3) -->
    <div class="sig-container">
        <table class="sig-table">
            <tr>
                <td class="sig-col" style="font-weight: bold;">PIHAK PERTAMA</td>
                <td class="sig-col" style="font-weight: bold;">PIHAK KEDUA</td>
            </tr>
            <tr>
                <td class="sig-col sig-box">
                    <?php if (!empty($b64Sig1)): ?>
                        <img src="<?= $b64Sig1 ?>" class="sig-img" alt="TTD Pihak 1" />
                    <?php else: ?>
                        <div class="sig-placeholder"></div>
                    <?php endif; ?>
                </td>
                <td class="sig-col sig-box">
                    <?php if (!empty($b64Sig2)): ?>
                        <img src="<?= $b64Sig2 ?>" class="sig-img" alt="TTD Pihak 2" />
                    <?php else: ?>
                        <div class="sig-placeholder"></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="sig-col">
                    <div class="sig-name"><?= htmlspecialchars($p1Nama) ?></div>
                    <div class="sig-role"><?= htmlspecialchars($p1Jabatan) ?></div>
                </td>
                <td class="sig-col">
                    <div class="sig-name">(<?= htmlspecialchars($karyawan->full_name ?? '-') ?>)</div>
                    <div class="sig-role">Karyawan</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height: 14px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold;">Mengetahui,</td>
            </tr>
            <tr>
                <td colspan="2" class="sig-box" style="text-align: center;">
                    <?php if (!empty($b64Sig3)): ?>
                        <img src="<?= $b64Sig3 ?>" class="sig-img" alt="TTD Mengetahui" />
                    <?php else: ?>
                        <div class="sig-placeholder"></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <div class="sig-name" style="display: inline-block; min-width: 150px;"><?= htmlspecialchars($p3Nama) ?></div>
                    <div class="sig-role"><?= htmlspecialchars($p3Jabatan) ?></div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
