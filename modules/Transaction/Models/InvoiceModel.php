<?php

namespace Modules\Transaction\Models;

class InvoiceModel extends \App\Models\PrModel
{

    protected $table = "trans_invoice";
    protected $table2 = "trans_invoice_detail";
    protected $table3 = "trans_invoice_delivery";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.tgl_transaksi,  abx.kode_invoice, abx.keterangan, abx.id_konsumen,
                            abx.status, abx.total, abx.diskon,  abx.pph, abx.pph_total, abx.grand_total,
                            bbx.nama as konsumen_nama, abx.tgl_jatuh_tempo, abx.rentang_waktu, 
                            COALESCE(SUM(cbx.pay_item),0) as pay_item, 
                            COALESCE((
                                SELECT SUM(tid.down_payment)
                                FROM trans_invoice_detail tid
                                WHERE tid.id_invoice = abx.id
                            ),0) as total_down_payment,
                            COALESCE((
                                SELECT SUM(tso.pengiriman)
                                FROM trans_invoice_detail tid
                                INNER JOIN trans_sales_order tso on tso.id = tid.id_ref
                                WHERE tid.id_invoice = abx.id
                            ),0) as pengiriman");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("trans_customer_receipt_detail cbx", "cbx.id_invoice = abx.id", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(abx.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
            }
            if (!empty($params['status'])) {
                $builder->where('abx.status', $params['status']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id desc');
            }

            $builder->groupBy("
                abx.id, abx.tgl_transaksi, abx.kode_invoice, abx.keterangan, abx.id_konsumen,
                abx.status, abx.total, abx.diskon, abx.pph, abx.pph_total, abx.grand_total,
                bbx.nama, abx.tgl_jatuh_tempo, abx.rentang_waktu
            ");

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $builder->groupBy("
                abx.id, abx.tgl_transaksi, abx.kode_invoice, abx.keterangan, abx.id_konsumen,
                abx.status, abx.total, abx.diskon, abx.pph, abx.pph_total, abx.grand_total,
                bbx.nama, abx.tgl_jatuh_tempo, abx.rentang_waktu
            ");

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");
        
        $builder->select("count(1) as _cnt");
        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
                $builder->where('LOWER(abx.kode_invoice) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetail($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " abx");

        $builder->select(" abx.id, abx.id_invoice,  abx.id_ref, abx.kode_ref, abx.tipe_id,
                            abx.qty, abx.qty_do,  abx.total, abx.down_payment, abx.grand_total,
                            (case when abx.tipe_id = 1 then ts.kode_sample else tso.kode_sales_order end) as ref_kode,
                            (case when abx.tipe_id = 1 then ts.tgl_transaksi else tso.tgl_transaksi end) as ref_tgl");

        $builder->join("trans_sample_det ts", "ts.id = abx.id_ref and abx.tipe_id = 1", "left");
        $builder->join("trans_sales_order_det tso", "tso.id = abx.id_ref and abx.tipe_id = 2", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(abx.kode_sample) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(bbx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_invoice'])) {
                $builder->where('abx.id_invoice', $params['id_invoice']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataDelivery($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table3 . " abx");

        $builder->select(" abx.id, abx.id_invoice,  abx.id_invoice_detail, abx.id_delivery, abx.qty,
                            abx.total, abx.active,
                            (case when abx.tipe_id = 1 then ts.kode_sample else tso.kode_sales_order end) as ref_kode,
                            (case when abx.tipe_id = 1 then ts.tgl_transaksi else tso.tgl_transaksi end) as ref_tgl");

        if ($id == null or $id == "") {

            if (!empty($params['id_invoice'])) {
                $builder->where('abx.id_invoice', $params['id_invoice']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataDetailCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " abx");
        
        $builder->select("count(1) as _cnt");
        $builder->join("trans_sample_det ts", "ts.id = abx.id_ref and abx.tipe_id = 1", "left");
        $builder->join("trans_sales_order_det tso", "tso.id = abx.id_ref and abx.tipe_id = 2", "left");
        $builder->where('abx.active = 1');

        $builder->groupStart();
            $builder->where('LOWER(abx.kode_sample) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(bbx.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
        $builder->groupEnd();

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetailUpdate($id_invoice = null, $id_ref = null, $tipe_id = null)
    {
        $builder = $this->db->table($this->table2 . " abx");

        $builder->select(" abx.id, abx.id_invoice,  abx.id_ref, abx.kode_ref, abx.tipe_id,
                            abx.qty, abx.qty_do,  abx.total, abx.down_payment, abx.grand_total,
                            (case when abx.tipe_id = 1 then ts.kode_sample else tso.kode_sales_order end) as ref_kode,
                            (case when abx.tipe_id = 1 then ts.tgl_transaksi else tso.tgl_transaksi end) as ref_tgl");

        $builder->join("trans_sample ts", "ts.id = abx.id_ref and abx.tipe_id = 1", "left");
        $builder->join("trans_sales_order tso", "tso.id = abx.id_ref and abx.tipe_id = 2", "left");

        $builder->where("abx.id_invoice", $id_invoice);
        $builder->where("abx.id_ref", $id_ref);
        $builder->where("abx.tipe_id", $tipe_id);

        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }

    function getDataDetailNew($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " abx");

        $builder->select(" abx.id, abx.id_invoice,  abx.id_ref, abx.kode_ref, abx.tipe_id,
                            abx.qty, abx.qty_do,  abx.total, abx.down_payment, abx.grand_total,
                            (case when abx.tipe_id = 1 then ts.kode_sample else tso.kode_sales_order end) as ref_kode,
                            (case when abx.tipe_id = 1 then ts.deskripsi else tso.deskripsi end) as deskripsi,
                            (case when abx.tipe_id = 1 then ts.tgl_transaksi else tso.tgl_transaksi end) as tgl_transaksi,
                            (case when abx.tipe_id = 1 then ts.tgl_deadline else tso.tgl_deadline end) as tgl_deadline,
                            (case when abx.tipe_id = 1 then ts.tgl_transaksi else tso.tgl_transaksi end) as ref_tgl,
                            (case when abx.tipe_id = 1 then ts.id_konsumen else tso.id_konsumen end) as id_konsumen,
                            (case when abx.tipe_id = 1 then kons.nama else kon.nama end) as buyer,
                            (case when abx.tipe_id = 1 then kons.alamat else kon.alamat end) as alamat_buyer,
                            (case when abx.tipe_id = 1 then kons.no_hp else kon.no_hp end) as no_hp_buyer,
                            (case when abx.tipe_id = 1 then ts.style else tso.style end) as style,
                            tso.uang_dp, tso.uang_dp_2, tso.tgl_dp, tso.tgl_dp_2, tso.pengiriman, tso.type_dp, tso.type_dp_2, rek.rekening_no, rek.rekening_bank, rek_2.rekening_no as rekening_no_2, rek_2.rekening_bank as rekening_bank_2
                            ");

        $builder->join("trans_sample ts", "ts.id = abx.id_ref and abx.tipe_id = 1", "left");
        $builder->join("trans_sales_order tso", "tso.id = abx.id_ref and abx.tipe_id = 2", "left");
        $builder->join("ref_rekening rek", "rek.id = tso.type_dp", "left");
        $builder->join("ref_rekening rek_2", "rek_2.id = tso.type_dp_2", "left");
        $builder->join("ref_konsumen kon", "kon.id = tso.id_konsumen", "left");
        $builder->join("ref_konsumen kons", "kons.id = ts.id_konsumen", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->where('LOWER(ts.kode_sample) LIKE', strtolower("%{$filters[0]['value']}%"));
                    $builder->orWhere('LOWER(tso.kode_sales_order) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_invoice'])) {
                $builder->where('abx.id_invoice', $params['id_invoice']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function get_walkorder_konsumen($params){
        $builder = $this->db->table('trans_walkorder tw');
        $builder->select("  tw.id,
                            tw.tgl_transaksi,
                            tw.kode_walkorder,
                            tw.tipe_id,
                            tw.ref_kode,
                            tw.status,
                            tw.keterangan_style,
                            tw.qty,
                            rk.nama as konsumen_nama,
                            
                            ts.id as sample_id,
                            ts.kode_sample,
                            ts.qty as qty_sample,
                            ts.total_harga as total_sample,
                            ts.uang_dp as dp_sample,

                            tso.id as so_id,
                            tso.kode_sales_order,
                            (
                                SELECT SUM(xx.qty)
                                FROM trans_sales_order_ukuran xx
                                INNER JOIN trans_sales_order_det x ON x.id = xx.id_sales_order_det
                                WHERE x.id_sales_order = tso.id
                                GROUP BY x.id_sales_order
                            ) AS qty_so,
                            tso.total_harga as total_so,
                            tso.uang_dp as dp_so,
                            SUM(td.qty) FILTER (WHERE td.id IS NOT NULL) OVER (PARTITION BY tw.id) AS deliver_qty
                        ");
        $builder->join("trans_sample ts", "ts.id = tw.ref_id and tw.tipe_id = 1", "left");
        $builder->join("trans_sales_order tso", "tso.id = tw.ref_id and tw.tipe_id = 2", "left");
        $builder->join("ref_konsumen rk", "tw.id_konsumen = rk.id", "inner");
        $builder->join("trans_delivery td", "td.id_walkorder = tw.id", "inner join");

        $builder->where("tw.id_konsumen", $params['konsumen_id']);

        $builder->orderBy('tw.id desc');
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_walkorder_konsumen_ori_new($params){


        $whereExist_deliv = "";

        if(!empty($params['id_walkorder'])){
            $whereExist_deliv = "and EXISTS (
                            SELECT 1
                            FROM trans_delivery tdd
                            WHERE tdd.id_walkorder = xtb.id_walkorder and tdd.active = 1 and tdd.invoice_status = false
                        )";
        }

        $sql = "
            select 
                xtb.*,
                rk.nama as konsumen_nama
            from (
                SELECT
                    2 as tipe_id,
                    tso.id,
                    tso.kode_sales_order as kode,
                    tso.id_konsumen,
                    tso.style as keterangan_style,
                    tso.tgl_transaksi,
                    sum(coalesce(tsou.qty, 0)) as qty,
                    sum(coalesce(tsou.harga_total, 0)) as total_harga,
                    coalesce(tso.uang_dp, 0) as uang_dp, 
                    coalesce(tso.uang_dp_2, 0) as uang_dp_2, 
                    coalesce((select sum(sd.qty) from trans_delivery sd inner join trans_walkorder tw on tw.id = sd.id_walkorder where tw.tipe_id = 2 and tw.ref_id = tso.id and sd.invoice_status = false and sd.status = 2 ),0) as qty_dlv,
                    coalesce((
                        select sum(tdp.qty_do * tdp.harga_satuan)
                        from trans_delivery td
                        inner join trans_delivery_prod tdp on tdp.id_delivery = td.id
                        where td.id_walkorder = tw.id and td.invoice_status = false and td.status = 2
                    ),0) as total_harga_delivery,
                    (
                                select string_agg(sd.id::text, ',')
                                from trans_delivery sd
                                inner join trans_walkorder tw2 on tw2.id = sd.id_walkorder
                                where tw2.tipe_id = 2 
                                and tw2.ref_id = tso.id 
                                and sd.invoice_status = false and sd.status = 2
                            ) as list_delivery,
                                        tw.id as id_walkorder
                                    FROM
                                        trans_sales_order_ukuran tsou
                                        inner join trans_sales_order tso on tso.id = tsou.id_sales_order
                                        inner join trans_walkorder tw on tw.ref_id = tso.id and tw.tipe_id = 2
                                        group by 
                                            tso.id, tso.kode_sales_order,
                                            tso.id_konsumen, tso.style,  tso.tgl_transaksi,
                                            tso.uang_dp, tso.uang_dp_2, tw.id 
                                    union all 
                                    select 
                                        1 as tipe_id,
                                        ts.id,
                                        ts.kode_sample as kode,
                                        ts.id_konsumen, 
                                        ts.style as keterangan_style,
                                        ts.tgl_transaksi,
                                        sum(coalesce(tsu.qty, 0)) as qty,
                                        sum(coalesce(tsu.harga_total, 0)) as total_harga,
                                        coalesce(ts.uang_dp, 0) as uang_dp,
                                        coalesce(ts.uang_dp_2, 0) as uang_dp_2,
                                        coalesce((select sum(sd.qty) from trans_delivery sd inner join trans_walkorder tw on tw.id = sd.id_walkorder where tw.tipe_id = 1 and tw.ref_id = ts.id 
                                        and sd.invoice_status = false and sd.status = 2),0) as qty_dlv,
                                        coalesce((
                        select sum(tdp.qty_do * tdp.harga_satuan)
                        from trans_delivery td
                        inner join trans_delivery_prod tdp on tdp.id_delivery = td.id
                        where td.id_walkorder = tw.id and td.invoice_status = false and td.status = 2
                    ),0) as total_harga_delivery,
                    (
                                select string_agg(sd.id::text, ',')
                                from trans_delivery sd
                                inner join trans_walkorder tw2 on tw2.id = sd.id_walkorder
                                where tw2.tipe_id = 1 
                                and tw2.ref_id = ts.id 
                                and sd.invoice_status = false and sd.status = 2
                            ) as list_delivery,
                    tw.id as id_walkorder
                from 
                    trans_sample_ukuran tsu
                inner join trans_sample ts on ts.id = tsu.id_sample
                inner join trans_walkorder tw on tw.ref_id = ts.id and tw.tipe_id = 1
                group by 
                   ts.id,
                    ts.kode_sample,
                    ts.id_konsumen, 
                    ts.style,
                    ts.tgl_transaksi,
                    ts.uang_dp, ts.uang_dp_2, tw.id 
            ) xtb 
            inner join ref_konsumen rk on  xtb.id_konsumen = rk.id
            where xtb.id_konsumen = {$params['id_konsumen']} 
            {$whereExist_deliv}
            order by xtb.tgl_transaksi desc
        ";
        $query = $this->db->query($sql);

        $this->_data = $query->getResult();

        return $this->_data;
    }

    function get_walkorder_konsumen_ori($params){

        $sql = "
            select 
                xtb.*,
                rk.nama as konsumen_nama
            from (
                SELECT
                    2 as tipe_id,
                    tso.id,
                    tso.kode_sales_order as kode,
                    tso.id_konsumen,
                    tso.style as keterangan_style,
                    tso.tgl_transaksi,
                    sum(coalesce(tsou.qty, 0)) as qty,
                    sum(coalesce(tsou.harga_total, 0)) as total_harga,
                    coalesce(tso.uang_dp, 0) as uang_dp, 
                    coalesce(tso.uang_dp_2, 0) as uang_dp_2, 
                    coalesce((select sum(sd.qty) from trans_delivery sd inner join trans_walkorder tw on tw.id = sd.id_walkorder where tw.tipe_id = 2 and tw.ref_id = tso.id 
                    and sd.invoice_status = true and sd.id_invoice = {$params['id_invoice']} ),0) as qty_dlv,
                    coalesce((
                        select sum(tdp.qty_do * tdp.harga_satuan)
                        from trans_delivery td
                        inner join trans_delivery_prod tdp on tdp.id_delivery = td.id
                        where td.id_walkorder = tw.id and td.invoice_status = true and td.id_invoice = {$params['id_invoice']}
                    ),0) as total_harga_delivery,
                    (
                                select string_agg(sd.id::text, ',')
                                from trans_delivery sd
                                inner join trans_walkorder tw2 on tw2.id = sd.id_walkorder
                                where tw2.tipe_id = 2 
                                and tw2.ref_id = tso.id 
                                and sd.invoice_status = true and sd.id_invoice = {$params['id_invoice']}
                            ) as list_delivery,
                                        tw.id as id_walkorder
                                    FROM
                                        trans_sales_order_ukuran tsou
                                        inner join trans_sales_order tso on tso.id = tsou.id_sales_order
                                        inner join trans_walkorder tw on tw.ref_id = tso.id and tw.tipe_id = 2
                                        group by 
                                            tso.id, tso.kode_sales_order,
                                            tso.id_konsumen, tso.style,  tso.tgl_transaksi,
                                            tso.uang_dp, tso.uang_dp_2, tw.id 
                                    union all 
                                    select 
                                        1 as tipe_id,
                                        ts.id,
                                        ts.kode_sample as kode,
                                        ts.id_konsumen, 
                                        ts.style as keterangan_style,
                                        ts.tgl_transaksi,
                                        sum(coalesce(tsu.qty, 0)) as qty,
                                        sum(coalesce(tsu.harga_total, 0)) as total_harga,
                                        coalesce(ts.uang_dp, 0) as uang_dp,
                                        coalesce(ts.uang_dp_2, 0) as uang_dp_2,
                                        coalesce((select sum(sd.qty) from trans_delivery sd inner join trans_walkorder tw on tw.id = sd.id_walkorder where tw.tipe_id = 1 and tw.ref_id = ts.id 
                                        and sd.invoice_status = true and sd.id_invoice = {$params['id_invoice']}),0) as qty_dlv,
                                        coalesce((
                        select sum(tdp.qty_do * tdp.harga_satuan)
                        from trans_delivery td
                        inner join trans_delivery_prod tdp on tdp.id_delivery = td.id
                        where td.id_walkorder = tw.id and td.invoice_status = true and td.id_invoice = {$params['id_invoice']}
                    ),0) as total_harga_delivery,
                    (
                                select string_agg(sd.id::text, ',')
                                from trans_delivery sd
                                inner join trans_walkorder tw2 on tw2.id = sd.id_walkorder
                                where tw2.tipe_id = 1 
                                and tw2.ref_id = ts.id 
                                and sd.invoice_status = true and sd.id_invoice = {$params['id_invoice']}
                            ) as list_delivery,
                    tw.id as id_walkorder
                from 
                    trans_sample_ukuran tsu
                inner join trans_sample ts on ts.id = tsu.id_sample
                inner join trans_walkorder tw on tw.ref_id = ts.id and tw.tipe_id = 1
                group by 
                   ts.id,
                    ts.kode_sample,
                    ts.id_konsumen, 
                    ts.style,
                    ts.tgl_transaksi,
                    ts.uang_dp, ts.uang_dp_2, tw.id 
            ) xtb 
            inner join ref_konsumen rk on  xtb.id_konsumen = rk.id
            where xtb.id_konsumen = {$params['id_konsumen']}
            and EXISTS (
                            SELECT 1
                            FROM trans_invoice_detail tdx
                            WHERE tdx.id_invoice = {$params['id_invoice']} AND tdx.id_ref = xtb.id AND tdx.tipe_id = xtb.tipe_id
                        )
             and EXISTS (
                            SELECT 1
                            FROM trans_delivery tdd
                            WHERE tdd.id_walkorder = xtb.id_walkorder and tdd.active = 1 and tdd.invoice_status = true
                        )
            order by xtb.tgl_transaksi desc
        ";
        $query = $this->db->query($sql);

        $this->_data = $query->getResult();

        return $this->_data;
    }

    function getDetail_delivery($params){
        $id_walkorder = $params['id_walkorder'];
         // Dynamic Columns
         $col1 = "";
         $col2 = "";
         $col3 = "";
         $ukuranArr = explode(",", $params['ukuran']);
         foreach ($ukuranArr as $item) {
             $preg = preg_match('/^[a-zA-Z_]+$/', $item) ? $item : "\"$item\"";
             $col1 .= ($col1 == "") ? "coalesce(tbl.$preg,0) as $preg" : ",coalesce(tbl.$preg,0) as $preg";
             $col2 .= ($col2 == "") ? "$preg Int" : ",$preg Int";
         }

         $whr = "";
         if(!empty($params['get'])){
            $whr = "AND tbl.id_delivery NOT IN (SELECT cx.id_delivery FROM trans_invoice_delivery cx)";
         }

        $sql = "
            select 
                *,
                (select sum(xt.qty) from trans_delivery_detail xt where xt.id_delivery = tbl.id_delivery and xt.ref_detail_id = tbl.ref_detail_id) as qty_do,
                (select sum(xt.harga_satuan * xd.qty) from trans_delivery_prod xt 
                                             inner join trans_delivery_detail xd ON xt.id_delivery = xd.id_delivery  and xt.id_ukuran = xd.id_ukuran and xt.ref_detail_id = xd.ref_detail_id 
                                             where xt.id_delivery = tbl.id_delivery and xt.ref_detail_id = tbl.ref_detail_id) as total_harga
            from
                CROSSTAB(
                     'select 
                        twpu.ref_detail_id,
                        twpu.id_delivery,
                        twp.delivery_kode,
                        rw.kode_warna,
                        tw.tipe_id,
                        rk.key_ukuran,
                        COALESCE(twpu.qty, 0) as qty_prod
                    from trans_delivery_prod twpu
                    inner join trans_delivery twp on twp.id = twpu.id_delivery
                    inner join trans_walkorder tw on tw.id = twp.id_walkorder
                    inner join ref_ukuran rk on rk.id = twpu.id_ukuran
                    LEFT JOIN trans_sample_det ts ON ts.id = twpu.ref_detail_id AND tw.tipe_id = 1
                    LEFT JOIN trans_sales_order_det tso ON tso.id = twpu.ref_detail_id AND tw.tipe_id = 2
                    left join ref_warna rw on rw.id = (case when tw.tipe_id = 1 then ts.id_warna_1 when tw.tipe_id = 2 then tso.id_warna_1 else -1 end)
                    where tw.id = {$id_walkorder}
                    order by twpu.ref_detail_id, twpu.id_ukuran',
                'select key_ukuran from ref_ukuran rx where rx.active = 1 order by rx.seq asc'
            ) as tbl (ref_detail_id int, id_delivery int, delivery_kode varchar, kode_warna varchar, tipe_id int, {$col2})
             WHERE 1 = 1 {$whr}
        ";



        $query = $this->db->query($sql);

        $this->_data = $query->getResult();

        return $this->_data;
    }

    function get_export($from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.tgl_transaksi,  abx.kode_invoice,
                            abx.total, abx.grand_total,
                            bbx.nama as konsumen_nama, abx.tgl_jatuh_tempo, abx.rentang_waktu, COALESCE(SUM(cbx.pay_item),0) as pay_item, COALESCE((
                                SELECT SUM(tid.down_payment)
                                FROM trans_invoice_detail tid
                                WHERE tid.id_invoice = abx.id
                            ),0) as total_down_payment");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("trans_customer_receipt_detail cbx", "cbx.id_invoice = abx.id", "left");

        $builder->where("abx.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");

        $builder->groupBy("
                abx.id, abx.tgl_transaksi, abx.kode_invoice, abx.keterangan, abx.id_konsumen,
                abx.status, abx.total, abx.diskon, abx.pph, abx.pph_total, abx.grand_total,
                bbx.nama, abx.tgl_jatuh_tempo, abx.rentang_waktu
            ");
       
        $builder->orderBy("abx.tgl_transaksi", 'desc');
        
        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }
}