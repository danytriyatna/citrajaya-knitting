<?php

namespace Modules\Assistant\Libraries;

use Config\Database;
use App\Models\Mdashboard;
use Modules\Transaction\Models\WalkorderModel;
use Modules\Laporan\Models\LaporanPersediaanModel;

class KaniaTools
{
    protected $db;
    protected $mdashboard;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->mdashboard = new Mdashboard();
    }

    /**
     * 🛡️ 1. Dynamic Safe SQL Analytics Engine (100% READ-ONLY)
     * Memungkinkan AI menyusun query agregasi SQL analitis secara dinamis.
     */
    public function runSafeSqlAnalytics(array $params = []): array
    {
        $sql = trim($params['sql'] ?? '');

        if (empty($sql)) {
            return [
                'status'  => false,
                'message' => 'Query SQL analitis tidak boleh kosong.'
            ];
        }

        // Bersihkan query
        $cleanSql = trim(preg_replace('/;.*$/s', '', $sql));
        $cleanSql = preg_replace('/\/\*.*?\*\//s', '', $cleanSql);
        $cleanSql = preg_replace('/--.*$/m', '', $cleanSql);
        $cleanSql = trim($cleanSql);

        // Security Check 1: Query HARUS diawali SELECT atau WITH (CTE)
        if (!preg_match('/^(SELECT|WITH)\b/i', $cleanSql)) {
            return [
                'status'  => false,
                'message' => 'Keamanan: Hanya query SELECT / data retrieval yang diizinkan.'
            ];
        }

        // Security Check 2: Dilarang modifikasi data / DDL
        $forbidden = '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|REPLACE|CREATE|GRANT|REVOKE|EXEC|EXECUTE|VACUUM|REINDEX|COPY|INTO|SET|PG_SLEEP|PG_READ_FILE|PG_WRITE_FILE)\b/i';
        if (preg_match($forbidden, $cleanSql)) {
            return [
                'status'  => false,
                'message' => 'Keamanan: Terdeteksi perintah manipulasi atau DDL yang dilarang. KANIA beroperasi dalam mode 100% Read-Only.'
            ];
        }

        if (!preg_match('/\bLIMIT\s+[0-9]+\b/i', $cleanSql)) {
            $cleanSql .= ' LIMIT 50';
        }

        // 🛡️ Self-Healing Auto-Correction untuk Mismatched Column Views
        if (stripos($cleanSql, 'v_traking_order_so') !== false) {
            $cleanSql = preg_replace('/\bkode_sales_order\b/i', 'trans_kode AS kode_sales_order', $cleanSql);
        }
        if (stripos($cleanSql, 'v_all_transaksi') !== false) {
            $cleanSql = preg_replace('/\bkode_sales_order\b/i', 'kode_transaksi AS kode_sales_order', $cleanSql);
        }
        if (stripos($cleanSql, 'trans_barang_header') !== false) {
            $cleanSql = preg_replace('/\b(bh|tbh|trans_barang_header)\.id_header\b/i', '$1.id', $cleanSql);
        }

        try {
            $startTime = microtime(true);
            $query = $this->db->query($cleanSql);
            $rows = $query->getResultArray();
            $execTime = round((microtime(true) - $startTime) * 1000, 2);

            // Auto-fallback: Jika 0 baris dan query menggunakan operator '=' pada string (nama, buyer, nama_barang, style), coba lagi dengan ILIKE
            if (empty($rows) && preg_match('/\b(nama|buyer|nama_barang|style|deskripsi)\s*=\s*\'([^\']+)\'/i', $cleanSql, $mEq)) {
                $retrySql = preg_replace('/\b(nama|buyer|nama_barang|style|deskripsi)\s*=\s*\'([^\']+)\'/i', '$1 ILIKE \'%' . trim($mEq[2]) . '%\'', $cleanSql);
                try {
                    $retryQuery = $this->db->query($retrySql);
                    $retryRows = $retryQuery->getResultArray();
                    if (!empty($retryRows)) {
                        $rows = $retryRows;
                        $cleanSql = $retrySql;
                    }
                } catch (\Throwable $e2) {
                    // Ignore retry error and keep original rows
                }
            }

            return [
                'status'         => true,
                'query_executed' => $cleanSql,
                'total_rows'     => count($rows),
                'execution_time' => "{$execTime} ms",
                'columns'        => !empty($rows) ? array_keys($rows[0]) : [],
                'data'           => $rows
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => false,
                'message' => 'Error eksekusi query: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 📊 2. Analisa Stok Barang & Material Gudang CJK (Dengan Validasi Buffer & Lot Kritis)
     */
    public function getStokBarang(array $params = []): array
    {
        $keyword  = $params['keyword'] ?? '';
        $idGudang = $params['id_gudang'] ?? null;

        $sql = "
            SELECT 
                rb.kode_barang,
                rb.nama_barang,
                rg.nama_gudang,
                tl.lot_no,
                tl.qty
            FROM trans_lots tl
            LEFT JOIN ref_barang rb ON tl.id_barang = rb.id
            LEFT JOIN ref_gudang rg ON tl.id_gudang = rg.id
            WHERE tl.qty > 0
        ";

        $binds = [];
        if (!empty($keyword)) {
            $sql .= " AND (LOWER(rb.nama_barang) LIKE ? OR LOWER(rb.kode_barang) LIKE ? OR LOWER(tl.lot_no) LIKE ?)";
            $kw = '%' . strtolower(trim($keyword)) . '%';
            $binds[] = $kw;
            $binds[] = $kw;
            $binds[] = $kw;
        }

        if (!empty($idGudang)) {
            $sql .= " AND tl.id_gudang = ?";
            $binds[] = (int) $idGudang;
        }

        $sql .= " ORDER BY rb.nama_barang ASC, rg.nama_gudang ASC, tl.lot_no ASC LIMIT 30";

        $query = $this->db->query($sql, $binds);
        $results = $query->getResultArray();

        $totalStok = 0;
        $byGudang = [];
        $compactData = [];
        $kritis = [];

        foreach ($results as $row) {
            $qty = (float)$row['qty'];
            $totalStok += $qty;
            $gName = $row['nama_gudang'] ?? 'Tanpa Gudang';
            $byGudang[$gName] = ($byGudang[$gName] ?? 0) + $qty;

            $item = [
                'kode'   => $row['kode_barang'],
                'barang' => $row['nama_barang'],
                'gudang' => $row['nama_gudang'],
                'lot'    => $row['lot_no'],
                'qty_kg' => round($qty, 2)
            ];
            $compactData[] = $item;

            // Validasi Aplikasi: Buffer stok kritis (< 25 kg)
            if ($qty <= 25.0) {
                $kritis[] = $item;
            }
        }

        return [
            'status'             => true,
            'pencarian'          => !empty($keyword) ? $keyword : 'Semua Stok Aktif',
            'total_stok_kg'      => round($totalStok, 2),
            'total_item_lot'     => count($results),
            'distribusi_gudang'  => $byGudang,
            'detail_data'        => $compactData,
            'lot_kritis_count'   => count($kritis),
            'lot_kritis_samples' => array_slice($kritis, 0, 5)
        ];
    }

    /**
     * 🧵 3. Analisa Progres, Validasi Yield & Susut Produksi SPK (Sesuai Standar Mutu Rajut CJK)
     */
    public function getStatusSPK(array $params = []): array
    {
        $noWo = trim($params['no_wo'] ?? '');

        if (empty($noWo) || in_array(strtolower($noWo), ['terbaru', 'latest', 'aktif', 'jalan', 'terkini'])) {
            $sqlHeader = "
                SELECT 
                    tw.id,
                    tw.kode_walkorder as no_wo,
                    tw.tgl_transaksi as tanggal,
                    tw.ref_kode as no_so,
                    rk.nama as nama_customer,
                    COALESCE(ts.style, tso.style, tw.keterangan_style, '-') as item_style,
                    COALESCE(ts.deskripsi, tso.deskripsi, tw.keterangan, '-') as deskripsi_kain,
                    tw.qty as target_wo_pcs,
                    tw.status,
                    tw.keterangan
                FROM trans_walkorder tw
                LEFT JOIN ref_konsumen rk ON tw.id_konsumen = rk.id
                LEFT JOIN trans_sample ts ON ts.id = tw.ref_id AND tw.tipe_id = 1
                LEFT JOIN trans_sales_order tso ON tso.id = tw.ref_id AND tw.tipe_id = 2
                WHERE tw.active = 1
                ORDER BY tw.id DESC LIMIT 1
            ";
            $rowHeader = $this->db->query($sqlHeader)->getRowArray();
        } else {
            $sqlHeader = "
                SELECT 
                    tw.id,
                    tw.kode_walkorder as no_wo,
                    tw.tgl_transaksi as tanggal,
                    tw.ref_kode as no_so,
                    rk.nama as nama_customer,
                    COALESCE(ts.style, tso.style, tw.keterangan_style, '-') as item_style,
                    COALESCE(ts.deskripsi, tso.deskripsi, tw.keterangan, '-') as deskripsi_kain,
                    tw.qty as target_wo_pcs,
                    tw.status,
                    tw.keterangan
                FROM trans_walkorder tw
                LEFT JOIN ref_konsumen rk ON tw.id_konsumen = rk.id
                LEFT JOIN trans_sample ts ON ts.id = tw.ref_id AND tw.tipe_id = 1
                LEFT JOIN trans_sales_order tso ON tso.id = tw.ref_id AND tw.tipe_id = 2
                WHERE (LOWER(tw.kode_walkorder) LIKE ? OR LOWER(tw.ref_kode) LIKE ?) AND tw.active = 1
                ORDER BY tw.id DESC
                LIMIT 1
            ";
            $bindKw = '%' . strtolower($noWo) . '%';
            $rowHeader = $this->db->query($sqlHeader, [$bindKw, $bindKw])->getRowArray();
        }

        if (!$rowHeader) {
            return [
                'status' => false,
                'message' => "Work Order / Sales Order dengan kode '{$noWo}' tidak ditemukan di sistem."
            ];
        }

        $idWo = $rowHeader['id'];
        $kodeWo = $rowHeader['no_wo'];

        // 1. Validasi Aplikasi: Total Benang Terpakai (BTK Approved)
        $sqlBtk = "
            SELECT 
                SUM(COALESCE(tbd.qty, 0)) as total_benang_keluar_kg,
                SUM(COALESCE(COALESCE(tbd.price, 0) * COALESCE(tbd.qty, 0), 0)) as total_biaya_benang_rp
            FROM trans_barang_header tbh
            JOIN trans_barang_detail tbd ON tbd.id_header = tbh.id
            WHERE (tbh.id_wo = ? OR tbh.no_ref_wo = ?) AND tbh.jenis_transaksi = 2 AND tbh.status = 1 AND tbh.active = 1
        ";
        $rowBtk = $this->db->query($sqlBtk, [$idWo, $kodeWo])->getRowArray();
        $benangKeluarKg = (float)($rowBtk['total_benang_keluar_kg'] ?? 0);
        $biayaBenangRp  = (float)($rowBtk['total_biaya_benang_rp'] ?? 0);

        // 2. Validasi Aplikasi: Total Hasil Kain Masuk (BTM Approved)
        $sqlBtm = "
            SELECT 
                SUM(COALESCE(tbd.qty, 0)) as total_kain_masuk_kg,
                SUM(COALESCE(COALESCE(tbd.price, 0) * COALESCE(tbd.qty, 0), 0)) as total_nilai_kain_rp
            FROM trans_barang_header tbh
            JOIN trans_barang_detail tbd ON tbd.id_header = tbh.id
            WHERE (tbh.id_wo = ? OR tbh.no_ref_wo = ?) AND tbh.jenis_transaksi = 1 AND tbh.status = 1 AND tbh.active = 1
        ";
        $rowBtm = $this->db->query($sqlBtm, [$idWo, $kodeWo])->getRowArray();
        $kainMasukKg = (float)($rowBtm['total_kain_masuk_kg'] ?? 0);

        $targetPcs = (float)$rowHeader['target_wo_pcs'];
        $yieldPersen = $benangKeluarKg > 0 ? round(($kainMasukKg / $benangKeluarKg) * 100, 2) : 0;
        $susutKg     = max(0, $benangKeluarKg - $kainMasukKg);
        $susutPersen = $benangKeluarKg > 0 ? round(($susutKg / $benangKeluarKg) * 100, 2) : 0;

        // Validasi Aturan Mutu CJK (Standar Toleransi Susut: 3.5% - 5%)
        $statusEfisiensi = 'Optimal';
        $anomaliNote = null;
        if ($benangKeluarKg > 0) {
            if ($susutPersen > 5.0) {
                $statusEfisiensi = 'Boros (Loss Melebihi Toleransi > 5%)';
                $anomaliNote = "Perhatian: Tingkat susut rajut tercatat {$susutPersen}%. Melebihi batas standar pabrik CJK (maks 5%).";
            } elseif ($yieldPersen < 90.0 && $benangKeluarKg >= 50) {
                $statusEfisiensi = 'In progress / Potensi Susut';
                $anomaliNote = "Hasil kain masuk baru {$yieldPersen}% dari benang yang dikeluarkan.";
            }
        }

        return [
            'status'                   => true,
            'no_wo'                    => $rowHeader['no_wo'],
            'tanggal'                  => $rowHeader['tanggal'],
            'customer'                 => $rowHeader['nama_customer'] ?? 'Customer CJK',
            'no_so'                    => $rowHeader['no_so'] ?? '-',
            'style'                    => $rowHeader['item_style'],
            'deskripsi'                => $rowHeader['deskripsi_kain'],
            'target_produksi_pcs'      => $targetPcs,
            'realisasi_kain_btm_kg'    => $kainMasukKg,
            'benang_terpakai_btk_kg'   => $benangKeluarKg,
            'susut_loss_kg'            => round($susutKg, 2),
            'susut_persen'             => $susutPersen . '%',
            'yield_efisiensi_rajut'    => $yieldPersen . '%',
            'status_efisiensi'         => $statusEfisiensi,
            'catatan_anomali'          => $anomaliNote,
            'total_biaya_benang_rp'    => $biayaBenangRp,
            'status_wo'                => $rowHeader['status'] == '1' ? 'Selesai' : 'Sedang Berjalan',
            'keterangan'               => $rowHeader['keterangan']
        ];
    }

    /**
     * 🎯 3B. End-to-End Tracking Order & Produksi CJK
     * Melacak status Sales Order (SOD), SPK Rajut (WRD), Produksi (PRD), Surat Jalan (DEV), Kesiapan Bahan Benang & Keuangan
     */
    public function getTrackingOrderProduksi(array $params = []): array
    {
        $q = trim($params['query'] ?? ($params['no_order'] ?? ($params['no_wo'] ?? '')));

        $builder = $this->db->table("v_traking_order_so tbl");
        $builder->select("tbl.*, rk.id_walkorder");
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");

        if (!empty($q)) {
            $builder->groupStart();
                $builder->where('LOWER(tbl.trans_kode) LIKE', '%' . strtolower($q) . '%');
                $builder->orWhere('LOWER(tbl.kode_prod) LIKE', '%' . strtolower($q) . '%');
                $builder->orWhere('LOWER(tbl.kode_dev) LIKE', '%' . strtolower($q) . '%');
                $builder->orWhere('LOWER(tbl.nama) LIKE', '%' . strtolower($q) . '%');
                $builder->orWhere('LOWER(tbl.style) LIKE', '%' . strtolower($q) . '%');
                $builder->orWhere('LOWER(tbl.deskripsi) LIKE', '%' . strtolower($q) . '%');
            $builder->groupEnd();
        }
        $builder->orderBy("tbl.tgl_transaksi DESC, tbl.trans_id DESC");
        $order = $builder->get(1)->getRowArray();

        // Fallback: Jika tidak ada di v_traking_order_so, cari langsung ke trans_walkorder
        if (!$order && !empty($q)) {
            $wo = $this->db->table("trans_walkorder tw")
                ->select("tw.*, rk.nama as nama_customer, tso.kode_sales_order, tso.total_harga, tso.uang_dp, tso.uang_dp_2")
                ->join("ref_konsumen rk", "tw.id_konsumen = rk.id", "left")
                ->join("trans_sales_order tso", "tw.ref_id = tso.id AND tw.tipe_id = 2", "left")
                ->groupStart()
                    ->where('LOWER(tw.kode_walkorder) LIKE', '%' . strtolower($q) . '%')
                    ->orWhere('LOWER(tw.ref_kode) LIKE', '%' . strtolower($q) . '%')
                    ->orWhere('LOWER(tw.keterangan_style) LIKE', '%' . strtolower($q) . '%')
                ->groupEnd()
                ->orderBy("tw.id DESC")
                ->get(1)->getRowArray();

            if ($wo) {
                $order = [
                    'trans_id'           => $wo['ref_id'],
                    'trans_kode'         => $wo['ref_kode'] ?: $wo['kode_walkorder'],
                    'tgl_transaksi'      => $wo['tgl_transaksi'],
                    'nama'               => $wo['nama_customer'],
                    'style'              => $wo['keterangan_style'],
                    'deskripsi'          => $wo['keterangan'],
                    'qty'                => $wo['qty'],
                    'tgl_deadline'       => $wo['tgl_deadline'],
                    'harga_total'        => $wo['total_harga'] ?? 0,
                    'uang_dp'            => $wo['uang_dp'] ?? 0,
                    'uang_dp_2'          => $wo['uang_dp_2'] ?? 0,
                    'nilai_pembayaran'   => 0,
                    'id_walkorder'       => $wo['id'],
                    'kode_prod'          => '',
                    'qty_prod'           => 0,
                    'kode_dev'           => '',
                    'qty_kirim'          => 0
                ];
            }
        }

        if (!$order) {
            return [
                'status'  => false,
                'message' => "Order / SPK Produksi dengan kata kunci '{$q}' tidak ditemukan di sistem CJK."
            ];
        }

        $transKode = $order['trans_kode'];

        // 1. Data Walk Order (SPK Rajut)
        $woData = $this->db->query("
            SELECT tw.*, rk.nama as nama_konsumen 
            FROM trans_walkorder tw
            LEFT JOIN ref_konsumen rk ON tw.id_konsumen = rk.id
            WHERE tw.ref_kode = ? OR tw.kode_walkorder = ?
            ORDER BY tw.id DESC LIMIT 1
        ", [$transKode, $transKode])->getRowArray();

        // 2. Breakdown Matrix Warna & Ukuran dari v_all_transaksi
        $items = $this->db->query("
            SELECT color, kode_ukuran, SUM(qty) as qty, amount
            FROM v_all_transaksi 
            WHERE kode_transaksi = ?
            GROUP BY color, kode_ukuran, amount
            ORDER BY color ASC, kode_ukuran ASC
        ", [$transKode])->getResultArray();

        $byColor = [];
        foreach ($items as $it) {
            $cName = $it['color'] ?: 'Warna Standar';
            if (!isset($byColor[$cName])) {
                $byColor[$cName] = ['total_qty' => 0, 'sizes' => []];
            }
            $byColor[$cName]['total_qty'] += (int)$it['qty'];
            $byColor[$cName]['sizes'][] = $it['kode_ukuran'] . ": " . (int)$it['qty'] . " pcs";
        }

        // 3. Realisasi Bahan Baku Benang (BTK & BTM)
        $benangKeluarKg = 0;
        $kainMasukKg = 0;
        if ($woData) {
            $idWo = $woData['id'];
            $kodeWo = $woData['kode_walkorder'];
            $rowBtk = $this->db->query("
                SELECT SUM(COALESCE(tbd.qty, 0)) as total_benang_keluar_kg
                FROM trans_barang_header tbh
                JOIN trans_barang_detail tbd ON tbd.id_header = tbh.id
                WHERE (tbh.id_wo = ? OR tbh.no_ref_wo = ?) AND tbh.jenis_transaksi = 2 AND tbh.status = 1 AND tbh.active = 1
            ", [$idWo, $kodeWo])->getRowArray();
            $benangKeluarKg = (float)($rowBtk['total_benang_keluar_kg'] ?? 0);

            $rowBtm = $this->db->query("
                SELECT SUM(COALESCE(tbd.qty, 0)) as total_kain_masuk_kg
                FROM trans_barang_header tbh
                JOIN trans_barang_detail tbd ON tbd.id_header = tbh.id
                WHERE (tbh.id_wo = ? OR tbh.no_ref_wo = ?) AND tbh.jenis_transaksi = 1 AND tbh.status = 1 AND tbh.active = 1
            ", [$idWo, $kodeWo])->getRowArray();
            $kainMasukKg = (float)($rowBtm['total_kain_masuk_kg'] ?? 0);
        }

        // 4. Cek Ketersediaan Stok Fisik Benang di Seluruh Gudang CJK
        $stokBahan = [];
        foreach (array_keys($byColor) as $colorName) {
            if ($colorName != 'Warna Standar') {
                $stokRow = $this->db->query("
                    SELECT SUM(tl.qty) as total_stok_kg
                    FROM trans_lots tl
                    JOIN ref_barang rb ON tl.id_barang = rb.id
                    WHERE LOWER(rb.nama_barang) LIKE ? AND tl.qty > 0
                ", ['%' . strtolower($colorName) . '%'])->getRowArray();
                $stokBahan[$colorName] = round((float)($stokRow['total_stok_kg'] ?? 0), 2);
            }
        }

        // 5. Hitung Countdown Deadline
        $today = new \DateTime();
        $deadline = !empty($order['tgl_deadline']) ? new \DateTime($order['tgl_deadline']) : null;
        $sisaHari = $deadline ? (int)$today->diff($deadline)->format("%r%a") : null;

        // 6. Hitung Posisi Keuangan & Tagihan
        $hargaTotal = (float)($order['harga_total'] ?? 0);
        $totalDp = (float)($order['uang_dp'] ?? 0) + (float)($order['uang_dp_2'] ?? 0) + (float)($order['nilai_pembayaran'] ?? 0);
        $sisaBayar = max(0, $hargaTotal - $totalDp);

        $qtyTarget = (int)$order['qty'];
        $qtyProd = (int)($order['qty_prod'] ?? 0);
        $qtyKirim = (int)($order['qty_kirim'] ?? 0);

        return [
            'status'                 => true,
            'trans_kode'             => $order['trans_kode'],
            'tgl_transaksi'          => $order['tgl_transaksi'],
            'customer'               => $order['nama'] ?: 'Customer CJK',
            'style'                  => $order['style'] ?: '-',
            'deskripsi'              => $order['deskripsi'] ?: '-',
            'qty_target'             => $qtyTarget,
            'harga_total_rp'         => $hargaTotal,
            'dp_terbayar_rp'         => $totalDp,
            'sisa_tagihan_rp'        => $sisaBayar,
            'deadline'               => $order['tgl_deadline'] ?: '-',
            'sisa_hari_deadline'     => $sisaHari,
            'spk_walkorder'          => [
                'kode_wo'              => $woData['kode_walkorder'] ?? '-',
                'status_wo'            => isset($woData['status']) && $woData['status'] == '1' ? 'Selesai' : 'Aktif / Berjalan',
                'benang_keluar_btk_kg' => $benangKeluarKg,
                'kain_masuk_btm_kg'    => $kainMasukKg
            ],
            'produksi'               => [
                'kode_prod'      => $order['kode_prod'] ?: '-',
                'qty_selesai'    => $qtyProd,
                'persen_selesai' => $qtyTarget > 0 ? round(($qtyProd / $qtyTarget) * 100, 1) . '%' : '0%'
            ],
            'pengiriman'             => [
                'kode_dev'     => $order['kode_dev'] ?: '-',
                'qty_kirim'    => $qtyKirim,
                'persen_kirim' => $qtyTarget > 0 ? round(($qtyKirim / $qtyTarget) * 100, 1) . '%' : '0%'
            ],
            'breakdown_warna'        => $byColor,
            'stok_ketersediaan_benang'=> $stokBahan
        ];
    }

    /**
     * 💰 4. Analisa Keuangan & Laba Rugi (SINKRON DENGAN VALIDASI DASHBOARD CJK)
     */
    public function getLabaRugi(array $params = []): array
    {
        $bulan = !empty($params['bulan']) ? (int)$params['bulan'] : (int)date('m');
        $tahun = !empty($params['tahun']) ? (int)$params['tahun'] : (int)date('Y');

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // 1. Data Penjualan Resmi Dashboard: Total Uang Muka (DP) + Pelunasan Penerimaan Konsumen (PCR)
        $totalDp = (float) $this->mdashboard->getDataDP($bulan, $tahun);
        $totalPcr = (float) $this->mdashboard->getDataPCR($bulan, $tahun);
        $totalPenjualan = $totalDp + $totalPcr;

        // 2. Biaya Bahan Baku Terpakai Resmi Dashboard (Approved BTK)
        $totalBebanBahan = (float) $this->mdashboard->getDataPemakaian($bulan, $tahun);

        // 3. Biaya Operasional Resmi Dashboard (COA Akun Biaya 5000+)
        $biayaList = $this->mdashboard->getDataBiaya($bulan, $tahun);
        $totalBebanOperasional = 0;
        $topBeban = [];
        $codeProcessed = [];
        foreach ($biayaList as $v) {
            if (!in_array($v->kode, $codeProcessed)) {
                $codeProcessed[] = $v->kode;
                if (isset($v->bln1) && (float)$v->bln1 > 0) {
                    $val = (float)$v->bln1;
                    $totalBebanOperasional += $val;
                    $topBeban[] = [
                        'kode' => $v->kode,
                        'nama' => $v->nama,
                        'total' => $val
                    ];
                }
            }
        }

        // Urutkan pos beban terbesar untuk diagnostik Antigravity
        usort($topBeban, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Kalkulasi Laba Kotor & Bersih identik dengan App\Controllers\Dashboard::lists_laba()
        $labaKotor = $totalPenjualan - $totalBebanBahan;
        $labaBersih = $labaKotor - $totalBebanOperasional;
        $marginKotorPersen  = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 2) : 0;
        $marginBersihPersen = $totalPenjualan > 0 ? round(($labaBersih / $totalPenjualan) * 100, 2) : 0;

        return [
            'status'                        => true,
            'periode'                       => ($namaBulan[$bulan] ?? $bulan) . " " . $tahun,
            'total_pendapatan_penjualan_rp' => $totalPenjualan,
            'rincian_penjualan'             => [
                'dp_pesanan_rp'            => $totalDp,
                'pelunasan_konsumen_pcr_rp' => $totalPcr
            ],
            'biaya_bahan_baku_btk_rp'       => $totalBebanBahan,
            'laba_kotor_rp'                 => $labaKotor,
            'margin_laba_kotor'             => $marginKotorPersen . '%',
            'biaya_operasional_rp'          => $totalBebanOperasional,
            'estimasi_laba_bersih_rp'       => $labaBersih,
            'margin_laba_bersih'            => $marginBersihPersen . '%',
            'top_beban_operasional'         => array_slice($topBeban, 0, 3)
        ];
    }

    /**
     * 👥 5. Analisa Target Harian & Produktivitas Operator SDM (Tabel sdm_target_harian)
     */
    public function getTargetHarianSDM(array $params = []): array
    {
        $tanggalReq = !empty($params['tanggal']) ? $params['tanggal'] : date('Y-m-d');

        // Cari header target harian untuk tanggal yang diminta
        $header = $this->db->query("
            SELECT sth.*, ms.nama_shift 
            FROM sdm_target_harian sth
            LEFT JOIN m_shift ms ON sth.id_shift = ms.id
            WHERE sth.tanggal = ? AND sth.active = 1
            ORDER BY sth.id DESC LIMIT 1
        ", [$tanggalReq])->getRowArray();

        $catatanTanggal = null;
        // Jika belum ada data untuk tanggal hari ini, ambil record target harian terakhir yang tersedia
        if (!$header) {
            $header = $this->db->query("
                SELECT sth.*, ms.nama_shift 
                FROM sdm_target_harian sth
                LEFT JOIN m_shift ms ON sth.id_shift = ms.id
                WHERE sth.active = 1
                ORDER BY sth.tanggal DESC, sth.id DESC LIMIT 1
            ")->getRowArray();

            if ($header) {
                $catatanTanggal = "Target harian untuk hari ini (" . date('d-m-Y', strtotime($tanggalReq)) . ") belum diinput tim operasional. Menampilkan data rekap target terakhir terdata (" . date('d-m-Y', strtotime($header['tanggal'])) . ").";
            }
        }

        if (!$header) {
            return [
                'status'  => false,
                'message' => 'Belum ada data target harian SDM yang terinput di sistem.'
            ];
        }

        $idH = $header['id'];
        $sqlDet = "
            SELECT 
                COALESCE(rk.full_name, sthd.posisi, 'Operator') as nama_pegawai,
                sthd.posisi,
                sthd.target_qty,
                sthd.total_realisasi as actual_qty,
                sthd.satuan,
                sthd.persentase
            FROM sdm_target_harian_det sthd
            LEFT JOIN ref_karyawan rk ON sthd.id_karyawan = rk.id
            WHERE sthd.id_header = ? AND sthd.active = 1
            ORDER BY sthd.id ASC
        ";

        $rows = $this->db->query($sqlDet, [$idH])->getResultArray();

        $totalTarget = 0;
        $totalActual = 0;
        $operatorPerformances = [];

        foreach ($rows as $r) {
            $t = (float)$r['target_qty'];
            $a = (float)$r['actual_qty'];
            $totalTarget += $t;
            $totalActual += $a;
            $pRate = (float)$r['persentase'];
            if ($pRate == 0 && $t > 0) {
                $pRate = round(($a / $t) * 100, 1);
            }

            $operatorPerformances[] = [
                'nama'       => $r['nama_pegawai'],
                'posisi'     => $r['posisi'],
                'target_pcs' => $t,
                'aktual_pcs' => $a,
                'satuan'     => $r['satuan'] ?: 'Pcs',
                'capaian'    => $pRate . '%'
            ];
        }

        $achievementRate = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 2) : 0;

        return [
            'status'                  => true,
            'tanggal'                 => $header['tanggal'],
            'catatan_tanggal'         => $catatanTanggal,
            'kode_transaksi'          => $header['kode_transaksi'],
            'divisi'                  => $header['divisi'] ?: 'Produksi',
            'shift'                   => $header['nama_shift'] ?: ('Shift ' . $header['id_shift']),
            'total_operator_tercatat' => count($rows),
            'total_target_pcs'        => $totalTarget,
            'total_aktual_pcs'        => $totalActual,
            'persentase_pencapaian'   => $achievementRate . '%',
            'detail_operator'         => $operatorPerformances
        ];
    }

    /**
     * ⚠️ 6. Cek Transaksi Menunggu Approval (Pending Approvals)
     */
    public function getPendingApprovals(array $params = []): array
    {
        $pending = [];

        // 1. Barang Masuk / Keluar Draft (trans_barang_header status = 0)
        $sqlBarang = "
            SELECT 
                CASE WHEN tbh.jenis_transaksi = 2 THEN 'Barang Keluar (BTK)' ELSE 'Barang Masuk (BTM)' END as jenis,
                tbh.kode_transaksi,
                tbh.tanggal,
                COALESCE(tbh.no_ref_wo, '-') as ref_dokumen,
                COALESCE(rg.nama_gudang, 'Gudang CJK') as gudang,
                COALESCE(tbh.keterangan, '-') as keterangan
            FROM trans_barang_header tbh
            LEFT JOIN ref_gudang rg ON tbh.id_gudang = rg.id
            WHERE tbh.status = 0 AND tbh.active = 1
            ORDER BY tbh.id DESC LIMIT 15
        ";
        $pending = array_merge($pending, $this->db->query($sqlBarang)->getResultArray());

        // 2. Transfer Draft (trans_barang_trf_header status = 0)
        $sqlTrf = "
            SELECT 
                'Item Transfer' as jenis,
                tbth.kode_transaksi,
                tbth.tanggal,
                '-' as ref_dokumen,
                CONCAT(COALESCE(ga.nama_gudang, 'Gudang Asal'), ' ➔ ', COALESCE(gt.nama_gudang, 'Gudang Tujuan')) as gudang,
                COALESCE(tbth.keterangan, '-') as keterangan
            FROM trans_barang_trf_header tbth
            LEFT JOIN ref_gudang ga ON tbth.id_gudang_asal = ga.id
            LEFT JOIN ref_gudang gt ON tbth.id_gudang_tujuan = gt.id
            WHERE tbth.status = 0 AND tbth.active = 1
            ORDER BY tbth.id DESC LIMIT 15
        ";
        $pending = array_merge($pending, $this->db->query($sqlTrf)->getResultArray());

        return [
            'status'        => true,
            'total_pending' => count($pending),
            'data'          => $pending
        ];
    }
}
