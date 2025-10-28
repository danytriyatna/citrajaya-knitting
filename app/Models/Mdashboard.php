<?php

namespace App\Models;

use CodeIgniter\Model;

class Mdashboard extends Model
{
    protected $table = "trans_po_pembayaran_detail";
    protected $_data = null;
    protected $primaryKey = 'id';

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        
        $builder = $this->db->table("v_traking_order_so tbl");

        $builder->select("tbl.trans_id, tbl.trans_kode, tbl.tgl_transaksi, tbl.id_konsumen, tbl.nama, tbl.keterangan, tbl.tgl_deadline, tbl.qty, tbl.style, tbl.deskripsi, tbl.tipe, 
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, 
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim, rk.id_walkorder, tbl.file_name, tbl.uang_dp, tbl.harga_total, tbl.nilai_invoice");
        
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");

        $builder->where("tbl.harga_total > (tbl.uang_dp + tbl.nilai_invoice)");
        $builder->where("tbl.qty > tbl.qty_kirim");

        // $builder->where("EXTRACT(MONTH FROM tbl.tgl_dp) = 10");
        // $builder->where("EXTRACT(YEAR FROM tbl.tgl_dp) = 2025"); 
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

        $builder->where("tbl.harga_total > (tbl.uang_dp + tbl.nilai_invoice)");
        $builder->where("tbl.qty > tbl.qty_kirim");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(tbl.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.trans_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.kode_dev) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.kode_prod) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tbl.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        // $builder->where("EXTRACT(MONTH FROM tbl.tgl_dp) = 10");
        // $builder->where("EXTRACT(YEAR FROM tbl.tgl_dp) = 2025"); 

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

        $builder->where("tbl.harga_total > tbl.nilai_invoice");
        $builder->where("tbl.qty > tbl.qty_kirim");

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

        $builder->where("tbl.harga_total > tbl.nilai_invoice");
        $builder->where("tbl.qty > tbl.qty_kirim");

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

    function getDataInvNew($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table('ref_konsumen rk');

        $subNilaiSO = "
            COALESCE((
                SELECT SUM(tsou.harga_total)
                FROM trans_sales_order_ukuran tsou
                INNER JOIN trans_sales_order tsod ON tsod.id = tsou.id_sales_order
                WHERE tsou.active = 1
                AND tsod.id_konsumen = rk.id
                AND tsod.id IN (
                    SELECT DISTINCT tid.id_ref
                    FROM trans_invoice_detail tid
                    WHERE tid.tipe_id = 2
                )
            ), 0)
        ";

        $subNilaiSample = "
            COALESCE(SUM((
                SELECT SUM(tsu.harga_total)
                FROM trans_sample_ukuran tsu
                INNER JOIN trans_sample tsd ON tsd.id = tsu.id_sample
                WHERE tsu.active = 1 
                AND tsd.id_konsumen = rk.id
                AND tsd.id IN (
                    SELECT DISTINCT tid.id_ref
                    FROM trans_invoice_detail tid
                    where tid.tipe_id = 1
                )
            )), 0)
        ";

        $subTotalInvoice = "
            COALESCE((
                SELECT SUM(ti.total)
                FROM trans_invoice ti
                WHERE ti.id_konsumen = rk.id
            ), 0)
        ";

        $subTotalDP = "
            COALESCE(SUM((
                SELECT SUM(tsot.down_payment)
                FROM trans_invoice_detail tsot
                INNER JOIN trans_invoice ti ON ti.id = tsot.id_invoice
                WHERE ti.id_konsumen = rk.id
            )), 0)
        ";

        $subPembayaran = "
            COALESCE((
                SELECT SUM(xc.pay_item)
                FROM trans_customer_receipt_detail xc
                INNER JOIN trans_customer_receipt tcr ON tcr.id = xc.id_cr
                WHERE tcr.id_konsumen = rk.id
            ), 0)
        ";

        // Pilih kolom utama dan subquery
        $builder->select("
            rk.nama AS buyer,
            {$subNilaiSO} AS nilai_so,
            {$subTotalInvoice} AS nilai_invoice,
            ({$subTotalDP} + {$subPembayaran}) AS pembayaran,
            ({$subNilaiSO} - {$subTotalInvoice}) AS sisa_tagihan,
            ({$subNilaiSO} - ({$subTotalDP} + {$subPembayaran})) AS sisa_pembayaran
        ");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->where('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        // Group & filter
        $builder->groupBy(['rk.id', 'rk.nama']);
        $builder->having("{$subNilaiSO} > ({$subTotalDP} + {$subPembayaran})");
        $builder->orderBy('rk.nama', 'ASC');

        // Eksekusi
        $query = $builder->get();
        $result = $query->getResult();
        return $result;

    }

    function getDataInvCntNew($filters = null, $params = null)
    {
        $builder = $this->db->table('ref_konsumen rk');

        $subNilaiSO = "
            COALESCE((
                SELECT SUM(tsou.harga_total)
                FROM trans_sales_order_ukuran tsou
                INNER JOIN trans_sales_order tsod ON tsod.id = tsou.id_sales_order
                WHERE tsou.active = 1
                AND tsod.id_konsumen = rk.id
                AND tsod.id IN (
                    SELECT DISTINCT tid.id_ref
                    FROM trans_invoice_detail tid
                    WHERE tid.tipe_id = 2
                )
            ), 0)
        ";

        $subNilaiSample = "
            COALESCE(SUM((
                SELECT SUM(tsu.harga_total)
                FROM trans_sample_ukuran tsu
                INNER JOIN trans_sample tsd ON tsd.id = tsu.id_sample
                WHERE tsu.active = 1 
                AND tsd.id_konsumen = rk.id
                AND tsd.id IN (
                    SELECT DISTINCT tid.id_ref
                    FROM trans_invoice_detail tid
                    where tid.tipe_id = 1
                )
            )), 0)
        ";

        $subTotalInvoice = "
            COALESCE((
                SELECT SUM(ti.total)
                FROM trans_invoice ti
                WHERE ti.id_konsumen = rk.id
            ), 0)
        ";

        $subTotalDP = "
            COALESCE(SUM((
                SELECT SUM(tsot.down_payment)
                FROM trans_invoice_detail tsot
                INNER JOIN trans_invoice ti ON ti.id = tsot.id_invoice
                WHERE ti.id_konsumen = rk.id
            )), 0)
        ";

        $subPembayaran = "
            COALESCE((
                SELECT SUM(xc.pay_item)
                FROM trans_customer_receipt_detail xc
                INNER JOIN trans_customer_receipt tcr ON tcr.id = xc.id_cr
                WHERE tcr.id_konsumen = rk.id
            ), 0)
        ";

        // Pilih kolom utama dan subquery
        $builder->select("
            rk.nama AS buyer,
            {$subNilaiSO} AS nilai_so,
            {$subTotalInvoice} AS nilai_invoice,
            ({$subTotalDP} + {$subPembayaran}) AS pembayaran,
            ({$subNilaiSO} - {$subTotalInvoice}) AS sisa_tagihan,
            ({$subNilaiSO} - ({$subTotalDP} + {$subPembayaran})) AS sisa_pembayaran
        ");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->where('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        // Group & filter
        $builder->groupBy(['rk.id', 'rk.nama']);
        $builder->having("{$subNilaiSO} > ({$subTotalDP} + {$subPembayaran})");
        $builder->orderBy('rk.nama', 'ASC');

        // Dapatkan SQL-nya
        $sql = $builder->getCompiledSelect();

        // Bungkus dan hitung jumlah baris
        $countQuery = $this->db->query("SELECT COUNT(*) AS total FROM ({$sql}) AS x");
        $total = $countQuery->getRow()->total;

        return $total;

    }



    function getDataInv($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_invoice ti");

        //SUM(ti.total) as total_invoice,
        
        $builder->select("
            rk.id,
            rk.nama,
            COALESCE(SUM((
                SELECT SUM(tid.total)
                FROM trans_invoice_detail tid
                WHERE tid.id_invoice = ti.id
            )), 0) as total_invoice, 
            SUM(ti.grand_total) as total_invoice_bayar,
            COALESCE(SUM((
                SELECT SUM(xc.pay_item)
                FROM trans_customer_receipt_detail xc
                WHERE xc.id_invoice = ti.id
            )), 0) as pembayaran,
            COALESCE(SUM((
                SELECT SUM(tid.down_payment)
                FROM trans_invoice_detail tid
                WHERE tid.id_invoice = ti.id and tipe_id = 2
            )), 0) as total_down_payment,
            COALESCE(SUM((
                SELECT sum(tsou.harga_total)
                FROM trans_sales_order_ukuran tsou
                inner join trans_sales_order tso on tsou.id_sales_order = tso.id 
                inner join trans_invoice_detail tidt on tso.id = tidt.id_ref 
                WHERE tidt.id_invoice = ti.id and tidt.tipe_id = 2 and tsou.active = 1
            )), 0) as nilai_so,
            COALESCE(SUM((
                SELECT sum(tsu.harga_total)
                FROM trans_sample_ukuran tsu
                inner join trans_sample ts on tsu.id_sample = ts.id 
                inner join trans_invoice_detail tidt on ts.id = tidt.id_ref 
                WHERE tidt.id_invoice = ti.id and tidt.tipe_id = 1 and ts.active = 1
            )), 0) as nilai_sample,
        ");

        $builder->join("ref_konsumen rk", "rk.id = ti.id_konsumen", "left");

        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                    // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->where('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                    // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }
            $builder->where("
                ti.total > 
                (
                    COALESCE((
                        SELECT SUM(tid.down_payment)
                        FROM trans_invoice_detail tid
                        WHERE tid.id_invoice = ti.id AND tid.tipe_id = 2
                    ), 0)
                    +
                    COALESCE((
                        SELECT SUM(xc.pay_item)
                        FROM trans_customer_receipt_detail xc
                        WHERE xc.id_invoice = ti.id
                    ), 0)
                )
            ", null, false);
             $builder->groupBy("rk.id, rk.nama");
            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("rk.nama asc");
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

        $builder->select("COUNT(rk.id) as _cnt");

        $builder->join("ref_konsumen rk", "rk.id = ti.id_konsumen", "left");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $builder->where("
                ti.total > 
                (
                    COALESCE((
                        SELECT SUM(tid.down_payment)
                        FROM trans_invoice_detail tid
                        WHERE tid.id_invoice = ti.id AND tid.tipe_id = 2
                    ), 0)
                    +
                    COALESCE((
                        SELECT SUM(xc.pay_item)
                        FROM trans_customer_receipt_detail xc
                        WHERE xc.id_invoice = ti.id
                    ), 0)
                )
            ", null, false);
        $builder->groupBy("rk.id, rk.nama");
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

    function getDataPoNew($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $addSQL = '';
        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $data = $filters[0]['value'];
            $addSQL = " AND rv.nama ILIKE '%{$data}%'";
        }
        $sql = "
            WITH po_filtered AS (
                SELECT 
                    ph.id,
                    ph.id_vendor,
                    rv.nama AS nama_vendor,
                    ph.po_no,
                    ph.po_date,
                    ph.date_exc,
                    ph.total::int AS total_bayar,
                    COALESCE(ph.diskon::int, 0) AS diskon,
                    term.days,
                    (SELECT SUM(tpo.total_bayar::int) + SUM(tpo.diskon::int)
                    FROM trans_po_pembayaran_detail tpo
                    WHERE tpo.id_po = ph.id) AS dibayar
                FROM trans_po_header ph
                INNER JOIN ref_vendor rv ON rv.id = ph.id_vendor
                INNER JOIN ref_term term ON term.id = ph.id_term
                WHERE 
                    (ph.total > (SELECT SUM(tpo.total_bayar) + SUM(tpo.diskon)
                                FROM trans_po_pembayaran_detail tpo
                                WHERE tpo.id_po = ph.id)
                    OR ph.total_payment = 0)
                    AND ph.active = 1 
                    AND ph.approve_status = 1
                    {$addSQL} 
            )
            SELECT 
                id_vendor,
                nama_vendor,
                SUM(total_bayar) AS total_bayar,
                SUM(COALESCE(dibayar, 0)) AS dibayar,
                SUM(diskon) AS diskon
            FROM po_filtered
            GROUP BY id_vendor, nama_vendor
            ORDER BY nama_vendor;
            ";

            $query = $this->db->query($sql);
            $this->_data = $query->getResult();
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

    function getDataPoCntNew($filters = null, $params = null)
    {
        $addSQL = '';
        $sqlOld = 'COUNT(id_vendor) as _cnt';
        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $data = $filters[0]['value'];
            $addSQL = " AND rv.nama ILIKE '%{$data}%'";
            $sqlOld = 'COUNT(DISTINCT id_vendor) as _cnt';
        }
        $sql = "
            WITH po_filtered AS (
                SELECT 
                    ph.id,
                    ph.id_vendor,
                    rv.nama AS nama_vendor,
                    ph.po_no,
                    ph.po_date,
                    ph.date_exc,
                    ph.total::int AS total_bayar,
                    COALESCE(ph.diskon::int, 0) AS diskon,
                    term.days,
                    (SELECT SUM(tpo.total_bayar::int) + SUM(tpo.diskon::int)
                    FROM trans_po_pembayaran_detail tpo
                    WHERE tpo.id_po = ph.id) AS dibayar
                FROM trans_po_header ph
                INNER JOIN ref_vendor rv ON rv.id = ph.id_vendor
                INNER JOIN ref_term term ON term.id = ph.id_term
                WHERE 
                    (ph.total > (SELECT SUM(tpo.total_bayar) + SUM(tpo.diskon)
                                FROM trans_po_pembayaran_detail tpo
                                WHERE tpo.id_po = ph.id)
                    OR ph.total_payment = 0)
                    AND ph.active = 1 
                    AND ph.approve_status = 1
                    {$addSQL} 
            )
            SELECT 
                {$sqlOld}
            FROM po_filtered
            GROUP BY id_vendor, nama_vendor
            ORDER BY nama_vendor;
            ";

            $query = $this->db->query($sql);
            $this->_data = $query->getRow()->_cnt;
            return $this->_data;
    }

    function getDataDP($month = null, $year = null)
    {
        $builder = $this->db->table("trans_sales_order abx");
        $builder->select("SUM(abx.uang_dp::float) as total_dp");
        
        $builder->where('abx.active = 1');
        $builder->where("EXTRACT(MONTH FROM abx.tgl_dp) = $month");
        $builder->where("EXTRACT(YEAR FROM abx.tgl_dp) = $year");    

        $this->_data = $builder->get()->getRow()->total_dp;
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