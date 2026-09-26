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
          <li class="breadcrumb-item">SDM</li>
          <li class="breadcrumb-item active"><?= $titlehead ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">

          <div class="row align-items-center g-2 mb-3">

            <!-- Tambah Button -->
            <div class="col-sm-auto">
              <a href="/sdm/lembur/form" class="btn btn-sm btn-success" id="btn-add">
                <i class="fa fa-plus me-1"></i> Tambah Lembur
              </a>
            </div>

            <!-- Filter Perusahaan -->
            <div class="col-sm-2">
              <select id="filter_perusahaan" name="filter_perusahaan" class="form-select select2">
                <?php if ($superadmin == true) : ?>
                  <option value="all" selected>Semua Perusahaan</option>
                <?php endif; ?>
                <?php foreach ($perusahaan as $item) : ?>
                  <option <?= $user_perusahaan == $item->id ? 'selected' : '' ?> value="<?= $item->id ?>">
                    <?= $item->nama_perusahaan ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filter Status -->
            <div class="col-sm-2">
              <select id="filter_status" name="filter_status" class="form-select select2">
                <option value="" selected>Semua Status</option>
                <option value="0">Draft</option>
                <option value="1">Disetujui Penugasan (Approve 1)</option>
                <option value="2">Disetujui Jam Lembur (Approve 2)</option>
                <option value="3">Dibatalkan</option>
              </select>
            </div>

            <!-- Filter Tanggal Awal & Akhir -->
            <div class="col-sm-auto d-flex align-items-center">
              <input type="date" class="form-control form-control-sm" id="filter_tgl_awal" style="width: 135px;">
              <span class="mx-1 text-muted">s/d</span>
              <input type="date" class="form-control form-control-sm" id="filter_tgl_akhir" style="width: 135px;">
            </div>

            <!-- Spacer -->
            <div class="col"></div>

            <!-- Search -->
            <div class="col-sm-2">
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                  <i class="ti-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" id="tb-search" placeholder="Pencarian...">
              </div>
            </div>

          </div>

          <!-- Flash Message -->
          <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="fa fa-check-circle me-1"></i>
              <?= session()->getFlashdata('success') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fa fa-exclamation-triangle me-1"></i>
              <?= session()->getFlashdata('error') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-sm-12">
              <div id="dt-list" class="table-responsive table-striped"></div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<script src="/script/app/sdm/lembur/index.js"></script>
<?= $this->endSection('script'); ?>
