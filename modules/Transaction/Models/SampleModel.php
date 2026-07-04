<?php

namespace Modules\Transaction\Models;

class SampleModel extends \App\Models\PrModel
{

    protected $table = "trans_sample";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.kode_sample,abx.status, abx.deskripsi, bbx.nama, abx.id_konsumen, abx.keterangan, abx.tgl_transaksi, abx.tgl_deadline, abx.status, 
                          abx.gambar_id,cbx.file_name, abx.uang_dp, abx.style, abx.qty, abx.style_cnt, concat(abx.style, '  (', abx.style_cnt, ')') as style_print");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("_files cbx", "abx.gambar_id = cbx.id", "left");
        if ($id == null or $id == "") {
            if(empty($params['activedt'])){
                $builder->where('abx.active = 1');
            }
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_sample) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
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

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->select("count(1) as _cnt");
        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(abx.kode_sample) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetailSampleIdByIdSample($id)
    {
        $builder = $this->db->table("trans_sample_det" . " abx");
        $builder->select("abx.id");
        $builder->where("abx.id_sample", $id);
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataDetailSampleById($id)
    {
        $builder = $this->db->table("trans_sample_det" . " abx");
        $builder->where("abx.id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataUkuran($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null, $idProses = null)
    {
        $builder = $this->db->table("trans_sample_ukuran abx");

        $builder->select("abx.id,xb.id_walkorder_proses,abx.id,bbx.id as id_ukuran, dbx.id as id_warna, bbx.kode_ukuran,dbx.kode_warna, abx.qty, abx.harga_satuan, x.keterangan_style");
        $builder->join("ref_ukuran bbx", "abx.id_ukuran = bbx.id", "inner");
        $builder->join("trans_sample_det cbx", "abx.id_sample_det = cbx.id AND abx.id_sample = cbx.id_sample ", "inner");
        $builder->join("ref_warna dbx", "cbx.id_warna_1 = bbx.id", "inner");
        $builder->join("trans_walkorder x", "x.ref_id = abx.id_sample AND x.tipe_id = 1", "left");
        $builder->join("trans_walkorder_proses xa", "x.id = xa.id_walkorder AND xa.id_proses = $idProses", "left");
        $builder->join("trans_walkorder_proses_ukuran xb", "xa.id = xb.id_walkorder_proses", "left");
        $builder->groupBy("abx.id");
        $builder->groupBy("dbx.kode_warna");
        $builder->groupBy("x.keterangan_style");
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

            if (!empty($params['id_sample'])) {
                $builder->where('abx.id_sample', $params['id_sample']);
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

    function getDataUkuranCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("trans_sample_ukuran abx");

        $builder->join("ref_ukuran bbx", "abx.id_ukuran = bbx.id", "inner");
        $builder->join("trans_sample_det cbx", "abx.id_sample_det = cbx.id AND abx.id_sample = cbx.id_sample ", "inner");
        $builder->join("ref_warna dbx", "cbx.id_warna_1 = dbx.id", "inner");

        $builder->select("count(1) as _cnt");
        if (!empty($params['id_sample'])) {
            $builder->where('abx.id_sample', $params['id_sample']);
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

    function getDataDetailSample($idSample)
    {
        $builder = $this->db->table("trans_sample_det" . " abx");
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
        $builder->join("trans_sample_ukuran bbx", "abx.id = bbx.id_sample_det AND abx.id_sample = bbx.id_sample", "inner");
        $builder->join("ref_ukuran cbx", "bbx.id_ukuran = cbx.id", "left");
        $builder->join("ref_warna w1", "abx.id_warna_1 = w1.id", "left");
        $builder->join("ref_warna w2", "abx.id_warna_2 = w2.id", "left");
        $builder->join("ref_warna w3", "abx.id_warna_3 = w3.id", "left");
        $builder->join("ref_warna w4", "abx.id_warna_4 = w4.id", "left");
        $builder->join("ref_warna w5", "abx.id_warna_5 = w5.id", "left");
        $builder->join("ref_warna w6", "abx.id_warna_6 = w6.id", "left");
        $builder->join("ref_warna w7", "abx.id_warna_7 = w7.id", "left");
        $builder->join("ref_warna w8", "abx.id_warna_8 = w8.id", "left");
        $builder->where("abx.id_sample", $idSample);
        $builder->groupBy(array("abx.id", "w1.kode_warna", "w2.kode_warna", "w3.kode_warna", "w4.kode_warna", "w5.kode_warna", "w6.kode_warna", "w7.kode_warna", "w8.kode_warna"));
        $this->_data = $builder->get()->getResult();

        $s_data = [];

        // foreach ($this->_data as $td) {
        //     $td->print_barcode = '<button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-print-barcode"> <i class="fa fa-print"></i></button>';
        //     array_push($s_data, $td);
        // }

        return $this->_data;
    }

    function getDataDetailSample_crostab($id)
    {


        // get data ukuran 
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $id;
        $dtUkuran = $this->getUkuranTrans($pru);

        // looping data ukuran
        // Dynamic Columns
        $col11 = "";
        $col12 = "";
        $col21 = "";
        $col22 = "";
        $col3 = "";

        foreach ($dtUkuran as $item) {
            $key = $item->key_ukuran;
            if ($key == 'all') $key = 'all_';
            $hrg = $key . '_hrg';
            $col11 .= ($col11 == "") ? "coalesce(tbl.$key,  0) as $key" : ",coalesce(tbl.$key, 0) as $key";
            //  $col12 .= ($col12 == "") ? "coalesce(tbl.$hrg,0) as $hrg" : ",coalesce(tbl.$hrg,0) as $hrg";

            $col21 .= ($col21 == "") ? "$key INT" : ",$key INT";

            $col3 .= ($col3 == "") ? $key : "," . $key;
            //  $col22 .= ($col22 == "") ? "$hrg Float" : ",$hrg Float";
        }

        $sql = "
            SELECT 
                tbl.id,
                ROW_NUMBER() OVER (ORDER BY tbl.id) AS no,

                CASE 
                    WHEN td.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                w1.kode_warna AS colorDasar,

                -- colour: tetap dari kode_warna
                TRIM(BOTH ' - ' FROM 
                    COALESCE(w1.kode_warna, '') || 
                    CASE WHEN w2.kode_warna IS NOT NULL THEN ' - ' || w2.kode_warna ELSE '' END ||
                    CASE WHEN w3.kode_warna IS NOT NULL THEN ' - ' || w3.kode_warna ELSE '' END ||
                    CASE WHEN w4.kode_warna IS NOT NULL THEN ' - ' || w4.kode_warna ELSE '' END ||
                    CASE WHEN w5.kode_warna IS NOT NULL THEN ' - ' || w5.kode_warna ELSE '' END ||
                    CASE WHEN w6.kode_warna IS NOT NULL THEN ' - ' || w6.kode_warna ELSE '' END ||
                    CASE WHEN w7.kode_warna IS NOT NULL THEN ' - ' || w7.kode_warna ELSE '' END ||
                    CASE WHEN w8.kode_warna IS NOT NULL THEN ' - ' || w8.kode_warna ELSE '' END 
                ) AS colorsampledasar,
                 TRIM(BOTH '~' FROM
                    COALESCE(w1.kode_warna, '') ||
                    CASE WHEN w2.kode_warna IS NOT NULL THEN '~' || w2.kode_warna ELSE '' END ||
                    CASE WHEN w3.kode_warna IS NOT NULL THEN '~' || w3.kode_warna ELSE '' END ||
                    CASE WHEN w4.kode_warna IS NOT NULL THEN '~' || w4.kode_warna ELSE '' END ||
                    CASE WHEN w5.kode_warna IS NOT NULL THEN '~' || w5.kode_warna ELSE '' END ||
                    CASE WHEN w6.kode_warna IS NOT NULL THEN '~' || w6.kode_warna ELSE '' END ||
                    CASE WHEN w7.kode_warna IS NOT NULL THEN '~' || w7.kode_warna ELSE '' END ||
                    CASE WHEN w8.kode_warna IS NOT NULL THEN '~' || w8.kode_warna ELSE '' END
                ) AS colour_warna,
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
                tso.kode_sample as kode_so,
                -- keterangan: ambil dari ref_barang jika ada, fallback ke kode_warna ref_warna
                TRIM(BOTH ' ~ ' FROM
                    CASE 
                        WHEN b1.id IS NOT NULL THEN COALESCE(NULLIF(b1.keterangan, ''), COALESCE(w1.keterangan, '')) 
                        ELSE COALESCE(w1.keterangan, '') 
                    END ||
                    CASE 
                        WHEN b2.id IS NOT NULL AND NULLIF(b2.keterangan, '') IS NOT NULL THEN ' ~ ' || b2.keterangan
                        WHEN b2.id IS NOT NULL AND NULLIF(b2.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w2.keterangan, ''), '')
                        WHEN b2.id IS NULL     AND w2.keterangan IS NOT NULL             THEN ' ~ ' || w2.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b3.id IS NOT NULL AND NULLIF(b3.keterangan, '') IS NOT NULL THEN ' ~ ' || b3.keterangan
                        WHEN b3.id IS NOT NULL AND NULLIF(b3.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w3.keterangan, ''), '')
                        WHEN b3.id IS NULL     AND w3.keterangan IS NOT NULL             THEN ' ~ ' || w3.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b4.id IS NOT NULL AND NULLIF(b4.keterangan, '') IS NOT NULL THEN ' ~ ' || b4.keterangan
                        WHEN b4.id IS NOT NULL AND NULLIF(b4.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w4.keterangan, ''), '')
                        WHEN b4.id IS NULL     AND w4.keterangan IS NOT NULL             THEN ' ~ ' || w4.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b5.id IS NOT NULL AND NULLIF(b5.keterangan, '') IS NOT NULL THEN ' ~ ' || b5.keterangan
                        WHEN b5.id IS NOT NULL AND NULLIF(b5.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w5.keterangan, ''), '')
                        WHEN b5.id IS NULL     AND w5.keterangan IS NOT NULL             THEN ' ~ ' || w5.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b6.id IS NOT NULL AND NULLIF(b6.keterangan, '') IS NOT NULL THEN ' ~ ' || b6.keterangan
                        WHEN b6.id IS NOT NULL AND NULLIF(b6.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w6.keterangan, ''), '')
                        WHEN b6.id IS NULL     AND w6.keterangan IS NOT NULL             THEN ' ~ ' || w6.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b7.id IS NOT NULL AND NULLIF(b7.keterangan, '') IS NOT NULL THEN ' ~ ' || b7.keterangan
                        WHEN b7.id IS NOT NULL AND NULLIF(b7.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w7.keterangan, ''), '')
                        WHEN b7.id IS NULL     AND w7.keterangan IS NOT NULL             THEN ' ~ ' || w7.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b8.id IS NOT NULL AND NULLIF(b8.keterangan, '') IS NOT NULL THEN ' ~ ' || b8.keterangan
                        WHEN b8.id IS NOT NULL AND NULLIF(b8.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w8.keterangan, ''), '')
                        WHEN b8.id IS NULL     AND w8.keterangan IS NOT NULL             THEN ' ~ ' || w8.keterangan ELSE '' 
                    END
                ) AS keterangan,

                {$col11},
                COALESCE(th.total_harga, 0) AS total_harga

            FROM 
                CROSSTAB(
                    $$ 
                    SELECT 
                        td.id,
                        ru.seq,
                        (CASE WHEN ru.key_ukuran = 'all' THEN 'all_' ELSE ru.key_ukuran END) AS key_ukuran,
                        SUM(COALESCE(tu.qty, 0)) AS qty
                    FROM 
                        trans_sample_ukuran tu 
                    INNER JOIN trans_sample_det td ON td.id = tu.id_sample_det
                    INNER JOIN ref_ukuran ru ON ru.id = tu.id_ukuran
                    WHERE (tu.qty IS NOT NULL AND tu.qty > 0) AND td.id_sample = {$id}
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
            INNER JOIN trans_sample_det td ON td.id = tbl.id
            INNER JOIN trans_sample tso ON tso.id = td.id_sample
            LEFT JOIN (
                SELECT id_sample_det, SUM(harga_total) AS total_harga
                FROM trans_sample_ukuran
                GROUP BY id_sample_det
            ) AS th ON th.id_sample_det = tbl.id
            LEFT JOIN ref_barang b1 ON td.id_barang_1 = b1.id
            LEFT JOIN ref_barang b2 ON td.id_barang_2 = b2.id
            LEFT JOIN ref_barang b3 ON td.id_barang_3 = b3.id
            LEFT JOIN ref_barang b4 ON td.id_barang_4 = b4.id
            LEFT JOIN ref_barang b5 ON td.id_barang_5 = b5.id
            LEFT JOIN ref_barang b6 ON td.id_barang_6 = b6.id
            LEFT JOIN ref_barang b7 ON td.id_barang_7 = b7.id
            LEFT JOIN ref_barang b8 ON td.id_barang_8 = b8.id
            LEFT JOIN ref_warna w1 ON COALESCE(b1.id_warna, td.id_warna_1) = w1.id
            LEFT JOIN ref_warna w2 ON COALESCE(b2.id_warna, td.id_warna_2) = w2.id
            LEFT JOIN ref_warna w3 ON COALESCE(b3.id_warna, td.id_warna_3) = w3.id
            LEFT JOIN ref_warna w4 ON COALESCE(b4.id_warna, td.id_warna_4) = w4.id
            LEFT JOIN ref_warna w5 ON COALESCE(b5.id_warna, td.id_warna_5) = w5.id
            LEFT JOIN ref_warna w6 ON COALESCE(b6.id_warna, td.id_warna_6) = w6.id
            LEFT JOIN ref_warna w7 ON COALESCE(b7.id_warna, td.id_warna_7) = w7.id
            LEFT JOIN ref_warna w8 ON COALESCE(b8.id_warna, td.id_warna_8) = w8.id;
        ";

        $query = $this->db->query($sql);
        $this->_data = $query->getResult();

        return $this->_data;
    }

    function getDataDetailSample_crostab_si($id, $idInvoice = null)
    {
        $pru['use'] = 1;
        $pru['id_sample'] = $id;
        $dtUkuran = $this->getUkuranTrans($pru);
        if (empty($dtUkuran)) {
            return null;
        }

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

            // Subquery col_harga untuk sample
            $subHarga = "
                COALESCE(
                    (
                        SELECT tsu.harga_satuan
                        FROM trans_sample_ukuran tsu 
                        INNER JOIN ref_ukuran ru ON ru.id = tsu.id_ukuran
                        INNER JOIN trans_sample_det tsd ON tsd.id = tsu.id_sample_det

                        -- JOIN ref_barang untuk subquery col_harga
                        LEFT JOIN ref_barang bsub1 ON bsub1.id = tsd.id_barang_1
                        LEFT JOIN ref_barang bsub2 ON bsub2.id = tsd.id_barang_2
                        LEFT JOIN ref_barang bsub3 ON bsub3.id = tsd.id_barang_3
                        LEFT JOIN ref_barang bsub4 ON bsub4.id = tsd.id_barang_4
                        LEFT JOIN ref_barang bsub5 ON bsub5.id = tsd.id_barang_5
                        LEFT JOIN ref_barang bsub6 ON bsub6.id = tsd.id_barang_6
                        LEFT JOIN ref_barang bsub7 ON bsub7.id = tsd.id_barang_7
                        LEFT JOIN ref_barang bsub8 ON bsub8.id = tsd.id_barang_8

                        -- JOIN ref_warna dengan COALESCE ref_barang, fallback ke tsd
                        LEFT JOIN ref_warna rw1 ON rw1.id = COALESCE(bsub1.id_warna, tsd.id_warna_1)
                        LEFT JOIN ref_warna rw2 ON rw2.id = COALESCE(bsub2.id_warna, tsd.id_warna_2)
                        LEFT JOIN ref_warna rw3 ON rw3.id = COALESCE(bsub3.id_warna, tsd.id_warna_3)
                        LEFT JOIN ref_warna rw4 ON rw4.id = COALESCE(bsub4.id_warna, tsd.id_warna_4)
                        LEFT JOIN ref_warna rw5 ON rw5.id = COALESCE(bsub5.id_warna, tsd.id_warna_5)
                        LEFT JOIN ref_warna rw6 ON rw6.id = COALESCE(bsub6.id_warna, tsd.id_warna_6)
                        LEFT JOIN ref_warna rw7 ON rw7.id = COALESCE(bsub7.id_warna, tsd.id_warna_7)
                        LEFT JOIN ref_warna rw8 ON rw8.id = COALESCE(bsub8.id_warna, tsd.id_warna_8)

                        WHERE tsu.id_sample_det = tbl.id
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
                    CASE 
                        WHEN b1.id IS NOT NULL THEN COALESCE(NULLIF(b1.keterangan, ''), COALESCE(w1.keterangan, '')) 
                        ELSE COALESCE(w1.keterangan, '') 
                    END ||
                    CASE 
                        WHEN b2.id IS NOT NULL AND NULLIF(b2.keterangan, '') IS NOT NULL THEN ' ~ ' || b2.keterangan
                        WHEN b2.id IS NOT NULL AND NULLIF(b2.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w2.keterangan, ''), '')
                        WHEN b2.id IS NULL     AND w2.keterangan IS NOT NULL             THEN ' ~ ' || w2.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b3.id IS NOT NULL AND NULLIF(b3.keterangan, '') IS NOT NULL THEN ' ~ ' || b3.keterangan
                        WHEN b3.id IS NOT NULL AND NULLIF(b3.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w3.keterangan, ''), '')
                        WHEN b3.id IS NULL     AND w3.keterangan IS NOT NULL             THEN ' ~ ' || w3.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b4.id IS NOT NULL AND NULLIF(b4.keterangan, '') IS NOT NULL THEN ' ~ ' || b4.keterangan
                        WHEN b4.id IS NOT NULL AND NULLIF(b4.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w4.keterangan, ''), '')
                        WHEN b4.id IS NULL     AND w4.keterangan IS NOT NULL             THEN ' ~ ' || w4.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b5.id IS NOT NULL AND NULLIF(b5.keterangan, '') IS NOT NULL THEN ' ~ ' || b5.keterangan
                        WHEN b5.id IS NOT NULL AND NULLIF(b5.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w5.keterangan, ''), '')
                        WHEN b5.id IS NULL     AND w5.keterangan IS NOT NULL             THEN ' ~ ' || w5.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b6.id IS NOT NULL AND NULLIF(b6.keterangan, '') IS NOT NULL THEN ' ~ ' || b6.keterangan
                        WHEN b6.id IS NOT NULL AND NULLIF(b6.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w6.keterangan, ''), '')
                        WHEN b6.id IS NULL     AND w6.keterangan IS NOT NULL             THEN ' ~ ' || w6.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b7.id IS NOT NULL AND NULLIF(b7.keterangan, '') IS NOT NULL THEN ' ~ ' || b7.keterangan
                        WHEN b7.id IS NOT NULL AND NULLIF(b7.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w7.keterangan, ''), '')
                        WHEN b7.id IS NULL     AND w7.keterangan IS NOT NULL             THEN ' ~ ' || w7.keterangan ELSE '' 
                    END ||
                    CASE 
                        WHEN b8.id IS NOT NULL AND NULLIF(b8.keterangan, '') IS NOT NULL THEN ' ~ ' || b8.keterangan
                        WHEN b8.id IS NOT NULL AND NULLIF(b8.keterangan, '') IS NULL     THEN COALESCE(' ~ ' || NULLIF(w8.keterangan, ''), '')
                        WHEN b8.id IS NULL     AND w8.keterangan IS NOT NULL             THEN ' ~ ' || w8.keterangan ELSE '' 
                    END
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
                    INNER JOIN trans_sample_det td ON td.id = tdd.ref_detail_id
                    INNER JOIN ref_ukuran ru ON ru.id = tdd.id_ukuran
                    WHERE td.id_sample = {$id}
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
            INNER JOIN trans_sample_det td ON td.id = tbl.id

            -- JOIN ref_barang untuk query utama
            LEFT JOIN ref_barang b1 ON b1.id = td.id_barang_1
            LEFT JOIN ref_barang b2 ON b2.id = td.id_barang_2
            LEFT JOIN ref_barang b3 ON b3.id = td.id_barang_3
            LEFT JOIN ref_barang b4 ON b4.id = td.id_barang_4
            LEFT JOIN ref_barang b5 ON b5.id = td.id_barang_5
            LEFT JOIN ref_barang b6 ON b6.id = td.id_barang_6
            LEFT JOIN ref_barang b7 ON b7.id = td.id_barang_7
            LEFT JOIN ref_barang b8 ON b8.id = td.id_barang_8

            -- JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di trans_sample_det
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

    function getDataSample($kodeSample)
    {
        $builder = $this->db->table($this->table);
        $builder->select("id");
        $builder->where("kode_sample", $kodeSample);

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getUkuranTrans($params)
    {
        $builder = $this->db->table('trans_sample_ukuran tu');
        $builder->select("tu.id_ukuran, rk.key_ukuran, rk.kode_ukuran, tu.id_sample");

        $builder->join('trans_sample_det td', 'td.id = tu.id_sample_det', 'inner');
        $builder->join('ref_ukuran rk', 'tu.id_ukuran = rk.id', 'inner');

        if (!empty($params['use'])) {
            $builder->where('(tu.qty is not null and tu.qty > 0)');
        }

        if (!empty($params['id_sample'])) {
            $builder->where('tu.id_sample', $params['id_sample']);
        }

        $builder->groupBy("tu.id_ukuran, rk.key_ukuran, rk.kode_ukuran, rk.seq, tu.id_sample");

        $builder->orderBy("rk.seq");

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDetailGramasi($sampleDetId = null, $idBarang = null, $idWarna = null) {
        $builder = $this->db->table('trans_sample_gram tdg');

        $id_gudang = !empty($params['id_gudang']) ? $params['id_gudang'] : null;

        $subQtyOnHand = "COALESCE((
            SELECT tbh.jumlah
            FROM trans_barang_history tbh
            WHERE tbh.id_barang = tdg.id_barang
            " . (!empty($id_gudang) ? "AND tbh.id_gudang = $id_gudang" : "") . "
            ORDER BY tbh.year DESC, tbh.month DESC
            LIMIT 1
        ), 0) AS kuota_history";

        $builder->select(" 
            tdg.id, 
            tdg.id_warna, 
            tdg.id_barang,
            tdg.qty,
            tdg.gram, tdg.gram_nd, tdg.kg, tdg.loss, tdg.kg_loss, tdg.total,
            COALESCE(rb.nama_barang, rw.kode_warna) AS kode_warna,
            rw.keterangan as warna_keterangan,

            -- Flag sumber warna
            CASE 
                WHEN tdg.id_barang IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna,

            -- Qty on hand dari trans_barang_history
            {$subQtyOnHand}
        ");

        // JOIN ref_barang (nullable)
        $builder->join("ref_barang rb", "rb.id = tdg.id_barang", "left");

        // JOIN ref_warna: prioritaskan warna dari ref_barang, fallback ke id_warna di abx
        $builder->join("ref_warna rw", "rw.id = COALESCE(rb.id_warna, tdg.id_warna)", "left");

        if (!empty($sampleDetId)) {
            $builder->where('tdg.id_sample_det', $sampleDetId);
        }

        if (!empty($idBarang)) {
            $builder->where('tdg.id_barang', $idBarang);
        }

        if (!empty($idWarna)) {
            $builder->where('tdg.id_warna', $idWarna);
        }
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }


    function getDataDetailSample_ori($idSample, $params = null)
    {
        $builder = $this->db->table("trans_sample_det" . " abx");
        $builder->where("abx.id_sample", $idSample);

        if (!empty($params['id_warna_1'])) {
            $builder->where('id_warna_1', $params['id_warna_1']);
        }

        if (!empty($params['id_warna_2'])) {
            $builder->where('id_warna_2', $params['id_warna_2']);
        }

        if (!empty($params['id_warna_3'])) {
            $builder->where('id_warna_3', $params['id_warna_3']);
        }

        if (!empty($params['id_warna_4'])) {
            $builder->where('id_warna_4', $params['id_warna_4']);
        }

        if (!empty($params['id_barang_1'])) {
            $builder->where('id_barang_1', $params['id_barang_1']);
        }

        if (!empty($params['id_barang_2'])) {
            $builder->where('id_barang_2', $params['id_barang_2']);
        }

        if (!empty($params['id_barang_3'])) {
            $builder->where('id_barang_3', $params['id_barang_3']);
        }

        if (!empty($params['id_barang_4'])) {
            $builder->where('id_barang_4', $params['id_barang_4']);
        }

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataDetailSampleUkuran($idSample, $idSampleDet)
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
                    LEFT JOIN trans_sample_ukuran bbx ON bbx.id_ukuran = abx.id
                    AND bbx.id_sample = $idSample
                    AND bbx.id_sample_det = $idSampleDet
                WHERE abx.active = 1
                ORDER BY abx.id";
        $result = $this->db->query($sql);
        $this->_data   = $result->getResult();
        return $this->_data;
    }

    function getDataDetailSampleWarna($idSample, $id = null)
    {

        $builder = $this->db->table("trans_sample_det");
        $builder->where("id_sample", $idSample);
        if (!empty($id)) {
            $builder->where("id", $id);
            $this->_data = $builder->get()->getRow();
        } else {
            $this->_data = $builder->get()->getResult();
        }
        return $this->_data;
    }
    function getDataDetailSampleUkuranById($id)
    {

        $builder = $this->db->table("trans_sample_ukuran abx");
        $builder->select("w1.kode_warna as warna1,w2.kode_warna as warna2,w3.kode_warna as warna3,w4.kode_warna as warna4");
        $builder->select("w5.kode_warna as warna5,w6.kode_warna as warna6,w7.kode_warna as warna7,w8.kode_warna as warna8");
        $builder->select("abx.id_sample, abx.id_sample_det");
        $builder->join("trans_sample_det bbx", "abx.id_sample_det=bbx.id", "inner");
        $builder->join("ref_warna w1", "bbx.id_warna_1 = w1.id", "left");
        $builder->join("ref_warna w2", "bbx.id_warna_2 = w2.id", "left");
        $builder->join("ref_warna w3", "bbx.id_warna_3 = w3.id", "left");
        $builder->join("ref_warna w4", "bbx.id_warna_4 = w4.id", "left");
        $builder->join("ref_warna w5", "bbx.id_warna_5 = w5.id", "left");
        $builder->join("ref_warna w6", "bbx.id_warna_6 = w6.id", "left");
        $builder->join("ref_warna w7", "bbx.id_warna_7 = w7.id", "left");
        $builder->join("ref_warna w8", "bbx.id_warna_8 = w8.id", "left");
        $builder->where("abx.id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataDetailSalesOrderUkuranById($params)
    {
        $builder = $this->db->table("trans_sample_ukuran tu");
        $builder->select(" 
            tu.id as id_ukuran_so, 
            tu.id_ukuran,
            ru.kode_ukuran, 
            ru.key_ukuran, 
            w1.kode_warna as warna_1, w2.kode_warna as warna_2, w3.kode_warna as warna_3,
            w4.kode_warna as warna_4, w5.kode_warna as warna_5, w6.kode_warna as warna_6,
            w7.kode_warna as warna_7, w8.kode_warna as warna_8,
            tod.kode_sample, tod.style, tod.deskripsi, tu.id_sample,

            -- Flag sumber warna
            CASE 
                WHEN td.id_barang_1 IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna
        ");

        $builder->join("trans_sample_det td",  "td.id = tu.id_sample_det",  "inner");
        $builder->join("trans_sample tod",     "tod.id = td.id_sample",     "inner");
        $builder->join("ref_ukuran ru",        "tu.id_ukuran = ru.id",      "left");

        // JOIN ref_barang (nullable)
        $builder->join("ref_barang b1", "td.id_barang_1 = b1.id", "left");
        $builder->join("ref_barang b2", "td.id_barang_2 = b2.id", "left");
        $builder->join("ref_barang b3", "td.id_barang_3 = b3.id", "left");
        $builder->join("ref_barang b4", "td.id_barang_4 = b4.id", "left");
        $builder->join("ref_barang b5", "td.id_barang_5 = b5.id", "left");
        $builder->join("ref_barang b6", "td.id_barang_6 = b6.id", "left");
        $builder->join("ref_barang b7", "td.id_barang_7 = b7.id", "left");
        $builder->join("ref_barang b8", "td.id_barang_8 = b8.id", "left");

        // JOIN ref_warna dengan COALESCE: prioritaskan warna dari ref_barang, fallback ke trans_sample_det
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

        if (!empty($params['id_sample_det'])) {
            $builder->where("tu.id_sample_det", $params['id_sample_det']);
        }

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataUkuranSample($id = null)
    {
        $builder = $this->db->table("trans_sample_ukuran abx");
        $builder->select("abx.id, abx.qty, abx.id_ukuran, abx.harga_satuan, abx.id_sample, abx.id_sample_det, abx.harga_total");
        
        $builder->where("abx.id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataUkuranSampleOld($id = null, $sampleId = null, $idSampleDet = null)
    {
        $builder = $this->db->table("trans_sample_ukuran abx");
        $builder->select("abx.id, abx.qty, abx.id_ukuran, abx.harga_satuan, abx.id_sample, abx.id_sample_det, abx.harga_total");
        
        $builder->whereNotIn("abx.id", $id);
        $builder->where("abx.id_sample", $sampleId);
        $builder->where("abx.id_sample_det", $idSampleDet);
        $this->_data = $builder->get()->getResult();
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
        $builder->where("tp.tipe_id", 1);
        

        $builder->orderBy("td.id", 'desc');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDODetailSUM($ref_detail_id = null, $id_ukuran = null, $type = null)
    {
        $builder = $this->db->table("trans_delivery_detail abx");

        $builder->select("COALESCE(SUM(abx.qty),0) as qty");

        $builder->join("trans_delivery td", "td.id = abx.id_delivery", 'inner');
        $builder->join("trans_produksi tp", "tp.id = td.id_produksi", 'inner');
        $builder->where("abx.ref_detail_id", $ref_detail_id);
        if (!empty($id_ukuran)) {
            $builder->where("abx.id_ukuran", $id_ukuran);
        }
        if (!empty($type)) {
            $builder->where("td.id_invoice $type", null);
            $builder->where("td.active", 1);
        }
        $builder->where("tp.tipe_id", 1);

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getStatusSI($id = null)
    {
        $builder = $this->db->table("trans_invoice abx");
        $builder->select("abx.id, abx.status");
        
        $builder->whereIn("abx.id", $id);
        $this->_data = $builder->get()->getResult();
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
        $builder->where("tp.tipe_id", 1);
        $builder->orderBy("td.id", 'desc');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getTotalUkuranSample($id_sample = null)
    {
        $builder = $this->db->table("trans_sample_ukuran abx");

        $builder->select("COALESCE(SUM(abx.qty), 0) as qty, COALESCE(SUM(abx.harga_total), 0) as harga_total");


        $builder->where("abx.id_sample", $id_sample);
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
        AND tw.tipe_id = 1;
        ";

        return $this->db->query($sql, [$ref_detail_id, $id_ukuran]);
    }

    function trxInsertUpdateRecord($dataWarna, $dataUkuran, $dataGram)
    {
        $this->db->transStart();
        try {
            if (!empty($dataWarna['id'])) {
                $dataWarna['updated_at'] = date("Y-m-d H:i:s");
                $this->updateRecord("trans_sample_det", $dataWarna, 'id', $dataWarna['id']);
                $idSampleDet = $dataWarna['id'];
            } else {
                unset($dataWarna['id']);
                $dataWarna['created_at'] = date("Y-m-d H:i:s");
                $idSampleDet = $this->insertRecordGetid("trans_sample_det", $dataWarna);
            }
            if (!empty($dataWarna['id']) && ($dataWarna['id_sample'])) {
                // $arrDelete = [
                //     "id_sample" => $dataWarna['id_sample'],
                //     "id_sample_det" => $dataWarna['id']
                // ];
                // $this->deleteRecordMultipleColumn("trans_sample_ukuran", $arrDelete);

                // $arrDelete = [
                //     "id_sample_det" => $dataWarna['id']
                // ];
                // $this->deleteRecordMultipleColumn("trans_sample_gram", $arrDelete);
            }
            $head_qty = 0;
            $head_total = 0;
            $queryStatus = true;
            $IDS = [];
            foreach ($dataUkuran as $rowData) {
                $harga_total = (!empty($rowData['qty']) && !empty($rowData['harga_satuan'])) ? $rowData['qty'] * $rowData['harga_satuan'] : 0;
                $arrDataUkuran = [
                    "id_sample" => $dataWarna['id_sample'],
                    "id_sample_det" => !empty($dataWarna['id']) ? $dataWarna['id'] : $idSampleDet,
                    "id_ukuran" => $rowData['id_ukuran'],
                    "qty" => $rowData['qty'],
                    "harga_satuan" => $rowData['harga_satuan'],
                    "harga_total" =>  $harga_total, //$rowData['harga_total'],
                    "active" => 1,
                    "created_at" =>  date("Y-m-d H:i:s"),

                ];

                $head_qty = $head_qty + (!empty($rowData['qty'])) ? (int) $rowData['qty'] : 0;
                $head_total = $head_total + (!empty($harga_total)) ? (float) $harga_total : 0;

                $ukuranId = !empty($rowData['id']) ? $rowData['id'] : null;
                $getSOUkuran = $this->getDataUkuranSample($ukuranId);
                if (!empty($getSOUkuran)) {
                    $IDS[] = $getSOUkuran->id;
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

                    if ($rowData['qty'] == 0) {
                        $arr_do_prod = [
                            "id_ukuran" => $rowData['id_ukuran'],
                            "ref_detail_id" => $arrDataUkuran['id_sample_det'],
                        ];
                        $this->deleteRecordMultipleColumn("trans_delivery_prod", $arr_do_prod);
                        $this->deleteRecordMultipleColumn("trans_delivery_detail", $arr_do_prod);

                        $deleteWOProsesUkuran = $this->deleteDataWOProsesUkuran($dataWarna['id'], $rowData['id_ukuran']);

                        $this->deleteRecord("trans_sample_ukuran", "id", $rowData['id']);
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
                        
                        $this->updateRecord("trans_sample_ukuran", $arrDataUkuran, 'id', $getSOUkuran->id);
                    }
                }
                else {
                    $IDS[] = $this->insertRecordGetid("trans_sample_ukuran", $arrDataUkuran);
                }
            }
            
            if (!empty($dataWarna['id']) && ($dataWarna['id_sample']) && $queryStatus === true) {
                $getSOUkuranOld = $this->getDataUkuranSampleOld($IDS, $dataWarna['id_sample'], $dataWarna['id']);
                
                foreach ($getSOUkuranOld as $key => $value) {
                    $getDODetSUM = $this->getDODetailSUM($dataWarna['id'], $value->id_ukuran, "<>");
                    $getDODetSUMDO = $this->getDODetailSUM($dataWarna['id'], $value->id_ukuran, "=");
                    if ((int)$getDODetSUMDO->qty  + (int)$getDODetSUM->qty > 0) {
                        $queryStatus = false;
                        $msgError = 'Tidak dapat mengubah QTY <strong>lebih kecil dari '.(int)$getDODetSUMDO->qty  + (int)$getDODetSUM->qty.'</strong>! Total QTY Delivery Order pada item ini sudah mencapai batas minimal!';
                        break;
                    }
                    $arr_do_prod = [
                        "id_ukuran" => $value->id_ukuran,
                        "ref_detail_id" => $value->id_sample_det,
                    ];
                    $this->deleteRecordMultipleColumn("trans_delivery_prod", $arr_do_prod);
                    $this->deleteRecordMultipleColumn("trans_delivery_detail", $arr_do_prod);

                    $deleteWOProsesUkuran = $this->deleteDataWOProsesUkuran($dataWarna['id'], $value->id_ukuran);

                    $this->deleteRecord("trans_sample_ukuran", "id", $value->id);
                }
            }

            if (!empty($dataGram)) {
                # code...
                $IDS = [];
                foreach ($dataGram as $xrow) {
                    
                    $arrDataGram = [
                        "id_sample_det" => $idSampleDet,
                        "id_warna" => $xrow['id_warna'],
                        "id_barang" => $xrow['id_barang'],
                        "qty" => $xrow['qty'],
                        "gram" => $xrow['gram'],
                        "gram_nd" => $xrow['gram_nd'],
                        "kg" => $xrow['kg'],
                        "loss" => $xrow['loss'],
                        "kg_loss" => $xrow['kg_loss'],
                        "total" => $xrow['total'],
                        "active" => 1,
                        "created_at" =>  date("Y-m-d H:i:s"),
    
                    ];
                    if (!empty($xrow['id'])) {
                        $IDS[] = $xrow['id'];
                        $this->updateRecord("trans_sample_gram", $arrDataGram, 'id', $xrow['id']);
                    } else {
                        $IDS[] = $this->insertRecordGetid("trans_sample_gram", $arrDataGram);
                    }
                }
                if (!empty($dataWarna['id']) && ($dataWarna['id_sample'])) {
                    $arr_do_prod = [
                        "column" => 'id_sample_det',
                        "value" => $dataWarna['id'],
                    ];
                    $this->deleteRecord("trans_sample_gram", 'id', $IDS, $arr_do_prod);
                }
            }

            // update data qty dan total harga 
            $head_up['qty'] = $head_qty;
            $head_up['total_harga'] = $head_total;
            $this->updateRecord($this->table, $head_up, 'id', $dataWarna['id_sample']);

            if ($queryStatus == false) {
                $this->db->transRollback();
                return $msgError;
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === TRUE) {
                return true;
            } else {
                throw new \Exception("Transaction failed");
            }
        } catch (\Exception $e) {
            // print_r($e);exit;
            $this->db->transRollback();
            throw $e;
        }
    }

    function trxSubmitSample($arrData, $id)
    {
        $this->db->transStart();
        try {
            // print_r($arrData);exit;
            unset($arrData['total_harga']);
            unset($arrData['qty']);
            $this->updateRecord("trans_sample", $arrData, 'id', $id);

            if (!empty($arrData['status']) && $arrData['status'] == 1) {
                $result = $this->getData($id);
                $arrWorkOrder = [
                    "ref_id" => $id,
                    "ref_kode" => !empty($result->kode_sample) ? $result->kode_sample : null,
                    "kode_walkorder" => $this->generateNo("WRD", "trans_walkorder", "kode_walkorder"),
                    "id_konsumen" => $arrData['id_konsumen'],
                    'tgl_transaksi' => date("Y-m-d"),
                    'tgl_deadline' => $arrData['tgl_deadline'],
                    "qty" => $result->qty,
                    "file_id" => !empty($arrData['gambar_id']) ? $arrData['gambar_id'] : null,
                    "status" => 1,
                    "tipe_id" => 1,
                    "active" => 1,
                    'keterangan_style' => $arrData['keterangan'],
                    "created_at" => $arrData['updated_at'],

                ];
                $idWorkOrder = $this->insertRecordGetid("trans_walkorder", $arrWorkOrder);
                $dataWarna = $this->getDataDetailSampleWarna($id);

                if (!empty($dataWarna)) {
                    foreach ($dataWarna as $rowData) {

                        $detailWorkOrder = [
                            'id_walkorder' => $idWorkOrder,
                            'ref_detail_id' => $rowData->id,
                            'qty' => $this->getTotal_qty($rowData->id, 2),
                            'tipe_id' => 1,
                            'created_at' => date("Y-m-d H:i:s")
                        ];

                        $woIdDet = $this->insertRecordGetid("trans_walkorder_detail", $detailWorkOrder);



                        $prgram['id_sample_det'] = $rowData->id;
                        $dtGram = $this->getData_gram(null, 0, 999, null,  null, $prgram);

                        if (!empty($dtGram)) {
                            foreach ($dtGram as $x) {
                                $arrWarna = [
                                    'id_walkorder_detail' => $woIdDet,
                                    // 'id_warna' => $x->id_warna,
                                    'id_barang' => $x->id_barang,
                                    // 'qty' => $x->qty,
                                    'gram' => $x->gram,
                                    'gram_nd' => $x->gram_nd,
                                    'kg' => $x->kg,
                                    'loss' => $x->loss,
                                    'kg_loss' => $x->kg_loss,
                                    'total' => $x->total,
                                    'created_at' => date("Y-m-d H:i:s")
                                ];
                                // print_r($x);exit;
                                $this->insertRecordGetid("trans_walkorder_warna", $arrWarna);
                            }
                        } else {
                            for ($i = 0; $i < 8; $i++) {
                                $field_name = 'id_barang_' . ($i + 1);
                                if (!empty($rowData->$field_name)) {
                                    $arrWarna = [
                                        'id_walkorder_detail' => $woIdDet,
                                        // 'id_warna' => $rowData->$field_name,
                                        'id_barang' => $rowData->$field_name,
                                        'created_at' => date("Y-m-d H:i:s")
                                    ];
                                    $this->insertRecordGetid("trans_walkorder_warna", $arrWarna);
                                }
                            }
                        }
                    }
                }
            }

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
        $builder = $this->db->table("trans_sample_ukuran tsu");
        $builder->select("sum(tsu.qty) as cnt");
        $builder->join("trans_sample_det td", "td.id = tsu.id_sample_det");
        if ($tipe == 1) {
            $builder->where("td.id_sample", $trans_id);
        } else {
            $builder->where("tsu.id_sample_det", $trans_id);
        }
        $this->_data = $builder->get()->getRow();
        return $this->_data->cnt;
    }

    function generateNo($prefix, $table, $kode, $type_menu = null)
    {
        $kd = $prefix;
        $builder = $this->db->table($table . ' a');
        if (!empty($type_menu)) {
            $builder->select("LEFT($kode, 6) AS tgl, RIGHT( $kode, 5 ) AS kode ");
        }
        else {
            $builder->select("LEFT($kode, 7) AS tgl, RIGHT( $kode, 4 ) AS kode ");
        }

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

    function getData_gram($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_sample_gram tsg");
        $builder->select("
            tsg.id, 
            tsg.id_barang, 
            tsg.id_sample_det, 
            tsg.qty, 
            tsg.gram, 
            tsg.gram_nd, 
            tsg.kg, 
            tsg.loss, 
            tsg.kg_loss, 
            tsg.total, 
            tsg.id_warna,
            rw.kode_warna,

            -- Flag sumber warna
            CASE 
                WHEN tsg.id_barang IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna
        ");

        $builder->join('ref_barang rb', 'rb.id = tsg.id_barang', 'left');

        $builder->join('ref_warna rw', 'COALESCE(rb.id_warna, tsg.id_warna) = rw.id', 'left');

        if ($id == null or $id == "") {
            $builder->where('tsg.active = 1');

            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                // filter tambahan jika diperlukan
            }

            if (!empty($params['id_sample_det'])) {
                $builder->where('tsg.id_sample_det', $params['id_sample_det']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('tsg.id ASC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("tsg.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getData_gramCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("trans_sample_gram tsg");
        $builder->select("count(1) as _cnt");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            // $builder->groupStart();
            // $builder->where('LOWER(abx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
            // $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            // $builder->groupEnd();
        }

        $builder->where('tsg.active = 1');

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
