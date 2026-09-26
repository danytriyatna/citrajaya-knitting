<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontrak Kerjasama - <?= htmlspecialchars($karyawan->full_name ?? 'Karyawan') ?></title>
    <style>
        @page {
            size: 595.32pt 841.92pt;
            margin: 36pt 36pt 36pt 36pt;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
        }
        .page-break {
            page-break-after: always;
        }
        .watermark {
            position: fixed;
            top: 28%;
            left: 10%;
            width: 480px;
            opacity: 0.08;
            z-index: -1000;
        }
        p {
            text-align: justify;
            margin: 0 0 11.5pt 0;
            line-height: 1.2;
        }
        /* KOP SURAT */
        .header-box {
            text-align: center;
            border-bottom: 1.5pt solid #000;
            padding-bottom: 5pt;
            margin-bottom: 14pt;
        }
        .header-logo {
            height: 56px;
            display: block;
            margin: 0 auto 3pt auto;
        }
        .header-company-name {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 2pt;
        }
        .header-address {
            font-size: 9pt;
            color: #000;
        }
        /* JUDUL DOKUMEN */
        .doc-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin-top: 14pt;
            margin-bottom: 12pt;
            letter-spacing: 0.5px;
        }
        /* PASAL HEADERS */
        .pasal-title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin-top: 12pt;
            margin-bottom: 1pt;
        }
        .pasal-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 12pt;
        }
        .ayat-title {
            font-weight: bold;
            font-size: 10pt;
            margin-top: 12pt;
            margin-bottom: 10pt;
        }
        /* TABEL IDENTITAS */
        .identity-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 11.5pt 0;
        }
        .identity-table td {
            padding: 1.5pt 0;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.2;
        }
        .col-indent {
            width: 18pt;
        }
        .col-label {
            width: 110pt;
            white-space: nowrap;
        }
        .col-colon {
            width: 15pt;
            text-align: center;
        }
        .col-val {
            text-align: left;
        }
        /* DAFTAR LIST PRESISI WORD */
        .list-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .list-table td {
            padding: 0 0 1.2pt 0;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.2;
        }
        .col-mark {
            width: 18pt;
            padding-left: 18pt;
            white-space: nowrap;
        }
        .col-submark {
            width: 18pt;
            padding-left: 45pt;
            white-space: nowrap;
        }
        .col-text {
            text-align: justify;
        }
        /* PASAL 5 INDENTS */
        .pasal5-block {
            margin-left: 36pt;
            margin-bottom: 11.5pt;
        }
        .pasal5-block p {
            margin: 0 0 11.5pt 0;
        }
        /* TABEL TANDA TANGAN */
        .sig-container {
            width: 100%;
            margin-top: 12pt;
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
            padding: 0 8pt;
        }
        .sig-box {
            height: 46px;
            vertical-align: bottom;
            text-align: center;
            padding-bottom: 3pt;
        }
        .sig-img {
            max-height: 40px;
            max-width: 120px;
            display: inline-block;
            vertical-align: bottom;
        }
        .sig-line-name {
            display: inline-block;
            min-width: 140px;
            border-bottom: 1pt solid #000;
            padding-bottom: 1px;
            font-size: 10pt;
        }
        .sig-name {
            font-size: 10pt;
        }
        .sig-role {
            font-size: 10pt;
            margin-top: 2pt;
            color: #000;
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

    <p style="margin-bottom: 10pt;">Yang bertanda tangan di bawah ini:</p>

    <table class="identity-table">
        <tr>
            <td class="col-indent"></td>
            <td class="col-label">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars($p1Nama) ?></td>
        </tr>
        <tr>
            <td class="col-indent"></td>
            <td class="col-label">Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars($p1Jabatan) ?></td>
        </tr>
        <tr>
            <td colspan="4" style="padding-top: 6pt; padding-bottom: 8pt;">
                Dalam hal ini bertindak untuk dan atas nama CV. Citra Jaya Knitting. Selanjutnya disebut <strong>PIHAK PERTAMA</strong>.
            </td>
        </tr>
        <tr>
            <td class="col-indent"></td>
            <td class="col-label">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-val" style="font-weight: bold;"><?= htmlspecialchars(strtoupper($karyawan->full_name ?? '-')) ?></td>
        </tr>
        <tr>
            <td class="col-indent"></td>
            <td class="col-label">Tempat, tgl lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars(strtoupper($karyawan->tempat_lahir ?? '-') . ', ' . $ttlIndo) ?></td>
        </tr>
        <tr>
            <td class="col-indent"></td>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= ($karyawan->jenis_kelamin == 1) ? 'Laki-Laki' : 'Perempuan' ?></td>
        </tr>
        <tr>
            <td class="col-indent"></td>
            <td class="col-label">Alamat</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= htmlspecialchars(strtoupper($karyawan->alamat ?? '-')) ?></td>
        </tr>
        <tr>
            <td colspan="4" style="padding-top: 6pt; padding-bottom: 4pt;">
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
    <table class="list-table">
        <tr>
            <td class="col-mark">a.</td>
            <td class="col-text">Senin – Kamis masuk pukul 08.00 – 17.00. istirahat pukul 12.00 – 13.00</td>
        </tr>
        <tr>
            <td class="col-mark">b.</td>
            <td class="col-text">Jum’at masuk pukul 08 .00 – 17.30. istirahat pukul 11.30 – 13.00</td>
        </tr>
        <tr>
            <td class="col-mark">c.</td>
            <td class="col-text">Sabtu masuk pukul 08 .00 – 17.00. istirahat pukul 12.00 – 13.00</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Batas absensi dari pukul 07.00 - 08.00</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Batas pulang mulai dari jam 17.00</td>
        </tr>
        <tr>
            <td class="col-mark">d.</td>
            <td class="col-text">Apabila lembur minimal 2 jam ada waktu istirahat selama ½ jam, dengan ketentuan sebagai berikut :</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Kepulangan Ke-1 pukul 17.00</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Istirahat pukul 18.00 –18.30</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Kepulangan Ke-2 pukul 19.30 atau</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Untuk waktu kepulangan di sesuaikan dengan kebutuhan yang diperlukan .</td>
        </tr>
        <tr>
            <td class="col-mark">e.</td>
            <td class="col-text">Untuk Karyawan Borongan diperbolehkan pulang di awal waktu jika persediaan barang yang harus diselesaikan tidak ada (habis) dan harus ada persetujuan dari Kepala Bagian Produksi (Ibu Wanti).</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Jika diperlukan untuk lembur harus siap dengan catatan tidak ada upah untuk lembur</td>
        </tr>
    </table>

    <!-- ======================================================== -->
    <!-- HALAMAN 2 -->
    <!-- ======================================================== -->
    <div class="page-break"></div>

    <table class="list-table" style="margin-top: 0; margin-bottom: 12pt;">
        <tr>
            <td class="col-mark">f.</td>
            <td class="col-text">Untuk Operator Mesin Jam Kerja 12 Jam tidak ada lembur kecuali atas Intruksi dari Atasan.</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Shift Pagi batas absensi dari pukul 07.00 - 08.00</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Batas pulang mulai dari jam 20.00</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Shift Malam batas absensi dari pukul 19.00 - 20.00</td>
        </tr>
        <tr>
            <td class="col-submark">o</td>
            <td class="col-text">Batas pulang mulai dari jam 08.00</td>
        </tr>
    </table>

    <div class="ayat-title">Ayat (2) Peraturan</div>
    <p>Peraturan yang harus diikuti oleh karyawan:</p>
    <table class="list-table" style="margin-bottom: 12pt;">
        <tr><td class="col-mark">a.</td><td class="col-text">Seluruh karyawan datang ke kantor/pabrik melakukan absensi sidik jari sebanyak 4 kali (masuk kerja, keluar istirahat, masuk kembali setelah istirahat, dan pulang kerja) dengan aturan yang berlaku, serta ditambah 2 kali absensi lembur (masuk lembur dan pulang lembur).</td></tr>
        <tr><td class="col-mark">b.</td><td class="col-text">Karyawan yang datang melewati pukul 08.00 WIB dinyatakan terlambat dan tidak berhak mendapatkan premi kehadiran Rp5.000 per hari dalam periode 2 (dua) minggu, meskipun keterlambatan hanya terjadi 1 (satu) kali.</td></tr>
        <tr><td class="col-mark">c.</td><td class="col-text">Jika mengabaikan kewajiban absensi maka akan dianggap mangkir / tidak hadir, kecuali ada penjelasan sakit.</td></tr>
        <tr><td class="col-mark">d.</td><td class="col-text">Apabila tidak masuk kerja karena sakit wajib membawa Surat Keterangan Dokter asli dan diserahkan ke HRD saat kembali bekerja. Surat berlaku maksimal 3 (tiga) hari, dan jika melebihi wajib melampirkan kembali dari dokter yang sama.</td></tr>
        <tr><td class="col-mark">e.</td><td class="col-text">Seluruh karyawan diharuskan masuk kerja lembur jika terdapat pekerjaan mendesak (urgent).</td></tr>
        <tr><td class="col-mark">f.</td><td class="col-text">Seluruh Karyawan Wanita disarankan berhijab, tidak menggunakan perhiasan dan makeup berlebihan.</td></tr>
        <tr><td class="col-mark">g.</td><td class="col-text">Seluruh karyawan Laki-Laki diwajibkan berpakaian rapi dan rambut rapi.</td></tr>
        <tr><td class="col-mark">h.</td><td class="col-text">Masuk dan Keluar Jam Kerja sesuai dengan Bell yang berbunyi.</td></tr>
        <tr><td class="col-mark">i.</td><td class="col-text">Jam istirahat siang digunakan untuk sholat dzuhur dan makan.</td></tr>
        <tr><td class="col-mark">j.</td><td class="col-text">Jam istirahat lembur digunakan untuk sholat dan makan.</td></tr>
        <tr><td class="col-mark">k.</td><td class="col-text">Tidak membuat kegaduhan pada saat jam kerja.</td></tr>
        <tr><td class="col-mark">l.</td><td class="col-text">Tidak merokok pada saat jam kerja dan di dalam area Produksi.</td></tr>
        <tr><td class="col-mark">m.</td><td class="col-text">Tidak menggunakan serta mengedarkan Minuman Keras dan Obat-obatan Terlarang.</td></tr>
        <tr><td class="col-mark">n.</td><td class="col-text">Menjaga hubungan baik antar karyawan.</td></tr>
        <tr><td class="col-mark">o.</td><td class="col-text">Dilarang mangkir / tidak masuk kerja tanpa alasan.</td></tr>
        <tr><td class="col-mark">p.</td><td class="col-text">Dilarang meninggalkan tugas / pekerjaan atau tempat kerja tanpa izin atasan.</td></tr>
        <tr><td class="col-mark">q.</td><td class="col-text">Dilarang tidur di waktu jam kerja.</td></tr>
        <tr><td class="col-mark">r.</td><td class="col-text">Dilarang MENGOPERASIONALKAN HANDPHONE (HP) saat kerja tanpa izin atasan.</td></tr>
        <tr><td class="col-mark">s.</td><td class="col-text">Dilarang berlama-lama di toilet selama jam kerja.</td></tr>
        <tr><td class="col-mark">t.</td><td class="col-text">Dilarang memanfaatkan toilet tidak sesuai fungsinya.</td></tr>
        <tr><td class="col-mark">u.</td><td class="col-text">Dilarang dengan sengaja merusak alat-alat kerja.</td></tr>
        <tr><td class="col-mark">v.</td><td class="col-text">Dilarang berada di tempat lain yang bukan area kerja tanpa izin atasan.</td></tr>
        <tr><td class="col-mark">w.</td><td class="col-text">Dilarang menolak perintah kerja atau bekerja tidak sesuai petunjuk atasan.</td></tr>
        <tr><td class="col-mark">x.</td><td class="col-text">Dilarang melakukan penganiayaan, berkelahi atau bertengkar.</td></tr>
        <tr><td class="col-mark">y.</td><td class="col-text">Dilarang melakukan PENIPUAN, PENCURIAN atau PENGGELAPAN barang dan/atau uang milik perusahaan atau pihak lain.</td></tr>
        <tr><td class="col-mark">z.</td><td class="col-text">Dilarang membocorkan rahasia perusahaan yang seharusnya dirahasiakan.</td></tr>
        <tr><td class="col-mark">å.</td><td class="col-text">Dilarang berjualan di lingkungan perusahaan tanpa izin pada saat waktu jam kerja.</td></tr>
    </table>

    <div class="ayat-title">Ayat (3) Sanksi</div>
    <p>Pelanggaran terhadap peraturan-peraturan diatas dapat mengakibatkan Karyawan dijatuhi <em>sanksi</em> beupa :</p>
    <table class="list-table">
        <tr>
            <td class="col-mark">a.</td>
            <td class="col-text">Peringatan Berupa:</td>
        </tr>
        <tr>
            <td class="col-submark">1.</td>
            <td class="col-text">Lisan</td>
        </tr>
        <tr>
            <td class="col-submark">2.</td>
            <td class="col-text">Tulisan</td>
        </tr>
        <tr>
            <td class="col-mark"><em>b.</em></td>
            <td class="col-text">Pemotongan Upah</td>
        </tr>
        <tr>
            <td class="col-mark"><em>c.</em></td>
            <td class="col-text">Skorsing</td>
        </tr>
        <tr>
            <td class="col-mark">d.</td>
            <td class="col-text">Pemutusan Hubungan Kerja (PHK).</td>
        </tr>
    </table>

    <!-- ======================================================== -->
    <!-- HALAMAN 3 -->
    <!-- ======================================================== -->
    <div class="page-break"></div>

    <div class="pasal-title" style="margin-top: 0;">PASAL 3</div>
    <div class="pasal-subtitle">PENEMPATAN, TUGAS, DAN TANGGUNG JAWAB</div>
    <p>PIHAK KEDUA bekerja untuk di bagian <?= strtoupper(htmlspecialchars($karyawan->posisi ?? '-')) ?>.</p>
    <p>PIHAK KEDUA menyatakan kesediaan dan berkewajiban untuk melakukan aktivitas sebagaimana jabatan yang telah ditentukan tersebut.</p>
    <p>PIHAK PERTAMA berhak menempatkan PIHAK KEDUA dalam melaksanakan tugas dan pekerjaan lain yang oleh PIHAK PERTAMA dianggap lebih cocok serta sesuai dengan keahlian yang dimiliki PIHAK KEDUA.</p>

    <div class="pasal-title">PASAL 4</div>
    <div class="pasal-subtitle">GAJI DAN UPAH LEMBUR</div>
    <p>PIHAK PERTAMA harus memberikan gaji kepada PIHAK KEDUA sebesar Rp. <?= $upahHarian ?>/hari.</p>
    <p>Pembayaran Gaji dilakukan setiap 2 minggu sekali.</p>
    <p>Pembayaran gantungan/sisa upah akan dibayarkan secara <em>transfer</em> atau <em>cash</em> setelah administrasi tuntas.</p>

    <div class="pasal-title">PASAL 5</div>
    <div class="pasal-subtitle">CUTI</div>

    <div class="ayat-title">Ayat (1) Ketentuan Umum Cuti</div>
    <div class="pasal5-block">
        <p>PIHAK KEDUA berhak mendapatkan cuti tahunan yang dibayar sebanyak 12 (dua belas) hari dalam 1 (satu) tahun kerja. Hak cuti tersebut hanya berlaku bagi PIHAK KEDUA yang telah bekerja minimal 1 (satu) tahun di CV. Citra Jaya Knitting.</p>
        <p>Pengajuan cuti wajib dilakukan paling lambat 3 (tiga) hari sebelum pelaksanaan cuti dengan mengajukan surat izin cuti kepada atasan.</p>
        <p>Persetujuan cuti ditentukan oleh PIHAK PERTAMA berdasarkan kondisi operasional di lapangan.</p>
        <p>Apabila dalam 1 (satu) tahun PIHAK KEDUA tidak mengambil cuti atau masih memiliki sisa cuti, maka sisa cuti tersebut dapat diuangkan sesuai dengan kebijakan perusahaan.</p>
    </div>

    <div class="ayat-title">Ayat (2) Jenis-Jenis Cuti</div>
    <div class="pasal5-block">
        <p>PIHAK KEDUA berhak mengajukan cuti untuk keperluan sebagai berikut:</p>
        <p>Cuti menikah selama 3 (tiga) hari, cuti menikahkan anak selama 2 (dua) hari, cuti khitanan anak selama 2 (dua) hari, cuti istri melahirkan/keguguran selama 2 (dua) hari, cuti anggota keluarga meninggal dunia selama 1 (satu) hari, cuti anggota keluarga serumah meninggal dunia selama 2 (dua) hari, serta cuti melahirkan selama 45 (empat puluh lima) hari.</p>
    </div>

    <div class="ayat-title">Ayat (3) Ketentuan Pembayaran Cuti</div>
    <div class="pasal5-block">
        <p>Cuti sebagaimana dimaksud pada Ayat (2) akan tetap dibayarkan apabila masih termasuk dalam jatah cuti tahunan sebanyak 12 (dua belas) hari.</p>
        <p>Apabila jumlah cuti yang diambil melebihi jatah cuti tahunan, maka kelebihan cuti tersebut tidak dibayarkan.</p>
    </div>

    <div class="ayat-title">Ayat (4) Cuti Sakit</div>
    <div class="pasal5-block">
        <p>Cuti sakit yang dibayarkan di luar jatah cuti tahunan hanya berlaku bagi PIHAK KEDUA yang mengalami sakit hingga memerlukan perawatan medis (rawat inap).</p>
        <p>PIHAK KEDUA wajib melampirkan Surat Keterangan Dokter atau bukti rawat inap dari rumah sakit sebagai syarat pengajuan cuti sakit yang dibayarkan (sesuai Upah), dengan ketentuan maksimal 3 (tiga) hari.</p>
    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 4 -->
    <!-- ======================================================== -->
    <div class="page-break"></div>

    <div class="pasal-title" style="margin-top: 0;">PASAL 6</div>
    <div class="pasal-subtitle">PEMUTUSAN HUBUNGAN KERJA (PHK)</div>
    <p>Jika terjadi Pemutusan Hubungan Kerja (PHK), maka PIHAK KEDUA diharuskan mengembalikan barang-barang yang selama itu dipercayakan padanya.</p>
    <p>PIHAK KEDUA juga diharuskan menyelesaikan hal-hal yang berhubungan dengan administrasi keuangan, seperti hutang atau pinjaman tersebut.</p>

    <div class="pasal-title">PASAL 7</div>
    <div class="pasal-subtitle">PENGUNDURAN DIRI</div>
    <p>Pengunduran diri secara baik-baik diperlihatkan dengan cara-cara sebagai berikut:</p>
    <table class="list-table" style="margin-bottom: 11.5pt;">
        <tr>
            <td class="col-mark">a.</td>
            <td class="col-text">PIHAK KEDUA telah mengajukan surat permohonan pengunduran diri maksimal 1 Bulan sebelumnya.</td>
        </tr>
        <tr>
            <td class="col-mark">b.</td>
            <td class="col-text">PIHAK KEDUA tetap melaksanakan tugas dan kewajibannya hingga batas waktu pengunduran dirinya berlaku.</td>
        </tr>
        <tr>
            <td class="col-mark">c.</td>
            <td class="col-text">PIHAK KEDUA telah menyerahkan barang-barang yang dipercayakan kepadanya dan juga telah menyelesaikan administrasi keuangan yang harus diselesaikan.</td>
        </tr>
    </table>

    <div class="pasal-title">PASAL 8</div>
    <div class="pasal-subtitle">PENURUNAN ORDER</div>
    <p>Apabila terjadi penurunan order selama masa kerja, PIHAK KEDUA bersedia diistirahatkan dirumah untuk sementara waktu sampai order masuk kembali dan PIHAK PERTAMA tidak wajib membayar upah kepada PIHAK KEDUA selama diistirahatkan.</p>

    <div class="pasal-title">PASAL 9</div>
    <div class="pasal-subtitle">PENYELESAIAN PERSELISIHAN</div>
    <p>Apabila terjadi perselisihan antara kedua belah pihak, akan diselesaikan secara musyawarah untuk mencapai mufakat.</p>
    <p>Apabila dengan cara diatas tidak tercapai kata sepakat, maka kedua belah pihak sepakat untuk menyelesaikan permasalahan tersebut melalui prosedur hukum, dengan memilih kedudukan hukum di Pengadilan setempat.</p>

    <div class="pasal-title">PASAL 10</div>
    <div class="pasal-subtitle">PENUTUP</div>
    <p>Demikianlah perjanjian ini dibuat, disetujui, dan ditandatangani dengan keadaan sadar dan tanpa paksaan dari pihak manapun.</p>

    <table style="width: 100%; margin-top: 14pt; margin-bottom: 8pt;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center; font-size: 10pt;">
                Bandung, <?= $tglTtd ?>
            </td>
        </tr>
    </table>

    <!-- BLOK TANDA TANGAN (SEMUA DI HALAMAN 4) -->
    <div class="sig-container">
        <table class="sig-table">
            <tr>
                <td class="sig-col" style="font-weight: bold; padding-bottom: 6pt;">PIHAK PERTAMA</td>
                <td class="sig-col" style="font-weight: bold; padding-bottom: 6pt;">PIHAK KEDUA</td>
            </tr>
            <tr>
                <td class="sig-col sig-box">
                    <?php if (!empty($b64Sig1)): ?>
                        <img src="<?= $b64Sig1 ?>" class="sig-img" alt="TTD Pihak 1" />
                    <?php else: ?>
                        <div style="height: 38px;"></div>
                    <?php endif; ?>
                </td>
                <td class="sig-col sig-box">
                    <?php if (!empty($b64Sig2)): ?>
                        <img src="<?= $b64Sig2 ?>" class="sig-img" alt="TTD Pihak 2" />
                    <?php else: ?>
                        <div style="height: 38px;"></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="sig-col">
                    <span class="sig-line-name"><?= htmlspecialchars($p1Nama) ?></span>
                    <div class="sig-role"><?= htmlspecialchars($p1Jabatan) ?></div>
                </td>
                <td class="sig-col">
                    <div class="sig-name">(<?= htmlspecialchars(strtoupper($karyawan->full_name ?? '-')) ?>)</div>
                    <div class="sig-role" style="visibility: hidden;">&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height: 10pt;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold; padding-bottom: 6pt;">Mengetahui,</td>
            </tr>
            <tr>
                <td colspan="2" class="sig-box" style="text-align: center;">
                    <?php if (!empty($b64Sig3)): ?>
                        <img src="<?= $b64Sig3 ?>" class="sig-img" alt="TTD Mengetahui" />
                    <?php else: ?>
                        <div style="height: 38px;"></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <span class="sig-line-name"><?= htmlspecialchars($p3Nama) ?></span>
                    <div class="sig-role"><?= htmlspecialchars($p3Jabatan) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Arial", "normal");
            $pdf->page_text(555, 814, "{PAGE_NUM}", $font, 10, array(0,0,0));
        }
    </script>
</body>
</html>
