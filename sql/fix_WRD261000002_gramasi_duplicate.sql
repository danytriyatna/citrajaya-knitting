-- Script Fix WRD261000002 Gramasi Kombo Benang Kembar
-- Tanggal: 07 Oktober 2026
-- Ref Sample: 1196 (Total 320 gram per pcs, Qty 125 pcs, Loss 5%)

BEGIN;

-- ============================================================================
-- 1. Kombo CTN 32/2 MARUN - SMA - M38 - MARUN (WOD 4480, Qty 125, Loss 5%)
-- ============================================================================
-- Slot 1: CTN 32/2 MARUN (ID WOV 8560) -> 223 gram
UPDATE trans_walkorder_warna
SET gram = 223, gram_nd = 27875, kg = 27.875, loss = 5, kg_loss = 1.39375, total = 29.26875, kuota_tambah = -29.26875, persen = 69.69, updated_at = NOW()
WHERE id = 8560;

-- Slot 2: CTN 32/2 SMA (ID WOV 8561) -> 52 gram
UPDATE trans_walkorder_warna
SET gram = 52, gram_nd = 6500, kg = 6.5, loss = 5, kg_loss = 0.325, total = 6.825, kuota_tambah = -6.825, persen = 16.25, updated_at = NOW()
WHERE id = 8561;

-- Slot 3: CTN 32/2 M38 (ID WOV 8562) -> 33 gram
UPDATE trans_walkorder_warna
SET gram = 33, gram_nd = 4125, kg = 4.125, loss = 5, kg_loss = 0.20625, total = 4.33125, kuota_tambah = -4.33125, persen = 10.31, updated_at = NOW()
WHERE id = 8562;

-- Slot 4: CTN 32/2 MARUN (ID WOV 8563) -> 12 gram (sebelumnya kembar 191 gram)
UPDATE trans_walkorder_warna
SET gram = 12, gram_nd = 1500, kg = 1.5, loss = 5, kg_loss = 0.075, total = 1.575, kuota_tambah = -1.575, persen = 3.75, updated_at = NOW()
WHERE id = 8563;

-- Header Detail Kombo Marun (WOD 4480) -> Total Gram 320, Total Kg 42
UPDATE trans_walkorder_detail
SET gram = 320, gram_nd = 40000, kg = 40, loss = 5, kg_loss = 2, total = 42, kuota_tambah = -42, updated_at = NOW()
WHERE id = 4480;


-- ============================================================================
-- 2. Kombo CTN 32/2 M38 - KOPI MUDA - SMA - KOPI MUDA (WOD 4479, Qty 125, Loss 5%)
-- ============================================================================
-- Slot 1: CTN 32/2 M38 (ID WOV 8556) -> 223 gram
UPDATE trans_walkorder_warna
SET gram = 223, gram_nd = 27875, kg = 27.875, loss = 5, kg_loss = 1.39375, total = 29.26875, kuota_tambah = -29.26875, persen = 69.69, updated_at = NOW()
WHERE id = 8556;

-- Slot 2: CTN 32/2 KOPI MUDA (ID WOV 8557) -> 52 gram
UPDATE trans_walkorder_warna
SET gram = 52, gram_nd = 6500, kg = 6.5, loss = 5, kg_loss = 0.325, total = 6.825, kuota_tambah = -6.825, persen = 16.25, updated_at = NOW()
WHERE id = 8557;

-- Slot 3: CTN 32/2 SMA (ID WOV 8558) -> 33 gram
UPDATE trans_walkorder_warna
SET gram = 33, gram_nd = 4125, kg = 4.125, loss = 5, kg_loss = 0.20625, total = 4.33125, kuota_tambah = -4.33125, persen = 10.31, updated_at = NOW()
WHERE id = 8558;

-- Slot 4: CTN 32/2 KOPI MUDA (ID WOV 8559) -> 12 gram (sebelumnya kembar 79 gram)
UPDATE trans_walkorder_warna
SET gram = 12, gram_nd = 1500, kg = 1.5, loss = 5, kg_loss = 0.075, total = 1.575, kuota_tambah = -1.575, persen = 3.75, updated_at = NOW()
WHERE id = 8559;

-- Header Detail Kombo M38 (WOD 4479) -> Total Gram 320, Total Kg 42
UPDATE trans_walkorder_detail
SET gram = 320, gram_nd = 40000, kg = 40, loss = 5, kg_loss = 2, total = 42, kuota_tambah = -42, updated_at = NOW()
WHERE id = 4479;

COMMIT;
