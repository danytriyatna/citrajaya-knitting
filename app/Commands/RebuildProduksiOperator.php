<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Transaction\Models\BarangMasukModel;

/**
 * MAINTENANCE ONLY (2026-08-08)
 *
 * Membangun ulang trans_produksi_operator dari trans_barang_masuk_produksi
 * (ground truth) utk daftar dokumen (id_header) yang datanya tidak cocok -
 * lihat BarangMasukModel::rebuildProduksiOperatorForHeader() utk detail
 * logika & alasan kenapa ini dibutuhkan (2 bug terpisah yang pernah
 * menyebabkan trans_produksi_operator kelebihan ATAU kekurangan data
 * dibanding trans_barang_masuk_produksi).
 *
 * CARA PAKAI:
 *   1. Jalankan salah satu skrip verifikasi di folder sql/ (misalnya
 *      2026-08-08_verifikasi_qty_nilai_semua.sql), export hasilnya ke
 *      CSV. CSV HARUS punya kolom header "id_header".
 *   2. BACKUP dulu tabel trans_produksi_operator &
 *      trans_walkorder_proses_ukuran.
 *   3. Dry-run dulu (TIDAK menulis apa pun ke DB), cek hasilnya masuk akal:
 *        php spark maintenance:rebuild-produksi-operator /path/ke/hasil.csv
 *   4. Kalau sudah yakin, jalankan sungguhan dengan --apply:
 *        php spark maintenance:rebuild-produksi-operator /path/ke/hasil.csv --apply
 *   5. Setelah selesai, jalankan lagi skrip verifikasi SQL utk pastikan
 *      semua sudah cocok.
 *
 * Command ini AMAN dijalankan berkali-kali (idempotent) - tiap dokumen
 * diproses independen, per-identitas di dalam dibangun ulang dari nol
 * berdasarkan data trans_barang_masuk_produksi saat ini.
 */
class RebuildProduksiOperator extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'maintenance:rebuild-produksi-operator';
    protected $description = 'Bangun ulang trans_produksi_operator dari trans_barang_masuk_produksi utk daftar id_header di file CSV.';
    protected $usage       = 'maintenance:rebuild-produksi-operator <csv_path> [--apply]';
    protected $arguments   = [
        'csv_path' => 'Path ke file CSV hasil export query verifikasi (harus punya kolom "id_header").',
    ];
    protected $options = [
        '--apply' => 'Tulis perubahan ke database. Tanpa opsi ini, command hanya dry-run (laporan saja, TIDAK mengubah data).',
    ];

    public function run(array $params)
    {
        $csvPath = $params[0] ?? null;
        $apply   = (bool) CLI::getOption('apply');

        if (empty($csvPath) || !is_file($csvPath)) {
            CLI::error('File CSV tidak ditemukan: ' . ($csvPath ?? '(kosong)'));
            CLI::write('Pemakaian: ' . $this->usage);
            return;
        }

        $rows = array_map('str_getcsv', file($csvPath));
        $header = array_map(fn($h) => trim($h, " \t\n\r\0\x0B\""), array_shift($rows));
        $idxIdHeader = array_search('id_header', $header);

        if ($idxIdHeader === false) {
            CLI::error('Kolom "id_header" tidak ditemukan di header CSV. Kolom yang ada: ' . implode(', ', $header));
            return;
        }

        $idHeaders = [];
        foreach ($rows as $r) {
            if (!isset($r[$idxIdHeader])) continue;
            $val = trim($r[$idxIdHeader], " \t\n\r\0\x0B\"");
            if ($val !== '' && is_numeric($val)) {
                $idHeaders[] = (int) $val;
            }
        }
        $idHeaders = array_values(array_unique($idHeaders));

        if (empty($idHeaders)) {
            CLI::error('Tidak ada id_header valid ditemukan di CSV.');
            return;
        }

        CLI::write('Mode: ' . ($apply ? CLI::color('APPLY (menulis ke DB)', 'red') : CLI::color('DRY-RUN (laporan saja, tidak ada perubahan)', 'yellow')));
        CLI::write('Jumlah dokumen akan diproses: ' . count($idHeaders));
        CLI::newLine();

        if (!$apply) {
            CLI::write('Ini dry-run. Jalankan lagi dengan --apply setelah hasil di bawah dicek dan masuk akal.', 'yellow');
            CLI::newLine();
        }

        $model = new BarangMasukModel();

        $sukses = 0;
        $dilewati = 0;
        $gagal = 0;
        $totalQtyBaru = 0;
        $totalNilaiBaru = 0;
        $logRows = [];

        foreach ($idHeaders as $i => $idHeader) {
            $hasil = $model->rebuildProduksiOperatorForHeader($idHeader, !$apply);

            $no = $i + 1;
            $total = count($idHeaders);

            if (!empty($hasil['status'])) {
                if ($apply) {
                    $sukses++;
                    CLI::write("[$no/$total] id_header=$idHeader ({$hasil['kode_transaksi']}): OK - {$hasil['jumlah_identitas']} baris ditulis, qty=" . $hasil['total_qty_baru'] . ', nilai=' . $hasil['total_nilai_baru'], 'green');
                } else {
                    $sukses++;
                    CLI::write("[$no/$total] id_header=$idHeader ({$hasil['kode_transaksi']}): akan jadi {$hasil['jumlah_identitas']} baris, qty=" . $hasil['total_qty_baru'] . ', nilai=' . $hasil['total_nilai_baru']);
                }
                $totalQtyBaru += $hasil['total_qty_baru'];
                $totalNilaiBaru += $hasil['total_nilai_baru'];

                if (!empty($hasil['errors'])) {
                    foreach ($hasil['errors'] as $err) {
                        CLI::write("    ! $err", 'yellow');
                    }
                }
            } else {
                $dilewati++;
                CLI::write("[$no/$total] id_header=$idHeader: DILEWATI - {$hasil['message']}", 'yellow');
            }

            $logRows[] = [
                $idHeader,
                $hasil['kode_transaksi'] ?? '',
                !empty($hasil['status']) ? 'ok' : 'dilewati',
                $hasil['message'] ?? '',
                $hasil['jumlah_identitas'] ?? '',
                $hasil['total_qty_baru'] ?? '',
                $hasil['total_nilai_baru'] ?? '',
                !empty($hasil['errors']) ? implode(' | ', $hasil['errors']) : '',
            ];
        }

        CLI::newLine();
        CLI::write('=== RINGKASAN ===', 'cyan');
        CLI::write("Diproses OK   : $sukses");
        CLI::write("Dilewati      : $dilewati");
        CLI::write('Total qty baru (gabungan semua dokumen di atas)   : ' . $totalQtyBaru);
        CLI::write('Total nilai baru (gabungan semua dokumen di atas) : ' . $totalNilaiBaru);

        $logPath = WRITEPATH . 'logs/rebuild_produksi_operator_' . date('Ymd_His') . '.csv';
        $fp = fopen($logPath, 'w');
        fputcsv($fp, ['id_header', 'kode_transaksi', 'status', 'message', 'jumlah_identitas', 'total_qty_baru', 'total_nilai_baru', 'errors']);
        foreach ($logRows as $lr) {
            fputcsv($fp, $lr);
        }
        fclose($fp);
        CLI::write('Log detail tersimpan di: ' . $logPath);

        if (!$apply) {
            CLI::newLine();
            CLI::write('Ini masih DRY-RUN - belum ada perubahan ke database. Cek log di atas, lalu jalankan ulang dengan --apply kalau sudah yakin.', 'yellow');
        }
    }
}
