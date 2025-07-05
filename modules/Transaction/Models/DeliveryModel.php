<?php

namespace Modules\Transaction\Models;

class DeliveryModel extends \App\Models\PrModel
{

    protected $table = "trans_delivery";
    protected $table2 = "trans_delivery_detail";
    protected $table3 = "trans_delivery_prod";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.tgl_transaksi,  abx.delivery_kode, abx.id_produksi, abx.produksi_kode, abx.id_konsumen, abx.alamat,
                            abx.status, abx.qty as qty_delv, abx.tipe_id,  abx.id_walkorder,
                            bbx.nama as konsumen_nama, tp.keterangan_style,
                            tp.qty, twx.ref_kode as kode_so");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inn er");
        $builder->join("trans_produksi tp", "tp.id = abx.id_produksi", "inner");
        $builder->join("trans_walkorder twx", "tp.id_walkorder = twx.id", "inner");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(abx.delivery_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(abx.wo_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
            }

            if (!empty($params['id_produksi'])) {
                $builder->where('abx.id_produksi', $params['id_produksi']);
            }

            if (!empty($params['id_walkorder'])) {
                $builder->where('abx.id_walkorder', $params['id_walkorder']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");
        
        $builder->select("count(1) as _cnt");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(abx.delivery_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.wo_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getAlldeliveryQty($params){
        $builder = $this->db->table($this->table2 . ' td');

        $builder->select("sum(td.qty) as jml");
        $builder->where("td.ref_detail_id", $params['ref_detail_id']);
        $builder->where("td.id_ukuran", $params['id_ukuran']);

        $dt = $builder->get()->getRow();
        $this->_data = !empty($dt) ? $dt->jml : 0;
        return $this->_data;
    }

    function getDataProduksi($params){
        $builder = $this->db->table("trans_delivery_detail tdd");
        $builder->select("tdd.id_ukuran, tdd.qty, tdd.ref_detail_id, tdd.id_delivery,
                          rk.kode_ukuran, rw.kode_warna");
        $builder->join('ref_ukuran rk', 'tdd.id_ukuran = rk.id');
        $builder->join('trans_delivery td', 'tdd.id_delivery = td.id');
        $builder->join('trans_walkorder tw', 'td.id_walkorder = tw.id');
        $builder->join("trans_sample_det tsd", "tdd.ref_detail_id = tsd.id and tw.tipe_id = 1", "left");
        $builder->join("trans_sales_order_det tsod", "tdd.ref_detail_id = tsod.id and tw.tipe_id = 2", "left");
        $builder->join("ref_warna rw", "rw.id = (case when tw.tipe_id = 1 then tsd.id_warna_1 when tw.tipe_id = 2 then tsod.id_warna_1 else -1 end)", "left");

        if(!empty($params['id_delivery'])){
            $builder->where('id_delivery', $params['id_delivery']);
        }

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
}