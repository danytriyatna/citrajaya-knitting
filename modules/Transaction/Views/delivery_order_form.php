<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>


<div id="modal-list-produksi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title ipendix-penguji-preview-title">List Data Produksi</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 mb-3">
            <div class="col-md-4" style="float: right; position: relative; right: 15px;">
              <div class="homeSearch w-100" style="width: 100%; margin-left: 5%; margin-top: 0;">
                <input type="text" id="tb-produksi" class="form-control" placeholder="Pencarian . . .">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div id="dt-list-produksi" class="table-striped table-centered"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <!-- <a href="javascript:void(0)" id="pilihUkuran" class="btn btn-success float-left">Pilih Data</a> -->
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

  <form action="<?= base_url().'/'.uri_string(); ?>" id="fmain" method="post" enctype='multipart/form-data' class="form-horizontal">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-3 col-form-label" for="do_no">DO No.</label>
                      <div class="col-md-9">
                        <input <?=  ($view_read) ? 'disabled' : ''; ?> type="text" id="do_no" name="do_no" class="form-control " placeholder="Ketikkan nomor DO" value="<?= !empty($row) ? $row->delivery_kode: '' ; ?>">
                        <input type="hidden" name="id_produksi" id="id_produksi" value="<?= !empty($row->id_produksi)? $row->id_produksi : ''; ?>" >
                        <input type="hidden" name="id_walkorder" id="id_walkorder" value="<?= !empty($row->id_walkorder)? $row->id_walkorder : ''; ?>" >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-3 col-form-label" for="so_no">Ref No.</label>
                      <div class="col-md-8">
                        <input type="hidden" readonly name="kode_produksi" id="kode_produksi" class="form-control" value="<?= !empty($row) ? $row->kode_produksi : '' ;?>" > 
                        <input type="text" readonly name="kode_so" id="kode_so" class="form-control" value="<?= !empty($row) ? $row->kode_so : '' ;?>" > 
                      </div>
                      <div <?=  (!empty($id)) ? 'hidden' : ''; ?> class="col-md-1">
                        <button type="button" id="list_prod" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tgl_do">DO Date</label>
                      <div class="col-md-8">
                        <input <?=  ($view_read) ? 'disabled' : ''; ?> type="text" id="tgl_do" name="tgl_do" class="form-control datepickerx" placeholder="Pilih tanggal DO" value="<?= !empty($row) ? $row->tgl_do : '' ;?>">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-2 col-form-label custom-col-md-2" for="select_style">Style</label>
                      <div class="col-md-10">
                        <input <?=  ($view_read) ? 'disabled' : ''; ?> type="text" name="keterangan_style" id="keterangan_style" class="form-control" value="<?= !empty($row) ? $row->keterangan_style : '' ;?>" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group row">
                  <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_buyer">Buyer</label>
                  <div class="col-md-9">
                    <select <?=  (!empty($id)) ? 'disabled' : ''; ?> id="select_buyer" name="select_buyer" class="form-select select2" data-placeholder="-- Pilih Buyer --" value="<?= !empty($row) ? $row->select_buyer : '' ;?>" >
                      <option value=""> - Pilih Buyer - </option>
                      <?php foreach ($buyer as $item) { ?>
                        <option value="<?= $item['id']; ?>" data-alamat="<?= $item['alamat'] ?>"><?= $item['nama']; ?></option> 
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="control-label text-start text-md-end col-md-2 col-form-label" for="alamat_buyer">Alamat</label>
                  <div class="col-md-9">
                    <textarea <?= ($view_read) ? 'disabled' : ''; ?> rows="3"
          id="alamat_buyer" name="alamat_buyer"
          class="form-control" placeholder="Ketikkan alamat"><?=
    !empty($row) ? trim($row->alamat_buyer) : '' ;
?></textarea>
                  </div>
                </div>
              </div>
            </div>

            <hr>

            <div class="row">
              <div class="col-sm-12">
                <div class="table-responsive">
                  <div class="table-striped table-centered" id="dt-prod"></div>
                  <!-- <table class="table table-striped table-centered">
                    <thead>
                      <tr>
                        <th>No.</th>
                        <th>COLOUR</th>
                        <th style="min-width: 80px;">S</th>
                        <th style="min-width: 80px;">M</th>
                        <th style="min-width: 80px;">L</th>
                        <th style="min-width: 80px;">XL</th>
                        <th style="min-width: 80px;">2XL</th>
                        <th style="min-width: 80px;">ALL</th>
                        <th rowspan="2">ORDER QTY</th>
                        <th rowspan="2">DO QTY</th>
                        <th rowspan="2">REMAIN</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>1</td>
                        <td class="text-nowrap">M38 - MINT- HITAM- OFF WHITE</td>
                        <td>1511</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>1511</td>
                        <td>36</td>
                        <td>1475</td>
                      </tr>
                    </tfoot>
                  </table> -->
                </div>
              </div>
            </div>

            <hr>

            <div class="row">
              <div class="col-sm-12">
                <div <?=  ($view_read) ? 'hidden' : ''; ?> class="input-group my-2">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-qrcode"></i></span>
                  <input type="text" class="form-control bg-info bg-opacity-25" placeholder="Scan" aria-label="Scan" aria-describedby="basic-addon1" id="text_barcode">
                </div>

                <div class="table-responsive">
                  <!-- <table class="table table-striped">
                    <thead>
                      <tr>
                        <th></th>
                        <th>DATE</th>
                        <th>COLOUR</th>
                        <th>SIZE</th>
                        <th>AVAILABLE QTY</th>
                        <th>DO QTY</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <button type="button" class="btn btn-sm btn-danger"> <i class="fa fa-trash"></i></button>
                        </td>
                        <td>01-10-2024</td>
                        <td>M38 - MINT- HITAM- OFF WHITE</td>
                        <td>M</td>
                        <td>12</td>
                        <td>
                          <input type="text" class="form-control" value="12">
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <button type="button" class="btn btn-sm btn-danger"> <i class="fa fa-trash"></i></button>
                        </td>
                        <td>05-10-2024</td>
                        <td>M38 - MINT- HITAM- OFF WHITE</td>
                        <td>M</td>
                        <td>24</td>
                        <td>
                          <input type="text" class="form-control" value="24">
                        </td>
                      </tr>
                    </tbody>
                  </table> -->
                  <div class="table-striped" id="dt-list-detail"></div>
                </div>
                
                <br>

                <div class="row">
                  <div class="col-sm-10">
                    <a href="trans/delivery-order" class="btn btn-default m-e-5">
                      <span class="fa fa-arrow-left"></span> Kembali
                    </a>
                    <?php if($status == 1) { ?>
                      <button type="button" id="btn-save" class='btn btn-success'>
                        <span class="fa fa-save"></span> Simpan
                      </button>
                      <?php if(!empty($id)) { ?>
                      <button type="button" id="btn-send" class='btn btn-info'>
                        <span class="fa fa-paper-plane"></span> Approval
                      </button>
                      <?php } ?>
                    <?php } ?>
                    <input type="hidden" name="actionf" id="actionf">
                    <input type="hidden" name="status" id="status" value="<?= !empty($status)? $status : '0' ?>">
                    <input type="hidden" name="id" id="id" value="<?= !empty($id)? $id : '' ?>">
                    <input type="hidden" name="data-ukuran" id="data-ukuran" value='<?= !empty($dt_ukuran)? $dt_ukuran : "" ?>'>
                    <input type="hidden" name="data-details" id="data-details" value='<?= !empty($dt_details)? $dt_details : "" ?>'>
                    <input type="hidden" name="data-prods" id="data-prods" value='<?= !empty($dt_prods)? $dt_prods : "" ?>'>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/transaction/delivery/form.js"></script>
<?= $this->endSection('script'); ?>