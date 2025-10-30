<?php

namespace Modules\Transaction\Models;

class SalesOrderModel extends \App\Models\PrModel
{

    protected $table = "trans_sales_order";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.kode_sales_order, abx.deskripsi, bbx.nama, bbx.alamat, abx.id_konsumen, abx.keterangan, abx.tgl_transaksi, abx.tgl_deadline, abx.tgl_deadline_dua, abx.status, 
                          abx.gambar_id,cbx.file_name, abx.id_sample, abx.uang_dp, abx.style as stylex , abx.style_cnt,
                          concat(abx.style,' - ', abx.style_cnt) as style, concat(abx.style, '  (', abx.style_cnt, ')') as style_print, abx.tgl_dp, abx.type_dp");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("_files cbx", "abx.gambar_id = cbx.id", "left");
        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id DESC');
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

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->select("count(1) as _cnt");
        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(abx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if(!empty($params['style'])){
            $builder->where("abx.style", $params['style']);
        }

        if(!empty($params['id_konsumen'])){
            $builder->where("abx.id_konsumen", $params['id_konsumen']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataUkuran($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null, $idProses = null)
    {
        $builder = $this->db->table("trans_sales_order_ukuran abx");

        $builder->select("abx.id,xb.id_walkorder_proses,bbx.id as id_ukuran, dbx.id as id_warna,  bbx.kode_ukuran,dbx.kode_warna, abx.qty, abx.harga_satuan");
        $builder->join("ref_ukuran bbx", "abx.id_ukuran = bbx.id", "inner");
        $builder->join("trans_sales_order_det cbx", "abx.id_sales_order_det = cbx.id AND abx.id_sales_order = cbx.id_sales_order ", "inner");
        $builder->join("ref_warna dbx", "cbx.id_warna_1 = dbx.id", "inner");
        $builder->join("trans_walkorder x", "x.ref_id = abx.id_sales_order AND x.tipe_id = 2", "left");
        $builder->join("trans_walkorder_proses xa", "x.id = xa.id_walkorder AND xa.id_proses = $idProses", "left");
        $builder->join("trans_walkorder_proses_ukuran xb", "xa.id = xb.id_walkorder_proses", "left");
        $builder->groupBy("abx.id");
        $builder->groupBy("dbx.kode_warna");
        $builder->groupBy("dbx.id");
        $builder->groupBy("bbx.kode_ukuran");
        $builder->groupBy("bbx.id");
        $builder->groupBy("abx.qty");
        $builder->groupBy("abx.harga_satuan");
        $builder->groupBy("xb.id_walkorder_proses");
        $builder->groupBy("abx.id");

        if ($id == null or $id == "") {
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(dbx.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.kode_ukuran) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_sales_order'])) {
                $builder->where('abx.id_sales_order', $params['id_sales_order']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id');
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

    function getDataUkuranCnt($filters = null, $params = null, $idProses = null)
    {
        $builder = $this->db->table("trans_sales_order_ukuran abx");

        $builder->join("ref_ukuran bbx", "abx.id_ukuran = bbx.id", "inner");
        $builder->join("trans_sales_order_det cbx", "abx.id_sales_order_det = cbx.id AND abx.id_sales_order = cbx.id_sales_order ", "inner");
        $builder->join("ref_warna dbx", "cbx.id_warna_1 = dbx.id", "inner");

        $builder->select("count(1) as _cnt");
        if (!empty($params['id_sales_order'])) {
            $builder->where('abx.id_sales_order', $params['id_sales_order']);
        }

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(dbx.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(bbx.kode_ukuran) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetailSalesOrder($idSalesOrder = null, $idSalesOrderDet = null)
    {
        $builder = $this->db->table("trans_sales_order_det" . " abx");
        $builder->select("abx.id,ROW_NUMBER
                            ( ) OVER ( ORDER BY abx.id ) AS No,
                            TRIM (
                                BOTH ' - ' 
                            FROM
                                COALESCE ( w1.kode_warna, '' ) ||
                            CASE
                                    
                                    WHEN w2.kode_warna IS NOT NULL THEN
                                    ' - ' || w2.kode_warna ELSE'' 
                                END ||
                        CASE
                            
                            WHEN w3.kode_warna IS NOT NULL THEN
                            ' - ' || w3.kode_warna ELSE'' 
                            END ||
                        CASE
                            
                            WHEN w4.kode_warna IS NOT NULL THEN
                            ' - ' || w4.kode_warna ELSE'' 
                            END ||
                        CASE
                            
                            WHEN w5.kode_warna IS NOT NULL THEN
                            ' - ' || w5.kode_warna ELSE'' 
                            END ||
                        CASE
                            
                            WHEN w6.kode_warna IS NOT NULL THEN
                            ' - ' || w6.kode_warna ELSE'' 
                            END ||
                        CASE
                            
                            WHEN w7.kode_warna IS NOT NULL THEN
                            ' - ' || w7.kode_warna ELSE'' 
                            END ||
                        CASE
                            
                            WHEN w8.kode_warna IS NOT NULL THEN
                            ' - ' || w8.kode_warna ELSE'' 
                        END 
                            ) AS colour,
                        COALESCE ( w1.kode_warna, '' ) as colorDasar");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'xs' THEN bbx.qty ELSE 0 END ) AS XS ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 's' THEN bbx.qty ELSE 0 END ) AS S ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'm' THEN bbx.qty ELSE 0 END ) AS M ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'l' THEN bbx.qty ELSE 0 END ) AS L ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'xl' THEN bbx.qty ELSE 0 END ) AS XL ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'xxl' THEN bbx.qty ELSE 0 END ) AS XXL ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'xxxl' THEN bbx.qty ELSE 0 END ) AS XXXL ");
        $builder->select("MAX ( CASE WHEN cbx.key_ukuran = 'all' THEN bbx.qty ELSE 0 END ) AS All ");
        $builder->select("SUM(bbx.harga_satuan) AS harga_satuan");
        $builder->join("trans_sales_order_ukuran bbx", "abx.id = bbx.id_sales_order_det AND abx.id_sales_order = bbx.id_sales_order", "inner");
        $builder->join("ref_ukuran cbx", "bbx.id_ukuran = cbx.id", "left");
        $builder->join("ref_warna w1", "abx.id_warna_1 = w1.id", "left");
        $builder->join("ref_warna w2", "abx.id_warna_2 = w2.id", "left");
        $builder->join("ref_warna w3", "abx.id_warna_3 = w3.id", "left");
        $builder->join("ref_warna w4", "abx.id_warna_4 = w4.id", "left");
        $builder->join("ref_warna w5", "abx.id_warna_5 = w5.id", "left");
        $builder->join("ref_warna w6", "abx.id_warna_6 = w6.id", "left");
        $builder->join("ref_warna w7", "abx.id_warna_7 = w7.id", "left");
        $builder->join("ref_warna w8", "abx.id_warna_8 = w8.id", "left");
        
        if(!empty($idSalesOrder)){
            $builder->where("abx.id_sales_order", $idSalesOrder);
        }
        
        if(!empty($idSalesOrderDet)){
            $builder->where("abx.id", $idSalesOrderDet);
        }
        $builder->groupBy(array("abx.id", "w1.kode_warna", "w2.kode_warna", "w3.kode_warna", "w4.kode_warna", "w5.kode_warna", "w6.kode_warna", "w7.kode_warna", "w8.kode_warna"));
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataDetailSalesOrder_crostab($id)
    {


        // get data ukuran 
        $pru['use'] = 1; // ambil ukuran yang digunakan order 
        $pru['id_sales_order'] = $id;
        $dtUkuran = $this->getUkuranTrans($pru);

        // Dynamic Columns
        $col11 = "";
        $col12 = "";
        $col21 = "";
        $col22 = "";
        $col3  = "";

        foreach ($dtUkuran as $item) {
            $key = $item->key_ukuran;
            if ($key == 'all') $key = 'all_';
            $hrg = $key . '_hrg';

            
            $keySql = preg_match('/^[a-zA-Z_]+$/', $key) ? $key : "\"$key\"";

            $col11 .= ($col11 == "") ? "coalesce(tbl.$keySql,  0) as $keySql" : ",coalesce(tbl.$keySql, 0) as $keySql";
            //  $col12 .= ($col12 == "") ? "coalesce(tbl.$hrg,0) as $hrg" : ",coalesce(tbl.$hrg,0) as $hrg";
            
            $col21 .= ($col21 == "") ? "$keySql INT" : ",$keySql INT";

            $col3 .= ($col3 == "") ? $keySql : "," . $keySql;
            //  $col22 .= ($col22 == "") ? "$hrg Float" : ",$hrg Float";
        }

        $sql = "
            SELECT 
                tbl.id,
                ROW_NUMBER() OVER (ORDER BY tbl.id) AS no,
                COALESCE(w1.kode_warna, '') as colorDasar,
                TRIM(BOTH ' - ' FROM COALESCE(w1.kode_warna, '') ||
                    CASE WHEN w2.kode_warna IS NOT NULL THEN ' - ' || w2.kode_warna ELSE '' END ||
                    CASE WHEN w3.kode_warna IS NOT NULL THEN ' - ' || w3.kode_warna ELSE '' END ||
                    CASE WHEN w4.kode_warna IS NOT NULL THEN ' - ' || w4.kode_warna ELSE '' END ||
                    CASE WHEN w5.kode_warna IS NOT NULL THEN ' - ' || w5.kode_warna ELSE '' END ||
                    CASE WHEN w6.kode_warna IS NOT NULL THEN ' - ' || w6.kode_warna ELSE '' END ||
                    CASE WHEN w7.kode_warna IS NOT NULL THEN ' - ' || w7.kode_warna ELSE '' END ||
                    CASE WHEN w8.kode_warna IS NOT NULL THEN ' - ' || w8.kode_warna ELSE '' END 
                ) AS colour,
                TRIM(BOTH ' ~ ' FROM COALESCE(w1.keterangan, '') ||
                    CASE WHEN w2.keterangan IS NOT NULL THEN ' ~ ' || w2.keterangan ELSE '' END ||
                    CASE WHEN w3.keterangan IS NOT NULL THEN ' ~ ' || w3.keterangan ELSE '' END ||
                    CASE WHEN w4.keterangan IS NOT NULL THEN ' ~ ' || w4.keterangan ELSE '' END ||
                    CASE WHEN w5.keterangan IS NOT NULL THEN ' ~ ' || w5.keterangan ELSE '' END ||
                    CASE WHEN w6.keterangan IS NOT NULL THEN ' ~ ' || w6.keterangan ELSE '' END ||
                    CASE WHEN w7.keterangan IS NOT NULL THEN ' ~ ' || w7.keterangan ELSE '' END ||
                    CASE WHEN w8.keterangan IS NOT NULL THEN ' ~ ' || w8.keterangan ELSE '' END 
                ) AS keterangan,
                {$col11},
                COALESCE((
                    SELECT SUM(x.harga_total)
                    FROM trans_sales_order_ukuran x
                    WHERE x.id_sales_order_det = tbl.id
                ), 0) as total_harga
            FROM 
                CROSSTAB(
                    $$
                    SELECT 
                        td.id,
                        ru.seq,
                        (CASE WHEN ru.key_ukuran = 'all' THEN 'all_' ELSE 
                            LOWER(REGEXP_REPLACE(ru.key_ukuran, '[^a-zA-Z0-9]+', '_', 'g')) END) AS key_ukuran,
                        SUM(COALESCE(tu.qty, 0)) AS qty
                    FROM 
                        trans_sales_order_ukuran tu 
                    INNER JOIN trans_sales_order_det td ON td.id = tu.id_sales_order_det
                    INNER JOIN ref_ukuran ru ON ru.id = tu.id_ukuran
                    WHERE 
                        (tu.qty IS NOT NULL AND tu.qty > 0) 
                        AND td.id_sales_order = {$id}
                    GROUP BY td.id, ru.key_ukuran, ru.seq
                    ORDER BY td.id, ru.seq ASC
                    $$,
                    $$ 
                        SELECT unnest(string_to_array('{$col3}', ','))
                    $$
                ) AS tbl (
                    id INT,
                    seq INT,
                    {$col21}
                )
            INNER JOIN trans_sales_order_det td ON td.id = tbl.id
            INNER JOIN ref_warna w1 ON td.id_warna_1 = w1.id
            LEFT JOIN ref_warna w2 ON td.id_warna_2 = w2.id
            LEFT JOIN ref_warna w3 ON td.id_warna_3 = w3.id
            LEFT JOIN ref_warna w4 ON td.id_warna_4 = w4.id
            LEFT JOIN ref_warna w5 ON td.id_warna_5 = w5.id
            LEFT JOIN ref_warna w6 ON td.id_warna_6 = w6.id
            LEFT JOIN ref_warna w7 ON td.id_warna_7 = w7.id
            LEFT JOIN ref_warna w8 ON td.id_warna_8 = w8.id;
        ";

        $query = $this->db->query($sql);
        $this->_data = $query->getResult();
        return $this->_data;

    }

    function getDataDetailSalesOrder_crostab_si($id, $idInvoice = null)
{
    // get data ukuran 
    $pru['use'] = 1; // ambil ukuran yang digunakan order 
    $pru['id_sales_order'] = $id;
    $dtUkuran = $this->getUkuranTrans($pru);
    
    // Dynamic Columns
    $col11 = "";
    $col21 = "";
    $col3  = "";
    $tddInvoice = "";
    if (!empty($idInvoice)) {
        $tddInvoice = " and td_head.id_invoice = $idInvoice" ;
        $idInvoice = " and td_head.id_invoice = $idInvoice";
    }
    else {
        $idInvoice = "";
    }

    foreach ($dtUkuran as $item) {
        $key = $item->key_ukuran;
        if ($key == 'all') $key = 'all_';
        
        $keySql = preg_match('/^[a-zA-Z_]+$/', $key) ? $key : "\"$key\"";

        // 👉 sekarang qty ambil dari delivery (tdd)
        $col11 .= ($col11 == "") 
            ? "coalesce(tbl.$keySql,0) as $keySql" 
            : ",coalesce(tbl.$keySql,0) as $keySql";
        
        $col21 .= ($col21 == "") ? "$keySql INT" : ",$keySql INT";

        $col3 .= ($col3 == "") ? $keySql : "," . $keySql;
    }
    $sql = "
        SELECT 
            tbl.id,
            ROW_NUMBER() OVER (ORDER BY tbl.id) AS no,
            COALESCE(w1.kode_warna, '') as colorDasar,
            TRIM(BOTH ' - ' FROM COALESCE(w1.kode_warna, '') ||
                CASE WHEN w2.kode_warna IS NOT NULL THEN ' - ' || w2.kode_warna ELSE '' END ||
                CASE WHEN w3.kode_warna IS NOT NULL THEN ' - ' || w3.kode_warna ELSE '' END ||
                CASE WHEN w4.kode_warna IS NOT NULL THEN ' - ' || w4.kode_warna ELSE '' END ||
                CASE WHEN w5.kode_warna IS NOT NULL THEN ' - ' || w5.kode_warna ELSE '' END ||
                CASE WHEN w6.kode_warna IS NOT NULL THEN ' - ' || w6.kode_warna ELSE '' END ||
                CASE WHEN w7.kode_warna IS NOT NULL THEN ' - ' || w7.kode_warna ELSE '' END ||
                CASE WHEN w8.kode_warna IS NOT NULL THEN ' - ' || w8.kode_warna ELSE '' END 
            ) AS colour,
            TRIM(BOTH ' ~ ' FROM COALESCE(w1.keterangan, '') ||
                CASE WHEN w2.keterangan IS NOT NULL THEN ' ~ ' || w2.keterangan ELSE '' END ||
                CASE WHEN w3.keterangan IS NOT NULL THEN ' ~ ' || w3.keterangan ELSE '' END ||
                CASE WHEN w4.keterangan IS NOT NULL THEN ' ~ ' || w4.keterangan ELSE '' END ||
                CASE WHEN w5.keterangan IS NOT NULL THEN ' ~ ' || w5.keterangan ELSE '' END ||
                CASE WHEN w6.keterangan IS NOT NULL THEN ' ~ ' || w6.keterangan ELSE '' END ||
                CASE WHEN w7.keterangan IS NOT NULL THEN ' ~ ' || w7.keterangan ELSE '' END ||
                CASE WHEN w8.keterangan IS NOT NULL THEN ' ~ ' || w8.keterangan ELSE '' END 
            ) AS keterangan,
            {$col11},
             COALESCE((
                SELECT SUM(tdp.qty_do * tdp.harga_satuan)
                FROM trans_delivery_detail tdd
                INNER JOIN trans_delivery_prod tdp 
                    ON tdp.id_delivery = tdd.id_delivery 
                AND tdp.id_ukuran = tdd.id_ukuran 
                AND tdp.ref_detail_id = tdd.ref_detail_id
                INNER JOIN trans_delivery td_head 
                    ON td_head.id = tdd.id_delivery
                WHERE tdd.ref_detail_id = tbl.id {$tddInvoice}
            ), 0) AS total_harga
        FROM 
            CROSSTAB(
                $$
                SELECT 
                    td.id,
                    ru.seq,
                    (CASE WHEN ru.key_ukuran = 'all' THEN 'all_' ELSE 
                        LOWER(REGEXP_REPLACE(ru.key_ukuran, '[^a-zA-Z0-9]+', '_', 'g')) END) AS key_ukuran,
                    SUM(COALESCE(tdd.qty,0)) AS qty_do
                FROM 
                    trans_delivery_detail tdd
                INNER JOIN trans_delivery td_head ON td_head.id = tdd.id_delivery
                INNER JOIN trans_sales_order_det td ON td.id = tdd.ref_detail_id
                INNER JOIN ref_ukuran ru ON ru.id = tdd.id_ukuran
                WHERE td.id_sales_order = {$id}
                {$idInvoice}
                GROUP BY td.id, ru.key_ukuran, ru.seq
                ORDER BY td.id, ru.seq ASC
                $$,
                $$ 
                    SELECT unnest(string_to_array('{$col3}', ','))
                $$
            ) AS tbl (
                id INT,
                seq INT,
                {$col21}
            )
        INNER JOIN trans_sales_order_det td ON td.id = tbl.id
        INNER JOIN ref_warna w1 ON td.id_warna_1 = w1.id
        LEFT JOIN ref_warna w2 ON td.id_warna_2 = w2.id
        LEFT JOIN ref_warna w3 ON td.id_warna_3 = w3.id
        LEFT JOIN ref_warna w4 ON td.id_warna_4 = w4.id
        LEFT JOIN ref_warna w5 ON td.id_warna_5 = w5.id
        LEFT JOIN ref_warna w6 ON td.id_warna_6 = w6.id
        LEFT JOIN ref_warna w7 ON td.id_warna_7 = w7.id
        LEFT JOIN ref_warna w8 ON td.id_warna_8 = w8.id;
    ";

    $query = $this->db->query($sql);
    $this->_data = $query->getResult();
    return $this->_data;
}


    function getUkuranTrans($params)
    {
        $builder = $this->db->table('trans_sales_order_ukuran tu');
        $builder->select("tu.id_ukuran, rk.key_ukuran, rk.kode_ukuran, tu.id_sales_order");

        $builder->join('trans_sales_order_det td', 'td.id = tu.id_sales_order_det', 'inner');
        $builder->join('ref_ukuran rk', 'tu.id_ukuran = rk.id', 'inner');

        if (!empty($params['use'])) {
            $builder->where('(tu.qty is not null and tu.qty > 0)');
        }

        if (!empty($params['id_sales_order'])) {
            $builder->where('tu.id_sales_order', $params['id_sales_order']);
        }

        $builder->groupBy("tu.id_ukuran, rk.key_ukuran, rk.kode_ukuran, rk.seq, tu.id_sales_order");

        $builder->orderBy("rk.seq");

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataDetailSalesOrder_ori($idSalesOrder)
    {
        $builder = $this->db->table("trans_sales_order_det" . " abx");
        $builder->where("abx.id_sales_order", $idSalesOrder);
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataDetailSampleUkuran($idSalesOrder, $idSalesOrderDet)
    {
        $sql = "SELECT
                    bbx.id,
                    abx.kode_ukuran as ukuran,
                    abx.id AS id_ukuran,
                    bbx.qty,
                    bbx.harga_satuan,
                    bbx.harga_total
                FROM
                    ref_ukuran abx
                    LEFT JOIN trans_sales_order_ukuran bbx ON bbx.id_ukuran = abx.id
                    AND bbx.id_sales_order = $idSalesOrder
                    AND bbx.id_sales_order_det = $idSalesOrderDet
                    WHERE abx.active = 1
                    ORDER BY abx.id";
        $result = $this->db->query($sql);
        $this->_data   = $result->getResult();
        return $this->_data;
    }

    function getDataDetailSampleWarna($idSample, $id)
    {

        $builder = $this->db->table("trans_sales_order_det");
        $builder->where("id_sales_order", $idSample);
        $builder->where("id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function trxInsertUpdateRecord($dataWarna, $dataUkuran)
    {
        $this->db->transStart();
        try {

            if (!empty($dataWarna['id'])) {
                $dataWarna['updated_at'] = date("Y-m-d H:i:s");
                $this->updateRecord("trans_sales_order_det", $dataWarna, 'id', $dataWarna['id']);
            } else {
                unset($dataWarna['id']);
                $dataWarna['created_at'] = date("Y-m-d H:i:s");
                $idSampleDet = $this->insertRecordGetid("trans_sales_order_det", $dataWarna);
            }
            if (!empty($dataWarna['id']) && ($dataWarna['id_sales_order'])) {
                $arrDelete = [
                    "id_sales_order" => $dataWarna['id_sales_order'],
                    "id_sales_order_det" => $dataWarna['id']
                ];
                $this->deleteRecordMultipleColumn("trans_sales_order_ukuran", $arrDelete);
            }

            $head_qty = 0;
            $head_total = 0;
            foreach ($dataUkuran as $rowData) {

                $harga_total = (!empty($rowData['qty']) && !empty($rowData['harga_satuan'])) ? $rowData['qty'] * $rowData['harga_satuan'] : 0;
                $arrDataUkuran = [
                    "id_sales_order" => $dataWarna['id_sales_order'],
                    "id_sales_order_det" => !empty($dataWarna['id']) ? $dataWarna['id'] : $idSampleDet,
                    "id_ukuran" => $rowData['id_ukuran'],
                    "qty" => $rowData['qty'],
                    "harga_satuan" => $rowData['harga_satuan'],
                    "harga_total" => $harga_total, //$rowData['harga_total'],
                    "active" => 1,
                    "created_at" =>  date("Y-m-d H:i:s"),

                ];
                $head_qty = $head_qty + (!empty($rowData['qty'])) ? (int) $rowData['qty'] : 0;
                $head_total = $head_total + (!empty($harga_total)) ? (float) $harga_total : 0;
                $this->insertRecordGetid("trans_sales_order_ukuran", $arrDataUkuran);
            }

            // update data qty dan total harga 
            $head_up['qty'] = $head_qty;
            $head_up['total_harga'] = $head_total;
            $this->updateRecord($this->table, $head_up, 'id', $dataWarna['id_sales_order']);

            $this->db->transComplete();

            if ($this->db->transStatus() === TRUE) {
                return true;
            } else {
                throw new \Exception("Transaction failed");
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    function getTotal_qty($trans_id, $tipe)
    {
        $builder = $this->db->table("trans_sales_order_ukuran tsu");
        $builder->select("sum(tsu.qty) as cnt");
        $builder->join("trans_sales_order_det td", "td.id = tsu.id_sales_order_det");
        if ($tipe == 1) {
            $builder->where("td.id_sales_order", $trans_id);
        } else {
            $builder->where("tsu.id_sales_order_det", $trans_id);
        }
        $this->_data = $builder->get()->getRow();
        return $this->_data->cnt;
    }

    function getDataSO($kodeSalesOrder)
    {
        $builder = $this->db->table($this->table);
        $builder->select("id");
        $builder->where("kode_sales_order", $kodeSalesOrder);

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }


    function generete_kode()
    {
        $kd = "SOD";
        $builder = $this->db->table($this->table . ' a');
        $builder->select('LEFT(kode_sales_order, 7) AS tgl, RIGHT( kode_sales_order, 4 ) AS kode ');

        $builder->orderBy('a.id', "DESC");
        $builder->limit(1);
        $query = $builder->get()->getRow();

        if ($query != NULL) {
            if ($query->tgl == $kd . date('y') . date('m')) {     //cek dulu apakah ada sudah ada tahun dan bulan di tabel.   
                //jika tahun dan bulan ternyata sudah ada.      
                // $data = $query->row();
                $kode = intval($query->kode) + 1;
            } else {
                //jika tahun dan belum ada      
                $kode = 1;
            }
        } else {
            $kode = 1;
        }

        $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT); // angka 3 menunjukkan jumlah digit angka 0
        $kodejadi = $kd . date('y') . date('m') . $kodemax;

        // hasilnya SOD24100001 dst.
        return $kodejadi;
    }

    function getDataDetailSalesOrderUkuranById($params)
    {

        $builder = $this->db->table("trans_sales_order_ukuran tu");
        $builder->select(" tu.id as id_ukuran_so, tu.id_ukuran,ru.kode_ukuran, ru.key_ukuran, 
                           w1.kode_warna as warna_1, w2.kode_warna as warna_2, w3.kode_warna as warna_3,
                           w4.kode_warna as warna_4, w5.kode_warna as warna_5, w6.kode_warna as warna_6,
                           w7.kode_warna as warna_7, w8.kode_warna as warna_8,
                           tod.kode_sales_order, tod.style, tod.deskripsi, tu.id_sales_order");

        $builder->join("trans_sales_order_det td", "td.id = tu.id_sales_order_det", "inner");
        $builder->join("trans_sales_order tod", "tod.id = td.id_sales_order", "inner");
        $builder->join("ref_ukuran ru", "tu.id_ukuran = ru.id", "left");
        $builder->join("ref_warna w1", "td.id_warna_1 = w1.id", "left");
        $builder->join("ref_warna w2", "td.id_warna_2 = w2.id", "left");
        $builder->join("ref_warna w3", "td.id_warna_3 = w3.id", "left");
        $builder->join("ref_warna w4", "td.id_warna_4 = w4.id", "left");
        $builder->join("ref_warna w5", "td.id_warna_5 = w5.id", "left");
        $builder->join("ref_warna w6", "td.id_warna_6 = w6.id", "left");
        $builder->join("ref_warna w7", "td.id_warna_7 = w7.id", "left");
        $builder->join("ref_warna w8", "td.id_warna_8 = w8.id", "left");
        // $builder->where("abx.id", $id);

        if (!empty($params['id_ukuran_so'])) {
            $builder->where("tu.id", $params['id_ukuran_so']);
        }

        if (!empty($params['kode_ukuran'])) {
            $builder->where("ru.kode_ukuran", $params['kode_ukuran']);
        }

        if (!empty($params['key_ukuran'])) {
            $builder->where("ru.key_ukuran", $params['key_ukuran']);
        }

        if (!empty($params['id_sales_order_det'])) {
            $builder->where("tu.id_sales_order_det", $params['id_sales_order_det']);
        }

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_export($from_date = null, $to_date = null, $buyer = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.kode_sales_order, abx.deskripsi, bbx.nama, abx.tgl_transaksi, abx.tgl_deadline, abx.tgl_deadline_dua, abx.qty,
                          abx.uang_dp, abx.style as stylex , abx.style_cnt, abx.status,
                          concat(abx.style,' - ', abx.style_cnt) as style, concat(abx.style, '  (', abx.style_cnt, ')') as style_print, abx.tgl_dp,
                          COALESCE((
                                SELECT SUM(tsou.harga_total)
                                FROM trans_sales_order_ukuran tsou
                                WHERE tsou.id_sales_order = abx.id
                            ), 0) as harga_total,
                            COALESCE((
                                SELECT SUM(tid.grand_total)
                                FROM trans_invoice_detail tid
                                WHERE tid.id_ref = abx.id and tid.tipe_id = 2
                            ), 0) as nilai_invoice,
                            COALESCE((
                                SELECT STRING_AGG(ti.kode_invoice, ' - ')
                                FROM trans_invoice_detail tidt
                                INNER JOIN trans_invoice ti on ti.id = CAST(tidt.id_invoice  AS INTEGER)
                                WHERE tidt.id_ref = abx.id
                            ), '-') as kode_invoice,
                            COALESCE((
                                SELECT SUM(tidd.grand_total)
                                FROM trans_invoice_detail tidd
                                WHERE tidd.id_ref = abx.id AND tidd.tipe_id = 2 AND tidd.payment_status = 1
                            ), 0) as pembayaran
                            ");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
       
        $builder->where('abx.active = 1');
        $builder->where('abx.status = 2');
        $builder->where("abx.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
        // $builder->where("
        //     COALESCE((
        //         SELECT SUM(tsou.harga_total)
        //         FROM trans_sales_order_ukuran tsou
        //         WHERE tsou.id_sales_order = abx.id
        //     ), 0)
        //     >
        //     (
        //         abx.uang_dp +
        //         COALESCE((
        //             SELECT SUM(tidd.grand_total)
        //             FROM trans_invoice_detail tidd
        //             WHERE tidd.id_ref = abx.id AND tidd.tipe_id = 2 AND tidd.payment_status = 1
        //         ), 0)
        //     )
        // ");
        if (!empty($buyer)) {
            $builder->where("abx.id_konsumen", $buyer);
        }
        $builder->orderBy("abx.tgl_transaksi", 'desc');
        
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }
}
