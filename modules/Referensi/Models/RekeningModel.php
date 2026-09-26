<?php

namespace Modules\Referensi\Models;

class RekeningModel extends \App\Models\PrModel
{

    protected $table = "ref_rekening";
    protected $_data = null;
    protected $primaryKey = 'id';


    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " rk");

        // rk.coa_id: relasi ke m_coa, dipakai laporan Mutasi Keuangan (Mcoa::get_mutasi_export_all
        // dkk, filter "rr.coa_id") untuk menghubungkan tipe pembayaran ini ke akun COA-nya.
        $builder->select("rk.id, rk.rekening_no, rk.rekening_bank, rk.keterangan, rk.rekening_an,
                           rk.coa_id, mc.kode AS coa_kode, mc.nama AS coa_nama");
        $builder->join("m_coa mc", "mc.id = rk.coa_id", "left");

        if ($id == null or $id == "") {
            $builder->where('rk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rk.rekening_no) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.rekening_bank) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.rekening_an) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('id');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("rk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " rk");

        $builder->select("count(1) as _cnt");

        $builder->where('rk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(rk.rekening_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.rekening_bank) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.rekening_an) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
