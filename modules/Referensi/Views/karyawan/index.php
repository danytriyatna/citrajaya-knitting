<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-add-po" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i>Form Master Karyawan</h5>
        <input type="hidden" id="data_id">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4">
        <div class="alert alert-secondary border-0 small mb-4">
          <i class="fa fa-info-circle me-1"></i> Tanda bintang (<span class="text-danger">*</span>) wajib diisi.
        </div>

        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Data Pribadi</h6>
        
        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="nip">NIP <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" id="nip" name="nip" class="form-control" placeholder="Masukkan NIP" required>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="nama_konsumen">Nama Lengkap <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" id="nama_konsumen" name="nama_konsumen" class="form-control" placeholder="Masukkan Nama Lengkap" required>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="email">Email <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="email" id="email" name="email" class="form-control" placeholder="contoh@perusahaan.com" required>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
              <option value="1">Laki - Laki</option>
              <option value="2">Perempuan</option>
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="no_hp">No. WhatsApp <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <div class="input-group">
              <!-- <span class="input-group-text"><i class="fa fa-whatsapp"></i></span> -->
              <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="0812..." required>
            </div>
          </div>
        </div>

        <div class="row mb-4">
          <label class="col-md-3 col-form-label text-md-end" for="alamat">Alamat</label>
          <div class="col-md-8">
            <textarea name="alamat" id="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..."></textarea>
          </div>
        </div>

        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Penempatan & Jabatan</h6>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="posisi">Posisi <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" id="posisi" name="posisi" class="form-control" placeholder="Jabatan saat ini" required>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="tgl_bergabung">Tgl Bergabung <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="date" id="tgl_bergabung" name="tgl_bergabung" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="tipe">Tipe Karyawan</label>
          <div class="col-md-8">
            <select id="tipe" name="tipe" class="form-select select2">
              <option value="1">NON CMT</option>
              <option value="2">CMT</option>
            </select>
          </div>
        </div>

        <div class="row mb-3 d-none" id="div-cmt">
          <label class="col-md-3 col-form-label text-md-end" for="id_cmt">CMT <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select id="id_cmt" name="id_cmt" class="form-control custom-select select2">
              <?php foreach ($cmt as $rowData) : ?>
                <option value="<?= $rowData->id ?>"><?= $rowData->nama_operator ?></option>
              <?php endforeach ?>
            </select>
          </div>
        </div>

        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Upah & Rekening</h6>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-6 col-form-label text-md-end" for="nama_bank">Nama Bank</label>
              <div class="col-md-6">
                <input type="text" name="nama_bank" id="nama_bank" class="form-control" placeholder="Contoh: BCA, Mandiri, dll">
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-5 col-form-label text-md-end" for="no_rekening">No. Rekening</label>
              <div class="col-md-6">
                <input type="text" name="no_rekening" id="no_rekening" class="form-control" placeholder="Ketik nomor rekening">
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-6 col-form-label text-md-end" for="upah_harian">Upah Harian <span class="text-danger">*</span></label>
              <div class="col-md-6">
                <input type="number" name="upah_harian" id="upah_harian" class="form-control text-end" placeholder="0" required>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-5 col-form-label text-md-end" for="upah_jam">Upah Perjam <span class="text-danger">*</span></label>
              <div class="col-md-6">
                <input type="number" name="upah_jam" id="upah_jam" class="form-control text-end" placeholder="0" required>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-6 col-form-label text-md-end" for="upah_lembur">Upah Lembur <span class="text-danger">*</span></label>
              <div class="col-md-6">
                <input type="number" name="upah_lembur" id="upah_lembur" class="form-control text-end" placeholder="0" required>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-5 col-form-label text-md-end" for="upah_lembur_we">Lembur WE <span class="text-danger">*</span></label>
              <div class="col-md-6">
                <input type="number" name="upah_lembur_we" id="upah_lembur_we" class="form-control text-end" placeholder="0" required>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="row">
              <label class="col-md-6 col-form-label text-md-end" for="upah_lembur">Premi Kehadiran <span class="text-danger">*</span></label>
              <div class="col-md-6">
                <input type="number" name="premi_kehadiran" id="premi_kehadiran" class="form-control text-end" placeholder="0" required>
              </div>
            </div>
          </div>
        </div>

        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Foto Karyawan</h6>
        <div class="row">
          <label class="col-md-3 col-form-label text-md-end" for="fileKaryawan">Upload Foto</label>
          <div class="col-md-8">
            <input type="hidden" id="fileKaryawanOld">
            <input type="file" id="fileKaryawan" onchange="readURL(this,'#fileKaryawan')" name="fileKaryawan" class="form-control" accept=".jpg, .jpeg, .png">
            <div class="form-text mt-2 text-muted">Format: JPG, PNG. Maksimal 1MB.</div>
            <div class="mt-3">
               <img class="img-thumbnail d-none" id="linkFileKaryawan" style="max-height: 150px;" alt="Preview">
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button id="btn-save" type="button" class="btn btn-primary px-4">
          <i class="fa fa-save me-1"></i> Simpan Data
        </button>
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
            <li class="breadcrumb-item">Master Data</li>
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
              <div class="col-md-12 mb-3">
                <?php if (isset($_SESSION['message'])) { ?>
                    <script type="text/javascript">
                        window.setTimeout(function () {
                            $(".alert").alert('close');
                        }, 3000);
                    </script>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                <?php } ?>
                <?php if (isset($_SESSION['err'])) { ?>
                    <script type="text/javascript">
                        window.setTimeout(function () {
                            $(".alert").alert('close');
                        }, 5000);
                    </script>
                    <div class="alert alert-error">
                        <strong>Warning! </strong><?php echo $_SESSION['err']; ?>
                    </div>
                <?php } ?>
              </div>
              <div class="col-sm-3">
                <button type="button" class="btn btn-sm btn-success" id="btn-add"> <i class="fa fa-plus"></i> Tambah</button>
              </div>
              <div class="col-sm-4 offset-md-5">
                <div class="form-group">
                  <div class="input-group mb-3">
                    <span class="input-group-text bg-white" id="basic-addon11" style="border-right-width: 0px;"><i class="ti-search"></i></span>
                    <input id="tb-search" type="text" class="form-control p-s-0" placeholder="Pencarian" aria-label="Username" aria-describedby="basic-addon11" style="border-left-width: 0px;">
                  </div>
                </div>
              </div>
            </div>

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
<script src="script/app/referensi/karyawan/index.js"></script>
<?= $this->endSection('script'); ?>
