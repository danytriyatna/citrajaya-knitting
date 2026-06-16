<?php

namespace Modules\Transaction\Models;

class BarangMasukDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_detail";
    protected $tblBarang = "ref_barang";
    protected $tblSatuan = "ref_satuan";
    protected $tblGudang = "ref_gudang";
    protected $tblDetailSO = "trans_barang_trf_so_det";

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
        $builder->join("ref_pack pbx", "uk.pack_id = pbx.id", "left");

        $builder->select("uk.id,uk.id_header,uk.qty,uk.lot_no, uk.id_barang,fbx.nama_satuan as nama_unit, ebx.kode_barang, ebx.nama_barang, uk.price, pbx.pack_name, uk.pack_id");

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


    function getDataDetSO($idHeader = null)
    {
        $builder = $this->db->table($this->tblDetailSO . " abx");

        $builder->select("abx.qty,abx.kode_sales_order,abx.id_konsumen,abx.style,abx.kode_sales_order,abx.deskripsi,abx.color,abx.amount,cbx.nama as buyer");
        $builder->join("ref_konsumen cbx", "abx.id_konsumen = cbx.id", "inner");

        $builder->where("abx.id_header", $idHeader);
        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }

    function getDataPrint($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $id_header = $params['id_header'];

        $whereReceive = "";
        if (!empty($params['isReceive'])) {
            $whereReceive = "AND (uk.qty_receive < uk.qty OR uk.qty_receive IS NULL)";
        }

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
                {$whereReceive}
            ) sub
            GROUP BY 
                sub.id_barang, sub.id_header, sub.lot_no,
                sub.kode_barang, sub.nama_barang, sub.nama_unit
            ORDER BY sub.id_barang
        ";

        $this->_data = $this->db->query($sql, [$id_header])->getResult();
        return $this->_data;
    }
}
