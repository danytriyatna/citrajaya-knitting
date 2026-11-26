# LAPORAN KEGIATAN PENGEMBANGAN APLIKASI
## PT CITRA JAYA KNITTING
**Periode:** 28 September 2026 – 27 November 2026  
**Aplikasi:** Aplikasi Citra Jaya Knitting (ERP) | **Pengembang:** Dany Triyatna | **Status Pekerjaan:** 100% Selesai (Done)

---

### I. KEGIATAN PENGEMBANGAN SISTEM (BERDASARKAN MENU / MODUL)

| No | Kategori | Rincian Kegiatan Pengembangan Sistem | Status |
|:--:|:--------:|:-------------------------------------|:------:|
| **1.** | | **DASHBOARD & RINGKASAN PERSEDIAAN** | |
| 1 | **Fitur Baru** | Penambahan kolom **QTY WO Booked** (alokasi bahan benang yang sedang dipesan SPK/WO aktif) sebagai pengurang otomatis real-time dari stok fisik. | **Done** |
| 2 | **Fitur Baru** | Penambahan kolom fisik **QTY AKHIR (Gudang)** dan penyesuaian redaksi **QTY TERSEDIA (Stok Bersih Siap Pakai)** pada ringkasan persediaan bahan baku. | **Done** |
| 3 | **Fitur Baru** | Fitur pop-up modal drill-down rincian daftar Work Order pemesan saat angka QTY WO diklik langsung di layar dashboard, transparan untuk tim PPIC/Pengadaan. | **Done** |
| 4 | **Fitur Baru** | Penampilan item bersaldo fisik nol yang memiliki antrean booking WO aktif di Ringkasan Persediaan agar bagian purchasing segera memproses restock. | **Done** |
| **2.** | | **LAPORAN PERSEDIAAN & PERGUDANGAN** | |
| 5 | **Fitur Baru** | Mekanisme auto-sync real-time snapshot saldo persediaan fisik otomatis setiap kali transaksi pergudangan disetujui (Approved). | **Done** |
| 6 | **Fitur Baru** | Pergeseran titik cut-off pembukuan persediaan ke Juni 2026 dan pembukaan sistem carry forward saldo otomatis ke Juli 2026 dan seterusnya. | **Done** |
| 7 | **Fitur Baru** | Penambahan kategori transaksi baru **"Pemakaian Lainnya"** yang memotong kartu stok dan terintegrasi otomatis ke laporan Laba Rugi operasional. | **Done** |
| 8 | **Arsitektur** | Unifikasi integritas saldo minus riil tanpa clamping (Zero-Guard Bypass): sinkronisasi tampilan saldo fisik, stok tersedia, sisa kebutuhan WO, dan kuota transfer agar konsisten 100% menampilkan nilai minus dan penanda visual merah sesuai Laporan Persediaan fisik. | **Done** |
| **3.** | | **ITEM TRANSFER (DISTRIBUSI ANTAR GUDANG & CMT)** | |
| 9 | **Fitur Baru** | Dialog konfirmasi interaktif saat proses approval jika terdeteksi stok lot fisik menipis/kurang agar alur distribusi antar gudang tidak terhenti mendadak. | **Done** |
| 10 | **Fitur Baru** | Fitur Export Excel khusus tipe **Sisa Transfer CMT (Outstanding Inventory)** untuk audit saldo bahan/barang yang masih mengendap di pihak vendor CMT luar. | **Done** |
| 11 | **Fitur Baru** | Pemisahan kolom barang Reject dari transfer normal, sehingga perhitungan saldo sisa kirim murni tidak terdistorsi oleh barang cacat/rusak. | **Done** |
| **4.** | | **BARANG KELUAR (BTK - PEMAKAIAN BAHAN RAJUT)** | |
| 12 | **Fitur Baru** | Dialog konfirmasi interaktif pada approval pengeluaran bahan rajut saat kuota stok lot menipis dengan opsi alokasi fleksibel. | **Done** |
| 13 | **Fitur Baru** | Penambahan kolom live **STOK GUDANG**, fitur live refresh stok draft, auto-merge transfer & return per lot, dan proteksi auto-cap sisa kuota ke stok fisik. | **Done** |
| **5.** | | **WORK ORDER (WO) & PRODUKSI** | |
| 14 | **Fitur Baru** | Pemindahan fungsi penutupan status (Closed) langsung ke form Work Order dengan efek auto-cascade otomatis ke status Produksi dan pelepasan booking bahan di Dashboard. | **Done** |
| 15 | **Fitur Baru** | Fitur pop-up modal interaktif rincian breakdown alur bahan per WO: transfer benang, retur sisa bahan, dan pemakaian riil untuk kontrol rendemen. | **Done** |
| **6.** | | **DELIVERY ORDER (SURAT JALAN DO)** | |
| 16 | **Fitur Baru** | Integrasi scanner Barcode / QR Code pada entri Surat Jalan DO dengan validasi pencocokan ketat terhadap Sales Order dan rekap kuota sisa kirim real-time. | **Done** |
| **7.** | | **KEUANGAN & SDM** | |
| 17 | **Menu Baru** | Sinkronisasi menu baru Kas/Bank Masuk, modul Target Proses Produksi harian, dan integrasi utilitas operasional pabrik. | **Done** |
| **8.** | | **CORE ARCHITECTURE & INFRASTRUKTUR** | |
| 18 | **Arsitektur** | Implementasi universal auto-versioning asset (`?v=timestamp`) dan anti-cache HTTP headers (`no-cache, no-store`) untuk update instan tanpa perlu manual Ctrl+F5 di sisi user pabrik. | **Done** |
| 19 | **Arsitektur** | Implementasi arsitektur **Zero-Client-Cache** permanen & SOP automated deployment server agar rilis fitur baru langsung aktif di seluruh workstation tanpa clear browser cache. | **Done** |

---

### RINGKASAN PEKERJAAN (28 SEPTEMBER – 27 NOVEMBER 2026)
* **Kegiatan Pengembangan (19 Poin):** Integrasi Barcode Scanner DO, Booking WO Persediaan, Interactive Breakdown Produksi, Zero-Client-Cache, Auto-Cascade Closed WO, Export Sisa CMT, Integritas Saldo Minus Riil Persediaan, dsb.
* **Status Implementasi:** Seluruh 19 inisiatif pengembangan sistem telah selesai 100% dan teruji live di server produksi.
* *\* Rincian teknis kegiatan pemeliharaan sistem (maintenance, bugfix & integritas data) disajikan lengkap pada dokumen Lampiran.*

**TOTAL BIAYA PEMELIHARAAN & PENGEMBANGAN:**  
**Rp 6.200.000,-** *(Terbilang: Enam Juta Dua Ratus Ribu Rupiah)*  
*(Dihitung proporsional 2 periode bulanan: Oktober & November 2026)*

---

<br><br>

# DOKUMEN LAMPIRAN
## DAFTAR KEGIATAN PEMELIHARAAN SISTEM & INTEGRITAS DATA
### APLIKASI PT CITRA JAYA KNITTING (ERP)
**Periode:** 28 September 2026 – 27 November 2026  
**Kategori:** Maintenance, Bugfix, Integritas Database & Optimasi Server | **Total Volume:** 48 Poin Kegiatan | **Status:** 100% Selesai (Done)

---

| No | Kategori | Rincian Pemeliharaan & Penyesuaian Sistem | Status |
|:--:|:--------:|:------------------------------------------|:------:|
| **1.** | | **LAPORAN PERSEDIAAN & PERGUDANGAN** | |
| 1 | **Maintenance** | Rekonsiliasi saldo fisik riil dan perbaikan anomali kartu stok pada periode sebelum cut-off. | **Done** |
| 2 | **Audit** | Audit konsistensi ledger `trans_barang` terhadap snapshot history bulanan. | **Done** |
| 3 | **Bugfix** | Koreksi filter gudang pada laporan mutasi persediaan agar menampilkan transaksi transit. | **Done** |
| 4 | **Maintenance** | Sanitasi nomor lot dan perlindungan whitespace tersembunyi pada validasi persediaan berlapis. | **Done** |
| 5 | **Maintenance** | Sanitasi menyeluruh nomor lot pada form Barang Masuk & Barang Keluar serta pembersihan script debug. | **Done** |
| 6 | **Bugfix** | Penyempurnaan validasi stok fisik untuk barang non-lot (`-`) pada Barang Keluar dan Item Transfer. | **Done** |
| 7 | **Bugfix** | Pembatasan input tanggal transaksi maksimal hari ini dan filter pembukuan persediaan tidak melebihi bulan berjalan. | **Done** |
| 8 | **Bugfix** | Pencegahan carry forward persediaan melompat ke bulan masa depan dan penguncian saldo mutasi persediaan bulanan. | **Done** |
| 9 | **Bugfix** | Penegakan parameter nomor lot dan satuan kemasan (pack) pada pengecekan stok fisik di Transfer & Barang Keluar. | **Done** |
| 10 | **Bugfix** | Dukungan validasi total stok gudang untuk barang non-lot dan multi-pack pada Item Transfer dan Barang Keluar. | **Done** |
| 11 | **Bugfix** | Perhitungan saldo mutasi `trans_barang_balances` live langsung dari database (bukan nilai frontend `qty_exist`), perbaikan join edit transfer, dan filter approved BTK pada rendemen. | **Done** |
| 12 | **Bugfix** | Perhitungan live saldo barang non-lot dan pemasangan non-negative guard pada ledger balances. | **Done** |
| **2.** | | **ITEM TRANSFER (DISTRIBUSI ANTAR GUDANG & CMT)** | |
| 13 | **Bugfix** | Koreksi query join mutasi antar gudang agar saldo tidak terpotong ganda saat editing transfer. | **Done** |
| 14 | **Bugfix** | Penegakan validasi kuota maksimal transfer agar tidak melebihi alokasi order yang disetujui. | **Done** |
| 15 | **Bugfix** | Penambahan proteksi transaksi ganda (double submission prevention) saat submit form transfer. | **Done** |
| 16 | **Bugfix** | Perbaikan sorting tanggal transfer agar surat jalan tercetak kronologis. | **Done** |
| 17 | **Bugfix** | Standarisasi pencocokan sisa kuota WO dan join transfer berbasis nomor lot dengan dual fallback. | **Done** |
| 18 | **Maintenance** | Penguncian tombol approve jika stok lot gudang asal kurang, serta arahan interaktif ke mutasi barang. | **Done** |
| 19 | **Bugfix** | Unifikasi katalog item picker dengan Laporan Persediaan dan penegakan pencocokan nomor lot spesifik per kemasan saat approval. | **Done** |
| 20 | **Bugfix** | Penambahan validasi mandiri (*self-healing stock verification*) terhadap `trans_barang_history` saat approval transfer. | **Done** |
| 21 | **Bugfix** | Eliminasi join redundan `trans_lots` pada fungsi `getDataPrint` untuk menghilangkan duplikasi baris barang pada Surat Jalan. | **Done** |
| **3.** | | **BARANG KELUAR (BTK - PEMAKAIAN BAHAN RAJUT)** | |
| 22 | **Bugfix** | Sinkronisasi pengeluaran bahan baku rajut dengan nomor referensi WO yang valid. | **Done** |
| 23 | **Bugfix** | Penanganan error null pointer saat pemilihan lot barang pada dokumen draft. | **Done** |
| 24 | **Bugfix** | Filter seleksi hanya memunculkan lot bersaldo positif (`qty > 0`) pada modal pemilihan Barang Keluar. | **Done** |
| 25 | **Maintenance** | Penguncian tombol approve jika stok lot gudang fisik kurang, serta arahan interaktif ke mutasi barang. | **Done** |
| 26 | **Bugfix** | Pembungkusan agregasi `getDataByWalkorder` ke derived table agar kompatibel dengan strict mode PostgreSQL. | **Done** |
| 27 | **Bugfix** | Perbaikan routing dan handling parameter token terenkripsi pada link URL detail pengeluaran barang (outgoing-goods) untuk mencegah unhandled exceptions. | **Done** |
| **4.** | | **BARANG MASUK (BTM - HASIL RAJUT & CMT)** | |
| 28 | **Bugfix** | Penanganan nomor lot kosong dengan penetapan default strip (-) untuk menjaga relasi kartu stok. | **Done** |
| 29 | **Bugfix** | Sinkronisasi nomor surat jalan vendor CMT pada form penerimaan barang hasil rajut. | **Done** |
| 30 | **Bugfix** | Perbaikan query log fatal `abx.created_at does not exist` menjadi `head.created_at`, serta upgrade validasi scanner barcode cerdas yang kontekstual mengidentifikasi ketidaksesuaian kombinasi Proses dan CMT. | **Done** |
| **5.** | | **WORK ORDER (WO) & PRODUKSI** | |
| 31 | **Bugfix** | Perbaikan tampilan breakdown kebutuhan benang pada printout Work Order rajut. | **Done** |
| 32 | **Bugfix** | Sinkronisasi QTY on hand Work Order ke saldo akhir per item_id dan gudang produksi terkait. | **Done** |
| 33 | **Bugfix** | Perbaikan pemanggilan method `getData` pada `lists_detail` dan filter WO aktif non-sampel di dashboard. | **Done** |
| 34 | **Bugfix** | Menyembunyikan baris varian bernilai qty 0 yang dibatalkan pada lembar kerja WO/Produksi serta penegakan validasi gudang pada lot_id. | **Done** |
| 35 | **Bugfix** | Penguncian relasi transaksi Transfer (TRF), BTK, dan BTM hanya membaca dokumen approved (`status = 1 AND active = 1`) pada modal pop-up Form Produksi, laporan laba kotor, dan rekapitulasi monitoring, mencegah transaksi yang dibatalkan ikut terhitung ke riwayat produksi. | **Done** |
| **6.** | | **SALES ORDER, DO & SURAT JALAN** | |
| 36 | **Bugfix** | Penguncian status approved pada Sales Order dan sinkronisasi otomatis status SPK terkait. | **Done** |
| 37 | **Bugfix** | Penyesuaian relasi data delivery order terhadap varian warna dan nomor lot kain rajut. | **Done** |
| 38 | **Bugfix** | Penyesuaian kalkulasi sisa kuota kirim pada Sales Order saat DO dibatalkan. | **Done** |
| **7.** | | **PURCHASING & CUSTOMER RECEIPT** | |
| 39 | **Bugfix** | Penanganan presisi desimal dan pencegahan floating point error pada entri kuantitas dan harga penerimaan barang. | **Done** |
| 40 | **Bugfix** | Validasi kelengkapan header relasi customer receipt untuk mencegah record yatim (orphaned records). | **Done** |
| 41 | **Bugfix** | Penanganan purchase invoice berstatus void agar membatalkan mutasi stok terkait. | **Done** |
| 42 | **Bugfix** | Perbaikan pencetakan kwitansi pelunasan konsumen dengan format currency standar. | **Done** |
| 43 | **Bugfix** | Proteksi debouncing anti-double-click dan perbaikan mekanisme rollback status saat nota penerimaan dibatalkan. | **Done** |
| **8.** | | **KEUANGAN & SDM** | |
| 44 | **Bugfix** | Koreksi formula perhitungan PPh dan pembulatan pada jurnal kas operasional. | **Done** |
| 45 | **Bugfix** | Perbaikan formula insentif operator rajut berbasis target meter kain yang dihasilkan. | **Done** |
| **9.** | | **INFRASTRUKTUR SERVER & DATABASE** | |
| 46 | **Jaringan** | Dukungan akses direct IP Server ketika jaringan lokal atau DNS domain kantor mengalami kendala resolusi. | **Done** |
| 47 | **Maintenance** | Rekonfigurasi ownership dan hak akses direktori penyimpanan session/cache `/pejuang/production/writable` di server web LiteSpeed (LSPHP 8.3) untuk mencegah HTTP 500 error pada saat user mengakses form transaksi. | **Done** |
| 48 | **Maintenance** | Penghapusan constraint `chk_trans_barang_balances_non_negative` pada tabel `trans_barang_balances` di database PostgreSQL produksi & lokal untuk mendukung integritas mutasi riil ketika terjadi stok minus. | **Done** |

---
*Dokumen Lampiran resmi pemeliharaan sistem & integritas data Aplikasi PT Citra Jaya Knitting (28 Sept – 27 Nov 2026)*
