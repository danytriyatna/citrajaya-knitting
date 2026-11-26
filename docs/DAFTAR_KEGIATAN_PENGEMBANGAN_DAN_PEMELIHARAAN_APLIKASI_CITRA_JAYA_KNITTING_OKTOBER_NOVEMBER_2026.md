# LAPORAN KEGIATAN PENGEMBANGAN APLIKASI
## PT CITRA JAYA KNITTING
**Periode:** 28 September 2026 – 27 November 2026  
**Aplikasi:** Aplikasi Citra Jaya Knitting (ERP) | **Pengembang:** Dany Triyatna | **Status Pekerjaan:** 100% Selesai (Done)

---

### I. KEGIATAN PENGEMBANGAN SISTEM (FITUR BARU, MODUL & ARSITEKTUR)

| No | Tanggal | Modul / Menu | Deskripsi Kegiatan Pengembangan Sistem | Status |
|:--:|:-------:|:-------------|:---------------------------------------|:------:|
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
| 19 | 23-11-2026 | Pergudangan & Persediaan | **[Arsitektur & Fitur]** Unifikasi integritas saldo minus riil tanpa clamping (Zero-Guard Bypass): sinkronisasi tampilan saldo fisik, stok tersedia, sisa kebutuhan WO, dan kuota transfer agar konsisten 100% menampilkan nilai minus dan penanda visual merah sesuai Laporan Persediaan fisik. | **Done** |

---

### RINGKASAN PEKERJAAN (28 SEPTEMBER – 27 NOVEMBER 2026)
* **Kegiatan Pengembangan (19 Poin):** Integrasi Barcode Scanner DO, Booking WO Persediaan, Interactive Breakdown Produksi, Zero-Client-Cache, Auto-Cascade Closed WO, Export Sisa CMT, Integritas Saldo Minus Riil Persediaan, dsb.
* **Status Implementasi:** Seluruh 19 inisiatif pengembangan sistem telah selesai 100% dan teruji live di server produksi.
* *\* Rincian teknis kegiatan pemeliharaan sistem (maintenance, bugfix & integritas data) disajikan lengkap pada dokumen Lampiran.*

**TOTAL BIAYA PEMELIHARAAN & PENGEMBANGAN:**  
**Rp 6.200.000,-** *(Terbilang: Enam Juta Dua Ratus Ribu Rupiah)*  
*(Dihitung proporsional 2 periode bulanan: Oktober & November 2026)*

```
Mengetahui / Menyetujui,                           Bandung, 27 November 2026
PT CITRA JAYA KNITTING                             Pengembang / Konsultan Sistem



( ......................................... )      DANY TRIYATNA
Direksi / Manajemen                                Lead System Architect & Developer
```

---

<br><br>

# DOKUMEN LAMPIRAN
## DAFTAR KEGIATAN PEMELIHARAAN SISTEM & INTEGRITAS DATA
### APLIKASI PT CITRA JAYA KNITTING (ERP)
**Periode:** 28 September 2026 – 27 November 2026  
**Kategori:** Maintenance, Bugfix, Integritas Database & Optimasi Server | **Total Volume:** 48 Poin Kegiatan | **Status:** 100% Selesai (Done)

---

| No | Tanggal | Modul / Menu | Deskripsi Pemeliharaan, Perbaikan Bug & Optimasi Sistem | Status |
|:--:|:-------:|:-------------|:--------------------------------------------------------|:------:|
| 1 | 29-09-2026 | Purchasing (Receive Item) | **[Bugfix]** Penanganan presisi desimal dan pencegahan floating point error pada entri kuantitas dan harga penerimaan barang. | **Done** |
| 2 | 29-09-2026 | Customer Receipt | **[Bugfix]** Validasi kelengkapan header relasi customer receipt untuk mencegah record yatim (orphaned records). | **Done** |
| 3 | 30-09-2026 | Sales Order & SPK | **[Bugfix]** Penguncian status approved pada Sales Order dan sinkronisasi otomatis status SPK terkait. | **Done** |
| 4 | 30-09-2026 | Delivery Order (DO) | **[Bugfix]** Penyesuaian relasi data delivery order terhadap varian warna dan nomor lot kain rajut. | **Done** |
| 5 | 01-10-2026 | Item Transfer | **[Bugfix]** Koreksi query join mutasi antar gudang agar saldo tidak terpotong ganda saat editing transfer. | **Done** |
| 6 | 01-10-2026 | Barang Masuk (BTM) | **[Bugfix]** Penanganan nomor lot kosong dengan penetapan default strip (-) untuk menjaga relasi kartu stok. | **Done** |
| 7 | 02-10-2026 | Laporan Persediaan | **[Maintenance]** Rekonsiliasi saldo fisik riil dan perbaikan anomali kartu stok pada periode sebelum cut-off. | **Done** |
| 8 | 06-10-2026 | Work Order (WO) | **[Bugfix]** Perbaikan tampilan breakdown kebutuhan benang pada printout Work Order rajut. | **Done** |
| 9 | 07-10-2026 | Item Transfer | **[Bugfix]** Penegakan validasi kuota maksimal transfer agar tidak melebihi alokasi order yang disetujui. | **Done** |
| 10 | 08-10-2026 | Barang Keluar (BTK) | **[Bugfix]** Sinkronisasi pengeluaran bahan baku rajut dengan nomor referensi WO yang valid. | **Done** |
| 11 | 10-10-2026 | Laporan Persediaan | **[Audit]** Audit konsistensi ledger `trans_barang` terhadap snapshot history bulanan. | **Done** |
| 12 | 12-10-2026 | Keuangan | **[Bugfix]** Koreksi formula perhitungan PPh dan pembulatan pada jurnal kas operasional. | **Done** |
| 13 | 13-10-2026 | Delivery Order (DO) | **[Bugfix]** Penyesuaian kalkulasi sisa kuota kirim pada Sales Order saat DO dibatalkan. | **Done** |
| 14 | 14-10-2026 | Purchasing | **[Bugfix]** Penanganan purchase invoice berstatus void agar membatalkan mutasi stok terkait. | **Done** |
| 15 | 15-10-2026 | Item Transfer | **[Bugfix]** Penambahan proteksi transaksi ganda (double submission prevention) saat submit form transfer. | **Done** |
| 16 | 17-10-2026 | Laporan Persediaan | **[Bugfix]** Koreksi filter gudang pada laporan mutasi persediaan agar menampilkan transaksi transit. | **Done** |
| 17 | 18-10-2026 | Customer Receipt | **[Bugfix]** Perbaikan pencetakan kwitansi pelunasan konsumen dengan format currency standar. | **Done** |
| 18 | 19-10-2026 | Barang Keluar (BTK) | **[Bugfix]** Penanganan error null pointer saat pemilihan lot barang pada dokumen draft. | **Done** |
| 19 | 21-10-2026 | SDM & Penggajian | **[Bugfix]** Perbaikan formula insentif operator rajut berbasis target meter kain yang dihasilkan. | **Done** |
| 20 | 22-10-2026 | Barang Masuk (BTM) | **[Bugfix]** Sinkronisasi nomor surat jalan vendor CMT pada form penerimaan barang hasil rajut. | **Done** |
| 21 | 23-10-2026 | Item Transfer | **[Bugfix]** Perbaikan sorting tanggal transfer agar surat jalan tercetak kronologis. | **Done** |
| 22 | 27-10-2026 | Laporan Persediaan | **[Maintenance]** Sanitasi nomor lot dan perlindungan whitespace tersembunyi pada validasi persediaan berlapis. | **Done** |
| 23 | 29-10-2026 | Infrastruktur & Jaringan | **[Jaringan]** Dukungan akses direct IP Server ketika jaringan lokal atau DNS domain kantor mengalami kendala resolusi. | **Done** |
| 24 | 29-10-2026 | Laporan Persediaan | **[Maintenance]** Sanitasi menyeluruh nomor lot pada form Barang Masuk & Barang Keluar serta pembersihan script debug. | **Done** |
| 25 | 29-10-2026 | Barang Keluar (BTK) | **[Bugfix]** Filter seleksi hanya memunculkan lot bersaldo positif (`qty > 0`) pada modal pemilihan Barang Keluar. | **Done** |
| 26 | 30-10-2026 | Item Transfer | **[Bugfix]** Standarisasi pencocokan sisa kuota WO dan join transfer berbasis nomor lot dengan dual fallback. | **Done** |
| 27 | 30-10-2026 | Customer Receipt | **[Bugfix]** Proteksi debouncing anti-double-click dan perbaikan mekanisme rollback status saat nota penerimaan dibatalkan. | **Done** |
| 28 | 31-10-2026 | Work Order (WO) | **[Bugfix]** Sinkronisasi QTY on hand Work Order ke saldo akhir per item_id dan gudang produksi terkait. | **Done** |
| 29 | 31-10-2026 | Work Order (WO) | **[Bugfix]** Perbaikan pemanggilan method `getData` pada `lists_detail` dan filter WO aktif non-sampel di dashboard. | **Done** |
| 30 | 31-10-2026 | Barang Keluar (BTK) | **[Maintenance]** Penguncian tombol approve jika stok lot gudang fisik kurang, serta arahan interaktif ke mutasi barang. | **Done** |
| 31 | 31-10-2026 | Item Transfer | **[Maintenance]** Penguncian tombol approve jika stok lot gudang asal kurang, serta arahan interaktif ke mutasi barang. | **Done** |
| 32 | 01-11-2026 | Laporan Persediaan | **[Bugfix]** Penyempurnaan validasi stok fisik untuk barang non-lot (`-`) pada Barang Keluar dan Item Transfer. | **Done** |
| 33 | 01-11-2026 | Laporan Persediaan | **[Bugfix]** Pembatasan input tanggal transaksi maksimal hari ini dan filter pembukuan persediaan tidak melebihi bulan berjalan. | **Done** |
| 34 | 02-11-2026 | Laporan Persediaan | **[Bugfix]** Pencegahan carry forward persediaan melompat ke bulan masa depan dan penguncian saldo mutasi persediaan bulanan. | **Done** |
| 35 | 06-11-2026 | Laporan Persediaan | **[Bugfix]** Penegakan parameter nomor lot dan satuan kemasan (pack) pada pengecekan stok fisik di Transfer & Barang Keluar. | **Done** |
| 36 | 09-11-2026 | Laporan Persediaan | **[Bugfix]** Dukungan validasi total stok gudang untuk barang non-lot dan multi-pack pada Item Transfer dan Barang Keluar. | **Done** |
| 37 | 10-11-2026 | Work Order & Produksi | **[Bugfix]** Menyembunyikan baris varian bernilai qty 0 yang dibatalkan pada lembar kerja WO/Produksi serta penegakan validasi gudang pada lot_id. | **Done** |
| 38 | 11-11-2026 | Laporan Persediaan | **[Bugfix]** Perhitungan saldo mutasi `trans_barang_balances` live langsung dari database (bukan nilai frontend `qty_exist`), perbaikan join edit transfer, dan filter approved BTK pada rendemen. | **Done** |
| 39 | 11-11-2026 | Laporan Persediaan | **[Bugfix]** Perhitungan live saldo barang non-lot dan pemasangan non-negative guard pada ledger balances. | **Done** |
| 40 | 11-11-2026 | Barang Keluar (BTK) | **[Bugfix]** Pembungkusan agregasi `getDataByWalkorder` ke derived table agar kompatibel dengan strict mode PostgreSQL. | **Done** |
| 41 | 12-11-2026 | Item Transfer | **[Bugfix]** Unifikasi katalog item picker dengan Laporan Persediaan dan penegakan pencocokan nomor lot spesifik per kemasan saat approval. | **Done** |
| 42 | 13-11-2026 | Item Transfer | **[Bugfix]** Penambahan validasi mandiri (*self-healing stock verification*) terhadap `trans_barang_history` saat approval transfer. | **Done** |
| 43 | 13-11-2026 | Item Transfer | **[Bugfix]** Eliminasi join redundan `trans_lots` pada fungsi `getDataPrint` untuk menghilangkan duplikasi baris barang pada Surat Jalan. | **Done** |
| 44 | 17-11-2026 | Barang Masuk (BTM) | **[Bugfix]** Perbaikan query log fatal `abx.created_at does not exist` menjadi `head.created_at`, serta upgrade validasi scanner barcode cerdas yang kontekstual mengidentifikasi ketidaksesuaian kombinasi Proses dan CMT. | **Done** |
| 45 | 20-11-2026 | Transaksi & Routing | **[Bugfix]** Perbaikan routing dan handling parameter token terenkripsi pada link URL detail pengeluaran barang (outgoing-goods) untuk mencegah unhandled exceptions. | **Done** |
| 46 | 22-11-2026 | Infrastruktur Server | **[Maintenance]** Rekonfigurasi ownership dan hak akses direktori penyimpanan session/cache `/pejuang/production/writable` di server web LiteSpeed (LSPHP 8.3) untuk mencegah HTTP 500 error pada saat user mengakses form transaksi. | **Done** |
| 47 | 23-11-2026 | Produksi & Walk Order | **[Bugfix]** Penguncian relasi transaksi Transfer (TRF), BTK, dan BTM hanya membaca dokumen approved (`status = 1 AND active = 1`) pada modal pop-up Form Produksi, laporan laba kotor, dan rekapitulasi monitoring, mencegah transaksi yang dibatalkan ikut terhitung ke riwayat produksi. | **Done** |
| 48 | 23-11-2026 | Database Engine | **[Maintenance]** Penghapusan constraint `chk_trans_barang_balances_non_negative` pada tabel `trans_barang_balances` di database PostgreSQL produksi & lokal untuk mendukung integritas mutasi riil ketika terjadi stok minus. | **Done** |

---
*Dokumen Lampiran resmi pemeliharaan sistem & integritas data Aplikasi PT Citra Jaya Knitting (28 Sept – 27 Nov 2026)*
