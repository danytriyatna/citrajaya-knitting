<?php

namespace Modules\Keuangan\Models;

use App\Models\PrModel;

class Mtrans_akun extends PrModel
{
	protected $table                = 'trans_akun';
	protected $primaryKey           = 'id';
	protected $useAutoIncrement     = true;
	protected $protectFields        = false;

	protected $useTimestamps        = true;

	public $_data = '';

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table.' a');
        $builder->select("a.id as trans_akun_id, a.trans_akun_date, a.trans_akun_kode, a.ref_rekening_id, a.keterangan, a.ref_so_sp,
                          b.rekening_no, b.rekening_bank, a.total , a.status");

        $builder->join('ref_rekening b', "a.ref_rekening_id = b.id", "left");
       
        if ($id == null OR $id == "") {

            $builder->where("a.active = 1");

            if (!empty($filters)) {
                if (is_array($filters) && count($filters) >= 1) {
                    $builder->groupStart();
                        $builder->like('upper(a.trans_akun_date)', strtoupper($filters[0]['value']));
                        $builder->orLike('upper(a.trans_akun_kode)', strtoupper($filters[0]['value']));
                        $builder->orLike('upper(b.rekening_no)', strtoupper($filters[0]['value']));
                        $builder->orLike('upper(b.rekening_bank)', strtoupper($filters[0]['value']));
                    $builder->groupEnd();
                }
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('a.id DESC');
            }

            if(!empty($params['tahun'])){
                $builder->where("EXTRACT('YEAR' FROM a.trans_akun_date)", $params['tahun']);
            }

            if(!empty($params['bulan'])){
                $builder->where("EXTRACT('MONTH' FROM a.trans_akun_date)", $params['bulan']);
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

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table.' a');
        $builder->select('count(1) as _cnt');
        $builder->where("a.active = 1");

        $builder->join('ref_rekening b', "a.ref_rekening_id = b.id", "left");
        
        if (!empty($filters)) {
            if (is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                    $builder->like('upper(a.trans_akun_date)', strtoupper($filters[0]['value']));
                    $builder->orLike('upper(a.trans_akun_kode)', strtoupper($filters[0]['value']));
                    $builder->orLike('upper(b.rekening_no)', strtoupper($filters[0]['value']));
                    $builder->orLike('upper(b.rekening_bank)', strtoupper($filters[0]['value']));
                $builder->groupEnd();
            }
        }

        if(!empty($params['tahun'])){
            $builder->where("EXTRACT('YEAR' FROM a.trans_akun_date)", $params['tahun']);
        }

        if(!empty($params['bulan'])){
            $builder->where("EXTRACT('MONTH' FROM a.trans_akun_date)", $params['bulan']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;
        return $this->_data;
    }

    public function generateAutoNo()
    {
        $builder = $this->db->table('trans_akun');
        $builder->select('LEFT(trans_akun_kode, 9) AS tgl,RIGHT ( trans_akun_kode, 3 ) AS kode ');
        // $builder->orderBy('sl_order_id', "DESC");
        $builder->orderBy("id", "DESC");
        $builder->limit(1);
        $query = $builder->get()->getRow();
        
        if ($query != NULL) {
            if ($query->tgl == "TR." . date('Y') . date("m")) {     //cek dulu apakah ada sudah ada tahun dan bulan di tabel.   
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
        $kodemax = str_pad($kode, 3, "0", STR_PAD_LEFT); // angka 3 menunjukkan jumlah digit angka 0
        $kodejadi = "TR."  . date('Y') . date("m") . "." . $kodemax;
        // hasilnya SO.202108.0001 dst.
        return $kodejadi;
    }

    function get_tahun(){
        $builder = $this->db->table('trans_akun');
        $builder->select("EXTRACT ( YEAR FROM trans_akun_date ) as tahun");
        $builder->groupBy("EXTRACT ( YEAR FROM trans_akun_date )");

        $builder->orderBy("EXTRACT ( YEAR FROM trans_akun_date ) DESC");

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    public function get_nomor_sample_dan_sales()
    {
        // Ambil kode_sample dari tabel sample
        $builderSample = $this->db->table('trans_sample');
        $builderSample->select('kode_sample AS nomor');
        $querySample = $builderSample->getCompiledSelect();

        // Ambil kode_sales_order dari tabel sales
        $builderSales = $this->db->table('trans_sales_order');
        $builderSales->select('kode_sales_order AS nomor');
        $querySales = $builderSales->getCompiledSelect();

        // Gabungkan dengan UNION
        $queryGabungan = $this->db->query("$querySample UNION $querySales");

        return $queryGabungan->getResult(); // Atau getResultArray() kalau mau array
    }
}
