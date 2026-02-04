<?php

namespace Modules\Referensi\Models;

class UkuranModel extends \App\Models\PrModel
{

    protected $table = "ref_ukuran";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");

        $builder->select("uk.id, uk.kode_ukuran, uk.keterangan, uk.key_ukuran, uk.seq");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(uk.kode_ukuran) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if(!empty($params['key_ukuran'])){
                $builder->where('uk.key_ukuran', $params['key_ukuran']);
            }

            if(!empty($params['kode_ukuran'])){
                $builder->whereIn('uk.kode_ukuran', $params['kode_ukuran']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('seq asc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataUkuranByKey($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");

        $builder->select("uk.id, uk.kode_ukuran, uk.keterangan, uk.key_ukuran, uk.seq");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if(!empty($params['key_ukuran'])){
                $builder->whereIn('uk.key_ukuran', $params['key_ukuran']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('seq asc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }
    
    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");

        $builder->select("count(1) as _cnt");

        $builder->where('uk.active = 1');

        if(!empty($params['key_ukuran'])){
            $builder->where('uk.key_ukuran', $params['key_ukuran']);
        }


        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(k.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->where('LOWER(uk.kode_ukuran) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
