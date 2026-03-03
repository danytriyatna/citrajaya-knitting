<?= $this->extend('template'); ?>

<?= $this->section('content'); ?>

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
            <p class="m-y-0"><i class="fa fa-calendar-week f-s-11"></i>&nbsp;Deadline: <em id="tglDeadlinePrint">-</em></p>
            <p class="m-y-0"><i class="fa fa-calendar-week f-s-11"></i>&nbsp;Deadline 2: <em id="tglDeadlinePrintDua">-</em></p>
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
                    <th>TIPE PRINT</th>
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
                    <td>
                      <select id="print_type" class="form-select">
                        <option value="" disabled>-- Pilih Tipe --</option>
                        <option value="1" selected>Produksi</option>
                        <option value="2">Perbaikan</option>
                      </select>
                    </td>
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
            <div class="col-sm-3">
              <div hidden class="form-group m-b-0 d-flex align-items-center">
                <label class="control-label text-start text-md-end m-e-8" for="filter_status">Transaksi</label>
                <select id="filter_status" name="filter_status" class="form-control custom-select select2">
                  <option value="0">All</option>
                  <option value="1">Sample</option>
                  <option value="2">Sales Order</option>
                </select>
                <input type="hidden" id="role_id" value="<?=  !empty($role_id) ? $role_id : null ?>">
              </div>
            </div>
            <div class="col-sm-4 offset-md-5">
              <div class="form-group">
                <div class="input-group mb-3">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" id="tb-search" class="form-control p-s-0" id="tb-search" placeholder="Pencarian" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="table-responsive">
            <div class="table-striped" id="dt-list"> </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<script src="script/app/transaction/walkorder/index.js"></script>
<?= $this->endSection('script'); ?>