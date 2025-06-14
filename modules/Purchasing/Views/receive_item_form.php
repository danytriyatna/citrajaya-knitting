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
              <input type="hidden" id="edit" name="edit" class="form-control" required>
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
        <div class="form-group row d-none">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">Qty PO</label>
          <div class="col-md-9">
            <input type="hidden" id="qty_po" name="qty_po" class="form-control" placeholder="Terisi otomatis oleh sistem" value="" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">Qty Receive</label>
          <div class="col-md-9">
            <input type="text" id="qty_item" name="qty_item" class="form-control" placeholder="Ketikkan qty item" value="">
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="price">Price</label>
          <div class="col-md-9">
            <input type="text" id="price" name="price" class="form-control" placeholder="Terisi otomatis oleh sistem" value="" >
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_warehouse">Warehouse</label>
          <div class="col-md-9">
            <select id="select_warehouse" name="select_warehouse" class="form-select select2" data-placeholder="-- Pilih Warehouse --">
              <option value=""></option>
              <?php foreach ($gudang as $item) : ?>
                <option value="<?= $item->id ?>"><?= $item->nama_gudang ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="lot_no">Lot No.</label>
          <div class="col-md-9">
            <input type="text" id="lot_no" name="lot_no" class="form-control" placeholder="Ketikkan lot no." value="">
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
<div id="modal-po" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">List Purchase Order</h5>
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
          <div id="dt-list" class="table-responsive table-striped"></div>
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
          <div id="dt-list-barang" class="table-responsive table-striped"></div>
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
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="receive_no">Receive No.</label>
                    <div class="col-md-9">
                      <input type="hidden" id="status" name="status" value="<?= !empty($resData->status) ? $resData->status : null ?>" class="form-control" required>
                      <input type="hidden" id="data-details" value='<?= !empty($detail) ? $detail : null; ?>'>
                      <input type="hidden" id="id_header" name="id_header" value="<?= !empty($id) ? $id : null ?>" class="form-control" required>
                      <input type="text" id="receive_no" name="receive_no" class="form-control" placeholder="Diisi otomatis oleh sistem" value="<?= !empty($resData->po_no) ? $resData->po_no : null ?>" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tgl_receive">Receive Date</label>
                    <div class="col-md-8">
                      <input type="text" id="tgl_receive" name="tgl_receive" class="form-control datepicker" placeholder="Pilih tanggal Receive" value="<?= !empty($resData->rec_date) ? $resData->rec_date : date("d F Y", now()) ?>">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_no">PO NO</label>
                    <div class="col-md-9">
                      <div class="input-group">
                        <input type="text" id="poNo" value="<?= !empty($resData->po_no) ? $resData->po_no : null ?>" readonly name="poNo" class="form-control" placeholder="Pilih PO No." required>
                        <input type="hidden" id="idPo" name="idPo" value="<?= !empty($resData->id_po) ? $resData->id_po : null ?>" class="form-control" required>
                        <span id="spanPoNo" class="input-group-text bg-white" id="basic-addon11"><i class="ti-search"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="tgl_ship"><span class="text-nowrap">Ship Date</label>
                    <div class="col-md-9">
                      <input type="text" id="tgl_ship" name="tgl_ship" class="form-control datepicker" placeholder="Ship Date diisi otomatis oleh sistem." value="<?= !empty($resData->date_exc) ? $resData->date_exc : null ?>" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="form_no">No. Surat Jalan</label>
                    <div class="col-md-8">
                      <input type="text" id="form_no" name="form_no" class="form-control" placeholder="Ketikkan No. Surat Jalan" value="<?= !empty($resData->form_no) ? $resData->form_no : null ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group row">
                <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_vendor">Vendor</label>
                <div class="col-md-10">
                  <input type="text" id="nama_vendor" name="nama_vendor" class="form-control" placeholder="Vendor otomatis diisi oleh sistem." value="<?= !empty($resData->nama_vendor) ? $resData->nama_vendor : null ?>" readonly>
                </div>
              </div>
            </div>
          </div>

          <hr>
          <div class="col-sm-3 mb-2">
            <button type="button" class="btn btn-sm btn-primary" id="btn-add"> <i class="fa fa-plus"></i></button>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <div class="row">
                <div id="dt-list-detail" class="table-responsive table-striped"></div>
              </div>

              <br>

              <div class="row">
                <div class="col-sm-10">
                  <a href="purchasing/receive-item" class="btn btn-default m-e-5">
                    <span class="fa fa-arrow-left"></span> Kembali
                  </a>
                  <button class='btn btn-success' id="btn-simpan">
                    <span class="fa fa-save"></span> Simpan
                  </button>

                  <button type="button" class="m-s-5 btn btn-info" id="btn-approve"> <i class="fa fa-paper-plane"></i> Approval</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>]
<?= $this->section('script'); ?>
<script src="script/app/purchasing/receive/form.js"></script>
<?= $this->endSection('script'); ?>