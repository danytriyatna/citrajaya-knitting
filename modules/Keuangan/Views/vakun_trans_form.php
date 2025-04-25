<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>
<?php echo isset($disabled_input) ? "<script>let disabled_input =". json_encode($disabled_input) ."</script>" : ""; ?>
  
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
              <form action="<?= base_url().'/'.uri_string(); ?>" id="fmain" method="post" enctype='multipart/form-data' class="form-horizontal">
                  <div class="form-body">

                    <?php if (isset($message) && $message != "") { ?>
                        <?php echo (isset($message) && $message != "") ? $message : ""; ?>
                    <?php } ?>
                    <?php if (isset($errmsg) && $errmsg != "") { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button class="close" data-dismiss="alert" aria-hidden="true" type="button">
                                x
                            </button>
                            <?php echo (isset($errmsg) && $errmsg != "") ? $errmsg : ""; ?>
                        </div>
                    <?php } ?>

                    <div class="row">
                      <div class="col-md-6">

                        <div class="form-group row">
                          <label class="control-label text-start text-md-end col-md-4" for="trans_akun_kode">No. Transaksi</label>
                          <div class="col-md-8">
                            <?= (isset($trans_akun_kode) && !empty($trans_akun_kode)) ? form_input($trans_akun_kode) : ""; ?>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label class="control-label text-start text-md-end col-md-4" for="coa_kode">Tanggal</label>
                          <div class="col-md-8">
                            <?= (isset($trans_akun_date) && !empty($trans_akun_date)) ? form_input($trans_akun_date) : ""; ?>
                          </div>
                        </div>


                        <div class="form-group row">
                          <label class="control-label text-start text-md-end col-md-4" for="ref_rekening_id">Kas/Bank</label>
                          <div class="col-md-8">
                            <?= (isset($ref_rekening_id) && !empty($ref_rekening_id)) ? form_dropdown($ref_rekening_id) : ""; ?>
                          </div>
                        </div>


                      </div>
                      <div class="col-md-6">
                        <div class="form-group row">
                          <label class="control-label text-start text-md-end col-md-4" for="keterangan">Keterangan</label>
                          <div class="col-md-8">
                            <?= (isset($keterangan) && !empty($keterangan)) ? form_textarea($keterangan) : ""; ?>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label class="control-label text-start text-md-end col-md-4" for="ref_so_sp">Ref. SO/Sampel</label>
                          <div class="col-md-8">
                            <?= (isset($ref_so_sp) && !empty($ref_so_sp)) ? form_dropdown($ref_so_sp) : ""; ?>
                          </div>
                        </div>
                      </div>
                      
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                          <div class="table-responsive">
                            <table id="dt-list-det" class="table-striped"></table>
                          </div>
                        </div>
                    </div>

                  </div>
                  <div class="form-actions m-t-30">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="row">
                          <div class="offset-sm-2 col-md-9">
                          <?php echo isset($id) ? form_hidden('id', $id) : ""; ?>
                          <?php echo isset($Ldetail) ? form_hidden($Ldetail) : ""; ?>
                            <a href="<?= base_url(); ?>/keuangan/transaksi_akun" class="btn btn-secondary" ><i class="fa fa-arrow-left"></i> Kembali</a>
                            <?php if (isset($show_save_btn) && $show_save_btn === TRUE) : ?>
                            <button id="btn-save" type="button" name="actionf" class="m-s-5 btn btn-success"> <i class="fa fa-save"></i> Simpan</button>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
      </div>

      <div id="modal-item" class="modal fade" tabsindex="-1" role="dialog" aria-labelledby="item-title" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="item-title">Add Akun</h5>
                    <button class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <p>Akun Beban</p>
                        <select name="coa_id" id="coa_id" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <p>Keterangan</p>
                        <textarea class="form-control" name="keterangan_det" id="keterangan_det" rows="5"></textarea>
                    </div>
                    <div class="form-group">
                        <p>Nilai</p>
                        <input type="number" name="jumlah" value="" 
                          id="jumlah" min="0" step="1" pattern="[0-9]*" class="form-control" 
                          placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" 
                          data-politespace-delimiter="," data-politespace-reverse="" 
                          data-politespace-decimal-mark=".">
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="detail_seq">
                    <input type="hidden" id="detail_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="btn-save-det" type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/keuangan/trans_akun/form.js"></script>
<?= $this->endSection('script'); ?>
