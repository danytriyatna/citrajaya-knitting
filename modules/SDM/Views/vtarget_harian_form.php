<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal_input_detail" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white py-2">
        <h5 class="modal-title font-weight-bold m-0"><i class="fa fa-list-ul me-2"></i>Detail Breakdown Output Per Jenis Pekerjaan / Proses</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-2">
        <div class="card bg-light border-0 mb-2">
          <div class="card-body py-2 px-3">
            <div class="row align-items-center">
              <div class="col-md-5">
                <span class="text-muted small">Nama Pegawai:</span>
                <h6 class="m-0 font-weight-bold text-dark" id="modal_emp_name">-</h6>
              </div>
              <div class="col-md-3">
                <span class="text-muted small">NIP:</span>
                <div class="font-weight-bold text-primary" id="modal_emp_nip">-</div>
              </div>
              <div class="col-md-4">
                <span class="text-muted small">Posisi / Jabatan:</span>
                <div><span class="badge bg-secondary text-white" id="modal_emp_posisi">-</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
          <table class="table table-bordered table-sm mb-0" id="table_modal_breakdown">
            <thead class="bg-light text-center" style="position: sticky; top: 0; z-index: 5;">
              <tr>
                <th width="4%">Aksi</th>
                <th width="3%">No</th>
                <th width="20%">Jenis Proses / Pekerjaan</th>
                <th width="7%" class="bg-warning text-dark font-weight-bold">Target</th>
                <th width="5%">Satuan</th>
                <?php foreach ($time_slots as $slot): ?>
                  <th width="6%" class="bg-light text-primary"><?= $slot ?></th>
                <?php endforeach; ?>
                <th width="6%" class="font-weight-bold" style="background-color: #fff3cd !important; color: #856404;" title="Target Jam Lembur">Tgt Lembur</th>
                <th width="6%" class="font-weight-bold" style="background-color: #ffe69c !important; color: #856404;" title="Realisasi Output Jam Lembur">Realisasi Lembur</th>
                <th width="7%" class="bg-success text-white font-weight-bold">Total Realisasi</th>
              </tr>
            </thead>
            <tbody id="tbody_modal_breakdown">
              <!-- Dynamic Rows -->
            </tbody>
            <tfoot>
              <tr class="table-dark font-weight-bold">
                <td></td>
                <td colspan="2" class="text-end py-2">TOTAL AKUMULASI:</td>
                <td class="text-end py-2 text-warning" id="modal_foot_target">0</td>
                <td class="text-center py-2" id="modal_foot_satuan">Pcs</td>
                <td class="text-center py-2 text-info" id="modal_foot_08">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_09">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_10">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_11">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_13">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_14">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_15">0</td>
                <td class="text-center py-2 text-info" id="modal_foot_16">0</td>
                <td class="text-end py-2 text-warning" id="modal_foot_target_lembur">0</td>
                <td class="text-center py-2 text-warning" id="modal_foot_lembur">0</td>
                <td class="text-end py-2 text-success" id="modal_foot_realisasi" style="font-size: 14px;">0</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="mt-2">
          <button type="button" class="btn btn-outline-primary btn-sm" id="btn_add_modal_row">
            <i class="fa fa-plus-circle me-1"></i> Tambah Baris Proses
          </button>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i> Batal</button>
        <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btn_apply_modal_breakdown"><i class="fa fa-check me-1"></i> Terapkan ke Matrix</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection('modal') ?>

<?= $this->section('content'); ?>
<style>
.matrix-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.matrix-table th, .matrix-table td {
    vertical-align: middle !important;
    font-size: 13px;
    padding: 5px 6px !important;
}
.matrix-table thead th {
    position: sticky;
    top: 0;
    background-color: #f4f6f9;
    z-index: 10;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.matrix-sticky-col {
    position: sticky;
    left: 0;
    background-color: #fff;
    z-index: 9;
    font-weight: bold;
    box-shadow: 2px 0 3px rgba(0,0,0,0.05);
}
.matrix-cell-input {
    min-width: 95px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    border-radius: 4px;
    border: 1px solid #ced4da;
    background-color: #f8f9fa !important;
    cursor: default;
    padding: 4px 6px !important;
}
.target-input {
    width: 75px;
    text-align: right;
    font-weight: bold;
    background-color: #f8f9fa !important;
    cursor: default;
}
.satuan-input {
    width: 55px;
    background-color: #f8f9fa !important;
    cursor: default;
}
.process-badge {
    display: inline-block;
    font-size: 12px;
    font-weight: bold;
    padding: 4px 10px;
    border-radius: 14px;
    margin: 3px 5px;
}
.btn-input-detail {
    font-size: 11px;
    font-weight: 600;
}
</style>

<div class="container-fluid">

  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h4 class="text-themecolor"><?= empty($id_enc) ? 'Tambah' : 'Edit' ?> <?= $titlehead ?></h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
      <div class="d-flex justify-content-end align-items-center">
        <?php if (!empty($id_enc)): ?><a href="<?= base_url('sdm/target-harian/print/' . $id_enc) ?>" target="_blank" class="btn btn-info btn-sm shadow-sm me-2" id="btn_print_doc"><i class="fa fa-print me-1"></i> Cetak / PDF</a><?php endif; ?><a href="<?= base_url('sdm/target-harian') ?>" class="btn btn-secondary btn-sm shadow-sm me-2">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-success btn-sm shadow-sm" id="btn_save_all">
            <i class="fa fa-save me-1"></i> Simpan Data Matrix
        </button>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <form id="form_matrix">
        <input type="hidden" name="id" value="<?= $id_enc ?>">

        <!-- Header Info Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="small font-weight-bold mb-1">No. Transaksi:</label>
                        <input type="text" id="no_transaksi_preview" class="form-control form-control-sm bg-light font-weight-bold" value="<?= $header->kode_transaksi ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="small font-weight-bold mb-1">Tanggal Kerja:</label>
                        <input type="text" name="tanggal" id="tanggal" class="form-control form-control-sm datepicker font-weight-bold" value="<?= !empty($header->tanggal) ? date('d/m/Y', strtotime($header->tanggal)) : date('d/m/Y') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="small font-weight-bold mb-1">Status Dokumen:</label>
                        <select name="status" class="form-control form-control-sm font-weight-bold">
                            <option value="0" <?= $header->status == 0 ? 'selected' : '' ?>>Draft / Berjalan</option>
                            <option value="1" <?= $header->status == 1 ? 'selected' : '' ?>>Final / Selesai Shift</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Table Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="m-0 font-weight-bold text-dark"><i class="fa fa-th me-2"></i>Matrix Catatan Output Per Jam (Hourly Sheet)</h6>
                    </div>
                    <div class="col-md-6 text-right">
                        <span class="badge bg-primary text-white"><i class="fa fa-info-circle me-1"></i>Klik tombol <strong>Input Detail</strong> pada setiap pegawai untuk breakdown proses</span>
                    </div>
                </div>
                <?php if (!empty($target_processes)): ?>
                <div class="mt-2 pt-2 border-top d-flex flex-wrap align-items-center">
                    <small class="text-dark font-weight-bold me-2"><i class="fa fa-tags me-1"></i>Master Proses Produksi:</small>
                    <?php foreach ($target_processes as $tp): ?>
                        <span class="badge text-white px-2 py-1 me-1 mb-1 shadow-sm" style="background-color: <?= $tp->warna_badge ?>; font-size: 11px;" title="<?= htmlspecialchars($tp->nama) ?> (<?= $tp->satuan ?>)">
                            <strong><?= $tp->kode ?></strong> = <?= $tp->nama ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm matrix-table mb-0">
                        <thead class="text-center">
                            <tr>
                                <th width="7%" class="matrix-sticky-col text-center" style="min-width: 115px;">Aksi</th>
                                <th width="3%">No</th>
                                <th width="14%">Nama Pegawai</th>
                                <th width="10%">Posisi</th>
                                <th width="7%" class="bg-warning text-dark font-weight-bold">Target (Qty)</th>
                                <th width="5%">Satuan</th>
                                <?php foreach ($time_slots as $slot): ?>
                                    <th class="bg-light text-primary"><?= $slot ?></th>
                                <?php endforeach; ?>
                                <th class="font-weight-bold" style="background-color: #fff3cd !important; color: #856404; border-left: 2px solid #ffc107; min-width: 80px;" title="Target Jam Lembur"><i class="fa fa-moon-o me-1"></i>Tgt Lembur</th>
                                <th class="font-weight-bold" style="background-color: #ffe69c !important; color: #856404; border-right: 2px solid #ffc107; min-width: 95px;" title="Realisasi Output Jam Lembur"><i class="fa fa-clock-o me-1"></i>Realisasi Lembur</th>
                                <th width="7%" class="bg-success text-white font-weight-bold">Total Realisasi</th>
                                <th width="7%" class="bg-info text-white font-weight-bold">% Capaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $idx => $row): ?>
                                <tr data-karyawan-id="<?= $row->id_karyawan ?>">
                                    <td class="text-center matrix-sticky-col" style="min-width: 115px;">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button" class="btn btn-xs btn-warning text-dark btn-input-detail me-1 shadow-sm px-2 py-1 text-nowrap font-weight-bold" 
                                                    data-karyawan-id="<?= $row->id_karyawan ?>" 
                                                    data-nama="<?= htmlspecialchars($row->full_name) ?>" 
                                                    data-nip="<?= htmlspecialchars($row->nip) ?>" 
                                                    data-posisi="<?= htmlspecialchars($row->posisi) ?>"
                                                    title="Input Detail / Edit Breakdown Proses">
                                                <i class="fa fa-edit me-1"></i> Detail
                                                <span class="badge bg-dark text-white ms-1 badge-process-count" style="font-size: 9px;"><?= !empty($prosesHourlyMap[$row->id_karyawan]) ? count($prosesHourlyMap[$row->id_karyawan]) : '' ?></span>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-danger btn-del-matrix-row shadow-sm px-2 py-1" 
                                                    data-nama="<?= htmlspecialchars($row->full_name) ?>"
                                                    title="Hapus Pegawai dari Form">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-center row-number"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="font-weight-bold text-dark"><?= $row->full_name ?></div>
                                        <small class="text-muted"><?= $row->nip ?></small>
                                        <input type="hidden" name="matrix[<?= $row->id_karyawan ?>][posisi]" value="<?= $row->posisi ?>">
                                        <input type="hidden" name="matrix[<?= $row->id_karyawan ?>][breakdown_json]" class="breakdown-json-input" value='<?= !empty($prosesHourlyMap[$row->id_karyawan]) ? json_encode($prosesHourlyMap[$row->id_karyawan], JSON_HEX_APOS | JSON_HEX_QUOT) : "" ?>'>
                                    </td>
                                    <td><span class="badge bg-secondary text-white"><?= $row->posisi ?></span></td>
                                    <td>
                                        <input type="text" name="matrix[<?= $row->id_karyawan ?>][target_qty]" class="form-control form-control-sm target-input" value="<?= $row->target_qty > 0 ? (float)$row->target_qty : '' ?>" placeholder="0" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="matrix[<?= $row->id_karyawan ?>][satuan]" class="form-control form-control-sm text-center satuan-input" value="<?= !empty($row->satuan) ? $row->satuan : 'Pcs' ?>" readonly>
                                    </td>

                                    <!-- Hourly Slots (Readonly Display) -->
                                    <?php foreach ($time_slots as $slot): ?>
                                        <?php 
                                            $val = isset($hourlyMap[$row->id_karyawan][$slot]) ? $hourlyMap[$row->id_karyawan][$slot] : '';
                                        ?>
                                        <td class="text-center p-1">
                                            <input type="text" name="matrix[<?= $row->id_karyawan ?>][hourly][<?= $slot ?>]" class="form-control form-control-sm matrix-cell-input" value="<?= htmlspecialchars($val) ?>" placeholder="-" readonly>
                                        </td>
                                    <?php endforeach; ?>

                                    <!-- Jam Lembur Columns -->
                                    <td class="text-center p-1" style="border-left: 2px solid #ffc107; background-color: #fffdf5;">
                                        <input type="text" name="matrix[<?= $row->id_karyawan ?>][target_lembur]" class="form-control form-control-sm target-lembur-input text-end font-weight-bold" value="<?= !empty($row->target_lembur) && (float)$row->target_lembur > 0 ? (float)$row->target_lembur : '' ?>" placeholder="0" readonly>
                                    </td>
                                    <td class="text-center p-1" style="border-right: 2px solid #ffc107; background-color: #fffdf5;">
                                        <?php 
                                            $valLembur = isset($hourlyMap[$row->id_karyawan]['LEMBUR']) ? $hourlyMap[$row->id_karyawan]['LEMBUR'] : (!empty($row->realisasi_lembur) && (float)$row->realisasi_lembur > 0 ? (float)$row->realisasi_lembur : '');
                                        ?>
                                        <input type="text" name="matrix[<?= $row->id_karyawan ?>][hourly][LEMBUR]" class="form-control form-control-sm matrix-cell-input lembur-cell-input text-center font-weight-bold" value="<?= htmlspecialchars($valLembur) ?>" placeholder="-" readonly>
                                    </td>

                                    <!-- Total Realisasi & % -->
                                    <td class="text-right font-weight-bold text-success total-realisasi-cell" style="font-size: 14px;">
                                        <?= (float)$row->total_realisasi ?>
                                    </td>
                                    <td class="text-center font-weight-bold persentase-cell">
                                        <span class="badge <?= $row->persentase >= 100 ? 'bg-success text-white' : ($row->persentase >= 80 ? 'bg-warning text-dark' : 'bg-danger text-white') ?> px-2 py-1">
                                            <?= (float)$row->persentase ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rekap Total Output Per Proses & Keterangan Catatan -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm border-info h-100">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="m-0 font-weight-bold"><i class="fa fa-pie-chart me-2"></i>Rekap Total Output Per Jenis Proses / Pekerjaan Hari Ini</h6>
                    </div>
                    <div class="card-body py-3">
                        <div id="rekap_proses_container" class="d-flex flex-wrap align-items-center">
                            <span class="text-muted small">Input detail pada pegawai di atas untuk melihat rekapitulasi per proses...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light py-2">
                        <h6 class="m-0 font-weight-bold text-dark"><i class="fa fa-sticky-note-o me-2"></i>Catatan Khusus / Keterangan Shift</h6>
                    </div>
                    <div class="card-body py-2">
                        <textarea name="keterangan" class="form-control form-control-sm" rows="3" placeholder="Catatan kendala mesin, listrik, lembur, dsb..."><?= htmlspecialchars($header->keterangan ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

      </form>
    </div>
  </div>

</div>
<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<script>
$(document).ready(function() {
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    var masterProcesses = <?= json_encode($target_processes ?? []) ?>;
    var currentActiveRow = null;

    // Helper parser di JS: mendukung format "K=10, P=20, Qc=26", "10, 20, 30", "10+20", atau "150"
    function parseSlotNumber(text) {
        if (text === undefined || text === null) return 0;
        text = text.toString().trim();
        if (text === '' || text === '-') return 0;
        if (!isNaN(text)) return parseFloat(text);

        var total = 0;
        var parts = text.split(/[,+;]+/);
        for (var i = 0; i < parts.length; i++) {
            var p = parts[i].trim();
            if (!p) continue;
            if (p.indexOf('=') !== -1 || p.indexOf(':') !== -1 || p.indexOf('-') !== -1) {
                var match = p.split(/[=:\-]+/);
                var num = parseFloat(match[1]);
                if (!isNaN(num)) total += num;
            } else if (!isNaN(p)) {
                total += parseFloat(p);
            }
        }
        return total;
    }

    function parseCellItems(text) {
        var res = { total: 0, breakdown: {} };
        if (!text) return res;

        text = text.toString().trim();
        if (!isNaN(text) && text !== '') {
            var num = parseFloat(text);
            res.total = num;
            res.breakdown['OUTPUT'] = num;
            return res;
        }

        var items = text.split(/[,+;]+/);
        for (var i = 0; i < items.length; i++) {
            var item = items[i].trim();
            if (!item) continue;

            if (item.indexOf('=') !== -1 || item.indexOf(':') !== -1 || item.indexOf('-') !== -1) {
                var match = item.split(/[=:\-]+/);
                var code = (match[0] || 'LAINNYA').trim().toUpperCase();
                var val = parseFloat(match[1]);
                if (!isNaN(val)) {
                    res.total += val;
                    res.breakdown[code] = (res.breakdown[code] || 0) + val;
                }
            } else if (!isNaN(item)) {
                var val = parseFloat(item);
                res.total += val;
                res.breakdown['OUTPUT'] = (res.breakdown['OUTPUT'] || 0) + val;
            }
        }
        return res;
    }

    function recalculateAll() {
        var globalProcessTotals = {};

        $('.matrix-table tbody tr').each(function() {
            var $tr = $(this);
            var target = (parseFloat($tr.find('.target-input').val()) || 0) + (parseFloat($tr.find('.target-lembur-input').val()) || 0);
            var totalReal = 0;
            var rowBreakdown = {};

            // If breakdown JSON exists, use direct process totals
            var jsonStr = $tr.find('.breakdown-json-input').val();
            var hasJsonData = false;
            if (jsonStr) {
                try {
                    var bData = JSON.parse(jsonStr);
                    if (Array.isArray(bData) && bData.length > 0) {
                        hasJsonData = true;
                        bData.forEach(function(item) {
                            var pCode = item.kode || (item.nama_proses ? item.nama_proses.substring(0, 3).toUpperCase() : 'OUTPUT');
                            var pTot = parseFloat(item.total_realisasi) || 0;
                            if (pTot > 0) {
                                globalProcessTotals[pCode] = (globalProcessTotals[pCode] || 0) + pTot;
                                rowBreakdown[pCode] = (rowBreakdown[pCode] || 0) + pTot;
                            }
                            totalReal += pTot;
                        });
                    }
                } catch(e){}
            }

            if (!hasJsonData) {
                $tr.find('.matrix-cell-input').each(function() {
                    var val = $(this).val();
                    var parsed = parseCellItems(val);
                    totalReal += parsed.total;

                    for (var code in parsed.breakdown) {
                        rowBreakdown[code] = (rowBreakdown[code] || 0) + parsed.breakdown[code];
                        globalProcessTotals[code] = (globalProcessTotals[code] || 0) + parsed.breakdown[code];
                    }
                });
            }

            var tipText = 'Total: ' + totalReal;
            var tipParts = [];
            for (var c in rowBreakdown) {
                tipParts.push(c + ': ' + rowBreakdown[c]);
            }
            if (tipParts.length > 0) {
                tipText += ' (' + tipParts.join(' | ') + ')';
            }
            $tr.find('.total-realisasi-cell').attr('title', tipText);

            var breakdownHtml = '';
            if (tipParts.length > 0) {
                breakdownHtml = '<small class="d-block text-muted fw-normal mt-1" style="font-size: 10.5px; line-height: 1.2;">' + tipParts.join(' | ') + '</small>';
            }
            $tr.find('.total-realisasi-cell').html('<div>' + totalReal.toLocaleString() + '</div>' + breakdownHtml);

            var pct = (target > 0) ? Math.round((totalReal / target) * 100 * 10) / 10 : 0;
            var badgeCls = 'bg-danger text-white';
            if (pct >= 100) badgeCls = 'bg-success text-white';
            else if (pct >= 80) badgeCls = 'bg-warning text-dark';

            $tr.find('.persentase-cell').html('<span class="badge ' + badgeCls + ' px-2 py-1" title="' + tipText + '">' + pct + '%</span>');
        });

        var masterMap = {};
        if (Array.isArray(masterProcesses)) {
            masterProcesses.forEach(function(item) {
                if (item.kode) {
                    masterMap[item.kode.toUpperCase()] = item;
                }
            });
        }

        var badgeHtml = '';
        for (var pCode in globalProcessTotals) {
            var label = pCode;
            var bgColor = '#007bff';
            var satuan = 'Pcs';

            if (masterMap[pCode]) {
                label = masterMap[pCode].nama + ' (' + masterMap[pCode].kode + ')';
                bgColor = masterMap[pCode].warna_badge || '#007bff';
                satuan = masterMap[pCode].satuan || 'Pcs';
            } else if (pCode === 'OUTPUT') {
                label = 'Output Umum';
                bgColor = '#28a745';
            }

            badgeHtml += '<span class="process-badge text-white shadow-sm" style="background-color: ' + bgColor + ';">' 
                      + label + ': <strong>' + globalProcessTotals[pCode].toLocaleString() + ' ' + satuan + '</strong></span>';
        }

        if (badgeHtml === '') {
            badgeHtml = '<span class="text-muted small">Input detail pada pegawai di atas untuk melihat rekapitulasi per proses...</span>';
        }
        $('#rekap_proses_container').html(badgeHtml);
    }

    // Modal Helper: Build Process Select Options
    function buildProcessOptions(selectedId, selectedName) {
        var html = '<option value="">-- Pilih Jenis Proses --</option>';
        var matched = false;
        if (Array.isArray(masterProcesses)) {
            masterProcesses.forEach(function(p) {
                var isSel = (selectedId && parseInt(selectedId) === parseInt(p.id)) || (selectedName && p.nama.toLowerCase() === selectedName.toLowerCase());
                if (isSel) matched = true;
                html += '<option value="' + p.id + '" data-kode="' + (p.kode || '') + '" data-satuan="' + (p.satuan || 'Pcs') + '" data-warna="' + (p.warna_badge || '#6c757d') + '" data-target="' + (p.target_harian || 0) + '" ' + (isSel ? 'selected' : '') + '>' + p.nama + ' (' + p.kode + ')</option>';
            });
        }
        if (!matched && selectedName) {
            html += '<option value="" selected>' + selectedName + ' (Kustom)</option>';
        }
        return html;
    }

    // Modal Helper: Add Row to Breakdown Table
    function addModalBreakdownRow(data) {
        data = data || {};
        var rowCount = $('#tbody_modal_breakdown tr').length + 1;
        var pId = data.id_proses || '';
        var pName = data.nama_proses || '';
        var targetQty = data.target_qty !== undefined ? data.target_qty : '';
        var targetLembur = data.target_lembur !== undefined ? data.target_lembur : '';
        var satuan = data.satuan || 'Pcs';
        var j08 = data.jam_08_09 !== undefined ? (data.jam_08_09 || '') : '';
        var j09 = data.jam_09_10 !== undefined ? (data.jam_09_10 || '') : '';
        var j10 = data.jam_10_11 !== undefined ? (data.jam_10_11 || '') : '';
        var j11 = data.jam_11_12 !== undefined ? (data.jam_11_12 || '') : '';
        var j13 = data.jam_13_14 !== undefined ? (data.jam_13_14 || '') : '';
        var j14 = data.jam_14_15 !== undefined ? (data.jam_14_15 || '') : '';
        var j15 = data.jam_15_16 !== undefined ? (data.jam_15_16 || '') : '';
        var j16 = data.jam_16_17 !== undefined ? (data.jam_16_17 || '') : '';
        var jLembur = data.jam_lembur !== undefined ? (data.jam_lembur || '') : '';
        var totReal = data.total_realisasi !== undefined ? data.total_realisasi : 0;

        var trHtml = '<tr class="modal-b-row">' +
            '<td class="text-center align-middle">' +
                '<button type="button" class="btn btn-outline-danger btn-xs btn-del-modal-row" title="Hapus Baris"><i class="fa fa-trash"></i></button>' +
            '</td>' +
            '<td class="text-center align-middle modal-row-num">' + rowCount + '</td>' +
            '<td>' +
                '<select class="form-control form-control-sm modal-select-proses font-weight-bold">' +
                    buildProcessOptions(pId, pName) +
                '</select>' +
                '<input type="hidden" class="modal-inp-nama-proses" value="' + (pName || '') + '">' +
                '<input type="hidden" class="modal-inp-kode-proses" value="' + (data.kode || '') + '">' +
            '</td>' +
            '<td><input type="number" step="any" class="form-control form-control-sm text-end modal-inp-target font-weight-bold" value="' + targetQty + '" placeholder="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-inp-satuan" value="' + satuan + '"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="08" value="' + j08 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="09" value="' + j09 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="10" value="' + j10 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="11" value="' + j11 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="13" value="' + j13 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="14" value="' + j14 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="15" value="' + j15 + '" placeholder="-"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" data-slot="16" value="' + j16 + '" placeholder="-"></td>' +
            '<td><input type="number" step="any" class="form-control form-control-sm text-end modal-inp-target-lembur font-weight-bold" style="background-color: #fffdf5; border-color: #ffc107;" value="' + targetLembur + '" placeholder="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm text-center modal-hour-cell font-weight-bold" style="background-color: #fffdf5; border-color: #ffc107;" data-slot="lembur" value="' + jLembur + '" placeholder="-"></td>' +
            '<td class="text-end align-middle font-weight-bold text-success modal-row-tot-real" style="font-size: 13px;">' + totReal + '</td>' +
        '</tr>';

        $('#tbody_modal_breakdown').append(trHtml);
        calcModalFooterTotals();
    }

    // Modal Helper: Calculate Footer Totals
    function calcModalFooterTotals() {
        var totTarget = 0;
        var totTargetLembur = 0;
        var h08 = 0, h09 = 0, h10 = 0, h11 = 0, h13 = 0, h14 = 0, h15 = 0, h16 = 0, hLembur = 0;
        var grandReal = 0;
        var commonSatuan = 'Pcs';

        $('#tbody_modal_breakdown tr.modal-b-row').each(function(idx) {
            $(this).find('.modal-row-num').text(idx + 1);
            var $r = $(this);
            var t = parseFloat($r.find('.modal-inp-target').val()) || 0;
            var tLembur = parseFloat($r.find('.modal-inp-target-lembur').val()) || 0;
            totTarget += t;
            totTargetLembur += tLembur;

            var r08 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="08"]').val());
            var r09 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="09"]').val());
            var r10 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="10"]').val());
            var r11 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="11"]').val());
            var r13 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="13"]').val());
            var r14 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="14"]').val());
            var r15 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="15"]').val());
            var r16 = parseSlotNumber($r.find('.modal-hour-cell[data-slot="16"]').val());
            var rLembur = parseSlotNumber($r.find('.modal-hour-cell[data-slot="lembur"]').val());

            var rowTot = r08 + r09 + r10 + r11 + r13 + r14 + r15 + r16 + rLembur;
            $r.find('.modal-row-tot-real').text(rowTot.toLocaleString());

            h08 += r08; h09 += r09; h10 += r10; h11 += r11;
            h13 += r13; h14 += r14; h15 += r15; h16 += r16;
            hLembur += rLembur;
            grandReal += rowTot;

            var sat = $r.find('.modal-inp-satuan').val();
            if (sat) commonSatuan = sat;
        });

        $('#modal_foot_target').text(totTarget.toLocaleString());
        $('#modal_foot_satuan').text(commonSatuan);
        $('#modal_foot_08').text(h08.toLocaleString());
        $('#modal_foot_09').text(h09.toLocaleString());
        $('#modal_foot_10').text(h10.toLocaleString());
        $('#modal_foot_11').text(h11.toLocaleString());
        $('#modal_foot_13').text(h13.toLocaleString());
        $('#modal_foot_14').text(h14.toLocaleString());
        $('#modal_foot_15').text(h15.toLocaleString());
        $('#modal_foot_16').text(h16.toLocaleString());
        $('#modal_foot_target_lembur').text(totTargetLembur.toLocaleString());
        $('#modal_foot_lembur').text(hLembur.toLocaleString());
        $('#modal_foot_realisasi').text(grandReal.toLocaleString());
    }

    // Modal Event: Process Dropdown Change
    $(document).on('change', '.modal-select-proses', function() {
        var $tr = $(this).closest('tr');
        var $opt = $(this).find('option:selected');
        var pName = $opt.text().replace(/\s*\([^)]*\)$/, '').trim();
        var pKode = $opt.data('kode') || '';
        var pSatuan = $opt.data('satuan') || 'Pcs';
        var pTarget = parseFloat($opt.data('target')) || 0;

        $tr.find('.modal-inp-nama-proses').val(pName);
        $tr.find('.modal-inp-kode-proses').val(pKode);
        $tr.find('.modal-inp-satuan').val(pSatuan);
        if (pTarget > 0 && (!$tr.find('.modal-inp-target').val() || parseFloat($tr.find('.modal-inp-target').val()) === 0)) {
            $tr.find('.modal-inp-target').val(pTarget);
        }
        calcModalFooterTotals();
    });

    // Modal Event: Input Change in Hours/Target
    $(document).on('input change keyup', '.modal-hour-cell, .modal-inp-target, .modal-inp-target-lembur, .modal-inp-satuan', function() {
        calcModalFooterTotals();
    });

    // Modal Event: Delete Row
    $(document).on('click', '.btn-del-modal-row', function() {
        if ($('#tbody_modal_breakdown tr.modal-b-row').length > 1) {
            $(this).closest('tr').remove();
            calcModalFooterTotals();
        } else {
            alert('Minimal harus ada 1 baris proses.');
        }
    });

    // Modal Event: Add Row
    $('#btn_add_modal_row').on('click', function() {
        addModalBreakdownRow();
    });

    // Main Table Event: Click "Input Detail" Button
    $(document).on('click', '.btn-input-detail', function() {
        currentActiveRow = $(this).closest('tr');
        var empName = $(this).data('nama') || '';
        var empNip = $(this).data('nip') || '';
        var empPosisi = $(this).data('posisi') || '';

        $('#modal_emp_name').text(empName);
        $('#modal_emp_nip').text(empNip);
        $('#modal_emp_posisi').text(empPosisi);
        $('#tbody_modal_breakdown').empty();

        var jsonStr = currentActiveRow.find('.breakdown-json-input').val();
        var hasLoaded = false;
        if (jsonStr) {
            try {
                var bData = JSON.parse(jsonStr);
                if (Array.isArray(bData) && bData.length > 0) {
                    bData.forEach(function(item) {
                        addModalBreakdownRow(item);
                    });
                    hasLoaded = true;
                }
            } catch(e){}
        }

        if (!hasLoaded) {
            if (Array.isArray(masterProcesses) && masterProcesses.length > 0) {
                masterProcesses.forEach(function(p) {
                    addModalBreakdownRow({
                        id_proses: p.id,
                        nama_proses: p.nama,
                        kode: p.kode,
                        target_qty: parseFloat(p.target_harian) > 0 ? parseFloat(p.target_harian) : 0,
                        target_lembur: '',
                        satuan: p.satuan || 'Pcs',
                        jam_08_09: '',
                        jam_09_10: '',
                        jam_10_11: '',
                        jam_11_12: '',
                        jam_13_14: '',
                        jam_14_15: '',
                        jam_15_16: '',
                        jam_16_17: '',
                        jam_lembur: '',
                        total_realisasi: 0
                    });
                });
            } else {
                // Fallback 1 default row based on employee position
                var existingTarget = currentActiveRow.find('.target-input').val();
                var existingTargetLembur = currentActiveRow.find('.target-lembur-input').val();
                var existingSatuan = currentActiveRow.find('.satuan-input').val() || 'Pcs';
                var existingLemburReal = currentActiveRow.find('.lembur-cell-input').val();
                var initRow = {
                    nama_proses: empPosisi,
                    target_qty: existingTarget || '',
                    target_lembur: existingTargetLembur || '',
                    satuan: existingSatuan,
                    jam_08_09: '',
                    jam_09_10: '',
                    jam_10_11: '',
                    jam_11_12: '',
                    jam_13_14: '',
                    jam_14_15: '',
                    jam_15_16: '',
                    jam_16_17: '',
                    jam_lembur: existingLemburReal || ''
                };
                addModalBreakdownRow(initRow);
            }
        }

        $('#modal_input_detail').modal('show');
    });

    // Modal Event: Apply Breakdown Back to Outer Row
    $('#btn_apply_modal_breakdown').on('click', function() {
        if (!currentActiveRow) return;

        var breakdownData = [];
        var totTarget = 0;
        var totTargetLembur = 0;
        var hSlots = { '08': 0, '09': 0, '10': 0, '11': 0, '13': 0, '14': 0, '15': 0, '16': 0, 'lembur': 0 };
        var hFormat = { '08': [], '09': [], '10': [], '11': [], '13': [], '14': [], '15': [], '16': [], 'lembur': [] };
        var mainSatuan = 'Pcs';

        $('#tbody_modal_breakdown tr.modal-b-row').each(function() {
            var $r = $(this);
            var pId = $r.find('.modal-select-proses').val();
            var pName = $r.find('.modal-inp-nama-proses').val();
            var pKode = $r.find('.modal-inp-kode-proses').val() || (pName ? pName.substring(0, 3).toUpperCase() : '');
            var t = parseFloat($r.find('.modal-inp-target').val()) || 0;
            var tLembur = parseFloat($r.find('.modal-inp-target-lembur').val()) || 0;
            var sat = $r.find('.modal-inp-satuan').val() || 'Pcs';
            if (sat) mainSatuan = sat;

            var j08 = ($r.find('.modal-hour-cell[data-slot="08"]').val() || '').trim();
            var j09 = ($r.find('.modal-hour-cell[data-slot="09"]').val() || '').trim();
            var j10 = ($r.find('.modal-hour-cell[data-slot="10"]').val() || '').trim();
            var j11 = ($r.find('.modal-hour-cell[data-slot="11"]').val() || '').trim();
            var j13 = ($r.find('.modal-hour-cell[data-slot="13"]').val() || '').trim();
            var j14 = ($r.find('.modal-hour-cell[data-slot="14"]').val() || '').trim();
            var j15 = ($r.find('.modal-hour-cell[data-slot="15"]').val() || '').trim();
            var j16 = ($r.find('.modal-hour-cell[data-slot="16"]').val() || '').trim();
            var jLembur = ($r.find('.modal-hour-cell[data-slot="lembur"]').val() || '').trim();

            var r08 = parseSlotNumber(j08);
            var r09 = parseSlotNumber(j09);
            var r10 = parseSlotNumber(j10);
            var r11 = parseSlotNumber(j11);
            var r13 = parseSlotNumber(j13);
            var r14 = parseSlotNumber(j14);
            var r15 = parseSlotNumber(j15);
            var r16 = parseSlotNumber(j16);
            var rLembur = parseSlotNumber(jLembur);

            var rTot = r08 + r09 + r10 + r11 + r13 + r14 + r15 + r16 + rLembur;
            totTarget += t;
            totTargetLembur += tLembur;

            if (r08 > 0) { hSlots['08'] += r08; hFormat['08'].push((pKode ? pKode + '=' : '') + r08); }
            if (r09 > 0) { hSlots['09'] += r09; hFormat['09'].push((pKode ? pKode + '=' : '') + r09); }
            if (r10 > 0) { hSlots['10'] += r10; hFormat['10'].push((pKode ? pKode + '=' : '') + r10); }
            if (r11 > 0) { hSlots['11'] += r11; hFormat['11'].push((pKode ? pKode + '=' : '') + r11); }
            if (r13 > 0) { hSlots['13'] += r13; hFormat['13'].push((pKode ? pKode + '=' : '') + r13); }
            if (r14 > 0) { hSlots['14'] += r14; hFormat['14'].push((pKode ? pKode + '=' : '') + r14); }
            if (r15 > 0) { hSlots['15'] += r15; hFormat['15'].push((pKode ? pKode + '=' : '') + r15); }
            if (r16 > 0) { hSlots['16'] += r16; hFormat['16'].push((pKode ? pKode + '=' : '') + r16); }
            if (rLembur > 0) { hSlots['lembur'] += rLembur; hFormat['lembur'].push((pKode ? pKode + '=' : '') + rLembur); }

            breakdownData.push({
                id_proses: pId,
                nama_proses: pName,
                kode: pKode,
                target_qty: t,
                target_lembur: tLembur,
                satuan: sat,
                jam_08_09: j08,
                jam_09_10: j09,
                jam_10_11: j10,
                jam_11_12: j11,
                jam_13_14: j13,
                jam_14_15: j14,
                jam_15_16: j15,
                jam_16_17: j16,
                jam_lembur: jLembur,
                total_realisasi: rTot
            });
        });

        // Save JSON into hidden input
        currentActiveRow.find('.breakdown-json-input').val(JSON.stringify(breakdownData));

        // Update Outer Row Target and Satuan
        currentActiveRow.find('.target-input').val(totTarget > 0 ? totTarget : '');
        currentActiveRow.find('.target-lembur-input').val(totTargetLembur > 0 ? totTargetLembur : '');
        currentActiveRow.find('.satuan-input').val(mainSatuan);

        // Update Outer Row Hourly Slots
        var hourKeys = ['08', '09', '10', '11', '13', '14', '15', '16', 'lembur'];

        currentActiveRow.find('.matrix-cell-input').each(function(idx) {
            var k = hourKeys[idx];
            if (k && hSlots[k] > 0) {
                if (hFormat[k].length > 0) {
                    $(this).val(hFormat[k].join(', '));
                } else {
                    $(this).val(hSlots[k]);
                }
            } else {
                $(this).val('');
            }
        });

        // Update Button Badge Count
        var bCount = breakdownData.length;
        currentActiveRow.find('.badge-process-count').text(bCount > 0 ? bCount : '');

        // Recalculate everything
        recalculateAll();

        $('#modal_input_detail').modal('hide');
    });

    $(document).on('click', '.btn-del-matrix-row', function() {
        var $tr = $(this).closest('tr');
        var nama = $(this).data('nama') || 'pegawai ini';
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Pegawai?',
                text: 'Hapus baris ' + nama + ' dari form target harian ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $tr.remove();
                    renumberMatrixRows();
                    recalculateAll();
                }
            });
        } else {
            if (confirm('Hapus baris ' + nama + ' dari form target harian ini?')) {
                $tr.remove();
                renumberMatrixRows();
                recalculateAll();
            }
        }
    });

    function renumberMatrixRows() {
        $('.matrix-table tbody tr').each(function(idx) {
            $(this).find('.row-number').text(idx + 1);
        });
    }

    recalculateAll();

    $('#tanggal').on('change changeDate input', function() {
        var tgl = $(this).val();
        if ($('input[name="id"]').val() === '') {
            $.get('<?= base_url("sdm/target-harian/get-doc-code") ?>?tanggal=' + encodeURIComponent(tgl), function(res) {
                if (res && res.code) {
                    $('#no_transaksi_preview').val(res.code);
                }
            });
        }
    });

    $('#btn_save_all').click(function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        $.ajax({
            url: '<?= base_url("sdm/target-harian/save") ?>',
            type: 'POST',
            data: $('#form_matrix').serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data Matrix');
                if (res.status) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = '<?= base_url("sdm/target-harian") ?>';
                        });
                    } else {
                        alert(res.message);
                        window.location.href = '<?= base_url("sdm/target-harian") ?>';
                    }
                } else {
                    if (res.is_auth_error) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Sesi Login Berakhir!',
                                html: 'Sesi Anda telah kedaluwarsa karena terlalu lama di form ini.<br><br><b style="color:#d33;">PENTING: JANGAN REFRESH HALAMAN INI!</b><br>Data inputan matrix Anda masih ada di layar saat ini.<br><br>Silakan buka tab baru untuk login kembali, setelah berhasil login di tab baru, kembali ke tab ini dan klik <b>Simpan Data Matrix</b> lagi.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa fa-external-link me-1"></i> Buka Tab Login',
                                cancelButtonText: 'Tutup & Coba Nanti',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d'
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    window.open('<?= base_url("auth/login") ?>', '_blank');
                                }
                            });
                        } else {
                            alert(res.message);
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal!', res.message, 'error');
                        } else {
                            alert('Gagal: ' + res.message);
                        }
                    }
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Data Matrix');
                var errMsg = 'Terjadi kesalahan sistem.';
                try {
                    var errObj = JSON.parse(xhr.responseText);
                    if (errObj && errObj.message) errMsg = errObj.message;
                } catch(e) {
                    if (xhr.statusText) errMsg = xhr.statusText;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error!', errMsg, 'error');
                } else {
                    alert('Gagal: ' + errMsg);
                }
            }
        });
    });

    // Keep-alive heartbeat: ping server every 2 minutes (120 seconds) to keep PHP session alive
    setInterval(function() {
        $.getJSON('<?= base_url("sdm/target-harian/ping") ?>', function(res) {
            // Sesi aktif terus ter-refresh
        }).fail(function() {
            console.warn('[Keep-Alive] Ping server gagal.');
        });
    }, 120000);
});
</script>
<?= $this->endSection('script'); ?>