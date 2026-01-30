<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-add-po" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Tambah Sales Order</h5>
        <input type="hidden" id="data_id">
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-secondary p-y-8 text-muted">
          <i>*) Wajib diisi</i>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="no_sales_order">Sales Order No.</label>
          <div class="col-md-9">
            <input type="text" id="no_sales_order" readonly name="no_sales_order" class="form-control" placeholder="Ketikkan nomor sales_order" value="" required>
            <div class="invalid-feedback">
              Sales Order No. tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="tgl_sales_order">Date<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="tgl_sales_order" name="tgl_sales_order" class="form-control datepicker" placeholder="Pilih tanggal sales_order" value="" required>
            <div class="invalid-feedback">
              Date tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_buyer">Buyer<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <select id="select_buyer" name="select_buyer" class="form-select select2" data-placeholder="-- Pilih Buyer --" required>
              <option value=""></option>
              <?php foreach ($buyer as $item) : ?>
                <option value="<?= $item['id'] ?>"><?= $item['nama'] ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">
              Buyer tidak valid
            </div>
          </div>
        </div>
       
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_samples">Style/Sample</label>
          <div class="col-md-9">
            <select id="select_samples" name="select_samples" class="form-select select2" data-placeholder="-- Pilih Style/Sample --" required>
              <option value=""></option>
            </select>
            <div class="invalid-feedback">
                Sample tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="style">Style<span class="text-danger">*</span></label>
          <div class="col-md-4">
            <input type="text" id="style" name="style" class="form-control" placeholder="Ketikkan Sales Order Style" value="" required>
            <div class="invalid-feedback">
              Sales Order Style tidak valid
            </div>
          </div>
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="repeat_inp">Repeat</label>
          <div class="col-md-3">
            <input readonky type="text" id="repeat_inp" name="repeat_inp" class="form-control" value="0" placeholder="Ketikkan Repeat">
            <div class="invalid-feedback">
              Repeat tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="desc_style">Deskripsi<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="desc_style" name="desc_style" class="form-control" placeholder="Ketikkan sales_order description" value="" required>
            <div class="invalid-feedback">
              Sales Order Description tidak valid
            </div>
          </div>
        </div>
       
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="tgl_deadline">Deadline<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="tgl_deadline" name="tgl_deadline" class="form-control datepicker" placeholder="Pilih tanggal deadline" value="" required>
            <div class="invalid-feedback">
              Deadline tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="tgl_deadline_dua">Deadline 2<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="tgl_deadline_dua" name="tgl_deadline_dua" class="form-control datepicker" placeholder="Pilih tanggal deadline 2" value="" required>
            <div class="invalid-feedback">
              Deadline Kedua tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="uang_dp">DownPayment</label>
          <div class="col-md-2">
            <input type="number" name="uang_dp" value="" id="uang_dp" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark=".">
            <div class="invalid-feedback">
              DownPayment tidak valid
            </div>
          </div>
          <label class="control-label text-start text-md-end col-md-1 col-form-label" for="tgl_dp">Tgl DP</label>
          <div class="col-md-2">
            <input type="text" id="tgl_dp" name="tgl_dp" class="form-control datepicker" placeholder="Pilih tgl dp" value="" required>
            <div class="invalid-feedback">
              Tanggal DP tidak valid
            </div>
          </div>
          <label class="control-label text-start text-md-end col-md-1 col-form-label" for="select_type">Tipe</label>
          <div class="col-md-3">
            <select id="select_type" name="select_type" class="form-select select2" data-placeholder="-- Pilih Tipe Bayar --" required>
              <option value=""></option>
              <?php foreach ($rekening_list as $item) : ?>
                <option value="<?= $item['id'] ?>"><?= $item['rekening_no']; ?> - <?= $item['rekening_bank']; ?></option>
              <?php endforeach; ?>
              
            </select>
            <div class="invalid-feedback">
              Tipe Bayar tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="uang_dp_2">DownPayment 2</label>
          <div class="col-md-2">
            <input type="number" name="uang_dp_2" value="" id="uang_dp_2" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark=".">
            <div class="invalid-feedback">
              DownPayment 2 tidak valid
            </div>
          </div>
          <label class="control-label text-start text-md-end col-md-1 col-form-label" for="tgl_dp_2">Tgl DP</label>
          <div class="col-md-2">
            <input type="text" id="tgl_dp_2" name="tgl_dp_2" class="form-control datepicker" placeholder="Pilih tgl dp" value="" required>
            <div class="invalid-feedback">
              Tanggal DP 2 tidak valid
            </div>
          </div>
          <label class="control-label text-start text-md-end col-md-1 col-form-label" for="select_type_2">Tipe</label>
          <div class="col-md-3">
            <select id="select_type_2" name="select_type_2" class="form-select select2" data-placeholder="-- Pilih Tipe Bayar --" required>
              <option value=""></option>
              <?php foreach ($rekening_list as $item) : ?>
                <option value="<?= $item['id'] ?>"><?= $item['rekening_no']; ?> - <?= $item['rekening_bank']; ?></option>
              <?php endforeach; ?>
              
            </select>
            <div class="invalid-feedback">
              Tipe Bayar 2 tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="pengiriman">Pengiriman</label>
          <div class="col-md-9">
            <input type="number" name="pengiriman" value="" id="pengiriman" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark=".">
                 <div class="invalid-feedback">
                  Pengiriman tidak valid
                </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="ket_sales_order">Keterangan</label>
          <div class="col-md-9">
            <input type="text" id="ket_sales_order" name="ket_sales_order" class="form-control" placeholder="Ketikkan uraian keterangan" value="">
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="foto_style">Foto</label>
          <div class="col-md-9">
            <input type="hidden" id="fileSalesOrderOld">
            <input type="file" id="fileSalesOrder" onchange="readURL(this,'#fileSalesOrder')" name="fileSalesOrder" class="form-control file-drag-drop" accept=".jpg, .jpeg, .png">
            <small class="form-text">Format file *.JPG, *.JPEG, *.PNG, ukuran maks. 1 MB</small>
            <br>
            <img class="m-t-10 w-40 d-none" id="linkFileSalesOrder" alt="Foto Sales Order">
          </div>
        </div>
        <div id="rowDet">
          <hr>
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-sm btn-success mb-2" id="btn-add-detail"> Tambah <i class="fa fa-plus"></i></button>
              <div id="dt-detail" class="table-responsive table-striped"></div>

            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="m-s-5 btn btn-success" id="btn-save"> <i class="fa fa-save"></i> Simpan</button>
        <button type="button" class="m-s-5 btn btn-info" id="btn-send"> <i class="fa fa-paper-plane"></i> Approval</button>
      </div>
    </div>
  </div>
</div>

<div id="modal-form-po" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Detail Warna & Ukuran Sales Order</h5>
        <input type="hidden" id="id_sales_order_qty">
        <input type="hidden" id="id_sales_order_det_qty">
        <button class="btn-close" data-bs-toggle="modal" data-bs-target="#modal-form-add-po" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-3">
            <img class="w-100" id="fotoText" src="" alt="Foto Sales Order">
          </div>
          <div class="col-sm-9">
            <h6 class="f-w-700 m-b-6" id="noSalesOrderText"></h6>
            <p class="f-w-500 m-y-0" id="deskripsiText"></p>
            <hr class="m-y-8" />
            <p class="m-y-0" id="tglSalesOrderText"></p>
            <p class="m-y-0" id="tglDeadlineText"></p>
            <p class="m-t-8 badge bg-secondary d-inline-block" id="buyerText"></p>
          </div>
        </div>

        <hr>

        <h6 class="f-w-700">Pilih Warna Style</h6>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna1">Warna A</label>
              <div class="col-md-9">
                <select id="po_warna1" name="po_warna1" class="form-select select2" data-placeholder="-- Pilih Warna A --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna2">Warna B</label>
              <div class="col-md-9">
                <select id="po_warna2" name="po_warna2" class="form-select select2" data-placeholder="-- Pilih Warna B --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna3">Warna C</label>
              <div class="col-md-9">
                <select id="po_warna3" name="po_warna3" class="form-select select2" data-placeholder="-- Pilih Warna C --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna4">Warna D</label>
              <div class="col-md-9">
                <select id="po_warna4" name="po_warna4" class="form-select select2" data-placeholder="-- Pilih Warna D --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

          </div>
          <div class="col-sm-6">
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna6">Warna E</label>
              <div class="col-md-9">
                <select id="po_warna5" name="po_warna5" class="form-select select2" data-placeholder="-- Pilih Warna E --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna7">Warna F</label>
              <div class="col-md-9">
                <select id="po_warna6" name="po_warna6" class="form-select select2" data-placeholder="-- Pilih Warna F --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna7">Warna G</label>
              <div class="col-md-9">
                <select id="po_warna7" name="po_warna7" class="form-select select2" data-placeholder="-- Pilih Warna G --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-3 col-form-label" for="po_warna9">Warna H</label>
              <div class="col-md-9">
                <select id="po_warna8" name="po_warna8" class="form-select select2" data-placeholder="-- Pilih Warna H --">
                  <option value=""></option>
                  <?php foreach ($warna as $item) : ?>
                    <option value="<?= $item['id'] ?>"><?= $item['kode_warna'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

          </div>
        </div>

        <hr>

        <h6 class="f-w-700">Quantity dan Harga per Ukuran</h6>
        <div class="row m-t-16">
          <div class="col-sm-12">
            <div id="dt-detail-qty" class="table-responsive table-striped"></div>
            <div class="table-responsive d-none">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>NO</th>
                    <th>UKURAN</th>
                    <th style="min-width: 80px; width: 80px;">QTY</th>
                    <th style="min-width: 200px;">PRICE</th>
                    <th>TOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>S</td>
                    <td><input type="text" id="qty_s" name="qty_s" class="form-control" placeholder="Ketikkan qty ukuran S" value="100"></td>
                    <td><input type="text" id="price_s" name="price_s" class="form-control form-idr" placeholder="Ketikkan harga ukuran S" value="20000"></td>
                    <td>Rp 2.000.000</td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>M</td>
                    <td><input type="text" id="qty_m" name="qty_m" class="form-control" placeholder="Ketikkan qty ukuran M" value="100"></td>
                    <td><input type="text" id="price_m" name="price_m" class="form-control form-idr" placeholder="Ketikkan harga ukuran M" value="25000"></td>
                    <td>Rp 2.500.000</td>
                  </tr>
                  <tr>
                    <td>3</td>
                    <td>L</td>
                    <td><input type="text" id="qty_l" name="qty_l" class="form-control" placeholder="Ketikkan qty ukuran L" value="100"></td>
                    <td><input type="text" id="price_l" name="price_l" class="form-control form-idr" placeholder="Ketikkan harga ukuran L" value="30000"></td>
                    <td>Rp 3.000.000</td>
                  </tr>
                  <tr>
                    <td>4</td>
                    <td>XL</td>
                    <td><input type="text" id="qty_xl" name="qty_xl" class="form-control" placeholder="Ketikkan qty ukuran XL" value="100"></td>
                    <td><input type="text" id="price_xl" name="price_xl" class="form-control form-idr" placeholder="Ketikkan harga ukuran XL" value="35000"></td>
                    <td>Rp 3.500.000</td>
                  </tr>
                  <tr>
                    <td>5</td>
                    <td>2XL</td>
                    <td><input type="text" id="qty_2xl" name="qty_2xl" class="form-control" placeholder="Ketikkan qty ukuran 2XL" value="100"></td>
                    <td><input type="text" id="price_2xl" name="price_2xl" class="form-control form-idr" placeholder="Ketikkan harga ukuran 2XL" value="40000"></td>
                    <td>Rp 4.000.000</td>
                  </tr>
                  <tr>
                    <td>6</td>
                    <td>3XL</td>
                    <td><input type="text" id="qty_3xl" name="qty_3xl" class="form-control" placeholder="Ketikkan qty ukuran 3XL" value="100"></td>
                    <td><input type="text" id="price_3xl" name="price_3xl" class="form-control form-idr" placeholder="Ketikkan harga ukuran 3XL" value="45000"></td>
                    <td>Rp 4.500.000</td>
                  </tr>
                  <tr>
                    <td>7</td>
                    <td>ALL SIZE</td>
                    <td><input type="text" id="qty_all" name="qty_all" class="form-control" placeholder="Ketikkan qty ukuran ALL SIZE" value="100"></td>
                    <td><input type="text" id="price_all" name="price_all" class="form-control form-idr" placeholder="Ketikkan harga ukuran ALL SIZE" value="50000"></td>
                    <td>Rp 5.000.000</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="m-s-5 btn btn-success" id="btn-save-detail"> <i class="fa fa-save"></i> Simpan</button>
      </div>
    </div>
  </div>
</div>


<div id="modal-print-barcode" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-bold">Cetak Barcode</h5>
        <input type="hidden" id="id_sample_qty">
        <input type="hidden" id="id_sample_det_qty">
        <input type="hidden" id="style_input">
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row">
          <div class="col-sm-3">
            <img class="w-100" id="fotoPrint" alt="Foto Sample">
          </div>
          <div class="col-sm-9">
            <h6 class="f-w-700 m-b-6" id="noSamplePrint">-</h6>
            <p class="f-w-500 m-y-0" id="deskripsiPrint">-</p>
            <p class="f-w-500 m-y-0" id="warnaPrint">-</p>
            <p class="f-w-500 m-y-0 d-none" id="warnaTrans">Trans</p>
            <hr class="m-y-8">
            <p class="m-y-0"><i class="fa fa-calendar-day f-s-11"></i>&nbsp;<em id="tglSamplePrint">-</em> </p>
            <p class="m-y-0"><i class="fa fa-calendar-week f-s-11"></i>&nbsp;Deadline <em id="tglDeadlinePrint">-</em></p>
            <p class="m-t-8 badge bg-secondary d-inline-block"><i class="fa fa-user f-s-11"></i><em id="buyerPrint"></em></p>
          </div>
        </div>

        <hr>

        <h6 class="f-w-700">Quantity Cetak per Ukuran</h6>
        <div class="row m-t-16">
          <div class="col-sm-12">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <!-- <th>WARNA</th> -->
                    <th>UKURAN</th>
                    <th class="text-end">QTY</th>
                    <th class="text-end">QTY PRINT</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <!-- <td>
                      <select id="print_slc_warna" class="form-select">
                        <option value="" disabled>-- Pilih Warna --</option>
                      </select>
                    </td> -->
                    <td>
                      <select id="print_slc_ukuran" class="form-select">
                        <option value="" disabled selected>-- Pilih Ukuran --</option>
                        <?php foreach ($ukuran as $item) : ?>
                          <option value="<?= $item['key_ukuran'] ?>"><?= $item['kode_ukuran'] ?></option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td><input id="print_qty" type="text" class="form-control text-end" placeholder="Ketikkan qty" value="1"></td>
                    <td><input id="print_qtyp" type="text" class="form-control text-end" placeholder="Ketikkan qty print" value="1"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="m-s-5 btn btn-success" id="btn-cetak-print"><i class="fa fa-print"></i> Cetak</button>
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
            <div class="col-md-12 mb-3">
              <?php if (isset($_SESSION['message'])) { ?>
                <script type="text/javascript">
                  window.setTimeout(function() {
                    $(".alert").alert('close');
                  }, 3000);
                </script>
                <div class="alert alert-success">
                  <?php echo $_SESSION['message']; ?>
                </div>
              <?php } ?>
              <?php if (isset($_SESSION['err'])) { ?>
                <script type="text/javascript">
                  window.setTimeout(function() {
                    $(".alert").alert('close');
                  }, 5000);
                </script>
                <div class="alert alert-error">
                  <strong>Warning! </strong><?php echo $_SESSION['err']; ?>
                </div>
              <?php } ?>
            </div>
            <div class="col-sm-2 mb-3">
              <button type="button" class="btn btn-sm btn-success" id="btn-add"> <i class="fa fa-plus"></i> Tambah</button>
              <input type="hidden" id="role_id" value="<?=  !empty($role_id) ? $role_id : null ?>">
            </div>
            <div class="col-sm-2 mb-3">
              <select id="filter_buyer" name="filter_buyer" class="form-select select2" data-placeholder="-- Pilih Buyer --" required>
                <option value="all" selected>Semua Buyer</option>
                <?php foreach ($buyer as $item) : ?>
                  <option value="<?= $item['id'] ?>"><?= $item['nama'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-2 mb-3">
              <input type="text" id="from_date" name="from_date" class="form-control datepickerx" placeholder="FROM DATE">
            </div>
            <div class="col-sm-2 mb-3">
              <input type="text" id="to_date" name="to_date" class="form-control datepickerx" placeholder="TO DATE">
            </div>
            <div class="col-sm-2 mb-3">
              <button id="btn_excel" class="btn btn-success open_form" type="button"><i class="fa fa-file-excel"></i> Print</button>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <div class="input-group mb-3">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" class="form-control p-s-0" id="tb-search" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12">
              <div id="dt-list" class="custom-dt-layout"></div>
              <!-- <?php for ($i = 0; $i < 3; $i++) : ?>
                <div class="card shadow-sm">
                  <div class="card-header">
                    <div class="row">
                      <div class="col-sm-6">
                        <button type="button" class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modal-form-add-po"> <i class="fa fa-edit"></i> Edit</button>
                        <button type="button" class="btn btn-sm btn-danger"> <i class="fa fa-trash"></i> Hapus</button>
                      </div>
                      <div class="col-sm-6">
                        <div class="d-flex justify-content-end" style="column-gap: 8px;">
                          <div class="card m-y-8 cursor-pointer">
                            <div class="card-body p-y-6">
                              <div class="d-flex justify-content-start align-items-center">
                                <i class="fa fa-check-circle f-s-20 text-success m-e-6"></i>
                                <span class="f-w-700 text-success">PROGRAM</span>
                              </div>
                            </div>
                          </div>

                          <div class="card m-y-8 cursor-pointer">
                            <div class="card-body p-y-6">
                              <div class="d-flex justify-content-start align-items-center">
                                <i class="fa fa-check-circle f-s-20 text-success m-e-6"></i>
                                <span class="f-w-700 text-success">RAJUT</span>
                              </div>
                            </div>
                          </div>

                          <div class="card m-y-8 cursor-pointer">
                            <div class="card-body p-y-6">
                              <div class="d-flex justify-content-start align-items-center">
                                <i class="fa fa-dot-circle f-s-20 text-muted m-e-6"></i>
                                <span class="f-w-700 text-muted">KIRIM</span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-sm-3 text-center">
                        <h6 class="f-w-700 m-b-6">SPL/00<?= 3 - $i; ?>/10/2024</h6>
                        <h5 class="f-w-700 m-b-12">K-17 (CARDIGAN PITA)</h5>
                        <p class="m-y-0">7 Mei 2024</p>
                        <p class="m-y-0"><em>Deadline: 3 Juni 2024</em></p>
                        <p class="f-w-700 m-t-4">YUSUF</p>
                        <img class="m-t-10 w-90" src="assets/images/sales_order-dummy.png" alt="Foto Sales Order">
                      </div>
                      <div class="col-sm-9">
                        <div class="table-responsive">
                          <table class="table table-striped table-centered">
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
                                <th rowspan="2">QTY</th>
                                <th rowspan="2">AMOUNT</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>1</td>
                                <td class="text-nowrap">M38 - MINT - HITAM - OFF WHITE</td>
                                <td>44</td>
                                <td>90</td>
                                <td>66</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>200</td>
                                <td class="text-nowrap">Rp 200.000.000,00</td>
                              </tr>
                              <tr>
                                <td>2</td>
                                <td class="text-nowrap">M527 - OFF WHITE K - SMA - ROSE TUA</td>
                                <td>66</td>
                                <td>135</td>
                                <td>99</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>300</td>
                                <td class="text-nowrap">Rp 300.000.000,00</td>
                              </tr>
                              <tr>
                                <td>3</td>
                                <td class="text-nowrap">SAGE - OFF WHITE K - ROSE TUA - LILAC</td>
                                <td>33</td>
                                <td>68</td>
                                <td>50</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>151</td>
                                <td class="text-nowrap">Rp 151.000.000,00</td>
                              </tr>
                              <tr>
                                <td>4</td>
                                <td class="text-nowrap">HITAM - CREAM - ROSE TUA - LILAC</td>
                                <td>55</td>
                                <td>113</td>
                                <td>83</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>251</td>
                                <td class="text-nowrap">Rp 251.000.000,00</td>
                              </tr>
                            </tbody>
                            <tfoot>
                              <tr>
                                <th colspan="2" class="text-end">TOTAL</th>
                                <th>198</th>
                                <th>406</th>
                                <th>298</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>902</th>
                                <th class="text-nowrap">Rp 902.000.000,00</th>
                              </tr>
                            </tfoot>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endfor; ?> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script>
    const USER_ROLE = <?= json_encode(session()->get('role_id')) ?>;
</script>
<script src="script/app/transaction/sales_order/index.js"></script>
<?= $this->endSection('script'); ?>