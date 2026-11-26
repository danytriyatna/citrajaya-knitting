# LAPORAN KEGIATAN PENGEMBANGAN APLIKASI
## PT CITRA JAYA KNITTING
**Periode:** 28 September 2026 – 27 November 2026  
**Aplikasi:** Aplikasi Citra Jaya Knitting (ERP) | **Pengembang:** Dany Triyatna | **Status Pekerjaan:** 100% Selesai

---

### I. KEGIATAN PENGEMBANGAN SISTEM (BERDASARKAN MENU / BAGIAN OPERASIONAL)

| No | Kategori | Rincian Peningkatan & Fitur Baru Aplikasi | Status |
|:--:|:--------:|:------------------------------------------|:------:|
| **1.** | | **DASHBOARD & RINGKASAN PERSEDIAAN** | |
| 1 | **Fitur Baru** | Penguncian otomatis stok benang yang sudah dialokasikan untuk pesanan rajut aktif (**Stok Dipesan WO**), sehingga aman dan tidak bisa terambil oleh pesanan lain. | **Done** |
| 2 | **Fitur Baru** | Pemisahan tampilan yang jelas antara **Total Stok Fisik di Gudang** dengan **Stok Bersih yang Siap Dipakai**, memudahkan kontrol ketersediaan bahan. | **Done** |
| 3 | **Fitur Baru** | Fitur klik pada angka stok dipesan untuk langsung melihat rincian daftar Surat Perintah Kerja (SPK) mana saja yang sedang memakai bahan tersebut. | **Done** |
| 4 | **Fitur Baru** | Menampilkan bahan yang stok fisiknya sudah habis namun masih memiliki antrean pesanan rajut, agar bagian pengadaan (Purchasing) segera membeli bahan. | **Done** |
| **2.** | | **LAPORAN PERSEDIAAN & PERGUDANGAN** | |
| 5 | **Fitur Baru** | Pembaruan kartu stok secara instan dan otomatis setiap kali transaksi barang masuk, keluar, atau transfer disetujui kepala gudang. | **Done** |
| 6 | **Fitur Baru** | Pembaruan periode tutup buku ke Juni 2026 dan otomatisasi pemindahan sisa stok akhir menjadi stok awal bulan berikutnya tanpa hitung manual. | **Done** |
| 7 | **Fitur Baru** | Penambahan jenis transaksi **"Pemakaian Lainnya"** untuk mencatat pemakaian bahan di luar rajut rutin, langsung memotong kartu stok dan tercatat di biaya operasional. | **Done** |
| 8 | **Penyempurnaan** | Penampilan angka stok minus apa adanya dengan penanda warna merah jika fisik gudang tekor, agar selisih barang di lapangan langsung terlihat dan segera dicari fisiknya. | **Done** |
| **3.** | | **ITEM TRANSFER (DISTRIBUSI ANTAR GUDANG & CMT)** | |
| 9 | **Fitur Baru** | Peringatan otomatis saat pengiriman jika stok nomor lot di gudang asal menipis, disertai opsi konfirmasi agar operasional pengiriman barang tidak macet. | **Done** |
| 10 | **Fitur Baru** | Fitur download Excel khusus untuk **Laporan Sisa Barang di CMT**, memudahkan pengawasan dan penagihan sisa bahan/kain yang masih ada di vendor luar. | **Done** |
| 11 | **Fitur Baru** | Pemisahan pencatatan barang rusak (Reject) dari pengiriman normal, agar catatan sisa barang yang wajib dikirim tidak kacau oleh barang cacat. | **Done** |
| **4.** | | **BARANG KELUAR (BTK - PEMAKAIAN BAHAN RAJUT)** | |
| 12 | **Fitur Baru** | Peringatan otomatis saat pengeluaran bahan rajut jika stok lot menipis, memberi kemudahan admin gudang untuk menyesuaikan alokasi bahan di lapangan. | **Done** |
| 13 | **Fitur Baru** | Tampilan sisa stok gudang terkini pada form pengeluaran bahan, penggabungan otomatis retur sisa bahan per lot, dan proteksi agar pengeluaran tidak melebihi stok. | **Done** |
| **5.** | | **WORK ORDER (WO) & PRODUKSI** | |
| 14 | **Fitur Baru** | Fitur penutupan pesanan rajut (Tutup WO) langsung dari form, otomatis menyelesaikan status produksi dan membebaskan cadangan bahan kembali ke stok umum. | **Done** |
| 15 | **Fitur Baru** | Layar rincian alur bahan per pesanan rajut (benang yang dikirim, sisa benang yang dikembalikan, dan kain yang jadi) untuk memantau rendemen dan susut bahan. | **Done** |
| **6.** | | **DELIVERY ORDER (SURAT JALAN DO)** | |
| 16 | **Fitur Baru** | Fitur scan barcode / QR code pada pembuatan Surat Jalan pengiriman kain, otomatis mencocokkan barang dengan pesanan pelanggan untuk mencegah salah kirim. | **Done** |
| **7.** | | **KEUANGAN & SDM** | |
| 17 | **Menu Baru** | Penambahan menu pencatatan penerimaan uang kas/bank, pemantauan target proses produksi harian, dan pencatatan biaya operasional pabrik. | **Done** |
| **8.** | | **KEMUDAHAN AKSES & PEMBARUAN SISTEM** | |
| 18 | **Penyempurnaan** | Pembaruan sistem otomatis agar setiap ada perbaikan atau fitur baru langsung aktif di seluruh komputer pabrik tanpa perlu repot tekan Ctrl+F5. | **Done** |
| 19 | **Penyempurnaan** | Standarisasi update aplikasi ke server pabrik agar komputer staf kantor dan admin gudang selalu memakai versi terbaru tanpa kendala tampilan lama tersangkut. | **Done** |

---

### RINGKASAN KEGIATAN PENGEMBANGAN (28 SEPTEMBER – 27 NOVEMBER 2026)
* **Kegiatan Pengembangan (19 Poin):** Integrasi Barcode Scanner Surat Jalan DO, Penguncian Stok Dipesan WO, Pemantauan Rendemen Kain, Update Sistem Otomatis, Laporan Sisa Barang CMT, Tampilan Stok Minus Riil, dsb.
* **Status Pengerjaan:** Seluruh 19 inisiatif pengembangan sistem telah selesai 100% dan teruji siap pakai di operasional pabrik.
* **Garansi Pemeliharaan Sistem:** Seluruh 48 poin kegiatan pemeliharaan & penyesuaian sistem pada dokumen Lampiran merupakan **Garansi Bebas Biaya (Free of Charge / Rp 0,-)** selama aplikasi digunakan sesuai komitmen pengembang.

**TOTAL BIAYA PENGEMBANGAN APLIKASI:**  
**Rp 1.200.000,-** *(Terbilang: Satu Juta Dua Ratus Ribu Rupiah)*

---

<br><br>

# DOKUMEN LAMPIRAN
## DAFTAR PEMELIHARAAN SISTEM & PENYESUAIAN OPERASIONAL
### APLIKASI PT CITRA JAYA KNITTING (ERP)
**Periode:** 28 September 2026 – 27 November 2026  
**Kategori:** Pemeliharaan & Penyesuaian Sistem | **Komitmen Layanan:** Garansi Aplikasi Bebas Biaya (Rp 0,-) | **Total Volume:** 48 Poin Kegiatan | **Status:** 100% Selesai

---

| No | Tanggal | Bagian / Menu | Kategori | Deskripsi Penyesuaian & Pemeliharaan Sistem | Status |
|:--:|:-------:|:--------------|:--------:|:--------------------------------------------|:------:|
| 1 | 29-09-2026 | Pembelian (Penerimaan Barang) | **Perbaikan** | Perbaikan pembulatan desimal kuantitas dan harga saat penerimaan barang pembelian agar hitungan rupiah tidak selisih koma. | **Selesai** |
| 2 | 29-09-2026 | Pelunasan Konsumen | **Perbaikan** | Perapihan data bukti pelunasan konsumen agar tersimpan lengkap dan tidak hilang saat dicari kembali di laporan. | **Selesai** |
| 3 | 30-09-2026 | Pesanan Penjualan (SO) | **Perbaikan** | Penguncian pesanan penjualan yang sudah disetujui agar tidak bisa diubah sepihak, serta sinkronisasi otomatis ke status pengerjaan rajut. | **Selesai** |
| 4 | 30-09-2026 | Surat Jalan (DO) | **Perbaikan** | Penyesuaian Surat Jalan pengiriman kain agar rincian varian warna dan nomor lot gulungan sesuai dengan fisik yang dikirim. | **Selesai** |
| 5 | 01-10-2026 | Distribusi Antar Gudang | **Perbaikan** | Perbaikan pencatatan transfer antar gudang agar saldo stok tidak terpotong dua kali saat surat transfer diedit ulang. | **Selesai** |
| 6 | 01-10-2026 | Penerimaan Hasil Rajut (BTM) | **Perbaikan** | Penanganan nomor lot kosong dengan penetapan tanda strip (-) otomatis agar kartu stok tetap rapi dan tidak error. | **Selesai** |
| 7 | 02-10-2026 | Laporan Persediaan | **Pemeliharaan** | Pencocokan kartu stok dengan kondisi fisik barang di gudang sebelum penetapan batas tutup buku. | **Selesai** |
| 8 | 06-10-2026 | Pesanan Produksi (WO) | **Perbaikan** | Perbaikan cetakan lembar kerja rajut (WO) agar rincian kebutuhan jenis dan berat benang tercetak jelas untuk operator mesin. | **Selesai** |
| 9 | 07-10-2026 | Distribusi Antar Gudang | **Perbaikan** | Pembatasan jumlah pengiriman bahan agar tidak melebihi alokasi yang sudah disetujui di pesanan rajut. | **Selesai** |
| 10 | 08-10-2026 | Pengeluaran Bahan Rajut (BTK) | **Perbaikan** | Penyesuaian form pengeluaran benang rajut agar selalu terhubung ke nomor pesanan rajut yang benar. | **Selesai** |
| 11 | 10-10-2026 | Laporan Persediaan | **Pemeriksaan** | Pemeriksaan menyeluruh buku pencatatan persediaan barang agar selalu cocok dengan rekapan stok bulanan. | **Selesai** |
| 12 | 12-10-2026 | Keuangan & Kas | **Perbaikan** | Perbaikan rumus perhitungan pajak PPh dan pembulatan pada buku kas harian operasional pabrik. | **Selesai** |
| 13 | 13-10-2026 | Surat Jalan (DO) | **Perbaikan** | Pengembalian sisa kuota pesanan pelanggan secara otomatis saat Surat Jalan pengiriman dibatalkan. | **Selesai** |
| 14 | 14-10-2026 | Nota Pembelian Supplier | **Perbaikan** | Pembatalan otomatis penerimaan barang di gudang jika nota pembelian dari supplier dibatalkan. | **Selesai** |
| 15 | 15-10-2026 | Distribusi Antar Gudang | **Perbaikan** | Pencegahan data transfer tersimpan dobel jika staf gudang tidak sengaja mengklik tombol simpan berkali-kali. | **Selesai** |
| 16 | 17-10-2026 | Laporan Persediaan | **Perbaikan** | Perbaikan filter laporan stok agar barang yang sedang dalam perjalanan antar gudang tetap terpantau jelas. | **Selesai** |
| 17 | 18-10-2026 | Kwitansi Pembayaran | **Perbaikan** | Perbaikan cetakan kwitansi pelunasan konsumen dengan format angka rupiah standar yang rapi. | **Selesai** |
| 18 | 19-10-2026 | Pengeluaran Bahan Rajut (BTK) | **Perbaikan** | Perbaikan kendala saat pemilihan lot barang pada draf pengeluaran bahan rajut agar form berjalan lancar. | **Selesai** |
| 19 | 21-10-2026 | SDM & Penggajian | **Perbaikan** | Penyesuaian rumus insentif operator rajut berdasarkan panjang meter kain yang dihasilkan mesin. | **Selesai** |
| 20 | 22-10-2026 | Penerimaan Kain Jadi (BTM) | **Perbaikan** | Pencatatan nomor surat jalan vendor CMT pada bukti terima kain rajut agar mudah dilacak dan dicocokkan. | **Selesai** |
| 21 | 23-10-2026 | Distribusi Antar Gudang | **Perbaikan** | Perapihan urutan tanggal pada Surat Jalan transfer agar dokumen tercetak berurutan sesuai waktu pengiriman. | **Selesai** |
| 22 | 27-10-2026 | Laporan Persediaan | **Pemeliharaan** | Pembersihan spasi tersembunyi pada nomor lot barang agar nama lot seragam dan mudah dicari di sistem. | **Selesai** |
| 23 | 29-10-2026 | Jaringan & Server | **Jaringan** | Penyediaan jalur akses langsung via IP server saat jaringan internet atau nama domain kantor mengalami kendala. | **Selesai** |
| 24 | 29-10-2026 | Laporan Persediaan | **Pemeliharaan** | Pembersihan menyeluruh format nomor lot pada barang masuk dan keluar agar pencarian stok lebih cepat. | **Selesai** |
| 25 | 29-10-2026 | Pengeluaran Bahan Rajut (BTK) | **Perbaikan** | Pengaturan agar daftar pilihan lot hanya memunculkan barang yang masih ada stoknya saat admin mengeluarkan bahan. | **Selesai** |
| 26 | 30-10-2026 | Distribusi Antar Gudang | **Perbaikan** | Penyelarasan pencocokan sisa pesanan rajut dengan stok lot yang ada agar pengiriman bahan tepat sasaran. | **Selesai** |
| 27 | 30-10-2026 | Pelunasan Konsumen | **Perbaikan** | Perlindungan klik ganda saat membatalkan kwitansi pembayaran dan pengembalian status nota secara aman. | **Selesai** |
| 28 | 31-10-2026 | Pesanan Produksi (WO) | **Perbaikan** | Penyesuaian sisa kebutuhan bahan pada pesanan rajut agar cocok dengan sisa stok fisik di gudang produksi. | **Selesai** |
| 29 | 31-10-2026 | Pesanan Produksi (WO) | **Perbaikan** | Perbaikan daftar rincian pesanan rajut di dashboard agar memisahkan pesanan produksi massal dari pesanan sampel. | **Selesai** |
| 30 | 31-10-2026 | Pengeluaran Bahan Rajut (BTK) | **Pemeliharaan** | Penguncian tombol persetujuan pengeluaran bahan jika stok fisik kurang, disertai panduan cek mutasi barang. | **Selesai** |
| 31 | 31-10-2026 | Distribusi Antar Gudang | **Pemeliharaan** | Penguncian tombol persetujuan transfer jika stok gudang asal kurang, mencegah barang terkirim tanpa stok riil. | **Selesai** |
| 32 | 01-11-2026 | Laporan Persediaan | **Perbaikan** | Penyempurnaan pemeriksaan stok untuk barang tanpa nomor lot pada form pengeluaran dan transfer barang. | **Selesai** |
| 33 | 01-11-2026 | Laporan Persediaan | **Perbaikan** | Pembatasan tanggal transaksi agar tidak bisa menginput tanggal masa depan dan mengunci laporan bulan berjalan. | **Selesai** |
| 34 | 02-11-2026 | Laporan Persediaan | **Perbaikan** | Penguncian sistem agar sisa stok akhir bulan tidak melompat ke bulan yang belum berjalan. | **Selesai** |
| 35 | 06-11-2026 | Laporan Persediaan | **Perbaikan** | Pengetatan pemeriksaan satuan kemasan (karung/pack) dan nomor lot saat staf gudang memproses bahan. | **Selesai** |
| 36 | 09-11-2026 | Laporan Persediaan | **Perbaikan** | Dukungan pengecekan total stok gudang untuk barang kemasan campur agar admin mengetahui total berat riil. | **Selesai** |
| 37 | 10-11-2026 | Pesanan Produksi (WO) | **Perbaikan** | Menyembunyikan varian kain bernilai nol yang sudah dibatalkan agar lembar kerja operator rajut bersih dan jelas. | **Selesai** |
| 38 | 11-11-2026 | Laporan Persediaan | **Perbaikan** | Sinkronisasi perhitungan saldo mutasi langsung dari buku persediaan dan pemisahan pengeluaran bahan sah pada rendemen. | **Selesai** |
| 39 | 11-11-2026 | Laporan Persediaan | **Perbaikan** | Perhitungan langsung sisa stok barang non-lot di gudang agar kartu stok selalu terkini. | **Selesai** |
| 40 | 11-11-2026 | Pengeluaran Bahan Rajut (BTK) | **Perbaikan** | Optimalisasi pengelompokan data pengeluaran bahan rajut agar laporan pemakaian bahan tampil cepat dan tidak lemot. | **Selesai** |
| 41 | 12-11-2026 | Distribusi Antar Gudang | **Perbaikan** | Penyeragaman daftar pilihan barang di form transfer dengan Laporan Persediaan agar tidak ada salah pilih barang. | **Selesai** |
| 42 | 13-11-2026 | Distribusi Antar Gudang | **Perbaikan** | Pengecekan otomatis riwayat stok sebelum transfer disetujui, memastikan stok fisik benar-benar ada di gudang. | **Selesai** |
| 43 | 13-11-2026 | Distribusi Antar Gudang | **Perbaikan** | Perbaikan cetakan Surat Jalan pengiriman agar baris nama barang tidak tercetak dobel atau berulang. | **Selesai** |
| 44 | 17-11-2026 | Penerimaan Kain Jadi (BTM) | **Perbaikan** | Peningkatan validasi scanner barcode penerimaan kain, otomatis menolak jika kombinasi jenis proses dan vendor CMT keliru. | **Selesai** |
| 45 | 20-11-2026 | Pengeluaran Barang | **Perbaikan** | Perbaikan tautan detail surat pengeluaran barang agar bisa dibuka lancar oleh admin tanpa pesan error. | **Selesai** |
| 46 | 22-11-2026 | Kestabilan Server Pabrik | **Pemeliharaan** | Pengaturan izin folder sistem di server web agar admin pabrik tidak mengalami error saat membuka dan menyimpan form transaksi. | **Selesai** |
| 47 | 23-11-2026 | Laporan Hasil Produksi | **Perbaikan** | Penguncian riwayat produksi dan laporan laba kotor agar hanya menghitung dokumen yang resmi disetujui pimpinan. | **Selesai** |
| 48 | 23-11-2026 | Penyelarasan Data Gudang | **Pemeliharaan** | Pengaturan database server untuk mendukung pencatatan stok minus apa adanya jika terjadi selisih fisik di pabrik. | **Selesai** |

---
*Dokumen Lampiran resmi pemeliharaan & penyesuaian operasional Aplikasi PT Citra Jaya Knitting (28 Sept – 27 Nov 2026)*
