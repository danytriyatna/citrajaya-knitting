# LAPORAN KEGIATAN PENGEMBANGAN & PEMELIHARAAN APLIKASI
## PT CITRA JAYA KNITTING
**Periode:** 28 September 2026 – 18 November 2026  
**Aplikasi:** Aplikasi Citra Jaya Knitting (ERP) | **Pengembang:** Dany Triyatna | **Status Pekerjaan:** 100% Selesai (Done)

---

### I. KEGIATAN PENGEMBANGAN SISTEM (FITUR BARU, MODUL & ARSITEKTUR)

| No | Tanggal | Modul / Menu | Deskripsi Kegiatan & Penyesuaian Sistem | Status |
|:--:|:-------:|:-------------|:----------------------------------------|:------:|
| 1 | 29-09-2026 | Core Architecture | **[Arsitektur]** Implementasi universal auto-versioning asset (`?v=timestamp`) dan anti-cache HTTP headers (`no-cache, no-store`) untuk update instan tanpa perlu manual Ctrl+F5 di sisi user pabrik. | **Done** |
| 2 | 16-10-2026 | Item Transfer | **[Fitur Baru]** Dialog konfirmasi interaktif saat proses approval jika terdeteksi stok lot fisik menipis/kurang agar alur distribusi antar gudang tidak terhenti mendadak. | **Done** |
| 3 | 26-10-2026 | Item Transfer | **[Fitur Baru]** Fitur Export Excel khusus tipe **Sisa Transfer CMT (Outstanding Inventory)** untuk audit saldo bahan/barang yang masih mengendap di pihak vendor CMT luar. | **Done** |
| 4 | 26-10-2026 | Item Transfer | **[Fitur Baru]** Pemisahan kolom barang Reject dari transfer normal, sehingga perhitungan saldo sisa kirim murni tidak terdistorsi oleh barang cacat/rusak. | **Done** |
| 5 | 27-10-2026 | Laporan Persediaan | **[Fitur Baru]** Mekanisme auto-sync real-time snapshot saldo persediaan fisik otomatis setiap kali transaksi pergudangan disetujui (Approved). | **Done** |
| 6 | 30-10-2026 | Barang Keluar (BTK) | **[Fitur Baru]** Dialog konfirmasi interaktif pada approval pengeluaran bahan rajut saat kuota stok lot menipis dengan opsi alokasi fleksibel. | **Done** |
| 7 | 30-10-2026 | Keuangan & SDM | **[Menu Baru]** Sinkronisasi menu baru Kas/Bank Masuk, modul Target Proses Produksi harian, dan integrasi utilitas operasional pabrik. | **Done** |
| 8 | 31-10-2026 | Laporan Persediaan | **[Fitur Baru]** Pergeseran titik cut-off pembukuan persediaan ke Juni 2026 dan pembukaan sistem carry forward saldo otomatis ke Juli 2026 dan seterusnya. | **Done** |
| 9 | 31-10-2026 | Dashboard Persediaan | **[Fitur Baru]** Penambahan kolom **QTY WO Booked** (alokasi bahan benang yang sedang dipesan SPK/WO aktif) sebagai pengurang otomatis real-time dari stok fisik. | **Done** |
| 10 | 31-10-2026 | Dashboard Persediaan | **[Fitur Baru]** Penambahan kolom fisik **QTY AKHIR (Gudang)** dan penyesuaian redaksi **QTY TERSEDIA (Stok Bersih Siap Pakai)** pada ringkasan persediaan bahan baku. | **Done** |
| 11 | 31-10-2026 | Dashboard Persediaan | **[Fitur Baru]** Fitur pop-up modal drill-down rincian daftar Work Order pemesan saat angka QTY WO diklik langsung di layar dashboard, transparan untuk tim PPIC/Pengadaan. | **Done** |
| 12 | 31-10-2026 | Work Order (WO) | **[Fitur Baru]** Pemindahan fungsi penutupan status (Closed) langsung ke form Work Order dengan efek auto-cascade otomatis ke status Produksi dan pelepasan booking bahan di Dashboard. | **Done** |
| 13 | 31-10-2026 | Produksi | **[Fitur Baru]** Fitur pop-up modal interaktif rincian breakdown alur bahan per WO: transfer benang, retur sisa bahan, dan pemakaian riil untuk kontrol rendemen. | **Done** |
| 14 | 31-10-2026 | Persediaan & Laba Rugi | **[Fitur Baru]** Penambahan kategori transaksi baru **"Pemakaian Lainnya"** yang memotong kartu stok dan terintegrasi otomatis ke laporan Laba Rugi operasional. | **Done** |
| 15 | 31-10-2026 | Delivery Order (DO) | **[Fitur Baru]** Integrasi scanner Barcode / QR Code pada entri Surat Jalan DO dengan validasi pencocokan ketat terhadap Sales Order dan rekap kuota sisa kirim real-time. | **Done** |
| 16 | 02-11-2026 | Core Architecture | **[Arsitektur]** Implementasi arsitektur **Zero-Client-Cache** permanen & SOP automated deployment server agar rilis fitur baru langsung aktif di seluruh workstation tanpa clear browser cache. | **Done** |
| 17 | 09-11-2026 | Dashboard Persediaan | **[Fitur Baru]** Penampilan item bersaldo fisik nol yang memiliki antrean booking WO aktif di Ringkasan Persediaan agar bagian purchasing segera memproses restock. | **Done** |
| 18 | 11-11-2026 | Barang Keluar (BTK) | **[Fitur Baru]** Penambahan kolom live **STOK GUDANG**, fitur live refresh stok draft, auto-merge transfer & return per lot, dan proteksi auto-cap sisa kuota ke stok fisik. | **Done** |

---

### II. KEGIATAN PEMELIHARAAN SISTEM (MAINTENANCE, BUGFIX & INTEGRITAS DATA)

| No | Tanggal | Modul / Menu | Deskripsi Kegiatan & Penyesuaian Sistem | Status |
|:--:|:-------:|:-------------|:----------------------------------------|:------:|
| 19 | 29-09-2026 | Purchasing (Receive Item) | **[Bugfix]** Penanganan presisi desimal dan pencegahan floating point error pada entri kuantitas penerimaan barang supplier. | **Done** |
| 20 | 29-09-2026 | Purchasing (Receive Item) | **[Bugfix]** Penegakan pembulatan standar 2 desimal pada modal edit dan tabel listsBarang penerimaan. | **Done** |
| 21 | 29-09-2026 | Item Transfer | **[Bugfix]** Perbaikan penelusuran lot_id, type casting id_header, dan mitigasi unhandled error 500 saat approval transfer antar gudang. | **Done** |
| 22 | 30-09-2026 | Laporan Persediaan | **[Bugfix]** Relaksasi pengecekan ketersediaan lot gudang pada approval Barang Keluar untuk menjaga kelancaran alur operasional pabrik. | **Done** |
| 23 | 30-09-2026 | Produksi & Rendemen | **[Bugfix]** Perbaikan missing GROUP BY pada data rendemen rajut dan perbaikan nama tabel pada fungsi sinkronisasi harga transaksi anomali. | **Done** |
| 24 | 01-10-2026 | Barang Masuk (BTM) | **[Bugfix]** Perbaikan bug auto-overwrite tanggal detail pada hasil produksi agar otomatis sinkron dengan tanggal transaksi header. | **Done** |
| 25 | 01-10-2026 | Produksi | **[Bugfix]** Filter berat kain BTM dan rendemen rajut khusus Gudang Utama dengan status Approved agar hitungan akurat. | **Done** |
| 26 | 01-10-2026 | Barang Keluar (BTK) | **[Bugfix]** Perhitungan BTM (Kg) khusus Gudang Utama dan pemotongan retur benang yang valid ke Gudang Utama. | **Done** |
| 27 | 01-10-2026 | Barang Keluar (BTK) | **[Bugfix]** Penyelarasan BTM kain rajut dengan data riil modul Produksi (memastikan nilai konsisten antar form). | **Done** |
| 28 | 01-10-2026 | Item Transfer | **[Bugfix]** Pencegahan simpan transfer kosong tanpa item dan perbaikan layout printout Surat Jalan. | **Done** |
| 29 | 02-10-2026 | Master Data / Otorisasi | **[Maintenance]** Penataan dan penonaktifan menu asisten AI pada seluruh role pengguna di database. | **Done** |
| 30 | 06-10-2026 | Work Order (WO) | **[Bugfix]** Pemetaan gramasi benang rajut sampel berdasarkan urutan slot (slot index) untuk mencegah duplikasi gramasi identik pada varian benang yang sama. | **Done** |
| 31 | 06-10-2026 | Sampel | **[Bugfix]** Pengayaan label dropdown benang dengan nama warna dan auto-invalidation cache saat terjadi mutasi master barang. | **Done** |
| 32 | 06-10-2026 | Database Patching | **[Audit Data]** Perbaikan data gramasi slot duplikat benang untuk SPK/WO WRD261000001 (SOD261000001). | **Done** |
| 33 | 06-10-2026 | Item Transfer | **[Maintenance]** Mengizinkan simpan transfer tanpa item SO jika rincian benang/WO sudah terisi, disertai notifikasi dialog konfirmasi. | **Done** |
| 34 | 07-10-2026 | Database Patching | **[Audit Data]** Perbaikan data gramasi kombo benang duplikat untuk SPK/WO WRD261000002 (SOD261000002). | **Done** |
| 35 | 09-10-2026 | Laporan Persediaan | **[Audit Data]** Perbaikan carry-forward saldo awal antar bulan & sinkronisasi mutasi `trans_barang_history` agar saldo persediaan tidak bernilai negatif. | **Done** |
| 36 | 10-10-2026 | Item Transfer | **[Bugfix]** Smart lot resolution: memprioritaskan alokasi nomor lot yang memiliki stok positif dan fallback otomatis saat approval transfer. | **Done** |
| 37 | 19-10-2026 | Work Order (WO) | **[Bugfix]** Perbaikan parameter `filter_trans` pada request AJAX Tabulator agar filter data detail konsisten saat pencarian. | **Done** |
| 38 | 26-10-2026 | Laporan Persediaan | **[Bugfix]** Perbaikan error ambiguous column `jumlah` dan `stok_awal` pada method `getDataGudangPrint` export Excel persediaan. | **Done** |
| 39 | 26-10-2026 | Item Transfer | **[Bugfix]** Penyelarasan filter perusahaan dan header judul pada laporan export sisa CMT. | **Done** |
| 40 | 26-10-2026 | Item Transfer | **[Bugfix]** Filter tanggal berdasarkan tanggal transaksi SO dan sinkronisasi kalkulasi kumulatif transfer vs BTM. | **Done** |
| 41 | 27-10-2026 | Laporan Persediaan | **[Maintenance]** Sanitasi nomor lot dan perlindungan whitespace tersembunyi pada validasi persediaan berlapis. | **Done** |
| 42 | 29-10-2026 | Infrastruktur & Jaringan | **[Jaringan]** Dukungan akses direct IP Server ketika jaringan lokal atau DNS domain kantor mengalami kendala resolusi. | **Done** |
| 43 | 29-10-2026 | Laporan Persediaan | **[Maintenance]** Sanitasi menyeluruh nomor lot pada form Barang Masuk & Barang Keluar serta pembersihan script debug. | **Done** |
| 44 | 29-10-2026 | Barang Keluar (BTK) | **[Bugfix]** Filter seleksi hanya memunculkan lot bersaldo positif (`qty > 0`) pada modal pemilihan Barang Keluar. | **Done** |
| 45 | 30-10-2026 | Item Transfer | **[Bugfix]** Standarisasi pencocokan sisa kuota WO dan join transfer berbasis nomor lot dengan dual fallback. | **Done** |
| 46 | 30-10-2026 | Customer Receipt | **[Bugfix]** Proteksi debouncing anti-double-click dan perbaikan mekanisme rollback status saat nota penerimaan dibatalkan. | **Done** |
| 47 | 31-10-2026 | Work Order (WO) | **[Bugfix]** Sinkronisasi QTY on hand Work Order ke saldo akhir per item_id dan gudang produksi terkait. | **Done** |
| 48 | 31-10-2026 | Work Order (WO) | **[Bugfix]** Perbaikan pemanggilan method `getData` pada `lists_detail` dan filter WO aktif non-sampel di dashboard. | **Done** |
| 49 | 31-10-2026 | Barang Keluar (BTK) | **[Maintenance]** Penguncian tombol approve jika stok lot gudang fisik kurang, serta arahan interaktif ke mutasi barang. | **Done** |
| 50 | 31-10-2026 | Item Transfer | **[Maintenance]** Penguncian tombol approve jika stok lot gudang asal kurang, serta arahan interaktif ke mutasi barang. | **Done** |
| 51 | 01-11-2026 | Laporan Persediaan | **[Bugfix]** Penyempurnaan validasi stok fisik untuk barang non-lot (`-`) pada Barang Keluar dan Item Transfer. | **Done** |
| 52 | 01-11-2026 | Laporan Persediaan | **[Bugfix]** Pembatasan input tanggal transaksi maksimal hari ini dan filter pembukuan persediaan tidak melebihi bulan berjalan. | **Done** |
| 53 | 02-11-2026 | Laporan Persediaan | **[Bugfix]** Pencegahan carry forward persediaan melompat ke bulan masa depan dan penguncian saldo mutasi persediaan bulanan. | **Done** |
| 54 | 06-11-2026 | Laporan Persediaan | **[Bugfix]** Penegakan parameter nomor lot dan satuan kemasan (pack) pada pengecekan stok fisik di Transfer & Barang Keluar. | **Done** |
| 55 | 09-11-2026 | Laporan Persediaan | **[Bugfix]** Dukungan validasi total stok gudang untuk barang non-lot dan multi-pack pada Item Transfer dan Barang Keluar. | **Done** |
| 56 | 10-11-2026 | Work Order & Produksi | **[Bugfix]** Menyembunyikan baris varian bernilai qty 0 yang dibatalkan pada lembar kerja WO/Produksi serta penegakan validasi gudang pada lot_id. | **Done** |
| 57 | 11-11-2026 | Laporan Persediaan | **[Bugfix]** Perhitungan saldo mutasi `trans_barang_balances` live langsung dari database (bukan nilai frontend `qty_exist`), perbaikan join edit transfer, dan filter approved BTK pada rendemen. | **Done** |
| 58 | 11-11-2026 | Laporan Persediaan | **[Bugfix]** Perhitungan live saldo barang non-lot dan pemasangan non-negative guard pada ledger balances. | **Done** |
| 59 | 11-11-2026 | Barang Keluar (BTK) | **[Bugfix]** Pembungkusan agregasi `getDataByWalkorder` ke derived table agar kompatibel dengan strict mode PostgreSQL. | **Done** |
| 60 | 12-11-2026 | Item Transfer | **[Bugfix]** Unifikasi katalog item picker dengan Laporan Persediaan dan penegakan pencocokan nomor lot spesifik per kemasan saat approval. | **Done** |
| 61 | 13-11-2026 | Item Transfer | **[Bugfix]** Penambahan validasi mandiri (*self-healing stock verification*) terhadap `trans_barang_history` saat approval transfer. | **Done** |
| 62 | 13-11-2026 | Item Transfer | **[Bugfix]** Eliminasi join redundan `trans_lots` pada fungsi `getDataPrint` untuk menghilangkan duplikasi baris barang pada Surat Jalan. | **Done** |
| 63 | 17-11-2026 | Barang Masuk (BTM) | **[Bugfix]** Perbaikan query log fatal `abx.created_at does not exist` menjadi `head.created_at`, serta upgrade validasi scanner barcode cerdas yang kontekstual mengidentifikasi ketidaksesuaian kombinasi Proses dan CMT. | **Done** |

---

### REKAPITULASI KEGIATAN (28 SEPTEMBER – 18 NOVEMBER 2026)
* **Kegiatan Pengembangan (18 Poin):** Integrasi Barcode Scanner DO, Booking WO Persediaan, Interactive Breakdown Produksi, Zero-Client-Cache, Auto-Cascade Closed WO, Export Sisa CMT, dsb.
* **Kegiatan Pemeliharaan (45 Poin):** Eliminasi Duplikasi Surat Jalan, Self-Healing Stock Verification, Smart Lot Resolution, Perbaikan Query Server BTM, Carry Forward Audit Persediaan, dsb.
* **Total Volume Pekerjaan:** 63 Poin Kegiatan & Penyesuaian Sistem (Status: 100% Selesai).

**TOTAL BIAYA PEMELIHARAAN & PENGEMBANGAN:**  
**Rp 6.200.000,-** *(Terbilang: Enam Juta Dua Ratus Ribu Rupiah)*  
*(Dihitung proporsional 2 periode bulanan: Oktober & November 2026)*
