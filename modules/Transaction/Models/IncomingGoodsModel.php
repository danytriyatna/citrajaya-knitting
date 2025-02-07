<?php

namespace Modules\Transaction\Models;

class IncomingGoodsModel extends \App\Models\PrModel
{

    protected $table = "trans_barang";
    protected $kd = "1";
    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblKategori = "ref_kategori_persediaan";
    protected $tblSatuan = "ref_satuan";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang_tujuan = abx.id", "left");
        $builder->join($this->tblGudang . " bbx", "uk.id_gudang_asal = bbx.id", "left");
        $builder->join($this->tblBarang . " cbx", "uk.id_barang = cbx.id", "inner");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblSatuan . " ebx", "cbx.id_satuan = ebx.id", "inner");
        $builder->select("uk.id, uk.id_kategori, cbx.kode_barang, cbx.nama_barang,ebx.nama_satuan, uk.keterangan, bbx.nama_gudang as gudang_asal, abx.nama_gudang as gudang_tujuan, uk.jumlah, uk.tanggal, uk.nama, uk.kode_transaksi, dbx.kategori");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            $builder->where('uk.jenis_transaksi', $this->kd);
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(cbx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('id');
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
        $builder->where('uk.jenis_transaksi', $this->kd);
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(cbx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
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

    function getRefKategoriPersedian()
    {
        $builder = $this->db->table("ref_kategori_persediaan");
        $builder->select("*");
        $builder->where('jenis', $this->kd);
        $builder->where('aktif', 1);
        $this->_data = $builder->get()->getResultArray();
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
        // if ($builder->get()) {
        //     $this->_data = $builder->get()->getRow();
        //     return $this->_data;
        // } else {
        //     return null;
        // }
    }

    function getLastStokBarangBalances($idBarang, $idGudang, $lotId)
    {
        $builder = $this->db->table("trans_barang_balances");
        $builder->select("*");
        $builder->where('id_barang', $idBarang);
        $builder->where('id_gudang', $idGudang);
        $builder->where('lot_id', $lotId);
        $builder->where('tanggal', date("Y-m-d"));
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }
    function getLotNo($lotNo = null, $idBarang = null, $idLotNo = null, $idGudang = null)
    {
        $builder = $this->db->table("trans_lots");
        $builder->select("*");
        if (!empty($lotNo)) {

            $builder->where('lot_no', $lotNo);
        }
        if (!empty($idBarang)) {
            $builder->where('id_barang', $idBarang);
        }
        if (!empty($idLotNo)) {
            $builder->where('id', $idLotNo);
        }
        if (!empty($idGudang)) {
            $builder->where('id_gudang', $idGudang);
        }
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function trxInsertUpdateRecord($data)
    {
        $this->db->transStart();
        try {


            $this->insertRecordGetid("trans_barang", $data);
            $arrPersediaan = [
                "id_barang" => $data['id_barang'],
                "id_gudang" => $data['id_gudang_tujuan'],
                "stok" => $data['stok'],
                "created_at" =>  $data['created_at'],
                "created_by" =>  $data['created_by'],
                "active" => 1,
            ];
            $arrParam =  [
                "id_barang" => $data['id_barang'],
                "id_gudang" => $data['id_gudang_tujuan'],
            ];
            $resData = $this->getLastStokBarang($data['id_barang'], $data['id_gudang_tujuan']);
            if (!empty($resData)) {
                $this->updateRecords("trans_persediaan", $arrPersediaan, $arrParam);
            } else {
                $this->insertRecordGetid("trans_persediaan", $arrPersediaan);
            }

            if ($data['id_kategori'] == 1) {
                $data['tipe'] = 2;
                $data['id_kategori'] = 4;
                $data['jenis_transaksi'] = 2;
                $this->insertRecordGetid("trans_barang", $data);
                $resDataKeluar = $this->getLastStokBarang($data['id_barang'], $data['id_gudang_asal']);
                $arrPersediaanKeluar = [
                    "id_barang" => $data['id_barang'],
                    "id_gudang" => $data['id_gudang_asal'],
                    "stok" => $data['stok'],
                    "created_at" =>  $data['created_at'],
                    "created_by" =>  $data['created_by'],
                    "active" => 1,
                ];
                $arrParamKeluar =  [
                    "id_barang" => $data['id_barang'],
                    "id_gudang" => $data['id_gudang_asal'],
                ];
                $resDataKeluar = $this->getLastStokBarang($data['id_barang'], $data['id_gudang_asal']);
                if (!empty($resDataKeluar)) {
                    $this->updateRecords("trans_persediaan", $arrPersediaanKeluar, $arrParamKeluar);
                } else {
                    $this->insertRecordGetid("trans_persediaan", $arrPersediaanKeluar);
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
}
