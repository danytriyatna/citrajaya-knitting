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
                          jp.seq as no_proses, jp.nama as proses, uk.id_perusahaan, abx.id_perusahaan as id_perusahaan_asal, bbx.id_perusahaan as id_perusahaan_tujuan");

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
                $val = strtolower(trim($filters[0]['value']));
                $value = "%{$val}%";
                $builder->groupStart();
                $builder->where('LOWER(uk.kode_transaksi) LIKE', $value);
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', $value);
                $builder->orWhere('LOWER(bbx.nama_gudang) LIKE', $value);
                $builder->orWhere('LOWER(cbx.nama_operator) LIKE', $value);
                $builder->orWhere('LOWER(jp.nama) LIKE', $value);
                $builder->orWhere('LOWER(uk.keterangan) LIKE', $value);
                $builder->groupEnd();
            }

            // Filter Perusahaan: selama gudang kirim (asal) ATAU gudang tujuannya
            // ke salah satu gudang P1/P2, atau dokumen milik perusahaan tersebut, tetap dimunculkan.
            if (!empty($params['id_perusahaan'])) {
                $corpId = (int)$params['id_perusahaan'];
                $builder->groupStart();
                if ($corpId === 1) {
                    // Sisi Gudang Asal P1 (id_perusahaan = 1 atau gudang lama NULL)
                    $builder->where('abx.id_perusahaan', 1);
                    $builder->orGroupStart()
                        ->where('uk.id_gudang_asal IS NOT NULL', null, false)
                        ->where('abx.id_perusahaan IS NULL', null, false)
                    ->groupEnd();

                    // Sisi Gudang Tujuan P1 (id_perusahaan = 1 atau gudang lama NULL)
                    $builder->orWhere('bbx.id_perusahaan', 1);
                    $builder->orGroupStart()
                        ->where('uk.id_gudang_tujuan IS NOT NULL', null, false)
                        ->where('bbx.id_perusahaan IS NULL', null, false)
                    ->groupEnd();

                    // Entitas dokumen P1 atau format kode transaksi TRF (bukan TRFC)
                    $builder->orWhere('uk.id_perusahaan', 1);
                    $builder->orWhere("uk.kode_transaksi NOT LIKE 'TRFC%'", null, false);
                } elseif ($corpId === 2) {
                    // Sisi Gudang Asal P2
                    $builder->where('abx.id_perusahaan', 2);
                    // Sisi Gudang Tujuan P2
                    $builder->orWhere('bbx.id_perusahaan', 2);
                    // Entitas dokumen P2 atau format kode transaksi TRFC
                    $builder->orWhere('uk.id_perusahaan', 2);
                    $builder->orWhere("uk.kode_transaksi LIKE 'TRFC%'", null, false);
                } else {
                    $builder->where('abx.id_perusahaan', $corpId);
                    $builder->orWhere('bbx.id_perusahaan', $corpId);
                    $builder->orWhere('uk.id_perusahaan', $corpId);
                }
                $builder->groupEnd();
            }

            if (!empty($params['id_gudang_ref'])) {
                $builder->groupStart();
                $builder->where('uk.id_gudang_tujuan', $params['id_gudang_ref']);
                $builder->orWhere('uk.id_gudang_asal', $params['id_gudang_ref']);
                $builder->groupEnd();
            }

            if (!empty($params['id_gudang_tujuan'])) {
                $builder->where('uk.id_gudang_tujuan', $params['id_gudang_tujuan']);
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
        $builder->join($this->tblOperator . " cbx", "uk.id_cmt = cbx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
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
            $val = strtolower(trim($filters[0]['value']));
            $value = "%{$val}%";
            $builder->groupStart();
            $builder->where('LOWER(uk.kode_transaksi) LIKE', $value);
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', $value);
            $builder->orWhere('LOWER(bbx.nama_gudang) LIKE', $value);
            $builder->orWhere('LOWER(cbx.nama_operator) LIKE', $value);
            $builder->orWhere('LOWER(jp.nama) LIKE', $value);
            $builder->orWhere('LOWER(uk.keterangan) LIKE', $value);
            $builder->groupEnd();
        }

        // Sama seperti getData() - selama gudang kirim (asal) ATAU gudang tujuannya
        // ke salah satu gudang P1/P2, atau dokumen milik perusahaan tersebut, tetap dimunculkan.
        if (!empty($params['id_perusahaan'])) {
            $corpId = (int)$params['id_perusahaan'];
            $builder->groupStart();
            if ($corpId === 1) {
                // Sisi Gudang Asal P1 (id_perusahaan = 1 atau gudang lama NULL)
                $builder->where('abx.id_perusahaan', 1);
                $builder->orGroupStart()
                    ->where('uk.id_gudang_asal IS NOT NULL', null, false)
                    ->where('abx.id_perusahaan IS NULL', null, false)
                ->groupEnd();

                // Sisi Gudang Tujuan P1 (id_perusahaan = 1 atau gudang lama NULL)
                $builder->orWhere('bbx.id_perusahaan', 1);
                $builder->orGroupStart()
                    ->where('uk.id_gudang_tujuan IS NOT NULL', null, false)
                    ->where('bbx.id_perusahaan IS NULL', null, false)
                ->groupEnd();

                // Entitas dokumen P1 atau format kode transaksi TRF (bukan TRFC)
                $builder->orWhere('uk.id_perusahaan', 1);
                $builder->orWhere("uk.kode_transaksi NOT LIKE 'TRFC%'", null, false);
            } elseif ($corpId === 2) {
                // Sisi Gudang Asal P2
                $builder->where('abx.id_perusahaan', 2);
                // Sisi Gudang Tujuan P2
                $builder->orWhere('bbx.id_perusahaan', 2);
                // Entitas dokumen P2 atau format kode transaksi TRFC
                $builder->orWhere('uk.id_perusahaan', 2);
                $builder->orWhere("uk.kode_transaksi LIKE 'TRFC%'", null, false);
            } else {
                $builder->where('abx.id_perusahaan', $corpId);
                $builder->orWhere('bbx.id_perusahaan', $corpId);
                $builder->orWhere('uk.id_perusahaan', $corpId);
            }
            $builder->groupEnd();
        }

        if (!empty($params['id_gudang_ref'])) {
            $builder->groupStart();
            $builder->where('uk.id_gudang_tujuan', $params['id_gudang_ref']);
            $builder->orWhere('uk.id_gudang_asal', $params['id_gudang_ref']);
            $builder->groupEnd();
        }

        if (!empty($params['id_gudang_tujuan'])) {
            $builder->where('uk.id_gudang_tujuan', $params['id_gudang_tujuan']);
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
                $builder->where('LOWER(TRIM(tu.color))', strtolower(trim($params['all_color'])));
            }
            if (!empty($params['color'])) {
                $ascii = iconv("UTF-8", "ASCII//TRANSLIT", $params['color']);
                $prm_color = strtoupper($ascii);
                // Hilangkan spasi setelah value, misal "dan " jadi "dan"
                $prm_color = rtrim($prm_color);
                if (strpos($prm_color, '~') !== false) {
                    $builder->where('LOWER(TRIM(tu.color))', strtolower($prm_color));
                } else {
                    $builder->where(" upper(trim(split_part(tu.color, '~', 1)))", $prm_color);
                }
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
        $builder->select("abx.nama, abx.id, COALESCE(abx.is_rajut, 0) as is_rajut");
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
        if (!empty($idProses)) {
            $builder->where("abx.id_proses", $idProses);
        }
        if ($idPerusahaan != 2) {
            if (!empty($idOperator)) {
                $builder->where("abx.id_cmt", $idOperator);
            }
        }

        return array_column($builder->get()->getResultArray(), 'id');
    }


    function generateKodePersediaan($id_perusahaan = null)
    {
        $kd = "TRF";
        if (!empty($id_perusahaan) && (int)$id_perusahaan === 2) {
            $kd = "TRFC";
        }
        $currentTgl = $kd . date('y') . date('m'); // TRF2608 atau TRFC2608

        $builder = $this->db->table($this->table . ' a');
        $builder->select("a.kode_transaksi");
        $builder->where("a.kode_transaksi LIKE '{$currentTgl}%'");
        if ($kd === "TRF") {
            $builder->notLike("a.kode_transaksi", "TRFC", "after");
        }
        $builder->orderBy('a.kode_transaksi', "DESC");
        $builder->limit(1);
        $query = $builder->get()->getRow();

        if ($query != NULL && !empty($query->kode_transaksi)) {
            $lastSeq = substr($query->kode_transaksi, strlen($currentTgl));
            $kode = intval($lastSeq) + 1;
        } else {
            $kode = 1;
        }

        $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT);
        $kodejadi = $currentTgl . $kodemax;

        // Anti-collision verification
        while ($this->db->table($this->table)->where('kode_transaksi', $kodejadi)->countAllResults() > 0) {
            $kode++;
            $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $kodejadi = $currentTgl . $kodemax;
        }

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
        $builder->select("tbth.id_proses, tbth.id_cmt, tbth.status, abx.id, abx.id_header, abx.kode_sales_order, abx.qty, abx.id_konsumen, abx.kode_ukuran, abx.style, abx.color, abx.print_type, COALESCE(abx.is_reject, 0) as is_reject");
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
    
    function trxInsertUpdateRecord($data, $id, $detail, $dataSO, $oldStatus = null, $forceContinue = 0)
    {
        $this->db->transStart();
        try {
            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => (string)$id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                // $this->deleteRecordMultipleColumn($this->tblDetailSO, $arrDelete);
                
                $getCurrent = $this->getData($id);
                $data['kode_transaksi'] = $getCurrent->kode_transaksi;
                if ($getCurrent->status == 1) {
                    $data['status'] = 1;
                    // Dokumen yang sudah Approved tidak boleh lagi mengubah tanggal transaksi -
                    // kalau tanggal ikut berubah, baris trans_barang akan dihapus & dibuat ulang
                    // dengan tanggal baru (lihat $trxTanggal di bawah), sehingga histori Stock
                    // Card/Laporan Persediaan jadi salah tanggal padahal nomor dokumennya tetap sama.
                    $data['tanggal'] = $getCurrent->tanggal;
                }
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan(!empty($data['id_perusahaan']) ? $data['id_perusahaan'] : 1);
                $id = $this->insertRecordGetid($this->table,  $data);
            }
            
            if (!empty($dataSO)) {
                $IncludedIDSO = [];
                foreach ($dataSO as $rowData) {
                    $isReject = (!empty($rowData['is_reject']) && ($rowData['is_reject'] == 1 || $rowData['is_reject'] === '1' || $rowData['is_reject'] === true));

                    if (!$isReject && isset($data['kode_transaksi']) && strpos($data['kode_transaksi'], 'TRFC') !== 0) {
                        if (isset($rowData['qty_ref']) && floatval($rowData['qty']) > floatval($rowData['qty_ref'])) {
                            throw new \Exception("QTY melebihi QTY REF! SO: {$rowData['kode_sales_order']} ({$rowData['color']})");
                        }
                    }

                    $dataDetail = [
                        // "id_so" => !empty($rowData['id']) ? decrypt($rowData['id']) : null,
                        "id_header" => $id,
                        "color" => $rowData['color'],
                        "tipe" => !empty($rowData['tipe']) ? $rowData['tipe'] : null,
                        "print_type" => !empty($rowData['print_type']) ? (int)$rowData['print_type'] : 1,
                        "is_reject" => $isReject ? 1 : 0,
                        "total_scanned" => !empty($rowData['total_scanned']) ? $rowData['total_scanned'] : null,
                        "ref_detail_id" => !empty($rowData['ref_detail_id']) ? $rowData['ref_detail_id'] : null,
                        "deskripsi" => $rowData['deskripsi'],
                        "style" => !empty($rowData['style']) ? $rowData['style'] : null,
                        "qty" => !empty($rowData['qty']) ? $rowData['qty'] : null,
                        "berat" => !empty($rowData['berat']) ? (float)$rowData['berat'] : null,
                        // "amount" => !empty($rowData['amount']) ? $rowData['amount'] : null,
                        "id_konsumen" => $rowData['id_konsumen'],
                        "kode_sales_order" => $rowData['kode_sales_order'],
                        "kode_ukuran" => $rowData['kode_ukuran'],
                        "keterangan" => !empty($rowData['keterangan']) ? $rowData['keterangan'] : null,
                    ];

                    $idDet = !empty($rowData['id']) ? $rowData['id'] : null;
                    $getCurrentDet = $this->getDetailItem($idDet);
                    if (!empty($getCurrentDet)) {
                        // Hanya update tracking BTM jika BUKAN barang Reject (Opsi 1)
                        if (!$isReject) {
                            if ($oldStatus == 0 && $data['status'] == 1) {
                                $updateQtyKirimBtm = $this->updateDataBTMQtyKirim($rowData['kode_sales_order'], $rowData['kode_ukuran'], 
                                                                $rowData['color'], $rowData['id_konsumen'], $getCurrentDet->id_proses, 
                                                                $getCurrentDet->id_cmt, 'plus', $rowData['qty']);
                            }
                            else if ($getCurrentDet->status == 1 && empty($getCurrentDet->is_reject)){
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
                        }
                        $IncludedIDSO[] = $getCurrentDet->id;
                        $this->updateRecord($this->tblDetailSO, $dataDetail, 'id', $getCurrentDet->id);
                    }
                    else {
                        if (!$isReject && $data['status'] == 1) {
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
                            if (!empty($getCurrentDet) && empty($getCurrentDet->is_reject) && $getCurrentDet->status == 1 && $oldStatus != 0) {
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
            
            // Validasi ketersediaan stok lot di Gudang Asal sebelum approve (jika belum dikonfirmasi lanjut oleh user)
            if ($data['status'] == 1 && empty($forceContinue) && !empty($detail) && is_array($detail)) {
                $mBarangMasukCheck = new IncomingGoodsModel();
                $insufficientLots = [];
                foreach ($detail as $rowCheck) {
                    $chkIdBarang = !empty($rowCheck['id_barang']) ? (is_numeric($rowCheck['id_barang']) ? (int)$rowCheck['id_barang'] : decrypt($rowCheck['id_barang'])) : 0;
                    $chkQty = (float)($rowCheck['qty'] ?? 0);
                    if ($chkQty <= 0 || empty($chkIdBarang)) continue;

                    $chkLotNo = trim((string)($rowCheck['lot_no'] ?? ''));
                    $chkLot = null;
                    if (!empty($rowCheck['lot_id'])) {
                        $chkLot = $mBarangMasukCheck->getLotNo(null, null, $rowCheck['lot_id']);
                    }
                    if (empty($chkLot) && !empty($chkLotNo) && $chkLotNo !== '-') {
                        $chkLot = $mBarangMasukCheck->getLotNo($chkLotNo, $chkIdBarang, null, $data['id_gudang_asal'], !empty($rowCheck['pack_id']) ? $rowCheck['pack_id'] : null);
                        if (empty($chkLot)) {
                            $chkLot = $mBarangMasukCheck->getLotNo($chkLotNo, $chkIdBarang, null, $data['id_gudang_asal']);
                        }
                    }

                    if (empty($chkLot)) {
                        $lotLabel = !empty($chkLotNo) ? $chkLotNo : '-';
                        $insufficientLots[] = "• Lot {$lotLabel} (Tidak ditemukan di Gudang Asal, diminta: " . number_format($chkQty, 2) . ")";
                    } else {
                        $availQty = (float)$chkLot->qty;
                        if ($availQty < $chkQty && !empty($chkLot->lot_no)) {
                            $altLot = $this->db->table($this->tblTrxLots)
                                ->where('lot_no', $chkLot->lot_no)
                                ->where('id_barang', $chkIdBarang)
                                ->where('id_gudang', $data['id_gudang_asal'])
                                ->where('active', 1)
                                ->where('qty >=', $chkQty)
                                ->orderBy('qty', 'DESC')
                                ->get()->getRow();
                            if (!empty($altLot)) {
                                $availQty = (float)$altLot->qty;
                            }
                        }

                        if ($availQty - $chkQty < -0.0001) {
                            $insufficientLots[] = "• Lot {$chkLot->lot_no} (Tersedia: " . number_format($availQty, 2) . ", Diminta: " . number_format($chkQty, 2) . ")";
                        }
                    }
                }

                if (!empty($insufficientLots)) {
                    throw new \Exception("NEED_STOCK_CONFIRMATION: Terdapat stok lot yang tidak mencukupi di Gudang Asal:\n" . implode("\n", $insufficientLots));
                }
            }

            if(!empty($detail)){
                foreach ($detail as $key => $rowData) {
                    if ($rowData['id_barang'] != "") {
                        // $idBarang = decrypt($rowData['id_barang']);
                        $idBarang = $rowData['id_barang'];
                    }

                    // Kunci harga: pastikan harga setiap item transfer selalu mengikuti harga resmi
                    // perolehan lot (trans_receive_detail) atau ref_barang, tidak boleh dimanipulasi user.
                    $authenticPrice = null;
                    $lotNoVal = trim((string)($rowData['lot_no'] ?? ''));
                    if (empty($lotNoVal) || $lotNoVal === '-') {
                        if (!empty($rowData['lot_id'])) {
                            $lotObj = $this->db->table('trans_lots')->where('id', $rowData['lot_id'])->get()->getRow();
                            if (!empty($lotObj) && !empty($lotObj->lot_no)) {
                                $lotNoVal = trim((string)$lotObj->lot_no);
                            }
                        }
                    }
                    if (!empty($lotNoVal) && $lotNoVal !== '-') {
                        $rowRec = $this->db->table('trans_receive_detail trd')
                            ->select('trd.price')
                            ->join('trans_receive_header trh', 'trh.id = trd.id_header')
                            ->where('trd.id_barang', $idBarang)
                            ->where('trd.lot_no', $lotNoVal)
                            ->where('trd.price >', 0)
                            ->where('trd.active', 1)
                            ->orderBy('trh.id', 'DESC')
                            ->get()->getRow();
                        if (!empty($rowRec) && !empty($rowRec->price) && (float)$rowRec->price > 0) {
                            $authenticPrice = (float)$rowRec->price;
                        }
                    }
                    if (empty($authenticPrice) && !empty($idBarang)) {
                        $refB = $this->db->table('ref_barang')
                            ->select('harga_satuan')
                            ->where('id', $idBarang)
                            ->get()->getRow();
                        if (!empty($refB) && !empty($refB->harga_satuan) && (float)$refB->harga_satuan > 0) {
                            $authenticPrice = (float)$refB->harga_satuan;
                        }
                    }
                    if (!empty($authenticPrice) && $authenticPrice > 0) {
                        $rowData['price'] = $authenticPrice;
                    }
                
                    // Resolve lot_id cerdas: pastikan lot_id terisi ID valid dari trans_lots di gudang asal
                    $resolvedLotId = !empty($rowData['lot_id']) ? (int)$rowData['lot_id'] : 0;
                    if (!empty($resolvedLotId)) {
                        $currLot = $this->db->table($this->tblTrxLots)->where('id', $resolvedLotId)->get()->getRow();
                        if (empty($currLot) || (float)$currLot->qty < (float)$rowData['qty']) {
                            if (!empty($lotNoVal) && $lotNoVal !== '-' && !empty($idBarang)) {
                                $betterLot = $this->db->table($this->tblTrxLots)
                                    ->where('lot_no', $lotNoVal)
                                    ->where('id_barang', $idBarang)
                                    ->where('id_gudang', $data['id_gudang_asal'])
                                    ->where('active', 1)
                                    ->where('qty >=', (float)$rowData['qty'])
                                    ->orderBy('qty', 'DESC')
                                    ->get()->getRow();
                                if (!empty($betterLot)) {
                                    $resolvedLotId = (int)$betterLot->id;
                                    $rowData['lot_id'] = $resolvedLotId;
                                }
                            }
                        }
                    } else if (empty($resolvedLotId) && !empty($lotNoVal) && $lotNoVal !== '-' && !empty($idBarang)) {
                        $betterLot = $this->db->table($this->tblTrxLots)
                            ->where('lot_no', $lotNoVal)
                            ->where('id_barang', $idBarang)
                            ->where('id_gudang', $data['id_gudang_asal'])
                            ->where('active', 1)
                            ->where('qty >=', (float)$rowData['qty'])
                            ->orderBy('qty', 'DESC')
                            ->get()->getRow();
                        if (!empty($betterLot)) {
                            $resolvedLotId = (int)$betterLot->id;
                            $rowData['lot_id'] = $resolvedLotId;
                        } else {
                            $mBarangMasukTmp = new IncomingGoodsModel();
                            $foundLot = $mBarangMasukTmp->getLotNo($lotNoVal, $idBarang, null, $data['id_gudang_asal'], !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);
                            if (empty($foundLot)) {
                                $foundLot = $mBarangMasukTmp->getLotNo($lotNoVal, $idBarang, null, $data['id_gudang_asal']);
                            }
                            if (!empty($foundLot)) {
                                $resolvedLotId = (int)$foundLot->id;
                                $rowData['lot_id'] = $resolvedLotId;
                            }
                        }
                    }

                    $dataDetail = [
                        "id_barang" => $idBarang,
                        "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                        "kode_walkorder" => !empty($rowData['kode_walkorder']) ? $rowData['kode_walkorder'] : null,
                        "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                        "lot_id" => $resolvedLotId,
                        "keterangan" => !empty($rowData['keterangan']) ? $rowData['keterangan'] : null,
                        "id_header" => $id,
                        "qty" => $rowData['qty'],
                        "price" => !empty($rowData['price'])
                            ? (int) preg_replace('/[^0-9]/', '', $rowData['price'])
                            : 0,
                    ];
                    $this->insertRecordGetid($this->tblDet, $dataDetail);
                    $trxTanggal = !empty($data['tanggal']) ? (strlen($data['tanggal']) <= 10 ? $data['tanggal'] . " " . date("H:i:s") : $data['tanggal']) : date("Y-m-d H:i:s");
                    if ($data['status'] == 1 && $key === 0 && !empty($data['kode_transaksi'])) {
                        // CATATAN (2026-08-08): sebelum baris trans_barang lama dihapus,
                        // qty yang PERNAH ditambah/dikurangi ke trans_lots akibat dokumen
                        // ini (kalau dokumen ini sudah pernah disimpan/di-approve sebelumnya
                        // lalu diedit ulang) harus dibalik dulu - baris trans_barang lama
                        // adalah catatan persis berapa & dari lot mana. jenis_transaksi=1
                        // ("masuk", ke lot tujuan) dibalik dgn DIKURANGI lagi; jenis_transaksi=2
                        // ("keluar", dari lot asal) dibalik dgn DITAMBAH lagi. Tanpa ini,
                        // trans_lots akan terus bertambah/berkurang dobel setiap kali
                        // dokumen Item Transfer yang sama diedit/disimpan ulang - pola bug
                        // sama seperti yang sudah diperbaiki di trans_barang (Barang Masuk),
                        // dan trans_lots (Delivery Order).
                        $oldTransBarangTrf = $this->db->table($this->tblTrxBarang)
                            ->select('lot_id, jenis_transaksi, SUM(jumlah) as jumlah')
                            ->where('kode_transaksi', $data['kode_transaksi'])
                            ->where('COALESCE(active, 1) = 1')
                            ->groupBy('lot_id, jenis_transaksi')
                            ->get()->getResult();

                        foreach ($oldTransBarangTrf as $otb) {
                            if (empty($otb->lot_id)) continue;
                            $op = $otb->jenis_transaksi == 1 ? '-' : '+';
                            $this->db->table($this->tblTrxLots)
                                ->where('id', $otb->lot_id)
                                ->set('qty', 'qty ' . $op . ' ' . (float) $otb->jumlah, false)
                                ->update();
                        }

                        $userId = session()->get('user_id') ?? ($data['updated_by'] ?? ($data['created_by'] ?? 1));
                        $this->db->table($this->tblTrxBarang)
                            ->where('kode_transaksi', $data['kode_transaksi'])
                            ->where('COALESCE(active, 1) = 1')
                            ->set([
                                'active'     => 0,
                                'deleted_at' => date('Y-m-d H:i:s'),
                                'deleted_by' => $userId,
                            ])
                            ->update();
                    }
                    if ($data['status'] == 1) {
                        $mBarangMasuk = new IncomingGoodsModel();

                        // Cek apakah transfer ini Antar-Perusahaan (Beda Perusahaan Asal & Tujuan)
                        // ataukah Internal (Perusahaan Sama).
                        $gudangAsalObj = !empty($data['id_gudang_asal']) ? $this->db->table($this->tblGudang)->where('id', $data['id_gudang_asal'])->get()->getRow() : null;
                        $gudangTujuanObj = !empty($data['id_gudang_tujuan']) ? $this->db->table($this->tblGudang)->where('id', $data['id_gudang_tujuan'])->get()->getRow() : null;
                        $isCrossCompany = ($gudangAsalObj && $gudangTujuanObj && (int)$gudangAsalObj->id_perusahaan !== (int)$gudangTujuanObj->id_perusahaan);

                        // 1. STOK MASUK KE GUDANG TUJUAN:
                        // Hanya dieksekusi jika transfer INTERNAL (1 perusahaan).
                        // Jika Beda Perusahaan (2 tahap), stok masuk akan dicatat saat pihak penerima membuat & approve BTM.
                        if (!$isCrossCompany) {
                            $arrParam =  [
                                "id_barang" => $idBarang,
                                "id_gudang_tujuan" => $data['id_gudang_tujuan'],
                            ];
                            // FIX (2026-08-08): sebelumnya id_gudang_tujuan masuk ke parameter
                            // ke-3 getLotNo() (idLotNo - filter berdasarkan ID baris trans_lots
                            // spesifik), bukan parameter ke-4 (idGudang) yang seharusnya. Salah
                            // slot argumen ini bisa membuat lot yang diambil salah/tidak ketemu.
                            $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, null, $data['id_gudang_tujuan']);
                            $dataLots = [
                                "id_barang" => $idBarang,
                                "id_gudang" => !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                                "tanggal" => $trxTanggal,
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
                                "tanggal" => $trxTanggal,
                                "id_gudang_tujuan" =>  !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                                "id_kategori" => 4,
                                "keterangan" => "Barang Masuk Dari Transfer",
                                "active" => 1,
                                "tipe" => 1,
                                "created_at" =>  date("Y-m-d H:i:s"),
                                "lot_id" => $idLotsMasuk,
                                "lot_no" => $rowData['lot_no'],
                                "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                                "price" => !empty($rowData['price']) ? (int) preg_replace('/[^0-9]/', '', $rowData['price']) : 0,
                                "kode_transaksi" => $data['kode_transaksi'],
                            ];
                            $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                            $arrStockBalances = [
                                "id_barang" => $idBarang,
                                "id_gudang" => !empty($data['id_gudang_tujuan']) ? $data['id_gudang_tujuan'] : null,
                                "tanggal" => $trxTanggal,
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
                        }

                        // 2. STOK KELUAR DARI GUDANG ASAL:
                        // Selalu dieksekusi baik transfer Internal maupun Antar-Perusahaan.
                        $resLotNo = null;
                        if (!empty($rowData['lot_id'])) {
                            $resLotNo = $mBarangMasuk->getLotNo(null, null, $rowData['lot_id']);
                        }
                        if (empty($resLotNo) && !empty($rowData['lot_no']) && !empty($idBarang)) {
                            $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, null, $data['id_gudang_asal'], !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);
                            if (empty($resLotNo)) {
                                $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, null, $data['id_gudang_asal']);
                            }
                        }

                        if (empty($resLotNo)) {
                            $lotNama = !empty($rowData['lot_no']) ? $rowData['lot_no'] : '-';
                            if (empty($forceContinue)) {
                                throw new \Exception("NEED_STOCK_CONFIRMATION: Stok lot '{$lotNama}' tidak ditemukan di Gudang Asal untuk barang ini!");
                            } else {
                                $newLotId = $this->insertRecordGetid($this->tblTrxLots, [
                                    "id_barang" => $idBarang,
                                    "id_gudang" => !empty($data['id_gudang_asal']) ? $data['id_gudang_asal'] : null,
                                    "tanggal" => $trxTanggal,
                                    "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : '-',
                                    "qty" => 0 - (float)$rowData['qty'],
                                    "active" => 1,
                                    "created_at" => date("Y-m-d H:i:s"),
                                ]);
                                $resLotNo = (object)[
                                    "id" => $newLotId,
                                    "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : '-',
                                    "qty" => 0
                                ];
                                $idLots = $newLotId;
                                $sisaLot = 0 - (float)$rowData['qty'];
                            }
                        } else {
                            // Smart fallback: Jika lot_id yang diikat stoknya kurang dari diminta,
                            // cari apakah ada baris lot lain di gudang asal dengan nomor lot yang sama yang stoknya mencukupi
                            if (!empty($resLotNo) && (float)$resLotNo->qty < (float)$rowData['qty'] && !empty($resLotNo->lot_no) && !empty($idBarang)) {
                                $altLot = $this->db->table($this->tblTrxLots)
                                    ->where('lot_no', $resLotNo->lot_no)
                                    ->where('id_barang', $idBarang)
                                    ->where('id_gudang', $data['id_gudang_asal'])
                                    ->where('active', 1)
                                    ->where('qty >=', (float)$rowData['qty'])
                                    ->orderBy('qty', 'DESC')
                                    ->get()->getRow();
                                if (!empty($altLot)) {
                                    $resLotNo = $altLot;
                                    if (!empty($rowData['id'])) {
                                        $this->updateRecords($this->tblDet, ['lot_id' => $altLot->id], ['id' => $rowData['id']]);
                                    }
                                }
                            }

                            $idLots = $resLotNo->id;
                            $sisaLot = (float)$resLotNo->qty - (float)$rowData['qty'];
                            if ($sisaLot < -0.0001) {
                                if (empty($forceContinue)) {
                                    throw new \Exception("NEED_STOCK_CONFIRMATION: Stok lot {$resLotNo->lot_no} tidak mencukupi untuk transfer! Stok tersedia: " . number_format($resLotNo->qty, 2) . ", diminta: " . number_format($rowData['qty'], 2));
                                }
                            }
                            $this->updateRecords($this->tblTrxLots, array("qty" => $sisaLot), array("id" => $idLots));
                        }
    
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang_asal'], $idLots, !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);
    
                        // $stokAwal = !empty($resData) ? $resData->stok : 0;
                        $dataBarangAsal = [
                            "id_barang" => $idBarang,
                            "jenis_transaksi" => 2,
                            "jumlah" =>  $rowData['qty'],
                            "tanggal" => $trxTanggal,
                            "id_gudang_asal" =>  !empty($data['id_gudang_asal']) ? $data['id_gudang_asal'] : null,
                            "id_kategori" => 4,
                            "keterangan" => "Barang Keluar Dari Transfer",
                            "active" => 1,
                            "tipe" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                            "price" => !empty($rowData['price']) ? (int) preg_replace('/[^0-9]/', '', $rowData['price']) : 0,
                            "kode_transaksi" => $data['kode_transaksi'],
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarangAsal);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang_asal']) ? $data['id_gudang_asal'] : null,
                            "tanggal" => $trxTanggal,
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
                return $id;
            } else {

                throw new \Exception("Transaction failed");
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * TAMBAHAN (2026-08-09): "Batalkan Approve" - membalik SEMUA efek yang
     * terjadi saat dokumen ini di-approve (lihat blok status==1 di
     * trxInsertUpdateRecord() di atas), lalu kembalikan status ke Draft (0).
     * Baris detail barang & SO TIDAK dihapus, supaya dokumen bisa diedit &
     * di-approve ulang.
     *
     * Yang dibalik:
     * 1) trans_lots - qty di lot tujuan dikurangi lagi, qty di lot asal
     *    ditambah lagi (kebalikan persis dari saat approve).
     * 2) trans_barang_balances - baris snapshot HARI INI (kalau ada) utk
     *    lot terkait disamakan lagi ke qty trans_lots yg sudah dibalik,
     *    supaya tidak nyangkut di angka lama.
     * 3) trans_barang - baris masuk & keluar milik dokumen ini dihapus.
     * 4) qty_kirim di trans_barang_masuk_produksi (tracking produksi per
     *    SO/Sample + ukuran + warna + proses + CMT) dikurangi lagi sebesar
     *    qty yang tadi ditambahkan dokumen ini saat approve.
     */
    function cancelApprove($id, $updatedBy = null)
    {
        $this->db->transStart();
        try {
            $header = $this->getData($id);
            if (empty($header)) {
                throw new \Exception("Data transfer tidak ditemukan.");
            }
            if ((int) $header->status !== 1) {
                throw new \Exception("Dokumen ini belum di-Approve, tidak bisa dibatalkan.");
            }

            // 1) & 2) Balikkan trans_lots + samakan snapshot trans_barang_balances
            // hari ini, berdasarkan baris trans_barang yang tercatat utk
            // kode_transaksi ini.
            $trxRows = $this->db->table($this->tblTrxBarang)
                ->select('id_barang, lot_id, pack_id, jenis_transaksi, id_gudang_asal, id_gudang_tujuan, SUM(jumlah) as jumlah')
                ->where('kode_transaksi', $header->kode_transaksi)
                ->where('COALESCE(active, 1) = 1')
                ->groupBy('id_barang, lot_id, pack_id, jenis_transaksi, id_gudang_asal, id_gudang_tujuan')
                ->get()->getResult();

            foreach ($trxRows as $row) {
                if (empty($row->lot_id)) continue;

                $op = $row->jenis_transaksi == 1 ? '-' : '+';
                $this->db->table($this->tblTrxLots)
                    ->where('id', $row->lot_id)
                    ->set('qty', 'qty ' . $op . ' ' . (float) $row->jumlah, false)
                    ->update();

                $idGudangTerkait = $row->jenis_transaksi == 1 ? $row->id_gudang_tujuan : $row->id_gudang_asal;
                $lotBaru = $this->db->table($this->tblTrxLots)->where('id', $row->lot_id)->get()->getRow();
                if (!empty($lotBaru)) {
                    $this->db->table($this->tblTrxBalances)
                        ->where('id_barang', $row->id_barang)
                        ->where('id_gudang', $idGudangTerkait)
                        ->where('lot_id', $row->lot_id)
                        ->where('pack_id', $row->pack_id)
                        ->where('tanggal', date('Y-m-d'))
                        ->set('saldo_akhir', $lotBaru->qty)
                        ->update();
                }
            }

            // 3) Soft delete baris trans_barang (masuk & keluar) milik dokumen ini.
            $userId = session()->get('user_id') ?? 1;
            $this->db->table($this->tblTrxBarang)
                ->where('kode_transaksi', $header->kode_transaksi)
                ->where('COALESCE(active, 1) = 1')
                ->set([
                    'active'     => 0,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'deleted_by' => $userId,
                ])
                ->update();

            // 4) Balikkan qty_kirim BTM/tracking produksi per baris SO/Sample (hanya untuk non-reject).
            $soRows = $this->db->table($this->tblDetailSO)
                ->select('kode_sales_order, kode_ukuran, color, id_konsumen, qty, COALESCE(is_reject, 0) as is_reject')
                ->where('id_header', $id)
                ->get()->getResult();

            foreach ($soRows as $s) {
                if (!empty($s->is_reject) && $s->is_reject == 1) {
                    continue; // Barang Reject tidak pernah menambah qty_kirim saat approve
                }
                $this->updateDataBTMQtyKirim(
                    $s->kode_sales_order,
                    $s->kode_ukuran,
                    $s->color,
                    $s->id_konsumen,
                    $header->id_proses,
                    $header->id_cmt,
                    'minus',
                    $s->qty
                );
            }

            // 5) Kembalikan status dokumen ke Draft.
            $updateHeader = ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')];
            if (!empty($updatedBy)) {
                $updateHeader['updated_by'] = $updatedBy;
            }
            $this->updateRecords($this->table, $updateHeader, ['id' => $id]);

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

    function get_export($from_date = null, $to_date = null, $id_perusahaan = null)
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
            "COALESCE(rp.nama_perusahaan, CASE WHEN tbth.id_perusahaan = 2 THEN 'Citra Knitt' ELSE 'PT Citra Jaya Kniting' END) AS perusahaan",
            'COALESCE(tbth.id_perusahaan, 1) AS id_perusahaan',
            'jp.nama AS proses',
            'ro.nama_operator',
            'tbtsd.color',
            'tbtsd.kode_ukuran',
            'COALESCE(tbtsd.print_type, 1) AS print_type',
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
                AND (
                    CASE 
                        WHEN COALESCE(tbth.id_perusahaan, 1) = 2 
                        THEN (tbh.id_perusahaan = 2 OR LEFT(tbh.kode_transaksi, 4) = 'BTMC') 
                        ELSE (tbh.id_perusahaan = 1 OR tbh.id_perusahaan IS NULL) 
                    END
                )
            ), 0) AS qty_terima,
             COALESCE(
                ( 
                    SELECT SUM(tsu.qty)
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
                    SELECT SUM(tsou.qty)
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
        $subBuilder->join('ref_perusahaan rp', 'rp.id = tbth.id_perusahaan', 'left');

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

        if (!empty($id_perusahaan)) {
            $subBuilder->where('tbth.id_perusahaan', $id_perusahaan);
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
            'perusahaan',
            'id_perusahaan',
            'proses',
            'nama_operator',
            'color',
            'kode_ukuran',
            'print_type',
            'SUM(qty) AS qty',
            'qty_terima',
            'kode',
            'tgl_transaksi',
            'qty_ref'
        ]);

        $builder->groupBy([
            'buyer',
            'style',
            'perusahaan',
            'id_perusahaan',
            'proses',
            'nama_operator',
            'color',
            'kode_ukuran',
            'print_type',
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
            'COALESCE(tbtsd.is_reject, 0) AS is_reject',
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
                    SELECT SUM(tsu.qty) FROM trans_sample_ukuran tsu 
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
                    SELECT SUM(tsou.qty) FROM trans_sales_order_ukuran tsou 
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
            'MAX(is_reject) AS is_reject',
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

    function isProcessRajut($id_proses = null)
    {
        if (empty($id_proses)) return false;
        $row = $this->db->table('_jenis_proses_produksi')
            ->select('is_rajut')
            ->where('id', $id_proses)
            ->where('active', 1)
            ->get()->getRow();
        if (!empty($row) && $row->is_rajut == 1) {
            return true;
        }
        return ($id_proses == 1);
    }

    function getDataRajut($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $id = null, $style = null)
    {
        $getBaseBuilder = function() use ($id_konsumen, $kode_ukuran, $kode_so, $color, $style) {
            $b = $this->db->table('trans_barang_masuk_produksi tbmp');
            $b->join("trans_barang_header tbh", "tbh.id = tbmp.id_header", "inner");
            $b->join("_jenis_proses_produksi jpp", "jpp.id = tbh.id_proses", "left");
            $b->select("sum(tbmp.qty) as total_proses");
            $b->where('tbh.status', 1);
            $b->where('tbh.active', 1);
            $b->groupStart();
                $b->where('tbh.id_perusahaan', 1);
                $b->orWhere('tbh.id_perusahaan IS NULL');
            $b->groupEnd();

            if (!empty($id_konsumen)) $b->where('tbmp.id_konsumen', $id_konsumen);
            if (!empty($kode_ukuran)) $b->where('tbmp.kode_ukuran', $kode_ukuran);
            if (!empty($kode_so)) $b->where('tbmp.kode_sales_order', $kode_so);
            if (!empty($color)) $b->where('tbmp.color', $color);
            if (!empty($style)) $b->where('tbmp.style', $style);

            return $b;
        };

        // 1. Cek apakah ada proses 'RAJUT DBT' (id 31 / Depan Belakang Tangan - Badan Lengkap)
        $builderDBT = $getBaseBuilder();
        $builderDBT->groupStart();
            $builderDBT->where('jpp.id', 31);
            $builderDBT->orWhere("UPPER(jpp.nama) LIKE '%RAJUT DBT%'");
            $builderDBT->orWhere("UPPER(jpp.nama) LIKE '%RAJUT%DBT%'");
        $builderDBT->groupEnd();
        $resDBT = $builderDBT->get()->getRow();
        $totalDBT = !empty($resDBT->total_proses) ? (float)$resDBT->total_proses : 0;
        if ($totalDBT > 0) {
            return $totalDBT;
        }

        // 2. Cek apakah ada proses 'RAJUT DEPAN' / 'RAJUT BD' (id 29 / Badan Depan)
        $builderBD = $getBaseBuilder();
        $builderBD->groupStart();
            $builderBD->where('jpp.id', 29);
            $builderBD->orWhere("UPPER(jpp.nama) LIKE '%RAJUT DEPAN%'");
            $builderBD->orWhere("UPPER(jpp.nama) LIKE '%RAJUT BD%'");
            $builderBD->orWhere("UPPER(jpp.nama) LIKE '%RAJUT%BD%'");
        $builderBD->groupEnd();
        $resBD = $builderBD->get()->getRow();
        $totalBD = !empty($resBD->total_proses) ? (float)$resBD->total_proses : 0;
        if ($totalBD > 0) {
            return $totalBD;
        }

        // 3. Cek apakah ada proses 'RAJUT DB' (id 1 / Depan Belakang)
        $builderDB = $getBaseBuilder();
        $builderDB->groupStart();
            $builderDB->where('jpp.id', 1);
            $builderDB->orWhere("UPPER(jpp.nama) LIKE '%RAJUT DB%'");
            $builderDB->orWhere("UPPER(jpp.nama) LIKE '%RAJUT%DB%'");
        $builderDB->groupEnd();
        $resDB = $builderDB->get()->getRow();
        $totalDB = !empty($resDB->total_proses) ? (float)$resDB->total_proses : 0;
        if ($totalDB > 0) {
            return $totalDB;
        }

        // 4. Cek proses rajut badan lain (kecuali KRAH dan BT yang merupakan bagian terpisah/aksesoris)
        $builderOther = $getBaseBuilder();
        $builderOther->groupStart();
            $builderOther->where('jpp.is_rajut', 1);
            $builderOther->orWhere('tbh.id_proses', 1);
        $builderOther->groupEnd();
        $builderOther->whereNotIn('jpp.id', [30, 32]);
        $builderOther->where("UPPER(jpp.nama) NOT LIKE '%KRAH%'");
        $builderOther->where("UPPER(jpp.nama) NOT LIKE '%KERAH%'");
        $builderOther->where("UPPER(jpp.nama) NOT LIKE '%BT%'");
        $resOther = $builderOther->get()->getRow();
        $totalOther = !empty($resOther->total_proses) ? (float)$resOther->total_proses : 0;
        if ($totalOther > 0) {
            return $totalOther;
        }

        // 5. Fallback: jika hanya ada 1 proses rajut tunggal apa pun
        $builderFallback = $getBaseBuilder();
        $builderFallback->groupStart();
            $builderFallback->where('jpp.is_rajut', 1);
            $builderFallback->orWhere('tbh.id_proses', 1);
        $builderFallback->groupEnd();
        $resFallback = $builderFallback->get()->getRow();
        $this->_data = !empty($resFallback->total_proses) ? (float)$resFallback->total_proses : 0;

        return $this->_data;
    }

    function getDataPengurang($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $id = null, $style = null)
    {
        $builder = $this->db->table('trans_barang_trf_so_det tbtsd');
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtsd.id_header", "inner");
        
        $builder->select("sum(tbtsd.qty) as total_proses");

        $builder->where("LEFT(tbth.kode_transaksi, 4) !=", "TRFC");
        $builder->where("tbth.id_perusahaan !=", 2);
        $builder->whereNotIn("tbth.id_gudang_asal", [51, 56, 59, 60, 63, 70, 74]);
        $builder->where('tbth.id_proses', $id_proses);
        $builder->where('tbth.status', 1);
        $builder->where('tbth.active', 1);
        if (!empty($id)) {
            $builder->where('tbth.id !=', $id);
        }
        $builder->where('tbtsd.id_konsumen', $id_konsumen);
        $builder->where('tbtsd.kode_ukuran', $kode_ukuran);
        $builder->where('tbtsd.kode_sales_order', $kode_so);
        $builder->where('tbtsd.color', $color);
        if (!empty($style)) {
            $builder->where('tbtsd.style', $style);
        }

        $this->_data = $builder->get()->getRow()->total_proses;

        return (float)$this->_data;
    }

    function getDataBtmP2($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $style = null)
    {
        $builder = $this->db->table('trans_barang_masuk_produksi tbmp');
        $builder->join("trans_barang_header tbh", "tbh.id = tbmp.id_header", "inner");
        $builder->join("_jenis_proses_produksi jpp", "jpp.id = tbh.id_proses", "left");
        
        $builder->select("sum(tbmp.qty) as total_proses");

        $builder->where('tbh.status', 1);
        $builder->where('tbh.active', 1);
        $builder->groupStart();
            $builder->where('tbh.id_perusahaan', 2);
            $builder->orWhere("LEFT(tbh.kode_transaksi, 4)", "BTMC");
        $builder->groupEnd();

        if (!empty($id_proses)) {
            $builder->where('tbh.id_proses', $id_proses);
        }

        $builder->where('tbmp.id_konsumen', $id_konsumen);
        $builder->where('tbmp.kode_ukuran', $kode_ukuran);
        $builder->where('tbmp.kode_sales_order', $kode_so);
        if (!empty($color)) {
            $builder->where('tbmp.color', $color);
        }
        if (!empty($style)) {
            $builder->where('tbmp.style', $style);
        }

        $this->_data = $builder->get()->getRow()->total_proses;

        $totalBtm = !empty($this->_data) ? (float)$this->_data : 0;
        
        // Fallback: Jika P2 belum sempat input BTMC, gunakan TRF Masuk dari P1 agar operasional tidak terhambat
        if ($totalBtm <= 0) {
            return $this->getDataTrfMasukP2($id_proses, $id_konsumen, $kode_ukuran, $kode_so, $color, $style);
        }

        return $totalBtm;
    }

    function getDataTrfMasukP2($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $style = null)
    {
        $builder = $this->db->table('trans_barang_trf_so_det tbtsd');
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtsd.id_header", "inner");
        
        $builder->select("sum(tbtsd.qty) as total_proses");

        $builder->where("LEFT(tbth.kode_transaksi, 4) !=", "TRFC");
        $builder->where("tbth.id_perusahaan !=", 2);
        $builder->whereNotIn("tbth.id_gudang_asal", [51, 56, 59, 60, 63, 70, 74]);
        $builder->where('tbth.id_proses', $id_proses);
        $builder->where('tbth.status', 1);
        $builder->where('tbth.active', 1);
        $builder->where('tbtsd.id_konsumen', $id_konsumen);
        $builder->where('tbtsd.kode_ukuran', $kode_ukuran);
        $builder->where('tbtsd.kode_sales_order', $kode_so);
        if (!empty($color)) {
            $builder->where('tbtsd.color', $color);
        }
        if (!empty($style)) {
            $builder->where('tbtsd.style', $style);
        }

        $this->_data = $builder->get()->getRow()->total_proses;

        return (float)$this->_data;
    }

    function getDataPengurangP2($id_proses = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $id = null, $style = null)
    {
        $builder = $this->db->table('trans_barang_trf_so_det tbtsd');
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtsd.id_header", "inner");
        
        $builder->select("sum(tbtsd.qty) as total_proses");

        $builder->groupStart();
            $builder->where("LEFT(tbth.kode_transaksi, 4)", "TRFC");
            $builder->orWhere("tbth.id_perusahaan", 2);
            $builder->orWhereIn("tbth.id_gudang_asal", [51, 56, 59, 60, 63, 70, 74]);
        $builder->groupEnd();
        $builder->where('tbth.id_proses', $id_proses);
        $builder->where('tbth.status', 1);
        $builder->where('tbth.active', 1);
        if (!empty($id)) {
            $builder->where('tbth.id !=', $id);
        }
        $builder->where('tbtsd.id_konsumen', $id_konsumen);
        $builder->where('tbtsd.kode_ukuran', $kode_ukuran);
        $builder->where('tbtsd.kode_sales_order', $kode_so);
        if (!empty($color)) {
            $builder->where('tbtsd.color', $color);
        }
        if (!empty($style)) {
            $builder->where('tbtsd.style', $style);
        }

        $this->_data = $builder->get()->getRow()->total_proses;

        return (float)$this->_data;
    }

    function get_export_bahan_jadi($from_date = null, $to_date = null, $id_perusahaan = null){
        $builder = $this->db->table("trans_barang_trf_so_det tbtd");
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtd.id_header", "inner");
        $builder->join("ref_perusahaan rp", "rp.id = tbth.id_perusahaan", "left");
        $builder->join("_jenis_proses_produksi jpp", "jpp.id = tbth.id_proses", "left");
        $builder->join("ref_operator ro", "ro.id = tbth.id_cmt", "left");
        $builder->join("ref_gudang rg", "rg.id = tbth.id_gudang_asal", "left");
        $builder->join("ref_gudang rg2", "rg2.id = tbth.id_gudang_tujuan", "left");

        $builder->select("tbth.kode_transaksi, tbth.tanggal, COALESCE(rp.nama_perusahaan, CASE WHEN tbth.id_perusahaan = 2 THEN 'Citra Knitt' ELSE 'PT Citra Jaya Kniting' END) as perusahaan, COALESCE(tbth.id_perusahaan, 1) as id_perusahaan, COALESCE(tbtd.print_type, 1) as print_type, rg.nama_gudang as gudang_asal, rg2.nama_gudang as gudang_tujuan, 
                        jpp.nama as proses, ro.nama_operator, tbtd.kode_sales_order, tbtd.style, tbtd.color, tbtd.kode_ukuran, 
                        tbtd.qty, tbtd.keterangan, COALESCE(tbtd.is_reject, 0) as is_reject");

        $builder->where('tbth.active', 1);

        $builder->where("tbth.tanggal BETWEEN '$from_date' AND '$to_date'");
        if (!empty($id_perusahaan)) {
            $builder->where('tbth.id_perusahaan', $id_perusahaan);
        }
        $builder->orderBy("tbth.tanggal", "asc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_export_bahan_baku($from_date = null, $to_date = null, $id_perusahaan = null){
        $builder = $this->db->table("trans_barang_trf_detail tbtd");
        $builder->join("trans_barang_trf_header tbth", "tbth.id = CAST(tbtd.id_header AS INTEGER)", "inner");
        $builder->join("ref_perusahaan rp", "rp.id = tbth.id_perusahaan", "left");
        $builder->join("_jenis_proses_produksi jpp", "jpp.id = tbth.id_proses", "left");
        $builder->join("ref_operator ro", "ro.id = tbth.id_cmt", "left");
        $builder->join("ref_gudang rg", "rg.id = tbth.id_gudang_asal", "left");
        $builder->join("ref_gudang rg2", "rg2.id = tbth.id_gudang_tujuan", "left");
        $builder->join("ref_barang rb", "rb.id = tbtd.id_barang", "inner");
        $builder->join("ref_satuan rs", "rs.id = rb.id_satuan", "inner");

        $builder->select("tbth.kode_transaksi, tbth.tanggal, COALESCE(rp.nama_perusahaan, CASE WHEN tbth.id_perusahaan = 2 THEN 'Citra Knitt' ELSE 'PT Citra Jaya Kniting' END) as perusahaan, COALESCE(tbth.id_perusahaan, 1) as id_perusahaan, rg.nama_gudang as gudang_asal, rg2.nama_gudang as gudang_tujuan, 
                        jpp.nama as proses, ro.nama_operator, rb.kode_barang, rb.nama_barang, tbtd.qty, rs.nama_satuan, tbtd.lot_no, tbtd.keterangan");

        $builder->where('tbth.active', 1);

        $builder->where("tbth.tanggal BETWEEN '$from_date' AND '$to_date'");
        if (!empty($id_perusahaan)) {
            $builder->where('tbth.id_perusahaan', $id_perusahaan);
        }
        $builder->orderBy("tbth.tanggal", "asc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataTransferMasukP2($id_proses = null, $id_cmt = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $style = null)
    {
        $builder = $this->db->table('trans_barang_trf_so_det tbtsd');
        $builder->join("trans_barang_trf_header tbth", "tbth.id = tbtsd.id_header", "inner");
        $builder->join("ref_gudang g_asal", "g_asal.id = tbth.id_gudang_asal", "inner");
        $builder->join("ref_gudang g_tujuan", "g_tujuan.id = tbth.id_gudang_tujuan", "inner");
        $builder->select("sum(tbtsd.qty) as total_transfer");

        $builder->where('tbth.status', 1);
        $builder->where('tbth.active', 1);
        $builder->groupStart();
            $builder->where('g_asal.id_perusahaan', 1);
            $builder->orWhere('g_asal.id_perusahaan IS NULL');
        $builder->groupEnd();
        $builder->where('g_tujuan.id_perusahaan', 2);

        if (!empty($id_proses)) {
            $builder->where('tbth.id_proses', $id_proses);
        }
        if (!empty($id_cmt)) {
            $builder->where('tbth.id_cmt', $id_cmt);
        }
        if (!empty($id_konsumen)) {
            $builder->where('tbtsd.id_konsumen', $id_konsumen);
        }
        if (!empty($kode_ukuran)) {
            $builder->where('tbtsd.kode_ukuran', $kode_ukuran);
        }
        if (!empty($kode_so)) {
            $builder->where('tbtsd.kode_sales_order', $kode_so);
        }
        if (!empty($color)) {
            $builder->where('tbtsd.color', $color);
        }

        $res = $builder->get()->getRow();
        return !empty($res->total_transfer) ? (float)$res->total_transfer : 0;
    }

    function getDataBtmP1($id_proses = null, $id_cmt = null, $id_konsumen = null, $kode_ukuran = null, $kode_so = null, $color = null, $style = null)
    {
        $builder = $this->db->table('trans_barang_masuk_produksi tbmp');
        $builder->join("trans_barang_header tbh", "tbh.id = tbmp.id_header", "inner");
        $builder->select("sum(tbmp.qty) as total_btm");

        $builder->where('tbh.status', 1);
        $builder->where('tbh.active', 1);
        $builder->groupStart();
            $builder->where('tbh.id_perusahaan', 1);
            $builder->orWhere('tbh.id_perusahaan IS NULL');
        $builder->groupEnd();
        $builder->where("LEFT(tbh.kode_transaksi, 3) =", "BTM");

        if (!empty($id_proses)) {
            $builder->where('tbh.id_proses', $id_proses);
        }
        if (!empty($id_cmt)) {
            $builder->where('tbh.id_cmt', $id_cmt);
        }
        if (!empty($id_konsumen)) {
            $builder->where('tbmp.id_konsumen', $id_konsumen);
        }
        if (!empty($kode_ukuran)) {
            $builder->where('tbmp.kode_ukuran', $kode_ukuran);
        }
        if (!empty($kode_so)) {
            $builder->where('tbmp.kode_sales_order', $kode_so);
        }
        if (!empty($color)) {
            $builder->where('tbmp.color', $color);
        }

        $res = $builder->get()->getRow();
        return !empty($res->total_btm) ? (float)$res->total_btm : 0;
    }

    /**
     * Mengambil daftar acuan item Sales Order langsung dari trans_sales_order & trans_sample
     * Khusus untuk P2 (Citra Knitt) yang TIDAK mengikat dokumen transfer dan TIDAK mengikat WO.
     */
    public function getDataSOFromSalesOrder($id_proses = null, $kode_so = null, $days = 120)
    {
        $soWhere = "so.active = 1";
        $splWhere = "so.active = 1";

        if (!empty($kode_so)) {
            $soWhere .= " AND so.kode_sales_order = " . $this->db->escape($kode_so);
            $splWhere .= " AND so.kode_sample = " . $this->db->escape($kode_so);
        } elseif (!empty($days)) {
            $soWhere .= " AND so.tgl_transaksi >= (CURRENT_DATE - INTERVAL '{$days} days')";
            $splWhere .= " AND so.tgl_transaksi >= (CURRENT_DATE - INTERVAL '{$days} days')";
        }

        $idProsesInt = !empty($id_proses) ? (int)$id_proses : 0;

        $sql = "
            (
                SELECT 
                    sod.id as id,
                    1 as print_type,
                    0 as is_reject,
                    NULL::integer as id_header,
                    sou.qty,
                    NULL::numeric as berat,
                    2 as tipe,
                    sod.id as ref_detail_id,
                    sou.qty as qty_kirim,
                    so.kode_sales_order,
                    so.id_konsumen,
                    so.style,
                    CONCAT_WS('~', 
                        NULLIF(COALESCE(rw1.kode_warna, rbw1.kode_warna, rb1.nama_barang),''), 
                        NULLIF(COALESCE(rw2.kode_warna, rbw2.kode_warna, rb2.nama_barang),''), 
                        NULLIF(COALESCE(rw3.kode_warna, rbw3.kode_warna, rb3.nama_barang),''), 
                        NULLIF(COALESCE(rw4.kode_warna, rbw4.kode_warna, rb4.nama_barang),''), 
                        NULLIF(COALESCE(rw5.kode_warna, rbw5.kode_warna, rb5.nama_barang),''), 
                        NULLIF(COALESCE(rw6.kode_warna, rbw6.kode_warna, rb6.nama_barang),''), 
                        NULLIF(COALESCE(rw7.kode_warna, rbw7.kode_warna, rb7.nama_barang),''), 
                        NULLIF(COALESCE(rw8.kode_warna, rbw8.kode_warna, rb8.nama_barang),'')
                    ) as color,
                    NULL::numeric as amount,
                    rk.nama as buyer,
                    ru.kode_ukuran,
                    NULL::text as keterangan,
                    COALESCE(ru.key_ukuran, LOWER(REGEXP_REPLACE(ru.kode_ukuran, '[^a-zA-Z0-9]', '', 'g'))) as key_ukuran,
                    {$idProsesInt} as id_proses,
                    so.deskripsi,
                    0 as qty_terima,
                    sou.qty as qty_sisa,
                    0 as harga,
                    so.tgl_transaksi
                FROM trans_sales_order so
                INNER JOIN ref_konsumen rk ON rk.id = so.id_konsumen
                INNER JOIN trans_sales_order_det sod ON sod.id_sales_order = so.id
                INNER JOIN trans_sales_order_ukuran sou ON sou.id_sales_order_det = sod.id
                INNER JOIN ref_ukuran ru ON ru.id = sou.id_ukuran
                LEFT JOIN ref_barang rb1 ON rb1.id = sod.id_barang_1
                LEFT JOIN ref_warna rw1 ON rw1.id = sod.id_warna_1
                LEFT JOIN ref_warna rbw1 ON rbw1.id = rb1.id_warna
                LEFT JOIN ref_barang rb2 ON rb2.id = sod.id_barang_2
                LEFT JOIN ref_warna rw2 ON rw2.id = sod.id_warna_2
                LEFT JOIN ref_warna rbw2 ON rbw2.id = rb2.id_warna
                LEFT JOIN ref_barang rb3 ON rb3.id = sod.id_barang_3
                LEFT JOIN ref_warna rw3 ON rw3.id = sod.id_warna_3
                LEFT JOIN ref_warna rbw3 ON rbw3.id = rb3.id_warna
                LEFT JOIN ref_barang rb4 ON rb4.id = sod.id_barang_4
                LEFT JOIN ref_warna rw4 ON rw4.id = sod.id_warna_4
                LEFT JOIN ref_warna rbw4 ON rbw4.id = rb4.id_warna
                LEFT JOIN ref_barang rb5 ON rb5.id = sod.id_barang_5
                LEFT JOIN ref_warna rw5 ON rw5.id = sod.id_warna_5
                LEFT JOIN ref_warna rbw5 ON rbw5.id = rb5.id_warna
                LEFT JOIN ref_barang rb6 ON rb6.id = sod.id_barang_6
                LEFT JOIN ref_warna rw6 ON rw6.id = sod.id_warna_6
                LEFT JOIN ref_warna rbw6 ON rbw6.id = rb6.id_warna
                LEFT JOIN ref_barang rb7 ON rb7.id = sod.id_barang_7
                LEFT JOIN ref_warna rw7 ON rw7.id = sod.id_warna_7
                LEFT JOIN ref_warna rbw7 ON rbw7.id = rb7.id_warna
                LEFT JOIN ref_barang rb8 ON rb8.id = sod.id_barang_8
                LEFT JOIN ref_warna rw8 ON rw8.id = sod.id_warna_8
                LEFT JOIN ref_warna rbw8 ON rbw8.id = rb8.id_warna
                WHERE {$soWhere} AND sou.qty IS NOT NULL AND sou.qty > 0
            )
            UNION ALL
            (
                SELECT 
                    sod.id as id,
                    1 as print_type,
                    0 as is_reject,
                    NULL::integer as id_header,
                    sou.qty,
                    NULL::numeric as berat,
                    1 as tipe,
                    sod.id as ref_detail_id,
                    sou.qty as qty_kirim,
                    so.kode_sample as kode_sales_order,
                    so.id_konsumen,
                    so.style,
                    CONCAT_WS('~', 
                        NULLIF(COALESCE(rw1.kode_warna, rbw1.kode_warna, rb1.nama_barang),''), 
                        NULLIF(COALESCE(rw2.kode_warna, rbw2.kode_warna, rb2.nama_barang),''), 
                        NULLIF(COALESCE(rw3.kode_warna, rbw3.kode_warna, rb3.nama_barang),''), 
                        NULLIF(COALESCE(rw4.kode_warna, rbw4.kode_warna, rb4.nama_barang),''), 
                        NULLIF(COALESCE(rw5.kode_warna, rbw5.kode_warna, rb5.nama_barang),''), 
                        NULLIF(COALESCE(rw6.kode_warna, rbw6.kode_warna, rb6.nama_barang),''), 
                        NULLIF(COALESCE(rw7.kode_warna, rbw7.kode_warna, rb7.nama_barang),''), 
                        NULLIF(COALESCE(rw8.kode_warna, rbw8.kode_warna, rb8.nama_barang),'')
                    ) as color,
                    NULL::numeric as amount,
                    rk.nama as buyer,
                    ru.kode_ukuran,
                    NULL::text as keterangan,
                    COALESCE(ru.key_ukuran, LOWER(REGEXP_REPLACE(ru.kode_ukuran, '[^a-zA-Z0-9]', '', 'g'))) as key_ukuran,
                    {$idProsesInt} as id_proses,
                    so.deskripsi,
                    0 as qty_terima,
                    sou.qty as qty_sisa,
                    0 as harga,
                    so.tgl_transaksi
                FROM trans_sample so
                INNER JOIN ref_konsumen rk ON rk.id = so.id_konsumen
                INNER JOIN trans_sample_det sod ON sod.id_sample = so.id
                INNER JOIN trans_sample_ukuran sou ON sou.id_sample_det = sod.id
                INNER JOIN ref_ukuran ru ON ru.id = sou.id_ukuran
                LEFT JOIN ref_barang rb1 ON rb1.id = sod.id_barang_1
                LEFT JOIN ref_warna rw1 ON rw1.id = sod.id_warna_1
                LEFT JOIN ref_warna rbw1 ON rbw1.id = rb1.id_warna
                LEFT JOIN ref_barang rb2 ON rb2.id = sod.id_barang_2
                LEFT JOIN ref_warna rw2 ON rw2.id = sod.id_warna_2
                LEFT JOIN ref_warna rbw2 ON rbw2.id = rb2.id_warna
                LEFT JOIN ref_barang rb3 ON rb3.id = sod.id_barang_3
                LEFT JOIN ref_warna rw3 ON rw3.id = sod.id_warna_3
                LEFT JOIN ref_warna rbw3 ON rbw3.id = rb3.id_warna
                LEFT JOIN ref_barang rb4 ON rb4.id = sod.id_barang_4
                LEFT JOIN ref_warna rw4 ON rw4.id = sod.id_warna_4
                LEFT JOIN ref_warna rbw4 ON rbw4.id = rb4.id_warna
                LEFT JOIN ref_barang rb5 ON rb5.id = sod.id_barang_5
                LEFT JOIN ref_warna rw5 ON rw5.id = sod.id_warna_5
                LEFT JOIN ref_warna rbw5 ON rbw5.id = rb5.id_warna
                LEFT JOIN ref_barang rb6 ON rb6.id = sod.id_barang_6
                LEFT JOIN ref_warna rw6 ON rw6.id = sod.id_warna_6
                LEFT JOIN ref_warna rbw6 ON rbw6.id = rb6.id_warna
                LEFT JOIN ref_barang rb7 ON rb7.id = sod.id_barang_7
                LEFT JOIN ref_warna rw7 ON rw7.id = sod.id_warna_7
                LEFT JOIN ref_warna rbw7 ON rbw7.id = rb7.id_warna
                LEFT JOIN ref_barang rb8 ON rb8.id = sod.id_barang_8
                LEFT JOIN ref_warna rw8 ON rw8.id = sod.id_warna_8
                LEFT JOIN ref_warna rbw8 ON rbw8.id = rb8.id_warna
                WHERE {$splWhere} AND sou.qty IS NOT NULL AND sou.qty > 0
            )
            ORDER BY tgl_transaksi DESC, kode_sales_order DESC
        ";

        return $this->db->query($sql)->getResult();
    }
    

    /**
     * Mengambil data rekapitulasi transfer vs penerimaan (BTM) per CMT, Proses, SO, Style, Warna, Ukuran
     * untuk monitoring sisa barang yang masih berada di CMT (Outstanding Inventory).
     */
    public function get_export_sisa_cmt($from_date = null, $to_date = null, $id_perusahaan = null)
    {
        $db = $this->db;

        // Subquery T: Agregasi Transfer Barang per SO, Style, Color, Ukuran, CMT, Proses
        $subTrf = $db->table('trans_barang_trf_so_det tbtsd')
            ->join('trans_barang_trf_header tbth', 'tbth.id = tbtsd.id_header', 'inner')
            ->join('trans_sales_order tso', 'tso.kode_sales_order = tbtsd.kode_sales_order', 'left')
            ->join('trans_sample ts', 'ts.kode_sample = tbtsd.kode_sales_order', 'left')
            ->join('ref_operator ro', 'ro.id = tbth.id_cmt', 'left')
            ->join('_jenis_proses_produksi jp', 'jp.id = tbth.id_proses', 'left')
            ->join('ref_konsumen rk', 'rk.id = tbtsd.id_konsumen', 'left')
            ->join('ref_perusahaan rp', 'rp.id = tbth.id_perusahaan', 'left')
            ->select([
                "COALESCE(tbth.id_cmt, 0) AS id_cmt",
                "COALESCE(ro.nama_operator, 'Tanpa CMT / Internal') AS nama_cmt",
                "COALESCE(tbth.id_proses, 0) AS id_proses",
                "COALESCE(jp.nama, '-') AS nama_proses",
                "COALESCE(tbth.id_perusahaan, 1) AS id_perusahaan",
                "COALESCE(rp.nama_perusahaan, CASE WHEN tbth.id_perusahaan = 2 THEN 'Citra Knitt' ELSE 'PT Citra Jaya Kniting' END) AS perusahaan",
                "tbtsd.kode_sales_order",
                "COALESCE(rk.nama, '-') AS nama_konsumen",
                "COALESCE(tbtsd.style, '-') AS style",
                "COALESCE(tbtsd.color, '-') AS color",
                "COALESCE(tbtsd.kode_ukuran, '-') AS kode_ukuran",
                "SUM(tbtsd.qty) AS qty_transfer"
            ])
            ->where('tbth.active', 1);

        // Filter periode tanggal HANYA untuk pemilihan nomor SO / Sample yang tampil
        if (!empty($from_date) && !empty($to_date)) {
            $subTrf->groupStart()
                ->where('tso.tgl_transaksi >=', $from_date)
                ->where('tso.tgl_transaksi <=', $to_date)
            ->groupEnd()
            ->orGroupStart()
                ->where('ts.tgl_transaksi >=', $from_date)
                ->where('ts.tgl_transaksi <=', $to_date)
            ->groupEnd();
        }

        if (!empty($id_perusahaan) && $id_perusahaan !== 'all') {
            if ($id_perusahaan == 1) {
                $subTrf->groupStart()
                    ->where('tbth.id_perusahaan', 1)
                    ->orWhere('tbth.id_perusahaan IS NULL')
                ->groupEnd();
                $subTrf->where("tbth.kode_transaksi NOT LIKE 'TRFC%'");
            } else {
                $subTrf->groupStart()
                    ->where('tbth.id_perusahaan', $id_perusahaan)
                    ->orWhere("tbth.kode_transaksi LIKE 'TRFC%'")
                ->groupEnd();
            }
        }

        $subTrf->groupBy([
            "COALESCE(tbth.id_cmt, 0)",
            "ro.nama_operator",
            "COALESCE(tbth.id_proses, 0)",
            "jp.nama",
            "COALESCE(tbth.id_perusahaan, 1)",
            "rp.nama_perusahaan",
            "tbth.id_perusahaan",
            "tbtsd.kode_sales_order",
            "rk.nama",
            "tbtsd.style",
            "tbtsd.color",
            "tbtsd.kode_ukuran"
        ]);

        $sqlTrf = $subTrf->getCompiledSelect(false);

        // Subquery M: Agregasi Penerimaan (BTM) per SO, Color, Ukuran, CMT, Proses, Perusahaan
        $subBtm = $db->table('trans_barang_masuk_produksi tbmp')
            ->join('trans_barang_header tbh', 'tbh.id = tbmp.id_header', 'inner')
            ->select([
                "COALESCE(tbh.id_cmt, 0) AS id_cmt",
                "COALESCE(tbh.id_proses, 0) AS id_proses",
                "CASE WHEN (tbh.id_perusahaan = 2 OR LEFT(tbh.kode_transaksi, 4) = 'BTMC') THEN 2 ELSE 1 END AS id_perusahaan",
                "tbmp.kode_sales_order",
                "tbmp.color",
                "tbmp.kode_ukuran",
                "SUM(tbmp.qty) AS qty_terima"
            ])
            ->where('tbh.active', 1)
            ->where("LEFT(tbh.kode_transaksi, 3) =", "BTM");

        if (!empty($id_perusahaan) && $id_perusahaan !== 'all') {
            if ($id_perusahaan == 1) {
                $subBtm->groupStart()
                    ->where('tbh.id_perusahaan', 1)
                    ->orWhere('tbh.id_perusahaan IS NULL')
                ->groupEnd();
                $subBtm->where("LEFT(tbh.kode_transaksi, 4) !=", "BTMC");
            } else {
                $subBtm->groupStart()
                    ->where('tbh.id_perusahaan', $id_perusahaan)
                    ->orWhere("LEFT(tbh.kode_transaksi, 4) =", "BTMC")
                ->groupEnd();
            }
        }

        $subBtm->groupBy([
                "COALESCE(tbh.id_cmt, 0)",
                "COALESCE(tbh.id_proses, 0)",
                "CASE WHEN (tbh.id_perusahaan = 2 OR LEFT(tbh.kode_transaksi, 4) = 'BTMC') THEN 2 ELSE 1 END",
                "tbmp.kode_sales_order",
                "tbmp.color",
                "tbmp.kode_ukuran"
            ]);

        $sqlBtm = $subBtm->getCompiledSelect(false);

        // Gabungkan t dan m
        $finalQuery = "
            SELECT 
                t.id_cmt,
                t.nama_cmt,
                t.id_proses,
                t.nama_proses,
                t.id_perusahaan,
                t.perusahaan,
                t.kode_sales_order,
                t.nama_konsumen,
                t.style,
                t.color,
                t.kode_ukuran,
                t.qty_transfer,
                COALESCE(m.qty_terima, 0) AS qty_terima,
                (t.qty_transfer - COALESCE(m.qty_terima, 0)) AS qty_sisa
            FROM ({$sqlTrf}) t
            LEFT JOIN ({$sqlBtm}) m 
                ON m.id_cmt = t.id_cmt
                AND m.id_proses = t.id_proses
                AND m.id_perusahaan = t.id_perusahaan
                AND m.kode_sales_order = t.kode_sales_order
                AND m.color = t.color
                AND m.kode_ukuran = t.kode_ukuran
            ORDER BY 
                t.nama_cmt ASC,
                t.nama_proses ASC,
                t.kode_sales_order ASC,
                t.style ASC,
                t.color ASC,
                t.kode_ukuran ASC
        ";

        return $db->query($finalQuery)->getResult();
    }

}
