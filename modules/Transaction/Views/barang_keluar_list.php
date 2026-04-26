<?= $this->extend('template'); ?>

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
          <li class="breadcrumb-item">Transaksi</li>
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
            <div class="col-sm-2 mb-3">
              <a href="trans/outgoing-goods/form" type="button" class="btn btn-sm btn-success" id="btn-add"> <i class="fa fa-plus"></i> Tambah</a>
            </div>
            <div class="col-sm-2 mb-3">
              <input type="text" id="from_date" name="from_date" class="form-control datepickerx" placeholder="FROM DATE">
            </div>
            <div class="col-sm-2 mb-3">
              <input type="text" id="to_date" name="to_date" class="form-control datepickerx" placeholder="TO DATE">
            </div>
            <div class="col-sm-2 mb-3">
              <button id="btn_excel" class="btn btn-success open_form" type="button"><i class="fa fa-file-excel"></i> Export</button>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <div class="input-group mb-3">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" id="tb-search" class="form-control p-s-0" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
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
<script src="script/app/transaction/barang_keluar/index.js"></script>
<?= $this->endSection('script'); ?>