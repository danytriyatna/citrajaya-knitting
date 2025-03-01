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
                  <label class="control-label text-start col-form-label" for="filter_tgl_awal">Tanggal Awal</label>
                  <input type="text" id="filter_tgl_awal" name="filter_tgl_awal" class="form-control datepickerx" placeholder="Pilih tanggal" value="<?= $dnow; ?>">
                </div>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group row mb-0">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="filter_tgl_akhir">Tanggal Akhir</label>
                  <input type="text" id="filter_tgl_akhir" name="filter_tgl_akhir" class="form-control datepickerx" placeholder="Pilih tanggal" value="<?= $dnow; ?>">
                </div>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group m-b-0 d-flex align-items-center">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="filter_proses">Proses</label>
                  <select id="filter_proses" name="filter_proses" class="form-control custom-select select2">
                    <option value="0">- Semua -</option>
                    <?php foreach ($proses as $rowData) : ?>
                      <option value="<?= $rowData->id ?>"><?= $rowData->nama ?></option>
                    <?php endforeach ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group m-b-0 d-flex align-items-center">
                <div class="col-md-12">
                  <label class="control-label text-start text-md-end m-e-8" for="filter_operator">CMT</label>
                  <select id="filter_operator" name="filter_operator" class="form-control custom-select select2">
                    <option value="">-</option>
                    <?php foreach ($operator as $rowData) : ?>
                      <option value="<?= $rowData->id ?>"><?= $rowData->nama_operator ?></option>
                    <?php endforeach ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-2 align-self-end">
              <button id="btn-filter" class="btn btn-secondary" type="button"><i class="fa fa-filter"></i>&nbsp; Filter</button>
              <button id="btn-generate" class="btn btn-primary" type="button"><i class="fa fa-table"></i>&nbsp; Generate</button>
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

<script src="script/app/sdm/borongan/index.js"></script>
<?= $this->endSection('script') ?>