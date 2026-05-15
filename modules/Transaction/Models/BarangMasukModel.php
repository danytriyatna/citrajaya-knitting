<?php

namespace Modules\Transaction\Models;

class BarangMasukModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_header";
    protected $tblDet = "trans_barang_detail";
    protected $kd = "1";
    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblBuyer = "ref_konsumen";
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
        $builder->join($this->tblGudang . " abx", "uk.id_gudang = abx.id", "left");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblBuyer . " ebx", "uk.id_buyer = ebx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = uk.id_cmt', 'left');

        $builder->select("uk.no_ref_trf,uk.id, uk.id_buyer,uk.status, uk.id_kategori, uk.keterangan, abx.nama_gudang,  uk.tanggal, ebx.nama,
                          uk.kode_transaksi, dbx.kategori, uk.id_gudang, uk.nilai_mesin, uk.nomor_mesin, uk.jam_mesin, uk.id_cmt, uk.id_proses,
                          jp.nama as proses, rp.nama_operator, rp.alamat as alamat_cmt, uk.id_perusahaan");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            $builder->where('uk.jenis_transaksi', $this->kd);
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.no_ref_trf) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rp.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(jp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(dbx.kategori) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->groupStart();
                        $builder->where('uk.id_perusahaan', 1);
                        $builder->orWhere('uk.id_perusahaan IS NULL');
                    $builder->groupEnd();
                } else {
                    $builder->where('uk.id_perusahaan', $params['id_perusahaan']);
                }
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('uk.id', 'DESC');
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
        $builder->join($this->tblGudang . " abx", "uk.id_gudang = abx.id", "left");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblBuyer . " ebx", "uk.id_buyer = ebx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = uk.id_cmt', 'left');
        $builder->select("count(1) as _cnt");
        $builder->where('uk.jenis_transaksi', $this->kd);
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.no_ref_trf) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rp.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(jp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(dbx.kategori) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->groupStart();
                        $builder->where('uk.id_perusahaan', 1);
                        $builder->orWhere('uk.id_perusahaan IS NULL');
                    $builder->groupEnd();
                } else {
                    $builder->where('uk.id_perusahaan', $params['id_perusahaan']);
                }
            }
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function get_export($from_date = null, $to_date = null, $id_perusahaan = null){
        $builder = $this->db->table("trans_barang_masuk_produksi tbmp");
        $builder->join($this->table . " uk", "tbmp.id_header = uk.id", "inner");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang = abx.id", "left");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblBuyer . " ebx", "tbmp.id_konsumen = ebx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = uk.id_cmt', 'left');

        $builder->select("uk.tanggal, tbmp.tgl_transaksi, uk.kode_transaksi, dbx.kategori as jenis_transaksi, uk.no_ref_trf, tbmp.kode_sales_order, ebx.nama as nama_buyer, tbmp.style, tbmp.deskripsi, abx.nama_gudang as gudang_pengirim, 
                        jp.nama as nama_proses, rp.nama_operator as nama_cmt, tbmp.color, tbmp.kode_ukuran, tbmp.keterangan, 
                        tbmp.nomor_mesin, tbmp.jam_mesin, tbmp.nilai_mesin, tbmp.qty_kirim, tbmp.qty, tbmp.harga, tbmp.amount, tbmp.tgl_scan, tbmp.print_type, uk.status");
        $builder->where('uk.active = 1');
        $builder->where("tbmp.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
        $builder->orderBy("tbmp.tgl_transaksi", "desc");
        
        if (!empty($id_perusahaan)) {
            if ($id_perusahaan == 1) {
                $builder->groupStart();
                    $builder->where('uk.id_perusahaan', 1);
                    $builder->orWhere('uk.id_perusahaan IS NULL');
                $builder->groupEnd();
            } else {
                $builder->where('uk.id_perusahaan', $id_perusahaan);
            }
        }

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function generateKodePersediaan($type = null, $id_perusahaan = null)
    {
        $kd = "BTM";
        $builder = $this->db->table($this->table . ' a');
        if (!empty($id_perusahaan) && $id_perusahaan != 1) {
            $kd = "BTMC";
            if (!empty($type)) {
                $builder->select("LEFT(kode_transaksi, 8) AS tgl, RIGHT( kode_transaksi, 5 ) AS kode ");
            }
            else {
                $builder->select("LEFT(kode_transaksi, 8) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");
            }
            $builder->where("LEFT(kode_transaksi, 4) = '$kd'");
    
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
        }
        else {
            if (!empty($type)) {
                $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 5 ) AS kode ");
            }
            else {
                $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");
            }
            $builder->where("LEFT(kode_transaksi, 3) = '$kd'");
            $builder->where("LEFT(kode_transaksi, 4) != 'BTMC'");
    
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
        }
        dd($kodejadi);
        // hasilnya SOD24100001 dst.
        return $kodejadi;
    }

    function trxInsertUpdateRecord($data, $id, $detail, $dataProduksi)
    {
        $this->db->transStart();
        try {
            $nama = $data['nama'];
            $tgl_trans = $data['tanggal'];
            unset($data['nama']);

            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                // $this->deleteRecordMultipleColumn('trans_barang_masuk_produksi', $arrDelete);
                $getCurrent = $this->getData($id);
                $data['kode_transaksi'] = $getCurrent->kode_transaksi;
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan("BTM", $data['id_perusahaan']);
                $id = $this->insertRecordGetid($this->table,  $data);
            }

            // print_r($dataProduksi);exit;
            if(!empty($detail)){
                foreach ($detail as $rowData) {
                    if ($rowData['id_barang'] != "") {
                        $idBarang = decrypt($rowData['id_barang']);
                        // $idBarang = $rowData['id_barang'];
                    }
    
                    $dataDetail = [
                        "id_barang" => $idBarang,
                        "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                        "lot_id" => !empty($rowData['lot_id']) ? $rowData['lot_id'] : 0,
                        "id_header" => $id,
                        "qty" => $rowData['qty'],
                        "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                    ];
    
                    $this->insertRecordGetid($this->tblDet, $dataDetail);
    
                    if ($data['status'] == 1) {
    
                        $mBarangMasuk = new IncomingGoodsModel();
                        $arrParam =  [
                            "id_barang" => $idBarang,
                            "id_gudang" => $data['id_gudang'],
                        ];
                        $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, $data['id_gudang']);
                        $dataLots = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_no" => $rowData['lot_no'],
                            "qty" => $rowData['qty'],
                            "active" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                        ];
                        if (!empty($resLotNo)) {
                            $idLots = $resLotNo->id;
                            $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty + $rowData['qty']), array("id" => $idLots));
                        } else {
                            $idLots = $this->insertRecordGetid($this->tblTrxLots, $dataLots);
                        }
    
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang'], $idLots);
    
                        // $stokAwal = !empty($resData) ? $resData->stok : 0;
                        $dataBarang = [
                            "id_barang" => $idBarang,
                            "jenis_transaksi" => 1,
                            "jumlah" =>  $rowData['qty'],
                            "tanggal" => date("Y-m-d H:i:s"),
                            "id_gudang_tujuan" =>  !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "nama" => $nama,
                            "id_kategori" => $data['id_kategori'],
                            "keterangan" => "Barang Masuk Dari Incoming Goods",
                            "active" => 1,
                            "tipe" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "lot_no" => $rowData['lot_no'],
                            "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                            "kode_transaksi" => $data['kode_transaksi'],
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "lot_no" => $rowData['lot_no'],
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
                }
            }
            // print_r($data);exit;
            if($data['id_kategori'] == 12 || $data['id_kategori'] == 1){
                if(!empty($dataProduksi)){

                    $hedr_data = $this->getData($id);

                    $id_proses = $data['id_proses'];
                    $id_cmt = $data['id_cmt'];
                    $kode_transaksi = $hedr_data->kode_transaksi;
                    $i = 0;

                    $idMP = [];
                    foreach ($dataProduksi as $xrow) {
                        // print_r($xrow);exit;
                        $xpr = [];
                        $xpr['kode_sales_order'] = $xrow['kode_sales_order'];
                        $xpr['kode_ukuran'] = $xrow['kode_ukuran'];
                        $clr = explode('~', $xrow['color']);
                        $xpr['kode_warna1'] = $clr[0];
                        if(!empty($clr[1])){
                            $xpr['kode_warna2'] = $clr[1];
                        }

                        $tipe = substr($xpr['kode_sales_order'], 0, 3) === 'SPL' ? 1 : (substr($xpr['kode_sales_order'], 0, 3) === 'SOD' ? 2 : null);
                        // print_r($tipe);exit;
                        $dtSo = $this->getDataSO($xpr, $tipe);
                        
                        $idSo = !empty($dtSo) ? $dtSo->id_sales_order : 0;

                        $rtgl = !empty($xrow['tgl_transaksi']) ? $xrow['tgl_transaksi'] : null;

                        $tgl_scan = !empty($xrow['tgl_scan']) ? $xrow['tgl_scan'] : null;

                        $pattern = '/^(\d{4} \d{2} \d{2} \d{2}-\d{2}-\d{2}-\d{6})\((\d+)\)$/';

                        $hanya_tanggal = null;
                        $isi_kurung = null;

                        if (!empty($tgl_scan)) {
                            if (preg_match($pattern, trim($tgl_scan), $matches)) {
                                $hanya_tanggal = $matches[1];
                                $isi_kurung    = $matches[2];
                            }
                        }

                        // print_r($xrow);exit;
                        if ($xrow['qty_kirim'] < $xrow['qty'] && $data['id_kategori'] != 1 && $isi_kurung != 2) {
                            throw new \Exception("QTY Terima Melebihi QTY yang Tersedia!");
                        }


                        $dataDetail = [
                            "id_ref" => !empty($idSo) ? $idSo : null,
                            "id_header" => $id,
                            "color" => $xrow['color'],
                            "tgl_scan" => !empty($hanya_tanggal) ? $hanya_tanggal : null,
                            "print_type" => !empty($isi_kurung) ? $isi_kurung : null,
                            "deskripsi" => $xrow['deskripsi'],
                            "style" => !empty($xrow['style']) ? $xrow['style'] : null,
                            "qty" => !empty($xrow['qty']) ? $xrow['qty'] : null,
                            "berat" => !empty($xrow['berat']) ? $xrow['berat'] : null,
                            "qty_kirim" => !empty($xrow['qty_kirim']) ? $xrow['qty_kirim'] : null,
                            "id_konsumen" => $xrow['id_konsumen'],
                            "kode_sales_order" => $xrow['kode_sales_order'],
                            "kode_ukuran" => $xrow['kode_ukuran'],
                            "amount" => !empty($xrow['amount']) ? $xrow['amount'] : 0,
                            "harga" => !empty($xrow['harga']) ? $xrow['harga'] : 0,
                             "tgl_transaksi" => $rtgl,

                            "nomor_mesin" => !empty($data['nomor_mesin']) ? $data['nomor_mesin'] : '-',
                           
                            // "tgl_transaksi" => !empty($data['tanggal']) ? $data['tanggal'] : null,
                            "jam_mesin" => !empty($data['jam_mesin']) ? $data['jam_mesin'] : 0,
                            "nilai_mesin" => !empty($data['nilai_mesin']) ? $data['nilai_mesin'] : 0,
                        ];

                        if (!empty($xrow['id_mp'])) {
                            $arrParamMP =  [
                                "id_mp" => $xrow['id_mp'],
                            ];

                            $qtyNew = !empty($xrow['qty']) ? $xrow['qty'] : 0;

                            $getDataDetSo = $this->getDataDetSO(null, $arrParamMP);

                            $arrParamMPOther =  [
                                'id_proses' => $id_proses, 
                                'id_cmt' => $id_cmt, 
                                'id_ref' => $idSo, 
                                'color' => $xrow['color'], 
                                'kode_ukuran' => $xrow['kode_ukuran'],
                                'id_konsumen' => $xrow['id_konsumen'],
                                'current_id_mp' => $xrow['id_mp'],
                            ];
                            $getDataDetSoOther = $this->getDataDetSO(null, $arrParamMPOther);
                            if ($getDataDetSo->qty != $qtyNew) {
                                
                                foreach ($getDataDetSoOther as $key_so => $value_so) {
                                    if ($getDataDetSo->qty < $qtyNew) {
                                        $newQTYUpdate = $value_so->qty_kirim - ($qtyNew - $getDataDetSo->qty);
                                    }
                                    else {
                                        $newQTYUpdate = $value_so->qty_kirim + ($getDataDetSo->qty - $qtyNew);
                                        // if ($value_so->id_mp == 71258) {
                                        //     # code...
                                        //     dd($value_so->qty_kirim, $getDataDetSo->qty, $qtyNew, $value_so->id_mp, $newQTYUpdate);
                                        // }
                                    }
                                    $this->updateRecord('trans_barang_masuk_produksi', ['qty_kirim' => $newQTYUpdate], 'id', $value_so->id_mp);
                                }
                            }


                            $this->updateRecord('trans_barang_masuk_produksi', $dataDetail, 'id', $xrow['id_mp']);
                            // $getDataDetSo = $this->getDataDetSO(null, ['id_proses' => $id_proses, 'id_cmt' => $id_cmt, 'id_ref' => $idSo, 'color' => $xrow['color'], 'kode_ukuran' => $xrow['kode_ukuran']]);
                            $idMP[] = $xrow['id_mp'];
                        }
                        else {
                            $arrParamMPOther =  [
                                'id_proses' => $id_proses, 
                                'id_cmt' => $id_cmt, 
                                'id_ref' => $idSo, 
                                'color' => $xrow['color'], 
                                'kode_ukuran' => $xrow['kode_ukuran'],
                                'id_konsumen' => $xrow['id_konsumen'],
                            ];
                            
                            $qtyMinus = !empty($xrow['qty']) ? $xrow['qty'] : 0;

                            $getDataDetSo = $this->getDataDetSO(null, $arrParamMPOther);
                            foreach ($getDataDetSo as $key_so => $value_so) {
                                $newQty = $value_so->qty_kirim - $qtyMinus;
                                $this->updateRecord('trans_barang_masuk_produksi', ['qty_kirim' => $newQty], 'id', $value_so->id_mp);
                            }

                            $idMP[] = $this->insertRecordGetid('trans_barang_masuk_produksi', $dataDetail);
                            
                        }
        

                        // $getDataDetSo = $this->getDataDetSO(null, ['id_proses' => $id_proses, 'id_cmt' => $id_cmt, 'id_ref' => $idSo, 'color' => $xrow['color'], 'kode_ukuran' => $xrow['kode_ukuran']]);

                        if($data['status'] == 1){
                            $xp['id_proses'] = $id_proses;
                            $xp['kode_ukuran'] = $xrow['kode_ukuran'];
                            $xp['ref_id'] = $idSo;
                            $dtProses = $this->getDataWP($xp);
                            if(!empty($dtProses)){
                                $idProduksi = $this->getDataProduksiByIdWalkorder($dtProses->id_walkorder)->id;
                                // print_r("============================================="); print_r("<br>");
                                
                                // print_r($xpr);
                                // print_r("<br>");
                                // print_r("------------------------------------------------");   print_r("<br>");
                                // print_r($xrow);
                                // print_r("<br>");
                                // print_r("------------------------------------------------");   print_r("<br>");
                                // print_r($dtProses);
                                // print_r("<br>");
                                // print_r("------------------------------------------------");   print_r("<br>");
                                // print_r($idProduksi);
                                // print_r("<br>");

                                $amount = !empty($xrow['amount']) ? $xrow['amount'] : 0;
                                $qty = !empty($xrow['qty']) ? $xrow['qty'] : 0;
                                $berat = !empty($xrow['berat']) ? $xrow['berat'] : 0;
                                $harga = !empty($xrow['harga']) ? $xrow['harga'] : 0;
                              
                                // $harga = 0;
                                // if($qty > 0){
                                //     $harga = $amount / $qty;
                                //     $harga = round($harga, 0);
                                // }
                                // insert data produksi
                                $arrDataUkuran = [
                                    "kode_transaksi" => $kode_transaksi,
                                    "id_produksi" => $idProduksi,
                                    "id_walkorder_proses_ukuran" => $dtProses->key_kedua,
                                    "id_proses" => $id_proses,
                                    "id_ukuran" => $dtProses->id_ukuran,
                                    "id_warna" => $dtSo->id_warna_1,
                                    "id_operator" => $id_cmt,
                                    "tgl_transaksi" => $rtgl,//date('Y-m-d'),
                                    "qty" =>  $qty,
                                    "berat" =>  $berat,
                                    "harga" => $harga,
                                    "harga_total" => $amount,
                                    "ref_detail_id" => $dtSo->id,
                                    "nomor_mesin" => !empty($data['nomor_mesin']) ? $data['nomor_mesin'] : '-',
                                    "active" => 1,
                                    "flag" => 1,
                                    "id_category" => $data['id_kategori'],
                                    "created_at" =>  date("Y-m-d H:i:s"),
                                    "print_type" => !empty($isi_kurung) ? $isi_kurung : null,
                
                                ];
                                
                                $this->insertRecordGetid("trans_produksi_operator", $arrDataUkuran);

                                // update qty

                                $id_wop = $dtProses->key_kebenearan;
                                $qty_prod = $dtProses->qty_prod;

                                $qty_now = (int) $xrow['qty'] + (int) $qty_prod;

                                $upd['qty_prod'] = $qty_now;

                                if ($data['id_kategori'] == 12) {
                                    # code...
                                    $this->updateRecord('trans_walkorder_proses_ukuran', $upd, 'id', $id_wop);
                                }

                            }else{
                                throw new \Exception("Ada salah satu data SO Belum sampai proses Produksi " . $xrow['kode_sales_order'] . ', pastikan data sudah sampai proses produksi');
                            }

                            $i++;
                        }
                    }

                    $getDataDetSo = $this->getDataDetSO($id, null, true, $idMP);

                    foreach ($getDataDetSo as $keyDel => $valueDel) {
                        $arrParamMPOther =  [
                            'id_proses' => $id_proses, 
                            'id_cmt' => $id_cmt, 
                            'id_ref' => $valueDel->id_ref, 
                            'color' => $valueDel->color, 
                            'kode_ukuran' => $valueDel->kode_ukuran,
                            'id_konsumen' => $valueDel->id_konsumen,
                            'current_id_mp' => $valueDel->id_mp,
                        ];

                        $qtyMinus = !empty($valueDel->qty) ? $valueDel->qty : 0;

                        $getDataDetSoOther = $this->getDataDetSO(null, $arrParamMPOther);
                        foreach ($getDataDetSoOther as $key_so => $value_so) {
                            $newQty = $value_so->qty_kirim + $qtyMinus;
                            $this->updateRecord('trans_barang_masuk_produksi', ['qty_kirim' => $newQty], 'id', $value_so->id_mp);
                        }
                    }
                    $this->deleteRecordCondition('trans_barang_masuk_produksi', 'id', $idMP, 'id_header', $id);
                    // exit;
                }else{
                    throw new \Exception("Data Produksi tidak ada");
                }
            }

            if ($this->db->transStatus() === TRUE) {
                $this->db->transComplete();
                return [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                ];
            } else {
                throw new \Exception("Transaction failed");
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            // throw $e;
            return [
                'status' => false,
                // 'message' => $e->getMessage(),
                'message' => $e->getMessage() . " (di baris " . $e->getLine() . " file " . $e->getFile() . ")",
            ];
        }
    }

    function getDataSO($params, $tipe)
    {
        // $builder = $this->db->table('trans_sales_order');
        // $builder->select("id");
        // $builder->where("kode_sales_order", $kodeSalesOrder);
        $prms = '';
       
        if(!empty($params['kode_warna1'])){
            $prms .= " AND rw1.kode_warna = '" . $params['kode_warna1'] . "'";
        }

        if(!empty($params['kode_warna2'])){
            $prms .= " AND rw2.kode_warna = '" . $params['kode_warna2'] . "'";
        }

        if(!empty($params['kode_ukuran'])){
            $prms .= " AND rk.kode_ukuran = '" . $params['kode_ukuran'] . "'";
        }

        if ($tipe == 1) {

            if(!empty($params['kode_sales_order'])){
                $prms .= " AND so.kode_sample = '" . $params['kode_sales_order'] . "'";
            }

            $builder = $this->db->table('trans_sample_ukuran ou');
            $builder->select("
                ou.id,
                ou.id_sample as id_sales_order,
                ou.id_sample_det as id_sales_order_det,
                sod.id_warna_1,
                sod.id_warna_2,

                -- Flag sumber warna
                CASE 
                    WHEN sod.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                rw1.keterangan as color1,
                rw2.keterangan as color2
            ");

            $builder->join('trans_sample_det sod', 'sod.id = ou.id_sample_det', 'inner');
            $builder->join('trans_sample so', 'so.id = sod.id_sample', 'inner');
            $builder->join('ref_ukuran rk', 'rk.id = ou.id_ukuran', 'inner');

            // JOIN ref_barang
            $builder->join('ref_barang b1', 'b1.id = sod.id_barang_1', 'left');
            $builder->join('ref_barang b2', 'b2.id = sod.id_barang_2', 'left');

            // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di sod
            // rw1 diubah dari inner ke left agar tidak hilang jika warna via ref_barang
            $builder->join('ref_warna rw1', 'rw1.id = COALESCE(b1.id_warna, sod.id_warna_1)', 'left');
            $builder->join('ref_warna rw2', 'rw2.id = COALESCE(b2.id_warna, sod.id_warna_2)', 'left');

            $builder->where('1 = 1' . $prms);
        } elseif ($tipe == 2) {
            if(!empty($params['kode_sales_order'])){
                $prms .= " AND so.kode_sales_order = '" . $params['kode_sales_order'] . "'";
            }


            $builder = $this->db->table('trans_sales_order_ukuran ou');
            $builder->select("
                ou.id,
                ou.id_sales_order,
                ou.id_sales_order_det,
                sod.id_warna_1,
                sod.id_warna_2,

                -- Flag sumber warna
                CASE 
                    WHEN sod.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                rw1.keterangan as color1,
                rw2.keterangan as color2
            ");

            $builder->join('trans_sales_order_det sod', 'sod.id = ou.id_sales_order_det', 'inner');
            $builder->join('trans_sales_order so',      'so.id = sod.id_sales_order',     'inner');
            $builder->join('ref_ukuran rk',             'rk.id = ou.id_ukuran',           'inner');

            // JOIN ref_barang
            $builder->join('ref_barang b1', 'b1.id = sod.id_barang_1', 'left');
            $builder->join('ref_barang b2', 'b2.id = sod.id_barang_2', 'left');

            // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di sod
            // rw1 diubah dari inner ke left agar tidak hilang jika warna via ref_barang
            $builder->join('ref_warna rw1', 'rw1.id = COALESCE(b1.id_warna, sod.id_warna_1)', 'left');
            $builder->join('ref_warna rw2', 'rw2.id = COALESCE(b2.id_warna, sod.id_warna_2)', 'left');

            $builder->where('1 = 1' . $prms);
        }

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataWP($params)
    {
        $prms = '';
        if(!empty($params['id_proses'])){
            $prms .= " AND tp.id_proses = " . $params['id_proses'];
        }

        if(!empty($params['kode_ukuran'])){
            $prms .= " AND rk.kode_ukuran = '" . $params['kode_ukuran'] . "'";
        }

        if(!empty($params['ref_id'])){
            $prms .= " AND tw.ref_id = " . $params['ref_id'];
        }

        if(!empty($params['kode_sales_order'])){
            $prms .= " AND tw.ref_kode = " . $params['kode_sales_order'];
        }

        $builder = $this->db->table('trans_walkorder_proses_ukuran tpx');
        $builder->select("
            tpx.id as key_kebenearan,
            tp.id as key_kedua,
            tw.id as id_walkorder,
            tp.id_proses,
            tpx.id_walkorder_proses,
            tpx.id_ukuran,
            rk.kode_ukuran,
            tpx.qty,
            tpx.qty_prod
        ");
        $builder->join('trans_walkorder_proses tp', 'tp.id = tpx.id_walkorder_proses', 'inner');
        $builder->join('trans_walkorder tw', 'tw.id = tp.id_walkorder', 'inner');
        $builder->join('ref_ukuran rk', 'rk.id = tpx.id_ukuran', 'inner');
        $builder->where('1 = 1 '. $prms);
        
        $builder->orderBy('tpx.id', 'desc');

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataProduksiByIdWalkorder($idWalkorder)
    {
        $builder = $this->db->table('trans_produksi tp');
        $builder->select("
            tp.id,
            tp.id_walkorder
        ");

        $builder->where('tp.id_walkorder', $idWalkorder);
        $builder->orderBy('tp.id', 'desc');

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataDetSO($idHeader = null, $params = null, $condition = null, $idMP = null)
    {
        $builder = $this->db->table("trans_barang_masuk_produksi abx");

        $builder->select("abx.id as id_mp,abx.qty, abx.kode_sales_order, abx.id_konsumen, abx.style, abx.kode_sales_order, abx.deskripsi, 
                          abx.color,abx.amount, abx.tgl_scan, abx.print_type, cbx.nama as buyer, abx.kode_ukuran, abx.keterangan, abx.id_ref, abx.qty_kirim,
                          abx.nomor_mesin, abx, abx.tgl_transaksi, abx.jam_mesin, abx.nilai_mesin, abx.harga,
                          (abx.qty - abx.qty_kirim) as qty_sisa, abx.berat");

        $builder->join("ref_konsumen cbx", "abx.id_konsumen = cbx.id", "inner");
        $builder->join("trans_barang_header head", "abx.id_header = head.id", "inner");

        if (!empty($idHeader)) {
            $builder->where("abx.id_header", $idHeader);
            if ($condition) {
                $builder->whereNotIn("abx.id", $idMP);
            }
        }

        if (!empty($params['id_mp'])) {
            $builder->where("abx.id", $params['id_mp']);
            $this->_data = $builder->get()->getRow();
            return $this->_data;
        }

        else {
            if (!empty($params['id_proses'])) {
                $builder->where("head.id_proses", $params['id_proses']);
            }
            if (!empty($params['id_cmt'])) {
                $builder->where("head.id_cmt", $params['id_cmt']);
            }
            if (!empty($params['id_ref'])) {
                $builder->where("abx.id_ref", $params['id_ref']);
            }
            if (!empty($params['color'])) {
                $builder->where("abx.color", $params['color']);
            }
            if (!empty($params['kode_ukuran'])) {
                $builder->where("abx.kode_ukuran", $params['kode_ukuran']);
            }
            if (!empty($params['id_konsumen'])) {
                $builder->where("abx.id_konsumen", $params['id_konsumen']);
            }
            if (!empty($params['current_id_mp'])) {
            
                if (is_array($params['current_id_mp'])) {
                    $builder->whereNotIn("abx.id", $params['current_id_mp']);
                }
                else {
                    $builder->where("abx.id <>", $params['current_id_mp']);
                }
            }
        }

        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }
}
