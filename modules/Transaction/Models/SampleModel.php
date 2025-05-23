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
                          abx.gambar_id,cbx.file_name, abx.uang_dp, abx.style, abx.qty, abx.style_cnt");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("_files cbx", "abx.gambar_id = cbx.id", "left");
        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
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

        // crostab query 
        $sql = "
                    SELECT 
                        tbl.id,
                        ROW_NUMBER ( ) OVER ( ORDER BY tbl.id ) AS no,
                        COALESCE ( w1.kode_warna, '' ) as colorDasar,
                        TRIM ( BOTH ' - ' FROM COALESCE ( w1.kode_warna, '' ) || 
                                CASE WHEN w2.kode_warna IS NOT NULL THEN ' - ' || w2.kode_warna ELSE '' END ||
                                CASE WHEN w3.kode_warna IS NOT NULL THEN ' - ' || w3.kode_warna ELSE '' END ||
                                CASE WHEN w4.kode_warna IS NOT NULL THEN ' - ' || w4.kode_warna ELSE '' END ||
                                CASE WHEN w5.kode_warna IS NOT NULL THEN ' - ' || w5.kode_warna ELSE '' END ||
                                CASE WHEN w6.kode_warna IS NOT NULL THEN ' - ' || w6.kode_warna ELSE '' END ||
                                CASE WHEN w7.kode_warna IS NOT NULL THEN ' - ' || w7.kode_warna ELSE '' END ||
                                CASE WHEN w8.kode_warna IS NOT NULL THEN ' - ' || w8.kode_warna ELSE '' END 
                        ) AS colour,
                        {$col11},
                        COALESCE((select sum(x.harga_total) from trans_sample_ukuran x where x.id_sample_det = tbl.id), 0) as total_harga
                    FROM 
                        CROSSTAB(
                            $$ 
                            SELECT 
                                td.id,
                                ru.seq,
                                 (case when ru.key_ukuran = 'all' THEN 'all_' else ru.key_ukuran end) as key_ukuran,
                                SUM(COALESCE(tu.qty, 0)) AS qty
                            FROM 
                                trans_sample_ukuran tu 
                            INNER JOIN trans_sample_det td ON td.id = tu.id_sample_det
                            INNER JOIN ref_ukuran ru on ru.id = tu.id_ukuran
                            WHERE (tu.qty is not null and tu.qty > 0) AND td.id_sample = {$id}
                            group by td.id, ru.key_ukuran, ru.seq
                            order by td.id, ru.seq asc
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
                    INNER JOIN ref_warna w1 ON td.id_warna_1 = w1.id
                    LEFT JOIN ref_warna w2 ON td.id_warna_2 = w2.id
                    LEFT JOIN ref_warna w3 ON td.id_warna_3 = w3.id
                    LEFT JOIN ref_warna w4 ON td.id_warna_4 = w4.id
                    LEFT JOIN ref_warna w5 ON td.id_warna_5 = w5.id
                    LEFT JOIN ref_warna w6 ON td.id_warna_6 = w6.id
                    LEFT JOIN ref_warna w7 ON td.id_warna_7 = w7.id
                    LEFT JOIN ref_warna w8 ON  td.id_warna_8 = w8.id;

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
                $arrDelete = [
                    "id_sample" => $dataWarna['id_sample'],
                    "id_sample_det" => $dataWarna['id']
                ];
                $this->deleteRecordMultipleColumn("trans_sample_ukuran", $arrDelete);

                $arrDelete = [
                    "id_sample_det" => $dataWarna['id']
                ];
                $this->deleteRecordMultipleColumn("trans_sample_gram", $arrDelete);
            }

            $head_qty = 0;
            $head_total = 0;
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
                $this->insertRecordGetid("trans_sample_ukuran", $arrDataUkuran);
            }


            foreach ($dataGram as $xrow) {
                $arrDataGram = [
                    "id_sample_det" => $idSampleDet,
                    "id_warna" => $xrow['id_warna'],
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

                $this->insertRecordGetid("trans_sample_gram", $arrDataGram);
            }

            // update data qty dan total harga 
            $head_up['qty'] = $head_qty;
            $head_up['total_harga'] = $head_total;
            $this->updateRecord($this->table, $head_up, 'id', $dataWarna['id_sample']);
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

            if ($arrData['status'] == 1) {
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
                                    'id_warna' => $x->id_warna,
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
                                $field_name = 'id_warna_' . ($i + 1);
                                if (!empty($rowData->$field_name)) {
                                    $arrWarna = [
                                        'id_walkorder_detail' => $woIdDet,
                                        'id_warna' => $rowData->$field_name,
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

    function generateNo($prefix, $table, $kode)
    {
        $kd = $prefix;
        $builder = $this->db->table($table . ' a');
        $builder->select("LEFT($kode, 7) AS tgl, RIGHT( $kode, 4 ) AS kode ");

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
        $builder->select("tsg.id, tsg.id_sample_det, tsg.qty, tsg.gram,  tsg.gram_nd, tsg.kg, tsg.loss, tsg.kg_loss, tsg.total, rw.kode_warna, tsg.id_warna");

        $builder->join('ref_warna rw', 'rw.id = tsg.id_warna', 'inner');
        if ($id == null or $id == "") {
            $builder->where('tsg.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                // $builder->groupStart();
                //     $builder->where('LOWER(abx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
                //     $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->groupEnd();
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
