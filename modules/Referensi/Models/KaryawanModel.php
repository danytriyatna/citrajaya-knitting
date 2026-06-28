<?php

namespace Modules\Referensi\Models;

class KaryawanModel extends \App\Models\PrModel
{

    protected $table = "ref_karyawan";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " ky");

        $builder->select("ky.id, ky.id_perusahaan, ky.nip, ky.full_name, ky.email, ky.posisi, ky.tgl_bergabung, ky.jenis_kelamin, 
                            ky.tgl_lahir, ky.tempat_lahir, ky.no_hp, ky.alamat, ky.upah_lembur, ky.upah_harian, rp.nama_perusahaan, ro.nama_operator,
                            ky.upah_lembur_we, ky.upah_jam, ky.premi_kehadiran, ky.type, ky.id_operator, cbx.file_name, ky.nama_bank, ky.no_rekening ");
        $builder->join("_files cbx", "ky.gambar_id = cbx.id", "left");
        $builder->join("ref_perusahaan rp", "ky.id_perusahaan = rp.id", "left");
        $builder->join("ref_operator ro", "ky.id_operator = ro.id", "left");
        if ($id == null or $id == "") {
            $builder->where('ky.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(ky.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ky.email) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ky.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ky.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rp.nama_perusahaan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ro.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->groupStart();
                        $builder->where('ky.id_perusahaan', 1);
                        $builder->orWhere('ky.id_perusahaan IS NULL');
                    $builder->groupEnd();
                } else {
                    $builder->where('ky.id_perusahaan', $params['id_perusahaan']);
                }
            }

            if(!empty($params['nip'])){
                $builder->where('ky.nip', $params['nip']);
            }
            if(!empty($params['type'])){
                $builder->where('ky.type', $params['type']);
            }

            if(!empty($params['not_nip'])){
                $builder->whereNotIn('ky.nip', $params['not_nip']);
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
            $builder->where("ky.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " ky");

        $builder->select("count(1) as _cnt");

        $builder->where('ky.active = 1');

        $builder->join("ref_perusahaan rp", "ky.id_perusahaan = rp.id", "left");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(ky.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ky.email) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ky.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ky.alamat) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rp.nama_perusahaan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if (!empty($params['id_perusahaan'])) {
            if ($params['id_perusahaan'] == 1) {
                $builder->groupStart();
                    $builder->where('ky.id_perusahaan', 1);
                    $builder->orWhere('ky.id_perusahaan IS NULL');
                $builder->groupEnd();
            } else {
                $builder->where('ky.id_perusahaan', $params['id_perusahaan']);
            }
        }

        if(!empty($params['nip'])){
            $builder->where('ky.nip', $params['nip']);
        }

        if(!empty($params['not_nip'])){
            $builder->whereNotIn('ky.nip', $params['not_nip']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
