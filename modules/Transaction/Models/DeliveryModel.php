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
                            tp.qty, twx.ref_kode as kode_so, tso.deskripsi, abx.id_invoice,
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
                    $builder->orWhere('LOWER(twx.ref_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
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

        if (!empty($params['except_id'])) {
            $builder->where('abx.id <>', $params['except_id']);
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

    function getDataProduksi($params = null, $id = null){
        $builder = $this->db->table("trans_delivery_detail tdd");
        $builder->select("
            tdd.id, tdd.id_ukuran, tdd.qty, tdd.ref_detail_id, tdd.id_delivery,
            rk.kode_ukuran, rk.key_ukuran,

            -- Flag sumber warna
            CASE
                WHEN tw.tipe_id = 1 AND tsd.id_barang_1  IS NOT NULL THEN 'via_barang'
                WHEN tw.tipe_id = 2 AND tsod.id_barang_1 IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna,

            TRIM(BOTH ' - ' FROM
                COALESCE(rw1.kode_warna, '') ||
                CASE WHEN rw2.kode_warna IS NOT NULL THEN ' - ' || rw2.kode_warna ELSE '' END ||
                CASE WHEN rw3.kode_warna IS NOT NULL THEN ' - ' || rw3.kode_warna ELSE '' END ||
                CASE WHEN rw4.kode_warna IS NOT NULL THEN ' - ' || rw4.kode_warna ELSE '' END ||
                CASE WHEN rw5.kode_warna IS NOT NULL THEN ' - ' || rw5.kode_warna ELSE '' END ||
                CASE WHEN rw6.kode_warna IS NOT NULL THEN ' - ' || rw6.kode_warna ELSE '' END ||
                CASE WHEN rw7.kode_warna IS NOT NULL THEN ' - ' || rw7.kode_warna ELSE '' END ||
                CASE WHEN rw8.kode_warna IS NOT NULL THEN ' - ' || rw8.kode_warna ELSE '' END
            ) AS kode_warna,

            TRIM(BOTH ' ~ ' FROM
                COALESCE(rw1.keterangan, '') ||
                CASE WHEN rw2.keterangan IS NOT NULL THEN ' ~ ' || rw2.keterangan ELSE '' END ||
                CASE WHEN rw3.keterangan IS NOT NULL THEN ' ~ ' || rw3.keterangan ELSE '' END ||
                CASE WHEN rw4.keterangan IS NOT NULL THEN ' ~ ' || rw4.keterangan ELSE '' END ||
                CASE WHEN rw5.keterangan IS NOT NULL THEN ' ~ ' || rw5.keterangan ELSE '' END ||
                CASE WHEN rw6.keterangan IS NOT NULL THEN ' ~ ' || rw6.keterangan ELSE '' END ||
                CASE WHEN rw7.keterangan IS NOT NULL THEN ' ~ ' || rw7.keterangan ELSE '' END ||
                CASE WHEN rw8.keterangan IS NOT NULL THEN ' ~ ' || rw8.keterangan ELSE '' END
            ) AS keterangan
        ");

        $builder->join('ref_ukuran rk',       'tdd.id_ukuran = rk.id');
        $builder->join('trans_delivery td',   'tdd.id_delivery = td.id');
        $builder->join('trans_walkorder tw',  'td.id_walkorder = tw.id');

        // JOIN trans_sample_det (tipe_id = 1)
        $builder->join("trans_sample_det tsd",       "tdd.ref_detail_id = tsd.id AND tw.tipe_id = 1",  "left");

        // JOIN ref_barang untuk sample
        $builder->join("ref_barang bs1", "bs1.id = tsd.id_barang_1", "left");
        $builder->join("ref_barang bs2", "bs2.id = tsd.id_barang_2", "left");
        $builder->join("ref_barang bs3", "bs3.id = tsd.id_barang_3", "left");
        $builder->join("ref_barang bs4", "bs4.id = tsd.id_barang_4", "left");
        $builder->join("ref_barang bs5", "bs5.id = tsd.id_barang_5", "left");
        $builder->join("ref_barang bs6", "bs6.id = tsd.id_barang_6", "left");
        $builder->join("ref_barang bs7", "bs7.id = tsd.id_barang_7", "left");
        $builder->join("ref_barang bs8", "bs8.id = tsd.id_barang_8", "left");

        // JOIN trans_sales_order_det (tipe_id = 2)
        $builder->join("trans_sales_order_det tsod",  "tdd.ref_detail_id = tsod.id AND tw.tipe_id = 2", "left");

        // JOIN ref_barang untuk SO
        $builder->join("ref_barang bso1", "bso1.id = tsod.id_barang_1", "left");
        $builder->join("ref_barang bso2", "bso2.id = tsod.id_barang_2", "left");
        $builder->join("ref_barang bso3", "bso3.id = tsod.id_barang_3", "left");
        $builder->join("ref_barang bso4", "bso4.id = tsod.id_barang_4", "left");
        $builder->join("ref_barang bso5", "bso5.id = tsod.id_barang_5", "left");
        $builder->join("ref_barang bso6", "bso6.id = tsod.id_barang_6", "left");
        $builder->join("ref_barang bso7", "bso7.id = tsod.id_barang_7", "left");
        $builder->join("ref_barang bso8", "bso8.id = tsod.id_barang_8", "left");

        // JOIN ref_warna: COALESCE dari ref_barang masing-masing tipe, fallback ke id_warna di det
        $builder->join("ref_warna rw1", "rw1.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs1.id_warna  WHEN tw.tipe_id = 2 THEN bso1.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_1  WHEN tw.tipe_id = 2 THEN tsod.id_warna_1  ELSE NULL END)", "left");
        $builder->join("ref_warna rw2", "rw2.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs2.id_warna  WHEN tw.tipe_id = 2 THEN bso2.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_2  WHEN tw.tipe_id = 2 THEN tsod.id_warna_2  ELSE NULL END)", "left");
        $builder->join("ref_warna rw3", "rw3.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs3.id_warna  WHEN tw.tipe_id = 2 THEN bso3.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_3  WHEN tw.tipe_id = 2 THEN tsod.id_warna_3  ELSE NULL END)", "left");
        $builder->join("ref_warna rw4", "rw4.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs4.id_warna  WHEN tw.tipe_id = 2 THEN bso4.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_4  WHEN tw.tipe_id = 2 THEN tsod.id_warna_4  ELSE NULL END)", "left");
        $builder->join("ref_warna rw5", "rw5.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs5.id_warna  WHEN tw.tipe_id = 2 THEN bso5.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_5  WHEN tw.tipe_id = 2 THEN tsod.id_warna_5  ELSE NULL END)", "left");
        $builder->join("ref_warna rw6", "rw6.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs6.id_warna  WHEN tw.tipe_id = 2 THEN bso6.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_6  WHEN tw.tipe_id = 2 THEN tsod.id_warna_6  ELSE NULL END)", "left");
        $builder->join("ref_warna rw7", "rw7.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs7.id_warna  WHEN tw.tipe_id = 2 THEN bso7.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_7  WHEN tw.tipe_id = 2 THEN tsod.id_warna_7  ELSE NULL END)", "left");
        $builder->join("ref_warna rw8", "rw8.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs8.id_warna  WHEN tw.tipe_id = 2 THEN bso8.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_8  WHEN tw.tipe_id = 2 THEN tsod.id_warna_8  ELSE NULL END)", "left");

        if (!empty($params['id_delivery'])) {
            $builder->where('id_delivery', $params['id_delivery']);
            $builder->orderBy('rk.seq asc');
            $this->_data = $builder->get()->getResult();
        } else if (!empty($id)) {
            $builder->where('tdd.id', $id);
            $builder->orderBy('rk.seq asc');
            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function get_export($from_date = null, $to_date = null, $buyer = null)
    {
        $builder = $this->db->table($this->table2 . " tdd");

        $builder->select("
            tdd.id, td.delivery_kode, td.tgl_transaksi, tso.kode_sales_order, rk.nama, tso.style,

            -- Flag sumber warna
            CASE
                WHEN tsod.id_barang_1 IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna,

            TRIM(BOTH ' - ' FROM
                COALESCE(rw1.kode_warna, '') ||
                CASE WHEN rw2.kode_warna IS NOT NULL THEN ' - ' || rw2.kode_warna ELSE '' END ||
                CASE WHEN rw3.kode_warna IS NOT NULL THEN ' - ' || rw3.kode_warna ELSE '' END ||
                CASE WHEN rw4.kode_warna IS NOT NULL THEN ' - ' || rw4.kode_warna ELSE '' END ||
                CASE WHEN rw5.kode_warna IS NOT NULL THEN ' - ' || rw5.kode_warna ELSE '' END ||
                CASE WHEN rw6.kode_warna IS NOT NULL THEN ' - ' || rw6.kode_warna ELSE '' END ||
                CASE WHEN rw7.kode_warna IS NOT NULL THEN ' - ' || rw7.kode_warna ELSE '' END ||
                CASE WHEN rw8.kode_warna IS NOT NULL THEN ' - ' || rw8.kode_warna ELSE '' END
            ) AS kode_warna,

            tso.id as id_so,
            tsod.id as id_so_det,
            ru.id as id_ukuran,
            ru.kode_ukuran,
            tsou.qty as qty_so,
            tdd.qty as qty_do
        ");

        $builder->join("trans_delivery td",              "td.id = tdd.id_delivery",                                                "inner");
        $builder->join("ref_konsumen rk",                "rk.id = td.id_konsumen",                                                 "inner");
        $builder->join("ref_ukuran ru",                  "ru.id = tdd.id_ukuran",                                                  "inner");
        $builder->join("trans_walkorder tw",             "tw.id = td.id_walkorder",                                                "inner");
        $builder->join("trans_sales_order_det tsod",     "tsod.id = tdd.ref_detail_id",                                            "inner");
        $builder->join("trans_sales_order tso",          "tso.id = tsod.id_sales_order",                                           "inner");
        $builder->join("trans_sales_order_ukuran tsou",  "tsou.id_sales_order_det = tdd.ref_detail_id AND tsou.id_ukuran = tdd.id_ukuran", "inner");

        // JOIN ref_barang untuk SO
        $builder->join("ref_barang bso1", "bso1.id = tsod.id_barang_1", "left");
        $builder->join("ref_barang bso2", "bso2.id = tsod.id_barang_2", "left");
        $builder->join("ref_barang bso3", "bso3.id = tsod.id_barang_3", "left");
        $builder->join("ref_barang bso4", "bso4.id = tsod.id_barang_4", "left");
        $builder->join("ref_barang bso5", "bso5.id = tsod.id_barang_5", "left");
        $builder->join("ref_barang bso6", "bso6.id = tsod.id_barang_6", "left");
        $builder->join("ref_barang bso7", "bso7.id = tsod.id_barang_7", "left");
        $builder->join("ref_barang bso8", "bso8.id = tsod.id_barang_8", "left");

        // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di tsod
        $builder->join("ref_warna rw1", "rw1.id = COALESCE(bso1.id_warna, tsod.id_warna_1)", "left");
        $builder->join("ref_warna rw2", "rw2.id = COALESCE(bso2.id_warna, tsod.id_warna_2)", "left");
        $builder->join("ref_warna rw3", "rw3.id = COALESCE(bso3.id_warna, tsod.id_warna_3)", "left");
        $builder->join("ref_warna rw4", "rw4.id = COALESCE(bso4.id_warna, tsod.id_warna_4)", "left");
        $builder->join("ref_warna rw5", "rw5.id = COALESCE(bso5.id_warna, tsod.id_warna_5)", "left");
        $builder->join("ref_warna rw6", "rw6.id = COALESCE(bso6.id_warna, tsod.id_warna_6)", "left");
        $builder->join("ref_warna rw7", "rw7.id = COALESCE(bso7.id_warna, tsod.id_warna_7)", "left");
        $builder->join("ref_warna rw8", "rw8.id = COALESCE(bso8.id_warna, tsod.id_warna_8)", "left");

        $builder->where('tw.tipe_id = 2');
        $builder->where("td.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");

        if (!empty($buyer)) {
            $builder->where("td.id_konsumen", $buyer);
        }

        $builder->orderBy("td.tgl_transaksi", 'desc');

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getTotalQtyBefore($id_produksi, $id, $id_ukuran, $ref_id) {
        $builder = $this->db->table($this->table3 . " tdp");
        $builder->select("coalesce(sum(tdp.qty_do), 0) as total_qty_do");
        $builder->join("trans_delivery td", "td.id = tdp.id_delivery", "inner");
        $builder->where('td.active = 1');
        $builder->where('td.id_produksi', $id_produksi);
        $builder->where('tdp.id_ukuran', $id_ukuran);
        $builder->where('tdp.ref_detail_id', $ref_id);
        $builder->where('td.id <', $id);

        $this->_data = $builder->get()->getRow()->total_qty_do;
        return $this->_data;
    }
}