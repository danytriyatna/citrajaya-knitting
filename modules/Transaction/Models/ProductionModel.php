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
                $val = strtolower(trim($filters[0]['value']));
                $value = "%{$val}%";
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_prod) LIKE', $value);
                $builder->orWhere('LOWER(abx.kode_walkorder) LIKE', $value);
                $builder->orWhere('LOWER(twx.ref_kode) LIKE', $value);
                $builder->orWhere('LOWER(tso.kode_sales_order) LIKE', $value);
                $builder->orWhere('LOWER(abx.keterangan_style) LIKE', $value);
                $builder->orWhere('LOWER(bbx.nama) LIKE', $value);
                $builder->orWhere("LOWER(CONCAT(TRIM(abx.keterangan_style), ' - ', TRIM(bbx.nama))) LIKE", $value);
                $builder->groupEnd();
            }

            $builder->where("(tso.status IS NULL OR tso.status <> 3)");

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
            }

            if (!empty($params['only_active_wo'])) {
                $builder->where('twx.active', 1);
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
        $builder->join("trans_walkorder twx", "abx.id_walkorder = twx.id", "inner");
        $builder->join("trans_sales_order tso", "tso.id = twx.ref_id and twx.tipe_id = 2", "left");
        $builder->select("count(1) as _cnt");
        $builder->where('abx.active = 1');
        $builder->where("(tso.status IS NULL OR tso.status <> 3)");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $val = strtolower(trim($filters[0]['value']));
            $value = "%{$val}%";
            $builder->groupStart();
            $builder->where('LOWER(abx.kode_prod) LIKE', $value);
            $builder->orWhere('LOWER(abx.kode_walkorder) LIKE', $value);
            $builder->orWhere('LOWER(twx.ref_kode) LIKE', $value);
            $builder->orWhere('LOWER(tso.kode_sales_order) LIKE', $value);
            $builder->orWhere('LOWER(abx.keterangan_style) LIKE', $value);
            $builder->orWhere('LOWER(bbx.nama) LIKE', $value);
            $builder->orWhere("LOWER(CONCAT(TRIM(abx.keterangan_style), ' - ', TRIM(bbx.nama))) LIKE", $value);
            $builder->groupEnd();
        }

        if (!empty($params['id_konsumen'])) {
            $builder->where('abx.id_konsumen', $params['id_konsumen']);
        }

        if (!empty($params['only_active_wo'])) {
            $builder->where('twx.active', 1);
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
                                     where tp.id_walkorder = abx.id_walkorder and tpp.id_proses = abx.id_proses and (tpp.print_type = 1 or tpp.print_type is null)),0) as harga_proses");
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
        
        $builder->groupStart();
            $builder->where("tpo.kode_transaksi NOT LIKE", "BTMC%");
            $builder->orWhere("tpo.kode_transaksi IS NULL");
        $builder->groupEnd();
        $builder->groupStart();
            $builder->where("tpo.id_category", 12); // Penerimaan Hasil Produksi
            $builder->orWhere("tpo.id_category IS NULL");
        $builder->groupEnd();

        $builder->groupBy("jpp.nama, jpp.seq, jpp.id");
        if ($last_proses == true) {
            $lastProsesId = null;
            if (!empty($id)) {
                $tpRow = $this->db->table("trans_produksi")->select("id_walkorder")->where("id", $id)->get()->getRow();
                if (!empty($tpRow) && !empty($tpRow->id_walkorder)) {
                    $woLast = $this->db->table("trans_walkorder_proses twp")
                        ->select("twp.id_proses")
                        ->join("_jenis_proses_produksi jpp", "jpp.id = twp.id_proses", "inner")
                        ->where("twp.id_walkorder", $tpRow->id_walkorder)
                        ->orderBy("jpp.seq", "DESC")
                        ->orderBy("twp.id", "DESC")
                        ->limit(1)
                        ->get()
                        ->getRow();
                    if (!empty($woLast)) {
                        $lastProsesId = $woLast->id_proses;
                    }
                }
            }

            if (!empty($lastProsesId)) {
                $builder->where('tpo.id_proses', $lastProsesId);
                $this->_data = $builder->get()->getRow();
            } else {
                $builder->orderBy("jpp.seq", "DESC");
                $this->_data = $builder->get()->getRow();
            }
        }
        else if (!empty($rajut)){
            // 1. Prioritaskan proses 'RAJUT DBT' (sebagai patokan output rajut P1)
            $builderDBT = $this->db->table("trans_produksi_operator tpo");
            $builderDBT->select("jpp.nama, jpp.seq, jpp.id,
                                SUM(CASE WHEN tpo.print_type = 1 OR tpo.print_type IS NULL THEN tpo.qty ELSE 0 END) AS qty_prod,
                                SUM(CASE WHEN tpo.print_type = 2 THEN tpo.qty ELSE 0 END) AS qty_fix");
            $builderDBT->join("trans_produksi tp", "tp.id = tpo.id_produksi", "inner");
            $builderDBT->join("_jenis_proses_produksi jpp", "tpo.id_proses = jpp.id", "inner");
            if(!empty($id)){
                $builderDBT->where('tp.id', $id);
            }
            $builderDBT->groupStart();
                $builderDBT->where("tpo.kode_transaksi NOT LIKE", "BTMC%");
                $builderDBT->orWhere("tpo.kode_transaksi IS NULL");
            $builderDBT->groupEnd();
            $builderDBT->groupStart();
                $builderDBT->where("tpo.id_category", 12); // Penerimaan Hasil Produksi
                $builderDBT->orWhere("tpo.id_category IS NULL");
            $builderDBT->groupEnd();
            $builderDBT->groupStart();
                $builderDBT->where('jpp.id', 31); // RAJUT DBT
                $builderDBT->orWhere("UPPER(jpp.nama) LIKE '%RAJUT DBT%'");
                $builderDBT->orWhere("UPPER(jpp.nama) LIKE '%DBT%'");
            $builderDBT->groupEnd();
            $builderDBT->groupBy("jpp.nama, jpp.seq, jpp.id");
            $rowDBT = $builderDBT->get()->getRow();

            if (!empty($rowDBT) && (float)$rowDBT->qty_prod > 0) {
                $this->_data = $rowDBT;
            } else {
                // 2. Fallback: proses rajut lainnya (is_rajut = 1)
                $builder->where('jpp.is_rajut', 1);
                $builder->orderBy("jpp.seq", "ASC");
                $this->_data = $builder->get()->getRow();
            }
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
        $builder->where("abx.kode_transaksi NOT LIKE", "BTMC%");
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

    function getTotalBTMSO($kode_so = null, $kode_ukuran = null, $color = null){
        if ($kode_ukuran == 'sm') {
            $kode_ukuran = 'S/M';
        }
        else if ($kode_ukuran == 'ml') {
            $kode_ukuran = 'M/L';
        }
        else if ($kode_ukuran == 'lxl') {
            $kode_ukuran = 'L/XL';
        }
        // Ukuran "xxl", "xxxl", dst (n huruf x diikuti l) dihitung otomatis jadi
        // nXL - sebelumnya di-hardcode satu-satu dan cuma sampai xxxxxxl (6XL),
        // jadi ukuran besar spt 7XL-10XL (7-10 huruf x) tidak dikenali sama
        // sekali dan jatuh ke strtoupper() mentah (mis. "XXXXXXXXL"), tidak
        // pernah cocok dengan data BTM yang tersimpan rapi sbg "8XL" - makanya
        // Berat (Kg) utk ukuran besar selalu tampil 0 walau transaksi BTM-nya
        // ada (kasus SOD260700028 / LS-3, ukuran 8XL & 10XL).
        // x{2,} (bukan x+) - ukuran "xl" (1 huruf x = XL) sengaja TIDAK masuk sini,
        // karena XL sudah benar tanpa perlu dikonversi (strtoupper('xl') = 'XL').
        // Kalau ikut ditangkap di sini, "xl" akan jadi "1xl"/"1XL" yang salah dan
        // bikin ukuran XL malah hilang (baru ketauan dari kasus invoice SI260800007).
        else if (preg_match('/^x{2,}l$/', (string) $kode_ukuran)) {
            $jumlahX = strlen($kode_ukuran) - 1;
            $kode_ukuran = $jumlahX . 'XL';
        }
        $kode_ukuran = strtoupper(trim($kode_ukuran));
        $builder = $this->db->table("trans_barang_masuk_produksi abx");
        $builder->select("COALESCE(SUM(abx.berat), 0) as total_btm");
        $builder->join("trans_barang_header bbx", "abx.id_header = bbx.id", "inner");
        $builder->where('bbx.active', 1);
        $builder->where('abx.kode_sales_order', $kode_so);
        $builder->where('UPPER(TRIM(abx.kode_ukuran))', $kode_ukuran);
        // TRIM() krn data warna kadang tersimpan dgn spasi ekstra di akhir
        // (mis. "ACR 32/2 NAVY~ACR 32/2 PUTIH " dgn 1 spasi tambahan setelah
        // PUTIH), sehingga perbandingan persis (=) gagal walau isinya sama.
        $builder->where('TRIM(abx.color)', trim((string) $color));
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


    function getProduksilastV1($params)
    {
        $id_walkorder = (int)$params['id_walkorder'];
        $wo = $this->db->table('trans_walkorder')->where('id', $id_walkorder)->get()->getRow();
        if (empty($wo)) return [];

        $ukuranArr = array_unique(array_map('trim', explode(",", $params['ukuran'])));

        $builder = $this->db->table('trans_walkorder_detail wodet');
        $builder->select('wodet.id as wo_det_id, wodet.ref_detail_id, wodet.id_walkorder');
        $builder->where('wodet.id_walkorder', $id_walkorder);
        $builder->where('wodet.active', 1);
        $woDets = $builder->get()->getResult();

        if (empty($woDets)) return [];

        $results = [];
        $db = $this->db;

        foreach ($woDets as $row) {
            $obj = new \stdClass();
            $obj->ref_detail_id = $row->ref_detail_id;
            $obj->id_walkorder  = $row->id_walkorder;
            $obj->id_proses     = 0;
            $obj->tipe_id       = $wo->tipe_id;

            if ($wo->tipe_id == 1) {
                $detRow = $db->table('trans_sample_det tsd')
                    ->select("
                        CASE WHEN tsd.id_barang_1 IS NOT NULL THEN 'via_barang' ELSE 'via_warna' END AS sumber_warna,
                        TRIM(BOTH ' - ' FROM
                            COALESCE(COALESCE(b1.nama_barang, rw1.kode_warna), '') ||
                            CASE WHEN COALESCE(b2.nama_barang, rw2.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b2.nama_barang, rw2.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b3.nama_barang, rw3.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b3.nama_barang, rw3.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b4.nama_barang, rw4.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b4.nama_barang, rw4.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b5.nama_barang, rw5.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b5.nama_barang, rw5.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b6.nama_barang, rw6.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b6.nama_barang, rw6.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b7.nama_barang, rw7.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b7.nama_barang, rw7.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b8.nama_barang, rw8.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b8.nama_barang, rw8.kode_warna) ELSE '' END
                        ) AS kode_warna
                    ")
                    ->join('ref_barang b1', 'b1.id = tsd.id_barang_1', 'left')
                    ->join('ref_barang b2', 'b2.id = tsd.id_barang_2', 'left')
                    ->join('ref_barang b3', 'b3.id = tsd.id_barang_3', 'left')
                    ->join('ref_barang b4', 'b4.id = tsd.id_barang_4', 'left')
                    ->join('ref_barang b5', 'b5.id = tsd.id_barang_5', 'left')
                    ->join('ref_barang b6', 'b6.id = tsd.id_barang_6', 'left')
                    ->join('ref_barang b7', 'b7.id = tsd.id_barang_7', 'left')
                    ->join('ref_barang b8', 'b8.id = tsd.id_barang_8', 'left')
                    ->join('ref_warna rw1', 'rw1.id = COALESCE(b1.id_warna, tsd.id_warna_1)', 'left')
                    ->join('ref_warna rw2', 'rw2.id = COALESCE(b2.id_warna, tsd.id_warna_2)', 'left')
                    ->join('ref_warna rw3', 'rw3.id = COALESCE(b3.id_warna, tsd.id_warna_3)', 'left')
                    ->join('ref_warna rw4', 'rw4.id = COALESCE(b4.id_warna, tsd.id_warna_4)', 'left')
                    ->join('ref_warna rw5', 'rw5.id = COALESCE(b5.id_warna, tsd.id_warna_5)', 'left')
                    ->join('ref_warna rw6', 'rw6.id = COALESCE(b6.id_warna, tsd.id_warna_6)', 'left')
                    ->join('ref_warna rw7', 'rw7.id = COALESCE(b7.id_warna, tsd.id_warna_7)', 'left')
                    ->join('ref_warna rw8', 'rw8.id = COALESCE(b8.id_warna, tsd.id_warna_8)', 'left')
                    ->where('tsd.id', $row->ref_detail_id)
                    ->get()->getRow();

                $ukRows = $db->table('trans_sample_ukuran tsu')
                    ->select('ru.key_ukuran, tsu.qty, tsu.harga_satuan')
                    ->join('ref_ukuran ru', 'ru.id = tsu.id_ukuran')
                    ->where('tsu.id_sample_det', $row->ref_detail_id)
                    ->get()->getResult();
            } else {
                $detRow = $db->table('trans_sales_order_det tsod')
                    ->select("
                        CASE WHEN tsod.id_barang_1 IS NOT NULL THEN 'via_barang' ELSE 'via_warna' END AS sumber_warna,
                        TRIM(BOTH ' - ' FROM
                            COALESCE(COALESCE(b1.nama_barang, rw1.kode_warna), '') ||
                            CASE WHEN COALESCE(b2.nama_barang, rw2.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b2.nama_barang, rw2.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b3.nama_barang, rw3.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b3.nama_barang, rw3.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b4.nama_barang, rw4.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b4.nama_barang, rw4.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b5.nama_barang, rw5.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b5.nama_barang, rw5.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b6.nama_barang, rw6.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b6.nama_barang, rw6.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b7.nama_barang, rw7.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b7.nama_barang, rw7.kode_warna) ELSE '' END ||
                            CASE WHEN COALESCE(b8.nama_barang, rw8.kode_warna) IS NOT NULL THEN ' - ' || COALESCE(b8.nama_barang, rw8.kode_warna) ELSE '' END
                        ) AS kode_warna
                    ")
                    ->join('ref_barang b1', 'b1.id = tsod.id_barang_1', 'left')
                    ->join('ref_barang b2', 'b2.id = tsod.id_barang_2', 'left')
                    ->join('ref_barang b3', 'b3.id = tsod.id_barang_3', 'left')
                    ->join('ref_barang b4', 'b4.id = tsod.id_barang_4', 'left')
                    ->join('ref_barang b5', 'b5.id = tsod.id_barang_5', 'left')
                    ->join('ref_barang b6', 'b6.id = tsod.id_barang_6', 'left')
                    ->join('ref_barang b7', 'b7.id = tsod.id_barang_7', 'left')
                    ->join('ref_barang b8', 'b8.id = tsod.id_barang_8', 'left')
                    ->join('ref_warna rw1', 'rw1.id = COALESCE(b1.id_warna, tsod.id_warna_1)', 'left')
                    ->join('ref_warna rw2', 'rw2.id = COALESCE(b2.id_warna, tsod.id_warna_2)', 'left')
                    ->join('ref_warna rw3', 'rw3.id = COALESCE(b3.id_warna, tsod.id_warna_3)', 'left')
                    ->join('ref_warna rw4', 'rw4.id = COALESCE(b4.id_warna, tsod.id_warna_4)', 'left')
                    ->join('ref_warna rw5', 'rw5.id = COALESCE(b5.id_warna, tsod.id_warna_5)', 'left')
                    ->join('ref_warna rw6', 'rw6.id = COALESCE(b6.id_warna, tsod.id_warna_6)', 'left')
                    ->join('ref_warna rw7', 'rw7.id = COALESCE(b7.id_warna, tsod.id_warna_7)', 'left')
                    ->join('ref_warna rw8', 'rw8.id = COALESCE(b8.id_warna, tsod.id_warna_8)', 'left')
                    ->where('tsod.id', $row->ref_detail_id)
                    ->get()->getRow();

                $ukRows = $db->table('trans_sales_order_ukuran tsou')
                    ->select('ru.key_ukuran, tsou.qty, tsou.harga_satuan')
                    ->join('ref_ukuran ru', 'ru.id = tsou.id_ukuran')
                    ->where('tsou.id_sales_order_det', $row->ref_detail_id)
                    ->get()->getResult();
            }

            $obj->sumber_warna = !empty($detRow->sumber_warna) ? $detRow->sumber_warna : 'via_warna';
            $obj->kode_warna   = !empty($detRow->kode_warna) ? $detRow->kode_warna : '';

            $ukMap = [];
            $hrgMap = [];
            if (!empty($ukRows)) {
                foreach ($ukRows as $uk) {
                    $k = $uk->key_ukuran == 'all' ? 'all_' : $uk->key_ukuran;
                    $ukMap[$k] = (float)$uk->qty;
                    $hrgMap[$k] = (float)$uk->harga_satuan;
                }
            }

            foreach ($ukuranArr as $item) {
                $item = trim($item);
                $k = $item == 'all' ? 'all_' : $item;
                $hrgKey = $k . '_hrg';
                $obj->$k = isset($ukMap[$k]) ? $ukMap[$k] : 0;
                $obj->$hrgKey = isset($hrgMap[$k]) ? $hrgMap[$k] : 0;
            }

            $results[] = $obj;
        }

        return $results;
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

    public function getLabaKotorData($id_produksi, $id_walkorder, $id_so, $tipe_id, $kode_walkorder)
    {
        $idSo = (int)$id_so;
        $idProd = (int)$id_produksi;
        $kodeWo = trim((string)$kode_walkorder);

        $nilai_so = 0;
        $nilai_pendapatan = 0;
        $nilai_bahan = 0;
        $nilai_tk = 0;

        if ($tipe_id == 2 && !empty($idSo)) {
            // 1. Nilai SO (Target/Kontrak)
            $soUk = $this->db->query("
                SELECT COALESCE(SUM(qty * harga_satuan), 0) as total_so 
                FROM trans_sales_order_ukuran 
                WHERE id_sales_order = {$idSo}
            ")->getRow();
            $soRow = $this->db->table('trans_sales_order')->select('uang_dp, uang_dp_2, pengiriman')->where('id', $idSo)->get()->getRow();
            $nilai_so = (float)($soUk->total_so ?? 0) + (float)($soRow->pengiriman ?? 0);

            // 2. Pendapatan (DP1 + DP2 + Pembayaran Pelunasan CR)
            $dp1 = (float)($soRow->uang_dp ?? 0);
            $dp2 = (float)($soRow->uang_dp_2 ?? 0);

            $crRow = $this->db->query("
                SELECT COALESCE(SUM(crd.pay_item), 0) as total_cr
                FROM trans_customer_receipt_detail crd
                INNER JOIN trans_customer_receipt cr ON cr.id = crd.id_cr
                INNER JOIN trans_invoice_detail tid ON tid.id_invoice = crd.id_invoice
                WHERE tid.id_ref = {$idSo} AND cr.active = 1 AND cr.status = 1
            ")->getRow();
            $crPay = (float)($crRow->total_cr ?? 0);
            $nilai_pendapatan = $dp1 + $dp2 + $crPay;
        }

        // 3. Nilai Bahan Baku (BTK Pemakaian id_kategori = 5)
        if (!empty($kodeWo)) {
            $bbRow = $this->db->query("
                SELECT COALESCE(SUM(tbd.qty * COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0)), 0) as total_bahan
                FROM trans_barang_detail tbd
                INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                LEFT JOIN ref_barang rb ON rb.id = tbd.id_barang
                WHERE tbh.no_ref_wo = ? AND tbh.id_kategori = 5 AND tbh.active = 1
            ", [$kodeWo])->getRow();
            $nilai_bahan = (float)($bbRow->total_bahan ?? 0);
        }

        // 4. Nilai Tenaga Kerja (trans_produksi_operator) - Hanya P1 (BTM / Non-BTMC), P2 tidak dihitung
        if (!empty($idProd)) {
            $tkRow = $this->db->query("
                SELECT COALESCE(SUM(COALESCE(tpo.harga_total, tpo.qty * tpo.harga, 0)), 0) as total_tk
                FROM trans_produksi_operator tpo
                WHERE tpo.id_produksi = {$idProd}
                AND (tpo.kode_transaksi NOT LIKE 'BTMC%' OR tpo.kode_transaksi IS NULL)
                AND (tpo.print_type = 1 OR tpo.print_type IS NULL)
            ")->getRow();
            $nilai_tk = (float)($tkRow->total_tk ?? 0);
        }

        // 5. Laba Kotor
        $laba_kotor = $nilai_pendapatan - ($nilai_bahan + $nilai_tk);
        $laba_kotor_so = $nilai_so - ($nilai_bahan + $nilai_tk);

        $margin_persen = ($nilai_pendapatan > 0) ? round(($laba_kotor / $nilai_pendapatan) * 100, 1) : 0;
        $margin_so_persen = ($nilai_so > 0) ? round(($laba_kotor_so / $nilai_so) * 100, 1) : 0;

        $qty_so = 0;
        if (!empty($idProd)) {
            $tpQtyRow = $this->db->table("trans_produksi")->select("qty")->where("id", $idProd)->get()->getRow();
            if (!empty($tpQtyRow) && !empty($tpQtyRow->qty)) {
                $qty_so = (float)$tpQtyRow->qty;
            }
        }
        if (empty($qty_so) && !empty($idSo)) {
            if ($tipe_id == 1) {
                $sampleUkRow = $this->db->query("SELECT COALESCE(SUM(qty), 0) as tot_qty FROM trans_sample_ukuran WHERE id_sample = {$idSo}")->getRow();
                $qty_so = (float)($sampleUkRow->tot_qty ?? 0);
            } else {
                $soUkRow = $this->db->query("SELECT COALESCE(SUM(qty), 0) as tot_qty FROM trans_sales_order_ukuran WHERE id_sales_order = {$idSo}")->getRow();
                $qty_so = (float)($soUkRow->tot_qty ?? 0);
            }
        }

        return [
            'qty_so'            => $qty_so,
            'f_qty_so'          => number_format($qty_so, 0, ',', '.') . ' Pcs',
            'nilai_so'          => $nilai_so,
            'nilai_pendapatan'  => $nilai_pendapatan,
            'nilai_bahan'       => $nilai_bahan,
            'nilai_tk'          => $nilai_tk,
            'laba_kotor'        => $laba_kotor,
            'laba_kotor_so'     => $laba_kotor_so,
            'margin_persen'     => $margin_persen,
            'margin_so_persen'  => $margin_so_persen,
            'f_nilai_so'        => 'Rp ' . number_format($nilai_so, 0, ',', '.'),
            'f_nilai_pendapatan'=> 'Rp ' . number_format($nilai_pendapatan, 0, ',', '.'),
            'f_nilai_bahan'     => 'Rp ' . number_format($nilai_bahan, 0, ',', '.'),
            'f_nilai_tk'        => 'Rp ' . number_format($nilai_tk, 0, ',', '.'),
            'f_laba_kotor'      => 'Rp ' . number_format($laba_kotor, 0, ',', '.'),
            'f_laba_kotor_so'   => 'Rp ' . number_format($laba_kotor_so, 0, ',', '.')
        ];
    }

    public function getLabaKotorBreakdown($id_produksi, $kode_walkorder)
    {
        $idProd = (int)$id_produksi;
        $kodeWo = trim((string)$kode_walkorder);

        // Target Qty SO
        $qty_so = 0;
        if (!empty($idProd)) {
            $tpRow = $this->db->table("trans_produksi tp")
                ->select("tp.qty, tw.ref_id, tw.tipe_id")
                ->join("trans_walkorder tw", "tw.id = tp.id_walkorder", "left")
                ->where("tp.id", $idProd)
                ->get()->getRow();
            if (!empty($tpRow)) {
                $qty_so = (float)($tpRow->qty ?? 0);
                if (empty($qty_so) && !empty($tpRow->ref_id)) {
                    if ($tpRow->tipe_id == 1) {
                        $sRow = $this->db->query("SELECT COALESCE(SUM(qty), 0) as tot FROM trans_sample_ukuran WHERE id_sample = {$tpRow->ref_id}")->getRow();
                        $qty_so = (float)($sRow->tot ?? 0);
                    } else {
                        $sRow = $this->db->query("SELECT COALESCE(SUM(qty), 0) as tot FROM trans_sales_order_ukuran WHERE id_sales_order = {$tpRow->ref_id}")->getRow();
                        $qty_so = (float)($sRow->tot ?? 0);
                    }
                }
            }
        }

        // Breakdown Tenaga Kerja
        $tk_list = [];
        if (!empty($idProd)) {
            $tk_list = $this->db->query("
                SELECT jpp.nama as nama_proses, jpp.seq,
                       SUM(CASE WHEN tpo.print_type = 1 OR tpo.print_type IS NULL THEN tpo.qty ELSE 0 END) AS qty_prod,
                       SUM(CASE WHEN tpo.print_type = 2 THEN tpo.qty ELSE 0 END) AS qty_fix,
                       SUM(CASE WHEN tpo.print_type = 1 OR tpo.print_type IS NULL THEN tpo.qty ELSE 0 END) AS total_qty,
                       COALESCE(AVG(CASE WHEN tpo.print_type = 1 OR tpo.print_type IS NULL THEN tpo.harga ELSE NULL END), 0) as avg_harga,
                       COALESCE(SUM(CASE WHEN tpo.print_type = 1 OR tpo.print_type IS NULL THEN COALESCE(tpo.harga_total, tpo.qty * tpo.harga, 0) ELSE 0 END), 0) as subtotal_ongkos
                FROM trans_produksi_operator tpo
                INNER JOIN _jenis_proses_produksi jpp ON jpp.id = tpo.id_proses
                WHERE tpo.id_produksi = {$idProd}
                AND (tpo.kode_transaksi NOT LIKE 'BTMC%' OR tpo.kode_transaksi IS NULL)
                GROUP BY jpp.nama, jpp.seq, jpp.id
                ORDER BY jpp.seq ASC
            ")->getResult();

            foreach ($tk_list as $tk) {
                $tk->qty_so = $qty_so;
            }
        }

        // Breakdown Bahan Baku
        $bb_list = [];
        if (!empty($kodeWo)) {
            $bb_list = $this->db->query("
                SELECT rb.kode_barang, rb.nama_barang, rw.kode_warna, rw.keterangan as nama_warna,
                       SUM(tbd.qty) as qty_pakai,
                       COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0) as harga_satuan,
                       SUM(tbd.qty * COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0)) as subtotal_bahan
                FROM trans_barang_detail tbd
                INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                LEFT JOIN ref_barang rb ON rb.id = tbd.id_barang
                LEFT JOIN ref_warna rw ON rw.id = rb.id_warna
                WHERE tbh.no_ref_wo = ? AND tbh.id_kategori = 5 AND tbh.active = 1
                GROUP BY rb.kode_barang, rb.nama_barang, rw.kode_warna, rw.keterangan, tbd.price, rb.harga_satuan
                ORDER BY rb.nama_barang ASC
            ", [$kodeWo])->getResult();
        }

        return [
            'qty_so'       => $qty_so,
            'f_qty_so'     => number_format($qty_so, 0, ',', '.') . ' Pcs',
            'tenaga_kerja' => $tk_list,
            'bahan_baku'   => $bb_list
        ];
    }

    public function getGlobalLabaKotorSummary($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");
        $builder->select("abx.id, abx.kode_walkorder, twx.ref_id as id_so, twx.tipe_id");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("trans_walkorder twx", "abx.id_walkorder = twx.id", "inner");
        $builder->join("trans_sales_order tso", "tso.id = twx.ref_id and twx.tipe_id = 2", "left");
        $builder->where('abx.active = 1');
        $builder->where("(tso.status IS NULL OR tso.status <> 3)");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $val = strtolower(trim($filters[0]['value']));
            $value = "%{$val}%";
            $builder->groupStart();
            $builder->where('LOWER(abx.kode_prod) LIKE', $value);
            $builder->orWhere('LOWER(abx.kode_walkorder) LIKE', $value);
            $builder->orWhere('LOWER(twx.ref_kode) LIKE', $value);
            $builder->orWhere('LOWER(tso.kode_sales_order) LIKE', $value);
            $builder->orWhere('LOWER(abx.keterangan_style) LIKE', $value);
            $builder->orWhere('LOWER(bbx.nama) LIKE', $value);
            $builder->orWhere("LOWER(CONCAT(TRIM(abx.keterangan_style), ' - ', TRIM(bbx.nama))) LIKE", $value);
            $builder->groupEnd();
        }

        if (!empty($params['id_konsumen'])) {
            $builder->where('abx.id_konsumen', $params['id_konsumen']);
        }
        if (!empty($params['tipe_id'])) {
            $builder->where('abx.tipe_id', $params['tipe_id']);
        }
        if (!empty($params['tahun'])) {
            $builder->where('EXTRACT(YEAR FROM abx.tgl_transaksi) =', (int)$params['tahun']);
        }

        $prodRows = $builder->get()->getResult();

        $tot_nilai_so = 0;
        $tot_pendapatan = 0;
        $tot_bahan = 0;
        $tot_tk = 0;

        if (!empty($prodRows)) {
            $soIds = [];
            $woCodes = [];
            $prodIds = [];
            foreach ($prodRows as $pr) {
                if ($pr->tipe_id == 2 && !empty($pr->id_so)) {
                    $soIds[$pr->id_so] = (int)$pr->id_so;
                }
                if (!empty($pr->kode_walkorder)) {
                    $woCodes[] = "'" . addslashes(trim($pr->kode_walkorder)) . "'";
                }
                $prodIds[] = (int)$pr->id;
            }

            // A. Total Nilai SO & DP
            if (!empty($soIds)) {
                $soIdList = implode(',', $soIds);
                $soUk = $this->db->query("
                    SELECT COALESCE(SUM(qty * harga_satuan), 0) as tot_so 
                    FROM trans_sales_order_ukuran 
                    WHERE id_sales_order IN ({$soIdList})
                ")->getRow();
                $soHead = $this->db->query("
                    SELECT COALESCE(SUM(uang_dp), 0) + COALESCE(SUM(uang_dp_2), 0) as tot_dp,
                           COALESCE(SUM(pengiriman), 0) as tot_ongkir
                    FROM trans_sales_order 
                    WHERE id IN ({$soIdList})
                ")->getRow();
                $tot_nilai_so = (float)($soUk->tot_so ?? 0) + (float)($soHead->tot_ongkir ?? 0);
                $tot_dp = (float)($soHead->tot_dp ?? 0);

                // CR payments
                $crRow = $this->db->query("
                    SELECT COALESCE(SUM(crd.pay_item), 0) as tot_cr
                    FROM trans_customer_receipt_detail crd
                    INNER JOIN trans_customer_receipt cr ON cr.id = crd.id_cr
                    INNER JOIN trans_invoice_detail tid ON tid.id_invoice = crd.id_invoice
                    WHERE tid.id_ref IN ({$soIdList}) AND cr.active = 1 AND cr.status = 1
                ")->getRow();
                $tot_cr = (float)($crRow->tot_cr ?? 0);
                $tot_pendapatan = $tot_dp + $tot_cr;
            }

            // B. Total Bahan Baku
            if (!empty($woCodes)) {
                $woList = implode(',', array_unique($woCodes));
                $bbRow = $this->db->query("
                    SELECT COALESCE(SUM(tbd.qty * COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0)), 0) as tot_bahan
                    FROM trans_barang_detail tbd
                    INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                    LEFT JOIN ref_barang rb ON rb.id = tbd.id_barang
                    WHERE tbh.no_ref_wo IN ({$woList}) AND tbh.id_kategori = 5 AND tbh.active = 1
                ")->getRow();
                $tot_bahan = (float)($bbRow->tot_bahan ?? 0);
            }

            // C. Total Tenaga Kerja
            if (!empty($prodIds)) {
                $prodList = implode(',', $prodIds);
                $tkRow = $this->db->query("
                    SELECT COALESCE(SUM(COALESCE(tpo.harga_total, tpo.qty * tpo.harga, 0)), 0) as tot_tk
                    FROM trans_produksi_operator tpo
                    WHERE tpo.id_produksi IN ({$prodList})
                    AND (tpo.kode_transaksi NOT LIKE 'BTMC%' OR tpo.kode_transaksi IS NULL)
                    AND (tpo.print_type = 1 OR tpo.print_type IS NULL)
                ")->getRow();
                $tot_tk = (float)($tkRow->tot_tk ?? 0);
            }
        }

        $tot_laba_kotor = $tot_pendapatan - ($tot_bahan + $tot_tk);
        $tot_margin_persen = ($tot_pendapatan > 0) ? round(($tot_laba_kotor / $tot_pendapatan) * 100, 1) : 0;
        $tot_laba_kotor_so = $tot_nilai_so - ($tot_bahan + $tot_tk);
        $tot_margin_so_persen = ($tot_nilai_so > 0) ? round(($tot_laba_kotor_so / $tot_nilai_so) * 100, 1) : 0;

        return [
            'total_nilai_so'        => $tot_nilai_so,
            'total_nilai_pendapatan'=> $tot_pendapatan,
            'total_nilai_bahan'     => $tot_bahan,
            'total_nilai_tk'        => $tot_tk,
            'total_laba_kotor'      => $tot_laba_kotor,
            'total_margin_persen'   => $tot_margin_persen,
            'total_laba_kotor_so'   => $tot_laba_kotor_so,
            'total_margin_so_persen'=> $tot_margin_so_persen,
            'f_total_nilai_so'      => 'Rp ' . number_format($tot_nilai_so, 0, ',', '.'),
            'f_total_nilai_pendapatan' => 'Rp ' . number_format($tot_pendapatan, 0, ',', '.'),
            'f_total_nilai_bahan'   => 'Rp ' . number_format($tot_bahan, 0, ',', '.'),
            'f_total_nilai_tk'      => 'Rp ' . number_format($tot_tk, 0, ',', '.'),
            'f_total_laba_kotor'    => 'Rp ' . number_format($tot_laba_kotor, 0, ',', '.'),
            'f_total_laba_kotor_so' => 'Rp ' . number_format($tot_laba_kotor_so, 0, ',', '.')
        ];
    }
     
}
