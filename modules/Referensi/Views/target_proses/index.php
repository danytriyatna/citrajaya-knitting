<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-target-proses" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="modal-title-text">Form Proses Target Harian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="form-target-proses">
        <input type="hidden" id="data_id" name="id">
        <div class="modal-body">
          <div class="alert alert-secondary py-2 text-muted small">
            <i>*) Wajib diisi</i>
          </div>

          <div class="mb-3">
            <label class="form-label font-weight-bold" for="kode">Kode Singkatan <span class="text-danger">*</span></label>
            <input type="text" id="kode" name="kode" class="form-control text-uppercase font-weight-bold" placeholder="Contoh: K, P, QC, BB, ST" maxlength="20" required>
            <small class="text-muted">Kode huruf singkat yang diketik supervisor di cell jam (misal: <code>K=10</code>, <code>P=20</code>).</small>
          </div>

          <div class="mb-3">
            <label class="form-label font-weight-bold" for="nama">Nama Pekerjaan / Proses <span class="text-danger">*</span></label>
            <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Pasang Kancing, Packing, QC" maxlength="100" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label font-weight-bold" for="target_harian">Standar Target Harian</label>
              <input type="number" step="any" id="target_harian" name="target_harian" class="form-control text-end" placeholder="0" min="0">
              <small class="text-muted">Target standar umum untuk 1 hari kerja.</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label font-weight-bold" for="satuan">Satuan Output</label>
              <input type="text" id="satuan" name="satuan" class="form-control" placeholder="Pcs" value="Pcs" maxlength="20">
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label font-weight-bold" for="warna_badge">Warna Badge</label>
              <div class="input-group">
                <input type="color" id="warna_badge_picker" class="form-control form-control-color" value="#007bff" style="max-width: 50px;">
                <input type="text" id="warna_badge" name="warna_badge" class="form-control font-monospace" value="#007bff">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label font-weight-bold" for="keterangan">Keterangan (Opsional)</label>
            <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
          </div>

          <div class="mb-2">
            <label class="form-label font-weight-bold" for="seq">Urutan Tampilan (Sequence)</label>
            <input type="number" id="seq" name="seq" class="form-control" value="1" min="1">
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button id="btn-save" type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Simpan</button>
        </div>
      </form>
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
          <li class="breadcrumb-item">Master Data</li>
          <li class="breadcrumb-item active"><?= $titlehead ?></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="row mb-3 align-items-center">
            <div class="col-sm-6">
              <button type="button" class="btn btn-sm btn-success" id="btn-add">
                <i class="fa fa-plus me-1"></i> Tambah Proses Target
              </button>
            </div>
            <div class="col-sm-4 offset-sm-2">
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti-search"></i></span>
                <input type="text" id="tb-search" class="form-control" placeholder="Cari Kode atau Nama Proses...">
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <div id="dt-list"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<script src="<?= base_url('script/app/referensi/target_proses/index.js') ?>"></script>
<?= $this->endSection('script'); ?>
