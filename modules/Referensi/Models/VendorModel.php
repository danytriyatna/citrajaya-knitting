<?php

namespace Modules\Referensi\Models;

class VendorModel extends \App\Models\PrModel
{

    protected $table = "ref_vendor";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " k");

        $builder->select("k.id, k.nama, k.alamat, k.no_hp, k.email, k.pic");

        if ($id == null or $id == "") {
            $builder->where('k.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(k.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(k.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(k.email) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(k.pic) LIKE', strtolower("%{$filters[0]['value']}%"));
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
            $builder->where("k.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " k");

        $builder->select("count(1) as _cnt");

        $builder->where('k.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(k.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(k.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(k.email) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
