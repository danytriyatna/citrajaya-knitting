<?php

namespace Modules\SDM\Models;

class Mpenggajian extends \App\Models\PrModel
{

    protected $table = "sdm_gaji";
    protected $table2 = "sdm_gaji_detail";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " sdm");

        $builder->select("sdm.id, sdm.periode_awal, sdm.periode_akhir, sdm.status, sdm.keterangan, sdm.kode_gaji, sdm.type");

        if ($id == null or $id == "") {
            $builder->where('sdm.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(sdm.kode_gaji) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(sdm.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }
            
            if(!empty($params['periode_awal'])){
                $builder->where('sdm.periode_awal', $params['periode_awal']);
            }

            if(!empty($params['periode_akhir'])){
                $builder->where('sdm.periode_akhir', $params['periode_akhir']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("sdm.id desc");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("sdm.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " sdm");

        $builder->select("count(1) as _cnt");

        $builder->where('sdm.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(sdm.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }
 
        if(!empty($params['periode_awal'])){
            $builder->where('sdm.periode_awal', $params['periode_awal']);
        }

        if(!empty($params['periode_akhir'])){
            $builder->where('sdm.periode_akhir', $params['periode_akhir']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
    
    function getDataDet($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
       
        $builder = $this->db->table($this->table2 . " sdd");

        $builder->select("sdd.id, sdd.id_sdm_gaji, sdd.id_karyawan, sdd.nip, sdd.posisi, sdd.hadir, sdd.izin, sdd.sakit, sdd.alpha, sdd.gaji_harian, sdd.lembur,
                          sdd.bonus, sdd.potongan, sdd.durasi_kerja as jam_kerja,
                          sdd.lembur_we, sdd.uang_lembur, sdd.gaji, 
                          rk.full_name, rk.nip, rk.posisi, rk.no_hp, rk.alamat, sdd.bonus_keterangan, sdd.premi, sdd.terlambat, sdd.jml_sample, rk.nama_bank, rk.no_rekening, rk.email");

        $builder->join("ref_karyawan rk", "sdd.id_karyawan = rk.id");

        if ($id == null or $id == "") {
            $builder->where('sdd.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(sdd.nip) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(sdd.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }
            
            if(!empty($params['nip'])){
                $builder->where('sdd.nip', $params['nip']);
            }

            if(!empty($params['posisi'])){
                $builder->where('sdd.posisi', $params['posisi']);
            }


            if(!empty($params['id_sdm_gaji'])){
                $builder->where('sdd.id_sdm_gaji', $params['id_sdm_gaji']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("(rk.nip = '-'), rk.nip");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("sdd.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataDetCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " sdd");

        $builder->select("count(1) as _cnt");

        $builder->where('sdd.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(sdd.nip) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(sdd.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }
        
        if(!empty($params['nip'])){
            $builder->where('sdd.nip', $params['nip']);
        }

        if(!empty($params['posisi'])){
            $builder->where('sdd.posisi', $params['posisi']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateNo($prefix, $table, $kode)
    {
        $kd = $prefix;
        $builder = $this->db->table($table . ' a');
        $builder->select("LEFT($kode, 6) AS tgl, RIGHT( $kode, 5 ) AS kode ");

        $builder->orderBy('a.id', "DESC");
        $builder->limit(1);
        $query = $builder->get()->getRow();

        if ($query != NULL) {
            if ($query->tgl == $kd . date('y') . date('m')) {     //cek dulu apakah ada sudah ada tahun dan bulan di tabel.   
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

        $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT); // angka 3 menunjukkan jumlah digit angka 0
        $kodejadi = $kd . date('y') . date('m') . $kodemax;

        // hasilnya SOD24100001 dst.
        return $kodejadi;
    }
}
