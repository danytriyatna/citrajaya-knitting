<?php

namespace Modules\Keuangan\Models;

use \App\Models\PrModel;

class Mcoa extends PrModel
{
	protected $table                = 'm_coa';
	protected $table2               = 'm_mutasi_history';
	protected $primaryKey           = 'id';
	protected $useAutoIncrement     = true;
	protected $protectFields        = false;

	protected $useTimestamps        = true;

	public $_data = '';

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $slc = null, $child = null, $parent = null, $parent_id = null)
    {
        $builder = $this->db->table($this->table.' a');
        $builder->select("a.id as coa_id, a.kode, a.parent_id, a.level, a.nama,
                          b.nama as parent_coa, b.kode as parent_kode, a.position");

        $builder->join($this->table.' b', "a.parent_id = b.id", "left");
       
        if ($id == null OR $id == "") {

            $builder->where("a.active = 1");

            if (!empty($filters)) {
                if (is_array($filters) && count($filters) >= 1) {
                    $builder->groupStart();
                        $builder->like('upper(a.kode)', strtoupper($filters[0]['value']));
                        $builder->orLike('upper(a.nama)', strtoupper($filters[0]['value']));
                        $builder->orLike('upper(b.kode)', strtoupper($filters[0]['value']));
                        $builder->orLike('upper(b.nama)', strtoupper($filters[0]['value']));
                    $builder->groupEnd();
                }
            }

            if(!empty($parent)){
                $builder->where('b.kode', $parent);
            }

            if(!empty($parent_id)){
                $builder->where('a.parent_id', $parent_id);
            }

            if(!empty($slc)){
                $builder->where("a.id != {$slc}");
                // $builder->where("a.level != 4");
                $builder->where("a.level = 1");
            }

            if(!empty($child)){
                if($child == 1){
                    $builder->where("a.parent_id IS NOT NULL");
                }else if($child == 2){
                    $builder->where("a.parent_id IS NULL");
                }
            }
            

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                // $builder->orderBy($this->primaryKey.' ASC');
                $builder->orderBy("CAST(a.kode as numeric) ASC");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;
            
            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("a." . $this->primaryKey, $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null)
    {
        $builder = $this->db->table($this->table.' a');
        $builder->select('count(1) as _cnt');
        $builder->where("a.active = 1");
        $builder->where("a.level = 1");
        if (!empty($filters)) {
            if (is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->like('upper(a.kode)', strtoupper($filters[0]['value']));
                    // $builder->orLike('upper(a.coa_nomor)', strtoupper($filters[0]['value']));
                $builder->groupEnd();
            }
        }

        $this->_data = $builder->get()->getRow()->_cnt;
        return $this->_data;
    }

    function cek_level($parent_id){
        $builder = $this->db->table($this->table.' a');
        $builder->select('a.level');
        $builder->where("a.active = 1");
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function get_mutasi($tahun = null){
        if(empty($tahun)){
            $tahun = date("Y");
        }
        $builder = $this->db->table($this->table.' ax');
        $builder->select("  ax.id,
                            ax.parent_id,
                            ax.kode,
                            ax.nama,
                            ax.level,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 1, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 1, {$tahun}) 
                            END) as bln1,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 2, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 2, {$tahun}) 
                            END) as bln2,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 3, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 3, {$tahun}) 
                            END) as bln3,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 4, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 4, {$tahun}) 
                            END) as bln4,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 5, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 5, {$tahun}) 
                            END) as bln5,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 6, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 6, {$tahun}) 
                            END) as bln6,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 7, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 7, {$tahun}) 
                            END) as bln7,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 8, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 8, {$tahun}) 
                            END) as bln8,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 9, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 9, {$tahun}) 
                            END) as bln9,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 10, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 10, {$tahun}) 
                            END) as bln10,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 11, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 11, {$tahun}) 
                            END) as bln11,
                            (CASE WHEN ax.parent_id IS NULL
                                  THEN get_jml_month_parent(ax.id, 12, {$tahun}) 
                                  ELSE get_jml_month(ax.id, 12, {$tahun}) 
                            END) as bln12
                            ");

                            // ,
                            // get_jml_month(ax.coa_id, '02', {$tahun}') as bln2,
                            // get_jml_month(ax.coa_id, '03', {$tahun}') as bln3,
                            // get_jml_month(ax.coa_id, '04', {$tahun}') as bln4,
                            // get_jml_month(ax.coa_id, '05', {$tahun}') as bln5,
                            // get_jml_month(ax.coa_id, '06', {$tahun}') as bln6,
                            // get_jml_month(ax.coa_id, '07', {$tahun}') as bln7,
                            // get_jml_month(ax.coa_id, '08', {$tahun}') as bln8,
                            // get_jml_month(ax.coa_id, '09', {$tahun}') as bln9,
                            // get_jml_month(ax.coa_id, '10', {$tahun}') as bln10,
                            // get_jml_month(ax.coa_id, '11', {$tahun}') as bln11,
                            // get_jml_month(ax.coa_id, '12', {$tahun}') as bln12
        $builder->where("ax.active = 1");
        $builder->where("(ax.kode NOT LIKE '%1000%' AND ax.kode NOT LIKE '%2000%' AND ax.kode NOT LIKE '%3000%' AND ax.kode NOT LIKE '%4000%')");
        // $builder->where("ax.parent_id is not null");
        $builder->orderBy("CAST(ax.kode as numeric) ASC");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_mutasi_export($from_date = null, $to_date = null, $ref_rekening = null){
        $builder = $this->db->table("trans_akun_det tad");
        $builder->select("ta.trans_akun_date, tad.coa_id, ta.trans_akun_kode, mc.kode, mc.nama, tad.keterangan, tad.jumlah, ta.ref_rekening_id, CONCAT(rr.rekening_no , ' - ', rr.rekening_bank) AS tipe_bayar ");
        $builder->join("trans_akun ta", "ta.id = tad.trans_akun_id", "left");
        $builder->join("m_coa mc", "mc.id = tad.coa_id", "left");
        $builder->join("ref_rekening rr", "rr.id = ta.ref_rekening_id", "left");
        $builder->where("tad.active = 1");
        $builder->where("ta.trans_akun_date BETWEEN '$from_date' AND '$to_date'");
        if (!empty($ref_rekening)) {
            $builder->where("ta.ref_rekening_id", $ref_rekening);
        }
        else {
            $builder->orderBy("ta.ref_rekening_id ASC");
        }
        $builder->orderBy("ta.trans_akun_date ASC");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_mutasi_export_all($month = null, $year = null, $ref_masuk = null){
        $builder = $this->db->table("trans_akun_det tad");
        $builder->select("'Beban Biaya' as type, ta.trans_akun_date as tgl_transaksi, tad.coa_id, ta.trans_akun_kode, mc.kode, mc.nama, tad.keterangan, tad.jumlah, ta.ref_rekening_id, CONCAT(rr.rekening_no , ' - ', rr.rekening_bank) AS tipe_bayar ");
        $builder->join("trans_akun ta", "ta.id = tad.trans_akun_id", "left");
        $builder->join("m_coa mc", "mc.id = tad.coa_id", "left");
        $builder->join("ref_rekening rr", "rr.id = ta.ref_rekening_id", "left");
        $builder->where("tad.active = 1");
        $builder->where('EXTRACT(MONTH FROM ta.trans_akun_date)', $month);
        $builder->where('EXTRACT(YEAR FROM ta.trans_akun_date)', $year);
        if (!empty($ref_masuk)) {
            $builder->groupStart();
                $builder->where("tad.coa_id", $ref_masuk);
                $builder->orWhere("rr.coa_id", $ref_masuk);
            $builder->groupEnd();
        }

        $builder->orderBy("ta.trans_akun_date ASC");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_mutasi_export_so($month = null, $year = null, $ref_masuk = null){
        $builder = $this->db->table("trans_sales_order tso");
        $builder->select("'Sales Order' as type,tso.type_dp, CONCAT('Penerimaan Penjualan' , ' - ', rk.nama) AS keterangan, CONCAT(rr.rekening_no , ' - ', rr.rekening_bank) AS tipe_bayar, tso.tgl_dp as tgl_transaksi, tso.kode_sales_order, tso.uang_dp");
        $builder->join("ref_rekening rr", "rr.id = tso.type_dp", "left");
        $builder->join("ref_konsumen rk", "rk.id = tso.id_konsumen", "left");
        // $builder->join("m_coa mc", "mc.id = rr.coa_id", "left");
        $builder->where("tso.active = 1");
        $builder->where('EXTRACT(MONTH FROM tso.tgl_dp)', $month);
        $builder->where('EXTRACT(YEAR FROM tso.tgl_dp)', $year);
        if (!empty($ref_masuk)) {
            $builder->where("rr.coa_id", $ref_masuk);
        }
        

        $builder->orderBy("tso.tgl_dp ASC");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_mutasi_export_cr($month = null, $year = null, $ref_masuk = null){
        $builder = $this->db->table("trans_customer_receipt tcr");
        $builder->select("'Customer Receipt' as type,tcr.id_rekening, CONCAT('Penerimaan Penjualan' , ' - ', rk.nama) AS keterangan, CONCAT(rr.rekening_no , ' - ', rr.rekening_bank) AS tipe_bayar, tcr.tgl_transaksi, tcr.kode_cr, tcr.total_bayar");
        $builder->join("ref_rekening rr", "rr.id = tcr.id_rekening", "left");
        $builder->join("ref_konsumen rk", "rk.id = tcr.id_konsumen", "left");
        // $builder->join("m_coa mc", "mc.id = rr.coa_id", "left");
        $builder->where("tcr.active = 1");
        $builder->where('EXTRACT(MONTH FROM tcr.tgl_transaksi)', $month);
        $builder->where('EXTRACT(YEAR FROM tcr.tgl_transaksi)', $year);
        if (!empty($ref_masuk)) {
            $builder->where("rr.coa_id", $ref_masuk);
        }

        $builder->orderBy("tcr.tgl_transaksi ASC");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_mutasi_export_pb($month = null, $year = null, $ref_masuk = null){
        $builder = $this->db->table("trans_po_pembayaran tpp");
        $builder->select("'Pembayaran' as type, tpp.id_rek, CONCAT('Pembayaran Pembelian' , ' - ', rv.nama) AS keterangan, CONCAT(rr.rekening_no , ' - ', rr.rekening_bank) AS tipe_bayar, tpp.pay_date as tgl_transaksi, tpp.pay_no, tpp.total_bayar");
        $builder->join("ref_rekening rr", "rr.id = tpp.id_rek", "left");
        $builder->join("ref_vendor rv", "rv.id = tpp.id_vendor", "left");
        // $builder->join("m_coa mc", "mc.id = rr.coa_id", "left");
        $builder->where("tpp.active = 1");
        $builder->where('EXTRACT(MONTH FROM tpp.pay_date)', $month);
        $builder->where('EXTRACT(YEAR FROM tpp.pay_date)', $year);
        if (!empty($ref_masuk)) {
            $builder->where("rr.coa_id", $ref_masuk);
        }

        $builder->orderBy("tpp.pay_date ASC");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function get_mutasi_history($month = null, $year = null, $ref_masuk = null){
        $builder = $this->db->table("m_mutasi_history mh");
        $builder->select("mh.id, mh.coa_id, mh.month, mh.year, mh.saldo");
        $builder->where('mh.month', $month);
        $builder->where('mh.year', $year);
        if (!empty($ref_masuk)) {
            $builder->where('mh.coa_id', $ref_masuk);
        }
        
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }
}
