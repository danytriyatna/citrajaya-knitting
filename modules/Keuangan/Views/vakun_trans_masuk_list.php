<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
          <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor"><?= $titlehead; ?></h4>
          </div>
          <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Keuangan</a></li>
                <li class="breadcrumb-item active"><?= $titlehead; ?></li>
              </ol>
            </div>
          </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
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
                <div class="form-group row">
                  <div class="col-sm-3">
                    <a href="<?= base_url(); ?>/keuangan/transaksi_masuk/add" class="btn btn-primary open_form" type="button"><i class="fa fa-plus"></i>
                      Tambah</a>
                    <button class="btn btn-success open_form" type="button" id="excel_download"><i class="fa fa-excel"></i>
                      Download</button>
                  </div>
                  <label class="control-label text-start text-md-end col-md-1 offset-sm-2">Bulan</label>
                  <div class="col-md-1 ">
                      <?= !empty($slc_bulan) ? form_dropdown($slc_bulan) : ""; ?>
                  </div>
                  <label class="control-label text-start text-md-end col-md-1">Tahun</label>
                  <div class="col-md-1 ">
                      <?= !empty($tahun_trans) ? form_dropdown($tahun_trans) : ""; ?>
                  </div>
                  <div class="col-sm-3">
                    <div class="input-group">
                      <input type="text" class="form-control" id="tb-search" placeholder="Pencarian . . .">
                      <div class="input-group-append"><span class="input-group-text h-100"><i class="ti-search"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <hr>

                <div class="row">
                  <div class="col-sm-12">
                    <div id="dt-list" class="table-responsive table-striped"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
      </div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/keuangan/trans_masuk/index.js"></script>
<?= $this->endSection('script'); ?>
