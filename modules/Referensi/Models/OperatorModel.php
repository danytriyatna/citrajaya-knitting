<?php

namespace Modules\Referensi\Models;

class OperatorModel extends \App\Models\PrModel
{

    protected $table = "ref_operator";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");

        $builder->select("uk.id, uk.nama_operator, uk.alamat, uk.no_hp, uk.tgl_bergabung, uk.harga");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');

            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(uk.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.no_hp) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->whereIn('uk.id', function($subquery) {
                        $subquery->select('id_operator')
                                ->from('ref_karyawan')
                                ->groupStart()
                                    ->where('id_perusahaan', 1)
                                    ->orWhere('id_perusahaan IS NULL')
                                ->groupEnd()
                                ->where('id_operator IS NOT NULL');
                    });
                } else {
                    $builder->whereIn('uk.id', function($subquery) use ($params) {
                        $subquery->select('id_operator')
                                ->from('ref_karyawan')
                                ->where('id_perusahaan', $params['id_perusahaan'])
                                ->where('id_operator IS NOT NULL');
                    });
                }
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

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(uk.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.no_hp) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->whereIn('uk.id', function($subquery) {
                        $subquery->select('id_operator')
                                ->from('ref_karyawan')
                                ->groupStart()
                                    ->where('id_perusahaan', 1)
                                    ->orWhere('id_perusahaan IS NULL')
                                ->groupEnd()
                                ->where('id_operator IS NOT NULL');
                    });
                } else {
                    $builder->whereIn('uk.id', function($subquery) use ($params) {
                        $subquery->select('id_operator')
                                ->from('ref_karyawan')
                                ->where('id_perusahaan', $params['id_perusahaan'])
                                ->where('id_operator IS NOT NULL');
                    });
                }
            }
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
