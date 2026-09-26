<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<!-- Modal Pop-up Pilih Karyawan Multi-Select Checklist -->
<div class="modal fade" id="modal-karyawan" tabindex="-1" aria-labelledby="modalKaryawanLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title font-weight-bold" id="modalKaryawanLabel">
          <i class="fa fa-users text-primary me-2"></i>Pilih Karyawan Lembur
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row align-items-center g-2 mb-3">
          <div class="col-sm-5">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white"><i class="ti-search"></i></span>
              <input type="text" class="form-control" id="search-modal-karyawan" placeholder="Cari NIP / Nama / Posisi...">
            </div>
          </div>
          <div class="col-sm-4">
            <select id="modal_filter_perusahaan" class="form-select form-select-sm">
              <option value="all" selected>Semua Perusahaan</option>
              <?php foreach ($perusahaan as $item) : ?>
                <option value="<?= $item->id ?>"><?= $item->nama_perusahaan ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-3 text-end">
            <span class="badge bg-primary fs-6" id="badge-selected-count">0 Terpilih</span>
          </div>
        </div>

        <div id="dt-pilih-karyawan" class="table-responsive table-striped"></div>
      </div>
      <div class="modal-footer bg-light d-flex justify-content-between">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Batal
        </button>
        <button type="button" class="btn btn-sm btn-primary font-weight-bold" id="btn-insert-selected-karyawan">
          <i class="fa fa-check-circle me-1"></i> Masukkan Karyawan Terpilih
        </button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection('modal') ?>

<?= $this->section('content'); ?>

<div class="container-fluid">

  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h4 class="text-themecolor"><?= $titlehead ?></h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
      <div class="d-flex justify-content-end align-items-center">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><?= $name_app ?></li>
          <li class="breadcrumb-item"><a href="/sdm/lembur">Lembur Karyawan</a></li>
          <li class="breadcrumb-item active"><?= !empty($resData->id) ? 'Edit' : 'Tambah' ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">

          <!-- Form Header -->
          <form id="form-lembur">
            <input type="hidden" id="id_lembur" name="id" value="<?= !empty($resData->id) ? encrypt($resData->id) : '' ?>">
            <input type="hidden" id="status_lembur_doc" value="<?= (int) $resData->status ?>">

            <div class="row g-3 mb-4 p-3 bg-light rounded border">
              
              <!-- Kode Lembur -->
              <div class="col-md-3">
                <label class="form-label font-weight-bold text-muted small mb-1">NO. LEMBUR</label>
                <input type="text" class="form-control form-control-sm bg-white font-weight-bold" id="kode_lembur" name="kode_lembur" value="<?= $resData->kode_lembur ?>" readonly>
              </div>

              <!-- Tanggal Lembur -->
              <div class="col-md-3">
                <label class="form-label font-weight-bold text-muted small mb-1">TANGGAL LEMBUR <span class="text-danger">*</span></label>
                <input type="date" class="form-control form-control-sm <?= (int)$resData->status == 2 ? 'bg-white' : '' ?>" id="tanggal" name="tanggal" value="<?= $resData->tanggal ?>" <?= (int)$resData->status == 2 ? 'readonly' : '' ?> required>
              </div>

              <!-- Perusahaan -->
              <div class="col-md-3">
                <label class="form-label font-weight-bold text-muted small mb-1">PERUSAHAAN</label>
                <select id="id_perusahaan" name="id_perusahaan" class="form-select form-select-sm" <?= (int)$resData->status == 2 ? 'disabled' : '' ?>>
                  <?php foreach ($perusahaan as $item) : ?>
                    <option value="<?= $item->id ?>" <?= $resData->id_perusahaan == $item->id ? 'selected' : '' ?>>
                      <?= $item->nama_perusahaan ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Status Dokumen -->
              <div class="col-md-3 text-md-end">
                <label class="form-label font-weight-bold text-muted small mb-1 d-block">STATUS DOKUMEN</label>
                <?php if ($resData->status == 0) : ?>
                  <span class="badge bg-secondary fs-6 px-3 py-2">0 - Draft</span>
                <?php elseif ($resData->status == 1) : ?>
                  <span class="badge bg-info text-white fs-6 px-3 py-2">1 - Disetujui Penugasan</span>
                <?php elseif ($resData->status == 2) : ?>
                  <span class="badge bg-success fs-6 px-3 py-2">2 - Disetujui Jam Lembur</span>
                <?php else : ?>
                  <span class="badge bg-danger fs-6 px-3 py-2">Dibatalkan</span>
                <?php endif; ?>
              </div>

              <!-- Keterangan -->
              <div class="col-md-12">
                <label class="form-label font-weight-bold text-muted small mb-1">KETERANGAN / KEGIATAN LEMBUR</label>
                <input type="text" class="form-control form-control-sm" id="keterangan" name="keterangan" value="<?= htmlspecialchars($resData->keterangan ?? '') ?>" placeholder="Misal: Lembur Kejar Target SO-2608-001..." <?= (int)$resData->status == 2 ? 'readonly' : '' ?>>
              </div>

            </div>
          </form>

          <!-- Toolbar Tabel Detail -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex gap-2">
              <?php if ((int)$resData->status < 2) : ?>
                <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-karyawan">
                  <i class="fa fa-user-plus me-1"></i> + Tambah Karyawan
                </button>
                <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold" id="btn-tarik-target-harian" title="Tarik seluruh karyawan yang memiliki jam lembur di Target Harian pada tanggal ini">
                  <i class="fa fa-bullseye me-1"></i> 🎯 Tarik dari Target Harian
                </button>
                <button type="button" class="btn btn-sm btn-info text-white" id="btn-sync-absensi" title="Sinkronkan data jam absensi dan target/realisasi lembur dari Target Harian">
                  <i class="fa fa-sync me-1"></i> 🔄 Tarik / Sinkronisasi Absensi & Target
                </button>
              <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-3">
              <span class="badge bg-light text-dark border p-2">
                Total Karyawan: <b id="lbl-total-karyawan">0</b> Orang
              </span>
              <span class="badge bg-light-primary text-primary border border-primary p-2">
                Total Jam Dihitung: <b id="lbl-total-jam-dihitung">0.00</b> Jam
              </span>
            </div>
          </div>

          <!-- Tabulator Detail Lembur -->
          <div class="row">
            <div class="col-sm-12">
              <div id="dt-detail-lembur" class="table-responsive table-striped border rounded mb-3"></div>
            </div>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div>
              <a href="/sdm/lembur" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar
              </a>
            </div>

            <div class="d-flex gap-2">
              <?php if ((int)$resData->status < 2) : ?>
                <button type="button" class="btn btn-sm btn-success" id="btn-save-draft">
                  <i class="fa fa-save me-1"></i> Simpan Data
                </button>
              <?php endif; ?>

              <?php if (!empty($resData->id) && (int)$resData->status >= 1) : ?>
                <a href="/sdm/lembur/print/<?= encrypt($resData->id) ?>/spk" target="_blank" class="btn btn-sm btn-outline-primary">
                  <i class="fa fa-print me-1"></i> 🖨️ Cetak SPK Penugasan
                </a>
              <?php endif; ?>

              <?php if (!empty($resData->id) && (int)$resData->status == 0) : ?>
                <button type="button" class="btn btn-sm btn-info text-white font-weight-bold" id="btn-approve-1">
                  <i class="fa fa-check me-1"></i> 🔵 Approve 1 (Persetujuan Penugasan Karyawan)
                </button>
              <?php endif; ?>

              <?php if (!empty($resData->id) && (int)$resData->status == 1) : ?>
                <button type="button" class="btn btn-sm btn-success font-weight-bold" id="btn-approve-2">
                  <i class="fa fa-check-double me-1"></i> 🟢 Approve 2 (Persetujuan Jam Lembur & Sync)
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" id="btn-cancel-approve">
                  <i class="fa fa-undo me-1"></i> Batal Approve 1
                </button>
              <?php endif; ?>

              <?php if (!empty($resData->id) && (int)$resData->status == 2) : ?>
                <a href="/sdm/lembur/print/<?= encrypt($resData->id) ?>/rekap" target="_blank" class="btn btn-sm btn-success text-white font-weight-bold">
                  <i class="fa fa-print me-1"></i> 🖨️ Cetak Rekap Realisasi (Approve 2)
                </a>
                <button type="button" class="btn btn-sm btn-outline-warning" id="btn-cancel-approve">
                  <i class="fa fa-undo me-1"></i> Batal Approve 2
                </button>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<!-- Data awal JSON detail dari controller -->
<script>
  const initialDetailData = <?= !empty($detail) ? $detail : '[]' ?>;
  const docStatus = <?= (int) $resData->status ?>;
</script>

<script src="/script/app/sdm/lembur/form.js"></script>
<?= $this->endSection('script'); ?>
