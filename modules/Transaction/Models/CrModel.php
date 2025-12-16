<?php

namespace Modules\Transaction\Models;

class CrModel extends \App\Models\PrModel
{

    protected $table = "trans_customer_receipt";
    protected $table2 = "trans_customer_receipt_detail";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " tr");

        $builder->select("  tr.id, tr.kode_cr, tr.tgl_transaksi, tr.pph, tr.total_bayar, tr.id_rekening, 
                            tr.status, rk.id as id_konsumen,  rk.nama as nama_konsumen,  rk.alamat as alamat_konsumen,
                            rka.rekening_no, rka.rekening_bank, rka.rekening_an");

        $builder->join("ref_konsumen rk", "tr.id_konsumen = rk.id", "inner");
        $builder->join("ref_rekening rka", "tr.id_rekening = rka.id", "inner");

        if ($id == null or $id == "") {
            $builder->where('tr.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(tr.kode_cr) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('tr.id_konsumen', $params['id_konsumen']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('tr.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("tr.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " tr");
        
        $builder->select("count(1) as _cnt");
        $builder->join("ref_konsumen rk", "tr.id_konsumen = rk.id", "inner");
        $builder->where('tr.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(tr.kode_cr) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }


    function getDataDet($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " trd");

        $builder->select("  trd.id,
                            trd.id_cr,
                            trd.id_invoice,
                            trd.total_item,
                            trd.pph, 
                            trd.remain_item,
                            trd.pay_item,
                            ti.id as id_invoice,
                            ti.tgl_transaksi,
                            ti.kode_invoice, COALESCE((
                                SELECT SUM(tid.down_payment)
                                FROM trans_invoice_detail tid
                                WHERE tid.id_invoice = ti.id
                            ),0) as dp,
                            COALESCE((
                                SELECT SUM(tso.pengiriman)
                                FROM trans_invoice_detail tid
                                inner join trans_sales_order tso on tso.id = tid.id_ref
                                WHERE tid.id_invoice = ti.id
                            ),0) as pengiriman");

        $builder->join("trans_invoice ti", "trd.id_invoice = ti.id", "inner");

        if ($id == null or $id == "") {
            $builder->where('trd.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_cr'])) {
                $builder->where('trd.id_cr', $params['id_cr']);
            }

            if (!empty($params['id_invoice'])) {
                $builder->where('trd.id_invoice', $params['id_invoice']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('trd.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("trd.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataDetCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " trd");
        
        $builder->select("count(1) as _cnt");
        $builder->join("trans_invoice ti", "trd.id_invoice = ti.id", "inner");
        $builder->where('tr.active = 1');

        if (!empty($params['id_cr'])) {
            $builder->where('trd.id_cr', $params['id_cr']);
        }

        if (!empty($params['id_invoice'])) {
            $builder->where('trd.id_invoice', $params['id_invoice']);
        }

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}