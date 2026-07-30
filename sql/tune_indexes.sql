-- Database Index Tuning for citra_knitt_dev_db

CREATE INDEX IF NOT EXISTS idx_trans_barang_tanggal ON trans_barang (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_barang_kode_trans ON trans_barang (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_barang_id_barang ON trans_barang (id_barang);
CREATE INDEX IF NOT EXISTS idx_trans_barang_id_gudang ON trans_barang (id_gudang);
CREATE INDEX IF NOT EXISTS idx_trans_barang_comp ON trans_barang (tanggal, id_barang);

CREATE INDEX IF NOT EXISTS idx_trans_barang_history_tanggal ON trans_barang_history (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_barang_history_id_barang ON trans_barang_history (id_barang);
CREATE INDEX IF NOT EXISTS idx_trans_barang_history_kode ON trans_barang_history (kode_transaksi);

CREATE INDEX IF NOT EXISTS idx_trans_barang_header_tanggal ON trans_barang_header (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_barang_header_kode ON trans_barang_header (kode_transaksi);

CREATE INDEX IF NOT EXISTS idx_trans_barang_detail_kode ON trans_barang_detail (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_barang_detail_id_barang ON trans_barang_detail (id_barang);

CREATE INDEX IF NOT EXISTS idx_trans_barang_masuk_prod_tgl ON trans_barang_masuk_produksi (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_barang_masuk_prod_kode ON trans_barang_masuk_produksi (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_barang_masuk_prod_brg ON trans_barang_masuk_produksi (id_barang);

CREATE INDEX IF NOT EXISTS idx_sdm_absensi_tanggal ON sdm_absensi (tanggal);
CREATE INDEX IF NOT EXISTS idx_sdm_absensi_id_karyawan ON sdm_absensi (id_karyawan);

CREATE INDEX IF NOT EXISTS idx_trans_delivery_tanggal ON trans_delivery (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_delivery_kode ON trans_delivery (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_delivery_det_kode ON trans_delivery_detail (kode_transaksi);

CREATE INDEX IF NOT EXISTS idx_trans_sales_order_tanggal ON trans_sales_order (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_sales_order_kode ON trans_sales_order (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_sales_order_det_kode ON trans_sales_order_det (kode_transaksi);

CREATE INDEX IF NOT EXISTS idx_trans_walkorder_tanggal ON trans_walkorder (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_walkorder_kode ON trans_walkorder (kode_transaksi);

CREATE INDEX IF NOT EXISTS idx_trans_po_header_tanggal ON trans_po_header (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_po_header_kode ON trans_po_header (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_po_detail_kode ON trans_po_detail (kode_transaksi);

CREATE INDEX IF NOT EXISTS idx_log_activity_created ON log_activity (created_at);

ANALYZE;
