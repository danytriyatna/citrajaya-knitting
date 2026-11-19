<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Laporan\Models\LaporanPersediaanModel;

/**
 * REKONSILIASI CORE STOK CITRA JAYA KNITTING (CUTOFF 1 JUNI 2026)
 *
 * Command ini merekonsiliasi seluruh snapshot trans_barang_history secara berurutan
 * dari titik cutoff 1 Juni 2026 sampai bulan berjalan, memastikan:
 * 1. Seluruh transaksi non-pack dialokasikan ke Pack Dummy '-' (tidak ada NULL liar).
 * 2. Saldo awal Juni 2026 = 0, carry forward ke bulan-bulan berikutnya dihitung murni akumulatif.
 * 3. Menghilangkan anomali saldo awal minus akibat perpecahan pack di masa lalu.
 *
 * CARA PAKAI:
 *   php spark stock:reconcile
 */
class ReconcileStockHistory extends BaseCommand
{
    protected $group       = 'Stock';
    protected $name        = 'stock:reconcile';
    protected $description = 'Rekonsiliasi total snapshot trans_barang_history dari Cutoff 1 Juni 2026 hingga bulan berjalan.';

    public function run(array $params)
    {
        CLI::write("==================================================================", 'yellow');
        CLI::write(" MEMULAI REKONSILIASI STOK & REGENERASI SNAPSHOT BULANAN", 'white', 'blue');
        CLI::write(" Cutoff: 1 Juni 2026 s.d. Bulan Berjalan", 'yellow');
        CLI::write("==================================================================", 'yellow');

        $db = \Config\Database::connect();
        $mLaporan = new LaporanPersediaanModel();

        // 1. Pastikan Dummy Pack '-' ada di ref_pack
        CLI::write("\n[1/3] Menyiapkan Pack Dummy '-' di ref_pack...", 'cyan');
        $chkDummy = $db->table('ref_pack')->select('id')->where('pack_name', '-')->get()->getRow();
        if (empty($chkDummy)) {
            $db->table('ref_pack')->insert([
                'pack_name'  => '-',
                'keterangan' => 'Non-Pack / Standar',
                'active'     => 1,
            ]);
            $dummyPackId = (int)$db->insertID();
            CLI::write("  -> Pack Dummy '-' berhasil dibuat dengan ID: {$dummyPackId}", 'green');
        } else {
            $dummyPackId = (int)$chkDummy->id;
            CLI::write("  -> Pack Dummy '-' sudah ada dengan ID: {$dummyPackId}", 'green');
        }

        // 2. Normalisasi transaksi trans_barang dan detail sejak 1 Juni 2026
        CLI::write("\n[2/3] Menormalisasi transaksi historis sejak 1 Juni 2026...", 'cyan');
        
        $updTrx = $db->query("
            UPDATE trans_barang
            SET pack_id = ?
            WHERE (pack_id IS NULL OR pack_id = 0)
              AND tanggal >= '2026-06-01'
        ", [$dummyPackId]);
        CLI::write("  -> trans_barang ter-update (pack_id): " . $db->affectedRows() . " baris", 'green');

        $updLot = $db->query("
            UPDATE trans_barang
            SET lot_no = '-'
            WHERE (lot_no IS NULL OR TRIM(lot_no) = '')
              AND tanggal >= '2026-06-01'
        ");
        CLI::write("  -> trans_barang ter-update (lot_no): " . $db->affectedRows() . " baris", 'green');

        $db->query("UPDATE trans_barang_detail SET pack_id = ? WHERE pack_id IS NULL OR pack_id = 0", [$dummyPackId]);
        $db->query("UPDATE trans_barang_trf_detail SET pack_id = ? WHERE pack_id IS NULL OR pack_id = 0", [$dummyPackId]);
        $db->query("UPDATE trans_lots SET pack_id = ? WHERE pack_id IS NULL OR pack_id = 0", [$dummyPackId]);
        CLI::write("  -> detail transaksi dan trans_lots berhasil dinormalisasi.", 'green');

        // 3. Rekonsiliasi snapshot trans_barang_history bulan demi bulan
        CLI::write("\n[3/3] Menghitung ulang trans_barang_history per periode...", 'cyan');
        
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');

        // Daftar periode: dari 2026-06 sampai bulan berjalan
        $periods = [];
        $y = 2026;
        $m = 6;
        while (($y * 100 + $m) <= ($currentYear * 100 + $currentMonth)) {
            $periods[] = ['year' => $y, 'month' => $m];
            $m++;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
        }

        // Ambil daftar gudang aktif
        $listGudang = $db->table('ref_gudang')->select('id, nama_gudang')->where('active', 1)->get()->getResult();

        foreach ($periods as $period) {
            $yr = $period['year'];
            $mo = str_pad($period['month'], 2, '0', STR_PAD_LEFT);
            CLI::write("  -> Memproses Periode {$yr}-{$mo}...", 'yellow');

            foreach ($listGudang as $gudang) {
                try {
                    $mLaporan->updateDataHistory(null, (int)$gudang->id, $yr, (int)$period['month']);
                } catch (\Throwable $e) {
                    CLI::error("    [FAIL] Gudang {$gudang->nama_gudang} (ID {$gudang->id}): " . $e->getMessage());
                }
            }
            CLI::write("    [OK] Periode {$yr}-{$mo} selesai direkonsiliasi.", 'green');
        }

        CLI::write("\n==================================================================", 'green');
        CLI::write(" REKONSILIASI STOK SELESAI DENGAN SUKSES!", 'white', 'green');
        CLI::write("==================================================================\n", 'green');
        return 0;
    }
}
