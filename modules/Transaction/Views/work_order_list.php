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
              <div hidden class="form-group m-b-0 d-flex align-items-center">
                <label class="control-label text-start text-md-end m-e-8" for="filter_status">Transaksi</label>
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
                  <input type="text" id="tb-search" class="form-control p-s-0" id="tb-search" placeholder="Pencarian" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="table-responsive">
            <div class="table-striped" id="dt-list"> </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<script src="script/app/transaction/walkorder/index.js"></script>
<?= $this->endSection('script'); ?>