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
                <div class="form-group row">
                  <label class="col-sm-1">Periode</label>
                  <div class="col-sm-2">
                   <?= (isset($slc_tahun) && !empty($slc_tahun)) ? form_dropdown($slc_tahun) : ""; ?>
                  </div>
                  <div class="col-sm-2">
                    <button id="btn_cari" class="btn btn-primary open_form" type="button"><i class="fa fa-search"></i>
                      Cari</button>
                      <button id="btn_excel" class="btn btn-success open_form" type="button"><i class="fa fa-file-excel"></i>
                      Print</button>
                  </div>
                  <div class="col-sm-2">
                    <label class="control-label text-start col-form-label" for="from_date">From Date Export</label>
                    <input type="text" id="from_date" name="from_date" class="form-control datepickerx" placeholder="Pilih tanggal">
                  </div>
                  <div class="col-sm-2">
                    <label class="control-label text-start col-form-label" for="to_date">To Date Export</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepickerx" placeholder="Pilih tanggal">
                  </div>
                  <div class="col-sm-2">
                    <label class="control-label text-start col-form-label" for="select_payment_type">Payment Type Export</label>
                      <div class="col-md-9">
                        <select id="select_payment_type" name="select_payment_type" class="form-select select2" data-placeholder="-- Pilih Payment Type --">
                          <option value=""> - Pilih Payment Type - </option>
                          <?php foreach ($rekening_list as $item) { ?>
                            <option value="<?= $item->id; ?>"><?= $item->rekening_no; ?> - <?= $item->rekening_bank; ?></option> 
                          <?php } ?>
                      </select>
                      </div>
                  </div>
                  <div hidden class="col-sm-3 offset-sm-6">
                    <div class="input-group">
                      <input type="text" class="form-control" id="tb-search" placeholder="Pencarian . . .">
                      <div class="input-group-append"><span class="input-group-text h-100"><i class="ti-search"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
                <hr>
                <div class="table-responsive">
                  <table id="dt-list" class="table-striped">
                  </table>
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
<script src="/script/app/keuangan/laporan_mutasi/index.js"></script>
<?= $this->endSection('script'); ?>
