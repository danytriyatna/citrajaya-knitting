<?php

namespace Modules\Transaction\Models;

class BarangKeluarModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_header";
    protected $tblDet = "trans_barang_detail";
    protected $kd = "2";
    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblVendor = "ref_vendor";
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
        $builder->join($this->tblVendor . " ebx", "uk.id_vendor = ebx.id", "left");
        $builder->join("trans_walkorder tw", "uk.no_ref_wo = tw.kode_walkorder", "left");
        $builder->select("uk.no_ref_trf, uk.no_ref_wo, uk.id, uk.id_buyer,uk.status, uk.id_kategori, uk.keterangan, 
                        abx.nama_gudang,  uk.tanggal, ebx.nama , uk.kode_transaksi, dbx.kategori, uk.id_gudang, tw.ref_kode as kode_so");

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
        $builder->join($this->tblVendor . " ebx", "uk.id_vendor = ebx.id", "left");
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

    function getDataBarang($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->tblTrxLots . " uk");
        $builder->join($this->tblBarang . " abx", "uk.id_barang = abx.id", "left");
        $builder->join($this->tblSatuan . " dbx", "abx.id_satuan = dbx.id", "inner");
        $builder->select("uk.id_barang, dbx.nama_satuan, abx.nama_barang, abx.kode_barang, uk.id as lot_id, uk.lot_no,uk.qty");
        if (!empty($params['id_gudang'])) {
            $builder->where('uk.id_gudang', $params['id_gudang']);
        }
        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(abx.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.lot_no) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('uk.id');
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

    function getDataBarangCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->tblTrxLots . " uk");
        $builder->join($this->tblBarang . " abx", "uk.id_barang = abx.id", "left");
        $builder->join($this->tblSatuan . " dbx", "abx.id_satuan = dbx.id", "inner");
        $builder->select("count(1) as _cnt");
        $builder->where('uk.active = 1');
        if (!empty($params['id_gudang'])) {
            $builder->where('uk.id_gudang', $params['id_gudang']);
        }
        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(abx.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.lot_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }


    function generateKodePersediaan($type = null)
    {
        $kd = "BTK";
        $builder = $this->db->table($this->table . ' a');
        if (!empty($type)) {
            $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 5 ) AS kode ");
        }
        else {
            $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");
        }
        $builder->where("LEFT(kode_transaksi, 3) = '$kd'");
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

    function trxInsertUpdateRecord($data, $id, $detail)
    {
        $this->db->transStart();
        try {
            $nama = $data['nama'];
            unset($data['nama']);

            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                $getCurrent = $this->getData($id);
                $data['kode_transaksi'] = $getCurrent->kode_transaksi;
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan("BTK");
                $id = $this->insertRecordGetid($this->table,  $data);
            }

            foreach ($detail as $rowData) {
                if ($rowData['id_barang'] != "") {
                    // $idBarang = decrypt($rowData['id_barang']);
                    $idBarang = $rowData['id_barang'];
                }

                $dataDetail = [
                    "id_barang" => $idBarang,
                    "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                    "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
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

                    $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, null, $data['id_gudang']);
                    // $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, $data['id_gudang']);
                    if (!empty($resLotNo)) {
                        $idLots = $resLotNo->id;
                        $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty - $rowData['qty']), array("id" => $idLots));
                    }

                    $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang'], $idLots, !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);

                    // $stokAwal = !empty($resData) ? $resData->stok : 0;
                    $dataBarang = [
                        "id_barang" => $idBarang,
                        "jenis_transaksi" => 2,
                        "jumlah" =>  $rowData['qty'],
                        "tanggal" => date("Y-m-d H:i:s"),
                        "id_gudang_asal" =>  !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                        "nama" => $nama,
                        "id_kategori" => $data['id_kategori'],
                        "keterangan" => "Barang Keluar Dari Outgoing Goods",
                        "active" => 1,
                        "tipe" => 1,
                        "created_at" =>  date("Y-m-d H:i:s"),
                        "lot_id" => $idLots,
                        "lot_no" => $rowData['lot_no'],
                        "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
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
        $builder = $this->db->table("trans_barang_detail tbd");
        $builder->join("trans_barang_header tbh", "tbh.id = CAST(tbd.id_header AS INTEGER)", "inner");
        $builder->join("ref_kategori_persediaan rkp", "rkp.id = tbh.id_kategori", "inner");
        $builder->join("ref_gudang rg", "rg.id = tbh.id_gudang", "inner");
        $builder->join("ref_barang rb", "rb.id = tbd.id_barang", "inner");
        $builder->join("ref_satuan rs", "rs.id = rb.id_satuan", "inner");

        $builder->select("tbh.kode_transaksi, tbh.tanggal, rkp.kategori, rg.nama_gudang, 
                            tbh.no_ref_trf, tbh.no_ref_wo, rb.kode_barang, rb.nama_barang, 
                            tbd.lot_no, tbd.qty, rs.nama_satuan, tbd.price");

        $builder->where('tbh.active', 1);
        $builder->where('tbh.jenis_transaksi', 2);
        $builder->where('tbh.id_kategori', 5);

        $builder->where("tbh.tanggal BETWEEN'$from_date' AND '$to_date'");
        $builder->orderBy("tbh.tanggal", "asc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
}
