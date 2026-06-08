<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>

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
          <li class="breadcrumb-item active"><?= $titlehead ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">

      <?php if (isset($_SESSION['message'])) : ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i>
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
              <script>
                setTimeout(() => document.querySelector('.alert-success')?.remove(), 3000);
              </script>
            <?php endif; ?>

            <?php if (isset($_SESSION['err'])) : ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle me-1"></i>
                <strong>Warning!</strong> <?= $_SESSION['err'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
              <script>
                setTimeout(() => document.querySelector('.alert-danger')?.remove(), 5000);
              </script>
            <?php endif; ?>

        <div class="d-flex flex-wrap align-items-end gap-3">

          <div class="form-group mb-0">
            <label class="form-label fw-medium small text-muted" for="filter_tgl">Tanggal</label>
            <div class="input-group" style="min-width: 160px;">
              <span class="input-group-text"><i class="fa fa-calendar"></i></span>
              <input type="text" id="filter_tgl" name="filter_tgl"
                     class="form-control datepickerx"
                     placeholder="Pilih tanggal"
                     value="<?= $dnow; ?>">
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <button id="btn-filter" class="btn btn-secondary" type="button">
              <i class="fa fa-filter me-1"></i> Filter
            </button>
            <button id="btn-generate" class="btn btn-primary" type="button">
              <i class="fa fa-table me-1"></i> Generate
            </button>
            <button id="btn-save" class="btn btn-success" type="button">
              <i class="fa fa-save me-1"></i> Simpan
            </button>
          </div>

          <div class="form-group mb-0 ms-auto" style="flex: 1; max-width: 450px;">
            <label class="form-label fw-medium small text-muted" for="nmExcel">Import Excel</label>
            <div class="d-flex gap-2 align-items-center">
              <input type="file" name="nmExcel" id="nmExcel" class="form-control">
              <button id="btn-import" class="btn btn-success text-nowrap" type="button">
                <i class="fa fa-file-excel me-1"></i> Import
              </button>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 ms-auto">

            <div class="vr align-self-center mx-1"></div>

            <button id="btn-export-pdf" class="btn btn-danger" type="button"
                    data-bs-toggle="modal" data-bs-target="#modalExportPdf">
              <i class="fa fa-file-pdf me-1"></i> Export PDF
            </button>
          </div>

        </div>
        
      </div>
    </div>
  </div>
</div>

  <div class="modal fade" id="modalExportPdf" tabindex="-1" aria-labelledby="modalExportPdfLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="modalExportPdfLabel">
            <i class="fa fa-file-pdf me-2 text-danger"></i> Export PDF Absensi Karyawan
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <div class="modal-body">
          <!-- Type Selection -->
          <div class="mb-3">
            <label class="form-label fw-medium small text-muted" for="pdf_type">Tipe Export</label>
            <select id="pdf_type" name="pdf_type" class="form-select">
              <option value="karyawan" selected>Karyawan</option>
              <option value="rekap">Rekap Karyawan</option>
            </select>
          </div>

          <!-- Karyawan Selection -->
          <div class="mb-3">
            <label class="form-label fw-medium small text-muted" for="pdf_karyawan">Nama Karyawan</label>
            <select id="pdf_karyawan" name="pdf_karyawan" class="form-select select2" data-placeholder="-- Pilih Karyawan --">
              <option value="" disabled selected>— Pilih Karyawan —</option>
              <?php foreach ($karyawan as $key => $value): ?>
                <option value="<?= $value->id ?>"><?= $value->full_name ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-medium small text-muted" for="pdf_from">Dari Tanggal</label>
              <input type="text" id="pdf_from" name="pdf_from" class="form-control datepickerx" placeholder="dd/mm/yyyy">
            </div>
            <div class="col-6">
              <label class="form-label fw-medium small text-muted" for="pdf_to">Sampai Tanggal</label>
              <input type="text" id="pdf_to" name="pdf_to" class="form-control datepickerx" placeholder="dd/mm/yyyy">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" id="btn-download-pdf" class="btn btn-danger">
            <i class="fa fa-download me-1"></i> Download PDF
          </button>
        </div>

      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
        <div class="row">
            <div class="col-sm-4 offset-md-8">
              <div class="form-group">
                <div class="input-group mb-3">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" class="form-control p-s-0" placeholder="Pencarian" aria-label="penca" id="tb-search" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
              <div class="table-striped" id="dt-absensi"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>

<?= $this->section('script') ?>
<!-- <script>
  const btnGenerate = document.querySelector('#btn-generate');
  // const rowData = document.querySelector('table > tbody');

  if (rowData)

  btnGenerate.addEventListener('click', () => {
    iLoader.start()

    setTimeout(() => {
      rowData.classList.remove('d-none');
      iLoader.stop();
    }, 1000)
  })
</script> -->

<script src="script/app/sdm/absensi/index.js"></script>
<?= $this->endSection('script') ?>