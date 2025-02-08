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
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">QTY Remain </label>
          <div class="col-md-9">
            <input type="text" id="qty_exist" name="qty_exist" class="form-control" placeholder="Terisi otomatis oleh sistem" value="" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="qty_item">Qty</label>
          <div class="col-md-9">
            <input type="text" id="qty_item" name="qty_item" class="form-control" placeholder="Ketikkan qty item" value="">
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="price">Price</label>
          <div class="col-md-9">
            <input type="text" id="price" name="price" class="form-control" placeholder="Ketikkan Price" value="">
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="lot_no">Lot No.</label>
          <div class="col-md-9">
            <input type="hidden" id="id_lot" name="id_lot">
            <input type="text" id="lot_no" name="lot_no" class="form-control" placeholder="Ketikkan nomor lot" value="">
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
<div id="modal-so" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">List Data Sales Order / Sample</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 mb-3">
            <div class="col-md-4" style="float: right; position: relative; right: 15px;">
              <div class="homeSearch w-100" style="width: 100%; margin-left: 5%; margin-top: 0;">
                <input type="text" id="tb-search-so" class="form-control" placeholder="Pencarian . . .">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div id="dt-list-sample" class="table-responsive table-striped"></div>
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
          <li class="breadcrumb-item">Transaksi</li>
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
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="trans_no">Trans No.</label>
                    <div class="col-md-9">
                      <input type="hidden" id="status" name="status" value="<?= !empty($resData->status) ? $resData->status : null ?>" class="form-control" required>
                      <input type="hidden" id="data-details" value='<?= !empty($detail) ? $detail : null; ?>'>
                      <input type="hidden" id="id_header" name="id_header" value="<?= !empty($id) ? $id : null ?>" class="form-control" required>
                      <input type="text" id="trans_no" name="trans_no" class="form-control" placeholder="Diisi otomatis oleh sistem" value="<?= !empty($resData->kode_transaksi) ? $resData->kode_transaksi : null ?>" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tanggal">Date</label>
                    <div class="col-md-8">
                      <input type="text" id="tanggal" name="tanggal" class="form-control datepicker" placeholder="Pilih tanggal transfer" value="<?= !empty($resData->tanggal) ? $resData->tanggal : null ?>">
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="select_warehouse">Transfer From</label>
                    <div class="col-md-8">
                      <select id="gudang_asal" name="gudang_asal" class="form-select select2" data-placeholder="-- Pilih Warehouse --">
                        <?php foreach ($gudang as $item) : ?>
                          <?php if (!empty($resData->id_gudang_asal) && $resData->id_gudang_asal == $item->id) { ?>
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
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="gudang_tujuan">Transfer To</label>
                    <div class="col-md-8">
                      <select id="gudang_tujuan" name="gudang_tujuan" class="form-select select2" data-placeholder="-- Pilih Warehouse --">
                        <?php foreach ($gudang as $item) : ?>
                          <?php if (!empty($resData->id_gudang_tujuan) && $resData->id_gudang_tujuan == $item->id) { ?>
                            <option selected value="<?= $item->id ?>"><?= $item->nama_gudang ?></option>
                          <?php } else { ?>
                            <option value="<?= $item->id ?>"><?= $item->nama_gudang ?></option>
                          <?php } ?>

                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tipe">Tipe</label>
                    <div class="col-md-8">
                      <select id="tipe" name="tipe" class="form-select select2" data-placeholder="-- Pilih Tipe --">
                        <?php if (!empty($resData->tipe) && $resData->tipe == 2) { ?>
                          <option value="1">NON CMT</option>
                          <option selected value="2">CMT</option>
                        <?php } else { ?>
                          <option selected value="1">NON CMT</option>
                          <option value="2">CMT</option>
                        <?php } ?>
                      </select>
                    </div>

                  </div>
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-sm btn-info d-none" id="btn-view"> <i class="fa fa-eye"></i></button>
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
                  <a href="trans/item-transfer" class="btn btn-default m-e-5">
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

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/transaction/transfer/formv1.js"></script>
<?= $this->endSection('script'); ?>