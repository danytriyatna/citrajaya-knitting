<?php

namespace Modules\Purchasing\Models;

class PaymentModel extends \App\Models\PrModel
{

    protected $table = "trans_po_pembayaran";
    protected $tblDet = "trans_po_pembayaran_detail";
    protected $tblVendor = "ref_vendor";
    protected $tblRekening = "ref_rekening";
    protected $tblPoHeader = "trans_po_header";


    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblVendor . " dbx", "uk.id_vendor = dbx.id", "inner");
        $builder->join($this->tblRekening . " ebx", "uk.id_rek = ebx.id", "inner");
        $builder->select("uk.id,uk.status, uk.pay_date,uk.pay_no, uk.id_vendor, dbx.nama as nama_vendor, uk.id_rek,ebx.rekening_no, ebx.rekening_bank,uk.hutang, uk.total_bayar, uk.sisa_bayar, uk.diskon ");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.rekening_no) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.rekening_bank) LIKE', strtolower("%{$filters[0]['value']}%"));
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
        $builder->join($this->tblVendor . " dbx", "uk.id_vendor = dbx.id", "inner");
        $builder->join($this->tblRekening . " ebx", "uk.id_rek = ebx.id", "inner");
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.rekening_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.rekening_bank) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateKodePO()
    {
        $kd = "PPT";
        $builder = $this->db->table($this->table . ' a');
        $builder->select("LEFT(pay_no, 7) AS tgl, RIGHT( pay_no, 4 ) AS kode ");

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
        $builder->where("id_header", $idHeader);
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

    function trxInsertUpdateRecord($data, $id, $detail, $approve_status)
    {
        $this->db->transStart();
        try {
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
                $data['pay_no'] = $this->generateKodePO();
                $id = $this->insertRecordGetid($this->table,  $data);
            }
            $arrDelete =  [
                "id_header" => $id,
            ];

            foreach ($detail as $rowData) {
                if (!empty($rowData['total_bayar'])) {
                    $dataDetail = [
                        "po_no" => $rowData['po_no'],
                        "po_date" => !empty($rowData['po_date']) ? date('Y-m-d', strtotime($rowData['po_date'])) : null,
                        "do_date" => !empty($rowData['do_date']) ? date('Y-m-d', strtotime($rowData['do_date'])) : null,
                        "hutang" => !empty($rowData['hutang']) ? $rowData['hutang'] : 0,
                        "sisa_bayar" => !empty($rowData['sisa_bayar']) ? $rowData['sisa_bayar'] : 0,
                        "total_bayar" => !empty($rowData['total_bayar']) ? $rowData['total_bayar'] : 0,
                        "diskon" => !empty($rowData['diskon']) ? $rowData['diskon'] : 0,
                        "grand_total" => !empty($rowData['diskon']) ? $rowData['total_bayar'] - $rowData['diskon'] : $rowData['total_bayar'],
                        "qty" => !empty($rowData['qty']) ? $rowData['qty'] : 0,
                        "qty_receive" => !empty($rowData['qty_receive']) ? $rowData['qty_receive'] : 0,
                        "id_po" => !empty($rowData['id_header']) ? $rowData['id_header'] : null,
                        "id_header" => $id,

                    ];

                    $this->insertRecordGetid($this->tblDet, $dataDetail);
                    $arrParam =  [
                        "id" => $rowData['id_header'],
                    ];
                    $status = 1;
                    $totalPayment = !empty($rowData['total_bayar']) ? $rowData['total_bayar'] : 0;
                    $hutang = !empty($rowData['hutang']) ? $rowData['hutang'] : 0;
                    if ($totalPayment >= $hutang) {
                        $status = 2;
                    }

                    $bayar = !empty($rowData['total_bayar']) ? $rowData['total_bayar'] : 0;
                    $diskon = !empty($rowData['diskon']) ? $rowData['diskon'] : 0;
                    $this->updateRecords($this->tblPoHeader, array("status" => $status, "total_payment" => !empty($rowData['total_bayar']) ? $bayar : null , "diskon" => !empty($rowData['diskon']) ? $diskon : null), $arrParam);
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
