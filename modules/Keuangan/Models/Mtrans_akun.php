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

            if (!empty($filters) && is_array($filters) && count($filters) >= 1 && !empty($filters[0]['value'])) {
                $val = strtolower($filters[0]['value']);
                $builder->groupStart();
                    $builder->where('LOWER(a.trans_akun_kode) LIKE', "%{$val}%");
                    $builder->orWhere('LOWER(a.keterangan) LIKE', "%{$val}%");
                    $builder->orWhere('LOWER(b.rekening_no) LIKE', "%{$val}%");
                    $builder->orWhere('LOWER(b.rekening_bank) LIKE', "%{$val}%");
                    $builder->orWhere('CAST(a.trans_akun_date AS TEXT) LIKE', "%{$val}%");
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('a.id DESC');
            }

            if(!empty($params['tahun']) && !empty($params['bulan'])){
                $bulan = str_pad($params['bulan'], 2, "0", STR_PAD_LEFT);
                $tahun = $params['tahun'];
                $startDate = "{$tahun}-{$bulan}-01";
                $endDate = date("Y-m-t", strtotime($startDate));
                $builder->where("a.trans_akun_date >=", $startDate);
                $builder->where("a.trans_akun_date <=", $endDate);
            } elseif(!empty($params['tahun'])){
                $builder->where("a.trans_akun_date >=", "{$params['tahun']}-01-01");
                $builder->where("a.trans_akun_date <=", "{$params['tahun']}-12-31");
            }

            if(!empty($params['tipe_transaksi'])){
                $builder->where("a.tipe_transaksi", $params['tipe_transaksi']);
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

        $hasSearch = (!empty($filters) && is_array($filters) && count($filters) >= 1 && !empty($filters[0]['value']));
        if ($hasSearch) {
            $builder->join('ref_rekening b', "a.ref_rekening_id = b.id", "left");
            $val = strtolower($filters[0]['value']);
            $builder->groupStart();
                $builder->where('LOWER(a.trans_akun_kode) LIKE', "%{$val}%");
                $builder->orWhere('LOWER(a.keterangan) LIKE', "%{$val}%");
                $builder->orWhere('LOWER(b.rekening_no) LIKE', "%{$val}%");
                $builder->orWhere('LOWER(b.rekening_bank) LIKE', "%{$val}%");
                $builder->orWhere('CAST(a.trans_akun_date AS TEXT) LIKE', "%{$val}%");
            $builder->groupEnd();
        }

        if(!empty($params['tahun']) && !empty($params['bulan'])){
            $bulan = str_pad($params['bulan'], 2, "0", STR_PAD_LEFT);
            $tahun = $params['tahun'];
            $startDate = "{$tahun}-{$bulan}-01";
            $endDate = date("Y-m-t", strtotime($startDate));
            $builder->where("a.trans_akun_date >=", $startDate);
            $builder->where("a.trans_akun_date <=", $endDate);
        } elseif(!empty($params['tahun'])){
            $builder->where("a.trans_akun_date >=", "{$params['tahun']}-01-01");
            $builder->where("a.trans_akun_date <=", "{$params['tahun']}-12-31");
        }

        if(!empty($params['tipe_transaksi'])){
            $builder->where("a.tipe_transaksi", $params['tipe_transaksi']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;
        return $this->_data;
    }

    public function generateAutoNo($prefix = 'KK.')
    {
        $prefixLen = strlen($prefix);
        $builder = $this->db->table('trans_akun');
        $builder->select("LEFT(trans_akun_kode, " . ($prefixLen + 6) . ") AS tgl, RIGHT(trans_akun_kode, 3) AS kode");
        $builder->where("LEFT(trans_akun_kode, {$prefixLen})", $prefix);
        $builder->orderBy("id", "DESC");
        $builder->limit(1);
        $query = $builder->get()->getRow();
        
        $tglTarget = $prefix . date('Y') . date("m");
        if ($query != NULL) {
            if ($query->tgl == $tglTarget) {
                $kode = intval($query->kode) + 1;
            } else {
                $kode = 1;
            }
        } else {
            $kode = 1;
        }
        $kodemax = str_pad($kode, 3, "0", STR_PAD_LEFT);
        $kodejadi = $prefix . date('Y') . date("m") . "." . $kodemax;
        return $kodejadi;
    }

    function get_tahun(){
        $curYear = (int)date('Y');
        $years = [];
        for ($y = $curYear + 1; $y >= $curYear - 4; $y--) {
            $obj = new \stdClass();
            $obj->tahun = $y;
            $years[] = $obj;
        }
        return $years;
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
