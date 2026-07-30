-- Verified High-Performance Database Tuning Script for citra_knitt_dev_db

-- 1. Large Production & Operator Tables (55,000+ rows)
CREATE INDEX IF NOT EXISTS idx_trans_prod_op_produksi ON trans_produksi_operator (id_produksi);
CREATE INDEX IF NOT EXISTS idx_trans_prod_op_operator ON trans_produksi_operator (id_operator);
CREATE INDEX IF NOT EXISTS idx_trans_prod_op_proses ON trans_produksi_operator (id_proses);
CREATE INDEX IF NOT EXISTS idx_trans_prod_op_tgl ON trans_produksi_operator (tgl_transaksi);
CREATE INDEX IF NOT EXISTS idx_trans_prod_op_kode ON trans_produksi_operator (kode_transaksi);

-- 2. Large Transfer & Order Tables (52,000+ rows)
CREATE INDEX IF NOT EXISTS idx_tb_trf_so_det_header ON trans_barang_trf_so_det (id_header);
CREATE INDEX IF NOT EXISTS idx_tb_trf_so_det_so ON trans_barang_trf_so_det (kode_sales_order);
CREATE INDEX IF NOT EXISTS idx_tb_trf_so_det_konsumen ON trans_barang_trf_so_det (id_konsumen);

-- 3. Sales Order & Size Tables (17,000+ rows)
CREATE INDEX IF NOT EXISTS idx_so_ukuran_so ON trans_sales_order_ukuran (id_sales_order);
CREATE INDEX IF NOT EXISTS idx_so_ukuran_so_det ON trans_sales_order_ukuran (id_sales_order_det);
CREATE INDEX IF NOT EXISTS idx_so_ukuran_uk ON trans_sales_order_ukuran (id_ukuran);

CREATE INDEX IF NOT EXISTS idx_so_tgl ON trans_sales_order (tgl_transaksi);
CREATE INDEX IF NOT EXISTS idx_so_status ON trans_sales_order (status);

-- 4. Attendance & HR Tables (31,000+ rows)
CREATE INDEX IF NOT EXISTS idx_sdm_absensi_tgl ON sdm_absensi (tanggal);
CREATE INDEX IF NOT EXISTS idx_sdm_absensi_karyawan_tgl ON sdm_absensi (id_karyawan, tanggal);

-- 5. Barang Header & Movement Tables
CREATE INDEX IF NOT EXISTS idx_tb_hdr_kode ON trans_barang_header (kode_transaksi);
CREATE INDEX IF NOT EXISTS idx_tb_hdr_tgl ON trans_barang_header (tanggal);
CREATE INDEX IF NOT EXISTS idx_tb_hdr_vendor ON trans_barang_header (id_vendor);
CREATE INDEX IF NOT EXISTS idx_tb_hdr_status ON trans_barang_header (status);

CREATE INDEX IF NOT EXISTS idx_po_hdr_date ON trans_po_header (po_date);
CREATE INDEX IF NOT EXISTS idx_po_hdr_vendor ON trans_po_header (id_vendor);
CREATE INDEX IF NOT EXISTS idx_po_hdr_status ON trans_po_header (status);

-- 6. SSD Planner Configuration & Memory Allocation
ALTER DATABASE citra_knitt_dev_db SET random_page_cost = 1.1;
ALTER DATABASE citra_knitt_dev_db SET work_mem = '16MB';

-- 7. Rebuild Cost-Based Statistics for all tables
ANALYZE;
