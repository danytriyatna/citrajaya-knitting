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
        $cutoffDate = '2026-06-01';
        $cleanLot = trim((string)($lotNo ?? ''));
        if ($cleanLot === '' || $cleanLot === '-') {
            $cleanLot = '-';
        }
        $packIdInt = !empty($packId) ? (int)$packId : null;
        $dummyPackId = $this->getDefaultPackId();
        $isDummyOrNull = (empty($packIdInt) || (!empty($dummyPackId) && $packIdInt === $dummyPackId));

        // 1. STOK FISIK LIVE: Dihitung dari buku besar mutasi trans_barang sejak cutoff 1 Juni 2026
        $lotCond = ($cleanLot === '-') ? "AND (COALESCE(NULLIF(TRIM(abx.lot_no), ''), '-') = '-')" : "AND TRIM(COALESCE(abx.lot_no, '')) = :lot_no:";
        
        $params = [
            'id_barang' => (int)$idBarang,
            'id_gudang' => (int)$idGudang,
        ];
        if ($cleanLot !== '-') {
            $params['lot_no'] = $cleanLot;
        }

        if ($isDummyOrNull) {
            if (!empty($dummyPackId)) {
                $packCond = "AND (abx.pack_id = :pack_id: OR abx.pack_id IS NULL OR abx.pack_id = 0)";
                $params['pack_id'] = $dummyPackId;
            } else {
                $packCond = "AND (abx.pack_id IS NULL OR abx.pack_id = 0)";
            }
        } else {
            $packCond = "AND abx.pack_id = :pack_id:";
            $params['pack_id'] = $packIdInt;
        }

        $excludeTrxCond = "";
        if (!empty($excludeTrxKode)) {
            $params['exclude_trx_kode'] = trim((string)$excludeTrxKode);
            $excludeTrxCond = "AND (abx.kode_transaksi != :exclude_trx_kode: OR abx.kode_transaksi IS NULL)";
        }

        $sqlFisik = "
            SELECT
                COALESCE(SUM(CASE WHEN abx.id_gudang_tujuan = :id_gudang: THEN abx.jumlah ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN abx.id_gudang_asal = :id_gudang: THEN abx.jumlah ELSE 0 END), 0) AS stok_fisik
            FROM trans_barang abx
            WHERE COALESCE(abx.active, 1) = 1
              AND abx.id_barang = :id_barang:
              AND abx.tanggal >= '$cutoffDate'
              $lotCond
              $packCond
              $excludeTrxCond
        ";
        $rowFisik = $this->db->query($sqlFisik, $params)->getRow();
        $stokFisik = $rowFisik ? round((float)$rowFisik->stok_fisik, 2) : 0.0;

        // 2. STOK TER-BOOKING (DRAFT):
        // a. Dokumen Transfer yang masih Draft (status = 0) dari gudang asal ini
        $excludeTrfClause = "";
        $paramsBookingTrf = [
            'id_barang' => (int)$idBarang,
            'id_gudang' => (int)$idGudang,
        ];
        if (!empty($excludeTrfHeaderId)) {
            $paramsBookingTrf['exclude_trf_id'] = (int)$excludeTrfHeaderId;
            $excludeTrfClause = "AND th.id != :exclude_trf_id:";
        }
        $lotCondTd = ($cleanLot === '-') ? "AND (COALESCE(NULLIF(TRIM(td.lot_no), ''), '-') = '-')" : "AND TRIM(COALESCE(td.lot_no, '')) = :lot_no:";
        if ($isDummyOrNull) {
            if (!empty($dummyPackId)) {
                $packCondTd = "AND (td.pack_id = :pack_id: OR td.pack_id IS NULL OR td.pack_id = 0)";
                $paramsBookingTrf['pack_id'] = $dummyPackId;
            } else {
                $packCondTd = "AND (td.pack_id IS NULL OR td.pack_id = 0)";
            }
        } else {
            $packCondTd = "AND td.pack_id = :pack_id:";
            $paramsBookingTrf['pack_id'] = $packIdInt;
        }
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
              $excludeTrfClause
              $lotCondTd
              $packCondTd
        ";
        $rowBookingTrf = $this->db->query($sqlBookingTrf, $paramsBookingTrf)->getRow();
        $stokBookingTrf = $rowBookingTrf ? round((float)$rowBookingTrf->stok_booking, 2) : 0.0;

        // b. Dokumen Barang Keluar (BTK) yang masih Draft (status = 0) dari gudang ini
        $excludeBtkClause = "";
        $paramsBookingBtk = [
            'id_barang' => (int)$idBarang,
            'id_gudang' => (int)$idGudang,
        ];
        if (!empty($excludeBtkHeaderId)) {
            $paramsBookingBtk['exclude_btk_id'] = (int)$excludeBtkHeaderId;
            $excludeBtkClause = "AND tbh.id != :exclude_btk_id:";
        }
        $lotCondTbd = ($cleanLot === '-') ? "AND (COALESCE(NULLIF(TRIM(tbd.lot_no), ''), '-') = '-')" : "AND TRIM(COALESCE(tbd.lot_no, '')) = :lot_no:";
        if ($isDummyOrNull) {
            if (!empty($dummyPackId)) {
                $packCondTbd = "AND (tbd.pack_id = :pack_id: OR tbd.pack_id IS NULL OR tbd.pack_id = 0)";
                $paramsBookingBtk['pack_id'] = $dummyPackId;
            } else {
                $packCondTbd = "AND (tbd.pack_id IS NULL OR tbd.pack_id = 0)";
            }
        } else {
            $packCondTbd = "AND tbd.pack_id = :pack_id:";
            $paramsBookingBtk['pack_id'] = $packIdInt;
        }
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
              $excludeBtkClause
              $lotCondTbd
              $packCondTbd
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