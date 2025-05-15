
<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>
  <div class="container-fluid">
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
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-600">PIUTANG PENJUALAN</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-4 offset-md-8">
                <div class="form-group">
                  <div class="input-group mb-3">
                    <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                    <input type="text"id="inp-invoice" class="form-control p-s-0" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                  </div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <div class="table-striped"  id="tbl-invoice"></div>
              <!-- <table class="table table-striped">
                <thead>
                  <tr>
                    <th>NO. INVOICE</th>
                    <th>TGL INVOICE</th>
                    <th>BUYER</th>
                    <th>TGL JATUH TEMPO</th>
                    <th>NILAI INVOICE</th>
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

      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-600">HUTANG PEMBELIAN</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-4 offset-md-8">
                <div class="form-group">
                  <div class="input-group mb-3">
                    <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                    <input type="text"id="inp-po" class="form-control p-s-0" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                  </div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
            <div class="table-striped"  id="tbl-po"></div>
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
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-600">TRACKING PRODUKSI</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-sm-4 offset-md-8">
                <div class="form-group">
                  <div class="input-group mb-3">
                    <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                    <input type="text"id="inp-tracking" class="form-control p-s-0" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                  </div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <div class="table-striped"  id="tbl-tracking"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
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
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 f-w-600">LAPORAN LABA/RUGI</h6>
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
                    <td id="penjualan">20.000.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Total Penjualan</b></th>
                    <th></th>
                    <th><b id="total_penjualan">20.000.000,00</b></th>
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
                  <tr>
                    <td class="p-s-24">- Pemakaian Barang/Bahan</td>
                    <td id="pemakaian">7.500.000,00</td>
                    <td></td>
                  </tr>
                  <tr>
                    <th><b>Total Biaya Produksi</b></th>
                    <th></th>
                    <th><b id="total_pemakaian">12.500.000,00</b></th>
                  </tr>
                  <tr>
                    <th class="bg-info"><b>Laba Kotor</b></th>
                    <th class="bg-info"></th>
                    <th class="bg-info"><b id="laba_kotor">7.500.000,00</b></th>
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
                    <th><b id="total_operasional">5.250.000.00</b></th>
                  </tr>
                  <tr>
                    <th class="bg-info"><b>Laba Bersih</b></th>
                    <th class="bg-info"></th>
                    <th class="bg-info"><b id="laba_bersih">2.250.000,00</b></th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?= $this->endSection('content'); ?>

<?= $this->section('script') ?>
<script src="script/app/dashboard/index.js"></script>
<?= $this->endSection('script') ?>