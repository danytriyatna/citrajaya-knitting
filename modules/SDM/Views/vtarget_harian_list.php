<?= $this->extend('template'); ?>

<?= $this->section('content'); ?>
<style>
.table-responsive-custom {
    overflow: visible !important;
}
.card-body-custom {
    min-height: 420px;
}
.table-cjk-gold thead th {
    background-color: #f1b500 !important;
    color: #000 !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    font-size: 12px;
    vertical-align: middle !important;
    border-color: #d9a300 !important;
}
.btn-action-dropdown {
    position: relative;
}
.btn-action-dropdown .dropdown-toggle {
    font-size: 12px;
    padding: 3px 8px;
}
.btn-action-dropdown .dropdown-menu {
    position: absolute !important;
    z-index: 1060 !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
    min-width: 140px;
}
</style>

<div class="container-fluid">

  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h4 class="text-themecolor"><?= $titlehead ?></h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
      <div class="d-flex justify-content-end align-items-center">
        <a href="<?= base_url('sdm/target-harian/panduan') ?>" target="_blank" class="btn btn-sm btn-info text-white shadow-sm me-3">
          <i class="fa fa-book me-1"></i> Panduan Penggunaan
        </a>
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><?= $name_app ?></li>
          <li class="breadcrumb-item">Persediaan & Produksi</li>
          <li class="breadcrumb-item active"><?= $titlehead ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow-sm">
        <div class="card-body card-body-custom">

          <!-- Filter & Action Header matching standard CJK -->
          <div class="row align-items-center g-2 mb-3">
            <div class="col-sm-auto">
              <a href="/sdm/target-harian/form" class="btn btn-sm btn-success shadow-sm">
                <i class="fa fa-plus me-1"></i> Tambah
              </a>
            </div>

            <div class="col-sm-auto d-flex align-items-center">
              <input type="text" id="filter_from" class="form-control form-control-sm datepicker bg-light" value="<?= date('01/m/Y') ?>" placeholder="FROM DATE" style="width: 130px;">
              <span class="mx-1 text-muted">s/d</span>
              <input type="text" id="filter_to" class="form-control form-control-sm datepicker bg-light" value="<?= date('t/m/Y') ?>" placeholder="TO DATE" style="width: 130px;">
            </div>

            <div class="col-sm-auto">
              <select id="select_tipe_laporan" class="form-select form-control form-control-sm bg-light font-weight-bold" style="min-width: 220px; font-weight: 600;">
                <option value="rekap_tim">📊 Rekap Tim (Pegawai × Proses)</option>
                <option value="rapor_karyawan">👤 Rapor Karyawan (Tanggal × Proses)</option>
              </select>
            </div>

            <div class="col-sm-auto">
              <button type="button" id="btn_print_laporan" class="btn btn-sm btn-info text-white shadow-sm font-weight-bold">
                <i class="fa fa-print me-1"></i> Print
              </button>
            </div>

            <div class="col-sm-auto">
              <button type="button" id="btn_filter" class="btn btn-sm btn-success shadow-sm">
                <i class="fa fa-filter me-1"></i> Filter
              </button>
            </div>

            <div class="col-sm-4">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="tb_search" class="form-control border-start-0 ps-0" placeholder="Pencarian...">
              </div>
            </div>
          </div>

          <!-- Data Table -->
          <div class="table-responsive-custom">
            <table class="table table-hover table-bordered mb-0 table-cjk-gold" id="table_target">
              <thead>
                <tr>
                  <th width="8%" class="text-center">Aksi</th>
                  <th width="16%">NO. TRANSAKSI</th>
                  <th width="12%" class="text-center">TANGGAL</th>
                  <th width="12%" class="text-center">TOTAL PEGAWAI</th>
                  <th width="14%" class="text-right">TOTAL TARGET</th>
                  <th width="14%" class="text-right">TOTAL REALISASI</th>
                  <th width="12%" class="text-center">% CAPAIAN</th>
                  <th width="12%" class="text-center">STATUS</th>
                </tr>
              </thead>
              <tbody>
                <!-- Loaded via AJAX -->
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

<!-- Modal Pilih Karyawan untuk Rapor Karyawan -->
<div class="modal fade" id="modal_print_rapor_karyawan" tabindex="-1" role="dialog" aria-labelledby="modalRaporTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark py-2">
        <h5 class="modal-title font-weight-bold mb-0" id="modalRaporTitle">
          <i class="fa fa-print me-1"></i> Cetak Rapor Karyawan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-3">
        <p class="text-muted small mb-3">
          Silakan pilih karyawan yang ingin dicetak rapor capaian hariannya untuk periode yang dipilih di filter:
        </p>
        <div class="form-group mb-2">
          <label class="form-label font-weight-bold small">Pilih Karyawan:</label>
          <select id="modal_select_karyawan" class="form-select form-control">
            <option value="all">🌟 Semua Karyawan (Batch Print / Semua Halaman)</option>
            <?php if (!empty($employees)): ?>
              <?php foreach ($employees as $emp): ?>
                <option value="<?= $emp['id'] ?>"><?= esc($emp['nama']) ?> (<?= esc($emp['nik'] ?? '-') ?>)</option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <div class="alert alert-light border small text-muted mb-0 mt-3">
          <i class="fa fa-info-circle text-info me-1"></i>
          Pilihan <strong>Semua Karyawan</strong> akan mencetak rapor per karyawan secara urut dengan pemisah halaman (page break) otomatis untuk tiap lembar.
        </div>
      </div>
      <div class="modal-footer py-2 bg-light">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
        <button type="button" id="btn_confirm_print_rapor" class="btn btn-sm btn-warning text-dark font-weight-bold">
          <i class="fa fa-print me-1"></i> Buka Cetak Rapor
        </button>
      </div>
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

    var allData = [];

    function renderTable(data) {
        var html = '';
        var searchVal = ($('#tb_search').val() || '').toLowerCase();

        var filtered = data.filter(function(row) {
            if (!searchVal) return true;
            return (row.kode_transaksi && row.kode_transaksi.toLowerCase().indexOf(searchVal) !== -1) ||
                   (row.tanggal_fmt && row.tanggal_fmt.indexOf(searchVal) !== -1);
        });

        if (filtered && filtered.length > 0) {
            $.each(filtered, function(i, row) {
                var badgeStatus = (row.status == 1) 
                    ? '<span class="badge bg-success text-white px-2 py-1">Final</span>'
                    : '<span class="badge bg-secondary text-white px-2 py-1">Draft</span>';

                var pct = parseFloat(row.avg_persentase) || 0;
                var pctBadge = 'bg-danger text-white';
                if (pct >= 100) pctBadge = 'bg-success text-white';
                else if (pct >= 80) pctBadge = 'bg-warning text-dark';

                // Standard CJK Action dropdown
                var btnAksiHtml = row.btn_action;
                if (!btnAksiHtml || btnAksiHtml === '') {
                    var btnDeleteHtml = '';
                    if (row.status == 0) {
                        btnDeleteHtml = '<li><hr class="dropdown-divider my-1"></li>' +
                            '<li><a class="dropdown-item py-1 text-danger btn-delete" href="javascript:void(0)" data-id="' + row.id_encrypted + '"><i class="fa fa-trash me-2"></i> Hapus</a></li>';
                    }

                    btnAksiHtml = '<div class="dropdown btn-action-dropdown">' +
                        '<button type="button" class="btn btn-xs btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                        '<i class="fa fa-cog me-1"></i> Aksi' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-start shadow">' +
                        '<li><a class="dropdown-item py-1" href="<?= base_url("sdm/target-harian/form") ?>/' + row.id_encrypted + '"><i class="fa fa-edit text-primary me-2"></i> Edit</a></li>' +
                        '<li><a class="dropdown-item py-1" href="<?= base_url("sdm/target-harian/print") ?>/' + row.id_encrypted + '" target="_blank"><i class="fa fa-print text-info me-2"></i> Print</a></li>' +
                        btnDeleteHtml +
                        '</ul>' +
                        '</div>';
                }

                html += '<tr>';
                html += '<td class="text-center">' + btnAksiHtml + '</td>';
                html += '<td class="font-weight-bold"><a href="<?= base_url("sdm/target-harian/form") ?>/' + row.id_encrypted + '" class="text-primary font-weight-bold">' + row.kode_transaksi + '</a></td>';
                html += '<td class="text-center">' + row.tanggal_fmt + '</td>';
                html += '<td class="text-center font-weight-bold">' + row.total_pegawai + ' org</td>';
                html += '<td class="text-right font-weight-bold">' + parseFloat(row.total_target).toLocaleString() + '</td>';
                html += '<td class="text-right font-weight-bold text-success">' + parseFloat(row.total_realisasi).toLocaleString() + '</td>';
                html += '<td class="text-center"><span class="badge ' + pctBadge + ' px-2 py-1">' + pct + '%</span></td>';
                html += '<td class="text-center">' + badgeStatus + '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa fa-info-circle me-1"></i> Belum ada data target harian untuk periode ini.</td></tr>';
        }
        $('#table_target tbody').html(html);
    }

    function loadData() {
        var from = $('#filter_from').val();
        var to = $('#filter_to').val();

        $('#table_target tbody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>');

        $.ajax({
            url: '<?= base_url("sdm/target-harian/list") ?>',
            type: 'POST',
            data: {
                from_date: from,
                to_date: to,
                start: 0,
                length: 100
            },
            dataType: 'json',
            success: function(res) {
                allData = res.data || [];
                renderTable(allData);
            }
        });
    }

    loadData();
    $('#btn_filter').click(function() { loadData(); });
    $('#tb_search').on('keyup input', function() { renderTable(allData); });

    // Print Laporan Handlers
    $('#btn_print_laporan').click(function() {
        var tipe = $('#select_tipe_laporan').val();
        var from = $('#filter_from').val();
        var to = $('#filter_to').val();

        if (tipe === 'rekap_tim') {
            var url = '<?= base_url("sdm/target-harian/print-rekap-tim") ?>?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
            window.open(url, '_blank');
        } else if (tipe === 'rapor_karyawan') {
            $('#modal_print_rapor_karyawan').modal('show');
        }
    });

    $('#btn_confirm_print_rapor').click(function() {
        var from = $('#filter_from').val();
        var to = $('#filter_to').val();
        var idKaryawan = $('#modal_select_karyawan').val();

        var url = '<?= base_url("sdm/target-harian/print-rapor-karyawan") ?>?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) + '&id_karyawan=' + encodeURIComponent(idKaryawan);
        window.open(url, '_blank');
        $('#modal_print_rapor_karyawan').modal('hide');
    });

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (confirm('Yakin ingin menghapus dokumen target ini?')) {
            $.post('<?= base_url("sdm/target-harian/delete") ?>/' + id, function(res) {
                if (res.status) {
                    alert(res.message);
                    loadData();
                } else {
                    alert(res.message);
                }
            }, 'json');
        }
    });
});
</script>
<?= $this->endSection('script'); ?>
