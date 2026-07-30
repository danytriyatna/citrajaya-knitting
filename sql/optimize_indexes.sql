-- Master Performance Indexing Script for PostgreSQL
-- Citra Jaya Knitting ERP Database Optimization

-- 1. Security & Authentication Indexes
CREATE INDEX IF NOT EXISTS idx_sec_role_priv_role_mod ON sec_role_priv (role_id, module_id);
CREATE INDEX IF NOT EXISTS idx_sec_role_priv_mod ON sec_role_priv (module_id);
CREATE INDEX IF NOT EXISTS idx_sec_modul_alias ON sec_modul (alias);
CREATE INDEX IF NOT EXISTS idx_sec_user_role_user ON sec_user_role (user_id);
CREATE INDEX IF NOT EXISTS idx_sec_user_role_role ON sec_user_role (role_id);
CREATE INDEX IF NOT EXISTS idx_sec_log_user_created ON sec_log (user_id, created_at);

-- 2. Master Data (Referensi) Indexes
CREATE INDEX IF NOT EXISTS idx_ref_barang_gudang ON ref_barang (id_gudang);
CREATE INDEX IF NOT EXISTS idx_ref_barang_jenis ON ref_barang (id_jenis_barang);
CREATE INDEX IF NOT EXISTS idx_ref_barang_satuan ON ref_barang (id_satuan);
CREATE INDEX IF NOT EXISTS idx_ref_barang_kode ON ref_barang (kode_barang);
CREATE INDEX IF NOT EXISTS idx_ref_barang_active ON ref_barang (active);
CREATE INDEX IF NOT EXISTS idx_ref_gudang_active ON ref_gudang (active);
CREATE INDEX IF NOT EXISTS idx_ref_vendor_active ON ref_vendor (active);
CREATE INDEX IF NOT EXISTS idx_ref_konsumen_active ON ref_konsumen (active);
CREATE INDEX IF NOT EXISTS idx_ref_karyawan_active ON ref_karyawan (active);

-- 3. Purchasing & Receive Item Indexes
CREATE INDEX IF NOT EXISTS idx_trans_po_hdr_vendor ON trans_po_header (id_vendor);
CREATE INDEX IF NOT EXISTS idx_trans_po_hdr_status ON trans_po_header (status);
CREATE INDEX IF NOT EXISTS idx_trans_po_hdr_tgl ON trans_po_header (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_po_det_header ON trans_po_detail (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_po_det_barang ON trans_po_detail (id_barang);
CREATE INDEX IF NOT EXISTS idx_trans_receive_hdr_po ON trans_receive_header (id_po);
CREATE INDEX IF NOT EXISTS idx_trans_receive_hdr_vendor ON trans_receive_header (id_vendor);
CREATE INDEX IF NOT EXISTS idx_trans_receive_hdr_status ON trans_receive_header (status);
CREATE INDEX IF NOT EXISTS idx_trans_receive_det_hdr ON trans_receive_detail (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_receive_det_barang ON trans_receive_detail (id_barang);

-- 4. Inventory & Stock (Persediaan & Barang Keluar/Masuk) Indexes
CREATE INDEX IF NOT EXISTS idx_trans_barang_hdr_gudang ON trans_barang_header (id_gudang);
CREATE INDEX IF NOT EXISTS idx_trans_barang_hdr_kat ON trans_barang_header (id_kategori);
CREATE INDEX IF NOT EXISTS idx_trans_barang_hdr_status ON trans_barang_header (status);
CREATE INDEX IF NOT EXISTS idx_trans_barang_hdr_tgl ON trans_barang_header (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_barang_det_header ON trans_barang_detail (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_barang_det_barang ON trans_barang_detail (id_barang);
CREATE INDEX IF NOT EXISTS idx_trans_barang_hist_gb ON trans_barang_history (id_gudang, id_barang);
CREATE INDEX IF NOT EXISTS idx_trans_persediaan_gb ON trans_persediaan (id_gudang, id_barang);

-- 5. Sales & Delivery Order Indexes
CREATE INDEX IF NOT EXISTS idx_trans_so_konsumen ON trans_sales_order (id_konsumen);
CREATE INDEX IF NOT EXISTS idx_trans_so_kode ON trans_sales_order (kode_sales_order);
CREATE INDEX IF NOT EXISTS idx_trans_so_status ON trans_sales_order (status);
CREATE INDEX IF NOT EXISTS idx_trans_so_det_header ON trans_sales_order_det (id_sales_order);
CREATE INDEX IF NOT EXISTS idx_trans_delivery_so ON trans_delivery (id_sales_order);
CREATE INDEX IF NOT EXISTS idx_trans_delivery_status ON trans_delivery (status);
CREATE INDEX IF NOT EXISTS idx_trans_delivery_det_hdr ON trans_delivery_detail (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_invoice_so ON trans_invoice (id_sales_order);
CREATE INDEX IF NOT EXISTS idx_trans_invoice_status ON trans_invoice (status);
CREATE INDEX IF NOT EXISTS idx_trans_invoice_det_hdr ON trans_invoice_detail (id_header);

-- 6. Production & Work Order (WO) Indexes
CREATE INDEX IF NOT EXISTS idx_trans_wo_so ON trans_walkorder (id_sales_order);
CREATE INDEX IF NOT EXISTS idx_trans_wo_kode ON trans_walkorder (kode_walkorder);
CREATE INDEX IF NOT EXISTS idx_trans_wo_det_hdr ON trans_walkorder_detail (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_wo_proses_hdr ON trans_walkorder_proses (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_prod_wo ON trans_produksi (id_walkorder);
CREATE INDEX IF NOT EXISTS idx_trans_prod_status ON trans_produksi (status);

-- 7. Finance & Accounting (Keuangan) Indexes
CREATE INDEX IF NOT EXISTS idx_m_coa_code ON m_coa (code);
CREATE INDEX IF NOT EXISTS idx_m_coa_active ON m_coa (active);
CREATE INDEX IF NOT EXISTS idx_trans_akun_tgl ON trans_akun (tanggal);
CREATE INDEX IF NOT EXISTS idx_trans_akun_status ON trans_akun (status);
CREATE INDEX IF NOT EXISTS idx_trans_akun_det_hdr ON trans_akun_det (id_header);
CREATE INDEX IF NOT EXISTS idx_trans_akun_det_coa ON trans_akun_det (id_coa);

-- Update Query Planner Statistics
ANALYZE;
