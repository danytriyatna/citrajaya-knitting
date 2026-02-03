<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-wo" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Detail Work Order</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <input type="hidden" id="detail_id">
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-8">
            <b class="m-b-8 m-t-4" id="text-title-warna">-</b>
            <p class="m-t-4">QTY: <span id="text-qty-warna"></span></p>
          </div>
          <div class="col-sm-4">
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-4 col-form-label" for="loss_perc">Loss (%)</label>
              <div class="col-md-8">
                <input type="number" id="loss_perc" name="loss_perc" class="form-control" placeholder="Ketikkan nilai loss">
              </div>
            </div>
          </div>
        </div>

        <div class="table-striped" id="dt-warna">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="m-s-5 btn btn-success" id="btn-save-warna"> <i class="fa fa-save"></i> Simpan</button>
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
                        <option value="" disabled>-- Pilih Ukuran --</option>
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

<style>
  .tabulator-footer-contents{
    display: none !important; 
  }
</style>

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
          <input type="hidden" id="dataid" value="<?= $id; ?>" >
          <input type="hidden" id="data-details" value='<?= $detail; ?>' >
          <input type="hidden" id="data-ukuran" value='<?= $dtUkuran; ?>' >
          <input type="hidden" id="data-psaved" value='<?= $proses_saved; ?>' >
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-3 text-center">
              <h6 class="f-w-700 m-b-6"><?= $row->kode_walkorder; ?></h6>
              <!-- <h5 class="f-w-700 m-b-12"><?= $row->keterangan_style; ?></h5>
              <h7 class="f-w-700 m-b-12"><?= $row->keterangan; ?></h7> -->
              <h5 class="f-w-700 m-b-6"><?= $row->style; ?></h5>
              <h5 class="f-w-700 m-b-12"><?= $row->deskripsi; ?></h5>
              <p class="m-y-0"><?= fdate_eng_to_ind($row->tgl_transaksi); ?></p>
              <p class="m-y-0"><em>Deadline: <?= fdate_eng_to_ind($row->tgl_deadline); ?></em></p>
              <p class="m-y-0"><em>Deadline 2: <?= !empty($row->tgl_deadline_dua) ? fdate_eng_to_ind($row->tgl_deadline_dua) : '-'; ?></em></p>
              <p class="f-w-700 m-t-4"><?= $row->konsumen_nama; ?></p>
              <img class="m-t-10 w-90" src="<?= $row->file_gambar; ?>" alt="Foto Sample">
            </div>
            <div class="col-sm-9">
              <div>
                <div class="table-striped table-centered" id="dt-detail"></div>
              </div>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-sm-12">
              <div class="table-responsive">
                <div class="table-striped" id="dt-list-warna">
                 
                </div>
              </div>
              
              <br>

              
            </div>
          </div>

          <hr>

          <div class="row">
            <label class="control-label text-start text-md-end col-md-2 col-form-label" for="select_gudang">Gudang Produksi<span class="text-danger">*</span></label>
            <div class="col-md-5">
              <select id="select_gudang" value="<?= ($row->id_gudang) ? $row->id_gudang : ''?>" name="select_gudang" class="form-select select2" data-placeholder="-- Pilih Gudang --" required>
                <option value=""></option>
                <?php foreach ($gudang as $item) : ?>
                  <option value="<?= $item->id ?>"><?= $item->nama_gudang ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">
                Gudang Asal
              </div>
            </div>
          </div>
          <hr>
          
          <!-- <div class="container"> -->
            <div class="row">
              <div class="col-sm-12">
                <table class="table table-striped table-sm no-footer mb-0">
                    <thead>
                      <tr>
                        <th>PRODUCTION PROCESS</th>
                        <th>HARGA PROCESS</th>
                      </tr>
                    </thead>
                    <tbody>
                         <?php foreach ($proses as $r) { ?>
                          <tr>
                            <td>
                              <div class="form-check-inline">
                                <input class="form-check-input m-e-4" type="checkbox" name="jenis_proses" id="proses_<?= $r->id ?>" value="<?= $r->id ?>">
                                <label class="form-check-label mb-0" for="proses_<?= $r->id ?>"><?= $r->nama ?></label>
                              </div>
                            </td>
                            <td>
                              <input type="number" class="form-control" name="harga-proses" id="harga-proses-<?= $r->id ?>" value="<?= $r->harga ?>">
                            </td>
                          </tr>
                          <?php } ?>
                    </tbody>
                </table>
              </div>
            </div>
          <!-- </div> -->

          <div class="row" style="margin-top:3%;">
            <div class="col-sm-10">
              <a href="trans/work-order" class="btn btn-default m-e-5">
                <span class="fa fa-arrow-left"></span> Kembali
              </a>
              <button type="button" id="btn-save" class='btn btn-success'>
                  <span class="fa fa-save"></span> Simpan
                </button>
              <?php if($status == 1) { ?>
                
                <button type="button" id="btn-send" class='btn btn-info'>
                  <span class="fa fa-paper-plane"></span> Approval
                </button>
              <?php } ?>
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
  const dataRow =<?= json_encode($row) ?>;
</script>
<script src="script/app/transaction/walkorder/form.js"></script>
<?= $this->endSection('script'); ?>