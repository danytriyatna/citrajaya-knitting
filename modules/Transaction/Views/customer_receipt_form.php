<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>

<?= $this->endSection('modal') ?>

<?= $this->section('content'); ?>
<?php echo isset($disabled_input) ? "<script>let disabled_input =". json_encode($disabled_input) ."</script>" : ""; ?>
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
    <form action="<?= base_url().'/'.uri_string(); ?>" id="fmain" method="post" enctype='multipart/form-data' class="form-horizontal">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-3 col-form-label" for="cr_no">CR No.</label>
                      <div class="col-md-9">
                        <input type="text" readonly id="cr_no" name="cr_no" class="form-control" placeholder="Ketikkan nomor CR" value="<?= !empty($row->kode_cr)? $row->kode_cr : '' ?>">
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tgl_cr">CR Date</label>
                      <div class="col-md-8">
                        <input <?= ($disabled_input)? 'disabled' : '' ?> type="text" id="tgl_cr" name="tgl_cr" class="form-control datepickerx" placeholder="Pilih tanggal CR" value="<?= !empty($row->tgl_transaksi)? $row->tgl_transaksi : date("d F Y", now()) ?>">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-2 col-form-label custom-col-md-2" for="select_buyer">Buyer</label>
                      <div class="col-md-10">
                        <select <?= ($disabled_input)? 'disabled' : '' ?> value="<?= !empty($row->id_konsumen)? $row->id_konsumen : '' ?>" id="select_buyer" name="select_buyer" class="form-select select2" data-placeholder="-- Pilih Buyer --">
                          <option value=""> - Pilih Buyer - </option>
                          <?php foreach ($konsumen_list as $item) { ?>
                            <option value="<?= $item->id; ?>"><?= $item->nama; ?></option> 
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group row">
                  <label class="control-label text-start text-md-end col-md-3 col-form-label" for="select_payment_type">Payment Type</label>
                  <div class="col-md-9">
                    <select <?= ($disabled_input)? 'disabled' : '' ?> value="<?= !empty($row->id_rekening)? $row->id_rekening : '' ?>" id="select_payment_type" name="select_payment_type" class="form-select select2" data-placeholder="-- Pilih Payment Type --">
                        <option value=""> - Pilih Payment Type - </option>
                        <?php foreach ($rekening_list as $item) { ?>
                          <option value="<?= $item->id; ?>"><?= $item->rekening_no; ?> - <?= $item->rekening_bank; ?></option> 
                        <?php } ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <hr>

            <div class="row">
              <div class="col-sm-12">
                <div class="table-responsive">
                  <div id="dt-list-det" class="table-striped table-centered"></div>
                  <!-- <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>SI NO.</th>
                        <th>SI DATE</th>
                        <th>DUE DATE</th>
                        <th>INV. AMOUNT</th>
                        <th>PAID</th>
                        <th>REMAINING AMOUNT</th>
                        <th>PAYMENT AMOUNT</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>SIV24100001</td>
                        <td>01-10-2024</td>
                        <td>31-10-2024</td>
                        <td class="text-nowrap">1.800.000,00</td>
                        <td class="text-nowrap">0,00</td>
                        <td class="text-nowrap">1.800.000,00</td>
                        <td>
                          <input type="text" class="form-control form-idr" value="1800000">
                        </td>
                      </tr>
                    </tbody>
                  </table> -->
                </div>
                
                <br>

                <div class="row">
                  <div class="col-sm-10">
                  <?php echo isset($Ldetail) ? form_hidden($Ldetail) : ""; ?>
                  <?php echo isset($id) ? form_hidden('id', $id) : ""; ?>
                  <input type="hidden" id="actionf" name="actionf" value="">
                    <a href="trans/customer-receipt" class="btn btn-default m-e-5">
                      <span class="fa fa-arrow-left"></span> Kembali
                    </a>

                    <?php if (isset($show_save_btn) && $show_save_btn === TRUE): ?>
                        <button id="btn-save" type="button" name="actionf" value="save"
                                class='btn btn-primary float-left text-white m-l-5'>
                            <span class="fa fa-save"></span> Simpan
                        </button>
                    <?php endif; ?>
                    <?php if (isset($show_approve_btn) && $show_approve_btn === TRUE): ?>
                        <button id="btn-approve" type="button" name="actionf" value="approve"
                                class='btn btn-success float-left text-white m-l-5'>
                            <span class="fa fa-check"></span> Approve
                        </button>
                    <?php endif; ?>
                    <?php if (isset($show_reject_btn) && $show_reject_btn === TRUE): ?>
                        <button id="btn-reject" type="button" name="actionf" value="reject"
                                class='btn btn-danger float-left text-white m-l-5'>
                            <span class="fa fa-remove"></span> Reject
                        </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/transaction/cr/form.js"></script>
<?= $this->endSection('script'); ?>