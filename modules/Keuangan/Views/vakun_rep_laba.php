<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>
<style>
  .kpi-card {
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 12px;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    transition: transform 0.15s ease-in-out;
  }
  .kpi-card:hover {
    transform: translateY(-2px);
  }
  .kpi-title {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    opacity: 0.9;
    font-weight: 600;
  }
  .kpi-val {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 0;
    text-align: right;
  }
  .bg-penjualan { background: linear-gradient(135deg, #28a745, #218838); }
  .bg-pembelian { background: linear-gradient(135deg, #fd7e14, #e8590c); }
  .bg-labakotor { background: linear-gradient(135deg, #17a2b8, #117a8b); }
  .bg-operasional { background: linear-gradient(135deg, #dc3545, #c82333); }
  .bg-lababersih { background: linear-gradient(135deg, #6610f2, #520dc2); }

  .nav-tabs .nav-link {
    font-weight: 600;
    color: #495057;
    border-radius: 6px 6px 0 0;
    padding: 10px 18px;
  }
  .nav-tabs .nav-link.active {
    color: #007bff;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    border-bottom: 3px solid #007bff;
  }
  .table-ikhtisar th, .table-ikhtisar td {
    padding: 10px 14px;
    vertical-align: middle;
  }
  .row-header-sec {
    background-color: #f1f4f8;
    font-weight: 700;
    color: #2c3e50;
    font-size: 14px;
  }
  .row-subtotal {
    background-color: #e9ecef;
    font-weight: 700;
  }
  .row-highlight-kotor {
    background-color: #d1ecf1 !important;
    font-weight: 700;
    color: #0c5460;
    font-size: 14px;
  }
  .row-highlight-bersih {
    background-color: #d4edda !important;
    font-weight: 800;
    color: #155724;
    font-size: 15px;
  }
  .row-highlight-bersih-rugi {
    background-color: #f8d7da !important;
    font-weight: 800;
    color: #721c24;
    font-size: 15px;
  }
</style>

<div class="container-fluid">
  <!-- Breadcrumb -->
  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h4 class="text-themecolor"><?= $titlehead; ?></h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
      <div class="d-flex justify-content-end align-items-center">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Keuangan</a></li>
          <li class="breadcrumb-item active"><?= $titlehead; ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          
          <!-- Filter Controls -->
          <div class="row align-items-end mb-3">
            <div class="col-md-3">
              <label class="font-weight-bold text-primary mb-1">Dari Periode:</label>
              <div class="input-group">
                <select name="bulan_from" id="bulan_from" class="form-control">
                  <?php
                  $months = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                  ];
                  foreach ($months as $v => $label): ?>
                    <option value="<?= $v ?>" <?= ($bulan == $v) ? 'selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
                <?= (isset($slc_tahun) && !empty($slc_tahun)) ? form_dropdown($slc_tahun) : ""; ?>
              </div>
            </div>

            <div class="col-md-3">
              <label class="font-weight-bold text-danger mb-1">Sampai Periode:</label>
              <div class="input-group">
                <select name="bulan_to" id="bulan_to" class="form-control">
                  <?php foreach ($months as $v => $label): ?>
                    <option value="<?= $v ?>" <?= ($bulan == $v) ? 'selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="tahun_to" id="tahun_to" class="form-control">
                  <?php if (isset($slc_tahun['options'])) {
                    foreach ($slc_tahun['options'] as $y) { ?>
                      <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                  <?php } } ?>
                </select>
              </div>
            </div>

            <div class="col-md-6 d-flex justify-content-end align-items-end mt-2 mt-md-0">
              <button id="btn_cari" class="btn btn-primary me-2 px-3 shadow-sm" type="button">
                <i class="fa fa-search"></i> Cari
              </button>
              <button id="btn_excel" class="btn btn-success px-3 shadow-sm" type="button">
                <i class="fa fa-file-excel"></i> Export Excel
              </button>
            </div>
          </div>

          <!-- KPI Summary Cards -->
          <div class="row mt-3">
            <div class="col-md-2 col-sm-6">
              <div class="kpi-card bg-penjualan">
                <div class="kpi-title">Total Penjualan</div>
                <div class="kpi-val" id="kpi_penjualan">Rp 0</div>
              </div>
            </div>
            <div class="col-md-2 col-sm-6">
              <div class="kpi-card bg-pembelian">
                <div class="kpi-title" id="kpi_pembelian_title">Beban Pokok / Pembelian</div>
                <div class="kpi-val" id="kpi_pembelian">Rp 0</div>
              </div>
            </div>
            <div class="col-md-3 col-sm-6">
              <div class="kpi-card bg-labakotor">
                <div class="kpi-title">Laba Kotor (Penjualan - Pemakaian)</div>
                <div class="kpi-val" id="kpi_labakotor">Rp 0</div>
              </div>
            </div>
            <div class="col-md-2 col-sm-6">
              <div class="kpi-card bg-operasional">
                <div class="kpi-title">Beban Operasional</div>
                <div class="kpi-val" id="kpi_operasional">Rp 0</div>
              </div>
            </div>
            <div class="col-md-3 col-sm-6">
              <div class="kpi-card bg-lababersih">
                <div class="kpi-title">Laba Bersih (Kotor - Beban)</div>
                <div class="kpi-val" id="kpi_lababersih">Rp 0</div>
              </div>
            </div>
          </div>

          <hr class="my-3">

          <!-- Nav Tabs -->
          <ul class="nav nav-tabs mb-3" id="labaTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-ikhtisar-btn" data-bs-toggle="tab" data-bs-target="#tab-ikhtisar" type="button" role="tab">
                <i class="fa fa-file-text-o me-1"></i> Ikhtisar Laba Rugi
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-penjualan-btn" data-bs-toggle="tab" data-bs-target="#tab-penjualan" type="button" role="tab">
                <i class="fa fa-arrow-circle-down text-success me-1"></i> Rincian Penjualan <span class="badge bg-success ms-1" id="badge-cnt-penjualan">0</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-pembelian-btn" data-bs-toggle="tab" data-bs-target="#tab-pembelian" type="button" role="tab">
                <i class="fa fa-cubes text-warning me-1"></i> Rincian Pemakaian Bahan <span class="badge bg-warning text-dark ms-1" id="badge-cnt-pembelian">0</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-operasional-btn" data-bs-toggle="tab" data-bs-target="#tab-operasional" type="button" role="tab">
                <i class="fa fa-minus-circle text-danger me-1"></i> Rincian Beban Operasional <span class="badge bg-danger ms-1" id="badge-cnt-operasional">0</span>
              </button>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content" id="labaTabsContent">
            
            <!-- TAB 1: IKHTISAR LABA RUGI -->
            <div class="tab-pane fade show active" id="tab-ikhtisar" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-bordered table-ikhtisar mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th style="width: 70%;">DESKRIPSI / AKUN</th>
                      <th class="text-end" style="width: 30%;">JUMLAH (RP)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- I. PENDAPATAN -->
                    <tr class="row-header-sec">
                      <td colspan="2">I. PENDAPATAN / PENJUALAN</td>
                    </tr>
                    <tr>
                      <td class="ps-4">- Sales Order (DP Masuk)</td>
                      <td class="text-end font-weight-bold" id="ikhtisar_so">Rp 0</td>
                    </tr>
                    <tr>
                      <td class="ps-4">- Customer Receipt (Pelunasan Penjualan)</td>
                      <td class="text-end font-weight-bold" id="ikhtisar_cr">Rp 0</td>
                    </tr>
                    <tr class="row-subtotal">
                      <td class="text-end">TOTAL PENJUALAN</td>
                      <td class="text-end text-success font-weight-bold" id="ikhtisar_total_penjualan">Rp 0</td>
                    </tr>

                    <!-- II. BEBAN POKOK / PEMAKAIAN BAHAN BAKU -->
                    <tr class="row-header-sec">
                      <td colspan="2">II. BEBAN POKOK PRODUKSI / PEMAKAIAN BAHAN BAKU</td>
                    </tr>
                    <tr>
                      <td class="ps-4" id="ikhtisar_pb_title">- Beban Pokok Produksi / Pembelian</td>
                      <td class="text-end font-weight-bold" id="ikhtisar_pb">Rp 0</td>
                    </tr>
                    <tr class="row-subtotal">
                      <td class="text-end" id="ikhtisar_total_pb_title">TOTAL BEBAN POKOK / PEMBELIAN</td>
                      <td class="text-end text-warning font-weight-bold" id="ikhtisar_total_pembelian">Rp 0</td>
                    </tr>

                    <!-- LABA KOTOR -->
                    <tr class="row-highlight-kotor">
                      <td>LABA KOTOR (Total Penjualan - Pemakaian Bahan)</td>
                      <td class="text-end" id="ikhtisar_laba_kotor">Rp 0</td>
                    </tr>

                    <!-- III. BEBAN OPERASIONAL -->
                    <tr class="row-header-sec">
                      <td colspan="2">III. BEBAN OPERASIONAL (Beban Biaya Kas Keluar COA &ge; 5000)</td>
                    </tr>
                  </tbody>
                  <tbody id="ikhtisar_body_beban">
                    <!-- Dynamic Rows per COA -->
                  </tbody>
                  <tbody>
                    <tr class="row-subtotal">
                      <td class="text-end">TOTAL BEBAN OPERASIONAL</td>
                      <td class="text-end text-danger font-weight-bold" id="ikhtisar_total_operasional">Rp 0</td>
                    </tr>

                    <!-- LABA BERSIH -->
                    <tr class="row-highlight-bersih" id="row_laba_bersih">
                      <td>🏆 LABA BERSIH (Laba Kotor - Total Beban Operasional)</td>
                      <td class="text-end" id="ikhtisar_laba_bersih">Rp 0</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TAB 2: RINCIAN PENJUALAN -->
            <div class="tab-pane fade" id="tab-penjualan" role="tabpanel">
              <div class="row mb-2">
                <div class="col-md-4 ms-auto">
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control tb-search-tab" data-target="penjualan" placeholder="Cari no. ref atau konsumen...">
                  </div>
                </div>
              </div>
              <div id="dt-list-penjualan" class="table-striped table-bordered"></div>
            </div>

            <!-- TAB 3: RINCIAN PEMAKAIAN BAHAN BAKU -->
            <div class="tab-pane fade" id="tab-pembelian" role="tabpanel">
              <div class="row mb-2">
                <div class="col-md-4 ms-auto">
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control tb-search-tab" data-target="pembelian" placeholder="Cari no. transaksi, no. wo, barang...">
                  </div>
                </div>
              </div>
              <div id="dt-list-pembelian" class="table-striped table-bordered"></div>
            </div>

            <!-- TAB 4: RINCIAN BEBAN OPERASIONAL -->
            <div class="tab-pane fade" id="tab-operasional" role="tabpanel">
              <div class="row mb-2">
                <div class="col-md-4 ms-auto">
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control tb-search-tab" data-target="operasional" placeholder="Cari kode akun, nama, no. transaksi...">
                  </div>
                </div>
              </div>
              <div id="dt-list-operasional" class="table-striped table-bordered"></div>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="<?= base_url('script/app/keuangan/rpt_laba/rpt_index.js?v=' . time()); ?>"></script>
<?= $this->endSection('script'); ?>