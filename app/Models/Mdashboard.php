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
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, nilai_pembayaran,
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim, rk.id_walkorder, tbl.file_name, tbl.uang_dp, tbl.harga_total, tbl.nilai_invoice");
        
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");
        
        
        $builder->groupStart();
            $builder->where("tbl.qty > tbl.qty_kirim");
            $builder->orWhere("tbl.harga_total > (tbl.uang_dp + tbl.nilai_pembayaran)");
        $builder->groupEnd();

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

        $builder->groupStart();
            $builder->where("tbl.qty > tbl.qty_kirim");
            $builder->orWhere("tbl.harga_total > (tbl.uang_dp + tbl.nilai_pembayaran)");
        $builder->groupEnd();

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

    function getSumData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        
        $builder = $this->db->table($params['tabel']." tbl");

        $builder->select("tbl.trans_id, tbl.trans_kode, tbl.tgl_transaksi, tbl.id_konsumen, tbl.nama, tbl.keterangan, tbl.tgl_deadline, tbl.qty, tbl.style, tbl.deskripsi, tbl.tipe, 
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, nilai_pembayaran,
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim, rk.id_walkorder, tbl.file_name, tbl.uang_dp, tbl.harga_total, tbl.nilai_invoice");
        
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");
        
        
        $builder->groupStart();
            $builder->where("tbl.qty > tbl.qty_kirim");
            $builder->orWhere("tbl.harga_total > (tbl.uang_dp + tbl.nilai_pembayaran)");
        $builder->groupEnd();

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

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("tbl.trans_id", $id);

            $this->_data = $builder->get()->getRow();
        }
        return $this->_data;
    }

    function getDataSample($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        

        $builder = $this->db->table("v_traking_order_sample tbl");

        $builder->select("tbl.trans_id, tbl.trans_kode, tbl.tgl_transaksi, tbl.id_konsumen, tbl.nama, tbl.keterangan, tbl.tgl_deadline, tbl.qty, tbl.style, tbl.deskripsi, tbl.tipe, 
                          tbl.kode_prod, tbl.id_prod, tbl.qty_prod, nilai_pembayaran,
                          tbl.kode_dev, tbl.id_dev, tbl.qty_kirim, rk.id_walkorder, tbl.file_name, tbl.uang_dp, tbl.harga_total, tbl.nilai_invoice");
        
        $builder->join("trans_produksi rk", "rk.id = tbl.id_prod", "left");

        $builder->groupStart();
            $builder->where("tbl.qty > tbl.qty_kirim");
            $builder->orWhere("tbl.harga_total > (tbl.uang_dp + tbl.nilai_pembayaran)");
        $builder->groupEnd();

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

        $builder->groupStart();
            $builder->where("tbl.qty > tbl.qty_kirim");
            $builder->orWhere("tbl.harga_total > (tbl.uang_dp + tbl.nilai_pembayaran)");
        $builder->groupEnd();

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
        $subQuery = $this->db->table('trans_sales_order abx')
            ->select("
                bbx.nama,
                SUM(
                    COALESCE((
                        SELECT SUM(tsou.harga_total)
                        FROM trans_sales_order_ukuran tsou
                        WHERE tsou.id_sales_order = abx.id
                        AND (
                            -- Kondisi 1: Belum ada invoice sama sekali
                            abx.id NOT IN (
                                SELECT DISTINCT id_ref
                                FROM trans_invoice_detail
                                WHERE tipe_id = 2
                            )

                            -- Kondisi 2: Sudah ada invoice, tapi masih ada sisa tagihan
                            OR (
                                (
                                    SELECT COALESCE(SUM(tidd.grand_total), 0)
                                    FROM trans_invoice_detail tidd
                                    WHERE tidd.id_ref = abx.id 
                                        AND tidd.tipe_id = 2 
                                        AND tidd.payment_status = 1
                                ) < (
                                    SELECT COALESCE(SUM(tsou2.harga_total), 0)
                                    FROM trans_sales_order_ukuran tsou2
                                    WHERE tsou2.id_sales_order = abx.id
                                )
                            )
                        )
                    ), 0)
                ) AS nilai_so,
                SUM(
                    COALESCE((
                        SELECT SUM(tid.grand_total)
                        FROM trans_invoice_detail tid
                        WHERE tid.id_ref = abx.id AND tid.tipe_id = 2
                    ), 0)
                ) AS nilai_invoice,
                SUM(abx.uang_dp) AS total_down_payment,
                SUM(
                    COALESCE((
                        SELECT SUM(tidd.grand_total)
                        FROM trans_invoice_detail tidd
                        WHERE tidd.id_ref = abx.id 
                        AND tidd.tipe_id = 2 
                        AND tidd.payment_status = 1
                    ), 0)
                ) AS pembayaran
            ")
            ->join('ref_konsumen bbx', 'abx.id_konsumen = bbx.id', 'inner')
            ->where("
                abx.active = 1
            ")
            ->where('abx.status = 2')
            ->where("
                COALESCE((
                        SELECT SUM(tsou.harga_total)
                        FROM trans_sales_order_ukuran tsou
                        WHERE tsou.id_sales_order = abx.id
                        AND (
                            -- Kondisi 1: Belum ada invoice sama sekali
                            abx.id NOT IN (
                                SELECT DISTINCT id_ref
                                FROM trans_invoice_detail
                                WHERE tipe_id = 2
                            )

                            -- Kondisi 2: Sudah ada invoice, tapi masih ada sisa tagihan
                            OR (
                                (
                                    SELECT COALESCE(SUM(tidd.grand_total), 0)
                                    FROM trans_invoice_detail tidd
                                    WHERE tidd.id_ref = abx.id 
                                        AND tidd.tipe_id = 2 
                                        AND tidd.payment_status = 1
                                ) < (
                                    SELECT COALESCE(SUM(tsou2.harga_total), 0)
                                    FROM trans_sales_order_ukuran tsou2
                                    WHERE tsou2.id_sales_order = abx.id
                                )
                            )
                        )
                    ), 0)
                >
                (
                    abx.uang_dp +
                    COALESCE((
                        SELECT SUM(tidd.grand_total)
                        FROM trans_invoice_detail tidd
                        WHERE tidd.id_ref = abx.id 
                        AND tidd.tipe_id = 2 
                        AND tidd.payment_status = 1
                    ), 0)
                )
            ")
            // ->orWhere("
            //     (
            //         COALESCE((
            //             SELECT SUM(tid.grand_total)
            //             FROM trans_invoice_detail tid
            //             WHERE tid.id_ref = abx.id AND tid.tipe_id = 2
            //         ), 0) = 0
            //         AND
            //         COALESCE((
            //             SELECT SUM(tidd.grand_total)
            //             FROM trans_invoice_detail tidd
            //             WHERE tidd.id_ref = abx.id 
            //             AND tidd.tipe_id = 2 
            //             AND tidd.payment_status = 1
            //         ), 0) = 0
            //         AND
            //         abx.uang_dp = 0
            //     )
            // ")
            ->groupBy('bbx.nama')
            ->getCompiledSelect(); // hasilkan subquery sebagai string SQL

        $builder = $this->db->table("($subQuery) AS x");

        $builder->select("
            x.nama AS buyer,
            x.nilai_so,
            x.nilai_invoice,
            x.total_down_payment AS DP,
            x.pembayaran AS CR,
            (x.total_down_payment + x.pembayaran) AS pembayaran,
            x.nilai_so - (x.nilai_invoice + x.total_down_payment) AS sisa_tagihan,
            (x.nilai_so - (x.total_down_payment + x.pembayaran)) AS sisa_pembayaran
        ");

        $builder->where('x.nilai_so > (x.total_down_payment + x.pembayaran)');
        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                    // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->where('LOWER(x.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                    // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);
        }
        $builder->orderBy('x.nama', 'ASC');

        $query  = $builder->get();
        $result = $query->getResult();



        return $result;

    }

    function getDataInvCntNew($filters = null, $params = null)
    {
        $subQuery = $this->db->table('trans_sales_order abx')
            ->select("
                bbx.nama,
                SUM(
                    COALESCE((
                        SELECT SUM(tsou.harga_total)
                        FROM trans_sales_order_ukuran tsou
                        WHERE tsou.id_sales_order = abx.id
                        AND (
                            -- Kondisi 1: Belum ada invoice sama sekali
                            abx.id NOT IN (
                                SELECT DISTINCT id_ref
                                FROM trans_invoice_detail
                                WHERE tipe_id = 2
                            )

                            -- Kondisi 2: Sudah ada invoice, tapi masih ada sisa tagihan
                            OR (
                                (
                                    SELECT COALESCE(SUM(tidd.grand_total), 0)
                                    FROM trans_invoice_detail tidd
                                    WHERE tidd.id_ref = abx.id 
                                        AND tidd.tipe_id = 2 
                                        AND tidd.payment_status = 1
                                ) < (
                                    SELECT COALESCE(SUM(tsou2.harga_total), 0)
                                    FROM trans_sales_order_ukuran tsou2
                                    WHERE tsou2.id_sales_order = abx.id
                                )
                            )
                        )
                    ), 0)
                ) AS nilai_so,
                SUM(
                    COALESCE((
                        SELECT SUM(tid.grand_total)
                        FROM trans_invoice_detail tid
                        WHERE tid.id_ref = abx.id AND tid.tipe_id = 2
                    ), 0)
                ) AS nilai_invoice,
                SUM(abx.uang_dp) AS total_down_payment,
                SUM(
                    COALESCE((
                        SELECT SUM(tidd.grand_total)
                        FROM trans_invoice_detail tidd
                        WHERE tidd.id_ref = abx.id 
                        AND tidd.tipe_id = 2 
                        AND tidd.payment_status = 1
                    ), 0)
                ) AS pembayaran
            ")
            ->join('ref_konsumen bbx', 'abx.id_konsumen = bbx.id', 'inner')
            ->where("
                abx.active = 1
            ")
            ->where('abx.status = 2')
            ->where("
                COALESCE((
                        SELECT SUM(tsou.harga_total)
                        FROM trans_sales_order_ukuran tsou
                        WHERE tsou.id_sales_order = abx.id
                        AND (
                            -- Kondisi 1: Belum ada invoice sama sekali
                            abx.id NOT IN (
                                SELECT DISTINCT id_ref
                                FROM trans_invoice_detail
                                WHERE tipe_id = 2
                            )

                            -- Kondisi 2: Sudah ada invoice, tapi masih ada sisa tagihan
                            OR (
                                (
                                    SELECT COALESCE(SUM(tidd.grand_total), 0)
                                    FROM trans_invoice_detail tidd
                                    WHERE tidd.id_ref = abx.id 
                                        AND tidd.tipe_id = 2 
                                        AND tidd.payment_status = 1
                                ) < (
                                    SELECT COALESCE(SUM(tsou2.harga_total), 0)
                                    FROM trans_sales_order_ukuran tsou2
                                    WHERE tsou2.id_sales_order = abx.id
                                )
                            )
                        )
                    ), 0)
                >
                (
                    abx.uang_dp +
                    COALESCE((
                        SELECT SUM(tidd.grand_total)
                        FROM trans_invoice_detail tidd
                        WHERE tidd.id_ref = abx.id 
                        AND tidd.tipe_id = 2 
                        AND tidd.payment_status = 1
                    ), 0)
                )
            ")
            ->groupBy('bbx.nama')
            ->getCompiledSelect(); // hasilkan subquery sebagai string SQL

        $builder = $this->db->table("($subQuery) AS x");

        $builder->select("
            x.nama AS buyer,
            x.nilai_so,
            x.nilai_invoice,
            x.total_down_payment AS DP,
            x.pembayaran AS CR,
            (x.total_down_payment + x.pembayaran) AS pembayaran,
            x.nilai_so - (x.nilai_invoice + x.total_down_payment) AS sisa_tagihan,
            (x.nilai_so - (x.total_down_payment + x.pembayaran)) AS sisa_pembayaran
        ");

        $builder->where('x.nilai_so > (x.total_down_payment + x.pembayaran)');
        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->where('LOWER(x.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }
        $builder->orderBy('x.nama', 'ASC');
        $query  = $builder->get();

        $count = $query->getNumRows();

        return $count;

    }

    // function getDataInv($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    // {
    //     $builder = $this->db->table("trans_invoice ti");

    //     //SUM(ti.total) as total_invoice,
        
    //     $builder->select("
    //         rk.id,
    //         rk.nama,
    //         COALESCE(SUM((
    //             SELECT SUM(tid.total)
    //             FROM trans_invoice_detail tid
    //             WHERE tid.id_invoice = ti.id
    //         )), 0) as total_invoice, 
    //         SUM(ti.grand_total) as total_invoice_bayar,
    //         COALESCE(SUM((
    //             SELECT SUM(xc.pay_item)
    //             FROM trans_customer_receipt_detail xc
    //             WHERE xc.id_invoice = ti.id
    //         )), 0) as pembayaran,
    //         COALESCE(SUM((
    //             SELECT SUM(tid.down_payment)
    //             FROM trans_invoice_detail tid
    //             WHERE tid.id_invoice = ti.id and tipe_id = 2
    //         )), 0) as total_down_payment,
    //         COALESCE(SUM((
    //             SELECT sum(tsou.harga_total)
    //             FROM trans_sales_order_ukuran tsou
    //             inner join trans_sales_order tso on tsou.id_sales_order = tso.id 
    //             inner join trans_invoice_detail tidt on tso.id = tidt.id_ref 
    //             WHERE tidt.id_invoice = ti.id and tidt.tipe_id = 2 and tsou.active = 1
    //         )), 0) as nilai_so,
    //         COALESCE(SUM((
    //             SELECT sum(tsu.harga_total)
    //             FROM trans_sample_ukuran tsu
    //             inner join trans_sample ts on tsu.id_sample = ts.id 
    //             inner join trans_invoice_detail tidt on ts.id = tidt.id_ref 
    //             WHERE tidt.id_invoice = ti.id and tidt.tipe_id = 1 and ts.active = 1
    //         )), 0) as nilai_sample,
    //     ");

    //     $builder->join("ref_konsumen rk", "rk.id = ti.id_konsumen", "left");

    //     if ($id == null or $id == "") {
            
    //         if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
    //             $builder->groupStart();
    //                 // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
    //                 // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
    //                 $builder->where('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
    //                 // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             $builder->groupEnd();
    //         }
    //         $builder->where("
    //             ti.total > 
    //             (
    //                 COALESCE((
    //                     SELECT SUM(tid.down_payment)
    //                     FROM trans_invoice_detail tid
    //                     WHERE tid.id_invoice = ti.id AND tid.tipe_id = 2
    //                 ), 0)
    //                 +
    //                 COALESCE((
    //                     SELECT SUM(xc.pay_item)
    //                     FROM trans_customer_receipt_detail xc
    //                     WHERE xc.id_invoice = ti.id
    //                 ), 0)
    //             )
    //         ", null, false);
    //          $builder->groupBy("rk.id, rk.nama");
    //         if (!empty($order)) {
    //             $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
    //         } else {
    //             $builder->orderBy("rk.nama asc");
    //         }

    //         if (empty($offset)) $offset = 0;
    //         if (empty($limit)) $limit = 10;

    //         $builder->limit($limit, $offset);


    //         $this->_data = $builder->get()->getResult();
    //     } else {
    //         $builder->where("ti.id", $id);

    //         $this->_data = $builder->get()->getRow();
    //     }
    //     return $this->_data;
    // }

    // function getDataInvCnt($filters = null, $params = null)
    // {
    //     $builder = $this->db->table("trans_invoice ti");

    //     $builder->select("COUNT(rk.id) as _cnt");

    //     $builder->join("ref_konsumen rk", "rk.id = ti.id_konsumen", "left");

    //     if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
    //         $builder->groupStart();
    //             // $builder->where('LOWER(ti.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             // $builder->orWhere('LOWER(ti.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             $builder->orWhere('LOWER(rk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             // $builder->orWhere('LOWER(ti.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
    //         $builder->groupEnd();
    //     }

    //     $builder->where("
    //             ti.total > 
    //             (
    //                 COALESCE((
    //                     SELECT SUM(tid.down_payment)
    //                     FROM trans_invoice_detail tid
    //                     WHERE tid.id_invoice = ti.id AND tid.tipe_id = 2
    //                 ), 0)
    //                 +
    //                 COALESCE((
    //                     SELECT SUM(xc.pay_item)
    //                     FROM trans_customer_receipt_detail xc
    //                     WHERE xc.id_invoice = ti.id
    //                 ), 0)
    //             )
    //         ", null, false);
    //     $builder->groupBy("rk.id, rk.nama");
    //     $this->_data = $builder->get()->getRow()->_cnt;

    //     return $this->_data;
    // }

    // function getDataPo($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    // {
    //     $builder = $this->db->table("trans_po_header ph");

    //     $builder->select(" ph.id, ph.po_no, ph.po_date as tgl_po, rv.nama, ph.date_exc, ph.total as total_bayar,
    //                        (SELECT sum(tpo.total_bayar)+sum(tpo.diskon) from trans_po_pembayaran_detail tpo where tpo.id_po = ph.id) as dibayar, COALESCE(ph.diskon, 0) as diskon, term.days");

    //     $builder->join("ref_vendor rv", "rv.id = ph.id_vendor", "left");
    //     $builder->join("ref_term term", "term.id = ph.id_term", "left");

    //     if ($id == null or $id == "") {
            
    //         if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
    //             $builder->groupStart();
    //                 $builder->where('LOWER(ph.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
    //                 $builder->orWhere('LOWER(ph.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
    //                 $builder->orWhere('LOWER(rv.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
    //                 $builder->orWhere('LOWER(ph.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             $builder->groupEnd();
    //         }

    //         $builder->where("ph.total > (SELECT sum(tpo.total_bayar)+sum(tpo.diskon) from trans_po_pembayaran_detail tpo where tpo.id_po = ph.id)");
    //         $builder->orWhere("ph.total_payment", 0); 
    //         $builder->where("ph.active", 1); 
    //         $builder->where("ph.approve_status", 1); 
    //         // $builder->where('COALESCE(ph.diskon, 0) + COALESCE(ph.total_payment, 0) < ph.total');

    //         if (!empty($order)) {
    //             $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
    //         } else {
    //             $builder->orderBy("ph.date_exc asc");
    //         }

    //         if (empty($offset)) $offset = 0;
    //         if (empty($limit)) $limit = 10;

    //         $builder->limit($limit, $offset);

    //         $this->_data = $builder->get()->getResult();
    //     } else {
    //         $builder->where("ph.id", $id);

    //         $this->_data = $builder->get()->getRow();
    //     }

    //     return $this->_data;
    // }
    // function getDataPoCnt($filters = null, $params = null)
    // {
    //     $builder = $this->db->table("trans_po_header ph");

    //     $builder->select("count(1) as _cnt");

    //     $builder->join("ref_vendor rv", "rv.id = ph.id_vendor", "left");

    //     if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
    //         $builder->groupStart();
    //             $builder->where('LOWER(ph.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             $builder->orWhere('LOWER(ph.tgl_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             $builder->orWhere('LOWER(rv.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
    //             $builder->orWhere('LOWER(ph.tgl_jatuh_tempo) LIKE', strtolower("%{$filters[0]['value']}%"));
    //         $builder->groupEnd();
    //     }

    //     $builder->where("ph.total > (SELECT sum(tpo.total_bayar) from trans_po_pembayaran_detail tpo where tpo.id_header = ph.id)"); // kondisi untuk PO yang belum lunas

    //     $this->_data = $builder->get()->getRow()->_cnt;

    //     return $this->_data;
    // }

    function getDataPoNew($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builderPoFiltered = $this->db->table('trans_po_header ph')
            ->select("
                ph.id,
                ph.id_vendor,
                rv.nama AS nama_vendor,
                ph.po_no,
                ph.po_date,
                ph.po_date_exp,
                ph.date_exc,
                ph.total::int AS total_bayar,
                COALESCE(ph.diskon::int, 0) AS diskon,
                --term.days,
                --term.name as name_term,
                (
                    SELECT SUM(tpo.total_bayar::int) + SUM(tpo.diskon::int)
                    FROM trans_po_pembayaran_detail tpo
                    WHERE tpo.id_po = ph.id
                ) AS dibayar
            ", false)
            ->join('ref_vendor rv', 'rv.id = ph.id_vendor', 'inner')
            // ->join('ref_term term', 'term.id = ph.id_term', 'inner')
            ->where('ph.active', 1)
            ->where('ph.approve_status', 1)
            ->groupStart()
                ->where("
                    FLOOR(ph.total) > (
                        SELECT SUM(tpo.total_bayar) + SUM(tpo.diskon)
                        FROM trans_po_pembayaran_detail tpo
                        WHERE tpo.id_po = ph.id
                    )
                    OR FLOOR(ph.total) > ph.total_payment
                ", null, false)
            ->groupEnd();

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $data = strtolower($filters[0]['value']);
            $builderPoFiltered->where("rv.nama ILIKE '%{$data}%'", null, false);
        }

        $builder = $this->db->newQuery()->fromSubquery($builderPoFiltered, 'po_filtered');

        // Query utama (aggregate)
        $builder->select("
            id_vendor,
            nama_vendor,
            po_date_exp,
            SUM(total_bayar) AS total_bayar,
            SUM(COALESCE(dibayar, 0)) AS dibayar,
            SUM(diskon) AS diskon
        ", false)
        ->groupBy(['id_vendor', 'nama_vendor', 'po_date_exp'])
        ->orderBy('nama_vendor', 'ASC');

        if (empty($offset)) $offset = 0;
        if (empty($limit)) $limit = 10;
        $builder->limit($limit, $offset);

        $query = $builder->get();
        $this->_data = $query->getResult();
        return $this->_data;

    }

    function getDataPoCntNew($filters = null, $params = null)
    {
        $builderPoFiltered = $this->db->table('trans_po_header ph')
            ->select("
                ph.id,
                ph.id_vendor,
                rv.nama AS nama_vendor,
                ph.po_no,
                ph.po_date,
                ph.date_exc,
                ph.total::int AS total_bayar,
                COALESCE(ph.diskon::int, 0) AS diskon,
                term.days,
                (
                    SELECT SUM(tpo.total_bayar::int) + SUM(tpo.diskon::int)
                    FROM trans_po_pembayaran_detail tpo
                    WHERE tpo.id_po = ph.id
                ) AS dibayar
            ", false)
            ->join('ref_vendor rv', 'rv.id = ph.id_vendor', 'inner')
            ->join('ref_term term', 'term.id = ph.id_term', 'inner')
            ->where('ph.active', 1)
            ->where('ph.approve_status', 1)
            ->groupStart()
                ->where("
                    FLOOR(ph.total) > (
                        SELECT SUM(tpo.total_bayar) + SUM(tpo.diskon)
                        FROM trans_po_pembayaran_detail tpo
                        WHERE tpo.id_po = ph.id
                    )
                    OR FLOOR(ph.total) > ph.total_payment
                ", null, false)
            ->groupEnd();

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $data = strtolower($filters[0]['value']);
            $builderPoFiltered->where("rv.nama ILIKE '%{$data}%'", null, false);
        }

        $builder = $this->db->newQuery()->fromSubquery($builderPoFiltered, 'po_filtered');

        // Query utama (aggregate)
        $builder->select("
            id_vendor
        ", false)
        ->groupBy('id_vendor, nama_vendor')
        ->orderBy('nama_vendor', 'ASC');

        $query = $builder->get();
        $this->_data = $query->getNumRows();

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
        $builder->where('bbx.id_kategori !=', 13);

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

    function getDataSaldo($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        
        $builder = $this->db->table("m_coa mc");

        $builder->select("mc.id, mc.kode, mc.nama,
                        COALESCE((
                            SELECT SUM(mmh.saldo)
                            FROM m_mutasi_history mmh 
                            WHERE mmh.coa_id = mc.id 
                            and mmh.month = {$params['month']}
                            and mmh.year = {$params['year']}
                        ), 0) AS saldo");

        $builder->where('LOWER(mc.kode) LIKE', '13%');

        $builder->where("(
            COALESCE((
                SELECT SUM(mmh.saldo)
                FROM m_mutasi_history mmh 
                WHERE mmh.coa_id = mc.id 
                AND mmh.month = {$params['month']}
                AND mmh.year = {$params['year']}
            ), 0) > 0
        )");

        // $builder->where("EXTRACT(MONTH FROM tbl.tgl_dp) = 10");
        // $builder->where("EXTRACT(YEAR FROM tbl.tgl_dp) = 2025"); 
        if ($id == null or $id == "") {
            
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(mc.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("mc.kode asc");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("mc.id", $id);

            $this->_data = $builder->get()->getRow();
        }
        return $this->_data;
    }

    function getDataSaldoAll($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        
        $sub = "
            COALESCE((
                SELECT SUM(mmh.saldo)
                FROM m_mutasi_history mmh
                WHERE mmh.coa_id = mc.id
                AND mmh.month = {$params['month']}
                AND mmh.year = {$params['year']}
            ), 0)
        ";

        // 1) Ambil data (dengan pagination)
        $builder = $this->db->table("m_coa mc");
        $builder->select("mc.id, mc.kode, mc.nama, {$sub} AS saldo", FALSE);
        $builder->where("LOWER(mc.kode) LIKE", "13%");
        $builder->orderBy("mc.kode", "asc", TRUE);
        $builder->limit($limit ?: 10, $offset ?: 0);
        $rows = $builder->get()->getResult();

        // 2) Ambil total semua saldo (tanpa limit)
        $builder2 = $this->db->table("m_coa mc");
        $builder2->select("SUM({$sub}) AS total_saldo", FALSE);
        $builder2->where("LOWER(mc.kode) LIKE", "13%");
        if (!empty($filters) && is_array($filters)) {
            $builder2->groupStart();
            $builder2->where('LOWER(mc.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder2->groupEnd();
        }
        $totalRow = $builder2->get()->getRow();
        $totalSaldo = $totalRow ? (float)$totalRow->total_saldo : 0;

        return $totalSaldo;
    }

    function getDataSaldoCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("m_coa mc");

        $builder->select("count(1) as _cnt");

        $builder->where('LOWER(mc.kode) LIKE', '13%');

         $builder->where("(
            COALESCE((
                SELECT SUM(mmh.saldo)
                FROM m_mutasi_history mmh 
                WHERE mmh.coa_id = mc.id 
                AND mmh.month = {$params['month']}
                AND mmh.year = {$params['year']}
            ), 0) > 0
        )");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
             $builder->groupStart();
                $builder->where('LOWER(mc.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        // $builder->where("EXTRACT(MONTH FROM tbl.tgl_dp) = 10");
        // $builder->where("EXTRACT(YEAR FROM tbl.tgl_dp) = 2025"); 

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

}