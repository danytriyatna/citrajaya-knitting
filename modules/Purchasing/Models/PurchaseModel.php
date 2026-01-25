<?php

namespace Modules\Purchasing\Models;

class PurchaseModel extends \App\Models\PrModel
{

    protected $table = "trans_po_header";
    protected $tblDet = "trans_po_detail";
    protected $tblTerm = "ref_term";
    protected $tblVendor = "ref_vendor";
    protected $tblTax = "ref_tax";
    protected $tblBarang = "ref_barang";
    protected $tblSatuan = "ref_satuan";


    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null, $latest = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblVendor . " dbx", "uk.id_vendor = dbx.id", "inner");
        // $builder->join($this->tblTerm . " ebx", "uk.id_term = ebx.id", "inner");
        $builder->select("uk.id, uk.status, uk.id_vendor, uk.id_term, uk.po_no,dbx.nama as nama_vendor, uk.po_date_exp, uk.po_date, uk.date_exc, uk.ship_to, 
        COALESCE((
                        SELECT SUM(tpd.qty)
                        FROM trans_po_detail tpd
                        WHERE CAST(tpd.id_header AS INTEGER) = uk.id 
                    ), 0) as qty, 
        COALESCE((
                        SELECT SUM(tpd.grand_price)
                        FROM trans_po_detail tpd
                        WHERE CAST(tpd.id_header AS INTEGER) = uk.id 
                    ), 0) as total, 
        COALESCE((
                        SELECT SUM(trd.qty)
                        FROM trans_receive_detail trd
                        inner join trans_receive_header trh on trh.id = CAST(trd.id_header AS INTEGER)
                        WHERE CAST(trh.id_po AS INTEGER) = uk.id 
                        AND trh.status = 1
                    ), 0) as qty_payment, uk.total_payment, uk.approve_status, uk.keterangan");

        if (!empty($params['isReceive']) && $params['isReceive']) {
            $builder->groupStart();
            $builder->where("COALESCE((
                        SELECT SUM(trd.qty)
                        FROM trans_receive_detail trd
                        inner join trans_receive_header trh on trh.id = CAST(trd.id_header AS INTEGER)
                        WHERE CAST(trh.id_po AS INTEGER) = uk.id 
                        AND trh.status = 1
                    ), 0) < qty");
            $builder->orWhere("COALESCE((
                        SELECT SUM(trd.qty)
                        FROM trans_receive_detail trd
                        inner join trans_receive_header trh on trh.id = CAST(trd.id_header AS INTEGER)
                        WHERE CAST(trh.id_po AS INTEGER) = uk.id 
                        AND trh.status = 1
                    ), 0) IS NULL");
            $builder->groupEnd();
        }

        if (!empty($params['isHutang']) && $params['isHutang']) {
            $builder->whereIn("status", 1);
        }

        if (!empty($params['isReceive']) && $params['isReceive']) {
            $builder->where("approve_status", 1);
        }


        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.po_no) LIKE', strtolower("%{$filters[0]['value']}%"));
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

        if ($latest) {
            $this->_data = $builder->orderBy('id', 'DESC')->limit(1)->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->select("count(1) as _cnt");
        $builder->join($this->tblVendor . " dbx", "uk.id_vendor = dbx.id", "inner");
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.po_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateKodePO()
    {
        $kd = "POD";
        $builder = $this->db->table($this->table . ' a');
        $builder->select("LEFT(po_no, 7) AS tgl, RIGHT( po_no, 4 ) AS kode ");

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

    function trxInsertUpdateRecord($data, $id, $detail)
    {
        $this->db->transStart();
        try {
            // if (!empty($id)) {
            //     $arrDelete =  [
            //         "id_header" => $id,
            //     ];
            //     $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
            //     $arrParam =  [
            //         "id" => $id,
            //     ];
            //     $this->updateRecords($this->table, $data, $arrParam);
            // } else {
            //     $data['po_no'] = $this->generateKodePO();
            //     $id = $this->insertRecordGetid($this->table,  $data);
            // }
            // $arrDelete =  [
            //     "id_header" => $id,
            // ];

            foreach ($detail as $rowData) {
                if ($rowData['id_barang'] != "") {
                    $idBarang = decrypt($rowData['id_barang']);
                }
                $dataDetail = [
                    "id_barang" => $idBarang,
                    "disc" => !empty($rowData['disc']) ? $rowData['disc'] : null,
                    "tax" => !empty($rowData['tax']) ? $rowData['tax'] : null,
                    "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                    "grand_price" => !empty($rowData['grand_price']) ? $rowData['grand_price'] : null,
                    "disc_price" => !empty($rowData['disc_price']) ? $rowData['disc_price'] : null,
                    "tax_price" => !empty($rowData['tax_price']) ? $rowData['tax_price'] : null,
                    "kode" => !empty($rowData['kode']) ? $rowData['kode'] : null,
                    "id_header" => $id,
                    "qty_receive" => 0,
                    "id_satuan" => !empty($rowData['id_satuan']) ? $rowData['id_satuan'] : null,
                    "qty" => $rowData['qty'],
                ];

                $getDetail = $this->db->table($this->tblDet . " uk")->where('id', $rowData['id'])->get()->getRow();
                if (!empty($getDetail)) {
                    $this->updateRecord($this->tblDet, $dataDetail, 'id', $getDetail->id);
                }
                else {
                    $this->insertRecordGetid($this->tblDet, $dataDetail);
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

    function get_export($from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblVendor . " dbx", "uk.id_vendor = dbx.id", "inner");
        // $builder->join($this->tblTerm . " ebx", "uk.id_term = ebx.id", "inner");
        $builder->select("uk.id, uk.po_no, uk.id_vendor, uk.po_no,dbx.nama as nama_vendor, uk.po_date, 
                            uk.date_exc, uk.qty, 
                            COALESCE((
                                SELECT SUM(xt.qty)
                                FROM trans_receive_header xt
                                WHERE xt.id_po = uk.id
                            ), 0) as qty_receive
                            , uk.total, 
                            COALESCE((
                                SELECT SUM(xc.grand_total)
                                FROM trans_po_pembayaran_detail xc
                                WHERE xc.id_po = uk.id
                            ), 0) as total_payment, 
                            COALESCE((
                                SELECT SUM(xd.diskon)
                                FROM trans_po_pembayaran_detail xd
                                WHERE xd.id_po = uk.id
                            ), 0) as diskon");
        $builder->where("uk.po_date BETWEEN '$from_date' AND '$to_date'");
        $builder->orderBy("uk.po_date", 'desc');
        
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }
}
