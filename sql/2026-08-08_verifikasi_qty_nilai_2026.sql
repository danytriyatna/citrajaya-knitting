-- ============================================================
-- VERIFIKASI kecocokan QTY dan NILAI (Rp) - trans_barang_masuk_produksi
-- vs trans_produksi_operator - KHUSUS TAHUN 2026 (READ-ONLY)
-- ============================================================
-- Versi scoped tahun dari 2026-08-08_verifikasi_qty_nilai_semua.sql.
-- Hasil query ini yang di-export ke CSV utk dipakai sebagai input
-- command: php spark maintenance:rebuild-produksi-operator <csv> [--apply]
-- (CSV harus punya kolom "id_header" - query di bawah sudah sesuai).

SELECT
    h.id                          AS id_header,
    h.kode_transaksi,
    h.tanggal,
    h.status,

    COALESCE(bm.total_qty, 0)     AS qty_barang_masuk,
    COALESCE(tpo.total_qty, 0)    AS qty_produksi_operator,
    COALESCE(bm.total_qty, 0) - COALESCE(tpo.total_qty, 0)       AS selisih_qty,

    COALESCE(bm.total_nilai, 0)   AS nilai_barang_masuk,
    COALESCE(tpo.total_nilai, 0)  AS nilai_produksi_operator,
    COALESCE(bm.total_nilai, 0) - COALESCE(tpo.total_nilai, 0)   AS selisih_nilai,

    COALESCE(bm.jumlah_baris, 0)  AS jumlah_baris_barang_masuk,
    COALESCE(tpo.jumlah_baris, 0) AS jumlah_baris_produksi_operator
FROM trans_barang_header h
LEFT JOIN (
    SELECT id_header,
           SUM(qty)    AS total_qty,
           SUM(amount) AS total_nilai,
           COUNT(*)    AS jumlah_baris
    FROM trans_barang_masuk_produksi
    GROUP BY id_header
) bm ON bm.id_header = h.id
LEFT JOIN (
    SELECT kode_transaksi,
           SUM(qty)         AS total_qty,
           SUM(harga_total) AS total_nilai,
           COUNT(*)         AS jumlah_baris
    FROM trans_produksi_operator
    WHERE active = 1
    GROUP BY kode_transaksi
) tpo ON tpo.kode_transaksi = h.kode_transaksi
WHERE h.status = 1
  AND h.id_kategori IN (12, 1)
  AND h.tanggal >= '2026-01-01' AND h.tanggal < '2027-01-01'
  AND (
        COALESCE(bm.total_qty, 0)   <> COALESCE(tpo.total_qty, 0)
     OR COALESCE(bm.total_nilai, 0) <> COALESCE(tpo.total_nilai, 0)
  )
ORDER BY h.tanggal DESC;

-- Kosong = semua dokumen 2026 sudah cocok.
