<?php

namespace Modules\SDM\Models;

class Mabsensi extends \App\Models\PrModel
{

    protected $table = "sdm_absensi";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " sdm");

        $builder->select("sdm.id,  sdm.id_karyawan,  sdm.posisi,  sdm.tgl_absen,  sdm.jam_masuk,  sdm.status_kehadiran,  sdm.jam_keluar,  sdm.hari_hadir,  
                          sdm.keterangan_kehadiran,  sdm.status_lembur,  sdm.jml_lembur,  sdm.keterangan_lembur,  sdm.active, 
                          rk.id as id_karyawan_tbl, rk.nip, rk.full_name, rk.posisi, rk.upah_lembur, rk.upah_harian, rk.upah_lembur_we,
                          sdm.id_shift, sdm.jadwal_masuk, sdm.jadwal_pulang, sdm.potongan, sdm.bonus, sdm.durasi_kerja,
                          sdm.bonus_keterangan, sdm.potongan_keterangan,
                          sh.nama_shift, sh.jam_masuk as jam_masuk_shift, sh.jam_pulang as jam_pulang_shift");

        $builder->join("ref_karyawan rk", "sdm.id_karyawan = rk.id");
        $builder->join("m_shift sh", "sdm.id_shift = sh.id", "left");

        if ($id == null or $id == "") {
            $builder->where('sdm.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rk.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.nip) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }
            
            if(!empty($params['tgl_absen'])){
                $builder->where('sdm.tgl_absen', $params['tgl_absen']);
            }

            if(!empty($params['nip'])){
                $builder->where('rk.nip', $params['nip']);
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
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " sdm");

        $builder->select("count(1) as _cnt");

        $builder->join("ref_karyawan rk", "sdm.id_karyawan = rk.id");

        $builder->where('sdm.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(rk.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.nip) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.posisi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if(!empty($params['tgl_absen'])){
            $builder->where('sdm.tgl_absen', $params['tgl_absen']);
        }

        if(!empty($params['nip'])){
            $builder->where('rk.nip', $params['nip']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }


    function laporan_penggajian($params = null){
        $builder = $this->db->table($this->table . " sdm");

        if (!empty($params['type']) && $params['type'] == 2) {
            $builder->select([
                'rk.nip',
                'rk.full_name',
                'rk.posisi',
                'rk.id AS id_karyawan',
                'COUNT(sdm.id) FILTER (WHERE sdm.status_kehadiran = 1) AS hadir',
                'COUNT(sdm.id) FILTER (WHERE sdm.status_kehadiran = 2) AS izin',
                'COUNT(sdm.id) FILTER (WHERE sdm.status_kehadiran = 3) AS sakit',
                'COUNT(sdm.id) FILTER (WHERE sdm.status_kehadiran = 4 OR sdm.status_kehadiran NOT IN (1,2,3)) AS alpha',
                'rk.upah_harian',
                '(COUNT(sdm.id) FILTER (WHERE sdm.status_kehadiran = 1) * rk.upah_harian) AS gaji_harian',
                'COALESCE(SUM(sdm.durasi_kerja) FILTER (WHERE sdm.status_kehadiran = 1), 0) / 60 AS jam_kerja',
                'ROUND(COALESCE(SUM(sdm.durasi_kerja) FILTER (WHERE sdm.status_kehadiran = 1), 0) / 60) * rk.upah_jam AS gaji_jam',
                'rk.upah_lembur',
                'rk.upah_lembur_we',
                'rk.upah_jam',
                'SUM(COALESCE(sdm.bonus, 0)) AS bonus',
                'SUM(COALESCE(sdm.potongan, 0)) AS potongan',
                'SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 1) AS lembur',
                'SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 2) AS lembur_we',
                '(COALESCE(SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 1), 0) * rk.upah_lembur) AS gaji_lembur',
                '(COALESCE(SUM(COALESCE(sdm.jml_lembur, 0)) FILTER (WHERE sdm.status_lembur = 2), 0) * rk.upah_lembur_we) AS gaji_lembur_we',
                'COALESCE(th.harga_total, 0) AS harga_total'
            ]);
        }

        else {
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
        }
    
        $builder->join("ref_karyawan rk", "sdm.id_karyawan = rk.id");
        $builder->where('sdm.active', 1);

        if (!empty($params['type']) && $params['type'] == 2) {
            if (!empty($params['tgl_mulai']) && !empty($params['tgl_akhir'])) {
                $startDate = $params['tgl_mulai'];
                $endDate = $params['tgl_akhir'];
                $builder->join(
                    "(SELECT id_operator, COALESCE(SUM(harga_total), 0) AS harga_total 
                      FROM trans_produksi_operator 
                      WHERE tgl_transaksi BETWEEN '$startDate' AND '$endDate'
                      GROUP BY id_operator) th",
                    'rk.id_operator = th.id_operator',
                    'left'
                );
            }
            else {
                $builder->join(
                    "(SELECT id_operator, COALESCE(SUM(harga_total), 0) AS harga_total 
                      FROM trans_produksi_operator 
                      GROUP BY id_operator) th",
                    'rk.id_operator = th.id_operator',
                    'left'
                );
            }
            
            $builder->where("rk.type", $params['type']);
            $builder->groupBy('rk.nip, rk.full_name, rk.posisi, rk.id, rk.upah_harian, rk.upah_lembur, rk.upah_lembur_we, rk.upah_jam, th.harga_total');
        }
        else {
            $builder->where("rk.type", 1);
            $builder->orWhere("rk.type", null);
        }
    
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
