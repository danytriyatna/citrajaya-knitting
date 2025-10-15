<?= $this->extend('template'); ?>

<?= $this->section('modal') ?>
<div id="modal-form-add-po" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Master Karyawan</h5>
        <input type="hidden" id="data_id">
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-secondary p-y-8 text-muted">
          <i>*) Wajib diisi</i>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="nip">NIP<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="nip" name="nip" class="form-control" placeholder="Ketik NIP Karyawan" required>
            <div class="invalid-feedback">
              NIP Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="nama_konsumen">Nama<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="nama_konsumen" name="nama_konsumen" class="form-control" placeholder="Ketik Nama Karyawan" required>
            <div class="invalid-feedback">
              Nama Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="email">Email<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="email" name="email" class="form-control" placeholder="Ketik Email Karyawan" required>
            <div class="invalid-feedback">
              Email Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="posisi">Posisi<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="posisi" name="posisi" class="form-control" placeholder="Ketik Posisi Karyawan" required>
            <div class="invalid-feedback">
              Posisi Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="tgl_bergabung">Tgl Begabung<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="tgl_bergabung" name="tgl_bergabung" class="form-control" placeholder="Ketik Tgl Begabung Karyawan" required>
            <div class="invalid-feedback">
              Tgl Begabung Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="jenis_kelamin">Jenis Kelamin<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control">
              <option value="1">Laki - Laki</option>
              <option value="2">Perempuan</option>
            </select>
            <div class="invalid-feedback">
              Jenis Kelamin Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="alamat">Alamat</label>
          <div class="col-md-9">
            <textarea name="alamat" id="alamat" class="form-control" rows="5"></textarea>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="no_hp">No. Whatsapp<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="Ketik No. Whatsapp" required>
            <div class="invalid-feedback">
              No. Whatsapp Karyawan tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="tipe">Tipe</label>
          <div class="col-md-9">
            <select id="tipe" name="tipe" class="form-select select2" data-placeholder="-- Pilih Tipe --">
              <option value="1">NON CMT</option>
              <option value="2">CMT</option>
            </select>
          </div>

        </div>
        <div class="form-group row d-none" id="div-cmt">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="nama_jenis_barang">CMT<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <select id="id_cmt" name="id_cmt" class="form-control custom-select select2" data-placeholder="-- Pilih CMT --">
              <?php foreach ($cmt as $rowData) : ?>
                <option value="<?= $rowData->id ?>"><?= $rowData->nama_operator ?></option>
              <?php endforeach ?>
            </select>
            <div class="invalid-feedback">
              CMT tidak valid
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="upah_harian">Upah Harian<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <!-- <input type="email" id="upah_harian" name="upah_harian" class="form-control" placeholder="Ketik Upah Harian" required> -->
            <input type="number" name="upah_harian" value="" id="upah_harian" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark="." required>
            <div class="invalid-feedback">
              Upah Harian Karyawan tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="upah_lembur">Upah Lembur<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="number" name="upah_lembur" value="" id="upah_lembur" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark="." required>
            <div class="invalid-feedback">
              Upah Lembur Karyawan tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="upah_lembur_we">Upah Lembur Weekend<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="number" name="upah_lembur_we" value="" id="upah_lembur_we" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark="." required>
            <div class="invalid-feedback">
              Upah Weekend Karyawan tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="upah_jam">Upah Perjam<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="number" name="upah_jam" value="" id="upah_jam" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark="." required>
            <div class="invalid-feedback">
              Upah Perjam tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="premi_kehadiran">Permi Kehadiran<span class="text-danger">*</span></label>
          <div class="col-md-9">
            <input type="number" name="premi_kehadiran" value="" id="premi_kehadiran" min="0" step="1" pattern="[0-9]*" class="form-control" 
                 placeholder="[0-9]" data-politespace="" data-politespace-grouplength="3" data-politespace-delimiter="," 
                 data-politespace-reverse="" data-politespace-decimal-mark="." required>
            <div class="invalid-feedback">
              Permi Kehadiran tidak valid
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="control-label text-start text-md-end col-md-2 col-form-label" for="foto_style">Foto</label>
          <div class="col-md-9">
            <input type="hidden" id="fileKaryawanOld">
            <input type="file" id="fileKaryawan" onchange="readURL(this,'#fileKaryawan')" name="fileKaryawan" class="form-control file-drag-drop" accept=".jpg, .jpeg, .png">
            <small class="form-text">Format file *.JPG, *.JPEG, *.PNG, ukuran maks. 1 MB</small>
            <br>
            <img class="m-t-10 w-40 d-none" id="linkFileKaryawan" alt="Foto Sales Order">
          </div>
        </div>

        
        
      </div>
      <div class="modal-footer">
        <button id="btn-save" type="button" class="m-s-5 btn btn-success"> <i class="fa fa-save"></i> Simpan</button>
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
