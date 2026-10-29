-- ==============================================================================
-- Perbaikan manual: tanggal transaksi TRF260600171 (Item Transfer)
-- ------------------------------------------------------------------------------
-- Kasus: dokumen ini dibuat sebagai draft ~18 Juni 2026 (nomor "TRF2606..." sudah
-- terkunci sejak saat itu), tapi baru di-approve belakangan dengan field "Tanggal
-- Transfer" menunjuk 2026-07-01, sehingga baris trans_barang (sumber Laporan
-- Stock Card) ikut tercatat 1 Juli, bukan 18 Juni sesuai transaksi sebenarnya.
--
-- Baik trans_barang_trf_header maupun trans_barang sama-sama sudah salah
-- (sama-sama 2026-07-01), jadi tidak bisa diperbaiki lewat sync antar tabel biasa
-- (lihat sync_transaction_dates.sql) - tanggal yang benar (18 Juni 2026) harus
-- diisi ulang secara eksplisit di sini.
--
-- CATATAN: hanya trans_barang_trf_header & trans_barang yang diubah. Tabel
-- trans_lots & trans_barang_balances (saldo lot berjalan) SENGAJA TIDAK disentuh
-- karena keduanya representasi saldo saat ini per lot (bisa dipakai bersama oleh
-- transaksi lain), bukan histori per-dokumen - resiko salah ubah lebih besar
-- daripada manfaatnya, dan tidak memengaruhi kolom Tanggal di Laporan Stock Card.
-- ==============================================================================

BEGIN;

-- 1. PREVIEW dulu sebelum diubah - pastikan hasilnya sesuai dugaan di atas
SELECT id, kode_transaksi, tanggal, status
FROM trans_barang_trf_header
WHERE kode_transaksi = 'TRF260600171';

SELECT id, kode_transaksi, tanggal, jenis_transaksi, jumlah, lot_no
FROM trans_barang
WHERE kode_transaksi = 'TRF260600171'
ORDER BY id;

-- 2. UPDATE - set tanggal ke 18 Juni 2026, pertahankan jam yang tersimpan
UPDATE trans_barang_trf_header
SET tanggal = '2026-06-18'::date + tanggal::time
WHERE kode_transaksi = 'TRF260600171';

UPDATE trans_barang
SET tanggal = '2026-06-18'::date + tanggal::time
WHERE kode_transaksi = 'TRF260600171';

-- 3. VERIFIKASI hasil
SELECT id, kode_transaksi, tanggal, status
FROM trans_barang_trf_header
WHERE kode_transaksi = 'TRF260600171';

SELECT id, kode_transaksi, tanggal, jenis_transaksi, jumlah, lot_no
FROM trans_barang
WHERE kode_transaksi = 'TRF260600171'
ORDER BY id;

-- Kalau hasilnya sudah benar (18 Juni 2026), jalankan:
-- COMMIT;
-- Kalau ada yang tidak sesuai, jalankan:
-- ROLLBACK;
