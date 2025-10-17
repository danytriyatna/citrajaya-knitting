<?php

namespace App\Models;

use CodeIgniter\Model;

class Mdashboard extends Model
{
    protected $_data = null;

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        
        $builder = $this->db->table("v_traking_order_so tbl");

        $builder->select("tbl.trans_id, tbl.trans_kode, tbl.tgl_transaksi, tbl.id_konsumen, tbl.nama, tbl.keterangan, tbl.tgl_deadline, tbl.qty, tbl.style, tbl.deskripsi, tbl.tipe, 
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, 
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim, rk.id_walkorder, tbl.file_name, tbl.uang_dp, tbl.harga_total, tbl.nilai_invoice");
        
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");

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
        $builder = $this->db->table("v_traking_order_so tbl");

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

    function getDataSample($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        

        $builder = $this->db->table("v_traking_order_sample tbl");

        $builder->select("tbl.trans_id, tbl.trans_kode, tbl.tgl_transaksi, tbl.id_konsumen, tbl.nama, tbl.keterangan, tbl.tgl_deadline, tbl.qty, tbl.style, tbl.deskripsi, tbl.tipe, 
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, 
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim, rk.id_walkorder, tbl.file_name, tbl.uang_dp, tbl.harga_total, tbl.nilai_invoice");
        
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");

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

    function getDataCntSample($filters = null, $params = null)
    {

        $builder = $this->db->table("v_traking_order_sample tbl");

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

        $builder->select(" ti.id, ti.kode_invoice, ti.tgl_transaksi as tgl_invoice, rk.nama, ti.tgl_jatuh_tempo,  ti.grand_total as total_invoice_bayar, ti.total as total_invoice,
                           COALESCE((select sum(xc.pay_item) from trans_customer_receipt_detail xc where xc.id_invoice = ti.id), 0) as pembayaran,  COALESCE((SELECT SUM(tid.down_payment)
                                FROM trans_invoice_detail tid
                                WHERE tid.id_invoice = ti.id
                            ),0) as total_down_payment");

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

            $builder->where("ti.grand_total > ( COALESCE((select sum(xc.pay_item) from trans_customer_receipt_detail xc where xc.id_invoice = ti.id), 0) + COALESCE((SELECT SUM(tid.down_payment) FROM trans_invoice_detail tid WHERE tid.id_invoice = ti.id),0))"); // kondisi untuk invoice yang belum lunas

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

    function getDataPenjualan($month = null, $year = null)
    {
        $builder = $this->db->table("trans_invoice abx");
        
        $builder->select("SUM(abx.grand_total::float) as grand_total");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->where('abx.active = 1');

        $builder->where("EXTRACT(MONTH FROM abx.tgl_transaksi) = $month");
        $builder->where("EXTRACT(YEAR FROM abx.tgl_transaksi) = $year");    

        $this->_data = $builder->get()->getRow()->grand_total;

        return $this->_data;
    }

    function getDataPemakaian($month = null, $year = null)
    {
        $builder = $this->db->table("trans_barang_detail abx");
        
        $builder->select("SUM(COALESCE(abx.price, 0) * COALESCE(abx.qty, 0)) AS grand_total");
        $builder->join("trans_barang_header bbx", "CAST(abx.id_header AS INTEGER) = bbx.id", "inner");
        $builder->where('bbx.active = 1');
        $builder->where('bbx.jenis_transaksi', 2);

        $builder->where("EXTRACT(MONTH FROM bbx.tanggal) = $month");
        $builder->where("EXTRACT(YEAR FROM bbx.tanggal) = $year");    

        $this->_data = $builder->get()->getRow()->grand_total;

        return $this->_data;
    }

    function getDataBiaya($month = null, $year = null)
    {
        $builder = $this->db->table('m_coa ax');
        
        $builder->select("  ax.id,
                            ax.parent_id,
                            ax.kode,
                            ax.nama,
                            ax.level,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, $month, {$year}) 
                                  ELSE get_jml_month(ax.id, $month, {$year}) 
                            END) as bln1
                            ");

        $builder->where("ax.active = 1");
        $builder->where("(
    CAST(ax.kode AS INTEGER) >= 5000 
    AND MOD(CAST(ax.kode AS INTEGER), 1000) != 0
  )");

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function getGrafikDataPenjualan($year = null)
    {
        $builder = $this->db->table("trans_invoice abx");
        $builder->select("
            EXTRACT(MONTH FROM abx.tgl_transaksi) AS bulan,
            SUM(abx.grand_total::float) AS total_penjualan
        ");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->where("abx.active", 1);
        $builder->where("EXTRACT(YEAR FROM abx.tgl_transaksi)", $year);
        $builder->groupBy("EXTRACT(MONTH FROM abx.tgl_transaksi)");
        $builder->orderBy("bulan", "ASC");

        $result = $builder->get()->getResult();

        // Jika mau hasil dalam bentuk array bulan => total
        $data = [];
        for ($i = 0; $i < 12; $i++) {
            $data[$i] = 0; // default 0
        }

        foreach ($result as $row) {
            $data[(int)$row->bulan-1] = (float)$row->total_penjualan;
        }

        return $data;
    }

    function getGrafikDataPemakaian($year = null)
    {
        $builder = $this->db->table("trans_barang_detail abx");
        $builder->select("
            EXTRACT(MONTH FROM bbx.tanggal) AS bulan,
            SUM(COALESCE(abx.price, 0) * COALESCE(abx.qty, 0)) AS total_pemakaian
        ");
        $builder->join("trans_barang_header bbx", "CAST(abx.id_header AS INTEGER) = bbx.id", "inner");
        $builder->where("bbx.active", 1);
        $builder->where("bbx.jenis_transaksi", 2);
        $builder->where("EXTRACT(YEAR FROM bbx.tanggal)", $year);
        $builder->groupBy("EXTRACT(MONTH FROM bbx.tanggal)");
        $builder->orderBy("bulan", "ASC");

        $result = $builder->get()->getResult();

        // Pastikan hasil lengkap dari bulan 1–12 (yang kosong diisi 0)
        $data = [];
        for ($i = 0; $i < 12; $i++) {
            $data[$i] = 0;
        }

        foreach ($result as $row) {
            $data[(int)$row->bulan-1] = (float)$row->total_pemakaian;
        }

        return $data;
    }

}