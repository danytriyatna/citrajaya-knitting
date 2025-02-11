<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-add-po" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Master Rekening</h5>
        <input type="hidden" id="data_id">
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-secondary p-y-8 text-muted">
          <i>*) Wajib diisi</i>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="rekening_no">No Rekening<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="rekening_no" name="rekening_no" class="form-control" placeholder="Ketik No Rekening" required>
            <div class="invalid-feedback">
              No Rekening tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="rekening_bank">Nama Rekening<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="rekening_bank" name="rekening_bank" class="form-control" placeholder="Ketik Nama Rekening" required>
            <div class="invalid-feedback">
              Nama Rekening tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="rekening_an">Atasnama<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="rekening_an" name="rekening_an" class="form-control" placeholder="Ketik Atasnama" required>
            <div class="invalid-feedback">
              Atasnama tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="keterangan">Keterangan</label>
          <div class="col-md-9">
            <textarea name="keterangan" id="keterangan" class="form-control" rows="5"></textarea>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button id="btn-save" type="button" class="m-s-5 btn btn-success"> <i class="fa fa-save"></i> Simpan</button>
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
          <li class="breadcrumb-item">Master Data</li>
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
            <div class="col-md-12 mb-3">
              <?php if (isset($_SESSION['message'])) { ?>
                <script type="text/javascript">
                  window.setTimeout(function() {
                    $(".alert").alert('close');
                  }, 3000);
                </script>
                <div class="alert alert-success">
                  <?php echo $_SESSION['message']; ?>
                </div>
              <?php } ?>
              <?php if (isset($_SESSION['err'])) { ?>
                <script type="text/javascript">
                  window.setTimeout(function() {
                    $(".alert").alert('close');
                  }, 5000);
                </script>
                <div class="alert alert-error">
                  <strong>Warning! </strong><?php echo $_SESSION['err']; ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-sm-3">
              <button type="button" class="btn btn-sm btn-success" id="btn-add"> <i class="fa fa-plus"></i> Tambah</button>
            </div>
            <div class="col-sm-4 offset-md-5">
              <div class="form-group">
                <div class="input-group mb-3">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" class="form-control p-s-0" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12">
              <div id="dt-list" class="table-responsive table-striped"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/referensi/rekening/index.js"></script>
<?= $this->endSection('script'); ?>