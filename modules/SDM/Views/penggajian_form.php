<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>

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
          <li class="breadcrumb-item active"><?= $titlehead ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
        <form action="<?= base_url() . '/' . uri_string(); ?>" id="fmain" method="post" enctype='multipart/form-data' class="form-horizontal">
            <div class="row">
              <div class="col-sm-12">
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-3 col-form-label" for="kode_gaji">Kode Gaji</label>
                      <div class="col-md-9">
                        <input type="text" readonly id="kode_gaji" name="kode_gaji" class="form-control" placeholder="Ketikkan Kode Gaji" value="<?= !empty($row->kode_gaji)? $row->kode_gaji : '' ?>">
                        <input type="hidden" id="id_transaksi" value="<?= !empty($id) ? $id : '' ?>">
                        <input type="hidden" id="inpDet">
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group row">
                      <label class="control-label text-start text-md-end col-md-4 col-form-label" for="keterangan">Keterangan</label>
                      <div class="col-md-8">
                        <textarea rows="3" id="keterangan" name="keterangan" class="form-control" ><?= !empty($row->keterangan)? $row->keterangan : '' ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>
            </div>

            <hr>
          <div class="row">
            <div class="col-sm-2">
              <div class="form-group row mb-3">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="filter_tgl_from">Tanggal</label>
                  <input type="text" <?= empty($id) ? '' : 'disabled' ?> id="filter_tgl_from" name="filter_tgl_from" class="form-control datepickerx" placeholder="Pilih tanggal awal" value="<?= !empty($row->periode_awal)? $row->periode_awal : '' ?>">
                </div>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group row mb-3">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="filter_tgl_to">&nbsp;</label>
                  <input type="text" <?= empty($id) ? '' : 'disabled' ?> id="filter_tgl_to" name="filter_tgl_to" class="form-control datepickerx" placeholder="Pilih tanggal akhir" value="<?= !empty($row->periode_akhir)? $row->periode_akhir : '' ?>">
                </div>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group row mb-3">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="filter_cmt">&nbsp;</label>
                  <select <?= empty($id) ? '' : 'disabled' ?> id="filter_cmt" name="filter_cmt" class="form-select select2" data-placeholder="-- Pilih Tipe --">
                    <option value="1" <?= !empty($row->type) && $row->type == 1? "selected" : '' ?>>NON CMT</option>
                    <option value="2" <?= !empty($row->type) && $row->type == 2? "selected" : '' ?>>CMT</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group row mb-3">
                <div class="col-md-12">
                  <label class="control-label text-start col-form-label" for="id_perusahaan">&nbsp;</label>
                  <select <?= empty($id) ? '' : 'disabled' ?> id="id_perusahaan" name="id_perusahaan" class="form-select select2" data-placeholder="-- Pilih Perusahaan --">
                    <option value="" <?= empty($row->id_perusahaan) ? 'selected' : '' ?> disabled>-- Pilih Perusahaan --</option>
                    <?php foreach ($perusahaan as $item) : ?>
                      <option value="<?= $item->id ?>" <?= (!empty($row->id_perusahaan) && $row->id_perusahaan == $item->id) ? 'selected' : '' ?>>
                        <?= $item->nama_perusahaan ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <?php if(empty($id) || $row->status != 2) {?>
              <div class="col-sm-auto align-self-end mb-3 d-flex gap-2">
                <button id="btn-generate" class="btn btn-primary" type="button"><i class="fa fa-table me-1"></i> Generate</button>
                <button id="btn-sync-tbmp" class="btn btn-warning text-dark font-weight-bold" type="button"><i class="fa fa-refresh me-1"></i> Sinkronisasi</button>
              </div>
            <?php }?>
          </div>
          
          <hr>
          <div class="table-responsive">
            <div class="table-striped" id="dt-penggajian"></div>
          </div>

          <hr>
          <div class="row">
              <div class="col-sm-5">

              </div>
              <div class="col-sm-7 align-self-end text-end d-flex justify-content-end align-items-center">
                <a href="sdm/penggajian" class="btn btn-default me-2">
                  <span class="fa fa-arrow-left"></span> Kembali
                </a>
                <?php if($row->status != 2){ ?>
                  <button id="btn-save" class="btn btn-primary me-1" type="button"><i class="fa fa-save"></i>&nbsp; Simpan</button>
                  <button id="btn-approve" class="btn btn-success" type="button"><i class="fa fa-check"></i>&nbsp; Approve</button>
                <?php }else { ?>
                  <select id="tipe_cetak" class="form-control d-inline-block w-auto me-2" style="max-width: 180px;">
                    <option value="detail">Detail Penggajian</option>
                    <option value="rekap">Rekap Penggajian</option>
                  </select>
                  <button id="btn-cetak-print" class="btn btn-danger me-1" type="button"><i class="fa fa-print"></i>&nbsp; Print PDF</button>
                  <button id="btn-cetak-export" class="btn btn-success" type="button"><i class="fa fa-file-excel"></i>&nbsp; Print .xlsx</button>
                <?php } ?>
                <input type="hidden" name="detailData" id="detailData" value='<?= !empty($Ldetail) ? $Ldetail : ''?>'>
                <input type="hidden" name="actionf" id="actionf" value="">
              </div>
          </div>
        </form>
        </div>
      </div>
    </div>
  </div>
  
</div>

<?= $this->endSection('content'); ?>

<?= $this->section('script') ?>
<!-- <script>
  const btnGenerate = document.querySelector('#btn-generate');
  const rowData = document.querySelector('table > tbody');

  if (rowData)

  btnGenerate.addEventListener('click', () => {
    iLoader.start()

    setTimeout(() => {
      rowData.classList.remove('d-none');
      iLoader.stop();
    }, 1000)
  })
</script> -->
<script src="script/app/sdm/penggajian/form.js"></script>
<?= $this->endSection('script') ?>