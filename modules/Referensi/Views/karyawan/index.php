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
          <label class="col-md-3 col-form-label text-md-end" for="tempat_lahir">Tempat & Tgl Lahir <span class="text-danger">*</span></label>
          <div class="col-md-4">
            <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" placeholder="Kota lahir" required>
          </div>
          <div class="col-md-4">
            <input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control" required>
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
          <label class="col-md-3 col-form-label text-md-end" for="alamat">Alamat <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <textarea name="alamat" id="alamat" class="form-control" rows="3" placeholder="Alamat lengkap..." required></textarea>
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
          <label class="col-md-3 col-form-label text-md-end" for="kategori_posisi">Kategori Posisi <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select id="kategori_posisi" name="kategori_posisi" class="form-select select2" required>
              <option value="1">Mesin</option>
              <option value="2">Non Mesin</option>
              <option value="3">Staff Khusus</option>
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="id_shift">Shift Kerja <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select id="id_shift" name="id_shift" class="form-select select2" required>
              <?php if (!empty($shift)) : ?>
                <?php foreach ($shift as $s) : ?>
                  <option value="<?= $s->id ?>"><?= $s->nama_shift ?> (<?= substr($s->jam_masuk, 0, 5) ?> - <?= substr($s->jam_pulang, 0, 5) ?>)</option>
                <?php endforeach ?>
              <?php endif ?>
            </select>
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
              <option value="" selected disabled>Pilih Tipe</option>
              <option value="1">NON CMT</option>
              <option value="2">CMT</option>
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <label class="col-md-3 col-form-label text-md-end" for="id_perusahaan">Perusahaan</label>
          <div class="col-md-8">
            <select id="id_perusahaan" name="id_perusahaan" class="form-control custom-select select2">
              <option value="" selected disabled>Pilih Perusahaan</option>
              <?php foreach ($perusahaan as $rowData) : ?>
                <option value="<?= $rowData->id ?>"><?= $rowData->nama_perusahaan ?></option>
              <?php endforeach ?>
            </select>
          </div>
        </div>

        <div class="row mb-3 d-none" id="div-cmt">
          <label class="col-md-3 col-form-label text-md-end" for="id_cmt">CMT <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select id="id_cmt" name="id_cmt" class="form-control custom-select select2">
              <option value="" selected disabled>Pilih CMT</option>
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

<!-- Modal Kelola Kontrak Kerja Karyawan -->
<div id="modal-kelola-kontrak" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title text-white"><i class="fa fa-file-text-o me-2"></i>Kelola Kontrak Kerja Karyawan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <!-- Info Karyawan Header Card -->
        <div class="card bg-light border-0 mb-3">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <span class="badge bg-secondary mb-1" id="kontrak-nip">NIP: -</span>
                <h5 class="mb-0 fw-bold text-dark" id="kontrak-nama">-</h5>
                <small class="text-muted" id="kontrak-posisi">-</small>
              </div>
              <div class="text-end">
                <span class="badge" id="kontrak-status-badge">Status Kontrak</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Alert Validasi Kelengkapan Profil -->
        <div id="kontrak-alert-warning" class="alert alert-warning d-none" role="alert">
          <div class="d-flex">
            <div class="me-3 fs-3 text-warning"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="flex-grow-1">
              <h6 class="alert-heading fw-bold mb-1">Data Profil Belum Lengkap!</h6>
              <div class="small mb-2" id="kontrak-alert-message">
                Semua data pada draf kontrak bersifat wajib. Silakan lengkapi profil karyawan terlebih dahulu sebelum mengunduh draf kontrak.
              </div>
              <div id="kontrak-missing-list" class="d-flex flex-wrap gap-1 mb-2"></div>
              <button type="button" class="btn btn-sm btn-warning fw-bold text-dark mt-1" id="btn-edit-dari-kontrak">
                <i class="fa fa-edit me-1"></i> Lengkapi Profil Karyawan Sekarang
              </button>
            </div>
          </div>
        </div>

        <!-- Panel Kontrak (Hanya tampil jika Profil Lengkap) -->
        <div id="kontrak-panel-complete">
          <!-- Ringkasan Data Kontrak -->
          <div class="border rounded p-3 mb-3 bg-white">
            <h6 class="fw-bold text-primary mb-2 border-bottom pb-1"><i class="fa fa-user me-1"></i> Ringkasan Identitas Kontrak</h6>
            <div class="row g-2 small">
              <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                  <tr><td class="text-muted" style="width: 130px;">Tempat, Tgl Lahir:</td><td class="fw-semibold" id="kontrak-preview-ttl">-</td></tr>
                  <tr><td class="text-muted">Jenis Kelamin:</td><td class="fw-semibold" id="kontrak-preview-gender">-</td></tr>
                  <tr><td class="text-muted">Alamat Domisili:</td><td class="fw-semibold" id="kontrak-preview-alamat">-</td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                  <tr><td class="text-muted" style="width: 130px;">Posisi / Bagian:</td><td class="fw-semibold" id="kontrak-preview-posisi">-</td></tr>
                  <tr><td class="text-muted">Upah Harian:</td><td class="fw-semibold text-success" id="kontrak-preview-upah">Rp. -</td></tr>
                </table>
              </div>
            </div>
          </div>

          <!-- Form Parameter Kontrak -->
          <div class="border rounded p-3 mb-3 bg-white">
            <h6 class="fw-bold text-primary mb-3 border-bottom pb-1"><i class="fa fa-calendar-check-o me-1"></i> Periode Masa Kontrak Kerja</h6>
            <form id="form-generate-kontrak">
              <input type="hidden" id="kontrak_karyawan_id">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label small fw-bold" for="input_tgl_mulai_kontrak">Tanggal Masuk / Mulai <span class="text-danger">*</span></label>
                  <input type="date" id="input_tgl_mulai_kontrak" name="input_tgl_mulai_kontrak" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold" for="select_durasi_kontrak">Durasi Kontrak <span class="text-danger">*</span></label>
                  <select id="select_durasi_kontrak" name="select_durasi_kontrak" class="form-select">
                    <option value="1">1 Bulan</option>
                    <option value="2">2 Bulan</option>
                    <option value="3" selected>3 Bulan</option>
                    <option value="6">6 Bulan</option>
                    <option value="12">12 Bulan (1 Tahun)</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Tanggal Akhir / Selesai</label>
                  <div class="form-control bg-light fw-bold text-primary" id="display_tgl_selesai_kontrak">-</div>
                </div>
              </div>

              <div class="mt-3 text-end">
                <button type="button" class="btn btn-primary px-3" id="btn-do-generate-kontrak">
                  <i class="fa fa-download me-1"></i> Unduh Draf Kontrak (.docx)
                </button>
              </div>
            </form>
          </div>

          <!-- Upload Signed Scan -->
          <div class="border rounded p-3 bg-white">
            <h6 class="fw-bold text-primary mb-2 border-bottom pb-1"><i class="fa fa-cloud-upload me-1"></i> Unggah Berkas Kontrak Bertandatangan</h6>
            <p class="small text-muted mb-3">
              Setelah dokumen draf dicetak dan ditandatangani basah oleh kedua belah pihak beserta pimpinan, unggah hasil scan dokumen di sini.
            </p>

            <div id="kontrak-existing-file" class="alert alert-success d-none mb-3 py-2 px-3">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <i class="fa fa-check-circle me-1"></i> Berkas kontrak bertandatangan tersimpan: 
                  <strong id="kontrak-file-name">-</strong>
                </div>
                <a href="#" target="_blank" class="btn btn-sm btn-success" id="link-download-signed-kontrak">
                  <i class="fa fa-external-link me-1"></i> Buka / Unduh Berkas
                </a>
              </div>
            </div>

            <form id="form-upload-kontrak" enctype="multipart/form-data">
              <div class="row align-items-end g-2">
                <div class="col-md-8">
                  <label class="form-label small fw-bold" for="file_scan_kontrak">Pilih File Scan (PDF / JPG / PNG / WEBP, Maks. 5MB)</label>
                  <input type="file" id="file_scan_kontrak" name="file_kontrak" class="form-control" accept=".pdf, .jpg, .jpeg, .png, .webp">
                </div>
                <div class="col-md-4">
                  <button type="button" class="btn btn-success w-100" id="btn-do-upload-kontrak">
                    <i class="fa fa-upload me-1"></i> Simpan Berkas
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
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

            <div class="row align-items-center g-2 mb-3">

              <!-- Tambah Button - paling kiri -->
              <div class="col-sm-auto">
                <button type="button" class="btn btn-sm btn-success" id="btn-add">
                  <i class="fa fa-plus me-1"></i> Tambah
                </button>
              </div>

              <!-- Filter Perusahaan -->
              <div class="col-sm-2">
                <select id="filter_perusahaan" name="filter_perusahaan"
                  class="form-select select2" data-placeholder="-- Pilih Perusahaan --">
                  <?php if ($superadmin == true) : ?>
                    <option value="all" selected>Semua Perusahaan</option>
                  <?php endif; ?>
                  <?php foreach ($perusahaan as $item) : ?>
                    <option <?= $superadmin == false ? 'disabled' : '' ?> <?= $user_perusahaan == $item->id ? 'selected' : '' ?>
                      value="<?= $item->id ?>">
                      <?= $item->nama_perusahaan ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Filter Tipe (Internal / CMT) -->
              <div class="col-sm-2">
                <select id="filter_tipe" name="filter_tipe"
                  class="form-select select2" data-placeholder="-- Pilih Tipe --">
                  <option value="all" selected>Semua Tipe Karyawan</option>
                  <option value="1">Internal (Non CMT)</option>
                  <option value="2">CMT (Mitra)</option>
                </select>
              </div>

              <!-- Spacer -->
              <div class="col"></div>

              <!-- Search - selalu paling kanan -->
              <div class="col-sm-2">
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="ti-search text-muted"></i>
                  </span>
                  <input type="text" class="form-control border-start-0 ps-0" id="tb-search"
                    placeholder="Pencarian...">
                </div>
              </div>

            </div>

            <!-- Flash Message -->
            <?php if (isset($_SESSION['message'])) : ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i>
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
              <script>
                setTimeout(() => document.querySelector('.alert-success')?.remove(), 3000);
              </script>
            <?php endif; ?>

            <?php if (isset($_SESSION['err'])) : ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle me-1"></i>
                <strong>Warning!</strong> <?= $_SESSION['err'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
              <script>
                setTimeout(() => document.querySelector('.alert-danger')?.remove(), 5000);
              </script>
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
<script src="script/app/referensi/karyawan/index.js"></script>
<?= $this->endSection('script'); ?>
