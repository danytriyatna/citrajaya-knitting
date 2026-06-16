<?php

namespace Modules\Transaction\Models;

class BarangKeluarDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_detail";
    protected $tblBarang = "ref_barang";
    protected $tblSatuan = "ref_satuan";
    protected $tblGudang = "ref_gudang";
    protected $tblTrxLots = "trans_lots";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblBarang . " ebx", "uk.id_barang = ebx.id", "inner");
        $builder->join($this->tblSatuan . " fbx", "ebx.id_satuan = fbx.id", "inner");
        $builder->join("ref_pack rp", "uk.pack_id = rp.id", "left");
        // $builder->join($this->tblTrxLots . " gbx", "uk.lot_no = gbx.lot_no AND gbx.id_gudang = $params[id_gudang] ", "inner");

        // $builder->select("uk.id,uk.id_header,uk.qty,uk.lot_no,gbx.id as lot_id, uk.id_barang,fbx.nama_satuan as nama_unit, ebx.kode_barang, ebx.nama_barang, uk.price");
        $builder->select("uk.id,uk.id_header,uk.qty,uk.lot_no, uk.lot_id,uk.id_barang,fbx.nama_satuan as nama_unit, rp.pack_name, uk.pack_id, ebx.kode_barang, ebx.nama_barang, uk.price");

        if (!empty($params['id_header'])) {
            $builder->where('uk.id_header', $params['id_header']);
        }
        if ($params['isReceive']) {
            $builder->groupStart();
            $builder->where("qty_receive < qty");
            $builder->orWhere("qty_receive IS NULL");
            $builder->groupEnd();
        }
        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.rec_no) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('id');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

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
        $id_header = $params['id_header'];

        $sql = "
            SELECT 
                sub.id_barang,
                sub.id_header,
                sub.lot_no,
                sub.kode_barang,
                sub.nama_barang,
                sub.nama_unit,
                STRING_AGG(sub.pack_qty, '|' ORDER BY sub.urutan) AS pack_data,
                SUM(sub.qty) AS qty,
                SUM(sub.qty * sub.price) AS jumlah
            FROM (
                SELECT 
                    uk.id AS urutan,
                    uk.id_barang,
                    uk.id_header,
                    uk.lot_no,
                    uk.price,
                    uk.qty,
                    ebx.kode_barang,
                    ebx.nama_barang,
                    fbx.nama_satuan AS nama_unit,
                    CONCAT(COALESCE(rp.pack_name, '-'), ':', uk.qty::text) AS pack_qty
                FROM {$this->table} uk
                INNER JOIN {$this->tblBarang} ebx ON uk.id_barang = ebx.id
                INNER JOIN {$this->tblSatuan} fbx ON ebx.id_satuan = fbx.id
                LEFT  JOIN ref_pack rp ON uk.pack_id = rp.id
                WHERE uk.id_header = ?
                AND uk.active = 1
            ) sub
            GROUP BY 
                sub.id_barang, sub.id_header, sub.lot_no,
                sub.kode_barang, sub.nama_barang, sub.nama_unit
            ORDER BY sub.id_barang
            LIMIT ? OFFSET ?
        ";

        $offset = empty($offset) ? 0 : $offset;
        $limit  = empty($limit)  ? 10 : $limit;

        $this->_data = $this->db->query($sql, [$id_header, $limit, $offset])->getResult();
        return $this->_data;
    }
}
