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
                  <div class="col-sm-2">
                    <label class="control-label text-start col-form-label">Periode</label>
                   <?= (isset($slc_tahun) && !empty($slc_tahun)) ? form_dropdown($slc_tahun) : ""; ?>
                  </div>
                  <div class="col-sm-2">
                    <label class="control-label text-start col-form-label" style="color: transparent;">From Date Export</label>
                    <br>
                    <button id="btn_cari" class="btn btn-primary open_form" type="button"><i class="fa fa-search"></i>
                      Cari</button>
                      <button id="btn_excel" class="btn btn-success open_form" type="button"><i class="fa fa-file-excel"></i>
                      Print</button>
                  </div>
                  <div class="col-sm-2">
                    <label class="control-label text-start col-form-label" for="select_type_export">Export Type</label>
                      <div class="col-md-9">
                        <select id="select_type_export" name="select_type_export" class="form-select select2" data-placeholder="-- Pilih Export Type --">
                          <option value=""> - Pilih Payment Type - </option>
                          <option value="0"> Beban Biaya </option>
                          <option value="1"> Mutasi Biaya </option>
                      </select>
                      </div>
                  </div>
                  <div class="col-sm-2 type-export-mutasi" hidden>
                    <div class="form-group m-b-0">
                      <label class="ccontrol-label text-start col-form-label" for="filter_penguji">Bulan</label>
                      <select id="filter_bulan" name="filter_bulan" class="form-select select2" data-placeholder="-- Pilih Bulan --">
                        <option value="">Semua Bulan</option>
                        <?php foreach ($bulan as $item) : ?>
                          <option value="<?= $item->id ?>"><?= $item->bulan ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-2 type-export-mutasi" hidden>
                    <div class="form-group m-b-0">
                      <label class="ccontrol-label text-start col-form-label" for="filter_penguji">Tahun</label>
                      <select id="filter_tahun" name="filter_tahun" class="form-select select2" data-placeholder="-- Pilih Tahun --">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($tahun as $item) : ?>
                          <option value="<?= $item->tahun ?>"><?= $item->tahun ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-2 type-export-mutasi" hidden>
                    <label class="control-label text-start col-form-label" for="select_payment_type_one">Payment Type Export</label>
                      <div class="col-md-9">
                        <select id="select_payment_type_one" name="select_payment_type_one" class="form-select select2" data-placeholder="-- Pilih Payment Type --">
                          <option value=""> - Pilih Payment Type - </option>
                          <?php foreach ($coa_list as $item) { ?>
                            <option value="<?= $item->coa_id; ?>"><?= $item->kode; ?> - <?= $item->nama; ?></option> 
                          <?php } ?>
                      </select>
                      </div>
                  </div>
                  <div class="col-sm-2 type-export-beban" hidden>
                    <label class="control-label text-start col-form-label" for="from_date">From Date Export</label>
                    <input type="text" id="from_date" name="from_date" class="form-control datepickerx" placeholder="Pilih tanggal">
                  </div>
                  <div class="col-sm-2 type-export-beban" hidden>
                    <label class="control-label text-start col-form-label" for="to_date">To Date Export</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepickerx" placeholder="Pilih tanggal">
                  </div>
                  <div class="col-sm-2 type-export-beban" hidden>
                    <label class="control-label text-start col-form-label" for="select_payment_type">Payment Type Export</label>
                      <div class="col-md-9">
                        <select id="select_payment_type" name="select_payment_type" class="form-select select2" data-placeholder="-- Pilih Payment Type --">
                          <option value=""> - Pilih Payment Type - </option>
                          <option value="all"> Semua </option>
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
