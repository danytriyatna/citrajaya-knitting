<?php

namespace Modules\Purchasing\Models;

use Modules\Transaction\Models\IncomingGoodsModel;

class ReceiveItemModel extends \App\Models\PrModel
{

    protected $table = "trans_receive_header";
    protected $tblPoHeader = "trans_po_header";
    protected $tblPoDetail = "trans_po_detail";
    protected $tblDet = "trans_receive_detail";
    protected $tblTrxBarang = "trans_barang";
    protected $tblTrxLots = "trans_lots";
    protected $tblTrxBalances = "trans_barang_balances";
    protected $tblTrxPersediaan = "trans_persediaan";
    protected $tblVendor = "ref_vendor";


    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblPoHeader . " ebx", "uk.id_po = ebx.id", "inner");
        $builder->join($this->tblVendor . " dbx", "ebx.id_vendor = dbx.id", "inner");
        $builder->select("uk.id, uk.id_po,  uk.status, ebx.id_vendor, ebx.po_no, dbx.nama as nama_vendor, ebx.po_date, uk.rec_date, ebx.date_exc, ebx.ship_to, uk.form_no, uk.rec_no, uk.form_no, uk.qty");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.po_no) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.rec_no) LIKE', strtolower("%{$filters[0]['value']}%"));
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
        $builder->select("count(1) as _cnt");
        $builder->join($this->tblPoHeader . " ebx", "uk.id_po = ebx.id", "inner");
        $builder->join($this->tblVendor . " dbx", "ebx.id_vendor = dbx.id", "inner");
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.po_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.rec_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateKode()
    {
        $kd = "REC";
        $builder = $this->db->table($this->table . ' a');
        $builder->select("LEFT(rec_no, 7) AS tgl, RIGHT( rec_no, 4 ) AS kode ");

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

    function getRefTerm()
    {
        $builder = $this->db->table($this->tblTerm);
        $builder->select("*");
        $this->_data = $builder->get()->getResultArray();
        return $this->_data;
    }

    function getRefTax()
    {
        $builder = $this->db->table($this->tblTax);
        $builder->select("*");
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }
    function getDataDetail($idHeader = null)
    {
        $builder = $this->db->table($this->tblDet . " uk");
        $builder->select("uk.id");
        $builder->where("uk.id_header", $idHeader);
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getLastStokBarang($idBarang, $idGudang)
    {
        $builder = $this->db->table("trans_persediaan");
        $builder->select("stok");
        $builder->where('id_barang', $idBarang);
        $builder->where('id_gudang', $idGudang);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }


    function getLastStokPO($idHeader)
    {
        $builder = $this->db->table($this->tblPoHeader);
        $builder->select("qty_payment as stok");
        $builder->where('id', $idHeader);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }
    function getLastStokPODetail($idHeader, $idBarang)
    {
        $builder = $this->db->table($this->tblPoDetail);
        $builder->select("qty_receive as stok");
        $builder->where('id_header', $idHeader);
        $builder->where('id_barang', $idBarang);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function trxInsertUpdateRecord($data, $id, $detail)
    {


        $this->db->transStart();
        try {
            $namaVendor = $data['nama_vendor'];
            unset($data['nama_vendor']);

            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['rec_no'] = $this->generateKode();
                $id = $this->insertRecordGetid($this->table,  $data);
            }

            foreach ($detail as $rowData) {
                if ($rowData['id_barang'] != "") {
                    // $idBarang = decrypt($rowData['id_barang']);
                    $idBarang = $rowData['id_barang'];
                }

                $dataDetail = [
                    "id_barang" => $idBarang,
                    "id_gudang" => !empty($rowData['id_gudang']) ? $rowData['id_gudang'] : null,
                    "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                    "id_header" => $id,
                    "qty" => $rowData['qty'],
                    "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                ];

                $this->insertRecordGetid($this->tblDet, $dataDetail);

                if ($data['status'] == 1) {
                    $arrParam =  [
                        "id_barang" => $idBarang,
                        "id_header" => $data['id_po'],
                    ];
                    $lastStokDet = $this->getLastStokPODetail($data['id_po'], $idBarang);
                    $stokAkhirDet = !empty($lastStokDet) ? $lastStokDet->stok : 0;

                    $this->updateRecords($this->tblPoDetail, array("qty_receive" => $stokAkhirDet + $rowData['qty']), $arrParam);
                    $mBarangMasuk = new IncomingGoodsModel();
                    $arrParam =  [
                        "id_barang" => $idBarang,
                        "id_gudang" => $rowData['id_gudang'],
                    ];
                    $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, $rowData['id_gudang']);
                    $dataLots = [
                        "id_barang" => $idBarang,
                        "id_gudang" => !empty($rowData['id_gudang']) ? $rowData['id_gudang'] : null,
                        "tanggal" => date("Y-m-d H:i:s"),
                        "lot_no" => $rowData['lot_no'],
                        "qty" => $rowData['qty'],
                        "active" => 1,
                        "created_at" =>  date("Y-m-d H:i:s"),
                    ];
                    if (!empty($resLotNo)) {
                        $idLots = $resLotNo->id;
                        $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty + $rowData->qty), array("id" => $idLots));
                    } else {
                        $idLots = $this->insertRecordGetid($this->tblTrxLots, $dataLots);
                    }

                    $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $rowData['id_gudang'], $idLots);

                    // $stokAwal = !empty($resData) ? $resData->stok : 0;
                    $dataBarang = [
                        "id_barang" => $idBarang,
                        "jenis_transaksi" => 1,
                        "jumlah" =>  $rowData['qty'],
                        "tanggal" => date("Y-m-d H:i:s"),
                        "id_gudang_tujuan" =>  !empty($rowData['id_gudang']) ? $rowData['id_gudang'] : null,
                        "nama" => $namaVendor,
                        "id_kategori" => 2,
                        "keterangan" => "Barang Masuk Dari Receive Item Purchase Order",
                        "active" => 1,
                        "tipe" => 1,
                        "created_at" =>  date("Y-m-d H:i:s"),
                        "lot_id" => $idLots,
                        "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                        "kode_transaksi" => $this->generateKode(),
                    ];
                    $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                    $arrStockBalances = [
                        "id_barang" => $idBarang,
                        "id_gudang" => !empty($rowData['id_gudang']) ? $rowData['id_gudang'] : null,
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

                    // $arrPersediaan = [
                    //     "id_barang" => $idBarang,
                    //     "id_gudang" => $rowData['id_gudang'],
                    //     "stok" => $stokAwal + $rowData['qty'],
                    //     "created_at" =>  date("Y-m-d H:i:s"),
                    //     "active" => 1,
                    // ];

                    // if (!empty($resData)) {

                    //     $this->updateRecords($this->tblTrxPersediaan, $arrPersediaan, $arrParam);
                    // } else {
                    //     $this->insertRecordGetid($this->tblTrxPersediaan, $arrPersediaan);
                    // }
                }
            }

            if ($data['status'] == 1) {
                $lastStok = $this->getLastStokPO($data['id_po']);
                $arrParam =  [
                    "id" => $data['id_po'],
                ];
                $stokAkhir = !empty($lastStok) ? $lastStok->stok : 0;
                $this->updateRecords($this->tblPoHeader, array("qty_payment" => $stokAkhir + $data['qty']), $arrParam);
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
}
