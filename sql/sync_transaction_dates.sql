-- ==============================================================================
-- Audit & Synchronization Script for Stock Card Transaction Dates
-- Database: PostgreSQL & MySQL compatible
-- ==============================================================================

-- 1. AUDIT: View Discrepancies between trans_barang and Header Tables
-- ------------------------------------------------------------------------------
-- A. Barang Masuk / Keluar Header (trans_barang_header)
SELECT 
    tb.id AS trans_barang_id,
    tb.kode_transaksi,
    tb.tanggal AS stock_card_date,
    tbh.tanggal AS header_date,
    'trans_barang_header' AS source_table
FROM trans_barang tb
INNER JOIN trans_barang_header tbh ON tb.kode_transaksi = tbh.kode_transaksi
WHERE tb.tanggal <> tbh.tanggal;

-- B. Delivery Order (trans_delivery)
SELECT 
    tb.id AS trans_barang_id,
    tb.kode_transaksi,
    tb.tanggal AS stock_card_date,
    td.tanggal AS header_date,
    'trans_delivery' AS source_table
FROM trans_barang tb
INNER JOIN trans_delivery td ON tb.kode_transaksi = td.kode_delivery
WHERE tb.tanggal <> td.tanggal;

-- C. Purchase Order Receive (trans_receive_header)
SELECT 
    tb.id AS trans_barang_id,
    tb.kode_transaksi,
    tb.tanggal AS stock_card_date,
    trh.tanggal AS header_date,
    'trans_receive_header' AS source_table
FROM trans_barang tb
INNER JOIN trans_receive_header trh ON tb.kode_transaksi = trh.kode_receive
WHERE tb.tanggal <> trh.tanggal;

-- D. Work Order / SPK (trans_walkorder)
SELECT 
    tb.id AS trans_barang_id,
    tb.kode_transaksi,
    tb.tanggal AS stock_card_date,
    two.tanggal AS header_date,
    'trans_walkorder' AS source_table
FROM trans_barang tb
INNER JOIN trans_walkorder two ON tb.kode_transaksi = two.kode_walkorder
WHERE tb.tanggal <> two.tanggal;


-- ==============================================================================
-- 2. SYNCHRONIZATION: Update trans_barang dates to match parent header dates
-- ==============================================================================

-- Sync Barang Masuk & Keluar Header
UPDATE trans_barang 
SET tanggal = tbh.tanggal
FROM trans_barang_header tbh
WHERE trans_barang.kode_transaksi = tbh.kode_transaksi
  AND trans_barang.tanggal <> tbh.tanggal;

-- Sync Delivery Order Header
UPDATE trans_barang 
SET tanggal = td.tanggal
FROM trans_delivery td
WHERE trans_barang.kode_transaksi = td.kode_delivery
  AND trans_barang.tanggal <> td.tanggal;

-- Sync Receive Item Header
UPDATE trans_barang 
SET tanggal = trh.tanggal
FROM trans_receive_header trh
WHERE trans_barang.kode_transaksi = trh.kode_receive
  AND trans_barang.tanggal <> trh.tanggal;

-- Sync Work Order Header
UPDATE trans_barang 
SET tanggal = two.tanggal
FROM trans_walkorder two
WHERE trans_barang.kode_transaksi = two.kode_walkorder
  AND trans_barang.tanggal <> two.tanggal;
