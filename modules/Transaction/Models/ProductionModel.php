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
                        abx.tipe_id, cbx.file_name,abx.kode_prod,twx.ref_kode as kode_walkorder_ref, twx.ref_id as id_walkorder_ref, tso.deskripsi");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("trans_walkorder twx", "abx.id_walkorder = twx.id", "inner");
        // $builder->join("trans_sample ts", "ts.id = abx.ref_id and abx.tipe_id = 1", "left");
        $builder->join("trans_sales_order tso", "tso.id = twx.ref_id and twx.tipe_id = 2", "left");
        $builder->join("_files cbx", "abx.file_id = cbx.id", "left");
        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $value = strtolower("%{$filters[0]['value']}%");
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_prod) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.kode_walkorder) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tso.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.keterangan_style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere("LOWER(CONCAT(TRIM(abx.keterangan_style), ' - ', TRIM(bbx.nama))) LIKE", $value);
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
            }

            if (!empty($params['id_walkorder'])) {
                $builder->where('abx.id_walkorder', $params['id_walkorder']);
                $builder->where('abx.tipe_id', $params['tipe_id']);
                $builder->where('abx.active', 1);
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
        $builder->select("bbx.seq,bbx.nama,bbx.id ,SUM(COALESCE(qty, 0)) AS qty, SUM(COALESCE(qty_prod, 0)) AS qty_prod,
                           COALESCE((select sum(COALESCE(tpp.harga_total, 0)) 
                                     from trans_produksi_operator tpp inner join trans_produksi tp on tp.id = tpp.id_produksi
                                     where tp.id_walkorder = abx.id_walkorder and tpp.id_proses = abx.id_proses),0) as harga_proses");
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
        $builder->groupBy("bbx.nama, bbx.seq, bbx.id, abx.id_walkorder, abx.id_proses");
        $builder->orderBy("bbx.seq", "ASC");
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function getDataProsesProdNew($id, $last_proses = null, $rajut = null)
    {
        $builder = $this->db->table("trans_produksi_operator tpo");
        $builder->select("jpp.nama, jpp.seq, jpp.id,
                            SUM(CASE WHEN tpo.print_type = 1 OR tpo.print_type IS NULL THEN tpo.qty ELSE 0 END) AS qty_prod,
    SUM(CASE WHEN tpo.print_type = 2 THEN tpo.qty ELSE 0 END) AS qty_fix");
        $builder->join("trans_produksi tp", "tp.id = tpo.id_produksi", "inner");
        $builder->join("_jenis_proses_produksi jpp", "tpo.id_proses = jpp.id", "inner");
        if(!empty($id)){
            $builder->where('tp.id', $id);
        }
        $builder->groupBy("jpp.nama, jpp.seq, jpp.id");
        if ($last_proses == true) {
            $builder->orderBy("jpp.seq", "DESC");
            $this->_data = $builder->get()->getRow();
        }
        else if (!empty($rajut)){
            $builder->where('jpp.id', 1);
            $this->_data = $builder->get()->getRow();
        }
        else {
            $builder->orderBy("jpp.seq", "ASC");
            $this->_data = $builder->get()->getResult();
        }
        

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


    function getProduksilastV1($params){
        $id_walkorder = $params['id_walkorder'];

        $col11 = "";
        $col12 = "";
        $col21 = "";
        $col22 = "";
        $col3  = "";
        $ord   = "";
        $ordx  = "";

        $ukuranArr = array_unique(array_map('trim', explode(",", $params['ukuran'])));
        foreach ($ukuranArr as $item) {
            $item = trim($item); 
            $preg = preg_match('/^[a-zA-Z_]+$/', $item) ? $item : "\"$item\"";
            $hrg  = $item . '_hrg';
            if (preg_match('/^[a-zA-Z_]+$/', $hrg)) {
                $hrg = $hrg;
            } else {
                $hrg = "\"$hrg\"";
            }
            $col11 .= ($col11 == "") ? "coalesce(tbl.$preg,0) as $preg" : ",coalesce(tbl.$preg,0) as $preg";
            $col12 .= ($col12 == "") ? "coalesce(tbl.$hrg,0) as $hrg"   : ",coalesce(tbl.$hrg,0) as $hrg";
            $col21 .= ($col21 == "") ? "$preg Int"   : ",$preg Int";
            $col22 .= ($col22 == "") ? "$hrg Float"  : ",$hrg Float";
            $ord   .= $ord  == "" ? $preg : "," . $preg;
            $ordx  .= $ordx == "" ? $hrg  : "," . $hrg;
        }
        $all_order = $ord . ',' . $ordx;

        $sql = "
            SELECT 
                tbl.ref_detail_id,
                tbl.id_walkorder,
                tbl.id_proses,
                tw.tipe_id,

                -- Flag sumber warna
                CASE
                    WHEN tw.tipe_id = 1 AND tsd.id_barang_1 IS NOT NULL THEN 'via_barang'
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
                            WHEN subquery.is_qty THEN 
                                CASE 
                                    WHEN tw.tipe_id = 1 THEN
                                        (SELECT xt.qty FROM trans_sample_ukuran xt 
                                        WHERE xt.id_ukuran = twpu.id_ukuran 
                                        AND xt.id_sample_det = twpu.ref_detail_id)
                                    WHEN tw.tipe_id = 2 THEN
                                        (SELECT xt.qty FROM trans_sales_order_ukuran xt 
                                        WHERE xt.id_ukuran = twpu.id_ukuran 
                                        AND xt.id_sales_order_det = twpu.ref_detail_id)
                                    ELSE 0
                                END
                            ELSE 
                                CASE 
                                    WHEN tw.tipe_id = 1 THEN
                                        (SELECT xt.harga_satuan FROM trans_sample_ukuran xt 
                                        WHERE xt.id_ukuran = twpu.id_ukuran 
                                        AND xt.id_sample_det = twpu.ref_detail_id)
                                    WHEN tw.tipe_id = 2 THEN
                                        (SELECT xt.harga_satuan FROM trans_sales_order_ukuran xt 
                                        WHERE xt.id_ukuran = twpu.id_ukuran 
                                        AND xt.id_sales_order_det = twpu.ref_detail_id)
                                    ELSE 0
                                END
                        END AS value
                    FROM trans_walkorder_proses_ukuran twpu
                    INNER JOIN trans_walkorder_proses twp ON twp.id = twpu.id_walkorder_proses
                    INNER JOIN trans_walkorder tw ON tw.id = twp.id_walkorder
                    INNER JOIN ref_ukuran rk ON rk.id = twpu.id_ukuran
                    CROSS JOIN (
                        SELECT DISTINCT key_ukuran, TRUE AS is_qty 
                        FROM ref_ukuran WHERE active = 1
                        UNION ALL
                        SELECT DISTINCT key_ukuran, FALSE AS is_qty 
                        FROM ref_ukuran WHERE active = 1
                    ) subquery
                    WHERE twp.id_walkorder = {$id_walkorder}
                    AND twp.id_proses = (
                        SELECT (tx.id_proses) FROM trans_walkorder_proses tx 
                        INNER JOIN _jenis_proses_produksi jp ON jp.id = tx.id_proses 
                        WHERE tx.id_walkorder = {$id_walkorder} 
                        ORDER BY jp.seq DESC LIMIT 1
                    )
                    ORDER BY twpu.ref_detail_id, twp.id_proses, column_name
                    $$,
                    $$ 
                        SELECT unnest(string_to_array('$all_order', ',')) AS param_id
                    $$
                ) AS tbl (
                    ref_detail_id INT,
                    id_walkorder  INT,
                    id_proses     INT,
                    {$col21},
                    {$col22}
                )
            INNER JOIN trans_walkorder tw ON tbl.id_walkorder = tw.id

            -- JOIN trans_sample_det (tipe_id = 1)
            LEFT JOIN trans_sample_det tsd ON tbl.ref_detail_id = tsd.id AND tw.tipe_id = 1

            -- JOIN ref_barang untuk sample
            LEFT JOIN ref_barang bs1 ON bs1.id = tsd.id_barang_1
            LEFT JOIN ref_barang bs2 ON bs2.id = tsd.id_barang_2
            LEFT JOIN ref_barang bs3 ON bs3.id = tsd.id_barang_3
            LEFT JOIN ref_barang bs4 ON bs4.id = tsd.id_barang_4
            LEFT JOIN ref_barang bs5 ON bs5.id = tsd.id_barang_5
            LEFT JOIN ref_barang bs6 ON bs6.id = tsd.id_barang_6
            LEFT JOIN ref_barang bs7 ON bs7.id = tsd.id_barang_7
            LEFT JOIN ref_barang bs8 ON bs8.id = tsd.id_barang_8

            -- JOIN trans_sales_order_det (tipe_id = 2)
            LEFT JOIN trans_sales_order_det tsod ON tbl.ref_detail_id = tsod.id AND tw.tipe_id = 2

            -- JOIN ref_barang untuk SO
            LEFT JOIN ref_barang bso1 ON bso1.id = tsod.id_barang_1
            LEFT JOIN ref_barang bso2 ON bso2.id = tsod.id_barang_2
            LEFT JOIN ref_barang bso3 ON bso3.id = tsod.id_barang_3
            LEFT JOIN ref_barang bso4 ON bso4.id = tsod.id_barang_4
            LEFT JOIN ref_barang bso5 ON bso5.id = tsod.id_barang_5
            LEFT JOIN ref_barang bso6 ON bso6.id = tsod.id_barang_6
            LEFT JOIN ref_barang bso7 ON bso7.id = tsod.id_barang_7
            LEFT JOIN ref_barang bso8 ON bso8.id = tsod.id_barang_8

            -- JOIN ref_warna: COALESCE dari ref_barang masing-masing tipe, fallback ke id_warna di det
            LEFT JOIN ref_warna rw1 ON rw1.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs1.id_warna  WHEN tw.tipe_id = 2 THEN bso1.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_1 WHEN tw.tipe_id = 2 THEN tsod.id_warna_1 ELSE NULL END
            )
            LEFT JOIN ref_warna rw2 ON rw2.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs2.id_warna  WHEN tw.tipe_id = 2 THEN bso2.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_2 WHEN tw.tipe_id = 2 THEN tsod.id_warna_2 ELSE NULL END
            )
            LEFT JOIN ref_warna rw3 ON rw3.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs3.id_warna  WHEN tw.tipe_id = 2 THEN bso3.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_3 WHEN tw.tipe_id = 2 THEN tsod.id_warna_3 ELSE NULL END
            )
            LEFT JOIN ref_warna rw4 ON rw4.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs4.id_warna  WHEN tw.tipe_id = 2 THEN bso4.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_4 WHEN tw.tipe_id = 2 THEN tsod.id_warna_4 ELSE NULL END
            )
            LEFT JOIN ref_warna rw5 ON rw5.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs5.id_warna  WHEN tw.tipe_id = 2 THEN bso5.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_5 WHEN tw.tipe_id = 2 THEN tsod.id_warna_5 ELSE NULL END
            )
            LEFT JOIN ref_warna rw6 ON rw6.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs6.id_warna  WHEN tw.tipe_id = 2 THEN bso6.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_6 WHEN tw.tipe_id = 2 THEN tsod.id_warna_6 ELSE NULL END
            )
            LEFT JOIN ref_warna rw7 ON rw7.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs7.id_warna  WHEN tw.tipe_id = 2 THEN bso7.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_7 WHEN tw.tipe_id = 2 THEN tsod.id_warna_7 ELSE NULL END
            )
            LEFT JOIN ref_warna rw8 ON rw8.id = COALESCE(
                CASE WHEN tw.tipe_id = 1 THEN bs8.id_warna  WHEN tw.tipe_id = 2 THEN bso8.id_warna  ELSE NULL END,
                CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_8 WHEN tw.tipe_id = 2 THEN tsod.id_warna_8 ELSE NULL END
            );
        ";

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
