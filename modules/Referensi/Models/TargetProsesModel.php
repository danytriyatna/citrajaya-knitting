<?php

namespace Modules\Referensi\Models;

use App\Models\PrModel;

class TargetProsesModel extends PrModel
{
    protected $table      = "ref_target_proses";
    protected $primaryKey = 'id';
    protected $_data      = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " tp");
        $builder->select("tp.id, tp.kode, tp.nama, tp.target_harian, tp.satuan, tp.warna_badge, tp.keterangan, tp.seq, tp.active, tp.created_at, tp.updated_at");

        if ($id === null || $id === "") {
            $builder->where('tp.active', 1);

            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(tp.kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(tp.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], true);
            } else {
                $builder->orderBy('tp.seq ASC, tp.kode ASC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 25;

            $builder->limit($limit, $offset);
            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("tp.id", $id);
            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    public function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " tp");
        $builder->select("COUNT(1) as _cnt");
        $builder->where('tp.active', 1);

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(tp.kode) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(tp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(tp.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        return (int)$builder->get()->getRow()->_cnt;
    }

    public function getActiveProsesList()
    {
        return $this->db->table($this->table)
            ->select("id, kode, nama, target_harian, satuan, warna_badge, keterangan, seq")
            ->where('active', 1)
            ->orderBy('seq ASC, kode ASC')
            ->get()->getResult();
    }
}
