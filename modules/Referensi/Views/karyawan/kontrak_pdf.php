<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontrak Kerjasama - <?= htmlspecialchars($karyawan->full_name ?? 'Karyawan') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.4cm 1.8cm 1.4cm 1.8cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.22;
            color: #000;
        }
        .page-break {
            page-break-after: always;
        }
        .page-num {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 10px;
            color: #000;
        }
        .watermark {
            position: fixed;
            top: 25%;
            left: 12%;
            width: 520px;
            opacity: 0.08;
            z-index: -1000;
        }
        /* KOP SURAT */
        .header-box {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-logo {
            height: 60px;
            display: block;
            margin: 0 auto 4px auto;
        }
        .header-company-name {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .header-address {
            font-size: 8.5pt;
            color: #111;
        }
        /* JUDUL DOKUMEN */
        .doc-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        /* PASAL HEADERS */
        .pasal-title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 8px;
            margin-bottom: 1px;
        }
        .pasal-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        .ayat-title {
            font-weight: bold;
            font-size: 10pt;
            margin-top: 6px;
            margin-bottom: 3px;
        }
        p {
            text-align: justify;
            margin: 0 0 5px 0;
            line-height: 1.25;
        }
        /* TABEL IDENTITAS */
        .identity-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
        }
        .identity-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.25;
        }
        .col-num {
            width: 18px;
        }
        .col-label {
            width: 135px;
            white-space: nowrap;
        }
        .col-colon {
            width: 14px;
            text-align: center;
        }
        .col-val {
            text-align: left;
        }
        /* LIST ALPHA & SUB-BULLET */
        ol.list-alpha {
            margin: 2px 0 5px 0;
            padding-left: 20px;
            list-style-type: lower-alpha;
        }
        ol.list-alpha > li {
            margin-bottom: 3px;
            text-align: justify;
            padding-left: 2px;
            line-height: 1.25;
        }
        ul.list-sub {
            margin: 2px 0 2px 0;
            padding-left: 18px;
            list-style-type: circle;
        }
        ul.list-sub > li {
            margin-bottom: 2px;
            text-align: justify;
            padding-left: 2px;
            line-height: 1.25;
        }
        ol.list-num {
            margin: 2px 0 2px 0;
            padding-left: 18px;
            list-style-type: decimal;
        }
        ol.list-num > li {
            margin-bottom: 2px;
            padding-left: 2px;
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
            padding: 0 5px;
        }
        .sig-box {
            height: 52px;
            vertical-align: middle;
            text-align: center;
        }
        .sig-img {
            max-height: 48px;
            max-width: 130px;
            display: inline-block;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 2px;
            margin-bottom: 1px;
            font-size: 10pt;
        }
        .sig-role {
            font-size: 9.5pt;
            color: #111;
        }
    </style>
</head>
<body>

    <?php if (!empty($b64Watermark)): ?>
        <img src="<?= $b64Watermark ?>" class="watermark" alt="Watermark" />
    <?php endif; ?>

    <!-- ======================================================== -->
    <!-- HALAMAN 1 -->
    <!-- ======================================================== -->
    <div class="header-box">
        <?php if (!empty($b64Logo)): ?>
            <img src="<?= $b64Logo ?>" class="header-logo" alt="Citra Jaya Knitting" />
        <?php endif; ?>
        <div class="header-company-name">CV. CITRA JAYA KNITTING</div>
        <div class="header-address">
            Jln Terusan Panyileukan Kavling No.3, Cipadung Kidul, Panyileukan, Kota Bandung, Jawa Barat.
        </div>
    </div>

    <div class="doc-title">KONTRAK KERJASAMA</div>

    <p>Yang bertanda tangan di bawah ini:</p>

    <table class="identity-table">
        <tr>
            <td class="col-label" style="padding-left: 15px;">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars($p1Nama) ?></td>
        </tr>
        <tr>
            <td class="col-label" style="padding-left: 15px;">Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars($p1Jabatan) ?></td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 2px; padding-bottom: 6px;">
                Dalam hal ini bertindak untuk dan atas nama CV. Citra Jaya Knitting. Selanjutnya disebut <strong>PIHAK PERTAMA</strong>.
            </td>
        </tr>
        <tr>
            <td class="col-label" style="padding-left: 15px;">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-val" style="font-weight: bold;"><?= htmlspecialchars($karyawan->full_name ?? '-') ?></td>
        </tr>
        <tr>
            <td class="col-label" style="padding-left: 15px;">Tempat, tgl lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars(($karyawan->tempat_lahir ?? '-') . ', ' . $ttlIndo) ?></td>
        </tr>
        <tr>
            <td class="col-label" style="padding-left: 15px;">Jenis Kelamin</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= ($karyawan->jenis_kelamin == 1) ? 'Laki-Laki' : 'Perempuan' ?></td>
        </tr>
        <tr>
            <td class="col-label" style="padding-left: 15px;">Alamat</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars($karyawan->alamat ?? '-') ?></td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 2px;">
                Dalam hal ini bertindak untuk dan atas nama diri pribadi dan selanjutnya disebut <strong>PIHAK KEDUA</strong>.
            </td>
        </tr>
    </table>

    <div class="pasal-title">PASAL 1</div>
    <div class="pasal-subtitle">MASA KERJA</div>
    <p>
        PIHAK PERTAMA menyatakan menerima PIHAK KEDUA sebagai karyawan dengan masa kontrak selama <?= $durasi ?> (<?= $durasiTerbilang ?>) bulan (terhitung mulai tanggal <?= $tglMulaiIndo ?> sampai dengan <?= $tglSelesaiIndo ?>) di CV. Citra Jaya Knitting yang berkedudukan di Bandung dan PIHAK KEDUA dengan ini menyatakan kesediaannya.
    </p>

    <div class="pasal-title">PASAL 2</div>
    <div class="pasal-subtitle">TATA TERTIB PERUSAHAAN</div>
    <p>
        PIHAK KEDUA menyatakan kesediannya untuk mematuhi serta mentaati seluruh peraturan tata tertib yang telah ditetapkan PIHAK PERTAMA.
    </p>

    <div class="ayat-title">Ayat (1) Jam Kerja</div>
    <ol class="list-alpha">
        <li>Senin – Kamis masuk pukul 08.00 – 17.00. istirahat pukul 12.00 – 13.00</li>
        <li>Jum’at masuk pukul 08 .00 – 17.30. istirahat pukul 11.30 – 13.00</li>
        <li>
            Sabtu masuk pukul 08 .00 – 17.00. istirahat pukul 12.00 – 13.00
            <ul class="list-sub">
                <li>Batas absensi dari pukul 07.00 - 08.00</li>
                <li>Batas pulang mulai dari jam 17.00</li>
            </ul>
        </li>
        <li>
            Apabila lembur minimal 2 jam ada waktu istirahat selama ½ jam, dengan ketentuan sebagai berikut :
            <ul class="list-sub">
                <li>Kepulangan Ke-1 pukul 17.00</li>
                <li>Istirahat pukul 18.00 –18.30</li>
                <li>Kepulangan Ke-2 pukul 19.30 atau</li>
                <li>Untuk waktu kepulangan di sesuaikan dengan kebutuhan yang diperlukan .</li>
            </ul>
        </li>
        <li>
            Untuk Karyawan Borongan diperbolehkan pulang di awal waktu jika persediaan barang yang harus diselesaikan tidak ada (habis) dan harus ada persetujuan dari Kepala Bagian Produksi (Ibu Wanti).
            <ul class="list-sub">
                <li>Jika diperlukan untuk lembur harus siap dengan catatan tidak ada upah untuk lembur</li>
            </ul>
        </li>
    </ol>

    <!-- ======================================================== -->
    <!-- HALAMAN 2 -->
    <!-- ======================================================== -->
    <div class="page-break"></div>
    <div class="page-num">2</div>

    <ol class="list-alpha" start="6" style="margin-top: 0;">
        <li>
            Untuk Operator Mesin Jam Kerja 12 Jam tidak ada lembur kecuali atas Intruksi dari Atasan.
            <ul class="list-sub">
                <li>Shift Pagi batas absensi dari pukul 07.00 - 08.00</li>
                <li>Batas pulang mulai dari jam 20.00</li>
                <li>Shift Malam batas absensi dari pukul 19.00 - 20.00</li>
                <li>Batas pulang mulai dari jam 08.00</li>
            </ul>
        </li>
    </ol>

    <div class="ayat-title">Ayat (2) Peraturan</div>
    <p>Peraturan yang harus diikuti oleh karyawan:</p>
    <ol class="list-alpha">
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
    <p>Pelanggaran terhadap peraturan-peraturan diatas dapat mengakibatkan Karyawan dijatuhi sanksi beupa :</p>
    <ol class="list-alpha">
        <li>
            Peringatan Berupa:
            <ol class="list-num">
                <li>Lisan</li>
                <li>Tulisan</li>
            </ol>
        </li>
        <li>Pemotongan Upah</li>
        <li>Skorsing</li>
        <li>Pemutusan Hubungan Kerja (PHK).</li>
    </ol>

    <!-- ======================================================== -->
    <!-- HALAMAN 3 -->
    <!-- ======================================================== -->
    <div class="page-break"></div>
    <div class="page-num">3</div>

    <div class="pasal-title" style="margin-top: 0;">PASAL 3</div>
    <div class="pasal-subtitle">PENEMPATAN, TUGAS, DAN TANGGUNG JAWAB</div>
    <p>PIHAK KEDUA bekerja untuk di bagian <?= htmlspecialchars($karyawan->posisi ?? '-') ?>.</p>
    <p>PIHAK KEDUA menyatakan kesediaan dan berkewajiban untuk melakukan aktivitas sebagaimana jabatan yang telah ditentukan tersebut.</p>
    <p>PIHAK PERTAMA berhak menempatkan PIHAK KEDUA dalam melaksanakan tugas dan pekerjaan lain yang oleh PIHAK PERTAMA dianggap lebih cocok serta sesuai dengan keahlian yang dimiliki PIHAK KEDUA.</p>

    <div class="pasal-title">PASAL 4</div>
    <div class="pasal-subtitle">GAJI DAN UPAH LEMBUR</div>
    <p>PIHAK PERTAMA harus memberikan gaji kepada PIHAK KEDUA sebesar Rp. <?= $upahHarian ?>/hari.</p>
    <p>Pembayaran Gaji dilakukan setiap 2 minggu sekali.</p>
    <p>Pembayaran gantungan/sisa upah akan dibayarkan secara transfer atau cash setelah administrasi tuntas.</p>

    <div class="pasal-title">PASAL 5</div>
    <div class="pasal-subtitle">CUTI</div>

    <div class="ayat-title">Ayat (1) Ketentuan Umum Cuti</div>
    <p>PIHAK KEDUA berhak mendapatkan cuti tahunan yang dibayar sebanyak 12 (dua belas) hari dalam 1 (satu) tahun kerja. Hak cuti tersebut hanya berlaku bagi PIHAK KEDUA yang telah bekerja minimal 1 (satu) tahun di CV. Citra Jaya Knitting.</p>
    <p>Pengajuan cuti wajib dilakukan paling lambat 3 (tiga) hari sebelum pelaksanaan cuti dengan mengajukan surat izin cuti kepada atasan.</p>
    <p>Persetujuan cuti ditentukan oleh PIHAK PERTAMA berdasarkan kondisi operasional di lapangan.</p>
    <p>Apabila dalam 1 (satu) tahun PIHAK KEDUA tidak mengambil cuti atau masih memiliki sisa cuti, maka sisa cuti tersebut dapat diuangkan sesuai dengan kebijakan perusahaan.</p>

    <div class="ayat-title">Ayat (2) Jenis-Jenis Cuti</div>
    <p>PIHAK KEDUA berhak mengajukan cuti untuk keperluan sebagai berikut:</p>
    <p>Cuti menikah selama 3 (tiga) hari, cuti menikahkan anak selama 2 (dua) hari, cuti khitanan anak selama 2 (dua) hari, cuti istri melahirkan/keguguran selama 2 (dua) hari, cuti anggota keluarga meninggal dunia selama 1 (satu) hari, cuti anggota keluarga serumah meninggal dunia selama 2 (dua) hari, serta cuti melahirkan selama 45 (empat puluh lima) hari.</p>

    <div class="ayat-title">Ayat (3) Ketentuan Pembayaran Cuti</div>
    <p>Cuti sebagaimana dimaksud pada Ayat (2) akan tetap dibayarkan apabila masih termasuk dalam jatah cuti tahunan sebanyak 12 (dua belas) hari.</p>
    <p>Apabila jumlah cuti yang diambil melebihi jatah cuti tahunan, maka kelebihan cuti tersebut tidak dibayarkan.</p>

    <div class="ayat-title">Ayat (4) Cuti Sakit</div>
    <p>Cuti sakit yang dibayarkan di luar jatah cuti tahunan hanya berlaku bagi PIHAK KEDUA yang mengalami sakit hingga memerlukan perawatan medis (rawat inap).</p>
    <p>PIHAK KEDUA wajib melampirkan Surat Keterangan Dokter atau bukti rawat inap dari rumah sakit sebagai syarat pengajuan cuti sakit yang dibayarkan (sesuai Upah), dengan ketentuan maksimal 3 (tiga) hari.</p>

    <!-- ======================================================== -->
    <!-- HALAMAN 4 -->
    <!-- ======================================================== -->
    <div class="page-break"></div>
    <div class="page-num">4</div>

    <div class="pasal-title" style="margin-top: 0;">PASAL 6</div>
    <div class="pasal-subtitle">PEMUTUSAN HUBUNGAN KERJA (PHK)</div>
    <p>Jika terjadi Pemutusan Hubungan Kerja (PHK), maka PIHAK KEDUA diharuskan mengembalikan barang -barang yang selama itu dipercayakan padanya.</p>
    <p>PIHAK KEDUA juga diharuskan menyelesaikan hal-hal yang berhubungan dengan administrasi keuangan, seperti hutang atau pinjaman tersebut.</p>

    <div class="pasal-title">PASAL 7</div>
    <div class="pasal-subtitle">PENGUNDURAN DIRI</div>
    <p>Pengunduran diri secara baik-baik diperlihatkan dengan cara-cara sebagai berikut:</p>
    <ol class="list-alpha">
        <li>PIHAK KEDUA telah mengajukan surat permohonan pengunduran diri maksimal 1 Bulan sebelumnya.</li>
        <li>PIHAK KEDUA tetap melaksanakan tugas dan kewajibannya hingga batas waktu pengunduran dirinya berlaku.</li>
        <li>PIHAK KEDUA telah menyerahkan barang-barang yang dipercayakan kepadanya dan juga telah menyelesaikan administrasi keuangan yang harus diselesaikan.</li>
    </ol>

    <div class="pasal-title">PASAL 8</div>
    <div class="pasal-subtitle">PENURUNAN ORDER</div>
    <p>Apabila terjadi penurunan order selama masa kerja, PIHAK KEDUA bersedia diistirahatkan dirumah untuk sementara waktu sampai order masuk kembali dan PIHAK PERTAMA tidak wajib membayar upah kepada PIHAK KEDUA selama diistirahatkan.</p>

    <div class="pasal-title">PASAL 9</div>
    <div class="pasal-subtitle">PENYELESAIAN PERSELISIHAN</div>
    <p>Apabila terjadi perselisihan antara kedua belah pihak, akan diselesaikan secara musyawarah untuk mencapai mufakat.</p>
    <p>Apabila dengan cara diatas tidak tercapai kata sepakat, maka kedua belah pihak sepakat untuk menyelesaikan permasalahan tersebut melalui prosedur hukum, dengan memilih kedudukan h ukum di Pengadilan setempat.</p>

    <div class="pasal-title">PASAL 10</div>
    <div class="pasal-subtitle">PENUTUP</div>
    <p>Demikianlah perjanjian ini dibuat, disetujui, dan ditandatangani dengan keadaan sadar dan tanpa paksaan dari pihak manapun.</p>

    <div style="text-align: right; margin-top: 6px; margin-bottom: 8px;">
        Bandung, <?= $tglTtd ?>
    </div>

    <!-- BLOK TANDA TANGAN (SEMUA DI HALAMAN 4) -->
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
                        <div style="height: 48px;"></div>
                    <?php endif; ?>
                </td>
                <td class="sig-col sig-box">
                    <?php if (!empty($b64Sig2)): ?>
                        <img src="<?= $b64Sig2 ?>" class="sig-img" alt="TTD Pihak 2" />
                    <?php else: ?>
                        <div style="height: 48px;"></div>
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
                <td colspan="2" style="height: 8px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold;">Mengetahui,</td>
            </tr>
            <tr>
                <td colspan="2" class="sig-box" style="text-align: center;">
                    <?php if (!empty($b64Sig3)): ?>
                        <img src="<?= $b64Sig3 ?>" class="sig-img" alt="TTD Mengetahui" />
                    <?php else: ?>
                        <div style="height: 48px;"></div>
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
