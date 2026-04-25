<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>
<style>

  .bg-hejo {
    background-color: #fff2cc !important;
  }
  .bg-hejo td {
    background-color: #fff2cc !important;
  }
</style>
<div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
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
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <div class="form-group row align-items-end">
    
                  <div class="col-md-4">
                    <label class="font-weight-bold text-primary">Dari Periode:</label>
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

                  <div class="col-md-4">
                    <label class="font-weight-bold text-danger">Sampai Periode:</label>
                    <div class="input-group">
                      <select name="bulan_to" id="bulan_to" class="form-control">
                        <?php foreach ($months as $v => $label): ?>
                          <option value="<?= $v ?>" <?= ($bulan == $v) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                      </select>
                      <?= (isset($slc_tahun) && !empty($slc_tahun)) ? form_dropdown($slc_tahun) : ""; ?>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="btn-group w-100">
                      <button id="btn_excel" class="btn btn-success shadow-sm" type="button">
                        <i class="fa fa-file-excel"></i> Export Excel
                      </button>
                    </div>
                  </div>

                </div>

                <!-- <div class="row mt-3">
                  <div class="col-md-4 offset-md-8">
                    <div class="input-group shadow-sm">
                      <input type="text" class="form-control" id="tb-search" placeholder="Cari data di tabel...">
                      <div class="input-group-append">
                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                      </div>
                    </div>
                  </div>
                </div> -->
                <hr>
                <div class="table-responsive d-none">
                  <?php 
                    $total_pendapatan = 0;
                    $total_pengeluaran = 0;
                    $total_pengeluaran_beban = 0;
                    $total_pendapatan += $mlaba->getDataPendapatan(1, $tahun, $bulan);
                    $total_pendapatan += 0;//$mlaba->getDataPendapatan(2, $tahun, $bulan);

                    $ttl_ = !empty($mlaba->getDataPengeluaran(1, 2, $tahun, $bulan)) ? (int) $mlaba->getDataPengeluaran(1, 2, $tahun, $bulan) : 0;
                    $total_pengeluaran += $ttl_;
                    $total_pengeluaran += 0;//$mlaba->getDataPengeluaran(1, 1, $tahun, $bulan);
                    $total_pengeluaran += 0;//$mlaba->getDataPengeluaran(2, 2, $tahun, $bulan);
                    $spasi = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                  ?>
                  <table style="width:100%;" id="dt-list" class="table table-striped">
                    <thead>
                      <tr>
                        <th>Nama Akun</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><b class="f-w-700">4000 - Sales</b></td>
                        <td></td>
                        <td></td>
                      </tr>
                      <tr>
                        <td><b class="f-w-700"><?= $spasi; ?>4001 - Pendapatan</b></td>
                        <td style="text-align:right;"><?= format_angka($mlaba->getDataPendapatan(1, $tahun, $bulan)); ?></td>
                        <td></td>
                      </tr>
                     
                      <tr class="bg-hejo" >
                        <td><b class="f-w-700" ><?= $spasi; ?>Total Pendapatan</b></td>
                        <td></td>
                        <td style="text-align:right;"><b><?= format_angka($total_pendapatan); ?></b></td>
                      </tr>
                      <tr>
                        <td><b class="f-w-700"><?= $spasi; ?>4002 - Pengeluaran</b></td>
                        <td style="text-align:right;"><?= format_angka($ttl_); ?></td>
                        <td></td>
                      </tr>
                      
                      <tr class="bg-hejo">
                        <td><b class="f-w-700" ><?= $spasi; ?>Total Pengeluaran</b></td>
                        <td></td>
                        <td style="text-align:right;"><b><?= format_angka($total_pengeluaran); ?></b></td>
                      </tr>
                      <tr>
                        <th><h4 class="f-w-700" >Total Sale</h4> </th>
                        <th></th>
                        <th style="text-align:right;"><?= format_angka($total_pendapatan - $total_pengeluaran); ?></th>
                      </tr>
                      
                      <?php
                       foreach ($list_coa as $r) {
                        $parnt = $mlaba->getDataTransaksi(null, $tahun, $bulan, $r->coa_id);
                        if($r->level == 1 && !empty($parnt)){ ?>
                      <tr>
                        <td><b class="f-w-700"><?= $r->kode; ?> - <?= $r->nama; ?> </b></td>
                        <td></td>
                        <td></td>
                      </tr>
                      <?php }
                       $spasix = $spasi;
                       if( $r->level == 2){
                        $spasix = $spasix . $spasix;
                       } else if( $r->level == 3){
                        $spasix = $spasix . $spasix . $spasix;
                       } 
                       $tot_labax = $mlaba->getDataTransaksi($r->coa_id, $tahun, $bulan);
                       $total_pengeluaran_beban += $tot_labax;
                       if(!empty($tot_labax)){
                       ?>
                      <tr>
                        <td><span><?= $spasix; ?> <?= $r->kode; ?> - <?= $r->nama; ?>  </span></td>
                        <td style="text-align:right;"><?= format_angka($mlaba->getDataTransaksi($r->coa_id, $tahun, $bulan)); ?></td>
                        <td></td>
                      </tr>
                      <?php } 
                        } ?>
                      <tr style="background-color: #fff2cc;">
                        <td><b class="f-w-700" >Total Fix Cost</b></td>
                        <td></td>
                        <td style="text-align:right;"><b><?= format_angka($total_pengeluaran_beban); ?></b></td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th><h4 class="f-w-700" >Laba Bersih</h4> </th>
                        <th></th>
                        <th style="text-align:right;"><?= format_angka($total_pendapatan - ($total_pengeluaran + $total_pengeluaran_beban)); ?></th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
      </div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="/script/app/keuangan/rpt_laba/rpt_index.js"></script>
<?= $this->endSection('script'); ?>
