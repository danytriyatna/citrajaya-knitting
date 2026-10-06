-- Script Fix WRD261000001 Gramasi Kombo Benang Kembar
-- Tanggal: 06 Oktober 2026

BEGIN;

-- 1. Kombo CREAM - SMA - CREAM (WOD 4482, Qty 50, Loss 5%)
UPDATE trans_walkorder_warna
SET gram = 15, gram_nd = 750, kg = 0.75, loss = 5, kg_loss = 0.0375, total = 0.7875, kuota_tambah = -0.7875, persen = 4.75, updated_at = NOW()
WHERE id = 8568;

UPDATE trans_walkorder_warna SET persen = 75.63 WHERE id = 8566;
UPDATE trans_walkorder_warna SET persen = 19.62 WHERE id = 8567;

UPDATE trans_walkorder_detail
SET gram = 316, gram_nd = 15800, kg = 15.8, loss = 5, kg_loss = 0.79, total = 16.59, kuota_tambah = -16.59, updated_at = NOW()
WHERE id = 4482;


-- 2. Kombo NAVY TUA - HIJAU.22 - NAVY TUA (WOD 4475, Qty 300, Loss 5%)
UPDATE trans_walkorder_warna
SET gram = 15, gram_nd = 4500, kg = 4.5, loss = 5, kg_loss = 0.225, total = 4.725, kuota_tambah = -4.725, persen = 4.75, updated_at = NOW()
WHERE id = 8546;

UPDATE trans_walkorder_warna SET persen = 75.63 WHERE id = 8544;
UPDATE trans_walkorder_warna SET persen = 19.62 WHERE id = 8545;

UPDATE trans_walkorder_detail
SET gram = 316, gram_nd = 94800, kg = 94.8, loss = 5, kg_loss = 4.74, total = 99.54, kuota_tambah = -99.54, updated_at = NOW()
WHERE id = 4475;


-- 3. Kombo M46 - KOPI TUA - KOPI TUA (WOD 4476, Qty 200, Loss 5%)
UPDATE trans_walkorder_warna
SET gram = 15, gram_nd = 3000, kg = 3, loss = 5, kg_loss = 0.15, total = 3.15, kuota_tambah = -3.15, persen = 4.75, updated_at = NOW()
WHERE id = 8549;

UPDATE trans_walkorder_warna SET persen = 75.63 WHERE id = 8547;
UPDATE trans_walkorder_warna SET persen = 19.62 WHERE id = 8548;

UPDATE trans_walkorder_detail
SET gram = 316, gram_nd = 63200, kg = 63.2, loss = 5, kg_loss = 3.16, total = 66.36, kuota_tambah = -66.36, updated_at = NOW()
WHERE id = 4476;


-- 4. Kombo OFF WHITE K - BCA - OFF WHITE K (WOD 4477, Qty 300, Loss 5%)
UPDATE trans_walkorder_warna
SET gram = 15, gram_nd = 4500, kg = 4.5, loss = 5, kg_loss = 0.225, total = 4.725, kuota_tambah = -4.725, persen = 4.75, updated_at = NOW()
WHERE id = 8552;

UPDATE trans_walkorder_warna SET persen = 75.63 WHERE id = 8550;
UPDATE trans_walkorder_warna SET persen = 19.62 WHERE id = 8551;

UPDATE trans_walkorder_detail
SET gram = 316, gram_nd = 94800, kg = 94.8, loss = 5, kg_loss = 4.74, total = 99.54, kuota_tambah = -99.54, updated_at = NOW()
WHERE id = 4477;

COMMIT;
