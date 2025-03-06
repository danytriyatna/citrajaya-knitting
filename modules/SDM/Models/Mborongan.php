<?php

namespace Modules\SDM\Models;

class Mborongan extends \App\Models\PrModel
{

    // protected $table = "sdm_absensi";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table("trans_produksi_operator pd");

        $builder->select("pd.tgl_transaksi, tp.keterangan_style, tp.keterangan, rp.nama_operator, pd.id_proses, pd.id_operator, jp.nama as proses, 
                          sum(pd.harga) as harga, sum(pd.qty) as qty, sum(pd.harga_total) as harga_total");

        $builder->join("trans_produksi tp", "tp.id = pd.id_produksi", "inner");
        $builder->join("ref_operator rp", "rp.id = pd.id_operator", "inner");
        $builder->join("_jenis_proses_produksi jp", "jp.id = pd.id_proses", "inner");

        if ($id == null or $id == "") {
            $builder->where('pd.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rp.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.nip) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }
            
            if(!empty($params['tgl_awal']) && !empty($params['tgl_akhir'])){
                $builder->where("pd.tgl_transaksi BETWEEN '".$params['tgl_awal']."' AND '".$params['tgl_akhir']."'");
            }

            if(!empty($params['id_operator'])){
                $builder->where('pd.id_operator', $params['id_operator']);
            }

            if(!empty($params['id_proses'])){
                $builder->where('pd.id_proses', $params['id_proses']);
            }

            $builder->where('(pd.active = 1 and tp.active = 1)');
            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("jp.nama");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->groupBy("pd.tgl_transaksi, tp.keterangan_style, rp.nama_operator, pd.id_proses, pd.id_operator, jp.nama, tp.keterangan");

            $builder->limit($limit, $offset);
           
            $this->_data = $builder->get()->getResult();
            // print_r($this->_data);exit;
        } else {
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table("trans_produksi_operator pd");

        $builder->select("count(1) as _cnt");

        $builder->join("trans_produksi tp", "tp.id = pd.id_produksi", "inner");
        $builder->join("ref_operator rp", "rp.id = pd.id_operator", "inner");
        $builder->join("_jenis_proses_produksi jp", "jp.id = pd.id_proses", "inner");

        $builder->where('(pd.active = 1 and tp.active = 1)');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(rp.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.nip) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if(!empty($params['tgl_awal']) && !empty($params['tgl_akhir'])){
            $builder->where("pd.tgl_transaksi BETWEEN '".$params['tgl_akhir']."' AND '".$params['tgl_akhir']."'", null, false);
        }

        if(!empty($params['id_operator'])){
            $builder->where('pd.id_operator', $params['id_operator']);
        }

        if(!empty($params['id_proses'])){
            $builder->where('pd.id_proses', $params['id_proses']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }


    function laporan_penggajian($params = null){
        $builder = $this->db->table($this->table . " sdm");
    
        $builder->select("rk.nip,
                          rk.full_name,
                          rk.posisi,
                          rk.id as id_karyawan,
                          COUNT(1) FILTER (WHERE sdm.status_kehadiran = 1) AS hadir,
                          COUNT(1) FILTER (WHERE sdm.status_kehadiran = 2) AS izin,
                          COUNT(1) FILTER (WHERE sdm.status_kehadiran = 3) AS sakit,
                          COUNT(1) FILTER (WHERE sdm.status_kehadiran = 4 OR sdm.status_kehadiran NOT IN (1,2,3)) AS alpha,
                          rk.upah_harian,
                          (COUNT(1) FILTER (WHERE sdm.status_kehadiran = 1) * rk.upah_harian) as gaji_harian,
                          -- Perbaikan SUM() dengan FILTER
                          COALESCE(SUM(sdm.durasi_kerja) FILTER (WHERE sdm.status_kehadiran = 1), 0) / 60 AS jam_kerja,
                          ROUND(COALESCE(SUM(sdm.durasi_kerja) FILTER (WHERE sdm.status_kehadiran = 1), 0) / 60) * rk.upah_jam AS gaji_jam,
                          rk.upah_lembur,
                          rk.upah_lembur_we,
                          rk.upah_jam,
                          SUM(COALESCE(sdm.bonus, 0)) as bonus,
                          SUM(COALESCE(sdm.potongan, 0)) as potongan,
                          SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 1) as lembur,
                          SUM(COALESCE(sdm.jml_lembur , 0)) FILTER (WHERE sdm.status_lembur = 2) as lembur_we,
                          (COALESCE(SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 1), 0) * rk.upah_lembur) as gaji_lembur,
                          (COALESCE(SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 2), 0) * rk.upah_lembur_we) as gaji_lembur_we");
    
        $builder->join("ref_karyawan rk", "sdm.id_karyawan = rk.id");
        $builder->where('sdm.active', 1);
    
        // Tambahkan kondisi WHERE untuk rentang tanggal jika parameter disediakan
        if (!empty($params['tgl_mulai']) && !empty($params['tgl_akhir'])) {
            $builder->where("sdm.tgl_absen >=", $params['tgl_mulai']);
            $builder->where("sdm.tgl_absen <=", $params['tgl_akhir']);
        }
    
        $builder->groupBy("rk.nip, rk.full_name, rk.posisi, rk.upah_harian, rk.upah_lembur, rk.upah_lembur_we, rk.id");
        $builder->orderBy("rk.nip ASC");
    
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
    
}
