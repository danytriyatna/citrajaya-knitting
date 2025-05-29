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
                          jp.nama as proses, rp.nama_operator, rp.alamat as alamat_cmt");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            $builder->where('uk.jenis_transaksi', $this->kd);
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
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
        $builder->select("count(1) as _cnt");
        $builder->where('uk.jenis_transaksi', $this->kd);
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateKodePersediaan()
    {
        $kd = "BTM";
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
                $this->deleteRecordMultipleColumn('trans_barang_masuk_produksi', $arrDelete);

                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan();
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
                            "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                            "kode_transaksi" => $this->generateKodePersediaan(),
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
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
            if($data['id_kategori'] == 12){
                if(!empty($dataProduksi)){

                    $hedr_data = $this->getData($id);

                    $id_proses = $data['id_proses'];
                    $id_cmt = $data['id_cmt'];
                    $kode_transaksi = $hedr_data->kode_transaksi;
                    $i = 0;
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

                        $dataDetail = [
                            "id_ref" => !empty($idSo) ? $idSo : null,
                            "id_header" => $id,
                            "color" => $xrow['color'],
                            "deskripsi" => $xrow['deskripsi'],
                            "style" => !empty($xrow['style']) ? $xrow['style'] : null,
                            "qty" => !empty($xrow['qty']) ? $xrow['qty'] : null,
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
        
                        $this->insertRecordGetid('trans_barang_masuk_produksi', $dataDetail);

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
                                    "harga" => $harga,
                                    "harga_total" => $amount,
                                    "ref_detail_id" => $dtSo->id,
                                    "nomor_mesin" => !empty($data['nomor_mesin']) ? $data['nomor_mesin'] : '-',
                                    "active" => 1,
                                    "flag" => 1,
                                    "created_at" =>  date("Y-m-d H:i:s"),
                
                                ];
                                
                                $this->insertRecordGetid("trans_produksi_operator", $arrDataUkuran);

                                // update qty

                                $id_wop = $dtProses->key_kebenearan;
                                $qty_prod = $dtProses->qty_prod;

                                $qty_now = (int) $xrow['qty'] + (int) $qty_prod;

                                $upd['qty_prod'] = $qty_now;

                                $this->updateRecord('trans_walkorder_proses_ukuran', $upd, 'id', $id_wop);
                            }else{
                                throw new \Exception("Ada salah satu data SO Belum sampai proses Produksi " . $xrow['kode_sales_order'] . ', pastikan data sudah sampai proses produksi');
                            }

                            $i++;
                        }
                    }
                    // exit;
                }else{
                    throw new \Exception("Data Produksi tidak ada");
                }
            }


            $this->db->transComplete();

            if ($this->db->transStatus() === TRUE) {
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
                'message' => $e->getMessage(),
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
            rw1.keterangan as color1,
            rw2.keterangan as color2
            ");
            $builder->join('trans_sample_det sod', 'sod.id = ou.id_sample_det', 'inner');
            $builder->join('trans_sample so', 'so.id = sod.id_sample', 'inner');
            $builder->join('ref_warna rw1', 'rw1.id = sod.id_warna_1', 'inner');
            $builder->join('ref_warna rw2', 'rw2.id = sod.id_warna_2', 'left');
            $builder->join('ref_ukuran rk', 'rk.id = ou.id_ukuran', 'inner');
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
            rw1.keterangan as color1,
            rw2.keterangan as color2
            ");
            $builder->join('trans_sales_order_det sod', 'sod.id = ou.id_sales_order_det', 'inner');
            $builder->join('trans_sales_order so', 'so.id = sod.id_sales_order', 'inner');
            $builder->join('ref_warna rw1', 'rw1.id = sod.id_warna_1', 'inner');
            $builder->join('ref_warna rw2', 'rw2.id = sod.id_warna_2', 'left');
            $builder->join('ref_ukuran rk', 'rk.id = ou.id_ukuran', 'inner');
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

    function getDataDetSO($idHeader = null)
    {
        $builder = $this->db->table("trans_barang_masuk_produksi abx");

        $builder->select("abx.qty, abx.kode_sales_order, abx.id_konsumen, abx.style, abx.kode_sales_order, abx.deskripsi, 
                          abx.color,abx.amount,cbx.nama as buyer, abx.kode_ukuran, abx.keterangan, abx.id_ref, abx.qty_kirim,
                          abx.nomor_mesin, abx, abx.tgl_transaksi, abx.jam_mesin, abx.nilai_mesin, abx.harga,
                          (abx.qty - abx.qty_kirim) as qty_sisa");

        $builder->join("ref_konsumen cbx", "abx.id_konsumen = cbx.id", "inner");

        $builder->where("abx.id_header", $idHeader);
        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }
}
