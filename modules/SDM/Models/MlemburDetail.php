<?php

namespace Modules\SDM\Models;

class MlemburDetail extends \App\Models\PrModel
{
    protected $table = "sdm_lembur_detail";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " d");

        $builder->select("
            d.id, d.id_header, d.id_karyawan, d.id_absensi, d.pekerjaan, d.target, d.satuan_target,
            d.realisasi, d.persentase, d.jam_masuk, d.jam_keluar, d.status_kehadiran, d.status_lembur,
            d.jam_lembur_absen, d.jam_lembur_dihitung, d.catatan, d.active,
            rk.nip, rk.full_name, rk.posisi, rk.id_perusahaan, rp.nama_perusahaan
        ");
        $builder->join("ref_karyawan rk", "d.id_karyawan = rk.id", "inner");
        $builder->join("ref_perusahaan rp", "rk.id_perusahaan = rp.id", "left");

        if ($id == null or $id == "") {
            $builder->where('d.active', 1);

            if (!empty($params['id_header'])) {
                $builder->where('d.id_header', (int) $params['id_header']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("d.id", "ASC");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 99999;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("d.id", (int) $id);
            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataByHeader($idHeader)
    {
        $builder = $this->db->table($this->table . " d");
        $builder->select("
            d.id, d.id_header, d.id_karyawan, d.id_absensi, d.pekerjaan, d.target, d.satuan_target,
            d.realisasi, d.persentase, d.jam_masuk, d.jam_keluar, d.status_kehadiran, d.status_lembur,
            d.jam_lembur_absen, d.jam_lembur_dihitung, d.catatan, d.active,
            rk.nip, rk.full_name, rk.posisi
        ");
        $builder->join("ref_karyawan rk", "d.id_karyawan = rk.id", "inner");
        $builder->where('d.id_header', (int) $idHeader);
        $builder->where('d.active', 1);
        $builder->orderBy('rk.nip', 'ASC');

        return $builder->get()->getResult();
    }

    /**
     * Cari record absensi karyawan pada tanggal tertentu
     */
    function getAbsensiKaryawan($idKaryawan, $tanggal)
    {
        $builder = $this->db->table("sdm_absensi sa");
        $builder->select("
            sa.id as id_absensi,
            sa.id_karyawan,
            sa.tgl_absen,
            sa.jam_masuk,
            sa.jam_keluar,
            sa.status_kehadiran,
            sa.status_lembur,
            COALESCE(sa.jml_lembur, 0) as jml_lembur
        ");
        $builder->where('sa.id_karyawan', (int) $idKaryawan);
        $builder->where('sa.tgl_absen', $tanggal);
        $builder->where('sa.active', 1);

        return $builder->get()->getRow();
    }
}
