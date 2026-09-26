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
                <div class="row align-items-end g-2">
                  <input type="hidden" id="slc_tahun" value="<?= date('Y') ?>">
                  
                  <div class="col-sm-3 col-md-2">
                    <label class="control-label text-start col-form-label py-0 mb-1" for="filter_perusahaan">Perusahaan</label>
                    <select id="filter_perusahaan" name="filter_perusahaan" class="form-select select2" data-placeholder="-- Semua Perusahaan --">
                      <?php if ($superadmin == true) : ?>
                        <option value="all" selected>Semua Perusahaan</option>
                      <?php endif; ?>
                      <?php foreach ($perusahaan as $item) : ?>
                        <option <?= $user_perusahaan == $item->id ? 'selected' : '' ?> value="<?= $item->id ?>">
                          <?= $item->nama_perusahaan ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-sm-2 col-md-2">
                    <label class="control-label text-start col-form-label py-0 mb-1" for="select_type_export">Export Type</label>
                    <select id="select_type_export" name="select_type_export" class="form-select select2" data-placeholder="-- Pilih Export Type --">
                      <option value=""> - Matriks Tahunan - </option>
                      <option value="0"> Beban Biaya </option>
                      <option value="1"> Mutasi Biaya </option>
                    </select>
                  </div>

                  <!-- Dynamic Filter: Mutasi Biaya -->
                  <div class="col-sm-2 col-md-2 type-export-mutasi" hidden>
                    <label class="control-label text-start col-form-label py-0 mb-1" for="filter_bulan">Bulan</label>
                    <select id="filter_bulan" name="filter_bulan" class="form-select select2" data-placeholder="-- Pilih Bulan --">
                      <?php foreach ($bulan as $item) : ?>
                        <option value="<?= $item->id ?>" <?= $item->id == $curr_month ? 'selected' : '' ?>><?= $item->bulan ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-sm-2 col-md-1 type-export-mutasi" hidden>
                    <label class="control-label text-start col-form-label py-0 mb-1" for="filter_tahun">Tahun</label>
                    <select id="filter_tahun" name="filter_tahun" class="form-select select2" data-placeholder="-- Pilih Tahun --">
                      <?php foreach ($tahun as $item) : ?>
                        <option value="<?= $item->tahun ?>" <?= $item->tahun == $curr_year ? 'selected' : '' ?>><?= $item->tahun ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-sm-3 col-md-2 type-export-mutasi" hidden>
                    <label class="control-label text-start col-form-label py-0 mb-1" for="select_payment_type_one">Kas/Bank (COA)</label>
                    <select id="select_payment_type_one" name="select_payment_type_one" class="form-select select2" data-placeholder="-- Pilih Kas/Bank --">
                      <option value=""> - Pilih Kas/Bank - </option>
                      <?php foreach ($coa_list as $item) { ?>
                        <option value="<?= $item->coa_id; ?>"><?= $item->kode; ?> - <?= $item->nama; ?></option> 
                      <?php } ?>
                    </select>
                  </div>

                  <!-- Dynamic Filter: Beban Biaya -->
                  <div class="col-sm-2 col-md-2 type-export-beban" hidden>
                    <label class="control-label text-start col-form-label py-0 mb-1" for="from_date">From Date</label>
                    <input type="text" id="from_date" name="from_date" value="<?= $curr_from_date ?>" class="form-control datepickerx" placeholder="Pilih tanggal">
                  </div>
                  <div class="col-sm-2 col-md-2 type-export-beban" hidden>
                    <label class="control-label text-start col-form-label py-0 mb-1" for="to_date">To Date</label>
                    <input type="text" id="to_date" name="to_date" value="<?= $curr_to_date ?>" class="form-control datepickerx" placeholder="Pilih tanggal">
                  </div>
                  <div class="col-sm-3 col-md-2 type-export-beban" hidden>
                    <label class="control-label text-start col-form-label py-0 mb-1" for="select_payment_type">Payment Type</label>
                    <select id="select_payment_type" name="select_payment_type" class="form-select select2" data-placeholder="-- Semua --">
                      <option value="all" selected> Semua </option>
                      <?php foreach ($rekening_list as $item) { ?>
                        <option value="<?= $item->id; ?>"><?= $item->rekening_no; ?> - <?= $item->rekening_bank; ?></option> 
                      <?php } ?>
                    </select>
                  </div>

                  <!-- Action Buttons on the Right -->
                  <div class="col-auto ms-auto d-flex align-items-end mb-1">
                    <button id="btn_cari" class="btn btn-primary me-2" type="button"><i class="fa fa-search"></i> Cari</button>
                    <button id="btn_excel" class="btn btn-success" type="button"><i class="fa fa-file-excel"></i> Print</button>
                  </div>
                </div>

                <hr>

                <div class="row mb-2">
                  <div class="col-md-4 col-lg-3 ms-auto">
                    <div class="input-group">
                      <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                      <input type="text" class="form-control" id="tb-search" placeholder="Cari kode atau nama akun...">
                    </div>
                  </div>
                </div>

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
<script src="script/app/keuangan/laporan_mutasi/index.js"></script>
<?= $this->endSection('script'); ?>