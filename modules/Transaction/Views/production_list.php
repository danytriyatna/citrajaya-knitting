<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<!-- MODAL DETAIL RINCIAN LABA KOTOR -->
<div id="modal-laba-detail" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title f-w-700"><i class="fa fa-chart-line text-primary me-2"></i>Rincian Analisis Laba Kotor Produksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- HEADER INFO -->
        <div class="row align-items-center mb-3">
          <div class="col-md-7">
            <h5 class="f-w-700 mb-1 text-dark" id="mdl-lbl-prod">PRD...</h5>
            <div class="text-muted f-s-13">
              <span id="mdl-lbl-wo" class="badge bg-secondary me-1">WO</span>
              <span id="mdl-lbl-konsumen" class="badge bg-info text-dark me-1">Konsumen</span>
              <span id="mdl-lbl-style" class="fw-semibold">Style</span>
            </div>
          </div>
          <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <span class="badge px-3 py-2 f-s-13" id="mdl-badge-laba">Laba Kotor: Rp 0</span>
          </div>
        </div>

        <!-- 5 METRICS STRIP -->
        <div class="row g-2 mb-4 text-center">
          <div class="col">
            <div class="p-2 border rounded bg-light">
              <div class="f-s-11 text-muted text-uppercase fw-bold">Nilai SO</div>
              <div class="f-w-700 text-info f-s-14" id="mdl-val-so">Rp 0</div>
            </div>
          </div>
          <div class="col">
            <div class="p-2 border rounded bg-light">
              <div class="f-s-11 text-muted text-uppercase fw-bold">Pendapatan Masuk</div>
              <div class="f-w-700 text-primary f-s-14" id="mdl-val-pendapatan">Rp 0</div>
            </div>
          </div>
          <div class="col">
            <div class="p-2 border rounded bg-light">
              <div class="f-s-11 text-muted text-uppercase fw-bold">Bahan Baku</div>
              <div class="f-w-700 text-warning f-s-14" id="mdl-val-bahan">Rp 0</div>
            </div>
          </div>
          <div class="col">
            <div class="p-2 border rounded bg-light">
              <div class="f-s-11 text-muted text-uppercase fw-bold">Tenaga Kerja</div>
              <div class="f-w-700 text-danger f-s-14" id="mdl-val-tk">Rp 0</div>
            </div>
          </div>
          <div class="col">
            <div class="p-2 border rounded bg-light">
              <div class="f-s-11 text-muted text-uppercase fw-bold">Laba Kotor</div>
              <div class="f-w-700 f-s-14" id="mdl-val-laba">Rp 0</div>
              <small class="f-s-10 text-muted" id="mdl-val-margin">Margin: 0%</small>
            </div>
          </div>
        </div>

        <!-- BREAKDOWN TABS -->
        <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
          <li class="nav-item">
            <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#tab-detail-tk" role="tab">
              <i class="fa fa-users me-1 text-danger"></i> Rincian Tenaga Kerja Operator
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#tab-detail-bahan" role="tab">
              <i class="fa fa-boxes me-1 text-warning"></i> Rincian Pemakaian Bahan Baku
            </a>
          </li>
        </ul>

        <div class="tab-content">
          <!-- TAB TENAGA KERJA -->
          <div class="tab-pane active" id="tab-detail-tk" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped align-middle mb-0" id="tbl-mdl-tk">
                <thead class="table-light">
                  <tr>
                    <th style="width: 5%">No</th>
                    <th>Tahapan Proses</th>
                    <th class="text-end" style="width: 20%">Total Qty</th>
                    <th class="text-end" style="width: 25%">Rata-rata Tarif / Pcs</th>
                    <th class="text-end" style="width: 25%">Subtotal Ongkos</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>
                </tbody>
                <tfoot class="table-light fw-bold">
                  <tr>
                    <td colspan="4" class="text-end">TOTAL BIAYA TENAGA KERJA:</td>
                    <td class="text-end text-danger" id="mdl-total-tk">Rp 0</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- TAB BAHAN BAKU -->
          <div class="tab-pane" id="tab-detail-bahan" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped align-middle mb-0" id="tbl-mdl-bahan">
                <thead class="table-light">
                  <tr>
                    <th style="width: 5%">No</th>
                    <th>Kode / Nama Barang</th>
                    <th>Warna</th>
                    <th class="text-end" style="width: 15%">Qty Pakai</th>
                    <th class="text-end" style="width: 20%">Harga Satuan</th>
                    <th class="text-end" style="width: 20%">Subtotal Bahan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="6" class="text-center text-muted">Memuat data...</td></tr>
                </tbody>
                <tfoot class="table-light fw-bold">
                  <tr>
                    <td colspan="5" class="text-end">TOTAL BIAYA BAHAN BAKU:</td>
                    <td class="text-end text-warning" id="mdl-total-bahan">Rp 0</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
            <div class="col-sm-3">
              <div class="form-group m-b-0 d-flex align-items-center">
                <label class="control-label text-start text-md-end m-e-8" for="filter_status">Status</label>
                <select id="filter_status" name="filter_status" class="form-control custom-select select2">
                  <option value="0">All</option>
                  <option value="1">Sample</option>
                  <option value="2">Sales Order</option>
                </select>
              </div>
            </div>
            <div class="col-sm-4 offset-md-5">
              <div class="form-group">
                <div class="input-group mb-3">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" id="tb-search" class="form-control p-s-0" placeholder="Pencarian" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>

          <!-- GLOBAL SUMMARY LABA KOTOR -->
          <div class="row g-2 mb-3 mt-1" id="global-laba-summary">
            <div class="col-xl col-md-4 col-sm-6">
              <div class="card bg-light border mb-0 p-2 text-center h-100 shadow-none" style="background-color: #f0f7ff !important; border-color: #b8daff !important;">
                <span class="text-muted f-s-11 f-w-600 text-uppercase">Total Nilai SO</span>
                <h5 class="f-w-700 text-primary m-b-0 m-t-4" id="sum-glob-so">Rp 0</h5>
                <small class="text-muted f-s-10">Target Kontrak</small>
              </div>
            </div>
            <div class="col-xl col-md-4 col-sm-6">
              <div class="card bg-light border mb-0 p-2 text-center h-100 shadow-none" style="background-color: #e8f4fd !important; border-color: #90caf9 !important;">
                <span class="text-muted f-s-11 f-w-600 text-uppercase">Total Pendapatan</span>
                <h5 class="f-w-700 text-info m-b-0 m-t-4" id="sum-glob-pendapatan">Rp 0</h5>
                <small class="text-muted f-s-10">DP & Pelunasan Masuk</small>
              </div>
            </div>
            <div class="col-xl col-md-4 col-sm-6">
              <div class="card bg-light border mb-0 p-2 text-center h-100 shadow-none" style="background-color: #fff9e6 !important; border-color: #ffe082 !important;">
                <span class="text-muted f-s-11 f-w-600 text-uppercase">Total Bahan Baku</span>
                <h5 class="f-w-700 text-warning m-b-0 m-t-4" id="sum-glob-bahan">Rp 0</h5>
                <small class="text-muted f-s-10">BTK Pemakaian</small>
              </div>
            </div>
            <div class="col-xl col-md-6 col-sm-6">
              <div class="card bg-light border mb-0 p-2 text-center h-100 shadow-none" style="background-color: #fdeeee !important; border-color: #ef9a9a !important;">
                <span class="text-muted f-s-11 f-w-600 text-uppercase">Total Tenaga Kerja</span>
                <h5 class="f-w-700 text-danger m-b-0 m-t-4" id="sum-glob-tk">Rp 0</h5>
                <small class="text-muted f-s-10">Upah Operator Produksi</small>
              </div>
            </div>
            <div class="col-xl col-md-6 col-sm-12">
              <div class="card bg-light border mb-0 p-2 text-center h-100 shadow-none" id="card-glob-laba" style="background-color: #e8f5e9 !important; border-color: #a5d6a7 !important;">
                <span class="text-muted f-s-11 f-w-600 text-uppercase">Total Laba Kotor</span>
                <h5 class="f-w-700 text-success m-b-0 m-t-4" id="sum-glob-laba">Rp 0</h5>
                <small class="text-muted f-s-10" id="sum-glob-margin">Margin: 0%</small>
              </div>
            </div>
          </div>

          <hr>
          <div class="table-responsive">
            <div class="table-responsive">
              <div class="table-striped" id="dt-list"> </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/transaction/production/index.js"></script>
<?= $this->endSection('script'); ?>