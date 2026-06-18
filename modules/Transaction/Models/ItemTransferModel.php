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
                            tu.buyer, tu.ref_detail_id");
        
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
                $builder->where('lower(tu.kode_ukuran)', strtolower($params['kode_ukuran']));
            }
            if (!empty($params['key_ukuran'])) {
                $builder->where('lower(tu.key_ukuran)', strtolower($params['key_ukuran']));
            }
            if (!empty($params['deskripsi'])) {
                $builder->where('tu.deskripsi', $params['deskripsi']);
            }
            if (!empty($params['style'])) {
                $builder->where('tu.style', strtolower($params['style']));
            }
            if (!empty($params['all_color'])) {
                $builder->where('tu.color',  $params['all_color']);
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

    function getDataByProsesAndOperatorArray($idProses = null, $idOperator = null, $idPerusahaan = null)
    {
        $builder = $this->db->table("trans_barang_trf_header abx");
        $builder->select("abx.id");
        if ($idPerusahaan != 2) {
            $builder->where("id_proses", $idProses);
            $builder->where("id_cmt", $idOperator);
        }

        return array_column($builder->get()->getResultArray(), 'id');
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

    function getTransferSOSUMQty($kode_sales_order = null, $kode_ukuran = null, $color = null, $id_konsumen = null, $id_proses = null, $id_cmt = null, $type_update = null, $value = null)
    {
        $builder = $this->db->table("trans_barang_trf_so_det tbtsd");

        $builder->select("COALESCE(SUM(tbtsd.qty), 0) as total_qty_transfer");

        $builder->join("trans_barang_trf_header tbh", "tbh.id = tbtsd.id_header", 'inner');
        $builder->where("tbtsd.kode_sales_order", $kode_sales_order);
        $builder->where("tbtsd.kode_ukuran", $kode_ukuran);
        $builder->where("tbtsd.color", $color);
        $builder->where("tbtsd.id_konsumen", $id_konsumen);
        $builder->where("tbh.id_proses",  $id_proses);
        $builder->where("tbh.id_cmt", $id_cmt);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getBTMSUMQty($kode_sales_order = null, $kode_ukuran = null, $color = null, $id_konsumen = null, $id_proses = null, $id_cmt = null, $type_update = null, $value = null)
    {
        $builder = $this->db->table("trans_barang_masuk_produksi tbmp");

        $builder->select("COALESCE(SUM(tbmp.qty), 0) as total_qty_btm, COALESCE(SUM(tbmp.qty_kirim), 0) as total_qty_kirim_btm");

        $builder->join("trans_barang_header tbh", "tbh.id =tbmp.id_header", 'inner');
        $builder->where("tbmp.kode_sales_order", $kode_sales_order);
        $builder->where("tbmp.kode_ukuran", $kode_ukuran);
        $builder->where("tbmp.color", $color);
        $builder->where("tbmp.id_konsumen", $id_konsumen);
        $builder->where("tbh.id_proses",  $id_proses);
        $builder->where("tbh.id_cmt", $id_cmt);
        $builder->where("tbh.id_perusahaan != 2");
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDetailItem($id = null)
    {
        $builder = $this->db->table("trans_barang_trf_so_det abx");
        $builder->select("tbth.id_proses, tbth.id_cmt, tbth.status, abx.id, abx.id_header, abx.kode_sales_order, abx.qty, abx.id_konsumen, abx.kode_ukuran, abx.style, abx.color, abx.print_type");
        $builder->join("trans_barang_trf_header tbth", "tbth.id = abx.id_header", 'inner');

        $builder->where("abx.id", $id);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function updateDataBTMQtyKirim($kode_sales_order = null, $kode_ukuran = null, $color = null, $id_konsumen = null, $id_proses = null, $id_cmt = null, $type_update = null, $value = null, $condition = null)
    {
        // Menentukan operator: jika type_update adalah 'minus', maka dikurangi. 
        // Jika tidak, default ditambah.
        $operator = ($type_update === 'minus') ? '-' : '+';
        
        $sql = "
            UPDATE trans_barang_masuk_produksi AS tbmp
            SET qty_kirim = tbmp.qty_kirim $operator :value:
            FROM trans_barang_header AS tbh
            WHERE tbh.id = tbmp.id_header 
            AND tbh.id_perusahaan != 2
            AND tbmp.kode_sales_order = :kode_so:
            AND tbmp.kode_ukuran = :ukuran:
            AND tbmp.color = :color:
            AND tbmp.id_konsumen = :id_konsumen:
            AND tbh.id_proses = :id_proses:
            AND tbh.id_cmt = :id_cmt:
        ";

        if (!empty($condition)) {
            $sql .= " AND tbmp.qty >= (tbmp.qty_kirim - :value:) ";
        }

        $params = [
            'value'             => (float) ($value ?? 0),
            'kode_so'           => $kode_sales_order,
            'ukuran'            => $kode_ukuran,
            'color'             => $color,
            'id_konsumen'       => $id_konsumen,
            'id_proses'         => $id_proses,
            'id_cmt'            => $id_cmt
        ];

        // Eksekusi query dengan parameter binding
        return $this->db->query($sql, $params);
    }

    public function getRecordConditionDelete($id, $id_header)
    {
        $builder = $this->db->table("trans_barang_trf_so_det abx");
        $builder->select("tbth.id_proses, tbth.id_cmt, tbth.status, abx.id, abx.id_header, abx.kode_sales_order, abx.qty, abx.id_konsumen, abx.kode_ukuran, abx.style, abx.color, abx.print_type");
        $builder->join("trans_barang_trf_header tbth", "tbth.id = abx.id_header", 'inner');

        $builder->whereNotIn("abx.id", $id);
        $builder->where("abx.id_header", $id_header);
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
    
    function trxInsertUpdateRecord($data, $id, $detail, $dataSO, $oldStatus = null)
    {
        $this->db->transStart();
        try {
            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                // $this->deleteRecordMultipleColumn($this->tblDetailSO, $arrDelete);
                
                $getCurrent = $this->getData($id);
                $data['kode_transaksi'] = $getCurrent->kode_transaksi;
                if ($getCurrent->status == 1) {
                    $data['status'] = 1;
                }
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan();
                $id = $this->insertRecordGetid($this->table,  $data);
            }
            
            if (!empty($dataSO)) {
                $IncludedIDSO = [];
                foreach ($dataSO as $rowData) {

                    // if ($rowData['qty_ref'] < $rowData['qty'] && (!empty($rowData['print_type']) && $rowData['print_type'] == 1)) {
                    //     throw new \Exception("QTY melebihi QTY REF! ({$rowData['kode_sales_order']})");
                    //     break;
                    // }

                    $dataDetail = [
                        // "id_so" => !empty($rowData['id']) ? decrypt($rowData['id']) : null,
                        "id_header" => $id,
                        "color" => $rowData['color'],
                        "tipe" => !empty($rowData['tipe']) ? $rowData['tipe'] : null,
                        "print_type" => !empty($rowData['print_type']) ? $rowData['print_type'] : null,
                        "total_scanned" => !empty($rowData['total_scanned']) ? $rowData['total_scanned'] : null,
                        "ref_detail_id" => !empty($rowData['ref_detail_id']) ? $rowData['ref_detail_id'] : null,
                        "deskripsi" => $rowData['deskripsi'],
                        "style" => !empty($rowData['style']) ? $rowData['style'] : null,
                        "qty" => !empty($rowData['qty']) ? $rowData['qty'] : null,
                        // "amount" => !empty($rowData['amount']) ? $rowData['amount'] : null,
                        "id_konsumen" => $rowData['id_konsumen'],
                        "kode_sales_order" => $rowData['kode_sales_order'],
                        "kode_ukuran" => $rowData['kode_ukuran'],
                        "keterangan" => !empty($rowData['keterangan']) ? $rowData['keterangan'] : null,
                    ];

                    $idDet = !empty($rowData['id']) ? $rowData['id'] : null;
                    $getCurrentDet = $this->getDetailItem($idDet);
                    if (!empty($getCurrentDet)) {
                        if ($oldStatus == 0 && $data['status'] == 1) {
                            $updateQtyKirimBtm = $this->updateDataBTMQtyKirim($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                            $rowData['color'], $rowData['id_konsumen'], $getCurrentDet->id_proses, 
                                                            $getCurrentDet->id_cmt, 'plus', $rowData['qty']);
                        }
                        else if ($getCurrentDet->status == 1){
                            $getQtySumTfSo = $this->getTransferSOSUMQty($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                            $rowData['color'], $rowData['id_konsumen'], $getCurrentDet->id_proses, 
                                                            $getCurrentDet->id_cmt);

                            $getQtySumBtm = $this->getBTMSUMQty($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                            $rowData['color'], $rowData['id_konsumen'], $getCurrentDet->id_proses, 
                                                            $getCurrentDet->id_cmt);
                            // dd($rowData['qty'] < $getCurrentDet->qty && $getQtySumBtm->total_qty_btm <= $getQtySumTfSo->total_qty_transfer - ($getCurrentDet->qty - $rowData['qty']));
                            if ($rowData['qty'] < $getCurrentDet->qty && $getQtySumBtm->total_qty_btm <= $getQtySumTfSo->total_qty_transfer - ($getCurrentDet->qty - $rowData['qty'])) {
                                $this->updateDataBTMQtyKirim($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                            $rowData['color'], $rowData['id_konsumen'], $getCurrentDet->id_proses, 
                                                            $getCurrentDet->id_cmt, 'minus', $getCurrentDet->qty - $rowData['qty'], true);
                            }
                            else if ($rowData['qty'] > $getCurrentDet->qty) {
                                $updatePlus = $rowData['qty'] - $getCurrentDet->qty;
                                $this->updateDataBTMQtyKirim($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                            $rowData['color'], $rowData['id_konsumen'], $getCurrentDet->id_proses, 
                                                            $getCurrentDet->id_cmt, 'plus', $updatePlus);
                            }
                            else if ($rowData['qty'] < $getCurrentDet->qty && $getQtySumBtm->total_qty_btm > $getQtySumTfSo->total_qty_transfer - ($getCurrentDet->qty - $rowData['qty'])) {
                                $total_selisih = $getQtySumTfSo->total_qty_transfer - $getQtySumBtm->total_qty_btm;
                                $total_selisih = $getCurrentDet->qty - $total_selisih;
                                throw new \Exception("QTY tidak dapat diubah lebih kecil dari {$total_selisih} pada ({$rowData['kode_sales_order']})");
                                break;
                            }
                        }
                        $IncludedIDSO[] = $getCurrentDet->id;
                        $this->updateRecord($this->tblDetailSO, $dataDetail, 'id', $getCurrentDet->id);
                    }
                    else {
                        if ($data['status'] == 1) {
                            $updateQtyKirimBtm = $this->updateDataBTMQtyKirim($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                            $rowData['color'], $rowData['id_konsumen'], $data['id_proses'], 
                                                            $data['id_cmt'], 'plus', $rowData['qty']);
                        }
                        $IncludedIDSO[] = $this->insertRecordGetid($this->tblDetailSO, $dataDetail);
                    }
                }

                if (count($IncludedIDSO) > 0) {
                        $getToDelete = $this->getRecordConditionDelete($IncludedIDSO, $id);
                        foreach ($getToDelete as $idSO) {
                            $getCurrentDet = $this->getDetailItem($idSO->id);
                            if (!empty($getCurrentDet) && $getCurrentDet->status == 1 && $oldStatus != 0) {
                                $getQtySumTfSo = $this->getTransferSOSUMQty($getCurrentDet->kode_sales_order, $getCurrentDet->kode_ukuran, 
                                                            $getCurrentDet->color, $getCurrentDet->id_konsumen, $getCurrentDet->id_proses, 
                                                            $getCurrentDet->id_cmt);

                                $getQtySumBtm = $this->getBTMSUMQty($getCurrentDet->kode_sales_order, $getCurrentDet->kode_ukuran, 
                                                                $getCurrentDet->color, $getCurrentDet->id_konsumen, $getCurrentDet->id_proses, 
                                                                $getCurrentDet->id_cmt);
                                // dd($rowData['qty'] < $getCurrentDet->qty && $getQtySumBtm->total_qty_btm <= $getQtySumTfSo->total_qty_transfer - ($getCurrentDet->qty - $rowData['qty']));
                                if ($getCurrentDet->get_qty_terima < $getCurrentDet->qty && $getQtySumBtm->total_qty_btm <= $getQtySumTfSo->total_qty_transfer - ($getCurrentDet->qty - $getCurrentDet->get_qty_terima)) {
                                    $this->updateDataBTMQtyKirim($getCurrentDet->kode_sales_order, $getCurrentDet->kode_ukuran, 
                                                                $getCurrentDet->color, $getCurrentDet->id_konsumen, $getCurrentDet->id_proses, 
                                                                $getCurrentDet->id_cmt, 'minus', $getCurrentDet->qty - $getCurrentDet->get_qty_terima, true);
                                }
                                else if ($getCurrentDet->get_qty_terima > $getCurrentDet->qty) {
                                    $updatePlus = $getCurrentDet->get_qty_terima - $getCurrentDet->qty;
                                    $this->updateDataBTMQtyKirim($getCurrentDet->kode_sales_order, $getCurrentDet->kode_ukuran, 
                                                                $getCurrentDet->color, $getCurrentDet->id_konsumen, $getCurrentDet->id_proses, 
                                                                $getCurrentDet->id_cmt, 'plus', $updatePlus);
                                }
                                else if ($getCurrentDet->get_qty_terima < $getCurrentDet->qty && $getQtySumBtm->total_qty_btm > $getQtySumTfSo->total_qty_transfer - ($getCurrentDet->qty - $getCurrentDet->get_qty_terima)) {
                                    $total_selisih = $getQtySumTfSo->total_qty_transfer - $getQtySumBtm->total_qty_btm;
                                    $total_selisih = $getCurrentDet->qty - $total_selisih;
                                    throw new \Exception("QTY tidak dapat diubah lebih kecil dari {$total_selisih} pada ({$getCurrentDet->kode_sales_order}) - ({$getCurrentDet->color}) - ({$getCurrentDet->kode_ukuran})");
                                    break;
                                }
                            }
                        }
                        $this->deleteRecordCondition('trans_barang_trf_so_det', 'id', $IncludedIDSO, 'id_header', $id);
                    }
            }
            
            if(!empty($detail)){
                foreach ($detail as $key => $rowData) {
                    if ($rowData['id_barang'] != "") {
                        // $idBarang = decrypt($rowData['id_barang']);
                        $idBarang = $rowData['id_barang'];
                    }
                
                    $dataDetail = [
                        "id_barang" => $idBarang,
                        "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                        "kode_walkorder" => !empty($rowData['kode_walkorder']) ? $rowData['kode_walkorder'] : null,
                        "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                        "lot_id" => !empty($rowData['lot_id']) ? $rowData['lot_id'] : 0,
                        "keterangan" => !empty($rowData['keterangan']) ? $rowData['keterangan'] : null,
                        "id_header" => $id,
                        "qty" => $rowData['qty'],
                        "price" => !empty($rowData['price'])
                            ? (int) preg_replace('/[^0-9]/', '', $rowData['price'])
                            : 0,
                    ];
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
    
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang_tujuan'], $idLotsMasuk, !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);
    
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
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                            "price" => !empty($rowData['price']) ? $rowData['price'] : 0,
                            "kode_transaksi" => $data['kode_transaksi'],
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLotsMasuk,
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
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
    
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang_asal'], $idLots, !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);
    
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
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                            "price" => !empty($rowData['price']) ? $rowData['price'] : 0,
                            "kode_transaksi" => $data['kode_transaksi'],
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarangAsal);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang_asal']) ? $data['id_gudang_asal'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
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

    function get_export($from_date = null, $to_date = null)
    {
        $db = $this->db;
        
        /*
        |--------------------------------------------------------------------------
        | SUBQUERY (x) – hitung qty_terima per TRF
        |--------------------------------------------------------------------------
        */
        $subBuilder = $db->table('trans_barang_trf_so_det tbtsd');

        $subBuilder->select([
            'tbth.kode_transaksi',
            'rk.nama AS buyer',
            'tbtsd.style',
            'jp.nama AS proses',
            'ro.nama_operator',
            'tbtsd.color',
            'tbtsd.kode_ukuran',
            'tbtsd.qty',
            'COALESCE(tso.kode_sales_order, ts.kode_sample) AS kode',
            'COALESCE(tso.tgl_transaksi, ts.tgl_transaksi) AS tgl_transaksi',
            "COALESCE((
                SELECT SUM(tbmp.qty)
                FROM trans_barang_masuk_produksi tbmp
                INNER JOIN trans_barang_header tbh
                    ON tbh.id = tbmp.id_header
                WHERE tbmp.kode_sales_order = tbtsd.kode_sales_order
                AND tbmp.kode_ukuran = tbtsd.kode_ukuran
                AND tbmp.color = tbtsd.color
                AND tbh.id_proses = tbth.id_proses
                AND tbh.id_cmt = tbth.id_cmt
                AND LEFT(tbh.kode_transaksi, 3) = 'BTM'
                AND tbh.active = 1
            ), 0) AS qty_terima,
             COALESCE(
                ( 
                    SELECT tsu.qty
                    FROM trans_sample_ukuran tsu 
                    INNER JOIN ref_ukuran ru ON ru.id = tsu.id_ukuran
                    INNER JOIN trans_sample_det tsd ON tsd.id = tsu.id_sample_det

                    -- JOIN ref_barang untuk sample
                    LEFT JOIN ref_barang bsub1 ON bsub1.id = tsd.id_barang_1
                    LEFT JOIN ref_barang bsub2 ON bsub2.id = tsd.id_barang_2
                    LEFT JOIN ref_barang bsub3 ON bsub3.id = tsd.id_barang_3
                    LEFT JOIN ref_barang bsub4 ON bsub4.id = tsd.id_barang_4
                    LEFT JOIN ref_barang bsub5 ON bsub5.id = tsd.id_barang_5
                    LEFT JOIN ref_barang bsub6 ON bsub6.id = tsd.id_barang_6
                    LEFT JOIN ref_barang bsub7 ON bsub7.id = tsd.id_barang_7
                    LEFT JOIN ref_barang bsub8 ON bsub8.id = tsd.id_barang_8

                    -- JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di tsd
                    LEFT JOIN ref_warna rw1 ON rw1.id = COALESCE(bsub1.id_warna, tsd.id_warna_1)
                    LEFT JOIN ref_warna rw2 ON rw2.id = COALESCE(bsub2.id_warna, tsd.id_warna_2)
                    LEFT JOIN ref_warna rw3 ON rw3.id = COALESCE(bsub3.id_warna, tsd.id_warna_3)
                    LEFT JOIN ref_warna rw4 ON rw4.id = COALESCE(bsub4.id_warna, tsd.id_warna_4)
                    LEFT JOIN ref_warna rw5 ON rw5.id = COALESCE(bsub5.id_warna, tsd.id_warna_5)
                    LEFT JOIN ref_warna rw6 ON rw6.id = COALESCE(bsub6.id_warna, tsd.id_warna_6)
                    LEFT JOIN ref_warna rw7 ON rw7.id = COALESCE(bsub7.id_warna, tsd.id_warna_7)
                    LEFT JOIN ref_warna rw8 ON rw8.id = COALESCE(bsub8.id_warna, tsd.id_warna_8)

                    WHERE tsu.id_sample = ts.id
                    AND ru.kode_ukuran = tbtsd.kode_ukuran
                    AND CONCAT_WS('~', 
                        NULLIF(rw1.kode_warna, ''), 
                        NULLIF(rw2.kode_warna, ''), 
                        NULLIF(rw3.kode_warna, ''),
                        NULLIF(rw4.kode_warna, ''),
                        NULLIF(rw5.kode_warna, ''),
                        NULLIF(rw6.kode_warna, ''),
                        NULLIF(rw7.kode_warna, ''),
                        NULLIF(rw8.kode_warna, '')
                    ) = tbtsd.color
                ), 
                ( 
                    SELECT tsou.qty
                    FROM trans_sales_order_ukuran tsou 
                    INNER JOIN ref_ukuran ru ON ru.id = tsou.id_ukuran
                    INNER JOIN trans_sales_order_det tsod ON tsod.id = tsou.id_sales_order_det

                    -- JOIN ref_barang untuk SO
                    LEFT JOIN ref_barang bsub1 ON bsub1.id = tsod.id_barang_1
                    LEFT JOIN ref_barang bsub2 ON bsub2.id = tsod.id_barang_2
                    LEFT JOIN ref_barang bsub3 ON bsub3.id = tsod.id_barang_3
                    LEFT JOIN ref_barang bsub4 ON bsub4.id = tsod.id_barang_4
                    LEFT JOIN ref_barang bsub5 ON bsub5.id = tsod.id_barang_5
                    LEFT JOIN ref_barang bsub6 ON bsub6.id = tsod.id_barang_6
                    LEFT JOIN ref_barang bsub7 ON bsub7.id = tsod.id_barang_7
                    LEFT JOIN ref_barang bsub8 ON bsub8.id = tsod.id_barang_8

                    -- JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di tsod
                    LEFT JOIN ref_warna rw1 ON rw1.id = COALESCE(bsub1.id_warna, tsod.id_warna_1)
                    LEFT JOIN ref_warna rw2 ON rw2.id = COALESCE(bsub2.id_warna, tsod.id_warna_2)
                    LEFT JOIN ref_warna rw3 ON rw3.id = COALESCE(bsub3.id_warna, tsod.id_warna_3)
                    LEFT JOIN ref_warna rw4 ON rw4.id = COALESCE(bsub4.id_warna, tsod.id_warna_4)
                    LEFT JOIN ref_warna rw5 ON rw5.id = COALESCE(bsub5.id_warna, tsod.id_warna_5)
                    LEFT JOIN ref_warna rw6 ON rw6.id = COALESCE(bsub6.id_warna, tsod.id_warna_6)
                    LEFT JOIN ref_warna rw7 ON rw7.id = COALESCE(bsub7.id_warna, tsod.id_warna_7)
                    LEFT JOIN ref_warna rw8 ON rw8.id = COALESCE(bsub8.id_warna, tsod.id_warna_8)

                    WHERE tsou.id_sales_order = tso.id
                    AND ru.kode_ukuran = tbtsd.kode_ukuran
                    AND CONCAT_WS('~', 
                        NULLIF(rw1.kode_warna, ''), 
                        NULLIF(rw2.kode_warna, ''), 
                        NULLIF(rw3.kode_warna, ''),
                        NULLIF(rw4.kode_warna, ''),
                        NULLIF(rw5.kode_warna, ''),
                        NULLIF(rw6.kode_warna, ''),
                        NULLIF(rw7.kode_warna, ''),
                        NULLIF(rw8.kode_warna, '')
                    ) = tbtsd.color
                )
            ) AS qty_ref"
        ]);

        $subBuilder->join('trans_barang_trf_header tbth', 'tbth.id = tbtsd.id_header', 'inner');
        $subBuilder->join('_jenis_proses_produksi jp', 'jp.id = tbth.id_proses', 'left');
        $subBuilder->join('ref_operator ro', 'ro.id = tbth.id_cmt', 'left');
        $subBuilder->join('trans_sales_order tso', 'tso.kode_sales_order = tbtsd.kode_sales_order', 'left');
        $subBuilder->join('trans_sample ts', 'ts.kode_sample = tbtsd.kode_sales_order', 'left');
        $subBuilder->join('ref_konsumen rk', 'rk.id = tbtsd.id_konsumen', 'inner');

        // 🔴 FILTER TANGGAL DINAMIS
        if ($from_date && $to_date) {
            $subBuilder->groupStart()
                ->where('tso.tgl_transaksi >=', $from_date)
                ->where('tso.tgl_transaksi <=', $to_date)
            ->groupEnd()
            ->orGroupStart()
                ->where('ts.tgl_transaksi >=', $from_date)
                ->where('ts.tgl_transaksi <=', $to_date)
            ->groupEnd();
        }

        /*
        |--------------------------------------------------------------------------
        | QUERY LUAR – GROUPING FINAL
        |--------------------------------------------------------------------------
        */
        $builder = $db->table("({$subBuilder->getCompiledSelect(false)}) x");

        $builder->select([
            'buyer',
            'style',
            'proses',
            'nama_operator',
            'color',
            'kode_ukuran',
            'SUM(qty) AS qty',
            'qty_terima',
            'kode',
            'tgl_transaksi',
            'qty_ref'
        ]);

        $builder->groupBy([
            'buyer',
            'style',
            'proses',
            'nama_operator',
            'color',
            'kode_ukuran',
            'qty_terima',
            'kode',
            'tgl_transaksi',
            'qty_ref'
        ]);

        $builder->orderBy('tgl_transaksi');

        return $builder->get()->getResult();
    }

    function get_trf_data($from_date = null, $to_date = null, $idProses = null, $idOperator = null, $approved = null)
    {
        $db = $this->db;

        /*
        |--------------------------------------------------------------------------
        | SUBQUERY (x) – Mengambil Data Detail & Hitung Qty Terkait
        |--------------------------------------------------------------------------
        */
        $subBuilder = $db->table('trans_barang_trf_so_det tbtsd');

        $subBuilder->select([
            'tbtsd.id',
            'tbth.kode_transaksi',
            'COALESCE(tso.kode_sales_order, ts.kode_sample) as kode_sales_order',
            'rk.nama AS buyer',
            'tbtsd.id_konsumen',
            'tbtsd.style',
            'jp.nama AS proses',
            'ro.nama_operator',
            'tbtsd.print_type',
            'tbtsd.color',
            'tbtsd.kode_ukuran',
            'ru.key_ukuran',
            'tbtsd.qty',
            'COALESCE(tso.kode_sales_order, ts.kode_sample) AS kode',
            'COALESCE(tso.tgl_transaksi, ts.tgl_transaksi) AS tgl_transaksi',
            // Subquery Qty Terima
            "COALESCE((
                SELECT SUM(tbmp.qty)
                FROM trans_barang_masuk_produksi tbmp
                INNER JOIN trans_barang_header tbh ON tbh.id = tbmp.id_header
                WHERE tbmp.kode_sales_order = tbtsd.kode_sales_order
                AND tbmp.kode_ukuran = tbtsd.kode_ukuran
                AND tbmp.color = tbtsd.color
                AND tbh.id_proses = tbth.id_proses
                AND tbh.id_cmt = tbth.id_cmt
                AND LEFT(tbh.kode_transaksi, 3) = 'BTM'
                AND tbh.active = 1
                AND tbh.status = 1
            ), 0) AS qty_terima",
            // Subquery Qty Kirim (Selisih)
            "(tbtsd.qty - COALESCE((
                SELECT SUM(tbmp.qty)
                FROM trans_barang_masuk_produksi tbmp
                INNER JOIN trans_barang_header tbh ON tbh.id = tbmp.id_header
                WHERE tbmp.kode_sales_order = tbtsd.kode_sales_order
                AND tbmp.kode_ukuran = tbtsd.kode_ukuran
                AND tbmp.color = tbtsd.color
                AND tbh.id_proses = tbth.id_proses
                AND tbh.id_cmt = tbth.id_cmt
                AND LEFT(tbh.kode_transaksi, 3) = 'BTM'
                AND tbh.active = 1
                AND tbh.status = 1
            ), 0)) AS qty_kirim",
            // Subquery Qty Ref (Logic COALESCE Sample vs SO)
            "COALESCE(
                ( 
                    SELECT tsu.qty FROM trans_sample_ukuran tsu 
                    INNER JOIN ref_ukuran ru ON ru.id = tsu.id_ukuran
                    INNER JOIN trans_sample_det tsd ON tsd.id = tsu.id_sample_det 
                    LEFT JOIN ref_warna rw1 ON rw1.id = tsd.id_warna_1 
                    LEFT JOIN ref_warna rw2 ON rw2.id = tsd.id_warna_2 
                    LEFT JOIN ref_warna rw3 ON rw3.id = tsd.id_warna_3 
                    LEFT JOIN ref_warna rw4 ON rw4.id = tsd.id_warna_4 
                    LEFT JOIN ref_warna rw5 ON rw5.id = tsd.id_warna_5 
                    LEFT JOIN ref_warna rw6 ON rw6.id = tsd.id_warna_6 
                    LEFT JOIN ref_warna rw7 ON rw7.id = tsd.id_warna_7 
                    LEFT JOIN ref_warna rw8 ON rw8.id = tsd.id_warna_8 
                    WHERE tsu.id_sample = ts.id AND ru.kode_ukuran = tbtsd.kode_ukuran
                    AND CONCAT_WS('~', NULLIF(rw1.kode_warna,''), NULLIF(rw2.kode_warna,''), NULLIF(rw3.kode_warna,''), NULLIF(rw4.kode_warna,''), NULLIF(rw5.kode_warna,''), NULLIF(rw6.kode_warna,''), NULLIF(rw7.kode_warna,''), NULLIF(rw8.kode_warna,'')) = tbtsd.color
                ), 
                ( 
                    SELECT tsou.qty FROM trans_sales_order_ukuran tsou 
                    INNER JOIN ref_ukuran ru ON ru.id = tsou.id_ukuran
                    INNER JOIN trans_sales_order_det tsod ON tsod.id = tsou.id_sales_order_det 
                    LEFT JOIN ref_warna rw1 ON rw1.id = tsod.id_warna_1 
                    LEFT JOIN ref_warna rw2 ON rw2.id = tsod.id_warna_2 
                    LEFT JOIN ref_warna rw3 ON rw3.id = tsod.id_warna_3 
                    LEFT JOIN ref_warna rw4 ON rw4.id = tsod.id_warna_4 
                    LEFT JOIN ref_warna rw5 ON rw5.id = tsod.id_warna_5 
                    LEFT JOIN ref_warna rw6 ON rw6.id = tsod.id_warna_6 
                    LEFT JOIN ref_warna rw7 ON rw7.id = tsod.id_warna_7 
                    LEFT JOIN ref_warna rw8 ON rw8.id = tsod.id_warna_8 
                    WHERE tsou.id_sales_order = tso.id AND ru.kode_ukuran = tbtsd.kode_ukuran
                    AND CONCAT_WS('~', NULLIF(rw1.kode_warna,''), NULLIF(rw2.kode_warna,''), NULLIF(rw3.kode_warna,''), NULLIF(rw4.kode_warna,''), NULLIF(rw5.kode_warna,''), NULLIF(rw6.kode_warna,''), NULLIF(rw7.kode_warna,''), NULLIF(rw8.kode_warna,'')) = tbtsd.color
                )
            ) AS qty_ref",
            // Subquery Harga
            "((SELECT wop.harga FROM trans_walkorder_proses wop 
                LEFT JOIN trans_walkorder wo ON wop.id_walkorder = wo.id 
                WHERE wop.id_proses = tbth.id_proses 
                AND wo.ref_kode = tbtsd.kode_sales_order LIMIT 1)) AS harga"
        ]);

        $subBuilder->join('trans_barang_trf_header tbth', 'tbth.id = tbtsd.id_header', 'inner');
        $subBuilder->join("ref_ukuran ru", "tbtsd.kode_ukuran = ru.kode_ukuran", "left");
        $subBuilder->join('_jenis_proses_produksi jp', 'jp.id = tbth.id_proses', 'left');
        $subBuilder->join('ref_operator ro', 'ro.id = tbth.id_cmt', 'left');
        $subBuilder->join('trans_sales_order tso', 'tso.kode_sales_order = tbtsd.kode_sales_order', 'left');
        $subBuilder->join('trans_sample ts', 'ts.kode_sample = tbtsd.kode_sales_order', 'left');
        $subBuilder->join('ref_konsumen rk', 'rk.id = tbtsd.id_konsumen', 'inner');

        // Filter Tanggal
        if ($from_date && $to_date) {
            $subBuilder->groupStart()
                ->where('tso.tgl_transaksi >=', $from_date)
                ->where('tso.tgl_transaksi <=', $to_date)
            ->groupEnd()
            ->orGroupStart()
                ->where('ts.tgl_transaksi >=', $from_date)
                ->where('ts.tgl_transaksi <=', $to_date)
            ->groupEnd();
        }

        // Filter Lainnya
        if (!empty($idProses)) $subBuilder->where('tbth.id_proses', $idProses);
        if (!empty($idOperator)) $subBuilder->where('tbth.id_cmt', $idOperator);
        if (!empty($approved)) $subBuilder->where('tbth.status', 1);

        /*
        |--------------------------------------------------------------------------
        | QUERY LUAR – Menyatukan Baris Duplicate dengan SUM & GROUP BY
        |--------------------------------------------------------------------------
        */
        $builder = $db->table("({$subBuilder->getCompiledSelect(false)}) x");

        $builder->select([
            'buyer',
            'kode_sales_order',
            'style',
            'proses',
            'nama_operator',
            'id_konsumen',
            'print_type',
            'color',
            'kode_ukuran',
            'key_ukuran',
            'SUM(qty) AS qty',            // Agregasi qty agar tidak duplicate baris
            'SUM(qty_terima) AS qty_terima',
            'SUM(qty_kirim) AS qty_kirim',
            'kode',
            'tgl_transaksi',
            'MAX(qty_ref) AS qty_ref',     // Mengambil nilai tertinggi/unik
            'MAX(harga) AS harga'          // Mengambil nilai tertinggi/unik
        ]);

        $builder->groupBy([
            'buyer',
            'kode_sales_order',
            'style',
            'proses',
            'nama_operator',
            'id_konsumen',
            'print_type',
            'color',
            'kode_ukuran',
            'key_ukuran',
            'kode',
            'tgl_transaksi'
        ]);

        $builder->orderBy('tgl_transaksi', 'ASC');

        return $builder->get()->getResult();
    }

    function get_qty_terima($from_date = null, $to_date = null){
        $builder = $this->db->table("trans_barang_masuk_produksi tbmp");
        $builder->join($this->table . " tbth", "tbth.id = tbtsd.id_header", "inner");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = tbth.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = tbth.id_cmt', 'left');
        $builder->join('trans_sales_order tso', 'tso.kode_sales_order  = tbtsd.kode_sales_order', 'left');
        $builder->join('trans_sample ts', 'ts.kode_sample  = tbtsd.kode_sales_order', 'left');
        $builder->join($this->tblBuyer . " rk", "rk.id = tbtsd.id_konsumen", "inner");

        $builder->select("sum(tbmp.qty) as qty_terima");
        $builder->where('tbth.active = 1');
        $builder->groupStart();
         $builder->where("tso.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
         $builder->orWhere("ts.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
        $builder->groupEnd();
        $builder->orderBy("tgl_transaksi", "desc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataRajut($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $id = null)
    {
        $builder = $this->db->table('trans_barang_masuk_produksi tbmp');
        $builder->join("trans_barang_header tbh", "tbh.id = tbmp.id_header", "inner");
        
        $builder->select("sum(tbmp.qty) as total_proses");

        $builder->where('tbh.id_proses', 1);
        $builder->where('tbh.status', 1);
        $builder->where('tbh.id_perusahaan', 1);
        $builder->where('tbmp.id_konsumen', $id_konsumen);
        $builder->where('tbmp.kode_ukuran', $kode_ukuran);
        $builder->where('tbmp.kode_sales_order', $kode_so);
        $builder->where('tbmp.color', $color);
        // if (!empty($id)) {
        //     $builder->where('tbmp.id <>', $id);
        // }

        $this->_data = $builder->get()->getRow()->total_proses;

        return $this->_data;
    }

    function getDataPengurang($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $id = null)
    {
        $builder = $this->db->table('trans_barang_trf_so_det tbtsd');
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtsd.id_header", "inner");
        
        $builder->select("sum(tbtsd.qty) as total_proses");

        $builder->where('tbth.id_proses', $id_proses);
        $builder->where('tbth.status', 1);
        $builder->where('tbtsd.id_konsumen', $id_konsumen);
        $builder->where('tbtsd.kode_ukuran', $kode_ukuran);
        $builder->where('tbtsd.kode_sales_order', $kode_so);
        $builder->where('tbtsd.color', $color);
        // if (!empty($id)) {
        //     $builder->where('tbtsd.id <>', $id);
        // }

        $this->_data = $builder->get()->getRow()->total_proses;

        return $this->_data;
    }

    function get_export_bahan_jadi($from_date = null, $to_date = null){
        $builder = $this->db->table("trans_barang_trf_so_det tbtd");
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtd.id_header", "inner");
        $builder->join("_jenis_proses_produksi jpp", "jpp.id = tbth.id_proses", "left");
        $builder->join("ref_operator ro", "ro.id = tbth.id_cmt", "left");
        $builder->join("ref_gudang rg", "rg.id = tbth.id_gudang_asal", "left");
        $builder->join("ref_gudang rg2", "rg2.id = tbth.id_gudang_tujuan", "left");

        $builder->select("tbth.kode_transaksi, tbth.tanggal, rg.nama_gudang as gudang_asal, rg2.nama_gudang as gudang_tujuan, 
                        jpp.nama as proses, ro.nama_operator, tbtd.kode_sales_order, tbtd.style, tbtd.color, tbtd.kode_ukuran, 
                        tbtd.qty, tbtd.keterangan");

        $builder->where('tbth.active', 1);

        $builder->where("tbth.tanggal BETWEEN'$from_date' AND '$to_date'");
        $builder->orderBy("tbth.tanggal", "asc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_export_bahan_baku($from_date = null, $to_date = null){
        $builder = $this->db->table("trans_barang_trf_detail tbtd");
        $builder->join("trans_barang_trf_header tbth", "tbth.id = CAST(tbtd.id_header AS INTEGER)", "inner");
        $builder->join("_jenis_proses_produksi jpp", "jpp.id = tbth.id_proses", "left");
        $builder->join("ref_operator ro", "ro.id = tbth.id_cmt", "left");
        $builder->join("ref_gudang rg", "rg.id = tbth.id_gudang_asal", "left");
        $builder->join("ref_gudang rg2", "rg2.id = tbth.id_gudang_tujuan", "left");
        $builder->join("ref_barang rb", "rb.id = tbtd.id_barang", "inner");
        $builder->join("ref_satuan rs", "rs.id = rb.id_satuan", "inner");

        $builder->select("tbth.kode_transaksi, tbth.tanggal, rg.nama_gudang as gudang_asal, rg2.nama_gudang as gudang_tujuan, 
                        jpp.nama as proses, ro.nama_operator, rb.kode_barang, rb.nama_barang, tbtd.qty, rs.nama_satuan, tbtd.lot_no, tbtd.keterangan");

        $builder->where('tbth.active', 1);

        $builder->where("tbth.tanggal BETWEEN'$from_date' AND '$to_date'");
        $builder->orderBy("tbth.tanggal", "asc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
    
}
