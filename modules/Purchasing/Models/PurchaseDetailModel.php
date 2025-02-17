<?php

namespace Modules\Purchasing\Models;

class PurchaseDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_po_detail";
    protected $tblBarang = "ref_barang";
    protected $tblSatuan = "ref_satuan";


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
        $builder->select("uk.id, uk.kode,uk.id_header,uk.qty,uk.qty_receive, uk.id_barang,uk.disc_price,uk.tax_price,fbx.nama_satuan as nama_unit, ebx.kode_barang, ebx.nama_barang,uk.tax,uk.disc,uk.price,uk.grand_price, (uk.grand_price/uk.qty) as price_2");

        if (!empty($params['id_header'])) {

            $builder->where('uk.id_header', $params['id_header']);
        }
        // if (!empty($params['isReceive']) && $params['isReceive']) {
        //     $builder->groupStart();
        //     // $builder->where("qty_receive < qty");
        //     // $builder->orWhere("qty_receive IS NULL");
        //     $builder->groupEnd();
        // }
        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(ebx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
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
        $builder->join($this->tblBarang . " ebx", "uk.id_barang = ebx.id", "inner");
        $builder->join($this->tblSatuan . " fbx", "ebx.id_satuan = fbx.id", "inner");
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(ebx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getUnitPrice($vendor = null, $barang = null)
    {
        // dd(decrypt($vendor), decrypt($barang));
        $builder = $this->db->table($this->table . " uk");
        $builder->join("trans_po_header hd", "CAST(uk.id_header AS INTEGER) = hd.id", "inner");
        $builder->select("uk.id, uk.id_header, uk.id_barang, uk.disc_price, uk.tax_price, uk.tax, uk.disc, uk.price, uk.grand_price, (uk.grand_price/uk.qty) as price_2, hd.id_vendor");
        $this->_data = $builder->where("id_vendor", decrypt($vendor))->where("id_barang", decrypt($barang))->orderBy('id', 'DESC')->limit(1)->get()->getRow();

        return $this->_data;
    }
}
