<?php 
namespace App\Models;

use CodeIgniter\Model;

class Mcommon extends Model
{
    protected $_data = null;

    public function __construct()
    {
        parent::__construct();
    }

    function checkMenuAccess($role_id, $menu_alias)
    {
        if (empty($menu_alias)) {
            return false;
        }

        $cache = \Config\Services::cache();
        $cacheKey = 'chk_menu_' . $role_id . '_' . md5((string)$menu_alias);
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $isAllow = false;
        $builder = $this->db->table('sec_role_priv a');
        $data = $builder->select('a.allow_view')
                ->join('sec_modul b', 'a.module_id = b.id')
                ->where('a.role_id', $role_id)
                ->where('b.alias', $menu_alias)
                ->get()->getRow();
        if($data){
            $isAllow = (bool)$data->allow_view;
        }
        $cache->save($cacheKey, $isAllow, 3600);
        return $isAllow;
    }

    function getMenuAccessCRUD($role_id, $menu_alias)
    {
        if (empty($menu_alias)) {
            return null;
        }

        $cache = \Config\Services::cache();
        $cacheKey = 'crud_menu_' . $role_id . '_' . md5((string)$menu_alias);
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $builder = $this->db->table('sec_role_priv a');
        $res = $builder->select('a.allow_view, a.allow_new, a.allow_edit, a.allow_delete, a.allow_print, a.allow_approve')
                ->join('sec_modul b', 'a.module_id = b.id')
                ->where('a.role_id', $role_id)
                ->where('b.alias', $menu_alias)
                ->get()->getRow();

        $cache->save($cacheKey, $res, 3600);
        return $res;
    }

    function getMenuByRoleID($roleid)
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'role_menu_' . $roleid;
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $builder = $this->db->table('sec_role_priv a');
        $builder->select('a.module_id, b.name as module_name, b.alias as module_alias, COALESCE(b.pid,0) AS module_pid, b.url as module_url, b.icon_cls as mod_icon_cls, b.seq as mod_seq, b.group as mod_group');
        $builder->join('sec_modul b', 'a.module_id = b.id');
        $builder->where(['b.publish' => 1, 'a.allow_view' => 1, 'a.role_id' => $roleid]);
        $builder->orderBy('b.pid, b.seq, a.module_id');
        $result = $builder->get()->getResultArray();

        $cache->save($cacheKey, $result, 3600);
        return $result;
    }

    function setLog($user_id, $module_alias, $trans_id, $activity, $description = "")
    {
		$server = \Config\Services::request()->getServer();
        $ip_address = $server['REMOTE_ADDR'];
        $http_agent = $server['HTTP_USER_AGENT'];
        $http_host = $server['HTTP_HOST'];
        $comp_name = gethostbyaddr($ip_address);

        $mac = "";

        $sql = "INSERT INTO log_activity(log_date,ip_address,comp_name,user_id,module_alias,
                trans_id,activity,description,http_agent,http_host,mac_address) 
                VALUES(NOW(),?,?,?,?,?,?,?,?,?,?);";

        $this->db->transStart();
        $this->db->query($sql, array($ip_address, $comp_name, $user_id,
            $module_alias, $trans_id, $activity, $description, $http_agent, $http_host, $mac));
        $this->db->transComplete();
    }

    function clearMenuCache($role_id = null)
    {
        $cache = \Config\Services::cache();
        if ($role_id !== null) {
            $cache->delete('role_menu_' . $role_id);
            $cache->delete('user_menu_html_' . $role_id);
        } else {
            // Clear for all roles
            $roles = $this->db->table('sec_role')->select('id')->get()->getResultArray();
            foreach ($roles as $r) {
                $cache->delete('role_menu_' . $r['id']);
                $cache->delete('user_menu_html_' . $r['id']);
            }
        }
    }

    /**
     * Single Source of Truth: Menghitung Saldo Stok Fisik, Stok Ter-booking, dan Stok Tersedia.
     * Mengunci Skenario A: spesifik per id_barang, id_gudang, lot_no, dan pack_id.
     *
     * @param int $idBarang
     * @param int $idGudang
     * @param string|null $lotNo
     * @param int|null $packId
     * @param int|null $excludeTrfHeaderId (Opsional: ID header transfer jika sedang mengedit dokumen draft sendiri)
     * @param string|null $excludeTrxKode (Opsional: kode_transaksi dokumen jika sedang mengedit dokumen approved sendiri)
     * @param int|null $excludeBtkHeaderId (Opsional: ID header BTK jika sedang mengedit dokumen draft sendiri)
     * @return object { stok_fisik, stok_booking, stok_tersedia }
     */
    public function getLiveStockWithBooking($idBarang, $idGudang, $lotNo = null, $packId = null, $excludeTrfHeaderId = null, $excludeTrxKode = null, $excludeBtkHeaderId = null)
    {
        $cleanLot = trim((string)($lotNo ?? ''));
        if ($cleanLot === '' || $cleanLot === '-') {
            $cleanLot = '-';
        }
        $packIdInt = !empty($packId) ? (int)$packId : null;
        $dummyPackId = $this->getDefaultPackId();
        $isDummyOrNull = (empty($packIdInt) || (!empty($dummyPackId) && $packIdInt === $dummyPackId));

        $stokFisik = 0.0;

        // 1. STOK FISIK LIVE (SINGLE SOURCE OF TRUTH: LAPORAN PERSEDIAAN / KARTU STOK):
        // Mengambil saldo akhir aktual dari trans_barang_history (periode berjalan sejak cutoff Juni 2026)
        $currentYM = (int)date('Ym');
        $paramsHist = [
            'id_barang'  => (int)$idBarang,
            'id_gudang'  => (int)$idGudang,
            'current_ym' => $currentYM,
        ];

        if ($cleanLot !== '-') {
            // A. UNTUK BARANG BER-LOT:
            $paramsHist['lot_no'] = $cleanLot;

            if (!$isDummyOrNull) {
                $paramsHist['pack_id'] = $packIdInt;
                $sqlHistPack = "
                    SELECT tbh.jumlah
                    FROM trans_barang_history tbh
                    WHERE tbh.id_gudang = :id_gudang:
                      AND tbh.id_barang = :id_barang:
                      AND COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-') = :lot_no:
                      AND tbh.pack_id = :pack_id:
                      AND (tbh.year * 100 + tbh.month) >= 202606
                      AND (tbh.year * 100 + tbh.month) <= :current_ym:
                    ORDER BY (tbh.year * 100 + tbh.month) DESC, tbh.id DESC
                    LIMIT 1
                ";
                $rowPack = $this->db->query($sqlHistPack, $paramsHist)->getRow();
                $stokPackHist = $rowPack ? (float)$rowPack->jumlah : 0.0;

                // Total lot lintas pack (jika per pack minus karena anomali pack masa lalu, jangan izinkan melebihi total lot yang riil tersedia)
                $sqlHistLotTotal = "
                    SELECT SUM(sub_h.jumlah) as total_qty
                    FROM (
                        SELECT DISTINCT ON (tbh.id_barang, tbh.id_gudang, tbh.pack_id, COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-'))
                            tbh.jumlah
                        FROM trans_barang_history tbh
                        WHERE tbh.id_gudang = :id_gudang:
                          AND tbh.id_barang = :id_barang:
                          AND COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-') = :lot_no:
                          AND (tbh.year * 100 + tbh.month) >= 202606
                          AND (tbh.year * 100 + tbh.month) <= :current_ym:
                        ORDER BY tbh.id_barang, tbh.id_gudang, tbh.pack_id, COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-'), (tbh.year * 100 + tbh.month) DESC, tbh.id DESC
                    ) sub_h
                ";
                $rowLotTotal = $this->db->query($sqlHistLotTotal, $paramsHist)->getRow();
                $stokLotTotal = $rowLotTotal ? (float)$rowLotTotal->total_qty : 0.0;

                if ($rowPack !== null) {
                    $stokFisik = (float)$rowPack->jumlah;
                } else {
                    $stokFisik = $rowLotTotal ? (float)$rowLotTotal->total_qty : 0.0;
                }
            } else {
                // Tanpa filter pack spesifik (total saldo lot fisik di gudang tersebut)
                $sqlHistLotTotal = "
                    SELECT SUM(sub_h.jumlah) as total_qty
                    FROM (
                        SELECT DISTINCT ON (tbh.id_barang, tbh.id_gudang, tbh.pack_id, COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-'))
                            tbh.jumlah
                        FROM trans_barang_history tbh
                        WHERE tbh.id_gudang = :id_gudang:
                          AND tbh.id_barang = :id_barang:
                          AND COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-') = :lot_no:
                          AND (tbh.year * 100 + tbh.month) >= 202606
                          AND (tbh.year * 100 + tbh.month) <= :current_ym:
                        ORDER BY tbh.id_barang, tbh.id_gudang, tbh.pack_id, COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-'), (tbh.year * 100 + tbh.month) DESC, tbh.id DESC
                    ) sub_h
                ";
                $rowLotTotal = $this->db->query($sqlHistLotTotal, $paramsHist)->getRow();
                $stokFisik = $rowLotTotal ? (float)$rowLotTotal->total_qty : 0.0;
            }
        } else {
            // B. UNTUK BARANG NON-LOT:
            $sqlHistNonLot = "
                SELECT SUM(sub_h.jumlah) as total_qty
                FROM (
                    SELECT DISTINCT ON (tbh.id_barang, tbh.id_gudang, tbh.pack_id)
                        tbh.jumlah
                    FROM trans_barang_history tbh
                    WHERE tbh.id_gudang = :id_gudang:
                      AND tbh.id_barang = :id_barang:
                      AND (COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-') = '-')
                      AND (tbh.year * 100 + tbh.month) >= 202606
                      AND (tbh.year * 100 + tbh.month) <= :current_ym:
                    ORDER BY tbh.id_barang, tbh.id_gudang, tbh.pack_id, (tbh.year * 100 + tbh.month) DESC, tbh.id DESC
                ) sub_h
            ";
            $rowNonLot = $this->db->query($sqlHistNonLot, $paramsHist)->getRow();
            $stokFisik = $rowNonLot ? (float)$rowNonLot->total_qty : 0.0;
        }

        $stokFisik = round($stokFisik, 2);

        // 2. STOK TER-BOOKING (DRAFT AKTIF):
        // Hanya menghitung dokumen draft AKTIF (rentang 30 hari terakhir) yang belum dihapus (deleted_at IS NULL).
        // Draft kadaluarsa / zombie (> 30 hari) diabaikan agar tidak mengunci inventori gudang.
        $draftActiveDate = date('Y-m-d', strtotime('-30 days'));

        // a. Dokumen Transfer Draft (status = 0) dari gudang asal ini
        $excludeTrfClause = "";
        $paramsBookingTrf = [
            'id_barang'         => (int)$idBarang,
            'id_gudang'         => (int)$idGudang,
            'draft_active_date' => $draftActiveDate,
        ];
        if (!empty($excludeTrfHeaderId)) {
            $paramsBookingTrf['exclude_trf_id'] = (int)$excludeTrfHeaderId;
            $excludeTrfClause = "AND th.id != :exclude_trf_id:";
        }
        $lotCondTd = ($cleanLot === '-') 
            ? "AND (COALESCE(NULLIF(TRIM(td.lot_no), ''), '-') = '-')" 
            : "AND TRIM(COALESCE(td.lot_no, '')) = :lot_no:";
        if ($cleanLot !== '-') {
            $paramsBookingTrf['lot_no'] = $cleanLot;
        }

        $sqlBookingTrf = "
            SELECT COALESCE(SUM(td.qty), 0) AS stok_booking
            FROM trans_barang_trf_detail td
            JOIN trans_barang_trf_header th ON th.id = td.id_header
            WHERE th.status = 0
              AND th.id_gudang_asal = :id_gudang:
              AND td.id_barang = :id_barang:
              AND (th.tanggal >= :draft_active_date: OR th.created_at >= :draft_active_date:)
              AND th.deleted_at IS NULL
              $excludeTrfClause
              $lotCondTd
        ";
        $rowBookingTrf = $this->db->query($sqlBookingTrf, $paramsBookingTrf)->getRow();
        $stokBookingTrf = $rowBookingTrf ? round((float)$rowBookingTrf->stok_booking, 2) : 0.0;

        // b. Dokumen Barang Keluar (BTK) Draft (status = 0) dari gudang ini
        $excludeBtkClause = "";
        $paramsBookingBtk = [
            'id_barang'         => (int)$idBarang,
            'id_gudang'         => (int)$idGudang,
            'draft_active_date' => $draftActiveDate,
        ];
        if (!empty($excludeBtkHeaderId)) {
            $paramsBookingBtk['exclude_btk_id'] = (int)$excludeBtkHeaderId;
            $excludeBtkClause = "AND tbh.id != :exclude_btk_id:";
        }
        $lotCondTbd = ($cleanLot === '-') 
            ? "AND (COALESCE(NULLIF(TRIM(tbd.lot_no), ''), '-') = '-')" 
            : "AND TRIM(COALESCE(tbd.lot_no, '')) = :lot_no:";
        if ($cleanLot !== '-') {
            $paramsBookingBtk['lot_no'] = $cleanLot;
        }

        $sqlBookingBtk = "
            SELECT COALESCE(SUM(tbd.qty), 0) AS stok_booking
            FROM trans_barang_detail tbd
            JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
            WHERE tbh.status = 0
              AND tbh.jenis_transaksi = 2
              AND tbh.id_gudang = :id_gudang:
              AND tbd.id_barang = :id_barang:
              AND (tbh.tanggal >= :draft_active_date: OR tbh.created_at >= :draft_active_date:)
              AND tbh.deleted_at IS NULL
              $excludeBtkClause
              $lotCondTbd
        ";
        $rowBookingBtk = $this->db->query($sqlBookingBtk, $paramsBookingBtk)->getRow();
        $stokBookingBtk = $rowBookingBtk ? round((float)$rowBookingBtk->stok_booking, 2) : 0.0;

        $stokBooking = round($stokBookingTrf + $stokBookingBtk, 2);

        // 3. STOK TERSEDIA (AVAILABLE): Stok Fisik dikurangi Booking
        $stokTersedia = round($stokFisik - $stokBooking, 2);

        return (object) [
            'stok_fisik'    => $stokFisik,
            'stok_booking'  => $stokBooking,
            'stok_tersedia' => $stokTersedia,
        ];
    }

    public function getDefaultPackId()
    {
        $row = $this->db->table('ref_pack')->select('id')->where('pack_name', '-')->get()->getRow();
        if (!empty($row)) {
            return (int)$row->id;
        }
        return null;
    }
}