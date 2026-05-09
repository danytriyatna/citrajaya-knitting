<?php

namespace Modules\Transaction\Models;

use Modules\Referensi\Models\BarangModel;

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
                          abx.gambar_id,cbx.file_name, abx.id_sample, abx.uang_dp, abx.uang_dp_2, abx.style as stylex , abx.style_cnt, abx.pengiriman,
                          concat(abx.style,' - ', abx.style_cnt) as style, concat(abx.style, '  (', abx.style_cnt, ')') as style_print, abx.tgl_dp, abx.type_dp, abx.tgl_dp_2, abx.type_dp_2");
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
        $pru['use'] = 1;
        $pru['id_sales_order'] = $id;
        $dtUkuran = $this->getUkuranTrans($pru);

        $col11     = "";
        $col21     = "";
        $col3      = "";
        $col_harga = "";

        foreach ($dtUkuran as $item) {
            $key    = $item->key_ukuran;
            if ($key == 'all') $key = 'all_';
            $keySql  = preg_match('/^[a-zA-Z_]+$/', $key) ? $key : "\"$key\"";
            $codeSub = $keySql;
            $keySql == 'all_' ? $codeSub = 'all' : $codeSub = $codeSub;

            $col11 .= ($col11 == "") ? "coalesce(tbl.$keySql, 0) as $keySql" : ",coalesce(tbl.$keySql, 0) as $keySql";
            $col21 .= ($col21 == "") ? "$keySql INT" : ",$keySql INT";
            $col3  .= ($col3  == "") ? $keySql : "," . $keySql;

            $subHarga = "
                COALESCE(
                    (
                        SELECT tsou.harga_satuan
                        FROM trans_sales_order_ukuran tsou 
                        INNER JOIN ref_ukuran ru ON ru.id = tsou.id_ukuran
                        INNER JOIN trans_sales_order_det tsod ON tsod.id = tsou.id_sales_order_det

                        -- JOIN ref_barang untuk subquery
                        LEFT JOIN ref_barang bsub1 ON bsub1.id = tsod.id_barang_1
                        LEFT JOIN ref_barang bsub2 ON bsub2.id = tsod.id_barang_2
                        LEFT JOIN ref_barang bsub3 ON bsub3.id = tsod.id_barang_3
                        LEFT JOIN ref_barang bsub4 ON bsub4.id = tsod.id_barang_4
                        LEFT JOIN ref_barang bsub5 ON bsub5.id = tsod.id_barang_5
                        LEFT JOIN ref_barang bsub6 ON bsub6.id = tsod.id_barang_6
                        LEFT JOIN ref_barang bsub7 ON bsub7.id = tsod.id_barang_7
                        LEFT JOIN ref_barang bsub8 ON bsub8.id = tsod.id_barang_8

                        -- JOIN ref_warna: COALESCE dari ref_barang, fallback ke tsod
                        LEFT JOIN ref_warna rw1 ON rw1.id = COALESCE(bsub1.id_warna, tsod.id_warna_1)
                        LEFT JOIN ref_warna rw2 ON rw2.id = COALESCE(bsub2.id_warna, tsod.id_warna_2)
                        LEFT JOIN ref_warna rw3 ON rw3.id = COALESCE(bsub3.id_warna, tsod.id_warna_3)
                        LEFT JOIN ref_warna rw4 ON rw4.id = COALESCE(bsub4.id_warna, tsod.id_warna_4)
                        LEFT JOIN ref_warna rw5 ON rw5.id = COALESCE(bsub5.id_warna, tsod.id_warna_5)
                        LEFT JOIN ref_warna rw6 ON rw6.id = COALESCE(bsub6.id_warna, tsod.id_warna_6)
                        LEFT JOIN ref_warna rw7 ON rw7.id = COALESCE(bsub7.id_warna, tsod.id_warna_7)
                        LEFT JOIN ref_warna rw8 ON rw8.id = COALESCE(bsub8.id_warna, tsod.id_warna_8)

                        WHERE tsou.id_sales_order_det = tbl.id
                        AND ru.key_ukuran = '$codeSub' 
                        AND CONCAT_WS('~', 
                            NULLIF(rw1.kode_warna, ''), 
                            NULLIF(rw2.kode_warna, ''), 
                            NULLIF(rw3.kode_warna, ''),
                            NULLIF(rw4.kode_warna, ''),
                            NULLIF(rw5.kode_warna, ''),
                            NULLIF(rw6.kode_warna, ''),
                            NULLIF(rw7.kode_warna, ''),
                            NULLIF(rw8.kode_warna, '')
                        ) = TRIM(BOTH '~' FROM
                            COALESCE(w1.kode_warna, '') ||
                            CASE WHEN w2.kode_warna IS NOT NULL THEN '~' || w2.kode_warna ELSE '' END ||
                            CASE WHEN w3.kode_warna IS NOT NULL THEN '~' || w3.kode_warna ELSE '' END ||
                            CASE WHEN w4.kode_warna IS NOT NULL THEN '~' || w4.kode_warna ELSE '' END ||
                            CASE WHEN w5.kode_warna IS NOT NULL THEN '~' || w5.kode_warna ELSE '' END ||
                            CASE WHEN w6.kode_warna IS NOT NULL THEN '~' || w6.kode_warna ELSE '' END ||
                            CASE WHEN w7.kode_warna IS NOT NULL THEN '~' || w7.kode_warna ELSE '' END ||
                            CASE WHEN w8.kode_warna IS NOT NULL THEN '~' || w8.kode_warna ELSE '' END
                        )
                    ), 0
                ) AS harga_satuan_$codeSub";

            $col_harga .= ($col_harga == "") ? $subHarga : "," . $subHarga;
        }

        $sql = "
            SELECT 
                tbl.id,
                ROW_NUMBER() OVER (ORDER BY tbl.id) AS no,

                -- Flag sumber warna
                CASE
                    WHEN td.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                w1.kode_warna AS colorDasar,
                TRIM(BOTH ' - ' FROM
                    COALESCE(w1.kode_warna, '') ||
                    CASE WHEN w2.kode_warna IS NOT NULL THEN ' - ' || w2.kode_warna ELSE '' END ||
                    CASE WHEN w3.kode_warna IS NOT NULL THEN ' - ' || w3.kode_warna ELSE '' END ||
                    CASE WHEN w4.kode_warna IS NOT NULL THEN ' - ' || w4.kode_warna ELSE '' END ||
                    CASE WHEN w5.kode_warna IS NOT NULL THEN ' - ' || w5.kode_warna ELSE '' END ||
                    CASE WHEN w6.kode_warna IS NOT NULL THEN ' - ' || w6.kode_warna ELSE '' END ||
                    CASE WHEN w7.kode_warna IS NOT NULL THEN ' - ' || w7.kode_warna ELSE '' END ||
                    CASE WHEN w8.kode_warna IS NOT NULL THEN ' - ' || w8.kode_warna ELSE '' END
                ) AS colour,
                TRIM(BOTH ' ~ ' FROM
                    COALESCE(w1.keterangan, '') ||
                    CASE WHEN w2.keterangan IS NOT NULL THEN ' ~ ' || w2.keterangan ELSE '' END ||
                    CASE WHEN w3.keterangan IS NOT NULL THEN ' ~ ' || w3.keterangan ELSE '' END ||
                    CASE WHEN w4.keterangan IS NOT NULL THEN ' ~ ' || w4.keterangan ELSE '' END ||
                    CASE WHEN w5.keterangan IS NOT NULL THEN ' ~ ' || w5.keterangan ELSE '' END ||
                    CASE WHEN w6.keterangan IS NOT NULL THEN ' ~ ' || w6.keterangan ELSE '' END ||
                    CASE WHEN w7.keterangan IS NOT NULL THEN ' ~ ' || w7.keterangan ELSE '' END ||
                    CASE WHEN w8.keterangan IS NOT NULL THEN ' ~ ' || w8.keterangan ELSE '' END
                ) AS keterangan,
                {$col11},
                (
                    SELECT 
                        COALESCE(SUM(x.harga_satuan), 0) 
                        / NULLIF(COUNT(CASE WHEN x.qty IS NOT NULL THEN 1 END), 0)
                    FROM trans_sales_order_ukuran x
                    WHERE x.id_sales_order_det = tbl.id
                ) AS total_satuan,
                COALESCE((
                    SELECT SUM(x.harga_total)
                    FROM trans_sales_order_ukuran x
                    WHERE x.id_sales_order_det = tbl.id
                ), 0) AS total_harga,
                {$col_harga}

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

            -- JOIN ref_barang untuk query utama
            LEFT JOIN ref_barang b1 ON b1.id = td.id_barang_1
            LEFT JOIN ref_barang b2 ON b2.id = td.id_barang_2
            LEFT JOIN ref_barang b3 ON b3.id = td.id_barang_3
            LEFT JOIN ref_barang b4 ON b4.id = td.id_barang_4
            LEFT JOIN ref_barang b5 ON b5.id = td.id_barang_5
            LEFT JOIN ref_barang b6 ON b6.id = td.id_barang_6
            LEFT JOIN ref_barang b7 ON b7.id = td.id_barang_7
            LEFT JOIN ref_barang b8 ON b8.id = td.id_barang_8

            -- JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di trans_sales_order_det
            LEFT JOIN ref_warna w1 ON w1.id = COALESCE(b1.id_warna, td.id_warna_1)
            LEFT JOIN ref_warna w2 ON w2.id = COALESCE(b2.id_warna, td.id_warna_2)
            LEFT JOIN ref_warna w3 ON w3.id = COALESCE(b3.id_warna, td.id_warna_3)
            LEFT JOIN ref_warna w4 ON w4.id = COALESCE(b4.id_warna, td.id_warna_4)
            LEFT JOIN ref_warna w5 ON w5.id = COALESCE(b5.id_warna, td.id_warna_5)
            LEFT JOIN ref_warna w6 ON w6.id = COALESCE(b6.id_warna, td.id_warna_6)
            LEFT JOIN ref_warna w7 ON w7.id = COALESCE(b7.id_warna, td.id_warna_7)
            LEFT JOIN ref_warna w8 ON w8.id = COALESCE(b8.id_warna, td.id_warna_8);
        ";

        $query = $this->db->query($sql);
        $this->_data = $query->getResult();
        return $this->_data;
    }

    function getDataDetailSalesOrder_crostab_si($id, $idInvoice = null)
    {
        $pru['use'] = 1;
        $pru['id_sales_order'] = $id;
        $dtUkuran = $this->getUkuranTrans($pru);

        $col11      = "";
        $col21      = "";
        $col3       = "";
        $tddInvoice = "";
        $col_harga  = "";

        if (!empty($idInvoice)) {
            $tddInvoice = " AND td_head.id_invoice = $idInvoice";
            $idInvoice  = " AND td_head.id_invoice = $idInvoice";
        } else {
            $idInvoice = "";
        }

        foreach ($dtUkuran as $item) {
            $key    = $item->key_ukuran;
            if ($key == 'all') $key = 'all_';
            $keySql  = preg_match('/^[a-zA-Z_]+$/', $key) ? $key : "\"$key\"";
            $codeSub = $keySql;
            $keySql == 'all_' ? $codeSub = 'all' : $codeSub = $codeSub;

            $col11 .= ($col11 == "") ? "coalesce(tbl.$keySql,0) as $keySql" : ",coalesce(tbl.$keySql,0) as $keySql";
            $col21 .= ($col21 == "") ? "$keySql INT" : ",$keySql INT";
            $col3  .= ($col3  == "") ? $keySql : "," . $keySql;

            // Subquery col_harga juga disesuaikan dengan COALESCE ref_barang
            $subHarga = "
                COALESCE(
                    (
                        SELECT tsou.harga_satuan
                        FROM trans_sales_order_ukuran tsou 
                        INNER JOIN ref_ukuran ru ON ru.id = tsou.id_ukuran
                        INNER JOIN trans_sales_order_det tsod ON tsod.id = tsou.id_sales_order_det

                        -- JOIN ref_barang untuk subquery col_harga
                        LEFT JOIN ref_barang bsub1 ON bsub1.id = tsod.id_barang_1
                        LEFT JOIN ref_barang bsub2 ON bsub2.id = tsod.id_barang_2
                        LEFT JOIN ref_barang bsub3 ON bsub3.id = tsod.id_barang_3
                        LEFT JOIN ref_barang bsub4 ON bsub4.id = tsod.id_barang_4
                        LEFT JOIN ref_barang bsub5 ON bsub5.id = tsod.id_barang_5
                        LEFT JOIN ref_barang bsub6 ON bsub6.id = tsod.id_barang_6
                        LEFT JOIN ref_barang bsub7 ON bsub7.id = tsod.id_barang_7
                        LEFT JOIN ref_barang bsub8 ON bsub8.id = tsod.id_barang_8

                        -- JOIN ref_warna dengan COALESCE ref_barang, fallback ke tsod
                        LEFT JOIN ref_warna rw1 ON rw1.id = COALESCE(bsub1.id_warna, tsod.id_warna_1)
                        LEFT JOIN ref_warna rw2 ON rw2.id = COALESCE(bsub2.id_warna, tsod.id_warna_2)
                        LEFT JOIN ref_warna rw3 ON rw3.id = COALESCE(bsub3.id_warna, tsod.id_warna_3)
                        LEFT JOIN ref_warna rw4 ON rw4.id = COALESCE(bsub4.id_warna, tsod.id_warna_4)
                        LEFT JOIN ref_warna rw5 ON rw5.id = COALESCE(bsub5.id_warna, tsod.id_warna_5)
                        LEFT JOIN ref_warna rw6 ON rw6.id = COALESCE(bsub6.id_warna, tsod.id_warna_6)
                        LEFT JOIN ref_warna rw7 ON rw7.id = COALESCE(bsub7.id_warna, tsod.id_warna_7)
                        LEFT JOIN ref_warna rw8 ON rw8.id = COALESCE(bsub8.id_warna, tsod.id_warna_8)

                        WHERE tsou.id_sales_order_det = tbl.id
                        AND ru.key_ukuran = '$codeSub' 
                        AND CONCAT_WS('~', 
                            NULLIF(rw1.kode_warna, ''), 
                            NULLIF(rw2.kode_warna, ''), 
                            NULLIF(rw3.kode_warna, ''),
                            NULLIF(rw4.kode_warna, ''),
                            NULLIF(rw5.kode_warna, ''),
                            NULLIF(rw6.kode_warna, ''),
                            NULLIF(rw7.kode_warna, ''),
                            NULLIF(rw8.kode_warna, '')
                        ) = TRIM(BOTH '~' FROM
                            COALESCE(w1.kode_warna, '') ||
                            CASE WHEN w2.kode_warna IS NOT NULL THEN '~' || w2.kode_warna ELSE '' END ||
                            CASE WHEN w3.kode_warna IS NOT NULL THEN '~' || w3.kode_warna ELSE '' END ||
                            CASE WHEN w4.kode_warna IS NOT NULL THEN '~' || w4.kode_warna ELSE '' END ||
                            CASE WHEN w5.kode_warna IS NOT NULL THEN '~' || w5.kode_warna ELSE '' END ||
                            CASE WHEN w6.kode_warna IS NOT NULL THEN '~' || w6.kode_warna ELSE '' END ||
                            CASE WHEN w7.kode_warna IS NOT NULL THEN '~' || w7.kode_warna ELSE '' END ||
                            CASE WHEN w8.kode_warna IS NOT NULL THEN '~' || w8.kode_warna ELSE '' END
                        )
                    ), 0
                ) AS harga_satuan_$codeSub";

            $col_harga .= ($col_harga == "") ? $subHarga : "," . $subHarga;
        }

        $sql = "
            SELECT 
                tbl.id,
                ROW_NUMBER() OVER (ORDER BY tbl.id) AS no,

                -- Flag sumber warna
                CASE
                    WHEN td.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                COALESCE(w1.kode_warna, '') AS colorDasar,
                TRIM(BOTH ' - ' FROM
                    COALESCE(w1.kode_warna, '') ||
                    CASE WHEN w2.kode_warna IS NOT NULL THEN ' - ' || w2.kode_warna ELSE '' END ||
                    CASE WHEN w3.kode_warna IS NOT NULL THEN ' - ' || w3.kode_warna ELSE '' END ||
                    CASE WHEN w4.kode_warna IS NOT NULL THEN ' - ' || w4.kode_warna ELSE '' END ||
                    CASE WHEN w5.kode_warna IS NOT NULL THEN ' - ' || w5.kode_warna ELSE '' END ||
                    CASE WHEN w6.kode_warna IS NOT NULL THEN ' - ' || w6.kode_warna ELSE '' END ||
                    CASE WHEN w7.kode_warna IS NOT NULL THEN ' - ' || w7.kode_warna ELSE '' END ||
                    CASE WHEN w8.kode_warna IS NOT NULL THEN ' - ' || w8.kode_warna ELSE '' END
                ) AS colour,
                TRIM(BOTH ' ~ ' FROM
                    COALESCE(w1.keterangan, '') ||
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
                ), 0) AS total_harga,
                {$col_harga}

            FROM 
                CROSSTAB(
                    $$
                    SELECT 
                        td.id,
                        ru.seq,
                        (CASE WHEN ru.key_ukuran = 'all' THEN 'all_' ELSE 
                            LOWER(REGEXP_REPLACE(ru.key_ukuran, '[^a-zA-Z0-9]+', '_', 'g')) END) AS key_ukuran,
                        SUM(COALESCE(tdd.qty, 0)) AS qty_do
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

            -- JOIN ref_barang untuk query utama
            LEFT JOIN ref_barang b1 ON b1.id = td.id_barang_1
            LEFT JOIN ref_barang b2 ON b2.id = td.id_barang_2
            LEFT JOIN ref_barang b3 ON b3.id = td.id_barang_3
            LEFT JOIN ref_barang b4 ON b4.id = td.id_barang_4
            LEFT JOIN ref_barang b5 ON b5.id = td.id_barang_5
            LEFT JOIN ref_barang b6 ON b6.id = td.id_barang_6
            LEFT JOIN ref_barang b7 ON b7.id = td.id_barang_7
            LEFT JOIN ref_barang b8 ON b8.id = td.id_barang_8

            -- JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di trans_sales_order_det
            LEFT JOIN ref_warna w1 ON w1.id = COALESCE(b1.id_warna, td.id_warna_1)
            LEFT JOIN ref_warna w2 ON w2.id = COALESCE(b2.id_warna, td.id_warna_2)
            LEFT JOIN ref_warna w3 ON w3.id = COALESCE(b3.id_warna, td.id_warna_3)
            LEFT JOIN ref_warna w4 ON w4.id = COALESCE(b4.id_warna, td.id_warna_4)
            LEFT JOIN ref_warna w5 ON w5.id = COALESCE(b5.id_warna, td.id_warna_5)
            LEFT JOIN ref_warna w6 ON w6.id = COALESCE(b6.id_warna, td.id_warna_6)
            LEFT JOIN ref_warna w7 ON w7.id = COALESCE(b7.id_warna, td.id_warna_7)
            LEFT JOIN ref_warna w8 ON w8.id = COALESCE(b8.id_warna, td.id_warna_8);
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

    function getTotalUkuranSO($id_sales_order = null)
    {
        $builder = $this->db->table("trans_sales_order_ukuran abx");

        $builder->select("COALESCE(SUM(abx.qty), 0) as qty, COALESCE(SUM(abx.harga_total), 0) as harga_total");


        $builder->where("abx.id_sales_order", $id_sales_order);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataUkuranSO($id = null)
    {
        $builder = $this->db->table("trans_sales_order_ukuran abx");
        $builder->select("abx.id, abx.qty, abx.id_ukuran, abx.harga_satuan, abx.id_sales_order, abx.id_sales_order_det, abx.harga_total");
        
        $builder->where("abx.id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataDODetail($ref_detail_id = null, $id_ukuran = null, $type = null)
    {
        $builder = $this->db->table("trans_delivery_detail abx");

        $builder->select("abx.id, abx.ref_detail_id, abx.id_ukuran, abx.qty, td.status, abx.id_delivery, td.id_invoice");


        $builder->join("trans_delivery td", "td.id = abx.id_delivery", 'inner');
        $builder->join("trans_produksi tp", "tp.id = td.id_produksi", 'inner');
        $builder->where("abx.ref_detail_id", $ref_detail_id);
        $builder->where("abx.id_ukuran", $id_ukuran);
        $builder->where("td.id_invoice $type", null);
        $builder->where("tp.tipe_id", 2);
        

        $builder->orderBy("td.id", 'desc');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDODetailSUM($ref_detail_id = null, $id_ukuran = null, $type = null)
    {
        $builder = $this->db->table("trans_delivery_detail abx");

        $builder->select("COALESCE(SUM(abx.qty),0) as qty");

        $builder->join("trans_delivery td", "td.id = abx.id_delivery", 'inner');
        $builder->where("abx.ref_detail_id", $ref_detail_id);
        $builder->where("abx.id_ukuran", $id_ukuran);
        $builder->where("td.id_invoice $type", null);
        $builder->where("td.active", 1);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataDOProd($ref_detail_id = null, $id_ukuran = null, $type = null)
    {
        $builder = $this->db->table("trans_delivery_prod abx");

        $builder->select("abx.id, abx.ref_detail_id, abx.id_ukuran, abx.qty,  abx.qty_do, td.status, abx.id_delivery");


        $builder->join("trans_delivery td", "td.id = abx.id_delivery", 'inner');
        $builder->join("trans_produksi tp", "tp.id = td.id_produksi", 'inner');
        $builder->where("abx.ref_detail_id", $ref_detail_id);
        $builder->where("abx.id_ukuran", $id_ukuran);
        // $builder->where("td.id_invoice $type", null);
        $builder->where("td.active", 1);
        $builder->where("tp.tipe_id", 2);
        $builder->orderBy("td.id", 'desc');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDO($id = null)
    {
        $builder = $this->db->table("trans_delivery abx");

        $builder->select("abx.id, abx.id_produksi, abx.id_konsumen, abx.qty,  abx.id_walkorder, abx.status, abx.id_invoice");


        $builder->where("abx.id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function deleteDataWOProsesUkuran($ref_detail_id = null, $id_ukuran = null, $type = null)
    {
        $sql = "
        DELETE FROM trans_walkorder_proses_ukuran abx
        USING trans_walkorder_proses twd, trans_walkorder tw
        WHERE abx.id_walkorder_proses = twd.id
        AND twd.id_walkorder = tw.id
        AND abx.ref_detail_id = ?
        AND abx.id_ukuran = ?
        AND tw.tipe_id = 2;
        ";

        return $this->db->query($sql, [$ref_detail_id, $id_ukuran]);
    }

    // function updateBarang($id_delivery = null, $item = null) {
    //     $getDOHead = $this->getDO($id_delivery);

    //     if (!empty($getDOHead)) {
    //         $mWalkorder = new WalkorderModel();
    //         $mBarang = new BarangModel();
    //         $dtWo = $mWalkorder->getData($getDOHead->id_walkorder);
    //         $params_b['nama_barang'] = $dtWo->keterangan_style;
    //         $dtBarang = $mBarang->getData(null, 0, 1, null, null, $params_b);
    //         if(!empty($dtBarang)){
    //             $id_gudang = $dtWo->id_gudang;
    //             $id_barang = $dtBarang[0]->id;

    //             $mBarangMasuk = new IncomingGoodsModel();
    //             $mBarangKeluar = new BarangKeluarModel();
    //             $arrParam =  [
    //                 "id_barang" => $id_barang,
    //                 "id_gudang" => $id_gudang,
    //             ];

    //             $resLotNo = $mBarangMasuk->getLotNo(null, $id_barang, null, $id_gudang);

    //             if (!empty($resLotNo)) {
    //                 $idLots = $resLotNo->id;
    //                 $mBarangKeluar->updateRecords('trans_lots', array("qty" => $resLotNo->qty - $item['qty']), array("id" => $idLots));
    //             } else {
    //                 $idLots = 0;
    //             }

    //             $resData = $mBarangMasuk->getLastStokBarangBalances($id_barang, $id_gudang, $idLots);

    //             // $stokAwal = !empty($resData) ? $resData->stok : 0;
    //             $dataBarang = [
    //                 "id_barang" => $id_barang,
    //                 "jenis_transaksi" => 2,
    //                 "jumlah" =>  $item['qty'],
    //                 "id_gudang_asal" =>  !empty($id_gudang) ? $id_gudang : null,
    //                 "nama" => 'Delivery',
    //                 "id_kategori" => 11,
    //                 "keterangan" => "Barang Keluar Produksi lewat delivery",
    //                 "active" => 1,
    //                 "tipe" => 1,
    //                 "lot_id" => $idLots,
    //                 "kode_transaksi" => $mBarangKeluar->generateKodePersediaan(),
    //             ];
    //             $mBarangKeluar->insertRecordGetid('trans_barang', $dataBarang);
                
    //             if (!empty($resData)) {
    //                 $stock = !empty($rowData['qty_exist']) ? $rowData['qty_exist'] + $rowData->qty : 0;
    //                 $mBarangKeluar->updateRecords('trans_barang_balances', array("saldo_akhir" => $stock), array("id" => $resData->id));
    //             }
    //         }
    //     }
    // }

    function getStatusSI($id = null)
    {
        $builder = $this->db->table("trans_invoice abx");
        $builder->select("abx.id, abx.status");
        
        $builder->whereIn("abx.id", $id);
        $this->_data = $builder->get()->getResult();
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
            // if (!empty($dataWarna['id']) && ($dataWarna['id_sales_order'])) {
            //     $arrDelete = [
            //         "id_sales_order" => $dataWarna['id_sales_order'],
            //         "id_sales_order_det" => $dataWarna['id']
            //     ];
            //     $this->deleteRecordMultipleColumn("trans_sales_order_ukuran", $arrDelete);
            // }

            $head_qty = 0;
            $head_total = 0;
            $queryStatus = true;
            foreach ($dataUkuran as $key => $rowData) {
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

                $ukuranId = !empty($rowData['id']) ? $rowData['id'] : null;
                $getSOUkuran = $this->getDataUkuranSO($ukuranId);
                if (!empty($getSOUkuran)) {
                    $getDODet = $this->getDataDODetail($dataWarna['id'], $rowData['id_ukuran'], "<>");
                    if (count($getDODet) > 0) {
                        $getDODetSUM = $this->getDODetailSUM($dataWarna['id'], $rowData['id_ukuran'], "<>");
                        $getDODetSUMDO = $this->getDODetailSUM($dataWarna['id'], $rowData['id_ukuran'], "=");
                        if ((float)$getSOUkuran->harga_satuan != (float) $rowData['harga_satuan']) {
                            $all_ids = array_column($getDODet, 'id_invoice');
                            $unique_ids = array_unique($all_ids);

                            if (!empty($all_ids)) {
                                // 2. Panggil fungsi getStatusSI
                                $statusData = $this->getStatusSI($unique_ids);
                                
                                // 3. Ambil semua nilai status saja dalam bentuk array [0, 1, 0, ...]
                                $allStatuses = array_column($statusData, 'status');

                                // 4. CEK: Jika ada angka 1 di dalam array tersebut
                                if (in_array(1, $allStatuses) || in_array("1", $allStatuses)) {
                                    $queryStatus = false;
                                    $msgError = 'Invoice sudah Approve, Tidak dapat merubah HARGA!';
                                    break;
                                }
                            }
                            if ($queryStatus == false) {
                                $queryStatus = false;
                                $msgError = 'Invoice sudah Approve, Tidak dapat merubah HARGA!';
                                break;
                            }
                        }
                        else if ((int)$rowData['qty'] < (int)$getSOUkuran->qty && ((int)$rowData['qty'] < ((int)$getDODetSUMDO->qty  + (int)$getDODetSUM->qty))) {
                            $queryStatus = false;
                            $msgError = 'Tidak dapat mengubah QTY <strong>lebih kecil dari '.(int)$getDODetSUMDO->qty  + (int)$getDODetSUM->qty.'</strong>! Total QTY Delivery Order pada item ini sudah mencapai batas minimal!';
                            break;
                        }
                    }

                    // if ((int)$rowData['qty'] < (int)$getSOUkuran->qty && $queryStatus) {
                    //     $getDODetSUMInvoice = $this->getDODetailSUM($dataWarna['id'], $rowData['id_ukuran'], "<>");
                    //     $getDODetSUMDO = $this->getDODetailSUM($dataWarna['id'], $rowData['id_ukuran'], "=");
                    //     if ((int)$rowData['qty'] < ((int)$getDODetSUMDO->qty  + (int)$getDODetSUMInvoice->qty)) {
                    //         $selisih_total = ((int)$getDODetSUMDO->qty  + (int)$getDODetSUMInvoice->qty) - (int)$rowData['qty'];
                    //         $getDODet = $this->getDataDODetail($dataWarna['id'], $rowData['id_ukuran'], "=");
                    //         $id_delivery = null;
                    //         foreach ($getDODet as $keyDODET => $valueDODET) {
                    //             $id_delivery = $valueDODET->id_delivery;
                    //             if ($selisih_total == 0) {
                    //                 break;
                    //             }
                    //             else if ($selisih_total < (int)$valueDODET->qty) {
                    //                 $arr_do_det = [
                    //                     "qty" => (int)$valueDODET->qty - 1,
                    //                 ];
                    //                 $selisih_total--;
                    //                 $this->updateRecord("trans_delivery_detail", $arr_do_det, 'id', $valueDODET->id);
                    //             }
                    //             else {
                    //                 $selisih_total--;
                    //                 $this->deleteRecord("trans_delivery_detail", 'id', $valueDODET->id);
                    //             }
                    //         }
                            
                    //         $selisih_total = ((int)$getDODetSUMDO->qty  + (int)$getDODetSUMInvoice->qty) - (int)$rowData['qty'];
                    //         $getDOPROD = $this->getDataDOProd($dataWarna['id'], $rowData['id_ukuran'], "=");
                    //         foreach ($getDOPROD as $keyDOPROD => $valueDOPROD) {
                    //             if ($selisih_total == 0) {
                    //                 break;
                    //             }
                    //             else if ((int)$valueDOPROD->qty_do == 0) {
                    //                 continue;
                    //             }
                    //             else if ($selisih_total < (int)$valueDOPROD->qty_do && (int)$valueDOPROD->qty_do > 1) {
                    //                 $arr_do_produksi = [
                    //                     "qty_do" => (int)$valueDOPROD->qty_do - 1,
                    //                 ];
                    //                 $selisih_total--;
                    //                 $this->updateRecord("trans_delivery_prod", $arr_do_produksi, 'id', $valueDOPROD->id);
                    //             }
                    //             else if ($selisih_total >= (int)$valueDOPROD->qty_do && $selisih_total > 0) {
                    //                 $arr_do_produksi = [
                    //                     "qty_do" => 0,
                    //                     "harga_satuan" => 0,
                    //                 ];
                    //                 $selisih_total--;
                    //                 $this->updateRecord("trans_delivery_prod", $arr_do_produksi, 'id', $valueDOPROD->id);
                    //             }
                    //         }

                    //         if ($id_delivery) {
                    //             $sumQtyDo = $this->db->table('trans_delivery_detail')
                    //                 ->selectSum('qty')
                    //                 ->where('id_delivery', $id_delivery)
                    //                 ->get()
                    //                 ->getRow();

                    //             $parentUpdate = [
                    //                 'qty' => (int)$sumQtyDo->qty
                    //             ];

                    //             $this->updateRecord("trans_delivery", $parentUpdate, 'id', $id_delivery);
                                
                    //         }
                            
                    //     }
                    // }

                    if ($rowData['qty'] == 0) {
                        $arr_do_prod = [
                            "id_ukuran" => $rowData['id_ukuran'],
                            "ref_detail_id" => $arrDataUkuran['id_sales_order_det'],
                        ];
                        $this->deleteRecordMultipleColumn("trans_delivery_prod", $arr_do_prod);
                        $this->deleteRecordMultipleColumn("trans_delivery_detail", $arr_do_prod);

                        $deleteWOProsesUkuran = $this->deleteDataWOProsesUkuran($dataWarna['id'], $rowData['id_ukuran']);

                        $this->deleteRecord("trans_sales_order_ukuran", "id", $rowData['id']);
                    }
                    else {
                        $getDODetSUM = $this->getDODetailSUM($dataWarna['id'], $rowData['id_ukuran'], "<>");
                        $getDODetSUMDO = $this->getDODetailSUM($dataWarna['id'], $rowData['id_ukuran'], "=");
                        if ((int)$rowData['qty'] < (int)$getSOUkuran->qty && ((int)$rowData['qty'] < ((int)$getDODetSUMDO->qty  + (int)$getDODetSUM->qty))) {
                            $queryStatus = false;
                            $msgError = 'Tidak dapat mengubah QTY <strong>lebih kecil dari '.(int)$getDODetSUMDO->qty  + (int)$getDODetSUM->qty.'</strong>! Total QTY Delivery Order pada item ini sudah mencapai batas minimal!';
                            break;
                        }

                        $getDOProd = $this->getDataDOProd($dataWarna['id'], $rowData['id_ukuran']);
                        foreach ($getDOProd as $keyDO => $value) {
                            $arr_do_prod = [
                                "harga_satuan" => (float)$rowData['harga_satuan'],
                                "qty" => $rowData['qty'],
                            ];
                            $this->updateRecord("trans_delivery_prod", $arr_do_prod, 'id', $value->id);
                        }
                        
                        $this->updateRecord("trans_sales_order_ukuran", $arrDataUkuran, 'id', $getSOUkuran->id);
                    }
                }
                else {
                    $this->insertRecordGetid("trans_sales_order_ukuran", $arrDataUkuran);
                }
            }

            if ($queryStatus == false) {
                $this->db->transRollback();
                return $msgError;
            }
            // update data qty dan total harga 
            // $head_up['qty'] = $head_qty;
            // $head_up['total_harga'] = $head_total;
            // $this->updateRecord($this->table, $head_up, 'id', $dataWarna['id_sales_order']);

            // $head_up_wo['qty'] = $head_qty;
            // $this->updateRecord("trans_walkorder", $head_up_wo, 'ref_id', $dataWarna['id_sales_order']);

            
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
        $builder->select(" 
            tu.id as id_ukuran_so, 
            tu.id_ukuran,
            ru.kode_ukuran, 
            ru.key_ukuran, 
            w1.kode_warna as warna_1, w2.kode_warna as warna_2, w3.kode_warna as warna_3,
            w4.kode_warna as warna_4, w5.kode_warna as warna_5, w6.kode_warna as warna_6,
            w7.kode_warna as warna_7, w8.kode_warna as warna_8,
            tod.kode_sales_order, tod.style, tod.deskripsi, tu.id_sales_order,
            -- Flag sumber warna
            CASE 
                WHEN td.id_barang_1 IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna
        ");

        $builder->join("trans_sales_order_det td", "td.id = tu.id_sales_order_det", "inner");
        $builder->join("trans_sales_order tod",    "tod.id = td.id_sales_order",    "inner");
        $builder->join("ref_ukuran ru",            "tu.id_ukuran = ru.id",          "left");

        // JOIN ref_barang (nullable)
        $builder->join("ref_barang b1", "td.id_barang_1 = b1.id", "left");
        $builder->join("ref_barang b2", "td.id_barang_2 = b2.id", "left");
        $builder->join("ref_barang b3", "td.id_barang_3 = b3.id", "left");
        $builder->join("ref_barang b4", "td.id_barang_4 = b4.id", "left");
        $builder->join("ref_barang b5", "td.id_barang_5 = b5.id", "left");
        $builder->join("ref_barang b6", "td.id_barang_6 = b6.id", "left");
        $builder->join("ref_barang b7", "td.id_barang_7 = b7.id", "left");
        $builder->join("ref_barang b8", "td.id_barang_8 = b8.id", "left");

        // JOIN ref_warna dengan COALESCE: prioritaskan warna dari ref_barang, fallback ke trans_sales_order_det
        $builder->join("ref_warna w1", "COALESCE(b1.id_warna, td.id_warna_1) = w1.id", "left");
        $builder->join("ref_warna w2", "COALESCE(b2.id_warna, td.id_warna_2) = w2.id", "left");
        $builder->join("ref_warna w3", "COALESCE(b3.id_warna, td.id_warna_3) = w3.id", "left");
        $builder->join("ref_warna w4", "COALESCE(b4.id_warna, td.id_warna_4) = w4.id", "left");
        $builder->join("ref_warna w5", "COALESCE(b5.id_warna, td.id_warna_5) = w5.id", "left");
        $builder->join("ref_warna w6", "COALESCE(b6.id_warna, td.id_warna_6) = w6.id", "left");
        $builder->join("ref_warna w7", "COALESCE(b7.id_warna, td.id_warna_7) = w7.id", "left");
        $builder->join("ref_warna w8", "COALESCE(b8.id_warna, td.id_warna_8) = w8.id", "left");

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

        $lwoSub = "
            (
                SELECT DISTINCT ON (tso_1.id)
                    tso_1.id AS order_id,
                    wp.id_walkorder
                FROM trans_walkorder_proses_ukuran xk
                JOIN trans_walkorder_proses wp ON wp.id = xk.id_walkorder_proses
                JOIN trans_walkorder xp ON xp.id = wp.id_walkorder
                JOIN trans_sales_order tso_1 ON xp.ref_id = tso_1.id
                WHERE xp.tipe_id = 2
                ORDER BY tso_1.id, wp.id_walkorder DESC
            ) as lwo
            ";

        $builder->select("abx.id, abx.kode_sales_order, abx.deskripsi, bbx.nama, abx.tgl_transaksi, abx.tgl_deadline, abx.tgl_deadline_dua,
                          abx.uang_dp, abx.uang_dp_2, abx.style as stylex , abx.style_cnt, abx.status,
                          concat(abx.style,' - ', abx.style_cnt) as style, concat(abx.style, '  (', abx.style_cnt, ')') as style_print, abx.tgl_dp,
                          COALESCE((
                                SELECT SUM(tsou.qty)
                                FROM trans_sales_order_ukuran tsou
                                WHERE tsou.id_sales_order = abx.id
                            ), 0) as qty,
                            ( SELECT tp.id
                                FROM trans_produksi tp
                                WHERE tp.id_walkorder = lwo.id_walkorder
                                ORDER BY tp.id DESC
                            LIMIT 1) AS id_prod,
                            (
                                SELECT tp.id_walkorder
                                FROM trans_produksi tp
                                WHERE tp.id_walkorder = lwo.id_walkorder
                                ORDER BY tp.id DESC
                                LIMIT 1
                            ) AS id_walkorder_prod,
                            COALESCE((
                                SELECT sum(xk.qty_prod)
                                FROM trans_walkorder_proses_ukuran xk
                                INNER JOIN trans_walkorder_proses wp ON wp.id = xk.id_walkorder_proses
                                INNER JOIN trans_walkorder xp ON xp.id = wp.id_walkorder
                                WHERE xp.ref_id = abx.id AND xp.tipe_id = 2
                                AND wp.id_proses = (( SELECT min(wp2.id_proses) AS min
                                    FROM trans_walkorder_proses wp2
                                    WHERE wp2.id_walkorder = xp.id))
                            ), 0) as qty_prod,
                            COALESCE((
                                SELECT SUM(tdd.qty)
                                FROM trans_delivery_detail tdd
                                INNER JOIN trans_delivery td on tdd.id_delivery = td.id
                                INNER JOIN trans_walkorder tw on td.id_walkorder = tw.id
                                WHERE tw.ref_id = abx.id and tw.tipe_id = 2
                                AND td.active = 1
                            ), 0) as qty_do,
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
        $builder->join($lwoSub, "lwo.order_id = abx.id", "left");
       
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
