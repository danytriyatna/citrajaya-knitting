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

        $builder->select("ky.id, ky.id_perusahaan, ky.nip, ky.full_name, ky.email, ky.posisi, ky.kategori_posisi, ky.id_shift, ms.nama_shift as nama_shift_karyawan, ky.tgl_bergabung, ky.jenis_kelamin,
                            ky.tgl_lahir, ky.tempat_lahir, ky.no_hp, ky.alamat, ky.upah_lembur, ky.upah_harian, rp.nama_perusahaan, ro.nama_operator,
                            ky.upah_lembur_we, ky.upah_jam, ky.premi_kehadiran, ky.type, ky.id_operator, cbx.file_name, ky.nama_bank, ky.no_rekening, ky.active,
                            ky.tgl_mulai_kontrak, ky.durasi_kontrak_bulan, ky.tgl_selesai_kontrak, ky.file_kontrak_id, ky.status_kontrak, fk.file_name as file_kontrak_name, fk.file_name_origin as file_kontrak_origin,
                            ky.ttd_pihak1_file, ky.ttd_pihak1_tgl, ky.ttd_pihak2_file, ky.ttd_pihak2_tgl, ky.ttd_mengetahui_file, ky.ttd_mengetahui_tgl ");
        $builder->join("_files cbx", "ky.gambar_id = cbx.id", "left");
        $builder->join("_files fk", "ky.file_kontrak_id = fk.id", "left");
        $builder->join("ref_perusahaan rp", "ky.id_perusahaan = rp.id", "left");
        $builder->join("ref_operator ro", "ky.id_operator = ro.id", "left");
        $builder->join("m_shift ms", "ky.id_shift = ms.id", "left");
        if ($id == null or $id == "") {
            // CATATAN (2026-08-08): active punya 3 nilai - 1=Aktif, 0=Nonaktif,
            // 2=Dihapus (soft-delete, datanya tetap ada di DB tapi TIDAK PERNAH
            // ditampilkan di list karyawan manapun, beda dgn Nonaktif yang masih
            // tampil di Master Data Karyawan dgn badge & bisa diaktifkan lagi).
            $builder->where('ky.active !=', 2);
            // Default-nya cuma karyawan aktif (dipakai dropdown/lookup di modul
            // lain, mis. SDM/Absensi) - kecuali pemanggil eksplisit minta lihat
            // aktif+nonaktif lewat $params['show_all_status'] (dipakai oleh
            // list Master Data Karyawan yang punya toggle Aktif/Nonaktif).
            if (empty($params['show_all_status'])) {
                $builder->where('ky.active = 1');
            }
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

            if (!empty($params['filter_kontrak']) && $params['filter_kontrak'] !== 'all') {
                $todayStr = date('Y-m-d');
                $next30Days = date('Y-m-d', strtotime('+30 days'));
                $builder->where('ky.type', 1);
                if ($params['filter_kontrak'] === 'warning') {
                    // Akan habis dalam 30 hari (antara hari ini s/d 30 hari ke depan)
                    $builder->where('ky.tgl_selesai_kontrak >=', $todayStr);
                    $builder->where('ky.tgl_selesai_kontrak <=', $next30Days);
                } elseif ($params['filter_kontrak'] === 'expired') {
                    // Sudah habis (tgl_selesai_kontrak < hari ini)
                    $builder->where('ky.tgl_selesai_kontrak IS NOT NULL');
                    $builder->where('ky.tgl_selesai_kontrak <', $todayStr);
                } elseif ($params['filter_kontrak'] === 'active') {
                    // Masih aman / aktif (> 30 hari ke depan)
                    $builder->where('ky.tgl_selesai_kontrak >', $next30Days);
                } elseif ($params['filter_kontrak'] === 'none') {
                    // Belum ada kontrak (internal)
                    $builder->where('ky.tgl_selesai_kontrak IS NULL');
                }
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

        // Sama seperti getData() - active=2 (Dihapus) tidak pernah dihitung.
        $builder->where('ky.active !=', 2);
        if (empty($params['show_all_status'])) {
            $builder->where('ky.active = 1');
        }

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

        if(!empty($params['type'])){
            $builder->where('ky.type', $params['type']);
        }

        if (!empty($params['filter_kontrak']) && $params['filter_kontrak'] !== 'all') {
            $todayStr = date('Y-m-d');
            $next30Days = date('Y-m-d', strtotime('+30 days'));
            $builder->where('ky.type', 1);
            if ($params['filter_kontrak'] === 'warning') {
                // Akan habis dalam 30 hari (antara hari ini s/d 30 hari ke depan)
                $builder->where('ky.tgl_selesai_kontrak >=', $todayStr);
                $builder->where('ky.tgl_selesai_kontrak <=', $next30Days);
            } elseif ($params['filter_kontrak'] === 'expired') {
                // Sudah habis (tgl_selesai_kontrak < hari ini)
                $builder->where('ky.tgl_selesai_kontrak IS NOT NULL');
                $builder->where('ky.tgl_selesai_kontrak <', $todayStr);
            } elseif ($params['filter_kontrak'] === 'active') {
                // Masih aman / aktif (> 30 hari ke depan)
                $builder->where('ky.tgl_selesai_kontrak >', $next30Days);
            } elseif ($params['filter_kontrak'] === 'none') {
                // Belum ada kontrak (internal)
                $builder->where('ky.tgl_selesai_kontrak IS NULL');
            }
        }

        if(!empty($params['not_nip'])){
            $builder->whereNotIn('ky.nip', $params['not_nip']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
