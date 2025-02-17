<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-vendor" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">List Vendor</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 mb-3">
            <div class="col-md-4" style="float: right; position: relative; right: 15px;">
              <div class="homeSearch w-100" style="width: 100%; margin-left: 5%; margin-top: 0;">
                <input type="text" id="tb-search2" class="form-control" placeholder="Pencarian . . .">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div id="dt-list-vendor" class="table-responsive table-striped"></div>
        </div>
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
          <li class="breadcrumb-item">Pembelian</li>
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
            <div class="col-sm-6">
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="pp_no">PP No.</label>
                    <div class="col-md-9">
                      <input type="hidden" id="status" name="status" value="<?= !empty($resData->status) ? $resData->status : null ?>" class="form-control" required>
                      <input type="hidden" id="data-details" value='<?= !empty($detail) ? $detail : null; ?>'>
                      <input type="hidden" id="id_header" name="id_header" value="<?= !empty($id) ? $id : null ?>" class="form-control" required>
                      <input type="text" id="pp_no" name="pp_no" class="form-control" readonly placeholder="Diisi otomatis oleh sistem" value="<?= !empty($resData) ? $resData->pay_no : null ?>">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="pp_cr">PP Date</label>
                    <div class="col-md-8">
                      <input type="text" id="pp_cr" name="pp_cr" class="form-control datepicker" placeholder="Pilih tanggal PP" value="<?= !empty($resData) ? $resData->pay_date : date("d F Y", now()) ?>">
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-2 col-form-label custom-col-md-2" for="select_vendor">Buyer</label>
                    <div class="col-md-9">
                      <div class="input-group">
                        <input type="text" id="namaVendor" value="<?= !empty($resData->nama_vendor) ? $resData->nama_vendor : null ?>" readonly name="namaVendor" class="form-control" placeholder="Pilih Vendor" required>
                        <input type="hidden" id="idVendor" name="idVendor" value="<?= !empty($resData->id_vendor) ? $resData->id_vendor : null ?>" class="form-control" required>
                        <span id="spanVendor" class="input-group-text bg-white" id="basic-addon11"><i class="ti-search"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group row">
                <label class="control-label text-start text-md-end col-md-3 col-form-label" for="select_payment_type">Payment Type</label>
                <div class="col-md-9">
                  <select id="select_payment_type" name="select_payment_type" class="form-select select2" data-placeholder="-- Pilih Payment Type --">
                    <?php foreach ($rekening_list as $item) : ?>
                      <?php if (!empty($resData->id_rek) && $resData->id_rek != $item->id) { ?>
                        <option checked value="<?= $item->id; ?>"><?= $item->rekening_no; ?> - <?= $item->rekening_bank; ?></option>
                      <?php } else { ?>
                        <option value="<?= $item->id; ?>"><?= $item->rekening_no; ?> - <?= $item->rekening_bank; ?></option>
                    <?php }
                    endforeach ?>

                  </select>
                </div>
              </div>
            </div>
          </div>

          <hr>
          <div class="row">
            <div id="dt-list-payment" class="table-responsive table-striped"></div>
          </div>
          <br>

          <div class="row">
            <div class="col-sm-10">
              <a href="purchasing/purchase-payment" class="btn btn-default m-e-5">
                <span class="fa fa-arrow-left"></span> Kembali
              </a>
              <button class='btn btn-success' id="btn-simpan">
                <span class="fa fa-save"></span> Simpan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/purchasing/payment/form.js"></script>
<?= $this->endSection('script'); ?>