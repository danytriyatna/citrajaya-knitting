<?php

namespace Modules\Transaction\Models;

class ItemTransferModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_trf_header";
    protected $tblDet = "trans_barang_trf_detail";
    protected $tblDetSO = "trans_barang_trf_so";
    protected $tblDetailSO = "trans_barang_trf_so_det";

    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblBuyer = "ref_konsumen";
    protected $tblOperator = "ref_operator";
    protected $tblKategori = "ref_kategori_persediaan";
    protected $tblSatuan = "ref_satuan";
    protected $tblTrxBarang = "trans_barang";
    protected $tblTrxLots = "trans_lots";
    protected $tblTrxBalances = "trans_barang_balances";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang_asal = abx.id", "left");
        $builder->join($this->tblGudang . " bbx", "uk.id_gudang_tujuan = bbx.id", "left");
        $builder->join($this->tblOperator . " cbx", "uk.id_cmt = cbx.id", "left");

        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');

        $builder->select("uk.id,uk.tanggal,uk.tipe,uk.id_cmt,uk.tipe,id_proses, cbx.nama_operator,  abx.nama_gudang as gudang_asal, bbx.nama_gudang as gudang_tujuan,
                          uk.id_gudang_tujuan, uk.id_gudang_asal, uk.kode_transaksi, uk.status, uk.tanggal, uk.keterangan, uk.ref_produk,
                          jp.seq as no_proses, jp.nama as proses");

        if (!empty($params['status'])) {
            $builder->where('uk.status = 1');
        }

        if(!empty($params['ref_produksi'])){
            if($params['ref_produksi'] == 1){
                $builder->where('ref_produk', 1);
            }else{
                $builder->where('ref_produk', 0);
            }
        }

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('id DESC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);
            

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang_asal = abx.id", "left");
        $builder->join($this->tblGudang . " bbx", "uk.id_gudang_tujuan = bbx.id", "left");
        $builder->select("count(1) as _cnt");
        $builder->where('uk.active = 1');
        if (!empty($params['status'])) {
            $builder->where('uk.status = 1');
        }

        if(!empty($params['ref_produksi'])){
            if($params['ref_produksi'] == 1){
                $builder->where('ref_produk', $params['ref_produksi']);
            }else{
                $builder->where('ref_produk', 0);
            }
        }


        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(bbx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataSO($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_sales_order_det" . " abx");
        $builder->join("trans_sales_order_ukuran" . " bbx", "bbx.id_sales_order_det = abx.id", "inner");
        $builder->join("trans_sales_order" . " cbx", "abx.id_sales_order = cbx.id", "inner");
        $builder->join("ref_warna" . " dbx", "abx.id_warna_1 = dbx.id", "left");
        $builder->join("ref_warna" . " ebx", "abx.id_warna_2 = ebx.id", "left");
        $builder->join("ref_konsumen" . " fbx", "fbx.id = cbx.id_konsumen", "left");

        $builder->select("cbx.kode_sales_order,cbx.id_konsumen,cbx.deskripsi,cbx.style,SUM(bbx.harga_satuan) AS amount,SUM(bbx.qty) AS qty,CASE WHEN ebx.kode_warna IS NOT NULL THEN CONCAT(dbx.kode_warna,'-',ebx.kode_warna)  ELSE dbx.kode_warna END AS color,fbx.nama AS buyer");

        if ($id == null or $id == "") {
            $builder->where('cbx.active = 1');
            $builder->where('cbx.status = 2');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(cbx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(cbx.style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(cbx.deskripsi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(dbx.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }
            $builder->groupBy("cbx.style,cbx.id_konsumen,cbx.kode_sales_order,cbx.deskripsi,dbx.kode_warna,ebx.kode_warna,fbx.nama");
            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('cbx.kode_sales_order');
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

    function getUkuranTrans($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table('v_all_transaksi tu');
        
        $builder->select("  tu.tipe, tu.tipe_text,
                            tu.id_ukuran, tu.key_ukuran, tu.kode_ukuran, tu.id_header,
                            tu.kode_transaksi,tu.id_konsumen,tu.deskripsi,tu.style,tu.amount, 
                            tu.qty, tu.color,
                            tu.buyer");
        
        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(tu.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tu.style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tu.deskripsi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tu.color) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if(!empty($params['kata_kunci'])){
                $builder->groupStart();
                $builder->where('LOWER(tu.kode_transaksi) LIKE', strtolower("%{$params['kata_kunci']}%"));
                $builder->orWhere('LOWER(tu.kode_ukuran) LIKE', strtolower("%{$params['kata_kunci']}%"));
                $builder->orWhere('LOWER(tu.deskripsi) LIKE', strtolower("%{$params['kata_kunci']}%"));
                $builder->orWhere('LOWER(tu.style) LIKE', strtolower("%{$params['kata_kunci']}%"));
                $builder->orWhere('LOWER(tu.color) LIKE', strtolower("%{$params['kata_kunci']}%"));
                $builder->groupEnd();
            }
            $builder->where('tu.qty IS NOT NULL');
            // Filter per item
            if (!empty($params['kode_transaksi'])) {
                $builder->where('tu.kode_transaksi', $params['kode_transaksi']);
            }
            if (!empty($params['kode_ukuran'])) {
                $builder->where('lower(tu.key_ukuran)', strtolower($params['kode_ukuran']));
            }
            if (!empty($params['deskripsi'])) {
                $builder->where('tu.deskripsi', $params['deskripsi']);
            }
            if (!empty($params['style'])) {
                $builder->where('tu.style', strtolower($params['style']));
            }
            if (!empty($params['color'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color']);
                $prm_color = strtoupper($ascii);
                // Hilangkan spasi setelah value, misal "dan " jadi "dan"
                $prm_color = rtrim($prm_color);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 1)))", $prm_color);
                // $builder->where('lower(tu.color) LIKE', strtolower("%{$params['color']}%"));
            }

            if (!empty($params['color2'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color2']);
                $prm_color2 = strtoupper($ascii);
                $prm_color2 = rtrim($prm_color2);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 2)))", $prm_color2);
            }
            if (!empty($params['color3'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color3']);
                $prm_color3 = strtoupper($ascii);
                $prm_color3 = rtrim($prm_color3);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 3)))", $prm_color3);
            }
            if (!empty($params['color4'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color4']);
                $prm_color4 = strtoupper($ascii);
                $prm_color4 = rtrim($prm_color4);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 4)))", $prm_color4);
            }
            if (!empty($params['color5'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color5']);
                $prm_color5 = strtoupper($ascii);
                $prm_color5 = rtrim($prm_color5);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 5)))", $prm_color5);
            }
            if (!empty($params['color6'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color6']);
                $prm_color6 = strtoupper($ascii);
                $prm_color6 = rtrim($prm_color6);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 6)))", $prm_color6);
            }
            if (!empty($params['color7'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color7']);
                $prm_color7 = strtoupper($ascii);
                $prm_color7 = rtrim($prm_color7);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 7)))", $prm_color7);
            }
            if (!empty($params['color8'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color8']);
                $prm_color8 = strtoupper($ascii);
                $prm_color8 = rtrim($prm_color8);
                // $builder->where("lower(tu.color)  ~ '^" . $prm_color . "'");
                $builder->where(" upper(trim(split_part(tu.color, '~', 8)))", $prm_color8);
            }
            
            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('tu.tgl_transaksi');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);
            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("tu.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataSOCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("trans_sales_order_det" . " abx");
        $builder->join("trans_sales_order_ukuran" . " bbx", "bbx.id_sales_order_det = abx.id", "inner");
        $builder->join("trans_sales_order" . " cbx", "abx.id_sales_order = cbx.id", "inner");
        $builder->join("ref_warna" . " dbx", "abx.id_warna_1 = dbx.id", "left");
        $builder->join("ref_warna" . " ebx", "abx.id_warna_2 = ebx.id", "left");
        $builder->join("ref_konsumen" . " fbx", "fbx.id = cbx.id_konsumen", "left");
        $builder->select("count(1) as _cnt");
        $builder->where('cbx.active = 1');
        $builder->where('cbx.status = 2');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(cbx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(cbx.style) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(cbx.deskripsi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(dbx.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }
        $builder->groupBy("cbx.style,cbx.kode_sales_order,cbx.deskripsi,dbx.kode_warna,ebx.kode_warna,fbx.nama");
        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataSOUkuranCnt($filters = null, $params = null)
    {
        $builder = $this->db->table('v_all_transaksi tu');
        
        $builder->select("count(1) as _cnt");
        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(tu.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(tu.style) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(tu.deskripsi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(tu.color) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataProses()
    {
        $builder = $this->db->table("_jenis_proses_produksi abx");
        $builder->select("abx.nama, abx.id");
        $builder->where("active", "1");
        $builder->orderBy("abx.seq", "ASC");
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function getDataByNoTrf($noTrf)
    {
        $builder = $this->db->table("trans_barang_trf_header abx");
        $builder->select("abx.id");
        $builder->where("kode_transaksi", $noTrf);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataByProsesAndOperator($idProses = null, $idOperator = null)
    {
        $builder = $this->db->table("trans_barang_trf_header abx");
        $builder->select("abx.id");
        $builder->where("id_proses", $idProses);
        $builder->where("id_cmt", $idOperator);
        $this->_data = $builder->get()->getResult();
        
        return $this->_data;
    }


    function generateKodePersediaan()
    {
        $kd = "TRF";
        $builder = $this->db->table($this->table . ' a');
        $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");

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

    function trxInsertUpdateRecord($data, $id, $detail, $dataSO)
    {
        $this->db->transStart();
        try {

            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                $this->deleteRecordMultipleColumn($this->tblDetailSO, $arrDelete);
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan();
                $id = $this->insertRecordGetid($this->table,  $data);
            }

            foreach ($dataSO as $rowData) {
                $dataDetail = [
                    // "id_so" => !empty($rowData['id']) ? decrypt($rowData['id']) : null,
                    "id_header" => $id,
                    "color" => $rowData['color'],
                    "deskripsi" => $rowData['deskripsi'],
                    "style" => !empty($rowData['style']) ? $rowData['style'] : null,
                    "qty" => !empty($rowData['qty']) ? $rowData['qty'] : null,
                    // "amount" => !empty($rowData['amount']) ? $rowData['amount'] : null,
                    "id_konsumen" => $rowData['id_konsumen'],
                    "kode_sales_order" => $rowData['kode_sales_order'],
                    "kode_ukuran" => $rowData['kode_ukuran'],
                    "keterangan" => !empty($rowData['keterangan']) ? $rowData['keterangan'] : '',
                ];

                $this->insertRecordGetid($this->tblDetailSO, $dataDetail);
            }

            if(!empty($detail)){
                foreach ($detail as $rowData) {
                    if ($rowData['id_barang'] != "") {
                        // $idBarang = decrypt($rowData['id_barang']);
                        $idBarang = $rowData['id_barang'];
                    }
    
                    $dataDetail = [
                        "id_barang" => $idBarang,
                        "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                        "lot_id" => !empty($rowData['lot_id']) ? $rowData['lot_id'] : 0,
                        "keterangan" => !empty($rowData['keterangan']) ? $rowData['keterangan'] : null,
                        "id_header" => $id,
                        "qty" => $rowData['qty'],
                        "price" => !empty($rowData['price']) ? $rowData['price'] : 0,
                    ];
                    // print_r($this->tblDet);exit;
                    $this->insertRecordGetid($this->tblDet, $dataDetail);
                    
                    if ($data['status'] == 1) {
    
                        $mBarangMasuk = new IncomingGoodsModel();
                        $arrParam =  [
                            "id_barang" => $idBarang,
                            "id_gudang_tujuan" => $data['id_gudang_tujuan'],
                        ];
                        $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, $data['id_gudang_tujuan']);
                        $dataLots = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_no" => $rowData['lot_no'],
                            "qty" => $rowData['qty'],
                            "active" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                        ];
    
    
                        if (!empty($resLotNo)) {
                            $idLotsMasuk = $resLotNo->id;
                            $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty + $rowData['qty']), array("id" => $idLotsMasuk));
                        } else {
                            $idLotsMasuk = $this->insertRecordGetid($this->tblTrxLots, $dataLots);
                        }
    
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang_tujuan'], $idLotsMasuk);
    
                        // $stokAwal = !empty($resData) ? $resData->stok : 0;
                        $dataBarang = [
                            "id_barang" => $idBarang,
                            "jenis_transaksi" => 1,
                            "jumlah" =>  $rowData['qty'],
                            "tanggal" => date("Y-m-d H:i:s"),
                            "id_gudang_tujuan" =>  !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                            "id_kategori" => 4,
                            "keterangan" => "Barang Masuk Dari Transfer",
                            "active" => 1,
                            "tipe" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                            "lot_id" => $idLotsMasuk,
                            "price" => !empty($rowData['price']) ? $rowData['price'] : 0,
                            "kode_transaksi" => $this->generateKodePersediaan(),
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLotsMasuk,
                            "saldo_awal" => 0,
                            "saldo_akhir" => $rowData['qty'],
                            "active" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                        ];
    
                        if (!empty($resData)) {
                            $this->updateRecords($this->tblTrxBalances, array("saldo_akhir" => $resLotNo->qty + $rowData['qty']), array("id" => $resData->id));
                        } else {
                            $this->insertRecordGetid($this->tblTrxBalances, $arrStockBalances);
                        }
    
                        $resLotNo = $mBarangMasuk->getLotNo(null, null, $rowData['lot_id']);
    
                        if (!empty($resLotNo)) {
                            $idLots = $resLotNo->id;
                            $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty - $rowData['qty']), array("id" => $idLots));
                        }
    
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang_asal'], $idLots);
    
                        // $stokAwal = !empty($resData) ? $resData->stok : 0;
                        $dataBarangAsal = [
                            "id_barang" => $idBarang,
                            "jenis_transaksi" => 2,
                            "jumlah" =>  $rowData['qty'],
                            "tanggal" => date("Y-m-d H:i:s"),
                            "id_gudang_asal" =>  !empty($data['id_gudang_asal']) ? $data['id_gudang_asal'] : null,
                            "id_kategori" => 4,
                            "keterangan" => "Barang Keluar Dari Transfer",
                            "active" => 1,
                            "tipe" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "price" => !empty($rowData['price']) ? $rowData['price'] : 0,
                            "kode_transaksi" => $this->generateKodePersediaan(),
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarangAsal);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang_asal']) ? $data['id_gudang_asal'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "saldo_awal" => 0,
                            "saldo_akhir" => $rowData['qty'],
                            "active" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                        ];
                        if (!empty($resData)) {
                            $stock = !empty($rowData['qty_exist']) ? $rowData['qty_exist'] - $rowData['qty'] : 0;
                            $this->updateRecords($this->tblTrxBalances, array("saldo_akhir" => $stock), array("id" => $resData->id));
                        } else {
                            $this->insertRecordGetid($this->tblTrxBalances, $arrStockBalances);
                        }



                    }
                }
            }

            //  if ($data['status'] == 1 && !empty($id) ) {

            //     $trfData =  $this->getData($id);

            //     $brngMasukModel = new BarangMasukModel();

            //     $dtBarang['tanggal'] = $data['tanggal'];
            //     $dtBarang['status'] = 0;
            //     $dtBarang['jenis_transaksi'] = 1;
            //     $dtBarang['id_proses'] = $data['id_proses'];
            //     $dtBarang['id_cmt'] = $data['id_cmt'];
            //     $dtBarang['keterangan'] = $data['keterangan'];
            //     $dtBarang['id_kategori'] = $data['ref_produk'] ? 12 : 0; 
            //     $dtBarang['created_at'] = !empty($data['updated_at']) ? $data['updated_at'] : $data['created_at'];
            //     $dtBarang['created_by'] = !empty($data['updated_by']) ? $data['updated_by'] : $data['created_by'];
            //     $dtBarang['id_gudang'] = $data['id_gudang_asal'];
            //     $dtBarang['no_ref_trf'] = $trfData->kode_transaksi;
            //     $dtBarang['kode_transaksi'] = $brngMasukModel->generateKodePersediaan();
            //     $this->insertRecordGetid($brngMasukModel->table, $dtBarang);
            //  }
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

    function get_export($from_date = null, $to_date = null){
        $builder = $this->db->table("trans_barang_trf_so_det tbtsd");
        $builder->join($this->table . " tbth", "tbth.id = tbtsd.id_header", "inner");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = tbth.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = tbth.id_cmt', 'left');
        $builder->join('trans_sales_order tso', 'tso.kode_sales_order  = tbtsd.kode_sales_order', 'left');
        $builder->join('trans_sample ts', 'ts.kode_sample  = tbtsd.kode_sales_order', 'left');
        $builder->join($this->tblBuyer . " rk", "rk.id = tbtsd.id_konsumen", "inner");

        $builder->select("rk.nama as buyer, tbtsd.style, jp.nama as proses, rp.nama_operator, tbtsd.color, tbtsd.kode_ukuran, 
                            tbtsd.qty,
                            (case when tso.kode_sales_order IS NOT NULL then tso.kode_sales_order else ts.kode_sample end) as kode,
                            (case when tso.kode_sales_order IS NOT NULL then tso.tgl_transaksi else ts.tgl_transaksi end) as tgl_transaksi,
                            coalesce ((select sum(tbmp.qty) from trans_barang_masuk_produksi tbmp inner join trans_barang_header tbh on tbh.id = tbmp.id_header
                            where tbmp.kode_sales_order = tbtsd.kode_sales_order 
                            and tbh.id_proses = tbth.id_proses 
                            and tbh.id_cmt = tbth.id_cmt 
                            and tbh.no_ref_trf = tbth.kode_transaksi 
                            and tbmp.kode_ukuran = tbtsd.kode_ukuran
                            and tbmp.color = tbtsd.color
                            AND LEFT(tbh.kode_transaksi, 3) = 'BTM'
                            and tbh.active = 1), 0) as qty_terima");
        $builder->where('tbth.active = 1');
        $builder->groupStart();
         $builder->where("tso.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
         $builder->orWhere("ts.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
        $builder->groupEnd();
        $builder->orderBy("tgl_transaksi", "desc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
}
