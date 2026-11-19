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
     * @return object { stok_fisik, stok_booking, stok_tersedia }
     */
    public function getLiveStockWithBooking($idBarang, $idGudang, $lotNo = null, $packId = null, $excludeTrfHeaderId = null)
    {
        $cutoffDate = '2026-06-01';
        $cleanLot = trim((string)($lotNo ?? ''));
        if ($cleanLot === '' || $cleanLot === '-') {
            $cleanLot = '-';
        }
        $packIdInt = !empty($packId) ? (int)$packId : null;

        // 1. STOK FISIK LIVE: Dihitung dari buku besar mutasi trans_barang sejak cutoff 1 Juni 2026
        $lotCond = ($cleanLot === '-') ? "AND (COALESCE(NULLIF(TRIM(abx.lot_no), ''), '-') = '-')" : "AND TRIM(COALESCE(abx.lot_no, '')) = :lot_no:";
        $packCond = !empty($packIdInt) ? "AND abx.pack_id = :pack_id:" : "AND (abx.pack_id IS NULL OR abx.pack_id = 0)";

        $params = [
            'id_barang' => (int)$idBarang,
            'id_gudang' => (int)$idGudang,
        ];
        if ($cleanLot !== '-') {
            $params['lot_no'] = $cleanLot;
        }
        if (!empty($packIdInt)) {
            $params['pack_id'] = $packIdInt;
        }

        $sqlFisik = "
            WITH raw_trans AS (
                SELECT abx.*,
                       ROW_NUMBER() OVER (
                           PARTITION BY abx.kode_transaksi, abx.id_barang,
                               COALESCE(abx.lot_no, ''), COALESCE(abx.pack_id, 0),
                               abx.id_gudang_asal, abx.id_gudang_tujuan, abx.jumlah
                           ORDER BY abx.id DESC
                       ) AS rn
                FROM trans_barang abx
                WHERE COALESCE(abx.active, 1) = 1
                  AND abx.id_barang = :id_barang:
                  AND abx.tanggal >= '$cutoffDate'
                  $lotCond
                  $packCond
            )
            SELECT
                COALESCE((SELECT SUM(jumlah) FROM raw_trans WHERE rn = 1 AND id_gudang_tujuan = :id_gudang:), 0) -
                COALESCE((SELECT SUM(jumlah) FROM raw_trans WHERE rn = 1 AND id_gudang_asal = :id_gudang:), 0) AS stok_fisik
        ";
        $rowFisik = $this->db->query($sqlFisik, $params)->getRow();
        $stokFisik = $rowFisik ? round((float)$rowFisik->stok_fisik, 2) : 0.0;

        // 2. STOK TER-BOOKING (DRAFT): Dokumen Transfer yang masih Draft (status = 0) dari gudang asal ini
        $excludeClause = "";
        $paramsBooking = [
            'id_barang' => (int)$idBarang,
            'id_gudang' => (int)$idGudang,
        ];
        if (!empty($excludeTrfHeaderId)) {
            $paramsBooking['exclude_id'] = (int)$excludeTrfHeaderId;
            $excludeClause = "AND th.id != :exclude_id:";
        }
        $lotCondTd = ($cleanLot === '-') ? "AND (COALESCE(NULLIF(TRIM(td.lot_no), ''), '-') = '-')" : "AND TRIM(COALESCE(td.lot_no, '')) = :lot_no:";
        $packCondTd = !empty($packIdInt) ? "AND td.pack_id = :pack_id:" : "AND (td.pack_id IS NULL OR td.pack_id = 0)";

        if ($cleanLot !== '-') {
            $paramsBooking['lot_no'] = $cleanLot;
        }
        if (!empty($packIdInt)) {
            $paramsBooking['pack_id'] = $packIdInt;
        }

        $sqlBooking = "
            SELECT COALESCE(SUM(td.qty), 0) AS stok_booking
            FROM trans_barang_trf_detail td
            JOIN trans_barang_trf_header th ON th.id = td.id_header
            WHERE th.status = 0
              AND th.id_gudang_asal = :id_gudang:
              AND td.id_barang = :id_barang:
              $excludeClause
              $lotCondTd
              $packCondTd
        ";
        $rowBooking = $this->db->query($sqlBooking, $paramsBooking)->getRow();
        $stokBooking = $rowBooking ? round((float)$rowBooking->stok_booking, 2) : 0.0;

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