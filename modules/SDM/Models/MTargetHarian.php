<?php

namespace Modules\SDM\Models;

use CodeIgniter\Model;

class MTargetHarian extends Model
{
    protected $table = 'sdm_target_harian';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'kode_transaksi',
        'tanggal',
        'id_shift',
        'divisi',
        'keterangan',
        'status',
        'active',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at'
    ];

    public function generateDocCode($tanggal = null)
    {
        $dt = !empty($tanggal) ? new \DateTime($tanggal) : new \DateTime();
        $prefix = "TGT" . $dt->format('ym');

        $query = $this->db->query("
            SELECT kode_transaksi 
            FROM sdm_target_harian 
            WHERE kode_transaksi LIKE '{$prefix}%'
        ");

        $rows = $query ? $query->getResult() : [];
        $maxNum = 0;
        foreach ($rows as $r) {
            $rawSuffix = substr($r->kode_transaksi, strlen($prefix));
            $num = (int) preg_replace('/\D/', '', $rawSuffix);
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        $nextNum = $maxNum + 1;
        $padLength = 4;
        $docCode = $prefix . str_pad($nextNum, $padLength, '0', STR_PAD_LEFT);

        // Anti-collision loop: pastikan nomor unik dan belum pernah dipakai
        while (true) {
            $chk = $this->db->query("SELECT 1 FROM sdm_target_harian WHERE kode_transaksi = ?", [$docCode])->getRow();
            if (!$chk) {
                break;
            }
            $nextNum++;
            $docCode = $prefix . str_pad($nextNum, $padLength, '0', STR_PAD_LEFT);
        }

        return $docCode;
    }

    public function getNonCmtEmployees()
    {
        $query = $this->db->query("
            SELECT id, nip, full_name, posisi, type, upah_harian
            FROM ref_karyawan
            WHERE active = 1 AND type = 1
            ORDER BY posisi ASC, full_name ASC
        ");
        return $query ? $query->getResult() : [];
    }

    public function getDataList($start = 0, $limit = 10, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table('sdm_target_harian h');
        $builder->select("
            h.id,
            h.kode_transaksi,
            h.tanggal,
            h.id_shift,
            h.divisi,
            h.keterangan,
            h.status,
            COUNT(d.id) AS total_pegawai,
            COALESCE(SUM(d.target_qty), 0) AS total_target,
            COALESCE(SUM(d.total_realisasi), 0) AS total_realisasi,
            CASE 
                WHEN COALESCE(SUM(d.target_qty), 0) > 0 
                THEN ROUND((SUM(d.total_realisasi) / SUM(d.target_qty) * 100)::numeric, 1)
                ELSE 0 
            END AS avg_persentase
        ");
        $builder->join('sdm_target_harian_det d', 'd.id_header = h.id AND d.active = 1', 'left');
        $builder->where('h.active', 1);

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $builder->where('h.tanggal >=', $params['from_date']);
            $builder->where('h.tanggal <=', $params['to_date']);
        }

        $builder->groupBy('h.id, h.kode_transaksi, h.tanggal, h.id_shift, h.divisi, h.keterangan, h.status');

        if (!empty($order)) {
            foreach ($order as $o) {
                $builder->orderBy($o['field'], $o['dir']);
            }
        } else {
            $builder->orderBy('h.tanggal', 'DESC');
            $builder->orderBy('h.id', 'DESC');
        }

        if ($limit > 0) {
            $builder->limit($limit, $start);
        }

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    public function getDataListCnt($filters = null, $params = null)
    {
        $builder = $this->db->table('sdm_target_harian h');
        $builder->where('h.active', 1);

        if (!empty($params['from_date']) && !empty($params['to_date'])) {
            $builder->where('h.tanggal >=', $params['from_date']);
            $builder->where('h.tanggal <=', $params['to_date']);
        }

        return $builder->countAllResults();
    }

    public function getHeaderById($id)
    {
        $id = (int)$id;
        $query = $this->db->query("SELECT * FROM sdm_target_harian WHERE id = {$id} AND active = 1");
        $rows = $query ? $query->getResult() : [];
        return !empty($rows) ? $rows[0] : null;
    }

    public function getDetailsByHeaderId($idHeader)
    {
        $idHeader = (int)$idHeader;
        $query = $this->db->query("
            SELECT d.*, k.nip, k.full_name, k.posisi as posisi_karyawan
            FROM sdm_target_harian_det d
            LEFT JOIN ref_karyawan k ON k.id = d.id_karyawan
            WHERE d.id_header = {$idHeader} AND d.active = 1
            ORDER BY k.posisi ASC, k.full_name ASC
        ");
        return $query ? $query->getResult() : [];
    }

    public function getHourlyByHeaderId($idHeader)
    {
        $idHeader = (int)$idHeader;
        $query = $this->db->query("
            SELECT * FROM sdm_target_harian_hourly 
            WHERE id_header = {$idHeader} AND active = 1
        ");
        return $query ? $query->getResult() : [];
    }

    public function getProsesHourlyByHeaderId($idHeader)
    {
        $idHeader = (int)$idHeader;
        $query = $this->db->query("
            SELECT p.*, tp.kode, tp.nama as master_nama_proses, tp.warna_badge
            FROM sdm_target_harian_proses_hourly p
            LEFT JOIN ref_target_proses tp ON tp.id = p.id_proses
            WHERE p.id_header = {$idHeader} AND p.active = 1
            ORDER BY p.id_karyawan ASC, p.seq ASC, p.id ASC
        ");
        return $query ? $query->getResult() : [];
    }

    public function parseRawCellText($text)
    {
        if (empty($text) && $text !== '0' && $text !== 0) {
            return 0;
        }

        $text = trim((string)$text);
        if (is_numeric($text)) {
            return (float) $text;
        }

        $total = 0;
        // Split by comma, plus, or semicolon
        $items = preg_split('/[,+;]+/', $text);
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') continue;

            if (preg_match('/[=:\-]/', $item)) {
                $sub = preg_split('/[=:\-]+/', $item);
                $val = isset($sub[1]) ? trim($sub[1]) : '';
                if (is_numeric($val)) {
                    $total += (float) $val;
                }
            } elseif (is_numeric($item)) {
                $total += (float) $item;
            }
        }

        return $total;
    }

    public function trxSaveData($idHeader, $headerData, $matrix, $userId)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $tglRaw = trim($headerData['tanggal'] ?? '');
            $tanggal = date('Y-m-d');
            if (!empty($tglRaw)) {
                $cleaned = str_replace(['/', '.'], '-', $tglRaw);
                if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $cleaned, $m)) {
                    $tanggal = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                } elseif (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $cleaned, $m)) {
                    $tanggal = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                } else {
                    $ts = strtotime($cleaned);
                    if ($ts && $ts > 0) {
                        $tanggal = date('Y-m-d', $ts);
                    }
                }
            }
            $id_shift   = !empty($headerData['id_shift']) ? (int)$headerData['id_shift'] : 1;
            $divisi     = !empty($headerData['divisi']) ? $headerData['divisi'] : 'ALL NON-CMT';
            $keterangan = !empty($headerData['keterangan']) ? $headerData['keterangan'] : '';
            $status     = isset($headerData['status']) ? (int)$headerData['status'] : 0;
            $userId     = !empty($userId) ? (int)$userId : 1;

            if (empty($idHeader)) {
                $kodeTrx = $this->generateDocCode($tanggal);
                
                $sqlHeader = "INSERT INTO sdm_target_harian (kode_transaksi, tanggal, id_shift, divisi, keterangan, status, active, created_by, created_at)
                              VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW()) RETURNING id";
                $qIns = $db->query($sqlHeader, [$kodeTrx, $tanggal, $id_shift, $divisi, $keterangan, $status, $userId]);
                
                if (!$qIns) {
                    $err = $db->error();
                    throw new \Exception("DB Error Header: " . ($err['message'] ?? 'Unknown error'));
                }
                
                $rowId = $qIns->getRow();
                $idHeader = $rowId ? (int)$rowId->id : 0;
                
                if (!$idHeader) {
                    throw new \Exception("Gagal mengenerate ID Header transaksi.");
                }
            } else {
                $sqlUpd = "UPDATE sdm_target_harian 
                           SET tanggal = ?, id_shift = ?, divisi = ?, keterangan = ?, status = ?, updated_by = ?, updated_at = NOW()
                           WHERE id = ?";
                $db->query($sqlUpd, [$tanggal, $id_shift, $divisi, $keterangan, $status, $userId, $idHeader]);

                $db->query("DELETE FROM sdm_target_harian_det WHERE id_header = ?", [$idHeader]);
                $db->query("DELETE FROM sdm_target_harian_hourly WHERE id_header = ?", [$idHeader]);
                $db->query("DELETE FROM sdm_target_harian_proses_hourly WHERE id_header = ?", [$idHeader]);
            }

            if (!empty($matrix) && is_array($matrix)) {
                $sqlDet = "INSERT INTO sdm_target_harian_det (id_header, id_karyawan, posisi, target_qty, target_lembur, satuan, ref_job, total_realisasi, realisasi_lembur, persentase, active, created_by, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";

                $sqlHour = "INSERT INTO sdm_target_harian_hourly (id_header, id_karyawan, jam_slot, raw_text, total_qty, active)
                            VALUES (?, ?, ?, ?, ?, 1)";

                $sqlProses = "INSERT INTO sdm_target_harian_proses_hourly (
                                id_header, id_karyawan, id_proses, nama_proses, target_qty, satuan,
                                jam_08_09, jam_09_10, jam_10_11, jam_11_12, jam_13_14, jam_14_15, jam_15_16, jam_16_17,
                                target_lembur, jam_lembur,
                                total_realisasi, seq, active, created_at
                              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";

                foreach ($matrix as $idKaryawan => $row) {
                    $idKaryawan = (int)$idKaryawan;
                    if (!$idKaryawan) continue;

                    $targetQty    = isset($row['target_qty']) ? (float) $row['target_qty'] : 0;
                    $targetLembur = isset($row['target_lembur']) ? (float) $row['target_lembur'] : 0;
                    $satuan       = isset($row['satuan']) ? trim($row['satuan']) : 'Pcs';
                    $refJob       = isset($row['ref_job']) ? trim($row['ref_job']) : '';
                    $posisi       = isset($row['posisi']) ? trim($row['posisi']) : '';

                    $totalRealisasi  = 0;
                    $realisasiLembur = 0;
                    $hourlyCells     = isset($row['hourly']) && is_array($row['hourly']) ? $row['hourly'] : [];

                    foreach ($hourlyCells as $slot => $cellText) {
                        $rawText = trim($cellText);
                        if ($rawText !== '' && $rawText !== '-') {
                            $cellQty = $this->parseRawCellText($rawText);
                            $totalRealisasi += $cellQty;
                            if (strtoupper($slot) === 'LEMBUR') {
                                $realisasiLembur += $cellQty;
                            }

                            $db->query($sqlHour, [$idHeader, $idKaryawan, $slot, $rawText, $cellQty]);
                        }
                    }

                    $grandTarget = $targetQty + $targetLembur;
                    $persentase  = ($grandTarget > 0) ? round(($totalRealisasi / $grandTarget) * 100, 2) : 0;
                    $db->query($sqlDet, [$idHeader, $idKaryawan, $posisi, $targetQty, $targetLembur, $satuan, $refJob, $totalRealisasi, $realisasiLembur, $persentase, $userId]);

                    // Save breakdown processes if submitted
                    if (!empty($row['breakdown_json'])) {
                        $breakdown = json_decode($row['breakdown_json'], true);
                        if (is_array($breakdown)) {
                            $seq = 1;
                            foreach ($breakdown as $b) {
                                $idProses      = !empty($b['id_proses']) ? (int)$b['id_proses'] : null;
                                $namaProses    = !empty($b['nama_proses']) ? trim($b['nama_proses']) : '';
                                $bTarget       = isset($b['target_qty']) ? (float)$b['target_qty'] : 0;
                                $bTargetLembur = isset($b['target_lembur']) ? (float)$b['target_lembur'] : 0;
                                $bSatuan       = !empty($b['satuan']) ? trim($b['satuan']) : 'Pcs';
                                $j08 = isset($b['jam_08_09']) ? trim((string)$b['jam_08_09']) : '';
                                $j09 = isset($b['jam_09_10']) ? trim((string)$b['jam_09_10']) : '';
                                $j10 = isset($b['jam_10_11']) ? trim((string)$b['jam_10_11']) : '';
                                $j11 = isset($b['jam_11_12']) ? trim((string)$b['jam_11_12']) : '';
                                $j13 = isset($b['jam_13_14']) ? trim((string)$b['jam_13_14']) : '';
                                $j14 = isset($b['jam_14_15']) ? trim((string)$b['jam_14_15']) : '';
                                $j15 = isset($b['jam_15_16']) ? trim((string)$b['jam_15_16']) : '';
                                $j16 = isset($b['jam_16_17']) ? trim((string)$b['jam_16_17']) : '';
                                $jLembur = isset($b['jam_lembur']) ? trim((string)$b['jam_lembur']) : '';

                                $r08 = $this->parseRawCellText($j08);
                                $r09 = $this->parseRawCellText($j09);
                                $r10 = $this->parseRawCellText($j10);
                                $r11 = $this->parseRawCellText($j11);
                                $r13 = $this->parseRawCellText($j13);
                                $r14 = $this->parseRawCellText($j14);
                                $r15 = $this->parseRawCellText($j15);
                                $r16 = $this->parseRawCellText($j16);
                                $rLembur = $this->parseRawCellText($jLembur);

                                $bTot = $r08 + $r09 + $r10 + $r11 + $r13 + $r14 + $r15 + $r16 + $rLembur;

                                if ($bTarget > 0 || $bTargetLembur > 0 || $bTot > 0 || !empty($namaProses)) {
                                    $db->query($sqlProses, [
                                        $idHeader, $idKaryawan, $idProses, $namaProses, $bTarget, $bSatuan,
                                        $j08, $j09, $j10, $j11, $j13, $j14, $j15, $j16,
                                        $bTargetLembur, $jLembur,
                                        $bTot, $seq
                                    ]);
                                    $seq++;
                                }
                            }
                        }
                    }
                }
            }

            $db->transCommit();
            return $idHeader;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function getReportRekapTim($fromDate, $toDate)
    {
        // 1. Ambil semua proses yang memiliki transaksi di periode ini
        $qProses = $this->db->query("
            SELECT DISTINCT 
                COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT') as kode_proses,
                COALESCE(tp.nama, p.nama_proses, 'Output') as nama_proses,
                COALESCE(tp.seq, 999) as seq
            FROM sdm_target_harian_proses_hourly p
            JOIN sdm_target_harian h ON h.id = p.id_header
            LEFT JOIN ref_target_proses tp ON tp.id = p.id_proses
            WHERE h.active = 1 AND p.active = 1
              AND h.tanggal >= ? AND h.tanggal <= ?
              AND p.total_realisasi > 0
            ORDER BY seq ASC, kode_proses ASC
        ", [$fromDate, $toDate]);
        $prosesList = $qProses ? $qProses->getResult() : [];

        // Fallback jika prosesList kosong
        if (empty($prosesList)) {
            $q = $this->db->query("SELECT kode as kode_proses, nama as nama_proses, seq FROM ref_target_proses WHERE active = 1 ORDER BY seq ASC");
            $prosesList = $q ? $q->getResult() : [];
        }

        // 2. Ambil semua pegawai yang memiliki target atau realisasi di periode ini
        $qPegawai = $this->db->query("
            SELECT 
                k.id as id_karyawan,
                k.full_name,
                k.nip,
                k.posisi,
                COUNT(DISTINCT h.id) as total_hari_kerja,
                COALESCE(SUM(d.target_qty), 0) as total_target,
                COALESCE(SUM(d.total_realisasi), 0) as total_realisasi
            FROM sdm_target_harian_det d
            JOIN sdm_target_harian h ON h.id = d.id_header
            JOIN ref_karyawan k ON k.id = d.id_karyawan
            WHERE h.active = 1 AND d.active = 1
              AND h.tanggal >= ? AND h.tanggal <= ?
              AND (d.target_qty > 0 OR d.total_realisasi > 0)
            GROUP BY k.id, k.full_name, k.nip, k.posisi
            ORDER BY k.posisi ASC, k.full_name ASC
        ", [$fromDate, $toDate]);
        $pegawaiList = $qPegawai ? $qPegawai->getResult() : [];

        // 3. Ambil matrix output per pegawai per proses
        $qMatrix = $this->db->query("
            SELECT 
                p.id_karyawan,
                COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT') as kode_proses,
                SUM(p.total_realisasi) as total_qty
            FROM sdm_target_harian_proses_hourly p
            JOIN sdm_target_harian h ON h.id = p.id_header
            LEFT JOIN ref_target_proses tp ON tp.id = p.id_proses
            WHERE h.active = 1 AND p.active = 1
              AND h.tanggal >= ? AND h.tanggal <= ?
            GROUP BY p.id_karyawan, COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT')
        ", [$fromDate, $toDate]);
        $matrixRows = $qMatrix ? $qMatrix->getResult() : [];

        $empProsesMap = [];
        foreach ($matrixRows as $m) {
            $empProsesMap[$m->id_karyawan][$m->kode_proses] = (float)$m->total_qty;
        }

        // Hitung total per proses (kolom bawah)
        $prosesTotals = [];
        foreach ($prosesList as $p) {
            $prosesTotals[$p->kode_proses] = 0;
        }

        $grandTotalTarget = 0;
        $grandTotalRealisasi = 0;

        foreach ($pegawaiList as &$emp) {
            $emp->proses = [];
            $rowSum = 0;
            foreach ($prosesList as $p) {
                $qty = $empProsesMap[$emp->id_karyawan][$p->kode_proses] ?? 0;
                $emp->proses[$p->kode_proses] = $qty;
                $prosesTotals[$p->kode_proses] += $qty;
                $rowSum += $qty;
            }
            if ($rowSum > 0 && (float)$emp->total_realisasi <= 0) {
                $emp->total_realisasi = $rowSum;
            }
            $target = (float)$emp->total_target;
            $real = (float)$emp->total_realisasi;
            $emp->persentase = ($target > 0) ? round(($real / $target) * 100, 2) : 0;

            $grandTotalTarget += $target;
            $grandTotalRealisasi += $real;
        }

        $grandPct = ($grandTotalTarget > 0) ? round(($grandTotalRealisasi / $grandTotalTarget) * 100, 2) : 0;

        return [
            'proses_list'           => $prosesList,
            'pegawai_list'          => $pegawaiList,
            'proses_totals'         => $prosesTotals,
            'grand_total_target'    => $grandTotalTarget,
            'grand_total_realisasi' => $grandTotalRealisasi,
            'grand_pct'             => $grandPct
        ];
    }

    public function getReportRaporKaryawan($fromDate, $toDate, $idKaryawan = null)
    {
        // 1. Tentukan daftar karyawan yang akan dicetak
        $whereKaryawan = "";
        $paramsKaryawan = [$fromDate, $toDate];
        if (!empty($idKaryawan) && $idKaryawan !== 'all') {
            $whereKaryawan = "AND k.id = ?";
            $paramsKaryawan[] = (int)$idKaryawan;
        }

        $qPegawai = $this->db->query("
            SELECT DISTINCT 
                k.id, k.nip, k.full_name, k.posisi, k.type, k.upah_harian
            FROM ref_karyawan k
            JOIN sdm_target_harian_det d ON d.id_karyawan = k.id AND d.active = 1
            JOIN sdm_target_harian h ON h.id = d.id_header AND h.active = 1
            WHERE h.tanggal >= ? AND h.tanggal <= ?
              AND (d.target_qty > 0 OR d.total_realisasi > 0)
              {$whereKaryawan}
            ORDER BY k.posisi ASC, k.full_name ASC
        ", $paramsKaryawan);
        $karyawanList = $qPegawai ? $qPegawai->getResult() : [];

        // 2. Ambil semua proses yang aktif di periode ini
        $qProses = $this->db->query("
            SELECT DISTINCT 
                COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT') as kode_proses,
                COALESCE(tp.nama, p.nama_proses, 'Output') as nama_proses,
                COALESCE(tp.seq, 999) as seq
            FROM sdm_target_harian_proses_hourly p
            JOIN sdm_target_harian h ON h.id = p.id_header
            LEFT JOIN ref_target_proses tp ON tp.id = p.id_proses
            WHERE h.active = 1 AND p.active = 1
              AND h.tanggal >= ? AND h.tanggal <= ?
              AND p.total_realisasi > 0
            ORDER BY seq ASC, kode_proses ASC
        ", [$fromDate, $toDate]);
        $prosesList = $qProses ? $qProses->getResult() : [];

        if (empty($prosesList)) {
            $q = $this->db->query("SELECT kode as kode_proses, nama as nama_proses, seq FROM ref_target_proses WHERE active = 1 ORDER BY seq ASC");
            $prosesList = $q ? $q->getResult() : [];
        }

        // 3. Untuk setiap karyawan, ambil record harian
        $reportData = [];
        foreach ($karyawanList as $karyawan) {
            // Ambil daftar proses unik yang HANYA dikerjakan karyawan ini di periode ini
            $qProsesEmp = $this->db->query("
                SELECT DISTINCT 
                    COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT') as kode_proses,
                    COALESCE(tp.nama, p.nama_proses, 'Output') as nama_proses,
                    COALESCE(tp.seq, 999) as seq
                FROM sdm_target_harian_proses_hourly p
                JOIN sdm_target_harian h ON h.id = p.id_header
                LEFT JOIN ref_target_proses tp ON tp.id = p.id_proses
                WHERE h.active = 1 AND p.active = 1
                  AND p.id_karyawan = ?
                  AND h.tanggal >= ? AND h.tanggal <= ?
                  AND p.total_realisasi > 0
                ORDER BY seq ASC, kode_proses ASC
            ", [$karyawan->id, $fromDate, $toDate]);
            $empProsesList = $qProsesEmp ? $qProsesEmp->getResult() : [];
            if (empty($empProsesList)) {
                $empProsesList = !empty($prosesList) ? $prosesList : [];
            }

            $qDays = $this->db->query("
                SELECT 
                    h.id as id_header,
                    h.kode_transaksi,
                    h.tanggal,
                    COALESCE(d.target_qty, 0) as target_qty,
                    COALESCE(d.total_realisasi, 0) as total_realisasi,
                    COALESCE(d.persentase, 0) as persentase
                FROM sdm_target_harian h
                JOIN sdm_target_harian_det d ON d.id_header = h.id AND d.id_karyawan = ? AND d.active = 1
                WHERE h.active = 1
                  AND h.tanggal >= ? AND h.tanggal <= ?
                ORDER BY h.tanggal ASC
            ", [$karyawan->id, $fromDate, $toDate]);
            $days = $qDays ? $qDays->getResult() : [];

            // Ambil breakdown proses harian
            $qDayProses = $this->db->query("
                SELECT 
                    h.tanggal,
                    COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT') as kode_proses,
                    SUM(p.total_realisasi) as total_qty
                FROM sdm_target_harian_proses_hourly p
                JOIN sdm_target_harian h ON h.id = p.id_header
                LEFT JOIN ref_target_proses tp ON tp.id = p.id_proses
                WHERE h.active = 1 AND p.active = 1
                  AND p.id_karyawan = ?
                  AND h.tanggal >= ? AND h.tanggal <= ?
                GROUP BY h.tanggal, COALESCE(NULLIF(TRIM(tp.kode), ''), NULLIF(TRIM(p.nama_proses), ''), 'OUTPUT')
            ", [$karyawan->id, $fromDate, $toDate]);
            $dayProsesRows = $qDayProses ? $qDayProses->getResult() : [];

            $dayProsesMap = [];
            foreach ($dayProsesRows as $dp) {
                $dayProsesMap[$dp->tanggal][$dp->kode_proses] = (float)$dp->total_qty;
            }

            $prosesTotals = [];
            foreach ($empProsesList as $p) {
                $prosesTotals[$p->kode_proses] = 0;
            }

            $totalTarget = 0;
            $totalReal = 0;

            foreach ($days as &$d) {
                $d->proses = [];
                $daySum = 0;
                foreach ($empProsesList as $p) {
                    $qty = $dayProsesMap[$d->tanggal][$p->kode_proses] ?? 0;
                    $d->proses[$p->kode_proses] = $qty;
                    $prosesTotals[$p->kode_proses] += $qty;
                    $daySum += $qty;
                }
                if ($daySum > 0 && (float)$d->total_realisasi <= 0) {
                    $d->total_realisasi = $daySum;
                }
                $tgt = (float)$d->target_qty;
                $rel = (float)$d->total_realisasi;
                $d->persentase = ($tgt > 0) ? round(($rel / $tgt) * 100, 2) : 0;

                $totalTarget += $tgt;
                $totalReal += $rel;
            }

            $avgPct = ($totalTarget > 0) ? round(($totalReal / $totalTarget) * 100, 2) : 0;

            $reportData[] = [
                'karyawan'      => $karyawan,
                'proses_list'   => $empProsesList,
                'days'          => $days,
                'proses_totals' => $prosesTotals,
                'total_target'  => $totalTarget,
                'total_real'    => $totalReal,
                'avg_pct'       => $avgPct
            ];
        }

        return $reportData;
    }
}