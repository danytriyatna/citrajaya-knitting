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
                            tp.qty, twx.ref_kode as kode_so, tso.deskripsi,
    (
        tp.qty - COALESCE((
            SELECT SUM(abx2.qty) 
            FROM trans_delivery abx2 
            WHERE abx2.id_produksi = abx.id_produksi 
              AND abx2.id < abx.id
        ),0)
    ) as sisa_qty");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("trans_produksi tp", "tp.id = abx.id_produksi", "inner");
        $builder->join("trans_walkorder twx", "tp.id_walkorder = twx.id", "inner");
        $builder->join("trans_sales_order tso", "tso.id = twx.ref_id and twx.tipe_id = 2", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(abx.delivery_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
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
                $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetailProd($params = null)
    {
        $builder = $this->db->table($this->table3 . " abx");

        $builder->select(" abx.id, abx.id_delivery,  abx.ref_detail_id, abx.id_ukuran, abx.qty,
                            abx.qty_do, abx.harga_satuan, COALESCE(abx.qty, 0) * COALESCE(abx.harga_satuan, 0) AS total_harga");

        if (!empty($params['id_delivery'])) {
            $builder->where('abx.id_delivery', $params['id_delivery']);
        }
        
        if (!empty($params['ref_detail_id'])) {
            $builder->where('abx.ref_detail_id', $params['ref_detail_id']);
        }

        $this->_data = $builder->get()->getResult();

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
                          rk.kode_ukuran, rk.key_ukuran, TRIM ( BOTH ' - ' FROM
                        COALESCE(rw1.kode_warna, '') ||
                        CASE WHEN rw2.kode_warna IS NOT NULL THEN ' - ' || rw2.kode_warna ELSE '' END ||
                        CASE WHEN rw3.kode_warna IS NOT NULL THEN ' - ' || rw3.kode_warna ELSE '' END ||
                        CASE WHEN rw4.kode_warna IS NOT NULL THEN ' - ' || rw4.kode_warna ELSE '' END ||
                        CASE WHEN rw5.kode_warna IS NOT NULL THEN ' - ' || rw5.kode_warna ELSE '' END ||
                        CASE WHEN rw6.kode_warna IS NOT NULL THEN ' - ' || rw6.kode_warna ELSE '' END ||
                        CASE WHEN rw7.kode_warna IS NOT NULL THEN ' - ' || rw7.kode_warna ELSE '' END ||
                        CASE WHEN rw8.kode_warna IS NOT NULL THEN ' - ' || rw8.kode_warna ELSE '' END
                    ) AS kode_warna, 
                     TRIM ( BOTH ' ~ ' FROM
                        COALESCE(rw1.keterangan, '') ||
                        CASE WHEN rw2.keterangan IS NOT NULL THEN ' ~ ' || rw2.keterangan ELSE '' END ||
                        CASE WHEN rw3.keterangan IS NOT NULL THEN ' ~ ' || rw3.keterangan ELSE '' END ||
                        CASE WHEN rw4.keterangan IS NOT NULL THEN ' ~ ' || rw4.keterangan ELSE '' END ||
                        CASE WHEN rw5.keterangan IS NOT NULL THEN ' ~ ' || rw5.keterangan ELSE '' END ||
                        CASE WHEN rw6.keterangan IS NOT NULL THEN ' ~ ' || rw6.keterangan ELSE '' END ||
                        CASE WHEN rw7.keterangan IS NOT NULL THEN ' ~ ' || rw7.keterangan ELSE '' END ||
                        CASE WHEN rw8.keterangan IS NOT NULL THEN ' ~ ' || rw8.keterangan ELSE '' END
                    ) AS keterangan");
        $builder->join('ref_ukuran rk', 'tdd.id_ukuran = rk.id');
        $builder->join('trans_delivery td', 'tdd.id_delivery = td.id');
        $builder->join('trans_walkorder tw', 'td.id_walkorder = tw.id');
        $builder->join("trans_sample_det tsd", "tdd.ref_detail_id = tsd.id and tw.tipe_id = 1", "left");
        $builder->join("trans_sales_order_det tsod", "tdd.ref_detail_id = tsod.id and tw.tipe_id = 2", "left");
        $builder->join("ref_warna rw1", "rw1.id = (case when tw.tipe_id = 1 then tsd.id_warna_1 when tw.tipe_id = 2 then tsod.id_warna_1 else -1 end)", "left");
        $builder->join("ref_warna rw2", "rw2.id = (case when tw.tipe_id = 1 then tsd.id_warna_2 when tw.tipe_id = 2 then tsod.id_warna_2 else -1 end)", "left");
        $builder->join("ref_warna rw3", "rw3.id = (case when tw.tipe_id = 1 then tsd.id_warna_3 when tw.tipe_id = 2 then tsod.id_warna_3 else -1 end)", "left");
        $builder->join("ref_warna rw4", "rw4.id = (case when tw.tipe_id = 1 then tsd.id_warna_4 when tw.tipe_id = 2 then tsod.id_warna_4 else -1 end)", "left");
        $builder->join("ref_warna rw5", "rw5.id = (case when tw.tipe_id = 1 then tsd.id_warna_5 when tw.tipe_id = 2 then tsod.id_warna_5 else -1 end)", "left");
        $builder->join("ref_warna rw6", "rw6.id = (case when tw.tipe_id = 1 then tsd.id_warna_6 when tw.tipe_id = 2 then tsod.id_warna_6 else -1 end)", "left");
        $builder->join("ref_warna rw7", "rw7.id = (case when tw.tipe_id = 1 then tsd.id_warna_7 when tw.tipe_id = 2 then tsod.id_warna_7 else -1 end)", "left");
        $builder->join("ref_warna rw8", "rw8.id = (case when tw.tipe_id = 1 then tsd.id_warna_8 when tw.tipe_id = 2 then tsod.id_warna_8 else -1 end)", "left");

        if(!empty($params['id_delivery'])){
            $builder->where('id_delivery', $params['id_delivery']);
        }
        $builder->orderBy('rk.seq asc');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
}