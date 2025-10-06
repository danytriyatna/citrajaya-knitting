<?php

namespace Modules\Referensi\Models;

class KonsumenModel extends \App\Models\PrModel
{

    protected $table = "ref_konsumen";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " k");

        $builder->select("k.id, k.nama, k.alamat, k.no_hp, k.email, k.npwp, k.pic");

        if ($id == null or $id == "") {
            $builder->where('k.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(k.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(k.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(k.email) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('id', 'desc');
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



    // setiap konsumen dapat memiliki style nya masing masin dari transaksi sample ataupun sale order
    function getDataStyle($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("ref_konsumen_style s");

        $builder->select("s.id, s.kode_style, s.keterangan_style, s.id_konsumen");

        if ($id == null or $id == "") {
            $builder->where('s.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(s.kode_style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(s.keterangan_style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if(!empty($params['id_konsumen'])){
                $builder->where('s.id_konsumen', $params['id_konsumen']);
            }

            if(!empty($params['kode_style'])){
                $builder->where('s.kode_style', $params['kode_style']);
            }

            if(!empty($params['kata_kunci'])){
                $builder->groupStart();
                    $builder->where('LOWER(s.kode_style) LIKE', strtolower("%{$params['kata_kunci']}%"));
                    $builder->orWhere('LOWER(s.keterangan_style) LIKE', strtolower("%{$params['kata_kunci']}%"));
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
            $builder->where("s.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }
    
    function getDataStyleCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("ref_konsumen_style s");

        $builder->select("count(1) as _cnt");

        $builder->where('s.active = 1');

        if(!empty($params['id_konsumen'])){
            $builder->where('s.id_konsumen', $params['id_konsumen']);
        }

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(k.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->where('LOWER(s.kode_style) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(s.keterangan_style) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    // disini untuk mendetailkan kembali style dimana mempunyai harga pada setiap proses 
    // setiap konsumen dapat memiliki style nya masing masin dari transaksi sample ataupun sale order
    function getDataHarga($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("ref_konsumen_style_harga sh");

        $builder->select("sh.id, sh.id_konsumen, sh.id_konsumen_style, sh.id_proses, sh.harga_borongan,
                          rp.seq as urutan_proses, rp.nama as nama_proses, 
                          rk.kode_style, rk.keterangan_style");

        $builder->join("_jenis_proses_produksi rp", "rp.id = sh.id_proses", "inner");
        $builder->join("ref_konsumen_style rk", "rk.id = sh.id_konsumen_style", "inner");

        if ($id == null or $id == "") {
            $builder->where('sh.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.kode_style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if(!empty($params['id_konsumen'])){
                $builder->where('sh.id_konsumen', $params['id_konsumen']);
            }

            if(!empty($params['id_konsumen_style'])){
                $builder->where('sh.id_konsumen_style', $params['id_konsumen_style']);
            }

            if(!empty($params['id_proses'])){
                $builder->where('sh.id_proses', $params['id_proses']);
            }

            if(!empty($params['style'])){
                $builder->where('rk.keterangan_style', $params['style']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('sh.id');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("sh.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }
    
    function getDataHargaCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("ref_konsumen_style s");

        $builder->select("count(1) as _cnt");

        $builder->join("_jenis_proses_produksi rp", "rp.id = sh.id_proses", "inner");
        $builder->join("ref_konsumen_style rk", "rk.id = sh.id_konsumen_style", "inner");

        $builder->where('sh.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(rp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.kode_style) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if(!empty($params['id_konsumen'])){
            $builder->where('sh.id_konsumen', $params['id_konsumen']);
        }

        if(!empty($params['id_konsumen_style'])){
            $builder->where('sh.id_konsumen_style', $params['id_konsumen_style']);
        }

        if(!empty($params['id_proses'])){
            $builder->where('sh.id_proses', $params['id_proses']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
