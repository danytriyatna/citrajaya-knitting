<?php

namespace Modules\Referensi\Models;

class GudangModel extends \App\Models\PrModel
{

    protected $table = "ref_gudang";
    protected $tableCMT = "ref_operator";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tableCMT . " abx", "uk.id_cmt = abx.id", "left");
        $builder->join("ref_perusahaan pbx", "uk.id_perusahaan = pbx.id", "left");
        $builder->select("uk.id, uk.nama_gudang, uk.keterangan, uk.tipe, abx.nama_operator, abx.id as id_cmt,
                          uk.id_perusahaan, pbx.nama_perusahaan");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(uk.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_perusahaan'])) {
                $builder->where('uk.id_perusahaan', $params['id_perusahaan']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('uk.id');
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
            $builder->where('LOWER(uk.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    // Dipakai Receive Item untuk default Warehouse ("Gudang Utama") saat
    // baris detail otomatis terisi dari PO - user tetap bisa mengubahnya.
    // Dicari berdasarkan nama (bukan id hardcode) supaya tidak rapuh kalau
    // datanya beda antar environment.
    function getGudangUtama()
    {
        $builder = $this->db->table($this->table);
        $builder->select("id, nama_gudang");
        $builder->where('active', 1);
        $builder->where('LOWER(nama_gudang) LIKE', 'gudang utama');
        $builder->orderBy('id');
        $builder->limit(1);
        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }
}
