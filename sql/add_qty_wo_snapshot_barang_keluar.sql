-- Tambahan kolom snapshot untuk transaksi Barang Keluar berbasis No. WO
-- (Tipe "Pemakaian Bahan Baku atau Barang").
--
-- Nilai-nilai ini dihitung SEKALI saat transaksi disimpan (di form Barang Keluar,
-- dari alokasi No. WO yang dipilih user) dan disimpan permanen di sini, supaya
-- cetakan (print) transaksi selalu menampilkan angka yang sama persis seperti
-- pada saat transaksi dibuat -- tidak berubah lagi meskipun ada transaksi
-- Barang Keluar lain untuk WO yang sama dibuat setelahnya.
--
-- qty_terima                 = total qty transfer masuk ke gudang tsb utk WO ini
-- qty_retur                  = total qty transfer keluar lagi dari gudang tsb utk WO ini
-- qty_pemakaian_sebelumnya   = qty yang sudah terpakai di transaksi Barang Keluar
--                              LAIN untuk WO & gudang yang sama, SEBELUM transaksi ini dibuat
-- qty_sisa                   = qty_terima - qty_retur - qty_pemakaian_sebelumnya
--                              (saldo tersedia SEBELUM qty pada baris ini dipakai)

ALTER TABLE trans_barang_detail
  ADD COLUMN IF NOT EXISTS qty_terima NUMERIC,
  ADD COLUMN IF NOT EXISTS qty_retur NUMERIC,
  ADD COLUMN IF NOT EXISTS qty_pemakaian_sebelumnya NUMERIC,
  ADD COLUMN IF NOT EXISTS qty_sisa NUMERIC;
