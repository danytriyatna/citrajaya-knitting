<?php

namespace Modules\Purchasing\Models;

class ReceiveItemDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_receive_detail";
    protected $tblBarang = "ref_barang";
    protected $tblSatuan = "ref_satuan";
    protected $tblGudang = "ref_gudang";


    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_receive_detail uk");
        $builder->join("ref_barang ebx", "uk.id_barang = ebx.id", "inner");
        $builder->join("ref_satuan fbx", "ebx.id_satuan = fbx.id", "inner");
        $builder->join("ref_gudang gbx", "uk.id_gudang = gbx.id", "inner");
        $builder->join("ref_pack rp", "uk.pack_id = rp.id", "left");
        $builder->select("uk.id, uk.id_gudang, gbx.nama_gudang, uk.id_header,uk.qty,uk.lot_no, uk.id_barang,fbx.nama_satuan as nama_unit,
        ebx.kode_barang, ebx.nama_barang, uk.price, rp.pack_name, uk.pack_id");

        if (!empty($params['id_header'])) {

            $builder->where('uk.id_header', $params['id_header']);
        }
        if ($params['isReceive']) {
            $builder->groupStart();
            $builder->where("qty_receive < qty");
            $builder->orWhere("qty_receive IS NULL");
            $builder->groupEnd();
        }

        $builder->where('uk.active = 1');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->select("count(1) as _cnt");
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.po_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataPrint($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $id_header = $params['id_header'] ?? null;

        $whereHeader = "";
        if (!empty($id_header)) {
            $whereHeader = "AND uk.id_header = ?";
        }

        $whereReceive = "";
        if (!empty($params['isReceive'])) {
            $whereReceive = "AND (uk.qty_receive < uk.qty OR uk.qty_receive IS NULL)";
        }

        $sql = "
            SELECT
                sub.id_barang,
                sub.id_header,
                sub.id_gudang,
                sub.nama_gudang,
                sub.lot_no,
                sub.price,
                sub.kode_warna,
                sub.nama_barang,
                STRING_AGG(sub.pack_qty, '|' ORDER BY sub.urutan) AS pack_data,
                SUM(sub.qty) AS qty,
                SUM(sub.qty * sub.price) AS jumlah
            FROM (
                SELECT
                    uk.id AS urutan,
                    uk.id_barang,
                    uk.id_header,
                    uk.id_gudang,
                    gbx.nama_gudang,
                    uk.lot_no,
                    uk.price,
                    uk.qty,
                    rw.keterangan as kode_warna,
                    ebx.nama_barang,
                    CONCAT(COALESCE(rp.pack_name, '-'), ':', uk.qty::text) AS pack_qty
                FROM trans_receive_detail uk
                INNER JOIN ref_barang ebx ON uk.id_barang = ebx.id
                LEFT JOIN ref_warna rw ON ebx.id_warna = rw.id
                INNER JOIN ref_gudang gbx ON uk.id_gudang = gbx.id
                LEFT  JOIN ref_pack rp ON uk.pack_id = rp.id
                WHERE uk.active = 1
                {$whereHeader}
                {$whereReceive}
            ) sub
            GROUP BY
                sub.id_barang, sub.id_header, sub.id_gudang, sub.nama_gudang,
                sub.lot_no, sub.price, sub.kode_warna, sub.nama_barang
            ORDER BY sub.id_barang
        ";

        $binds = [];
        if (!empty($id_header)) {
            $binds[] = $id_header;
        }

        $this->_data = $this->db->query($sql, $binds)->getResult();
        return $this->_data;
    }
}
