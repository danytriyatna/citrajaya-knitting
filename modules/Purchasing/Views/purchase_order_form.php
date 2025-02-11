<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-detail-item" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Detail Item</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_item">Item</label>
          <div class="col-md-9">
            <div class="input-group">
              <input type="text" id="namaBarang" readonly name="namaBarang" class="form-control" placeholder="Pilih Barang" required>
              <input type="hidden" id="idDetail" name="idDetail" class="form-control" required>
              <input type="hidden" id="idBarang" name="idBarang" class="form-control" required>
              <input type="hidden" id="kodeBarang" name="kodeBarang" class="form-control" required>
              <span id="spanBarang" class="input-group-text bg-white" id="basic-addon11"><i class="ti-search"></i></span>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_unit">Unit</label>
          <div class="col-md-9">
            <input type="text" id="unit" name="unit" class="form-control" placeholder="Terisi otomatis oleh sistem" value="" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">Qty</label>
          <div class="col-md-9">
            <input type="text" id="qty_item" name="qty_item" class="form-control" placeholder="Ketikkan qty item">
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="unit_price">Unit Price</label>
          <div class="col-md-9">
            <input type="text" id="unit_price" name="unit_price" pattern="\d{10,13}" class="form-control" placeholder="Ketikkan unit price" value="">
            <small class="form-text">Hanya menerima input berupa angka karena mata uang rupiah</small>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label d-none" for="disc_item">Disc (%)</label>
          <div class="col-md-5 d-none">
            <input type="text" id="disc_item" name="disc_item" class="form-control" placeholder="Ketikkan nilai diskon" value="0">
          </div>
          <div class="col-md-2"></div>
          <div class=" col-md-4">
            <div class="form-check">
              <input type="hidden" id="tax" name="tax" value="<?= !empty($tax->nilai) ? $tax->nilai : 0 ?>" class="form-control" required>
              <input class="form-check-input" type="checkbox" value="" id="check_tax">
              <label class="form-check-label" for="check_tax">
                Tax
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="kode">Kode</label>
          <div class="col-md-9">
            <input type="text" id="kode" name="kode" class="form-control" placeholder="Ketikkan kode">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-success" id="btn-simpan-det">Simpan</button>
      </div>
    </div>
  </div>
</div>
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
<div id="modal-barang" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">List Barang</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 mb-3">
            <div class="col-md-4" style="float: right; position: relative; right: 15px;">
              <div class="homeSearch w-100" style="width: 100%; margin-left: 5%; margin-top: 0;">
                <input type="text" id="tb-search" class="form-control" placeholder="Pencarian . . .">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div id="dt-list" class="table-responsive table-striped"></div>
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
            <div class="col-sm-7">
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_no">PO No.</label>
                    <div class="col-md-9">
                      <input type="hidden" id="status" name="status" value="<?= !empty($resData->status) ? $resData->status : null ?>" class="form-control" required>
                      <input type="hidden" id="data-details" value='<?= !empty($detail) ? $detail : null; ?>'>
                      <input type="hidden" id="id_header" name="id_header" value="<?= !empty($id) ? $id : null ?>" class="form-control" required>
                      <input type="text" id="po_no" name="po_no" class="form-control" value="<?= !empty($resData->po_no) ? $resData->po_no : null ?>" readonly placeholder="Diisi otomatis oleh sistem">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tgl_po">PO Date</label>
                    <div class="col-md-8">
                      <input type="text" id="tgl_po" name="tgl_po" class="form-control datepicker" placeholder="Pilih tanggal PO" value="<?= !empty($resData->po_date) ? $resData->po_date : null ?>">
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="tgl_expected"><span class="text-nowrap">Expected</span><br>Date</label>
                    <div class="col-md-9">
                      <input type="text" id="tgl_expected" name="tgl_expected" class="form-control datepicker" placeholder="Pilih expected date" value="<?= !empty($resData->date_exc) ? $resData->date_exc : null ?>">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="select_term">Term</label>
                    <div class="col-md-8">
                      <select id="select_term" name="select_term" class="form-select select2" data-placeholder="-- Pilih Term --">
                        <?php foreach ($term as $row) : ?>
                          <?php if (!empty($data->id_term) && $data->id_term == $row['id']) { ?>
                            <option checked value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                          <?php } else { ?>
                            <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                        <?php }
                        endforeach ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group row">
                <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_vendor">Vendor</label>
                <div class="col-md-10">
                  <div class="input-group">
                    <input type="text" id="namaVendor" value="<?= !empty($resData->nama_vendor) ? $resData->nama_vendor : null ?>" readonly name="namaVendor" class="form-control" placeholder="Pilih Vendor" required>
                    <input type="hidden" id="idVendor" name="idVendor" value="<?= !empty($resData->id_vendor) ? $resData->id_vendor : null ?>" class="form-control" required>
                    <span id="spanVendor" class="input-group-text bg-white" id="basic-addon11"><i class="ti-search"></i></span>
                  </div>
                </div>
              </div>
              <div class="form-group row">
                <label class="control-label text-start text-md-end col-md-2 col-form-label" for="ship_to">Ship To</label>
                <div class="col-md-10">
                  <textarea rows="3" id="ship_to" name="ship_to" class="form-control" placeholder="Ketikkan uraian pengiriman"><?= !empty($resData->ship_to) ? $resData->ship_to : null ?></textarea>
                </div>
              </div>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-sm-3 mb-2">
              <button type="button" class="btn btn-sm btn-primary" id="btn-add"> <i class="fa fa-plus"></i></button>
            </div>
            <div class="col-sm-12">

              <div class="row">
                <div id="dt-list-po" class="table-responsive table-striped"></div>
              </div>

              <br>

              <div class="row">
                <div class="col-sm-10">
                  <a href="purchasing/purchase-order" class="btn btn-default m-e-5">
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
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/purchasing/order/form.js"></script>
<?= $this->endSection('script'); ?>