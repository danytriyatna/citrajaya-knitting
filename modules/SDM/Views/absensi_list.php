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
          <div class="row">
            <div class="col-sm-2">
              <div class="form-group row mb-0">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="filter_tgl">Tanggal</label>
                  <input type="text" id="filter_tgl" name="filter_tgl" class="form-control datepickerx" placeholder="Pilih tanggal" value="<?= $dnow; ?>">
                </div>
              </div>
            </div>
            <div class="col-sm-5 align-self-end">
              <button id="btn-filter" class="btn btn-secondary" type="button"><i class="fa fa-filter"></i>&nbsp; Filter</button>
              <button id="btn-generate" class="btn btn-primary" type="button"><i class="fa fa-table"></i>&nbsp; Generate</button>
              <button id="btn-save" class="btn btn-success" type="button"><i class="fa fa-save"></i>&nbsp; Simpan</button>
            </div>
            <div class="col-sm-2">
              <div class="form-group row mb-0">
                <div class="col-md-12">
                  <label for="">Import Excel</label>
                  <input type="file" name="nmExcel" id="nmExcel" class="form-control">
                </div>
              </div>
            </div>
            <div class="col-sm-3 align-self-end">
              <button id="btn-import" class="btn btn-success" type="button">&nbsp;<i class="fa fa-file-excel"></i> Import</button>
            </div>
          </div>
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