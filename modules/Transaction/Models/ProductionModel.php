<?php

namespace Modules\Transaction\Models;

use Modules\Referensi\Models\BarangModel; 
use Modules\Referensi\Models\UkuranModel; 
use Modules\Referensi\Models\SatuanModel; 
// use  Modules\Transaction\Models\WalkorderModel;

class ProductionModel extends \App\Models\PrModel
{

    protected $table = "trans_produksi";
    protected $_data = null;
    protected $primaryKey = 'id';
    protected $mBarang;
    protected $mWalkorder;

    protected $tblTrxBarang = "trans_barang";
    protected $tblTrxLots = "trans_lots";
    protected $tblTrxBalances = "trans_barang_balances";

    public function __construct()
    {
        parent::__construct();
        $this->mBarang = new BarangModel();
        $this->mWalkorder = new WalkorderModel();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.id_walkorder,  abx.kode_walkorder, abx.id_konsumen, abx.id_style, abx.qty, abx.file_id,
                        abx.status, bbx.nama as konsumen_nama, abx.tgl_deadline, abx.tgl_transaksi, abx.keterangan_style, abx.keterangan,
                        abx.tipe_id, cbx.file_name,abx.kode_prod");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        // $builder->join("trans_sample ts", "ts.id = abx.ref_id and abx.tipe_id = 1", "left");
        // $builder->join("trans_sales_order tso", "tso.id = abx.ref_id and abx.tipe_id = 2", "left");
        $builder->join("_files cbx", "abx.file_id = cbx.id", "left");
        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_prod) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.kode_walkorder) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
            }

            if(!empty($params['tipe_id']))  $builder->where('abx.tipe_id', $params['tipe_id']);

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
            $builder->where('LOWER(abx.kode_prod) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.kode_walkorder) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if (!empty($params['id_konsumen'])) {
            $builder->where('abx.id_konsumen', $params['id_konsumen']);
        }

        if(!empty($params['tipe_id']))  $builder->where('abx.tipe_id', $params['tipe_id']);

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataProsesProd($params)
    {
        $builder = $this->db->table("trans_walkorder_proses abx");
        $builder->select("bbx.seq,bbx.nama,bbx.id ,SUM(COALESCE(qty, 0)) AS qty, SUM(COALESCE(qty_prod, 0)) AS qty_prod");
        $builder->join("_jenis_proses_produksi bbx", "abx.id_proses = bbx.id", "inner");
        $builder->join("trans_walkorder_proses_ukuran cbx", "cbx.id_walkorder_proses = abx.id", "inner");
        if(!empty($params['id_walkorder'])){
            $builder->where('abx.id_walkorder', $params['id_walkorder']);
        }
        if (!empty($params['last_proses']) && !empty($params['id_walkorder'])) {
            $subQuery = $this->db->table('trans_walkorder_proses tx')
            ->select('tx.id_proses')
            ->join('_jenis_proses_produksi jp', 'jp.id = tx.id_proses', 'inner')
            ->where('tx.id_walkorder', $params['id_walkorder'])
            ->orderBy('jp.seq', 'desc')
            ->limit(1)
            ->getCompiledSelect();

            $builder->where('abx.id_proses = (' . $subQuery . ')');
        }
        $builder->groupBy("bbx.nama, bbx.seq, bbx.id");
        $builder->orderBy("bbx.seq", "ASC");
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function getDataNextProses($idWorkOrder, $id, $seq)
    {
        $builder = $this->db->table("trans_walkorder_proses abx");
        $builder->select("abx.id");
        $builder->join("_jenis_proses_produksi bbx", "abx.id_proses = bbx.id", "inner");
        $builder->where('abx.id_walkorder', $idWorkOrder);
        // $builder->where('abx.id_proses >', $id);
        $builder->where('bbx.seq >', $seq);
        $builder->orderBy("bbx.seq", "ASC");
        $builder->limit(1);
        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }

    function getDataQuantityCurrent($id, $idUkuran, $ref_detail_id)
    {
        $builder = $this->db->table("trans_walkorder_proses_ukuran abx");
        $builder->select("abx.qty_prod");
        $builder->where('abx.id_walkorder_proses', $id);
        $builder->where('abx.id_ukuran', $idUkuran);
        $builder->where('abx.ref_detail_id', $ref_detail_id);
        $builder->orderBy("abx.id", "ASC");
        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }

    function getDataOperatorProd($id, $tgl_transaksi = null, $id_proses = null)
    {
        $builder = $this->db->table("trans_produksi_operator abx");
        $builder->select("abx.flag, abx.id_proses as id_walkorder_proses_ukuran, abx.qty, ebx.nama_operator as operator, dbx.kode_warna, 
                          abx.harga_total, abx.harga, abx.tgl_transaksi as date,bbx.nama as process,cbx.kode_ukuran, abx.nomor_mesin, abx.kode_transaksi");

        $builder->join("_jenis_proses_produksi bbx", "abx.id_proses = bbx.id", "inner");
        $builder->join("ref_ukuran cbx", "abx.id_ukuran = cbx.id", "inner");
        $builder->join("ref_warna dbx", "abx.id_warna = dbx.id", "inner");
        $builder->join("ref_operator ebx", "abx.id_operator = ebx.id", "inner");

        $builder->where('abx.id_produksi', $id);
        if (!empty($tgl_transaksi)) {
            $builder->where('abx.tgl_transaksi', $tgl_transaksi);
        }

        if (!empty($id_proses)) {
            $builder->where('abx.id_proses', $id_proses);
        }
        
        $builder->orderBy("abx.id", "ASC");
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function trxInsertUpdateRecord($data, $idProduksi, $idWorkOrder)
    {   
            
        $this->db->transStart();
        try {
            // print_r(json_encode($data));exit;
            $proses_last = $this->getlast_proses($idWorkOrder);
            foreach ($data as $rowData) {
                $dtProses = $this->getDataJenisProduksi($rowData['id_proses']);
                $resData = $this->getDataNextProses($idWorkOrder, $rowData['id_proses'], $dtProses->seq);
                $resQtyCurrent = $this->getDataQuantityCurrent($rowData['id_walkorder_proses_ukuran'], $rowData['id_ukuran'], $rowData['ref_detail_id']);
                
                $arrDataUkuran = [
                    "id_produksi" => $idProduksi,
                    "id_walkorder_proses_ukuran" => !empty($rowData['id_walkorder_proses_ukuran']) ? $rowData['id_walkorder_proses_ukuran'] : null,
                    "id_proses" => $rowData['id_proses'],
                    "id_ukuran" => $rowData['id_ukuran'],
                    "id_warna" => $rowData['id_warna'],
                    "id_operator" => $rowData['id_operator'],
                    "tgl_transaksi" => !empty($rowData['date']) ? \fdate_ind_to_eng($rowData['date']) : null,
                    "qty" => $rowData['qty'],
                    "harga" => $rowData['harga'],
                    "harga_total" => $rowData['harga_total'],
                    "ref_detail_id" => $rowData['ref_detail_id'],
                    "nomor_mesin" => $rowData['nomor_mesin'],
                    "active" => 1,
                    "flag" => 1,
                    "created_at" =>  date("Y-m-d H:i:s"),

                ];
                
                $this->insertRecordGetid("trans_produksi_operator", $arrDataUkuran);

                $qtyQr = !empty($resQtyCurrent) ? (float)$resQtyCurrent->qty_prod + (float)$rowData['qty'] : $rowData['qty'];

                $arrUpdData = [
                    "qty_prod" => $qtyQr
                ];
                // print_r(json_encode($arrUpdData));exit;
                // print_r(json_encode($arrUpdData));exit;
                $arrParam =  [
                    "id_walkorder_proses" => $rowData['id_walkorder_proses_ukuran'],
                    "id_ukuran" => $rowData['id_ukuran'],
                    "ref_detail_id" => $rowData['ref_detail_id'],
                ];
                // print_r(json_encode($arrParam));exit;
                $ups = $this->updateRecords("trans_walkorder_proses_ukuran", $arrUpdData, $arrParam);
                // print_r( $resData->id);exit;
                if(!empty($resData)){
                    $arrUpdData2 = [
                        "qty" => $qtyQr
                    ];
                    $arrParam2 =  [
                        "id_walkorder_proses" => $resData->id,
                        "id_ukuran" => $rowData['id_ukuran'],
                        "ref_detail_id" => $rowData['ref_detail_id'],
                    ];
                    $this->updateRecords("trans_walkorder_proses_ukuran", $arrUpdData2, $arrParam2);
                }else{

                    $dtWo = $this->mWalkorder->getData($idWorkOrder);

                    $mukuran = new UkuranModel();

                    $dataUkuran = $mukuran->getData($rowData['id_ukuran']);

                    $msatuan = new SatuanModel();
                    $prm_satuan['nama_satuan'] = $dataUkuran->kode_ukuran;
                    $dtSatuan = $msatuan->getData(null, 0,1, null, null, $prm_satuan);

                    $params_b['nama_barang'] = $dtWo->keterangan_style;
                    $dtBarang = $this->mBarang->getData(null, 0, 1, null, null, $params_b);
                    // if(empty($dtBarang)){
                    //     $arr_isi = [
                    //         'nama_barang'     => $dtWo->keterangan_style,
                    //         'id_jenis_barang' => 3,
                    //         'id_satuan'       => $dtSatuan[0]->id,
                    //         'harga_satuan'    => 0,
                    //         'stok_minimum'    => 1,
                    //         'keterangan'      => 'Generate dari produksi',
                    //     ];
                    //     $arr_isi['created_at'] = date("Y-m-d H:i:s");
                    //     $arr_isi['created_by'] = 1;
                    //     $arr_isi['kode_barang'] = $this->mBarang->generateKodeBarang();
                    //     $id_barang = $this->insertRecordGetid($this->mBarang->table, $arr_isi);
                    // }else{
                    //     $id_barang = $dtBarang[0]->id;
                    // }

                    // $id_gudang = $dtWo->id_gudang;
                    // $qty = $rowData['qty'];

                    // // $dtLot['id_barang'] = $id_barang;
                    // // $dtLot['id_gudang'] = $id_gudang;
                    // // $dtLot['lot_no']    = $this->generateRandomNumber(10);
                    // // $dtLot['qty']       = $qty;
                    // // $id_lot = $this->insertRecordGetid($this->tblTrxLots, $dtLot);
                    // $noLot = $this->generateRandomNumber(10);
                    // $mBarangMasuk = new IncomingGoodsModel();
                    // $arrParam =  [
                    //     "id_barang" => $id_barang,
                    //     "id_gudang" => $id_gudang,
                    // ];
                    // $resLotNo = $mBarangMasuk->getLotNo($noLot, $id_barang);
                    // $dataLots = [
                    //     "id_barang" => $id_barang,
                    //     "id_gudang" => !empty($id_gudang) ? $id_gudang : null,
                    //     "tanggal" => date("Y-m-d H:i:s"),
                    //     "lot_no" => $noLot,
                    //     "qty" => $qty,
                    //     "active" => 1,
                    //     "created_at" =>  date("Y-m-d H:i:s"),
                    // ];
                    // if (!empty($resLotNo)) {
                    //     $idLots = $resLotNo->id;
                    //     $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty + $qty), array("id" => $idLots));
                    // } else {
                    //     $idLots = $this->insertRecordGetid($this->tblTrxLots, $dataLots);
                    // }

                    // $resData = $mBarangMasuk->getLastStokBarangBalances($id_barang, $id_gudang, $idLots);

                    // $stokAwal = !empty($resData) ? $resData->stok : 0;
                    // $dataBarang = [
                    //     "id_barang" => $id_barang,
                    //     "jenis_transaksi" => 1,
                    //     "jumlah" =>  $qty,
                    //     "tanggal" => date("Y-m-d H:i:s"),
                    //     "id_gudang_tujuan" =>  !empty($id_gudang) ? $id_gudang : null,
                    //     "nama" => 'Produksi',
                    //     "id_kategori" => 11,
                    //     "keterangan" => "Barang Masuk Dari Produksi",
                    //     "active" => 1,
                    //     "tipe" => 1,
                    //     "created_at" =>  date("Y-m-d H:i:s"),
                    //     "lot_id" => $idLots,
                    //     "kode_transaksi" => $mBarangMasuk->generateKodePersediaan(),
                    // ];
                    // $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                    // $arrStockBalances = [
                    //     "id_barang" => $id_barang,
                    //     "id_gudang" => !empty($id_gudang) ? $id_gudang : null,
                    //     "tanggal" => date("Y-m-d H:i:s"),
                    //     "lot_id" => $idLots,
                    //     "saldo_awal" => 0,
                    //     "saldo_akhir" => $qty,
                    //     "active" => 1,
                    //     "created_at" =>  date("Y-m-d H:i:s"),
                    // ];
                    // if (!empty($resData)) {
                    //     $this->updateRecords($this->tblTrxBalances, array("saldo_akhir" => $resLotNo->qty + $qty), array("id" => $resData->id));
                    // } else {
                    //     $this->insertRecordGetid($this->tblTrxBalances, $arrStockBalances);
                    // }
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

    function getDataJenisProduksi($id_proses){
        $builder = $this->db->table("_jenis_proses_produksi abx");
        $builder->select("abx.id, abx.nama, abx.seq");
        $builder->where('abx.active', 1);
        $builder->where('abx.id', $id_proses);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getProduksilast($params){
        $id_walkorder = $params['id_walkorder'];
         // Dynamic Columns
         $col11 = "";
         $col12 = "";
         $col21 = "";
         $col22 = "";
         $col3 = "";

         $ord = "";
         $ordx = "";
         $ukuranArr = explode(",", $params['ukuran']);
         foreach ($ukuranArr as $item) {
             $item = trim($item); 
             $hrg = $item . '_hrg';
             $col11 .= ($col11 == "") ? "coalesce(tbl.$item,0) as $item" : ",coalesce(tbl.$item,0) as $item";
             $col12 .= ($col12 == "") ? "coalesce(tbl.$hrg,0) as $hrg" : ",coalesce(tbl.$hrg,0) as $hrg";

             $col21 .= ($col21 == "") ? "$item Int" : ",$item Int";
             $col22 .= ($col22 == "") ? "$hrg Float" : ",$hrg Float";

             $ord  .= $ord == "" ? $item  : "," . $item ;
             $ordx .= $ordx == "" ? $hrg : "," . $hrg;
         }
         $all_order = $ord . ',' . $ordx;
        //  print_r($all_order);exit;
        // $sql = "
        //     select 
        //         tbl.ref_detail_id__,
        //         tbl.id_walkorder,
        //         tbl.id_proses,
        //         tw.tipe_id,
        //         rw.kode_warna,
        //         coalesce(tbl.harga_satuan, 0) as harga_satuan,
        //         {$col1}
        //     from
        //         CROSSTAB(
        //             'select 
        //                 twpu.ref_detail_id,
        //                 twp.id_walkorder,
        //                 twp.id_proses,
        //                 (CASE WHEN tw.tipe_id = 1 THEN 
        //                     (select xt.harga_satuan from trans_sample_ukuran xt where xt.id_ukuran = twpu.id_ukuran and xt.id_sample_det =  twpu.ref_detail_id)
        //                 ELSE 
        //                     (select xt.harga_satuan from trans_sample_ukuran xt where xt.id_ukuran = twpu.id_ukuran and xt.id_sample_det =  twpu.ref_detail_id)
        //                 END) as harga_satuan,
        //                 rk.key_ukuran,
        //                 COALESCE(twpu.qty_prod, 0) as qty_prod
        //             from trans_walkorder_proses_ukuran twpu
        //             inner join trans_walkorder_proses twp on twp.id = twpu.id_walkorder_proses
        //             inner join trans_walkorder tw on tw.id = twp.id_walkorder
        //             inner join ref_ukuran rk on rk.id = twpu.id_ukuran
        //             where twp.id_walkorder = ".$id_walkorder." and  twp.id_proses = (select max(tx.id_proses) from trans_walkorder_proses tx where tx.id_walkorder = ".$id_walkorder.")
        //             order by twpu.ref_detail_id, twp.id_proses asc, twpu.id_ukuran',
        //         'select key_ukuran from ref_ukuran rx where rx.active = 1 order by rx.seq asc'
        //     ) as tbl (ref_detail_id int, id_walkorder int, id_proses int, harga_satuan float, {$col2})
        //     inner join trans_walkorder tw on tbl.id_walkorder = tw.id
        //     left join trans_sample_det tsd on tbl.ref_detail_id = tsd.id and tw.tipe_id = 1
        //     left join trans_sales_order_det tsod on tbl.ref_detail_id = tsod.id and tw.tipe_id = 2
        //     left join ref_warna rw on rw.id = (case when tw.tipe_id = 1 then tsd.id_warna_1 when tw.tipe_id = 2 then tsod.id_warna_1 else -1 end)
        // ";

        $sql = "
                SELECT 
                    tbl.ref_detail_id,
                    tbl.id_walkorder,
                    tbl.id_proses,
                    tw.tipe_id,
                    rw.kode_warna,
                    {$col11},
                    {$col12}
                FROM 
                    CROSSTAB(
                        $$ 
                        SELECT 
                            twpu.ref_detail_id,
                            twp.id_walkorder,
                            twp.id_proses,
                            rk.key_ukuran || case when rk.key_ukuran = 'all' then '_' else '' end || CASE 
                                WHEN subquery.is_qty THEN ''
                                ELSE '_hrg'
                            END AS column_name,
                            CASE 
                                WHEN subquery.is_qty THEN COALESCE(twpu.qty_prod, 0)
                                ELSE 
                                    CASE 
                                        WHEN tw.tipe_id = 1 THEN
                                            (
                                                SELECT xt.harga_satuan 
                                                FROM trans_sample_ukuran xt 
                                                WHERE xt.id_ukuran = twpu.id_ukuran 
                                                AND xt.id_sample_det = twpu.ref_detail_id
                                            )
                                        WHEN tw.tipe_id = 2 THEN
                                            (
                                                SELECT xt.harga_satuan 
                                                FROM trans_sales_order_ukuran xt 
                                                WHERE xt.id_ukuran = twpu.id_ukuran 
                                                AND xt.id_sales_order_det = twpu.ref_detail_id
                                            )
                                        ELSE
                                            0
                                    END
                            END AS value
                        FROM trans_walkorder_proses_ukuran twpu
                        INNER JOIN trans_walkorder_proses twp 
                            ON twp.id = twpu.id_walkorder_proses
                        INNER JOIN trans_walkorder tw 
                            ON tw.id = twp.id_walkorder
                        INNER JOIN ref_ukuran rk 
                            ON rk.id = twpu.id_ukuran
                        CROSS JOIN (
                            SELECT DISTINCT key_ukuran, TRUE AS is_qty 
                            FROM ref_ukuran 
                            WHERE active = 1
                            UNION ALL
                            SELECT DISTINCT key_ukuran, FALSE AS is_qty 
                            FROM ref_ukuran 
                            WHERE active = 1
                        ) subquery
                        WHERE twp.id_walkorder = {$id_walkorder}
                        AND twp.id_proses = (
                            SELECT ( tx.id_proses ) FROM trans_walkorder_proses tx 
                            inner join _jenis_proses_produksi jp on jp.id = tx.id_proses 
                            WHERE tx.id_walkorder = {$id_walkorder} 
                            order by jp.seq desc limit 1
                        )
                        ORDER BY twpu.ref_detail_id, twp.id_proses, column_name
                        $$,
                        $$ 
                            SELECT unnest(string_to_array('$all_order', ',')) AS param_id
                        $$
                    ) AS tbl (
                        ref_detail_id INT,
                        id_walkorder INT,
                        id_proses INT,
                        {$col21},
                        {$col22}
                    )
                INNER JOIN trans_walkorder tw ON tbl.id_walkorder = tw.id
                LEFT JOIN trans_sample_det tsd ON tbl.ref_detail_id = tsd.id AND tw.tipe_id = 1
                LEFT JOIN trans_sales_order_det tsod ON tbl.ref_detail_id = tsod.id AND tw.tipe_id = 2
                LEFT JOIN ref_warna rw ON rw.id = CASE 
                    WHEN tw.tipe_id = 1 THEN tsd.id_warna_1
                    WHEN tw.tipe_id = 2 THEN tsod.id_warna_1 
                    ELSE -1 
                END;

        ";


        // SELECT key_ukuran 
        //                 FROM (
        //                     SELECT key_ukuran, 1 as tseq, seq  FROM ref_ukuran WHERE active = 1
        //                     UNION ALL
        //                     SELECT CONCAT(key_ukuran, '_hrg'), 2 as tseq, seq  FROM ref_ukuran WHERE active = 1
        //                 ) AS subquery
        //                 ORDER BY tseq, seq

        // -- s INT, m INT, l INT, xl INT, xxl INT, all_ INT, xxxl INT, xs INT,
        //                 == s_hrg FLOAT, m_hrg FLOAT, l_hrg FLOAT, xl_hrg FLOAT, xxl_hrg FLOAT, all_hrg FLOAT, xxxl_hrg FLOAT, xs_hrg FLOAT

        $query = $this->db->query($sql);

        $this->_data = $query->getResult();

        return $this->_data;
    }   


    function getlast_proses($id_walkorder){
        $builder = $this->db->table('trans_walkorder_proses tp');
        $builder->select('max(tp.id_proses) as id_proses');
        $builder->where('tp.id_walkorder', $id_walkorder);
        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }

    function generateRandomNumber($length = 10) {
        $randomNumber = '';
        for ($i = 0; $i < $length; $i++) {
            $randomNumber .= rand(0, 9); // Menghasilkan angka acak antara 0 dan 9
        }
        return $randomNumber;
    }
     
}
