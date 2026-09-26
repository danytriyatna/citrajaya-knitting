<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-wo" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Detail Work Order</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-8">
            <h5 class="f-w-700 m-b-12">K-17 (CARDIGAN PITA)</h5>
            <p class="f-w-700 m-b-8 m-t-4">YUSUF</p>
            <p class="m-b-8 m-t-4">COLOUR: A. HITAM</p>
            <p class="m-t-4">QTY: 50</p>
          </div>
          <div class="col-sm-4">
            <div class="form-group row">
              <label class="control-label text-start text-md-end col-md-4 col-form-label" for="loss_perc">Loss (%)</label>
              <div class="col-md-8">
                <input type="text" id="loss_perc" name="loss_perc" class="form-control" placeholder="Ketikkan nilai loss" value="-5,00">
              </div>
            </div>
          </div>
        </div>

        <table class="table table-striped">
          <thead>
            <tr>
              <th>COLOUR</th>
              <th>%</th>
              <th>GRAM</th>
              <th>NEEDS (GRAM)</th>
              <th>IN KGS</th>
              <th>LOSS (KG)</th>
              <th>NFP (KG)</th>
              <th>QTY ON HAND</th>
              <th>(+/-)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-nowrap">A. HITAM</td>
              <td><input type="text" class="form-control"></td>
              <td><input type="text" class="form-control"></td>
              <td>26.400,10</td>
              <td>26,40</td>
              <td>1,32</td>
              <td>27,72</td>
              <td>29,90</td>
              <td>2,18</td>
            </tr>
            <tr>
              <td class="text-nowrap">B. BW.K</td>
              <td>17,20</td>
              <td>64,00</td>
              <td>9.599,83</td>
              <td>9,60</td>
              <td>0,48</td>
              <td>10,08</td>
              <td>10,02</td>
              <td>-0,06</td>
            </tr>
            <tr>
              <td class="text-nowrap">C. CREAM77</td>
              <td>19,36</td>
              <td>72,00</td>
              <td>10.800,09</td>
              <td>10,80</td>
              <td>0,54</td>
              <td>11,34</td>
              <td>11,34</td>
              <td>0,00</td>
            </tr>
            <tr>
              <td class="text-nowrap">D. MOCHA50</td>
              <td>16,31</td>
              <td>60,00</td>
              <td>8.999,99</td>
              <td>8,99</td>
              <td>0,45</td>
              <td>9,45</td>
              <td>9,45</td>
              <td>0,00</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2" class="text-end">TOTAL</th>
              <th>372,00</th>
              <th>55.800,00</th>
              <th>55,80</th>
              <th>2,79</th>
              <th>58,59</th>
              <th>60,71</th>
              <th>2,12</th>
            </tr>
          </tfoot>
        </table>

        <hr>

        <h6 class="f-w-700">PRODUCTION PROCESS</h6>
        <div class="row m-t-16">
          <div class="col-sm-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-1">
              <label class="form-check-label" for="check-process-1">
                RAJUT
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-2">
              <label class="form-check-label" for="check-process-2">
                LINKING-OBRAS
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-3">
              <label class="form-check-label" for="check-process-3">
                RABUT-SONTEK
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-4">
              <label class="form-check-label" for="check-process-4">
                WASHING
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-5">
              <label class="form-check-label" for="check-process-5">
                STEAM
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-6">
              <label class="form-check-label" for="check-process-6">
                LUBANG-KANCING
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="check-process-7">
              <label class="form-check-label" for="check-process-7">
                LABEL-SIZE
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="m-s-5 btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-form-add-po"> <i class="fa fa-save"></i> Simpan</button>
      </div>
    </div>
  </div>
</div>

<div id="modal-list-wo" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-m" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title ipendix-penguji-preview-title">List Data</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div hidden class="row">
          <div class="col-md-12 mb-3">
            <div class="col-md-4" style="float: right; position: relative; right: 15px;">
              <div class="homeSearch w-100" style="width: 100%; margin-left: 5%; margin-top: 0;">
                <input type="text" id="tb-search2" class="form-control" placeholder="Pencarian . . .">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div id="dt-list-ukuran" class="table-striped table-centered"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <!-- <a href="javascript:void(0)" id="pilihUkuran" class="btn btn-success float-left">Pilih Data</a> -->
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
          <input type="hidden" id="data-details" value='<?= $detail; ?>'>
          <input type="hidden" id="data-detail-barangs" value='<?= $walk_order_det; ?>'>
          <input type="hidden" id="data-ukuran" value='<?= $dtUkuran; ?>' >

          <input type="hidden" id="ref_id" value='<?= $ref_id; ?>'>
          <input type="hidden" id="tipe_id" value='<?= $tipe_id; ?>'>
          <input type="hidden" id="id_produksi" value='<?= $id_produksi; ?>'>
          <input type="hidden" id="id_walkorder" value='<?= $id_walkorder; ?>'>
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
              <h6 class="f-w-700 m-b-6"><?= $row->kode_prod; ?></h6>
              <h6 class="f-w-700 m-b-6"><?= $row->kode_walkorder; ?></h6>
              <h5 class="f-w-700 m-b-12"><?= $row->keterangan_style; ?></h5>
              <h7 class="f-w-700 m-b-12"><?= $row->keterangan; ?></h7>
              <p class="m-y-0"><?= fdate_eng_to_ind($row->tgl_transaksi); ?></p>
              <p class="m-y-0"><em>Deadline: <?= fdate_eng_to_ind($row->tgl_deadline); ?></em></p>
              <p class="f-w-700 m-t-4"><?= $row->konsumen_nama; ?></p>
              <img class="m-t-10 w-90" src="<?= $row->file_gambar; ?>" alt="Foto Sample">
            </div>
            <div class="col-sm-9">
              <div class="row">
                <div class="col-12">
                  <h6 class="f-w-700 m-b-6">DETAIL PESANAN (WARNA &amp; UKURAN)</h6>
                  <div class="table-striped table-centered" id="dt-detail"></div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12">
                  <h6 class="f-w-700 m-b-6 m-t-8">RINGKASAN PEMAKAIAN BAHAN PER WARNA</h6>
                  <div class="table-striped table-centered" id="dt-detail-barang"></div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12">
                  <h6 class="f-w-800 m-b-8 m-t-8 text-dark" style="letter-spacing: 0.5px; font-size: 13px;">
                    <i class="feather icon-bar-chart-2 text-primary mr-1"></i> ANALISIS RENDEMEN RAJUT &amp; GRAMATUR (HASIL RAJUT VS BENANG)
                  </h6>
                  <div class="table-responsive rounded" style="border: 2px solid #1e293b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);">
                    <table class="table table-bordered align-middle mb-0" style="border-collapse: collapse;">
                      <thead style="background: #0f172a !important; color: #ffffff !important;">
                        <tr class="text-center text-nowrap" style="white-space: nowrap !important; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;">
                          <th style="background: #1e3a8a !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #172554;">TARGET GRAMATUR WO</th>
                          <th style="background: #78350f !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #451a03;">BENANG TERPAKAI (BTK)</th>
                          <th style="background: #0f172a !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #020617;">HASIL QTY RAJUT</th>
                          <th style="background: #14532d !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #052e16;">BERAT KAIN RAJUT (BTM)</th>
                          <th style="background: #0f172a !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #020617;">RENDEMEN RAJUT (%)</th>
                          <th style="background: #3730a3 !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #1e1b4b;">GRAMATUR AKTUAL</th>
                          <th style="background: #0f172a !important; color: #ffffff !important; padding: 11px 8px; border: 1px solid #020617;">SELISIH / DEVIASI</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="text-center" style="font-size: 15px; font-weight: 800;">
                          <!-- Target Gramatur -->
                          <td style="background: #eff6ff; color: #1d4ed8; padding: 14px 8px; border: 1px solid #bfdbfe; font-weight: 800;">
                            <?= number_format($rendemen_rajut['target_gram'] ?? 0, 1, ',', '.'); ?> <span style="font-size: 11px; font-weight: 700; color: #3b82f6;">Gr/Pcs</span>
                          </td>
                          <!-- Benang Terpakai -->
                          <td style="background: #fffbeb; color: #b45309; padding: 14px 8px; border: 1px solid #fde68a; font-weight: 800;">
                            <?= number_format($rendemen_rajut['benang_keluar'] ?? 0, 2, ',', '.'); ?> <span style="font-size: 11px; font-weight: 700; color: #d97706;">Kg</span>
                          </td>
                          <!-- Hasil Qty Rajut -->
                          <td style="background: #f8fafc; color: #0f172a; padding: 14px 8px; border: 1px solid #cbd5e1; font-weight: 800;">
                            <?= number_format($rendemen_rajut['pcs_rajut'] ?? 0, 0, ',', '.'); ?> <span style="font-size: 11px; font-weight: 700; color: #475569;">Pcs</span>
                          </td>
                          <!-- Berat Kain Rajut -->
                          <td style="background: #f0fdf4; color: #15803d; padding: 14px 8px; border: 1px solid #bbf7d0; font-weight: 800;">
                            <?= number_format($rendemen_rajut['berat_kain'] ?? 0, 2, ',', '.'); ?> <span style="font-size: 11px; font-weight: 700; color: #16a34a;">Kg</span>
                          </td>
                          <!-- Rendemen Rajut -->
                          <td style="background: #ffffff; padding: 12px 8px; border: 1px solid #cbd5e1;">
                            <?php 
                              $benangVal = (float)($rendemen_rajut['benang_keluar'] ?? 0);
                              $kainVal   = (float)($rendemen_rajut['berat_kain'] ?? 0);
                              $yieldVal  = (float)($rendemen_rajut['rendemen'] ?? 0);
                              if ($benangVal > 0 && $kainVal > 0) : 
                                  if ($yieldVal >= 93 && $yieldVal <= 97) {
                                      $solidBg = 'background: #15803d !important; color: #ffffff !important; border: 1px solid #166534;';
                                      $labelKet = 'Normal (Ideal)';
                                  } elseif ($yieldVal >= 90 && $yieldVal < 93) {
                                      $solidBg = 'background: #ca8a04 !important; color: #ffffff !important; border: 1px solid #a16207;';
                                      $labelKet = 'Waspada (Susut)';
                                  } elseif ($yieldVal > 97 && $yieldVal <= 100) {
                                      $solidBg = 'background: #0284c7 !important; color: #ffffff !important; border: 1px solid #0369a1;';
                                      $labelKet = 'Sangat Efisien';
                                  } else {
                                      $solidBg = 'background: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c;';
                                      $labelKet = $yieldVal > 100 ? 'Anomali (>100%)' : 'Susut Tinggi (<90%)';
                                  }
                            ?>
                              <span class="badge px-3 py-2 fs-6 fw-bold" style="<?= $solidBg ?> font-weight: 800; border-radius: 4px; display: inline-block;">
                                <?= number_format($yieldVal, 2, ',', '.'); ?>%
                              </span>
                              <div style="font-size: 10px; font-weight: 700; color: #64748b; margin-top: 3px;"><?= $labelKet ?></div>
                            <?php else : ?>
                              <span class="text-muted fw-bold">-</span>
                            <?php endif; ?>
                          </td>
                          <!-- Gramatur Aktual -->
                          <td style="background: #eef2ff; color: #4338ca; padding: 14px 8px; border: 1px solid #c7d2fe; font-weight: 800;">
                            <?php if (($rendemen_rajut['gram_aktual'] ?? 0) > 0) : ?>
                              <?= number_format($rendemen_rajut['gram_aktual'], 1, ',', '.'); ?> <span style="font-size: 11px; font-weight: 700; color: #6366f1;">Gr/Pcs</span>
                            <?php else : ?>
                              <span class="text-muted fw-bold">-</span>
                            <?php endif; ?>
                          </td>
                          <!-- Selisih / Deviasi -->
                          <td style="background: #ffffff; padding: 12px 8px; border: 1px solid #cbd5e1;">
                            <?php 
                              $dev = (float)($rendemen_rajut['deviasi_gram'] ?? 0);
                              if (($rendemen_rajut['gram_aktual'] ?? 0) > 0 && ($rendemen_rajut['target_gram'] ?? 0) > 0) {
                                  if (abs($dev) <= 5) {
                                      $devStyle = 'background: #15803d !important; color: #ffffff !important; border: 1px solid #166534;';
                                      $devLabel = 'Sesuai Standar';
                                  } elseif ($dev > 0) {
                                      $devStyle = 'background: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c;';
                                      $devLabel = 'Lebih Berat';
                                  } else {
                                      $devStyle = 'background: #d97706 !important; color: #ffffff !important; border: 1px solid #b45309;';
                                      $devLabel = 'Lebih Ringan';
                                  }
                                  $signDev = ($dev > 0) ? '+' : '';
                                  echo "<span class='badge px-2 py-2 fs-6 fw-bold' style='{$devStyle} font-weight: 800; border-radius: 4px; display: inline-block;'>{$signDev}" . number_format($dev, 1, ',', '.') . " Gr</span>";
                                  echo "<div style='font-size: 10px; font-weight: 700; color: #64748b; margin-top: 3px;'>{$devLabel}</div>";
                              } else {
                                  echo "<span class='text-muted fw-bold'>-</span>";
                              }
                            ?>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <hr>

          <div class="row">
            <div class="col-sm-12">
              <h5><b>PRODUCTION PROCESS</b></h5>
                <div class="row">
                  <table class="table table-striped table-sm table-bordered no-footer mb-0">
                      <thead style="background-color: #1e293b !important; color: #ffffff !important;">
                        <tr>
                          <th style="width: 60%; background-color: #1e293b !important; color: #ffffff !important;">Proses</th>
                          <th style="text-align:right; width: 20%; background-color: #1e293b !important; color: #ffffff !important;">Qty Prod</th>
                          <th style="text-align:right; width: 20%; background-color: #1e293b !important; color: #ffffff !important;">Qty Perbaikan</th>
                        </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($proses as $r) { ?>
                            <tr>
                              <td>
                                  <h5 class="form-check-h1" for="proses_<?= $r->seq ?>"><?= $r->nama ?></h5>
                                  <input type="hidden" id="proses_<?= $r->id ?>" value="<?= $r->qty_prod ?>" />
                              </td>
                              <td style="text-align:right;">
                                <label class="form-check-label" for="proses_<?= $r->seq ?>"><?= $r->qty_prod ?></label>
                              </td>
                              <td style="text-align:right;">
                                <label class="form-check-label" for="proses_<?= $r->seq ?>"><?= $r->qty_fix ?></label>
                              </td>
                            </tr>
                          <?php } ?>
                            <tr>
                              <td>
                                 <h5 class="form-check-h1" for="proses_kirim">Kirim</h5>
                              </td>
                              <td style="text-align:right;">
                                <label class="form-check-label" for="proses_kirim" id="qty_kirim"><?= !empty($qty_kirim) ? $qty_kirim : 0; ?></label>
                              </td>
                              <td style="text-align:right;">
                                <label class="form-check-label" for="proses_kirim_fix" id="qty_kirim_fix"></label>
                              </td>
                            </tr>
                      </tbody>
                  </table>

                </div>

              <hr>

              <br>

              <div hidden class="row">
                <div class="col-sm-2">
                  <div class="form-group m-b-0 d-flex align-items-center">
                    <label class="control-label text-start text-md-end m-e-8" for="filter_status">Status</label>
                    <select id="filter_status" name="filter_status" class="form-control custom-select select2">
                      <?php foreach ($proses as $rowData) : ?>
                        <option value="<?= $rowData->id ?>"><?= $rowData->nama ?></option>
                      <?php endforeach ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="form-group m-b-0 d-flex align-items-center">
                    <label class="control-label text-start text-md-end m-e-8" for="filter_operator">CMT</label>
                    <select id="filter_operator" name="filter_operator" class="form-control custom-select select2">
                      <option value="">-</option>
                      <?php foreach ($operator as $rowData) : ?>
                        <option value="<?= $rowData->id ?>"><?= $rowData->nama_operator ?></option>
                      <?php endforeach ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-3">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-4 col-form-label" for="tgl_prod">Tanggal</label>
                    <div class="col-md-8">
                      <input type="text" id="tgl_prod" name="tgl_prod" class="form-control datepicker" placeholder="Pilih tanggal" value="<?= $dnow; ?>">
                    </div>
                  </div>
                </div>

                <div class="col-sm-4">
                  <div class="form-group row">
                    <label class="control-label text-start text-md-end col-md-3 col-form-label" for="nomesin">Nomor Mesin</label>
                    <div class="col-md-9">
                      <input type="text" id="nomesin" name="nomesin" class="form-control" placeholder="Ketikkan Nomor Mesin">
                    </div>
                  </div>
                </div>

              </div>

              <div hidden class="row mt-1">
                <!-- <div class="col-sm-2"> -->
                  <!-- <button type="button" class="btn btn-sm btn-info text-white mb-2" id="btn-add-detail"> Tambah <i class="fa fa-plus"></i></button> -->

                <!-- </div> -->
                <div class="col-sm-12">
                  <div class="input-group my-2">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-qrcode"></i></span>
                    <input type="text" class="form-control bg-info bg-opacity-25 ui-autocomplete-input" placeholder="Scan" aria-label="Scan" aria-describedby="basic-addon1" id="text_barcode" autocomplete="on">
                    <button type="button" class="btn btn-info text-white" id="btn-add-detail"><i class="fa fa-plus"></i></button> 
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-12">
              <div class="col-md-4" style="float: right;">
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="ti-search"></i></span>
                  <input type="text" id="tb-search-prod" class="form-control" placeholder="Cari Kode Transaksi / Operator...">
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-12">
              <div id="dt-list-prod" class="table-striped table-centered"></div>

              <br>

              <div class="row">
                <div class="col-sm-10">
                  <a href="trans/production" class="btn btn-default m-e-5">
                    <span class="fa fa-arrow-left"></span> Kembali
                  </a>
                  <button type="button" class="m-s-5 btn btn-success" hidden id="btn-save-ukuran"> <i class="fa fa-save"></i> Simpan</button>
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
<script src="script/app/transaction/production/form.js"></script>
<?= $this->endSection('script'); ?>