<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<?php if (!empty($can_view_laba)) : ?>
<!-- MODAL DETAIL RINCIAN LABA KOTOR -->
<div id="modal-laba-detail" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-light py-2 px-3">
        <h5 class="modal-title f-w-700 text-dark mb-0"><i class="fa fa-chart-line text-primary me-2"></i>Rincian Analisis Laba Kotor Produksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- HEADER INFO -->
        <div class="row align-items-center mb-3">
          <div class="col-md-7">
            <h5 class="f-w-800 mb-1 text-dark" id="mdl-lbl-prod">PRD...</h5>
            <div class="d-flex flex-wrap align-items-center gap-1 f-s-13">
              <span id="mdl-lbl-wo" class="badge bg-secondary text-white px-2 py-1 fw-bold">WO</span>
              <span id="mdl-lbl-so" class="badge bg-primary text-white px-2 py-1 fw-bold">SO</span>
              <span id="mdl-lbl-konsumen" class="badge bg-dark text-white px-2 py-1 fw-bold">Konsumen</span>
              <span id="mdl-lbl-qty-so" class="badge px-2 py-1 fw-bold text-white" style="background-color: #0284c7;">Qty SO: 0 Pcs</span>
              <span id="mdl-lbl-style" class="fw-bold text-dark ms-1">Style</span>
            </div>
          </div>
          <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <span class="badge px-3 py-2 fs-6 fw-bold" id="mdl-badge-laba">Laba Kotor: Rp 0</span>
          </div>
        </div>

        <!-- 5 METRICS STRIP DENGAN WARNA TEGAS -->
        <div class="row g-2 mb-4 text-center">
          <div class="col">
            <div class="p-2 rounded bg-white" style="border: 2px solid #1d4ed8; box-shadow: 0 2px 4px rgba(29, 78, 216, 0.12);">
              <div class="f-s-11 text-uppercase fw-bold" style="color: #1e40af;">Nilai SO</div>
              <div class="f-w-800 f-s-15" style="color: #1d4ed8;" id="mdl-val-so">Rp 0</div>
              <small class="f-s-11 fw-bold text-muted" id="mdl-val-qty-so">Target: 0 Pcs</small>
            </div>
          </div>
          <div class="col">
            <div class="p-2 rounded bg-white" style="border: 2px solid #0284c7; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.12);">
              <div class="f-s-11 text-uppercase fw-bold" style="color: #0369a1;">Pendapatan Masuk</div>
              <div class="f-w-800 f-s-15" style="color: #0284c7;" id="mdl-val-pendapatan">Rp 0</div>
              <small class="f-s-11 text-muted">DP &amp; Pelunasan</small>
            </div>
          </div>
          <div class="col">
            <div class="p-2 rounded bg-white" style="border: 2px solid #d97706; box-shadow: 0 2px 4px rgba(217, 119, 6, 0.12);">
              <div class="f-s-11 text-uppercase fw-bold" style="color: #b45309;">Bahan Baku</div>
              <div class="f-w-800 f-s-15" style="color: #d97706;" id="mdl-val-bahan">Rp 0</div>
              <small class="f-s-11 text-muted">BTK Pemakaian</small>
            </div>
          </div>
          <div class="col">
            <div class="p-2 rounded bg-white" style="border: 2px solid #dc2626; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.12);">
              <div class="f-s-11 text-uppercase fw-bold" style="color: #b91c1c;">Tenaga Kerja</div>
              <div class="f-w-800 f-s-15" style="color: #dc2626;" id="mdl-val-tk">Rp 0</div>
              <small class="f-s-11 text-muted">Upah Operator</small>
            </div>
          </div>
          <div class="col">
            <div class="p-2 rounded bg-white" id="mdl-card-laba" style="border: 2px solid #16a34a; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.12);">
              <div class="f-s-11 text-uppercase fw-bold" id="mdl-lbl-laba-title" style="color: #15803d;">Laba Kotor</div>
              <div class="f-w-800 f-s-15" id="mdl-val-laba" style="color: #16a34a;">Rp 0</div>
              <small class="f-s-11 fw-bold text-muted" id="mdl-val-margin">Margin: 0%</small>
            </div>
          </div>
        </div>

        <!-- TABEL REKAPITULASI PEMAKAIAN BAHAN BAKU (WARNA TEGAS) -->
        <div class="card mb-4 shadow-sm" style="border: 2px solid #d97706 !important;">
          <div class="card-header py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background-color: #fffbeb; border-bottom: 1px solid #fde68a;">
            <h6 class="m-0 fw-bold" style="color: #b45309;">
              <i class="fa fa-boxes me-2"></i>REKAPITULASI PEMAKAIAN BAHAN BAKU
            </h6>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <span class="badge px-2 py-1 fw-bold text-white shadow-sm" style="background-color: #1e40af;" id="mdl-rekap-badge-kebutuhan">Kebutuhan: 0 Kg</span>
              <span class="badge px-2 py-1 fw-bold text-white shadow-sm" style="background-color: #d97706;" id="mdl-rekap-badge-pakai">Pemakaian: 0 Kg</span>
              <span class="badge px-2 py-1 fw-bold text-white shadow-sm" style="background-color: #15803d;" id="mdl-rekap-badge-nilai">Nilai Pemakaian: Rp 0</span>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped align-middle mb-0" id="tbl-mdl-rekap-bahan">
                <thead style="background-color: #1e293b !important; color: #ffffff !important;">
                  <tr class="text-nowrap" style="white-space: nowrap !important;">
                    <th class="text-center text-nowrap" style="width: 4%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">No</th>
                    <th class="text-nowrap" style="background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Warna / Benang</th>
                    <th class="text-end text-nowrap" style="width: 12%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Kebutuhan (Kg)</th>
                    <th class="text-end text-nowrap" style="width: 12%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Pengiriman (Kg)</th>
                    <th class="text-end text-nowrap" style="width: 12%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Pengembalian (Kg)</th>
                    <th class="text-end text-nowrap" style="width: 12%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Pemakaian (Kg)</th>
                    <th class="text-end text-nowrap" style="width: 12%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Sisa (Kg)</th>
                    <th class="text-end text-nowrap" style="width: 16%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Nilai Pemakaian</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="8" class="text-center text-muted py-2">Memuat data rekapitulasi bahan baku...</td></tr>
                </tbody>
                <tfoot class="table-light fw-bold" style="border-top: 2px solid #1e293b;">
                  <tr>
                    <td colspan="2" class="text-end text-dark">TOTAL:</td>
                    <td class="text-end text-primary" id="mdl-tot-rekap-kebutuhan">0.00</td>
                    <td class="text-end text-info" id="mdl-tot-rekap-trf">0.00</td>
                    <td class="text-end text-secondary" id="mdl-tot-rekap-kembali">0.00</td>
                    <td class="text-end text-warning" id="mdl-tot-rekap-pakai">0.00</td>
                    <td class="text-end" id="mdl-tot-rekap-sisa">0.00</td>
                    <td class="text-end fw-bold" style="color: #15803d;" id="mdl-tot-rekap-nilai">Rp 0</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- BREAKDOWN TABS -->
        <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
          <li class="nav-item">
            <a class="nav-link active fw-bold text-dark" data-bs-toggle="tab" href="#tab-detail-tk" role="tab">
              <i class="fa fa-users me-1 text-danger"></i> Rincian Tenaga Kerja Operator
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link fw-bold text-dark" data-bs-toggle="tab" href="#tab-detail-bahan" role="tab">
              <i class="fa fa-boxes me-1 text-warning"></i> Rincian Pemakaian Bahan Baku
            </a>
          </li>
        </ul>

        <div class="tab-content">
          <!-- TAB TENAGA KERJA -->
          <div class="tab-pane active" id="tab-detail-tk" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped align-middle mb-0" id="tbl-mdl-tk">
                <thead style="background-color: #1e293b !important; color: #ffffff !important;">
                  <tr class="text-nowrap" style="white-space: nowrap !important;">
                    <th class="text-center text-nowrap" style="width: 4%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">No</th>
                    <th class="text-nowrap" style="background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Tahapan Proses</th>
                    <th class="text-end text-nowrap" style="width: 10%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Qty SO</th>
                    <th class="text-end text-nowrap" style="width: 10%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Qty Prod</th>
                    <th class="text-end text-nowrap" style="width: 11%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Qty Perbaikan</th>
                    <th class="text-end text-nowrap" style="width: 10%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Total Qty</th>
                    <th class="text-end text-nowrap" style="width: 17%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Tarif / Pcs</th>
                    <th class="text-end text-nowrap" style="width: 20%; background-color: #1e293b !important; color: #ffffff !important; white-space: nowrap !important;">Subtotal Ongkos</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="8" class="text-center text-muted">Memuat data...</td></tr>
                </tbody>
                <tfoot class="table-light fw-bold" style="border-top: 2px solid #1e293b;">
                  <tr class="text-nowrap" style="white-space: nowrap !important;">
                    <td colspan="2" class="text-end text-dark text-nowrap" style="white-space: nowrap !important;">TOTAL:</td>
                    <td class="text-end text-primary" id="mdl-total-qty-so">0</td>
                    <td class="text-end text-success" id="mdl-total-qty-prod">0</td>
                    <td class="text-end text-warning" id="mdl-total-qty-fix">0</td>
                    <td class="text-end text-dark" id="mdl-total-qty-all">0</td>
                    <td class="text-end text-dark text-nowrap" style="white-space: nowrap !important;">TOTAL BIAYA:</td>
                    <td class="text-end text-danger fs-6 text-nowrap" id="mdl-total-tk" style="white-space: nowrap !important;">Rp 0</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- TAB BAHAN BAKU -->
          <div class="tab-pane" id="tab-detail-bahan" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped align-middle mb-0" id="tbl-mdl-bahan">
                <thead style="background-color: #1e293b !important; color: #ffffff !important;">
                  <tr>
                    <th class="text-center" style="width: 5%; background-color: #1e293b !important; color: #ffffff !important;">No</th>
                    <th style="background-color: #1e293b !important; color: #ffffff !important;">Kode / Nama Barang</th>
                    <th style="background-color: #1e293b !important; color: #ffffff !important;">Warna</th>
                    <th class="text-end" style="width: 15%; background-color: #1e293b !important; color: #ffffff !important;">Qty Pakai</th>
                    <th class="text-end" style="width: 20%; background-color: #1e293b !important; color: #ffffff !important;">Harga Satuan</th>
                    <th class="text-end" style="width: 22%; background-color: #1e293b !important; color: #ffffff !important;">Subtotal Bahan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="6" class="text-center text-muted">Memuat data...</td></tr>
                </tbody>
                <tfoot class="table-light fw-bold" style="border-top: 2px solid #1e293b;">
                  <tr>
                    <td colspan="3" class="text-end text-dark">TOTAL:</td>
                    <td class="text-end text-dark" id="mdl-total-bahan-qty">0</td>
                    <td class="text-end text-dark">TOTAL BIAYA:</td>
                    <td class="text-end text-warning fs-6" id="mdl-total-bahan">Rp 0</td>
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
<?php endif; ?>
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
          <div class="row align-items-center">
            <div class="col-md-3 col-sm-6">
              <div class="form-group m-b-0 d-flex align-items-center">
                <label class="control-label text-start text-md-end m-e-8" for="filter_status">Status</label>
                <select id="filter_status" name="filter_status" class="form-control custom-select select2">
                  <option value="0">All</option>
                  <option value="1">Sample</option>
                  <option value="2">Sales Order</option>
                </select>
              </div>
            </div>
            <?php if (!empty($can_view_laba)) : ?>
            <div class="col-md-3 col-sm-6">
              <div class="form-group m-b-0 d-flex align-items-center">
                <label class="control-label text-start text-md-end m-e-8" for="filter_tahun">Tahun</label>
                <select id="filter_tahun" name="filter_tahun" class="form-control custom-select select2">
                  <?php 
                    $curYear = (int)date('Y');
                    for ($y = $curYear + 1; $y >= 2023; $y--) {
                      $sel = ($y == $curYear) ? 'selected' : '';
                      echo "<option value='{$y}' {$sel}>{$y}</option>";
                    }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-md-4 offset-md-2 col-sm-12 mt-2 mt-md-0">
            <?php else : ?>
            <div class="col-md-4 offset-md-5 col-sm-12 mt-2 mt-md-0">
            <?php endif; ?>
              <div class="form-group mb-0">
                <div class="input-group">
                  <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                  <input type="text" id="tb-search" class="form-control p-s-0" placeholder="Pencarian" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>

          <?php if (!empty($can_view_laba)) : ?>
          <!-- GLOBAL SUMMARY LABA KOTOR DENGAN WARNA TEGAS -->
          <div class="row g-2 mb-3 mt-1" id="global-laba-summary">
            <div class="col-xl col-md-4 col-sm-6">
              <div class="card mb-0 p-2 text-center h-100 bg-white" style="border: 2px solid #1d4ed8 !important; box-shadow: 0 2px 4px rgba(29, 78, 216, 0.12);">
                <span class="f-s-11 f-w-700 text-uppercase" style="color: #1e40af;">Total Nilai SO</span>
                <h5 class="f-w-800 m-b-0 m-t-4" style="color: #1d4ed8; font-size: 1.25rem;" id="sum-glob-so">Rp 0</h5>
                <small class="fw-bold f-s-10" style="color: #64748b;">Target Kontrak</small>
              </div>
            </div>
            <div class="col-xl col-md-4 col-sm-6">
              <div class="card mb-0 p-2 text-center h-100 bg-white" style="border: 2px solid #0284c7 !important; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.12);">
                <span class="f-s-11 f-w-700 text-uppercase" style="color: #0369a1;">Total Pendapatan</span>
                <h5 class="f-w-800 m-b-0 m-t-4" style="color: #0284c7; font-size: 1.25rem;" id="sum-glob-pendapatan">Rp 0</h5>
                <small class="fw-bold f-s-10" style="color: #64748b;">DP &amp; Pelunasan Masuk</small>
              </div>
            </div>
            <div class="col-xl col-md-4 col-sm-6">
              <div class="card mb-0 p-2 text-center h-100 bg-white" style="border: 2px solid #d97706 !important; box-shadow: 0 2px 4px rgba(217, 119, 6, 0.12);">
                <span class="f-s-11 f-w-700 text-uppercase" style="color: #b45309;">Total Bahan Baku</span>
                <h5 class="f-w-800 m-b-0 m-t-4" style="color: #d97706; font-size: 1.25rem;" id="sum-glob-bahan">Rp 0</h5>
                <small class="fw-bold f-s-10" style="color: #64748b;">BTK Pemakaian</small>
              </div>
            </div>
            <div class="col-xl col-md-6 col-sm-6">
              <div class="card mb-0 p-2 text-center h-100 bg-white" style="border: 2px solid #dc2626 !important; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.12);">
                <span class="f-s-11 f-w-700 text-uppercase" style="color: #b91c1c;">Total Tenaga Kerja</span>
                <h5 class="f-w-800 m-b-0 m-t-4" style="color: #dc2626; font-size: 1.25rem;" id="sum-glob-tk">Rp 0</h5>
                <small class="fw-bold f-s-10" style="color: #64748b;">Upah Operator Produksi</small>
              </div>
            </div>
            <div class="col-xl col-md-6 col-sm-12">
              <div class="card mb-0 p-2 text-center h-100 bg-white" id="card-glob-laba" style="border: 2px solid #16a34a !important; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.12);">
                <span class="f-s-11 f-w-700 text-uppercase" id="lbl-glob-laba-title" style="color: #15803d;">Total Laba Kotor</span>
                <h5 class="f-w-800 m-b-0 m-t-4" style="color: #16a34a; font-size: 1.25rem;" id="sum-glob-laba">Rp 0</h5>
                <small class="badge bg-success text-white px-2 py-1 f-s-10 mt-1 fw-bold" id="sum-glob-margin">Margin: 0%</small>
              </div>
            </div>
          </div>

          <hr>
          <?php else : ?>
          <hr class="mt-3 mb-3">
          <?php endif; ?>
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