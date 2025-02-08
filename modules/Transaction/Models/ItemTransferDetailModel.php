<?php

namespace Modules\Transaction\Models;

class ItemTransferDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_trf_detail";
    protected $tblDetSO = "trans_barang_trf_so";
    protected $tblSO = "trans_sales_order";
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
        $builder->join($this->tblTrxLots . " gbx", "uk.lot_no = gbx.lot_no", "left");
        $builder->select("uk.id,gbx.id as lot_id,uk.id_header,uk.qty,uk.lot_no, uk.id_barang,fbx.nama_satuan as nama_unit, ebx.kode_barang, ebx.nama_barang, uk.price, uk.keterangan");

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

    function getDataDetailSO($idHeader = null)
    {
        $builder = $this->db->table($this->tblDetSO . " abx");

        $builder->select("bbx.id,abx.id_so, bbx.kode_sales_order");
        $builder->join($this->tblSO . " bbx", "abx.id_so = bbx.id", "left");

        $builder->where("abx.id_header", $idHeader);
        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }
}
