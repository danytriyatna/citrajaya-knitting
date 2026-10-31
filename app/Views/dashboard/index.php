
<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>
  <div class="container-fluid">
    <style>
      /* ==========================================================================
         STANDAR UI/UX DASHBOARD (WARNA ASLI DASHBOARD DIPERTAHANKAN)
         ========================================================================== */
      .card-header h6 {
        font-size: 13px !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
      }

      /* Tipografi Thead Tabulator & Table (Warna Tetap Mengikuti Tema Asli Aplikasi) */
      .tabulator .tabulator-header .tabulator-col .tabulator-col-content .tabulator-col-title,
      table.table thead th {
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        text-transform: uppercase !important;
      }

      /* Standar Tbody Rows & Cells (Normal Font Weight - Tidak Bold) */
      .tabulator .tabulator-row .tabulator-cell,
      #tbl-tracking .tabulator-row .tabulator-cell,
      #tbl-persediaan-ringkasan .tabulator-row .tabulator-cell,
      #tbl-persediaan-detail .tabulator-row .tabulator-cell,
      #tbl-invoice .tabulator-row .tabulator-cell,
      #tbl-po .tabulator-row .tabulator-cell,
      #tbl-saldo .tabulator-row .tabulator-cell,
      table.table tbody td {
        font-size: 13px !important;
        font-weight: 400 !important;
        padding: 6px 10px !important;
        vertical-align: middle !important;
      }

      .tabulator .tabulator-row .tabulator-cell * {
        font-weight: 400 !important;
      }

      /* Standar Bottom Footer / Totals */
      .tabulator .tabulator-footer,
      .tabulator .tabulator-calcs-holder,
      .tabulator-calcs-holder .tabulator-cell,
      #tbl-tracking .tabulator-calcs-holder .tabulator-cell,
      #tbl-persediaan-ringkasan .tabulator-calcs-holder .tabulator-cell {
        font-size: 13px !important;
        font-weight: 700 !important;
      }
    </style>

    <div class="row page-titles">
      <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor"><?= $titlehead ?></h4>
      </div>
      <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./"><?= $name_app ?></a></li>
            <li class="breadcrumb-item active"><?= $titlehead ?></li>
          </ol>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-700">TRACKING PRODUKSI</h6>
          </div>
          <div class="card-body">
            <div class="row mb-2 align-items-end">
              <div class="col-sm-4 mb-2">
                <div class="form-group mb-0">
                  <select id="filter_tracking" name="filter_tracking" class="form-select">
                    <option value="1">Sample</option>
                    <option value="2" selected>Sales Order</option>
                  </select>
                </div>
              </div>

              <div class="col-sm-8 d-flex justify-content-end mb-2">
                <div class="form-group mb-0" style="max-width: 450px; width: 100%;">
                  <div class="input-group">
                    <span class="input-group-text bg-white" id="basic-addon11">
                      <i class="ti-search"></i>
                    </span>
                    <input type="text" id="inp-tracking" class="form-control" placeholder="Pencarian" aria-label="Pencarian" aria-describedby="basic-addon11">
                  </div>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <div class="table-striped" id="tbl-tracking"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-700">RINGKASAN PERSEDIAAN</h6>
          </div>
          <div class="card-body">

            <div class="row mb-2 align-items-end filter-persediaan-bar">
              <div class="col-sm-3 mb-2">
                <label class="control-label" for="filter_persediaan_gudang">Gudang</label>
                <select id="filter_persediaan_gudang" name="filter_persediaan_gudang" class="form-select">
                  <?php foreach ($gudangPersediaan as $g) : ?>
                    <option value="<?= $g->id ?>" <?= (strtolower($g->nama_gudang) == 'gudang utama') ? 'selected' : '' ?>><?= $g->nama_gudang ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-3 mb-2">
                <label class="control-label" for="filter_persediaan_jenis">Jenis Barang</label>
                <select id="filter_persediaan_jenis" name="filter_persediaan_jenis" class="form-select">
                  <?php foreach ($jenisBarangPersediaan as $j) : ?>
                    <option value="<?= $j->id ?>" <?= (strtolower($j->nama_jenis_barang) == 'benang rajut') ? 'selected' : '' ?>><?= $j->nama_jenis_barang ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-2 mb-2">
                <label class="control-label" for="filter_persediaan_bulan">Bulan</label>
                <select id="filter_persediaan_bulan" name="filter_persediaan_bulan" class="form-select">
                  <?php
                  $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                  foreach ($namaBulan as $numBulan => $labelBulan) : ?>
                    <option value="<?= $numBulan ?>" <?= ($numBulan == $bulanPersediaanSaatIni) ? 'selected' : '' ?>><?= $labelBulan ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-sm-2 mb-2">
                <label class="control-label" for="filter_persediaan_tahun">Tahun</label>
                <select id="filter_persediaan_tahun" name="filter_persediaan_tahun" class="form-select">
                  <?php for ($y = $tahunPersediaanSaatIni + 1; $y >= $tahunPersediaanSaatIni - 5; $y--) : ?>
                    <option value="<?= $y ?>" <?= ($y == $tahunPersediaanSaatIni) ? 'selected' : '' ?>><?= $y ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-sm-2 mb-2">
                <button type="button" id="btn_update_persediaan" class="btn btn-info w-100">
                  <i class="fa fa-refresh"></i> Update Data
                </button>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-12">
                <div class="input-group">
                  <span class="input-group-text bg-white" id="basic-addon-persediaan"><i class="ti-search"></i></span>
                  <input type="text" id="inp-persediaan-search" class="form-control" placeholder="Cari kode warna / nama barang..." aria-label="Pencarian" aria-describedby="basic-addon-persediaan">
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <div class="table-striped" id="tbl-persediaan-ringkasan"></div>
            </div>

            <div id="panel-persediaan-detail" class="mt-3" style="display:none; border:2px dashed #e0a800; border-radius:8px; padding:14px 16px; background:#fffdf5;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <h6 class="mb-0 f-w-700" id="txt-persediaan-detail-title">Detail Stock Card</h6>
                  <small class="text-muted" id="txt-persediaan-detail-sub">-</small>
                </div>
                <button type="button" class="btn-close" id="btn-close-persediaan-detail" aria-label="Tutup"></button>
              </div>
              <div class="row mb-2">
                <div class="col-sm-3">
                  <table class="table mb-0">
                    <tbody>
                      <tr>
                        <th>Total Awal</th>
                        <th class="text-end" id="txt-persediaan-total-awal">0,00</th>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-sm-3">
                  <table class="table mb-0">
                    <tbody>
                      <tr>
                        <th>Total Akhir</th>
                        <th class="text-end" id="txt-persediaan-total-akhir">0,00</th>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="table-responsive">
                <div class="table-striped" id="tbl-persediaan-detail"></div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-700">PIUTANG PENJUALAN</h6>
          </div>
          <div class="card-body">
            <div class="row mb-2">
              <div class="col-sm-12 d-flex justify-content-end">
                <div class="input-group" style="max-width: 320px; width: 100%;">
                  <span class="input-group-text bg-white" id="basic-addon-inv"><i class="ti-search"></i></span>
                  <input type="text" id="inp-invoice" class="form-control" placeholder="Cari No. Invoice / Buyer...">
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <div class="table-striped"  id="tbl-invoice"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-700">HUTANG PEMBELIAN</h6>
          </div>
          <div class="card-body">
            <div class="row mb-2">
              <div class="col-sm-12 d-flex justify-content-end">
                <div class="input-group" style="max-width: 320px; width: 100%;">
                  <span class="input-group-text bg-white" id="basic-addon-po"><i class="ti-search"></i></span>
                  <input type="text" id="inp-po" class="form-control" placeholder="Cari No. PO / Vendor...">
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <div class="table-striped"  id="tbl-po"></div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-700">SALDO KAS BON</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-3 mb-2">
                <div class="form-group mb-0">
                  <select id="filter_saldo_bulan" name="filter_saldo_bulan" class="form-select">
                      <option value="1">Januari</option>
                      <option value="2">Februari</option>
                      <option value="3">Maret</option>
                      <option value="4">April</option>
                      <option value="5">Mei</option>
                      <option value="6">Juni</option>
                      <option value="7">Juli</option>
                      <option value="8">Agustus</option>
                      <option value="9">September</option>
                      <option value="10">Oktober</option>
                      <option value="11">November</option>
                      <option value="12">Desember</option>
                    </select>
                </div>
              </div>
              <div class="col-sm-3 mb-2">
                <div class="form-group mb-0">
                  <select id="filter_saldo_tahun" name="filter_saldo_tahun" class="form-select">
                      <option value="2027">2027</option>
                      <option value="2026">2026</option>
                      <option value="2025">2025</option>
                      <option value="2024">2024</option>
                      <option value="2023">2023</option>
                      <option value="2022">2022</option>
                      <option value="2021">2021</option>
                      <option value="2020">2020</option>
                    </select>
                </div>
              </div>
              <div class="col-sm-2 mb-2">
                <div class="form-group mb-0">
                  <button id="btn_update" class="btn btn-info update_saldo" type="button">Update</button>
                </div>
              </div>
              <div class="col-sm-4 d-flex justify-content-end mb-2">
                <div class="input-group" style="width: 100%;">
                  <span class="input-group-text bg-white" id="basic-addon-saldo"><i class="ti-search"></i></span>
                  <input type="text" id="inp-saldo" class="form-control" placeholder="Pencarian...">
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <div class="table-striped"  id="tbl-saldo">
                
              </div>
              <!-- <table class="table table-striped">
                <thead>
                  <tr>
                    <th>NO. PO</th>
                    <th>TGL PO</th>
                    <th>VENDOR</th>
                    <th>TGL JATUH TEMPO</th>
                    <th>NILAI PO</th>
                    <th>PEMBAYARAN</th>
                    <th>SISA</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table> -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-600">GAJI/UPAH/BORONGAN</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group row mb-0">
                  <div class="col-md-12">
                    <label class="control-label text-start col-form-label" for="filter_gaji_from">Periode</label>
                    <input type="text" id="filter_gaji_from" name="filter_gaji_from" class="form-control datepicker" placeholder="Pilih tanggal awal" value="">
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group row mb-0">
                  <div class="col-md-12">
                    <label class="control-label text-start col-form-label" for="filter_gaji_to">&nbsp;</label>
                    <input type="text" id="filter_gaji_to" name="filter_gaji_to" class="form-control datepicker" placeholder="Pilih tanggal akhir" value="">
                  </div>
                </div>
              </div>
            </div>

            <hr>
            
            <div class="table-responsive">
              <table class="table">
                <tbody>
                  <tr>
                    <th><b>Gaji/Upah Karyawan</b></th>
                    <th></th>
                    <th></th>
                  </tr>
                  <tr>
                    <td class="p-s-24">- Gaji/Upah</td>
                    <td>975.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="p-s-24">- Lembur HK</td>
                    <td>225.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="p-s-24">- Lembur HL</td>
                    <td>0,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="p-s-24">- Potongan</td>
                    <td>50.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Total Gaji/Upah Karyawan</b></th>
                    <th></th>
                    <th><b>1.250.000,00</b></th>
                  </tr>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Gaji/Upah Borongan</b></th>
                    <th></th>
                    <th></th>
                  </tr>
                  <tr>
                    <td class="p-s-24">Teh Ndok</td>
                    <td>500.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td class="p-s-24">Pak Hasan</td>
                    <td>250.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Jumlah Gaji/Ipah Borongan</b></th>
                    <th></th>
                    <th><b>750.000,00</b></th>
                  </tr>
                  <tr>
                    <th class="bg-warning"><b>Total Gaji/Upah/Borongan</b></th>
                    <th class="bg-warning"></th>
                    <th class="bg-warning"><b>2.000.000,00</b></th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div> -->

      <div class="row align-items-stretch">
        <div class="col-lg-6 mb-3">
          <div class="card h-100">
            <div class="card-header">
              <h6 class="mb-0 f-w-700">GRAFIK PENJUALAN DAN BIAYA</h6>
            </div>
            <div class="card-body mt-5">
              
              <div id="chartBar" style="width: auto; height: 600px;"></div>

            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-3">
          <div class="card h-100">
            <div class="card-header">
              <h6 class="mb-0 f-w-700">LAPORAN LABA/RUGI</h6>
            </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group row mb-0">
                  <div class="col-md-12">
                    <label class="control-label text-start col-form-label" for="filter_lap_bulan">Bulan</label>
                    <select id="filter_lap_bulan" name="filter_lap_bulan" class="form-select">
                      <option value="1">Januari</option>
                      <option value="2">Februari</option>
                      <option value="3">Maret</option>
                      <option value="4">April</option>
                      <option value="5">Mei</option>
                      <option value="6">Juni</option>
                      <option value="7">Juli</option>
                      <option value="8">Agustus</option>
                      <option value="9">September</option>
                      <option value="10">Oktober</option>
                      <option value="11">November</option>
                      <option value="12" selected>Desember</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group row mb-0">
                  <div class="col-md-12">
                    <label class="control-label text-start col-form-label" for="filter_lap_tahun">Tahun</label>
                    <select id="filter_lap_tahun" name="filter_lap_tahun" class="form-select">
                      <option value="2027">2027</option>
                      <option value="2026">2026</option>
                      <option value="2025">2025</option>
                      <option value="2024">2024</option>
                      <option value="2023">2023</option>
                      <option value="2022">2022</option>
                      <option value="2021">2021</option>
                      <option value="2020">2020</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <hr>
            
            <div class="table-responsive">
              <table class="table">
                <tbody>
                  <tr>
                    <th><b>Pendapatan</b></th>
                    <th></th>
                    <th></th>
                  </tr>
                  <tr>
                    <td class="p-s-24">- Penjualan</td>
                    <td id="penjualan" class="text-end">20.000.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Total Penjualan</b></th>
                    <th></th>
                    <th class="text-end"><b id="total_penjualan">20.000.000,00</b></th>
                  </tr>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Biaya Produksi</b></th>
                    <th></th>
                    <th></th>
                  </tr>
                  <!-- <tr>
                    <td class="p-s-24">- Pemakaian Barang/Bahan</td>
                    <td id="pemakaian">7.500.000,00</td>
                    <td></td>
                  </tr> -->
                  <tr>
                    <td class="p-s-24" id="label-pemakaian-row">- Pemakaian Barang dan Bahan</td>
                    <td id="pemakaian" class="text-end">7.500.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Total Biaya Produksi</b></th>
                    <th></th>
                    <th class="text-end"><b id="total_pemakaian">12.500.000,00</b></th>
                  </tr>
                  <tr>
                    <th class="bg-info"><b>Laba Kotor</b></th>
                    <th class="bg-info"></th>
                    <th class="bg-info text-end"><b id="laba_kotor">7.500.000,00</b></th>
                  </tr>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Biaya - Biaya</b></th>
                    <th></th>
                    <th></th>
                  </tr>

                  <tr>
                    <th><b>Total Biaya Operasional</b></th>
                    <th></th>
                    <th class="text-end"><b id="total_operasional">5.250.000.00</b></th>
                  </tr>
                  <tr>
                    <th class="bg-info"><b>Laba Bersih</b></th>
                    <th class="bg-info"></th>
                    <th class="bg-info text-end"><b id="laba_bersih">2.250.000,00</b></th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?= $this->endSection('content'); ?>

<?= $this->section('modal') ?>
<div id="modal-persediaan-wo" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <div>
          <h5 class="modal-title f-w-700" id="txt-modal-wo-title">Rincian Work Order (Booked Benang)</h5>
          <small class="text-muted" id="txt-modal-wo-sub">-</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <div class="card border mb-0">
              <div class="card-body p-2 text-center">
                <span class="text-muted small d-block">Total Kebutuhan WO</span>
                <span class="fs-5 fw-bold text-dark" id="txt-modal-wo-totalkebutuhan">0,00</span> <small class="text-muted">kg</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border mb-0">
              <div class="card-body p-2 text-center">
                <span class="text-muted small d-block">Sudah Transfer (Gudang CJK)</span>
                <span class="fs-5 fw-bold text-success" id="txt-modal-wo-totaltrf">0,00</span> <small class="text-muted">kg</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border border-warning mb-0 bg-warning-subtle">
              <div class="card-body p-2 text-center">
                <span class="text-muted small d-block">Sisa Booked (Memotong Stok)</span>
                <span class="fs-5 fw-bold text-primary" id="txt-modal-wo-totalbooked">0,00</span> <small class="text-muted">kg</small>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <div id="tbl-modal-wo-detail"></div>
        </div>
      </div>
      <div class="modal-footer justify-content-between bg-light py-2">
        <button type="button" class="btn btn-sm btn-outline-success" id="btn-export-wo-detail">
          <i class="ti-download me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection('modal') ?>


<?= $this->section('script') ?>
<script src="script/app/dashboard/index.js?v=<?= time(); ?>"></script>
<script src="script/app/dashboard/persediaan.js?v=<?= time(); ?>"></script>
<?= $this->endSection('script') ?>