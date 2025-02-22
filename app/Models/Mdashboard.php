<?php

namespace App\Models;

use CodeIgniter\Model;

class Mdashboard extends Model
{
    protected $_data = null;

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("v_traking_order tbl");

        $builder->select("tbl.trans_id, tbl.trans_kode, tbl.tgl_transaksi, tbl.id_konsumen, tbl.nama, tbl.keterangan, tbl.tgl_deadline, tbl.qty, tbl.tipe, 
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, 
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim");

        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(tbl.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(tbl.trans_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(tbl.kode_dev) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(tbl.kode_prod) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(tbl.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("tbl.tgl_transaksi desc");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("tbl.trans_id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("v_traking_order tbl");

        $builder->select("count(1) as _cnt");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(tbl.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.trans_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.kode_dev) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.kode_prod) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }



    function getDataInv($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_invoice ti");

        $builder->select(" ti.id, ti.kode_invoice, ti.tgl_transaksi as tgl_invoice, rk.nama, ti.tgl_jatuh_tempo,  ti.grand_total as total_invoice,
                           COALESCE((select sum(xc.pay_item) from trans_customer_receipt_detail xc where xc.id_invoice = ti.id), 0) as pembayaran");

        $builder->join("ref_konsumen rk", "rk.id = ti.id_konsumen", "left");

        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            $builder->where("ti.grand_total > COALESCE((select sum(xc.pay_item) from trans_customer_receipt_detail xc where xc.id_invoice = ti.id), 0)"); // kondisi untuk invoice yang belum lunas

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("ti.tgl_jatuh_tempo asc");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("ti.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataInvCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("trans_invoice ti");

        $builder->select("count(1) as _cnt");

        $builder->join("ref_konsumen rk", "rk.id = ti.id_konsumen", "left");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $builder->where("ti.grand_total > (select sum(xc.pay_item) from trans_customer_receipt_detail xc where xc.id_invoice = ti.id)"); // kondisi untuk invoice yang belum lunas

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataPo($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_po_header ph");

        $builder->select(" ph.id, ph.po_no, ph.po_date as tgl_po, rv.nama, ph.date_exc, ph.total as total_bayar,
                           (SELECT sum(tpo.total_bayar)+sum(tpo.diskon) from trans_po_pembayaran_detail tpo where tpo.id_po = ph.id) as dibayar, COALESCE(ph.diskon, 0) as diskon, term.days");

        $builder->join("ref_vendor rv", "rv.id = ph.id_vendor", "left");
        $builder->join("ref_term term", "term.id = ph.id_term", "left");

        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(ph.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(ph.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(rv.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(ph.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            $builder->where("ph.total > (SELECT sum(tpo.total_bayar)+sum(tpo.diskon) from trans_po_pembayaran_detail tpo where tpo.id_po = ph.id)");
            $builder->orWhere("ph.total_payment", 0); 
            $builder->where("ph.active", 1); 
            $builder->where("ph.approve_status", 1); 
            // $builder->where('COALESCE(ph.diskon, 0) + COALESCE(ph.total_payment, 0) < ph.total');

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("ph.date_exc asc");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("ph.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataPoCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("trans_po_header ph");

        $builder->select("count(1) as _cnt");

        $builder->join("ref_vendor rv", "rv.id = ph.id_vendor", "left");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(ph.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ph.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rv.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ph.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $builder->where("ph.total > (SELECT sum(tpo.total_bayar) from trans_po_pembayaran_detail tpo where tpo.id_header = ph.id)"); // kondisi untuk PO yang belum lunas

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}