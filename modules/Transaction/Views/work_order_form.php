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
      <?php if($status == 1) { ?>
      <div class="modal-footer">
        <button type="button" class="m-s-5 btn btn-success" id="btn-save-warna"> <i class="fa fa-save"></i> Simpan</button>
      </div>
      <?php } ?>
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
              <h5 class="f-w-700 m-b-12"><?= $row->keterangan_style; ?></h5>
              <h7 class="f-w-700 m-b-12"><?= $row->keterangan; ?></h7>
              <p class="m-y-0"><?= fdate_eng_to_ind($row->tgl_transaksi); ?></p>
              <p class="m-y-0"><em>Deadline: <?= fdate_eng_to_ind($row->tgl_deadline); ?></em></p>
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
                          </tr>
                          <?php } ?>
                    </tbody>
                </table>
              </div>
            </div>
          <!-- </div> -->

          <div class="row">
            <div class="col-sm-12">
              <div class="table-responsive">
                <div class="table-striped" id="dt-list-warna">
                 
                </div>
              </div>
              
              <br>

              <div class="row">
                <div class="col-sm-10">
                  <a href="trans/work-order" class="btn btn-default m-e-5">
                    <span class="fa fa-arrow-left"></span> Kembali
                  </a>
                  <?php if($status == 1) { ?>
                    <button type="button" id="btn-save" class='btn btn-success'>
                      <span class="fa fa-save"></span> Simpan
                    </button>
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
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/transaction/walkorder/form.js"></script>
<?= $this->endSection('script'); ?>