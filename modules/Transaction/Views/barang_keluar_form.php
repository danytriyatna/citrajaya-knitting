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
        <div class="form-group row ">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">QTY Remain</label>
          <div class="col-md-9">
            <input type="text" id="qty_exist" name="qty_exist" class="form-control" placeholder="Terisi otomatis oleh sistem" value="" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">Qty Out</label>
          <div class="col-md-9">
            <input type="text" id="qty_item" name="qty_item" class="form-control" placeholder="Ketikkan qty item" value="">
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="price">Price</label>
          <div class="col-md-9">
            <input type="text" id="price" name="price" pattern="\d{10,13}" class="form-control" placeholder="Ketikkan Price" value="">
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="lot_no">Lot No.</label>
          <div class="col-md-9">
            <input type="hidden" id="id_lot" name="id_lot">
            <input type="text" id="lot_no" name="lot_no" class="form-control" placeholder="Lot No. diisi otomatis oleh sistem" readonly>
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
                <input type="text" id="tb-search3" class="form-control" placeholder="Pencarian . . .">
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
            <div class="col-sm-7">
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="kode_transaksi">Transaction No.</label>
                    <div class="col-md-9">
                      <input type="hidden" id="status" name="status" value="<?= !empty($resData->status) ? $resData->status : null ?>" class="form-control" required>
                      <input type="hidden" id="data-details" value='<?= !empty($detail) ? $detail : null; ?>'>
                      <input type="hidden" id="id_header" name="id_header" value="<?= !empty($id) ? $id : null ?>" class="form-control" required>
                      <input type="text" id="kode_transaksi" name="kode_transaksi" class="form-control" placeholder="Diisi otomatis oleh sistem" value="<?= !empty($resData->kode_transaksi) ? $resData->kode_transaksi : null ?>" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tanggal">Date</label>
                    <div class="col-md-8">
                      <input type="text" id="tanggal" name="tanggal" class="form-control datepicker" placeholder="Pilih tanggal" value="<?= !empty($resData->tanggal) ? $resData->tanggal : date("d F Y", now()) ?>">
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="select_kategori">Tipe<span class="text-danger">*</span></label>
                    <div class="col-md-9">
                      <select id="select_kategori" name="select_kategori" class="form-select select2" value="" data-placeholder="-- Pilih Tipe --" required>

                        <?php foreach ($kategori as $item) : ?>
                          <?php if (!empty($resData->id_kategori) && $resData->id_kategori == $item['id']) { ?>
                            <option selected value="<?= $item['id'] ?>"><?= $item['kategori'] ?></option>
                          <?php } else { ?>
                            <option value="<?= $item['id'] ?>"><?= $item['kategori'] ?></option>
                          <?php } ?>
                        <?php endforeach; ?>
                      </select>
                      <div class="invalid-feedback">
                        Kategori tidak valid
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="select_warehouse">Warehouse</label>
                    <div class="col-md-9">
                      <select id="select_warehouse" name="select_warehouse" class="form-select select2" data-placeholder="-- Pilih Warehouse --">
                        <?php foreach ($gudang as $item) : ?>
                          <?php if (!empty($resData->id_gudang) && $resData->id_gudang == $item->id) { ?>
                            <option selected value="<?= $item->id ?>"><?= $item->nama_gudang ?></option>
                          <?php } else { ?>
                            <option value="<?= $item->id ?>"><?= $item->nama_gudang ?></option>
                          <?php } ?>

                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row d-none" id="divNamaVendor">
                    <label id="labelNama" class="control-label text-start text-md-end col-md-3 col-form-label" for="nama">Vendor<span class="text-danger">*</span></label>
                    <div class="col-md-9">
                      <div class="input-group">
                        <input type="hidden" id="id_vendor" name="id_vendor" value="<?= !empty($resData->id_vendor) ? $resData->id_vendor : null ?>" class="form-control" required>
                        <input type="text" id="nama_vendor" name="nama_vendor" class="form-control" placeholder="Diisi otomatis oleh sistem" value="<?= !empty($resData->nama) ? $resData->nama : null ?>" required>
                        <span id="spanVendor" class="input-group-text bg-white" id="basic-addon11"><i class="ti-search"></i></span>
                      </div>
                      <div class="invalid-feedback">
                        Vendor tidak valid
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group row">
                <label class="control-label text-start text-md-end col-md-2 col-form-label" for="trans_desc">Desc</label>
                <div class="col-md-10">
                  <textarea rows="3" id="trans_desc" name="trans_desc" class="form-control" placeholder="Ketikkan uraian deskripsi"><?= !empty($resData->keterangan) ? $resData->keterangan : null ?></textarea>
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
                  <a href="trans/outgoing-goods" class="btn btn-default m-e-5">
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
<script src="script/app/transaction/barang_keluar/form.js"></script>
<?= $this->endSection('script'); ?>