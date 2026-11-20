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

        // 1. STOK FISIK LIVE:
        if ($cleanLot !== '-') {
            // A. UNTUK BARANG BER-LOT:
            // trans_lots adalah Single Source of Truth saldo lot fisik aktual per gudang.
            $builderLot = $this->db->table('trans_lots')
                ->select('COALESCE(SUM(qty), 0) as total_qty')
                ->where('id_barang', (int)$idBarang)
                ->where('id_gudang', (int)$idGudang)
                ->where('TRIM(COALESCE(lot_no, \'\')) =', $cleanLot)
                ->where('active', 1);

            $lotAllRow = (clone $builderLot)->get()->getRow();
            $stokLotAll = $lotAllRow ? round((float)$lotAllRow->total_qty, 2) : 0.0;

            if (!$isDummyOrNull) {
                $lotPackRow = (clone $builderLot)->where('pack_id', $packIdInt)->get()->getRow();
                $stokLotPack = $lotPackRow ? round((float)$lotPackRow->total_qty, 2) : 0.0;
                $stokFisik = ($stokLotPack > 0) ? $stokLotPack : $stokLotAll;
            } else {
                $stokFisik = $stokLotAll;
            }

            // Fallback: jika di trans_lots 0 (belum sync), cek mutasi trans_barang
            if ($stokFisik <= 0) {
                $paramsFisik = [
                    'id_barang' => (int)$idBarang,
                    'id_gudang' => (int)$idGudang,
                    'lot_no'    => $cleanLot,
                ];
                $excludeTrxCond = "";
                if (!empty($excludeTrxKode)) {
                    $paramsFisik['exclude_trx_kode'] = trim((string)$excludeTrxKode);
                    $excludeTrxCond = "AND (abx.kode_transaksi != :exclude_trx_kode: OR abx.kode_transaksi IS NULL)";
                }

                $sqlFisikTb = "
                    SELECT
                        COALESCE(SUM(CASE WHEN abx.id_gudang_tujuan = :id_gudang: THEN abx.jumlah ELSE 0 END), 0) -
                        COALESCE(SUM(CASE WHEN abx.id_gudang_asal = :id_gudang: THEN abx.jumlah ELSE 0 END), 0) AS stok_fisik
                    FROM trans_barang abx
                    WHERE COALESCE(abx.active, 1) = 1
                      AND abx.id_barang = :id_barang:
                      AND TRIM(COALESCE(abx.lot_no, '')) = :lot_no:
                      $excludeTrxCond
                ";
                $rowFisikTb = $this->db->query($sqlFisikTb, $paramsFisik)->getRow();
                $stokFisikTb = $rowFisikTb ? round((float)$rowFisikTb->stok_fisik, 2) : 0.0;
                if ($stokFisikTb > 0) {
                    $stokFisik = $stokFisikTb;
                }
            }
        } else {
            // B. UNTUK BARANG NON-LOT:
            $paramsFisik = [
                'id_barang' => (int)$idBarang,
                'id_gudang' => (int)$idGudang,
            ];
            $excludeTrxCond = "";
            if (!empty($excludeTrxKode)) {
                $paramsFisik['exclude_trx_kode'] = trim((string)$excludeTrxKode);
                $excludeTrxCond = "AND (abx.kode_transaksi != :exclude_trx_kode: OR abx.kode_transaksi IS NULL)";
            }

            $sqlFisikNonLot = "
                SELECT
                    COALESCE(SUM(CASE WHEN abx.id_gudang_tujuan = :id_gudang: THEN abx.jumlah ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN abx.id_gudang_asal = :id_gudang: THEN abx.jumlah ELSE 0 END), 0) AS stok_fisik
                FROM trans_barang abx
                WHERE COALESCE(abx.active, 1) = 1
                  AND abx.id_barang = :id_barang:
                  AND (COALESCE(NULLIF(TRIM(abx.lot_no), ''), '-') = '-')
                  $excludeTrxCond
            ";
            $rowFisikNonLot = $this->db->query($sqlFisikNonLot, $paramsFisik)->getRow();
            $stokFisikTb = $rowFisikNonLot ? round((float)$rowFisikNonLot->stok_fisik, 2) : 0.0;

            $rowLotNonLot = $this->db->table('trans_lots')
                ->select('COALESCE(SUM(qty), 0) as total_qty')
                ->where('id_barang', (int)$idBarang)
                ->where('id_gudang', (int)$idGudang)
                ->where('(lot_no = \'-\' OR lot_no = \'\' OR lot_no IS NULL)')
                ->where('active', 1)
                ->get()->getRow();
            $stokLotNonLot = $rowLotNonLot ? round((float)$rowLotNonLot->total_qty, 2) : 0.0;

            $stokFisik = max(0.0, max($stokFisikTb, $stokLotNonLot));
        }

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
        $stokTersedia = round(max(0.0, $stokFisik - $stokBooking), 2);

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