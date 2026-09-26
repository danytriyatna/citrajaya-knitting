<?php

namespace Modules\SDM\Models;

class Mlembur extends \App\Models\PrModel
{
    protected $table = "sdm_lembur_header";
    protected $tableDetail = "sdm_lembur_detail";
    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " h");

        $builder->select("
            h.id, h.kode_lembur, h.tanggal, h.id_perusahaan, h.keterangan, h.status,
            h.approved1_by, h.approved1_at, h.approved2_by, h.approved2_at,
            h.created_at, h.created_by,
            rp.nama_perusahaan,
            u1.full_name as approver1_name,
            u2.full_name as approver2_name,
            (SELECT COUNT(d.id) FROM sdm_lembur_detail d WHERE d.id_header = h.id AND d.active = 1) as total_karyawan,
            (SELECT COALESCE(SUM(d.target), 0) FROM sdm_lembur_detail d WHERE d.id_header = h.id AND d.active = 1) as total_target,
            (SELECT COALESCE(SUM(d.realisasi), 0) FROM sdm_lembur_detail d WHERE d.id_header = h.id AND d.active = 1) as total_realisasi,
            (SELECT COALESCE(SUM(d.jam_lembur_absen), 0) FROM sdm_lembur_detail d WHERE d.id_header = h.id AND d.active = 1) as total_jam_absen,
            (SELECT COALESCE(SUM(d.jam_lembur_dihitung), 0) FROM sdm_lembur_detail d WHERE d.id_header = h.id AND d.active = 1) as total_jam_dihitung
        ");
        $builder->join("ref_perusahaan rp", "h.id_perusahaan = rp.id", "left");
        $builder->join("sec_user u1", "h.approved1_by = u1.id", "left");
        $builder->join("sec_user u2", "h.approved2_by = u2.id", "left");

        if ($id == null or $id == "") {
            $builder->where('h.active', 1);

            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(h.kode_lembur) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(h.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rp.nama_perusahaan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->groupStart();
                    $builder->where('h.id_perusahaan', 1);
                    $builder->orWhere('h.id_perusahaan IS NULL');
                    $builder->groupEnd();
                } else {
                    $builder->where('h.id_perusahaan', $params['id_perusahaan']);
                }
            }

            if (!empty($params['tgl_awal'])) {
                $builder->where('h.tanggal >=', $params['tgl_awal']);
            }

            if (!empty($params['tgl_akhir'])) {
                $builder->where('h.tanggal <=', $params['tgl_akhir']);
            }

            if (isset($params['status']) && $params['status'] !== '') {
                $builder->where('h.status', (int) $params['status']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy("h.id", "DESC");
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("h.id", (int) $id);
            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " h");
        $builder->select("COUNT(1) as _cnt");
        $builder->join("ref_perusahaan rp", "h.id_perusahaan = rp.id", "left");
        $builder->where('h.active', 1);

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(h.kode_lembur) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(h.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rp.nama_perusahaan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if (!empty($params['id_perusahaan'])) {
            if ($params['id_perusahaan'] == 1) {
                $builder->groupStart();
                $builder->where('h.id_perusahaan', 1);
                $builder->orWhere('h.id_perusahaan IS NULL');
                $builder->groupEnd();
            } else {
                $builder->where('h.id_perusahaan', $params['id_perusahaan']);
            }
        }

        if (!empty($params['tgl_awal'])) {
            $builder->where('h.tanggal >=', $params['tgl_awal']);
        }

        if (!empty($params['tgl_akhir'])) {
            $builder->where('h.tanggal <=', $params['tgl_akhir']);
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $builder->where('h.status', (int) $params['status']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;
        return $this->_data;
    }

    function generateKodeLembur($tanggal = null)
    {
        $tanggal = !empty($tanggal) ? $tanggal : date('Y-m-d');
        $prefix = "LBR" . date('ym', strtotime($tanggal));

        $rows = $this->db->query("SELECT kode_lembur FROM sdm_lembur_header WHERE kode_lembur LIKE '{$prefix}%'")->getResult();
        $maxNum = 0;
        foreach ($rows as $r) {
            $num = (int) preg_replace('/\D/', '', substr($r->kode_lembur, strlen($prefix)));
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        $nextNum = $maxNum + 1;
        $docCode = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        while (true) {
            $chk = $this->db->query("SELECT 1 FROM sdm_lembur_header WHERE kode_lembur = ?", [$docCode])->getRow();
            if (!$chk) break;
            $nextNum++;
            $docCode = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        }

        return $docCode;
    }

    function trxInsertUpdateRecord($dataHeader, $dataDetail, $id = null)
    {
        $this->db->transBegin();
        try {
            if (empty($id)) {
                if (empty($dataHeader['kode_lembur'])) {
                    $dataHeader['kode_lembur'] = $this->generateKodeLembur($dataHeader['tanggal']);
                }
                $dataHeader['created_at'] = date('Y-m-d H:i:s');
                $dataHeader['created_by'] = session()->get('id');
                $dataHeader['active'] = 1;

                $this->db->table($this->table)->insert($dataHeader);
                $id = $this->db->insertID();
            } else {
                $dataHeader['updated_at'] = date('Y-m-d H:i:s');
                $dataHeader['updated_by'] = session()->get('id');

                $this->db->table($this->table)->where('id', $id)->update($dataHeader);
            }

            // Hapus detail lama jika update
            $this->db->table($this->tableDetail)->where('id_header', $id)->delete();

            // Insert detail baru
            if (!empty($dataDetail) && is_array($dataDetail)) {
                foreach ($dataDetail as $row) {
                    $target = (float) ($row['target'] ?? 0);
                    $realisasi = (float) ($row['realisasi'] ?? 0);
                    $persentase = 0;
                    if ($target > 0) {
                        $persentase = min(100.00, round(($realisasi / $target) * 100, 2));
                    }
                    $jamLemburAbsen = (float) ($row['jam_lembur_absen'] ?? 0);
                    $jamLemburDihitung = round($jamLemburAbsen * ($persentase / 100), 2);

                    $jamMasuk = (!empty($row['jam_masuk']) && $row['jam_masuk'] !== '-') ? trim($row['jam_masuk']) : null;
                    $jamKeluar = (!empty($row['jam_keluar']) && $row['jam_keluar'] !== '-') ? trim($row['jam_keluar']) : null;

                    $insertDetail = [
                        'id_header'           => $id,
                        'id_karyawan'         => (int) $row['id_karyawan'],
                        'id_absensi'          => !empty($row['id_absensi']) ? (int) $row['id_absensi'] : null,
                        'pekerjaan'           => $row['pekerjaan'] ?? '',
                        'target'              => $target,
                        'satuan_target'       => !empty($row['satuan_target']) ? $row['satuan_target'] : 'Pcs',
                        'realisasi'           => $realisasi,
                        'persentase'          => $persentase,
                        'jam_masuk'           => $jamMasuk,
                        'jam_keluar'          => $jamKeluar,
                        'status_kehadiran'    => !empty($row['status_kehadiran']) ? (int) $row['status_kehadiran'] : null,
                        'status_lembur'       => !empty($row['status_lembur']) ? (int) $row['status_lembur'] : null,
                        'jam_lembur_absen'    => $jamLemburAbsen,
                        'jam_lembur_dihitung' => $jamLemburDihitung,
                        'catatan'             => $row['catatan'] ?? null,
                        'active'              => 1
                    ];

                    $this->db->table($this->tableDetail)->insert($insertDetail);
                }
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();
            return $id;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in Mlembur::trxInsertUpdateRecord: ' . $e->getMessage());
            return false;
        }
    }

    function approve1($id, $userId)
    {
        return $this->db->table($this->table)
            ->where('id', (int) $id)
            ->where('status', 0)
            ->update([
                'status'        => 1,
                'approved1_by'  => (int) $userId,
                'approved1_at'  => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => (int) $userId
            ]);
    }

    function approve2($id, $userId)
    {
        $this->db->transBegin();
        try {
            // 1. Ambil data header & detail
            $header = $this->db->table($this->table)->where('id', (int) $id)->get()->getRow();
            if (!$header) {
                $this->db->transRollback();
                return false;
            }

            $details = $this->db->table($this->tableDetail)
                ->where('id_header', (int) $id)
                ->where('active', 1)
                ->get()
                ->getResult();

            // 2. Update status header menjadi 2 (Approved 2)
            $this->db->table($this->table)
                ->where('id', (int) $id)
                ->update([
                    'status'        => 2,
                    'approved2_by'  => (int) $userId,
                    'approved2_at'  => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                    'updated_by'    => (int) $userId
                ]);

            // 3. Sync update ke sdm_absensi (kolom jml_lembur)
            foreach ($details as $d) {
                $jmlLemburFinal = (float) $d->jam_lembur_dihitung;

                if (!empty($d->id_absensi)) {
                    $this->db->table('sdm_absensi')
                        ->where('id', (int) $d->id_absensi)
                        ->update([
                            'jml_lembur' => $jmlLemburFinal,
                            'keterangan_lembur' => 'Lembur ' . $header->kode_lembur . ' (' . $d->persentase . '%)'
                        ]);
                } else {
                    $this->db->table('sdm_absensi')
                        ->where('id_karyawan', (int) $d->id_karyawan)
                        ->where('tgl_absen', $header->tanggal)
                        ->update([
                            'jml_lembur' => $jmlLemburFinal,
                            'keterangan_lembur' => 'Lembur ' . $header->kode_lembur . ' (' . $d->persentase . '%)'
                        ]);
                }
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return false;
            }

            $this->db->transCommit();
            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in Mlembur::approve2: ' . $e->getMessage());
            return false;
        }
    }

    function cancelApprove($id, $userId)
    {
        $header = $this->db->table($this->table)->where('id', (int) $id)->get()->getRow();
        if (!$header) return false;

        $newStatus = 0;
        if ($header->status == 2) {
            $newStatus = 1;
        } else if ($header->status == 1) {
            $newStatus = 0;
        }

        return $this->db->table($this->table)
            ->where('id', (int) $id)
            ->update([
                'status'        => $newStatus,
                'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => (int) $userId
            ]);
    }
}
