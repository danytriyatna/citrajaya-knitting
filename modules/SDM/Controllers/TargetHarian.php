<?php

namespace Modules\SDM\Controllers;

use App\Controllers\BaseController;
use Modules\SDM\Models\MTargetHarian;
use Modules\Referensi\Models\KaryawanModel;

class TargetHarian extends BaseController
{
    protected $views = '\Modules\SDM\Views';
    protected $urlv  = 'sdm/target-harian';
    protected $mTarget;
    protected $mKaryawan;

    public function __construct()
    {
        $this->MOD_ALIAS = "MOD_SDM_TARGET_HARIAN";
        $this->mTarget   = new MTargetHarian();
        $this->mKaryawan = new KaryawanModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        if (!$this->_view) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke menu ini.');
        }

        $this->data['titlehead'] = "Target & Realisasi Harian (Non-CMT)";
        $this->data['name_app']  = "Citra Jaya Knitting";
        $this->data['employees'] = $this->mTarget->getNonCmtEmployees();
        return view($this->views . '\vtarget_harian_list', $this->data);
    }

    private function parseDateToYmd($dateStr)
    {
        if (empty($dateStr)) return null;
        $dateStr = trim($dateStr);

        $indoMonths = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
            'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
            'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'Jun' => '06', 'Jul' => '07', 'Agu' => '08', 'Sep' => '09',
            'Okt' => '10', 'Nov' => '11', 'Des' => '12'
        ];
        foreach ($indoMonths as $mIndo => $mNum) {
            if (stripos($dateStr, $mIndo) !== false) {
                $dateStr = str_ireplace($mIndo, $mNum, $dateStr);
                $dateStr = preg_replace('/\s+/', '-', $dateStr);
                break;
            }
        }

        $cleaned = str_replace(['/', '.'], '-', $dateStr);
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $cleaned, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $cleaned, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        $ts = strtotime($cleaned);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    public function lists()
    {
        $start   = (int) $this->request->getPost('start');
        $limit   = (int) $this->request->getPost('length');
        $filters = $this->request->getPost('filter');
        $order   = $this->request->getPost('sort');

        $params = [];
        $from = $this->parseDateToYmd($this->request->getPost('from_date'));
        $to   = $this->parseDateToYmd($this->request->getPost('to_date'));
        if (!empty($from) && !empty($to)) {
            $params['from_date'] = $from;
            $params['to_date']   = $to;
        }

        $results       = $this->mTarget->getDataList($start, $limit > 0 ? $limit : 50, $order, $filters, $params);
        $totalfiltered = $this->mTarget->getDataListCnt($filters, $params);
        $totaldata     = $this->mTarget->getDataListCnt(null, null);
        $maxpage       = $limit > 0 ? ceil($totalfiltered / $limit) : 1;

        $dataRows = [];
        foreach ($results as $row) {
            $idEnc = encrypt($row->id);

            $atr_edit = [
                'title'      => 'Edit',
                'url'        => $this->urlv . '/form/',
                'class'      => '',
                'icon_class' => 'fa-edit'
            ];
            $atr_del = null;
            if ($row->status == 0) {
                $atr_del = [
                    'title'      => 'Hapus',
                    'url'        => $this->urlv . '/delete/',
                    'class'      => '',
                    'onclick'    => "return confirm('Hapus dokumen target harian ini?')",
                    'icon_class' => 'fa-trash'
                ];
            }
            $atr_print = [
                'title'      => 'Print',
                'url'        => $this->urlv . '/print/',
                'class'      => '',
                'target'     => '_blank',
                'icon_class' => 'fa-print'
            ];

            $btnAction = function_exists('btn_action_group') 
                ? btn_action_group($idEnc, $atr_edit, $atr_del, $atr_print)
                : '';

            $row->id_encrypted = $idEnc;
            $row->btn_action   = $btnAction;
            $row->tanggal_fmt  = date('d-m-Y', strtotime($row->tanggal));
            $dataRows[] = $row;
        }

        return $this->response->setJSON([
            "last_page"       => $maxpage,
            "recordsTotal"    => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data"            => $dataRows
        ]);
    }

    public function form($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        if (!$this->_view) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke menu ini.');
        }

        $this->data['titlehead'] = "Form Target & Realisasi Harian";
        $this->data['name_app']  = "Citra Jaya Knitting";
        $this->data['urlv']      = $this->urlv;

        $this->data['time_slots'] = [
            '08.00-09.00',
            '09.00-10.00',
            '10.00-11.00',
            '11.00-12.00',
            '13.00-14.00',
            '14.00-15.00',
            '15.00-16.00',
            '16.00-17.00',
        ];

        $mTargetProses = new \Modules\Referensi\Models\TargetProsesModel();
        $this->data['target_processes'] = $mTargetProses->getActiveProsesList();

        if (!empty($id)) {
            $decId = decrypt($id);
            $header = $this->mTarget->getHeaderById($decId);
            if (!$header) {
                return redirect()->to($this->urlv)->with('error', 'Data tidak ditemukan!');
            }

            $details      = $this->mTarget->getDetailsByHeaderId($decId);
            $hourly       = $this->mTarget->getHourlyByHeaderId($decId);
            $prosesHourly = $this->mTarget->getProsesHourlyByHeaderId($decId);

            $hourlyMap = [];
            foreach ($hourly as $h) {
                $hourlyMap[$h->id_karyawan][$h->jam_slot] = $h->raw_text;
            }

            $prosesHourlyMap = [];
            foreach ($prosesHourly as $ph) {
                $prosesHourlyMap[$ph->id_karyawan][] = [
                    'id_proses'       => $ph->id_proses,
                    'nama_proses'     => !empty($ph->master_nama_proses) ? $ph->master_nama_proses : $ph->nama_proses,
                    'kode'            => $ph->kode ?? '',
                    'warna_badge'     => $ph->warna_badge ?? '#6c757d',
                    'target_qty'      => (float)$ph->target_qty,
                    'satuan'          => $ph->satuan,
                    'jam_08_09'       => (string)($ph->jam_08_09 ?? ''),
                    'jam_09_10'       => (string)($ph->jam_09_10 ?? ''),
                    'jam_10_11'       => (string)($ph->jam_10_11 ?? ''),
                    'jam_11_12'       => (string)($ph->jam_11_12 ?? ''),
                    'jam_13_14'       => (string)($ph->jam_13_14 ?? ''),
                    'jam_14_15'       => (string)($ph->jam_14_15 ?? ''),
                    'jam_15_16'       => (string)($ph->jam_15_16 ?? ''),
                    'jam_16_17'       => (string)($ph->jam_16_17 ?? ''),
                    'target_lembur'   => (float)($ph->target_lembur ?? 0),
                    'jam_lembur'      => (string)($ph->jam_lembur ?? ''),
                    'total_realisasi' => (float)$ph->total_realisasi,
                ];
            }

            $this->data['header']          = $header;
            $this->data['details']         = $details;
            $this->data['hourlyMap']       = $hourlyMap;
            $this->data['prosesHourlyMap'] = $prosesHourlyMap;
            $this->data['id_enc']          = $id;
        } else {
            $header = (object)[
                'id'             => '',
                'kode_transaksi' => $this->mTarget->generateDocCode(date('Y-m-d')),
                'tanggal'        => date('Y-m-d'),
                'id_shift'       => 1,
                'divisi'         => 'ALL NON-CMT',
                'keterangan'     => '',
                'status'         => 0
            ];

            $employees = $this->mTarget->getNonCmtEmployees();
            $details   = [];
            foreach ($employees as $emp) {
                $details[] = (object)[
                    'id'               => '',
                    'id_karyawan'      => $emp->id,
                    'nip'              => $emp->nip,
                    'full_name'        => $emp->full_name,
                    'posisi'           => $emp->posisi,
                    'target_qty'       => 0,
                    'target_lembur'    => 0,
                    'realisasi_lembur' => 0,
                    'satuan'           => 'Pcs',
                    'ref_job'          => '',
                    'total_realisasi'  => 0,
                    'persentase'       => 0,
                    'keterangan'       => ''
                ];
            }

            $this->data['header']          = $header;
            $this->data['details']         = $details;
            $this->data['hourlyMap']       = [];
            $this->data['prosesHourlyMap'] = [];
            $this->data['id_enc']          = '';
        }

        return view($this->views . '\vtarget_harian_form', $this->data);
    }

    public function ping()
    {
        $isLoggedIn = $this->auth->loggedIn() || !empty(session()->get('user_id')) || !empty(session()->get('identity'));
        return $this->response->setJSON([
            'status'    => true,
            'logged_in' => (bool) $isLoggedIn,
            'timestamp' => time()
        ]);
    }

    public function save()
    {
        $isLoggedIn = $this->auth->loggedIn() || !empty(session()->get('user_id')) || !empty(session()->get('identity'));
        if (!$isLoggedIn) {
            return $this->response->setJSON([
                'status'        => false,
                'is_auth_error' => true,
                'message'       => 'Sesi login Anda telah berakhir. JANGAN REFRESH halaman ini agar data inputan tidak hilang! Buka tab baru, login kembali ke sistem, lalu kembali ke tab ini dan klik tombol Simpan Data Matrix lagi.'
            ]);
        }

        $idEnc    = $this->request->getPost('id');
        $idHeader = !empty($idEnc) ? decrypt($idEnc) : null;

        $tanggalRaw = trim($this->request->getPost('tanggal') ?? '');
        $tanggal = $this->parseDateToYmd($tanggalRaw) ?? date('Y-m-d');

        $id_shift   = (int) $this->request->getPost('id_shift');
        $divisi     = $this->request->getPost('divisi');
        $keterangan = $this->request->getPost('keterangan');
        $status     = (int) $this->request->getPost('status');
        $matrix     = $this->request->getPost('matrix');

        $userId = 1;
        if (session()->get('user_id')) {
            $userId = session()->get('user_id');
        } elseif (session()->get('id')) {
            $userId = session()->get('id');
        }

        $headerData = [
            'tanggal'    => $tanggal,
            'id_shift'   => $id_shift > 0 ? $id_shift : 1,
            'divisi'     => !empty($divisi) ? $divisi : 'ALL NON-CMT',
            'keterangan' => $keterangan,
            'status'     => $status,
        ];

        try {
            $savedId = $this->mTarget->trxSaveData($idHeader, $headerData, $matrix, $userId);

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Data Target & Realisasi Harian berhasil disimpan!',
                'id_enc'  => encrypt($savedId)
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ]);
        }
    }

    public function get_doc_code()
    {
        $tanggalRaw = trim($this->request->getGet('tanggal') ?? '');
        $tanggal = $this->parseDateToYmd($tanggalRaw) ?? date('Y-m-d');
        $code = $this->mTarget->generateDocCode($tanggal);
        return $this->response->setJSON(['status' => true, 'code' => $code]);
    }

    public function delete($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid']);
        }

        $decId = decrypt($id);
        $header = $this->mTarget->getHeaderById($decId);
        if ($header && $header->status == 1) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Dokumen berstatus Final tidak dapat dihapus!'
            ]);
        }
        $db = \Config\Database::connect();
        $db->table('sdm_target_harian')->where('id', $decId)->update(['active' => 0]);
        $db->table('sdm_target_harian_det')->where('id_header', $decId)->update(['active' => 0]);
        $db->table('sdm_target_harian_hourly')->where('id_header', $decId)->update(['active' => 0]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data berhasil dihapus!'
        ]);
    }

    public function printDoc($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        if (empty($id)) {
            return "ID tidak valid";
        }

        $decId = is_numeric($id) ? (int)$id : decrypt($id);
        $header = $this->mTarget->getHeaderById($decId);
        if (!$header) {
            return "Data target tidak ditemukan.";
        }

        $details = $this->mTarget->getDetailsByHeaderId($decId);
        $hourly  = $this->mTarget->getHourlyByHeaderId($decId);
        $prosesHourly = $this->mTarget->getProsesHourlyByHeaderId($decId);

        $hourlyMap = [];
        foreach ($hourly as $h) {
            $hourlyMap[$h->id_karyawan][$h->jam_slot] = $h->raw_text;
        }

        // Jika ada data breakdown proses, lengkapi slot yang belum memiliki kode proses (hanya angka)
        if (!empty($prosesHourly)) {
            $slotMap = [
                '08.00-09.00' => 'jam_08_09',
                '09.00-10.00' => 'jam_09_10',
                '10.00-11.00' => 'jam_10_11',
                '11.00-12.00' => 'jam_11_12',
                '13.00-14.00' => 'jam_13_14',
                '14.00-15.00' => 'jam_14_15',
                '15.00-16.00' => 'jam_15_16',
                '16.00-17.00' => 'jam_16_17',
            ];
            $prosesMap = [];
            foreach ($prosesHourly as $p) {
                $empId = $p->id_karyawan;
                $kode = !empty($p->kode) ? trim($p->kode) : (!empty($p->master_nama_proses) ? trim($p->master_nama_proses) : (!empty($p->nama_proses) ? trim($p->nama_proses) : ''));
                foreach ($slotMap as $slotLabel => $colName) {
                    $rawVal = isset($p->$colName) ? trim((string)$p->$colName) : '';
                    if ($rawVal !== '' && $rawVal !== '-' && (float)$rawVal > 0) {
                        $valNum = (float)$rawVal;
                        $entry = (!empty($kode) ? $kode . '=' : '') . $valNum;
                        $prosesMap[$empId][$slotLabel][] = $entry;
                    }
                }
            }

            foreach ($prosesMap as $empId => $slots) {
                foreach ($slots as $slotLabel => $entries) {
                    $curVal = isset($hourlyMap[$empId][$slotLabel]) ? trim($hourlyMap[$empId][$slotLabel]) : '';
                    if (empty($curVal) || is_numeric($curVal) || !preg_match('/[=:\-]/', $curVal)) {
                        $hourlyMap[$empId][$slotLabel] = implode(', ', $entries);
                    }
                }
            }
        }

        $this->data['header']     = $header;
        $this->data['details']    = $details;
        $this->data['hourlyMap']  = $hourlyMap;
        $this->data['titlehead']  = "Lembar Output Harian - " . $header->kode_transaksi;
        $this->data['time_slots'] = [
            '08.00-09.00',
            '09.00-10.00',
            '10.00-11.00',
            '11.00-12.00',
            '13.00-14.00',
            '14.00-15.00',
            '15.00-16.00',
            '16.00-17.00',
        ];

        return view($this->views . '\vtarget_harian_print', $this->data);
    }

    public function panduan()
    {
        $this->data['guide_title']        = "Panduan Target & Realisasi Harian";
        $this->data['guide_subtitle']     = "Modul Target & Realisasi Harian Pegawai (Non-CMT)";
        $this->data['guide_version']      = "1.0 (Resmi)";
        $this->data['guide_release_date'] = "21 Agustus 2026";
        $this->data['guide_module']       = "Persediaan & Produksi / SDM";
        $this->data['back_url']           = base_url('sdm/target-harian');

        return view($this->views . '\vtarget_harian_guide', $this->data);
    }

    public function printRekapTim()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        helper('utils_helper');

        $fromRaw = $this->request->getGet('from');
        $toRaw   = $this->request->getGet('to');

        $fromDate = !empty($fromRaw) ? $this->parseDateToYmd($fromRaw) : date('Y-m-01');
        $toDate   = !empty($toRaw) ? $this->parseDateToYmd($toRaw) : date('Y-m-t');

        $report = $this->mTarget->getReportRekapTim($fromDate, $toDate);

        $this->data['from_date']    = $fromDate;
        $this->data['to_date']      = $toDate;
        $this->data['from_raw']     = $fromRaw ?: date('01/m/Y');
        $this->data['to_raw']       = $toRaw ?: date('t/m/Y');
        $this->data['proses_list']  = $report['proses_list'];
        $this->data['pegawai_list'] = $report['pegawai_list'];
        $this->data['proses_totals']= $report['proses_totals'];
        $this->data['grand_target'] = $report['grand_total_target'];
        $this->data['grand_real']   = $report['grand_total_realisasi'];
        $this->data['grand_pct']    = $report['grand_pct'];
        $this->data['titlehead']    = "Rekap Output Produksi Non-CMT ({$this->data['from_raw']} - {$this->data['to_raw']})";

        return view($this->views . '\vtarget_harian_print_rekap_tim', $this->data);
    }

    public function printRaporKaryawan()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        helper('utils_helper');

        $fromRaw    = $this->request->getGet('from');
        $toRaw      = $this->request->getGet('to');
        $idKaryawan = $this->request->getGet('id_karyawan');

        $fromDate = !empty($fromRaw) ? $this->parseDateToYmd($fromRaw) : date('Y-m-01');
        $toDate   = !empty($toRaw) ? $this->parseDateToYmd($toRaw) : date('Y-m-t');

        $reports = $this->mTarget->getReportRaporKaryawan($fromDate, $toDate, $idKaryawan);

        $this->data['from_date'] = $fromDate;
        $this->data['to_date']   = $toDate;
        $this->data['from_raw']  = $fromRaw ?: date('01/m/Y');
        $this->data['to_raw']    = $toRaw ?: date('t/m/Y');
        $this->data['reports']   = $reports;
        $this->data['titlehead'] = "Rapor Kinerja Output Karyawan ({$this->data['from_raw']} - {$this->data['to_raw']})";

        return view($this->views . '\vtarget_harian_print_rapor_karyawan', $this->data);
    }
}
