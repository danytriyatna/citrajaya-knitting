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
            <div class="col-sm-2 mb-3">
              <a href="/trans/delivery-order/add" class="btn btn-success"><i class="fa fa-plus"></i> Tambah</a>
            </div>
            <div class="col-sm-2 mb-3">
              <select id="filter_buyer" name="filter_buyer" class="form-select select2" data-placeholder="-- Pilih Buyer --" required>
                <option value="all" selected>Semua Buyer</option>
                <?php foreach ($buyer as $item) : ?>
                  <option value="<?= $item['id'] ?>"><?= $item['nama'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-2 mb-3">
              <input type="text" id="from_date" name="from_date" class="form-control datepickerx" placeholder="FROM DATE">
            </div>
            <div class="col-sm-2 mb-3">
              <input type="text" id="to_date" name="to_date" class="form-control datepickerx" placeholder="TO DATE">
            </div>
            <div class="col-sm-2 mb-3">
              <button id="btn_excel" class="btn btn-success open_form" type="button"><i class="fa fa-file-excel"></i> Print</button>
            </div>
            <div class="col-sm-2">
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
            <!-- <table class="table table-striped datatable">
              <thead>
                <tr>
                  <th style="min-width: 105px; width: 105px;">
                    <a href="trans/delivery-order/form" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah</a>
                  </th>
                  <th>DO NO.</th>
                  <th>DO DATE</th>
                  <th>SO NO.</th>
                  <th>STYLE</th>
                  <th>BUYER</th>
                  <th>SO QTY</th>
                  <th>DO QTY</th>
                  <th>REMAIN QTY</th>
                  <th>DO STATUS</th>
                </tr>
              </thead>
              <tbody>
                <?php for($i = 0; $i < 3; $i++) : ?>
                <tr>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Aksi <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-act" role="menu">
                        <li><a href="trans/delivery-order/form" title="Edit"><i class="fa fa-fw fa-edit"></i> Edit</a></li>
                        <li><a href="javascript:void(0)" title="Hapus"><i class="fa fa-fw fa-trash"></i> Hapus</a>
                        </li>
                      </ul>
                    </div>
                  </td>
                  <td>DO24100001</td>
                  <td>01-10-2024</td>
                  <td>SOD2410001</td>
                  <td>K-17 (CARDIGAN PITA)</td>
                  <td>YUSUF</td>
                  <td>1.000</td>
                  <td>0</td>
                  <td>1.000</td>
                  <td>
                    <span class="badge bg-secondary">DRAFT</span>
                  </td>
                </tr>
                <?php endfor; ?>
              </tbody>
            </table> -->
            <div class="table-striped" id="dt-list"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
<script src="script/app/transaction/delivery/index.js"></script>
<?= $this->endSection('script'); ?>