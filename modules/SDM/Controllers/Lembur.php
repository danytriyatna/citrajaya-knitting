<?php

namespace Modules\SDM\Controllers;

use App\Controllers\BaseController;
use Modules\SDM\Models\Mlembur;
use Modules\SDM\Models\MlemburDetail;
use Modules\SDM\Models\Mabsensi;
use Modules\Referensi\Models\KaryawanModel;
use Modules\Referensi\Models\PerusahaanModel;

class Lembur extends BaseController
{
    protected $views = '\Modules\SDM\Views';
    protected $urlv  = 'sdm/lembur';

    protected $mLembur;
    protected $mLemburDetail;
    protected $mAbsen;
    protected $mKaryawan;
    protected $mPerusahaan;

    function __construct()
    {
        $this->MOD_ALIAS     = "MOD_SDM_LEMBUR";
        $this->mLembur       = new Mlembur();
        $this->mLemburDetail = new MlemburDetail();
        $this->mAbsen        = new Mabsensi();
        $this->mKaryawan     = new KaryawanModel();
        $this->mPerusahaan   = new PerusahaanModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Lembur Karyawan";
        $this->data['superadmin'] = $this->auth->isSuperadmin();

        $sortPerusahaan = [
            ['field' => 'nama_perusahaan', 'dir' => 'ASC']
        ];

        if (!$this->auth->isSuperadmin()) {
            if (empty($this->currentUser->id_perusahaan)) {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => 1]);
            } else {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => $this->currentUser->id_perusahaan]);
            }
            $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) && $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
        } else {
            $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
            $this->data['user_perusahaan'] = null;
        }

        return view($this->views . '\lembur_list', $this->data);
    }

    public function lists()
    {
        $start         = $this->request->getPost('start');
        $limit         = $this->request->getPost('length');
        $filters       = $this->request->getPost('filter');
        $order         = $this->request->getPost('order');
        $id_perusahaan = $this->request->getPost('id_perusahaan');
        $tgl_awal      = $this->request->getPost('tgl_awal');
        $tgl_akhir     = $this->request->getPost('tgl_akhir');
        $status        = $this->request->getPost('status');

        $params = [
            'id_perusahaan' => $id_perusahaan,
            'tgl_awal'      => $tgl_awal,
            'tgl_akhir'     => $tgl_akhir,
            'status'        => $status
        ];

        $results       = $this->mLembur->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mLembur->getDataCnt($filters, $params);
        $totaldata     = $this->mLembur->getDataCnt(null, $params);
        $maxpage       = ($limit > 0) ? ceil($totalfiltered / $limit) : 1;

        $build_array = [
            "last_page"       => $maxpage,
            "recordsTotal"    => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data"            => []
        ];

        foreach ($results as $row) {
            $id = encrypt($row->id);

            $atr_edit  = null;
            $atr_del   = null;
            $atr_print = null;

            if ($this->_edit) {
                $atr_edit['title'] = 'Edit / Detail';
                $atr_edit['url']   = $this->urlv . '/form/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete && $row->status == 0) {
                $atr_del['title']   = 'Hapus';
                $atr_del['url']     = $this->urlv . '/delete/';
                $atr_del['class']   = '';
                $atr_del['onclick'] = "return confirm('Hapus dokumen lembur ini?')";
            }
            if ($this->_print) {
                $atr_print['title']      = 'Cetak Dokumen';
                $atr_print['url']        = $this->urlv . '/print/';
                $atr_print['class']      = '';
                $atr_print['target']     = 'blank';
                $atr_print['icon_class'] = 'fa-print';
            }

            $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_print);

            $statusBadge = "";
            if ($row->status == 0) {
                $statusBadge = "<span class='badge bg-secondary'>Draft</span>";
            } else if ($row->status == 1) {
                $statusBadge = "<span class='badge bg-info text-white'>Disetujui Penugasan</span>";
            } else if ($row->status == 2) {
                $statusBadge = "<span class='badge bg-success'>Disetujui Jam Lembur</span>";
            } else if ($row->status == 3) {
                $statusBadge = "<span class='badge bg-danger'>Dibatalkan</span>";
            }

            // Hitung rata-rata persentase pencapaian
            $persenRata2 = 0;
            if ($row->total_target > 0) {
                $persenRata2 = min(100.00, round(($row->total_realisasi / $row->total_target) * 100, 1));
            }

            array_push($build_array["data"], [
                "aksi"               => $btnAction ?: '',
                "id"                 => $id,
                "kode_lembur"        => $row->kode_lembur,
                "tanggal"            => fdate_eng_to_ind($row->tanggal),
                "nama_perusahaan"    => $row->nama_perusahaan ?: 'CITRA JAYA KNITTING',
                "keterangan"         => $row->keterangan ?: '-',
                "total_karyawan"     => (int) $row->total_karyawan . ' Orang',
                "total_target"       => number_format($row->total_target, 2, ',', '.'),
                "total_realisasi"    => number_format($row->total_realisasi, 2, ',', '.'),
                "pencapaian_rata"    => $persenRata2 . '%',
                "total_jam_absen"    => number_format($row->total_jam_absen, 2, ',', '.') . ' Jam',
                "total_jam_dihitung" => number_format($row->total_jam_dihitung, 2, ',', '.') . ' Jam',
                "status_badge"       => $statusBadge,
                "status_code"        => (int) $row->status
            ]);
        }

        return $this->response->setJSON($build_array);
    }

    public function form($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['id'] = $id;
        $resData = null;
        $resDetail = [];

        if (!empty($id)) {
            $idDecrypted = is_numeric($id) ? (int) $id : decrypt($id);
            if (!$idDecrypted && strlen($id) > 10) {
                // In case of doubled encrypted string
                $idDecrypted = decrypt(substr($id, 0, (int)(strlen($id) / 2)));
            }
            if ($idDecrypted) {
                $resData = $this->mLembur->getData($idDecrypted);
                if ($resData) {
                    $resDetail = $this->mLemburDetail->getDataByHeader($idDecrypted);
                    // Enrich target and realisasi from target harian if draft (status 0) and target or realisasi is 0
                    if ($resData->status == 0 && !empty($resDetail)) {
                        $db = \Config\Database::connect();
                        $empIds = array_map(function($d) { return (int)$d->id_karyawan; }, $resDetail);
                        $tgtRows = $db->table('sdm_target_harian_det td')
                            ->select('td.id_karyawan, td.target_lembur, td.realisasi_lembur, td.satuan')
                            ->join('sdm_target_harian h', 'h.id = td.id_header')
                            ->whereIn('td.id_karyawan', $empIds)
                            ->where('h.tanggal', $resData->tanggal)
                            ->where('h.active', 1)
                            ->where('td.active', 1)
                            ->get()->getResult();

                        $tgtMap = [];
                        foreach ($tgtRows as $tr) {
                            $tgtMap[$tr->id_karyawan] = $tr;
                        }

                        foreach ($resDetail as &$d) {
                            if (isset($tgtMap[$d->id_karyawan])) {
                                $t = $tgtMap[$d->id_karyawan];
                                if ((float)$d->target == 0 && (float)$t->target_lembur > 0) {
                                    $d->target = (float)$t->target_lembur;
                                    $d->satuan_target = !empty($t->satuan) ? $t->satuan : $d->satuan_target;
                                }
                                if ((float)$d->realisasi == 0 && (float)$t->realisasi_lembur > 0) {
                                    $d->realisasi = (float)$t->realisasi_lembur;
                                }
                                if ((float)$d->target > 0) {
                                    $d->persentase = round(min(100.00, ((float)$d->realisasi / (float)$d->target) * 100), 2);
                                    $d->jam_lembur_dihitung = round(((float)$d->jam_lembur_absen * ($d->persentase / 100)), 2);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($resData)) {
            $resData = (object) [
                'id'            => '',
                'kode_lembur'   => $this->mLembur->generateKodeLembur(),
                'tanggal'       => date('Y-m-d'),
                'id_perusahaan' => 1,
                'keterangan'    => '',
                'status'        => 0
            ];
        }

        $sortPerusahaan = [
            ['field' => 'nama_perusahaan', 'dir' => 'ASC']
        ];
        $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
        $this->data['resData']    = $resData;
        $this->data['detail']     = json_encode($resDetail);
        $this->data['titlehead']  = empty($id) ? "Tambah Lembur Karyawan" : "Edit Lembur Karyawan - " . $resData->kode_lembur;

        return view($this->views . '\lembur_form', $this->data);
    }

    public function save()
    {
        $id          = $this->request->getPost('id');
        $tanggal     = $this->request->getPost('tanggal');
        $idPerusahaan= $this->request->getPost('id_perusahaan');
        $keterangan  = $this->request->getPost('keterangan');
        $dataDetail  = $this->request->getPost('detail');

        if (!empty($id)) {
            $id = decrypt($id);
        }

        if (empty($tanggal)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal lembur wajib diisi!'
            ]);
        }

        if (empty($dataDetail) || !is_array($dataDetail) || count($dataDetail) == 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Pilih minimal 1 karyawan untuk penugasan lembur!'
            ]);
        }

        $dataHeader = [
            'tanggal'       => $tanggal,
            'id_perusahaan' => !empty($idPerusahaan) ? (int) $idPerusahaan : 1,
            'keterangan'    => $keterangan,
        ];

        $saveId = $this->mLembur->trxInsertUpdateRecord($dataHeader, $dataDetail, $id);

        if ($saveId !== false) {
            try {
                $this->syncLemburToTargetHarian($tanggal, $dataDetail, session()->get('id'));
            } catch (\Throwable $e) {
                log_message('error', 'Sync Lembur to Target Harian failed: ' . $e->getMessage());
            }

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Data Lembur Karyawan berhasil disimpan dan disinkronkan dengan Target Harian!',
                'id'      => encrypt($saveId)
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menyimpan data lembur!'
            ]);
        }
    }

    public function approve1()
    {
        $id = $this->request->getPost('id');
        if (!empty($id)) {
            $id = decrypt($id);
        }

        $userId = session()->get('id');
        $res = $this->mLembur->approve1($id, $userId);

        if ($res) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Penugasan Karyawan Lembur (Approve 1) berhasil disetujui!'
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal melakukan Approve 1!'
            ]);
        }
    }

    public function approve2()
    {
        $id = $this->request->getPost('id');
        if (!empty($id)) {
            $id = decrypt($id);
        }

        $userId = session()->get('id');
        $res = $this->mLembur->approve2($id, $userId);

        if ($res) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Persetujuan Jam Lembur (Approve 2) berhasil! Data Jam Lembur pada Absensi Karyawan telah otomatis disinkronkan.'
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal melakukan Approve 2!'
            ]);
        }
    }

    public function cancelApprove()
    {
        $id = $this->request->getPost('id');
        if (!empty($id)) {
            $id = decrypt($id);
        }

        $userId = session()->get('id');
        $res = $this->mLembur->cancelApprove($id, $userId);

        if ($res) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Status persetujuan berhasil diturunkan/dibatalkan.'
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal membatalkan persetujuan!'
            ]);
        }
    }

    public function delete($id = null)
    {
        if (!empty($id)) {
            $id = decrypt($id);
        } else {
            $id = $this->request->getPost('id');
            if (!empty($id)) $id = decrypt($id);
        }

        $db = \Config\Database::connect();
        $res = $db->table('sdm_lembur_header')
            ->where('id', (int) $id)
            ->where('status', 0)
            ->update([
                'active'     => 0,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => session()->get('id')
            ]);

        if ($res) {
            return redirect()->to('/' . $this->urlv)->with('success', 'Dokumen lembur berhasil dihapus!');
        } else {
            return redirect()->to('/' . $this->urlv)->with('error', 'Hanya dokumen berstatus Draft yang dapat dihapus.');
        }
    }

    public function listKaryawan()
    {
        $id_perusahaan = $this->request->getPost('id_perusahaan');

        $params = [
            'id_perusahaan' => $id_perusahaan,
            'type'          => 1 // Khusus Non-CMT
        ];

        // Ambil semua karyawan non-CMT
        $results = $this->mKaryawan->getData(null, 0, 99999, [['field' => 'ky.full_name', 'dir' => 'ASC']], null, $params);

        $build_array = [
            "data" => []
        ];

        foreach ($results as $row) {
            array_push($build_array["data"], [
                "id"              => $row->id,
                "nip"             => $row->nip ?: '-',
                "full_name"       => $row->full_name,
                "posisi"          => $row->posisi ?: '-',
                "nama_perusahaan" => $row->nama_perusahaan ?: 'CITRA JAYA KNITTING'
            ]);
        }

        return $this->response->setJSON($build_array);
    }

    public function syncAbsensi()
    {
        $tanggal     = $this->request->getPost('tanggal');
        $idKaryawans = $this->request->getPost('id_karyawan'); // array or single, optional

        if (empty($tanggal)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Parameter tanggal tidak valid',
                'data'    => []
            ]);
        }

        if (!empty($idKaryawans) && !is_array($idKaryawans)) {
            $idKaryawans = [$idKaryawans];
        }
        $idKaryawans = !empty($idKaryawans) ? array_map('intval', $idKaryawans) : [];

        $db = \Config\Database::connect();
        
        // 1. Ambil data absensi: karyawan yang sudah ada di tabel ATAU yang memiliki status lembur pada tanggal ini
        $builder = $db->table('sdm_absensi sa');
        $builder->select("
            sa.id as id_absensi,
            sa.id_karyawan,
            rk.nip,
            rk.full_name,
            rk.posisi,
            rp.nama_perusahaan,
            sa.tgl_absen,
            sa.jam_masuk,
            sa.jam_keluar,
            sa.status_kehadiran,
            sa.status_lembur,
            COALESCE(sa.jml_lembur, 0) as jml_lembur
        ");
        $builder->join('ref_karyawan rk', 'rk.id = sa.id_karyawan');
        $builder->join('ref_perusahaan rp', 'rp.id = rk.id_perusahaan', 'left');
        $builder->where('sa.tgl_absen', $tanggal);
        $builder->where('sa.active', 1);

        if (!empty($idKaryawans)) {
            $builder->groupStart()
                ->whereIn('sa.id_karyawan', $idKaryawans)
                ->orGroupStart()
                    ->where('sa.status_lembur > 0')
                    ->orWhere('sa.jml_lembur > 0')
                ->groupEnd()
            ->groupEnd();
        } else {
            $builder->groupStart()
                ->where('sa.status_lembur > 0')
                ->orWhere('sa.jml_lembur > 0')
            ->groupEnd();
        }

        $results = $builder->get()->getResult();
        $absMap = [];
        foreach ($results as $r) {
            $absMap[$r->id_karyawan] = $r;
        }

        // 2. Ambil data target & realisasi lembur dari Target Harian pada tanggal ini
        $tgtBuilder = $db->table('sdm_target_harian_det td');
        $tgtBuilder->select("
            td.id_karyawan,
            rk.nip,
            rk.full_name,
            rk.posisi,
            rp.nama_perusahaan,
            SUM(COALESCE(td.target_lembur, 0)) as target_lembur,
            SUM(COALESCE(td.realisasi_lembur, 0)) as realisasi_lembur,
            MAX(td.satuan) as satuan,
            MAX(h.kode_transaksi) as kode_target_harian
        ");
        $tgtBuilder->join('sdm_target_harian h', 'h.id = td.id_header');
        $tgtBuilder->join('ref_karyawan rk', 'rk.id = td.id_karyawan');
        $tgtBuilder->join('ref_perusahaan rp', 'rp.id = rk.id_perusahaan', 'left');
        $tgtBuilder->where('h.tanggal', $tanggal);
        $tgtBuilder->where('h.active', 1);
        $tgtBuilder->where('td.active', 1);

        if (!empty($idKaryawans)) {
            $tgtBuilder->groupStart()
                ->whereIn('td.id_karyawan', $idKaryawans)
                ->orGroupStart()
                    ->where('td.target_lembur > 0')
                    ->orWhere('td.realisasi_lembur > 0')
                ->groupEnd()
            ->groupEnd();
        } else {
            $tgtBuilder->groupStart()
                ->where('td.target_lembur > 0')
                ->orWhere('td.realisasi_lembur > 0')
            ->groupEnd();
        }

        $tgtBuilder->groupBy('td.id_karyawan, rk.nip, rk.full_name, rk.posisi, rp.nama_perusahaan');
        $tgtResults = $tgtBuilder->get()->getResult();
        $tgtMap = [];
        foreach ($tgtResults as $t) {
            $tgtMap[$t->id_karyawan] = $t;
        }

        // Kumpulkan semua ID karyawan unik (dari yang sudah ada di tabel + absensi lembur + target harian lembur)
        $allEmpIds = array_unique(array_merge(
            $idKaryawans,
            array_keys($absMap),
            array_keys($tgtMap)
        ));

        // Jika ada karyawan dari $idKaryawans yang belum ada di absensi atau target harian, ambil profil ref_karyawan
        $missingEmpIds = array_diff($allEmpIds, array_keys($absMap), array_keys($tgtMap));
        $missingEmpMap = [];
        if (!empty($missingEmpIds)) {
            $missingRows = $db->table('ref_karyawan rk')
                ->select('rk.id, rk.nip, rk.full_name, rk.posisi')
                ->whereIn('rk.id', array_values($missingEmpIds))
                ->get()->getResult();
            foreach ($missingRows as $mr) {
                $missingEmpMap[$mr->id] = $mr;
            }
        }

        $list = [];
        foreach ($allEmpIds as $empId) {
            $r = $absMap[$empId] ?? null;
            $t = $tgtMap[$empId] ?? null;
            $m = $missingEmpMap[$empId] ?? null;

            $nip = $r ? $r->nip : ($t ? $t->nip : ($m ? $m->nip : '-'));
            $fullName = $r ? $r->full_name : ($t ? $t->full_name : ($m ? $m->full_name : ''));
            $posisi = $r ? $r->posisi : ($t ? $t->posisi : ($m ? $m->posisi : '-'));

            $jamMasuk = ($r && !empty($r->jam_masuk)) ? date('H:i', strtotime($r->jam_masuk)) : '-';
            $jamKeluar = ($r && !empty($r->jam_keluar)) ? date('H:i', strtotime($r->jam_keluar)) : '-';
            $jamAbsen = $r ? (float) $r->jml_lembur : 0;

            $target = $t ? (float) $t->target_lembur : 0;
            $realisasi = $t ? (float) $t->realisasi_lembur : 0;
            $satuan = $t ? ($t->satuan ?: 'Pcs') : 'Pcs';

            $persentase = ($target > 0) ? min(100.00, round(($realisasi / target) * 100, 2)) : 0;
            $jamDihitung = round(($jamAbsen * ($persentase / 100)), 2);

            $list[] = [
                'id_karyawan'         => (int) $empId,
                'id_absensi'          => $r ? (int) $r->id_absensi : null,
                'nip'                 => $nip,
                'full_name'           => $fullName,
                'posisi'              => $posisi,
                'jam_masuk'           => $jamMasuk,
                'jam_keluar'          => $jamKeluar,
                'status_kehadiran'    => $r ? (int) $r->status_kehadiran : null,
                'status_lembur'       => $r ? (int) $r->status_lembur : null,
                'jam_lembur_absen'    => $jamAbsen,
                'has_target_harian'   => !empty($t),
                'target'              => $target,
                'realisasi'           => $realisasi,
                'satuan_target'       => $satuan,
                'persentase'          => $persentase,
                'jam_lembur_dihitung' => $jamDihitung,
                'kode_target_harian'  => $t ? $t->kode_target_harian : null,
                'is_synced'           => true
            ];
        }

        // Urutkan list berdasarkan nama lengkap
        usort($list, function($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        return $this->response->setJSON([
            'status' => true,
            'data'   => $list
        ]);
    }

    public function getTargetHarianKaryawan()
    {
        $tanggal = $this->request->getPost('tanggal');
        if (empty($tanggal)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal wajib diisi!',
                'data'    => []
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('sdm_target_harian_det td');
        $builder->select("
            td.id_karyawan,
            rk.nip,
            rk.full_name,
            rk.posisi,
            rp.nama_perusahaan,
            COALESCE(td.target_lembur, 0) as target_lembur,
            COALESCE(td.realisasi_lembur, 0) as realisasi_lembur,
            COALESCE(NULLIF(TRIM(td.satuan), ''), 'Pcs') as satuan,
            h.kode_transaksi
        ");
        $builder->join('sdm_target_harian h', 'h.id = td.id_header');
        $builder->join('ref_karyawan rk', 'rk.id = td.id_karyawan');
        $builder->join('ref_perusahaan rp', 'rp.id = rk.id_perusahaan', 'left');
        $builder->where('h.tanggal', $tanggal);
        $builder->where('h.active', 1);
        $builder->where('td.active', 1);
        $builder->where('(td.target_lembur > 0 OR td.realisasi_lembur > 0)');
        $builder->orderBy('rk.full_name', 'ASC');

        $rows = $builder->get()->getResult();

        if (empty($rows)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tidak ditemukan data lembur di Target Harian untuk tanggal ' . $tanggal,
                'data'    => []
            ]);
        }

        $empIds = array_map(function($r) { return (int) $r->id_karyawan; }, $rows);

        // Ambil data absensi mereka juga
        $absRows = $db->table('sdm_absensi')
            ->select('id as id_absensi, id_karyawan, jam_masuk, jam_keluar, status_kehadiran, status_lembur, COALESCE(jml_lembur, 0) as jml_lembur')
            ->whereIn('id_karyawan', $empIds)
            ->where('tgl_absen', $tanggal)
            ->where('active', 1)
            ->get()->getResult();

        $absMap = [];
        foreach ($absRows as $a) {
            $absMap[$a->id_karyawan] = $a;
        }

        $resultData = [];
        foreach ($rows as $r) {
            $abs = $absMap[$r->id_karyawan] ?? null;
            $jamMasuk = ($abs && !empty($abs->jam_masuk)) ? date('H:i', strtotime($abs->jam_masuk)) : '-';
            $jamKeluar = ($abs && !empty($abs->jam_keluar)) ? date('H:i', strtotime($abs->jam_keluar)) : '-';
            $jamAbsen = $abs ? (float) $abs->jml_lembur : 0;

            $target = (float) $r->target_lembur;
            $realisasi = (float) $r->realisasi_lembur;
            $persentase = ($target > 0) ? min(100.00, round(($realisasi / $target) * 100, 2)) : 0;
            $jamDihitung = round(($jamAbsen * ($persentase / 100)), 2);

            $resultData[] = [
                'id_karyawan'         => (int) $r->id_karyawan,
                'id_absensi'          => $abs ? (int) $abs->id_absensi : null,
                'nip'                 => $r->nip ?: '-',
                'full_name'           => $r->full_name,
                'posisi'              => $r->posisi ?: '-',
                'pekerjaan'           => '',
                'target'              => $target,
                'satuan_target'       => $r->satuan,
                'realisasi'           => $realisasi,
                'persentase'          => $persentase,
                'jam_masuk'           => $jamMasuk,
                'jam_keluar'          => $jamKeluar,
                'status_kehadiran'    => $abs ? (int) $abs->status_kehadiran : null,
                'status_lembur'       => $abs ? (int) $abs->status_lembur : null,
                'jam_lembur_absen'    => $jamAbsen,
                'jam_lembur_dihitung' => $jamDihitung,
                'catatan'             => '',
                'kode_target_harian'  => $r->kode_transaksi,
                'is_synced'           => true
            ];
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Berhasil menarik ' . count($resultData) . ' karyawan dari Target Harian!',
            'data'    => $resultData
        ]);
    }

    private function syncLemburToTargetHarian($tanggal, $dataDetail, $userId)
    {
        $db = \Config\Database::connect();
        $targetHarian = $db->table('sdm_target_harian')
            ->where('tanggal', $tanggal)
            ->where('active', 1)
            ->get()->getRow();

        if (!$targetHarian || empty($dataDetail)) {
            return;
        }

        $idHeader = (int) $targetHarian->id;

        foreach ($dataDetail as $row) {
            $idKaryawan = (int) ($row['id_karyawan'] ?? 0);
            if (!$idKaryawan) continue;

            $target    = isset($row['target']) ? (float) $row['target'] : 0;
            $realisasi = isset($row['realisasi']) ? (float) $row['realisasi'] : 0;
            $satuan    = !empty($row['satuan_target']) ? trim($row['satuan_target']) : 'Pcs';

            // Cek apakah karyawan terdaftar di sdm_target_harian_det
            $det = $db->table('sdm_target_harian_det')
                ->where('id_header', $idHeader)
                ->where('id_karyawan', $idKaryawan)
                ->where('active', 1)
                ->get()->getRow();

            if ($det) {
                // Update atau insert slot LEMBUR di sdm_target_harian_hourly
                $hourCheck = $db->table('sdm_target_harian_hourly')
                    ->where('id_header', $idHeader)
                    ->where('id_karyawan', $idKaryawan)
                    ->where('jam_slot', 'LEMBUR')
                    ->where('active', 1)
                    ->get()->getRow();

                if ($hourCheck) {
                    $db->table('sdm_target_harian_hourly')
                        ->where('id', $hourCheck->id)
                        ->update([
                            'raw_text'  => (string) $realisasi,
                            'total_qty' => $realisasi
                        ]);
                } else if ($realisasi > 0) {
                    $db->table('sdm_target_harian_hourly')->insert([
                        'id_header'   => $idHeader,
                        'id_karyawan' => $idKaryawan,
                        'jam_slot'    => 'LEMBUR',
                        'raw_text'    => (string) $realisasi,
                        'total_qty'   => $realisasi,
                        'active'      => 1
                    ]);
                }

                // Hitung ulang total_realisasi dari seluruh slot jam hourly
                $sumHourly = $db->table('sdm_target_harian_hourly')
                    ->select('SUM(total_qty) as total')
                    ->where('id_header', $idHeader)
                    ->where('id_karyawan', $idKaryawan)
                    ->where('active', 1)
                    ->get()->getRow();

                $totalRealisasi = $sumHourly ? (float) $sumHourly->total : $realisasi;
                $grandTarget    = (float) $det->target_qty + $target;
                $persentase     = ($grandTarget > 0) ? round(($totalRealisasi / $grandTarget) * 100, 2) : 0;

                $db->table('sdm_target_harian_det')
                    ->where('id', $det->id)
                    ->update([
                        'target_lembur'    => $target,
                        'realisasi_lembur' => $realisasi,
                        'satuan'           => $satuan,
                        'total_realisasi'  => $totalRealisasi,
                        'persentase'       => $persentase,
                        'updated_by'       => $userId,
                        'updated_at'       => date('Y-m-d H:i:s')
                    ]);
            }
        }
    }

    public function printDoc($id = null, $type = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        if (!empty($id)) {
            $idDecrypted = is_numeric($id) ? (int) $id : decrypt($id);
            if (!$idDecrypted && strlen($id) > 10) {
                $idDecrypted = decrypt(substr($id, 0, (int)(strlen($id) / 2)));
            }
            $id = $idDecrypted;
        }

        $resData = $this->mLembur->getData($id);
        if (!$resData) {
            return "Data lembur tidak ditemukan.";
        }

        $resDetail = $this->mLemburDetail->getDataByHeader($id);

        // Mode Cetak: 1 = SPK Penugasan (Approve 1), 2 = Rekapitulasi Realisasi & Jam Lembur (Approve 2)
        $printType = $this->request->getGet('type') ?: $type;
        if (empty($printType)) {
            $printType = ($resData->status == 2) ? 2 : 1;
        } else {
            $printType = ($printType === 'spk' || $printType == 1) ? 1 : 2;
        }

        $this->data['resData']   = $resData;
        $this->data['resDetail'] = $resDetail;
        $this->data['detail']    = $resDetail;
        $this->data['printType'] = $printType;
        $this->data['titlehead'] = ($printType == 1) 
            ? "SPK Penugasan Lembur - " . $resData->kode_lembur 
            : "Laporan Realisasi Lembur - " . $resData->kode_lembur;

        return view($this->views . '\vprint_lembur', $this->data);
    }
}
