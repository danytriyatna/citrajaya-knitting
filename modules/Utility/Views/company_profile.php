<?= $this->extend('template'); ?>

<?= $this->section('modal'); ?>
<!-- Modal Canvas Signature Pad untuk Profil Perusahaan -->
<div class="modal fade" id="modal-company-sig" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1065;" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
    <div class="modal-content shadow border-0">
      <div class="modal-header bg-dark text-white py-2">
        <h5 class="modal-title fs-6 text-white" id="modal-company-sig-title">
          <i class="fa fa-pencil-square-o me-2"></i> Tanda Tangan Pejabat Resmi
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <div class="alert alert-light border py-2 px-3 mb-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="badge bg-primary px-2 py-1 mb-1" id="company-sig-badge">PEJABAT</span>
              <div class="fw-bold text-dark" id="company-sig-target-name">-</div>
              <div class="text-muted small" id="company-sig-target-role">-</div>
            </div>
            <i class="fa fa-pencil fa-2x text-muted opacity-50"></i>
          </div>
        </div>

        <input type="hidden" id="company-sig-target-pihak" value="">

        <p class="small text-muted mb-2">
          Goreskan tanda tangan pada bidang kanvas di bawah menggunakan mouse atau sentuhan layar (touchscreen):
        </p>

        <div class="border rounded bg-white shadow-sm p-1 position-relative" style="touch-action: none; background-color: #fafbfc;">
          <canvas id="canvas-company-sig" width="480" height="200" style="width: 100%; height: 200px; display: block; cursor: crosshair; background: #ffffff; border-radius: 4px;"></canvas>
          <div class="position-absolute bottom-0 start-0 end-0 text-center pb-2 pointer-events-none" style="pointer-events: none;">
            <span class="text-muted" style="font-size: 11px; opacity: 0.6; border-top: 1px dashed #ced4da; display: inline-block; width: 80%; padding-top: 2px;">
              Garis Tanda Tangan Resmi
            </span>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-company-sig-clear">
            <i class="fa fa-eraser me-1"></i> Hapus / Bersihkan Kanvas
          </button>
          <span class="text-muted small" style="font-size: 11px;">
            <i class="fa fa-info-circle me-1"></i> Format PNG Transparan
          </span>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm px-4" id="btn-company-sig-apply">
          <i class="fa fa-check me-1"></i> Terapkan Tanda Tangan
        </button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection('modal'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">

  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h4 class="text-themecolor"><?= $titlehead ?></h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
      <div class="d-flex justify-content-end align-items-center">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="javascript:void(0)">Utilitas</a></li>
          <li class="breadcrumb-item active"><?= $titlehead ?></li>
        </ol>
      </div>
    </div>
  </div>

  <!-- Switcher Perusahaan jika ada lebih dari 1 -->
  <?php if (!empty($companies) && count($companies) > 1) : ?>
  <div class="row mb-3">
    <div class="col-12">
      <div class="card bg-light border-0 shadow-sm">
        <div class="card-body p-2 d-flex align-items-center gap-2">
          <span class="fw-bold small text-muted px-2"><i class="fa fa-building me-1"></i> Pilih Entitas Perusahaan:</span>
          <div class="btn-group" role="group">
            <?php foreach ($companies as $comp) : ?>
              <a href="/utilitas/company-profile?id=<?= $comp->id ?>" class="btn btn-sm <?= ($selectedId == $comp->id) ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fa fa-briefcase me-1"></i> <?= $comp->nama_perusahaan ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <form id="form-company-profile" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $company->id ?? 1 ?>">
    <input type="hidden" id="input_pihak1_signature" name="pihak1_signature" value="">
    <input type="hidden" id="input_mengetahui_signature" name="mengetahui_signature" value="">

    <div class="row">
      <!-- Kolom Kiri: Identitas & Kontak Perusahaan -->
      <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-primary">
              <i class="fa fa-id-card-o me-2"></i> Identitas & Legalitas Badan Usaha
            </h5>
            <small class="text-muted">Data ini menjadi acuan kop surat, surat jalan, dan dokumen resmi.</small>
          </div>
          <div class="card-body p-3">
            <div class="mb-3">
              <label class="form-label small fw-bold">Nama Sistem (Internal) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama_perusahaan" value="<?= esc($company->nama_perusahaan ?? '') ?>" required>
              <div class="form-text small">Nama ringkas untuk label modul dan transaksi aplikasi.</div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Nama Badan Usaha Resmi (Legal) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama_resmi" value="<?= esc($company->nama_resmi ?? '') ?>" placeholder="contoh: CV. Citra Jaya Knitting" required>
              <div class="form-text small">Nama lengkap berbadan hukum yang dicetak pada dokumen perjanjian/kontrak.</div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Alamat Lengkap Kantor & Pabrik</label>
              <textarea class="form-control" name="alamat" rows="3" placeholder="Alamat lengkap perusahaan..."><?= esc($company->alamat ?? '') ?></textarea>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold">No. Telepon / WhatsApp</label>
                <input type="text" class="form-control" name="telepon" value="<?= esc($company->telepon ?? '') ?>" placeholder="022-xxxxxxx / 08xxxx">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-bold">Email Resmi</label>
                <input type="email" class="form-control" name="email" value="<?= esc($company->email ?? '') ?>" placeholder="admin@citraknitt.com">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Nomor Pokok Wajib Pajak (NPWP)</label>
              <input type="text" class="form-control" name="npwp" value="<?= esc($company->npwp ?? '') ?>" placeholder="Nomor NPWP perusahaan">
            </div>

            <div class="mb-2">
              <label class="form-label small fw-bold">Logo Resmi Perusahaan</label>
              <div class="d-flex align-items-center gap-3">
                <div class="border rounded p-2 bg-light text-center" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                  <?php if (!empty($company->logo_url)) : ?>
                    <img id="preview-company-logo" src="<?= $company->logo_url ?>" alt="Logo" style="max-height: 80px; max-width: 80px; object-fit: contain;">
                  <?php else : ?>
                    <i class="fa fa-picture-o fa-2x text-muted" id="preview-company-logo-placeholder"></i>
                    <img id="preview-company-logo" src="" alt="Logo" class="d-none" style="max-height: 80px; max-width: 80px; object-fit: contain;">
                  <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                  <input type="file" class="form-control" name="logo_file" id="input-company-logo" accept="image/png, image/jpeg, image/webp">
                  <div class="form-text small">Format PNG, JPG, atau WEBP. Disarankan logo berlatar transparan.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Pejabat Penandatangan Dokumen Legal & Kontrak -->
      <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-bold text-success">
              <i class="fa fa-pencil-square-o me-2"></i> Pejabat Penandatangan Dokumen Resmi
            </h5>
            <small class="text-muted">Nama dan tanda tangan di bawah ini otomatis digunakan di seluruh draf Kontrak Kerja Karyawan.</small>
          </div>
          <div class="card-body p-3">

            <!-- Card Pihak Pertama (HRD) -->
            <div class="border rounded p-3 mb-3 bg-light">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-primary px-2 py-1"><i class="fa fa-user me-1"></i> PIHAK PERTAMA (HRD / WAKIL PERUSAHAAN)</span>
                <span class="text-muted small">Pihak 1 Kontrak</span>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Nama Pejabat</label>
                  <input type="text" class="form-control" id="form_pihak1_nama" name="pihak1_nama" value="<?= esc($company->pihak1_nama ?? 'Eva Fiantina') ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Jabatan Resmi</label>
                  <input type="text" class="form-control" id="form_pihak1_jabatan" name="pihak1_jabatan" value="<?= esc($company->pihak1_jabatan ?? 'HRD Citra Jaya Knitting') ?>" required>
                </div>
              </div>

              <!-- Tanda Tangan Pihak 1 -->
              <div>
                <label class="form-label small fw-bold mb-1">Tanda Tangan Digital Pihak Pertama</label>
                <div class="d-flex align-items-center gap-3">
                  <div class="border rounded bg-white p-1 text-center shadow-sm" style="width: 160px; height: 75px; display: flex; align-items: center; justify-content: center;">
                    <img id="view-sig-p1" src="<?= $company->pihak1_ttd_url ?: '' ?>" alt="TTD P1" class="<?= empty($company->pihak1_ttd_url) ? 'd-none' : '' ?>" style="max-height: 65px; max-width: 100%; object-fit: contain;">
                    <span id="placeholder-sig-p1" class="text-muted small fst-italic <?= !empty($company->pihak1_ttd_url) ? 'd-none' : '' ?>" style="font-size: 11px;">Belum Ada TTD</span>
                  </div>
                  <div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-1 d-block btn-open-company-sig" data-pihak="1">
                      <i class="fa fa-pencil me-1"></i> <?= empty($company->pihak1_ttd_url) ? 'Buat Tanda Tangan' : 'Ganti Tanda Tangan' ?>
                    </button>
                    <button type="button" class="btn btn-link text-danger btn-xs p-0 <?= empty($company->pihak1_ttd_url) ? 'd-none' : '' ?>" id="btn-delete-sig-p1" data-pihak="1">
                      <i class="fa fa-trash me-1"></i> Hapus TTD
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Mengetahui (Pimpinan / Direktur) -->
            <div class="border rounded p-3 mb-2 bg-light">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-dark px-2 py-1"><i class="fa fa-user-secret me-1"></i> MENGETAHUI (PIMPINAN / DIREKTUR)</span>
                <span class="text-muted small">Pihak Pengesah</span>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Nama Pimpinan</label>
                  <input type="text" class="form-control" id="form_mengetahui_nama" name="mengetahui_nama" value="<?= esc($company->mengetahui_nama ?? 'Edwin Ferdiansyah') ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Jabatan Resmi</label>
                  <input type="text" class="form-control" id="form_mengetahui_jabatan" name="mengetahui_jabatan" value="<?= esc($company->mengetahui_jabatan ?? 'Pimpinan CV. Citra Jaya Knitting') ?>" required>
                </div>
              </div>

              <!-- Tanda Tangan Mengetahui -->
              <div>
                <label class="form-label small fw-bold mb-1">Tanda Tangan Digital Pimpinan</label>
                <div class="d-flex align-items-center gap-3">
                  <div class="border rounded bg-white p-1 text-center shadow-sm" style="width: 160px; height: 75px; display: flex; align-items: center; justify-content: center;">
                    <img id="view-sig-p3" src="<?= $company->mengetahui_ttd_url ?: '' ?>" alt="TTD P3" class="<?= empty($company->mengetahui_ttd_url) ? 'd-none' : '' ?>" style="max-height: 65px; max-width: 100%; object-fit: contain;">
                    <span id="placeholder-sig-p3" class="text-muted small fst-italic <?= !empty($company->mengetahui_ttd_url) ? 'd-none' : '' ?>" style="font-size: 11px;">Belum Ada TTD</span>
                  </div>
                  <div>
                    <button type="button" class="btn btn-outline-dark btn-sm mb-1 d-block btn-open-company-sig" data-pihak="3">
                      <i class="fa fa-pencil me-1"></i> <?= empty($company->mengetahui_ttd_url) ? 'Buat Tanda Tangan' : 'Ganti Tanda Tangan' ?>
                    </button>
                    <button type="button" class="btn btn-link text-danger btn-xs p-0 <?= empty($company->mengetahui_ttd_url) ? 'd-none' : '' ?>" id="btn-delete-sig-p3" data-pihak="3">
                      <i class="fa fa-trash me-1"></i> Hapus TTD
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="alert alert-info border py-2 px-3 mt-3 mb-0 small">
              <i class="fa fa-info-circle me-1"></i> <strong>Otomatisasi:</strong> Setiap kali Bu Eva atau Pak Edwin menandatangani di sini, tanda tangan tersebut langsung menjadi standar default untuk seluruh pembuatan dan pengunduhan dokumen kontrak kerja.
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Sticky Bottom Bar -->
    <div class="card mt-3 border-0 shadow-sm bg-white">
      <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">
          <i class="fa fa-clock-o me-1"></i> Terakhir diperbarui: <strong><?= !empty($company->updated_at) ? date('d/m/Y H:i', strtotime($company->updated_at)) : '-' ?></strong>
        </span>
        <button type="button" class="btn btn-success px-4 fw-bold" id="btn-save-company-profile">
          <i class="fa fa-save me-1"></i> Simpan Pengaturan Profil Perusahaan
        </button>
      </div>
    </div>
  </form>

</div>
<?= $this->endSection('content'); ?>

<?= $this->section('script'); ?>
<script>
$(document).ready(function () {
    // Preview upload logo
    $('#input-company-logo').on('change', function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#preview-company-logo').attr('src', e.target.result).removeClass('d-none');
                $('#preview-company-logo-placeholder').addClass('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Canvas Signature Pad Engine
    let sigCanvas = document.getElementById('canvas-company-sig');
    let sigCtx = sigCanvas ? sigCanvas.getContext('2d') : null;
    let isDrawing = false;
    let hasDrawn = false;
    let lastX = 0, lastY = 0;

    function initCanvas() {
        if (!sigCanvas) {
            sigCanvas = document.getElementById('canvas-company-sig');
            if (!sigCanvas) return;
            sigCtx = sigCanvas.getContext('2d');
        }
        sigCanvas.width = 480;
        sigCanvas.height = 200;
        sigCtx.lineWidth = 2.5;
        sigCtx.lineCap = 'round';
        sigCtx.lineJoin = 'round';
        sigCtx.strokeStyle = '#111827';
    }

    function getCanvasCoords(e) {
        let r = sigCanvas.getBoundingClientRect();
        let clientX = e.clientX;
        let clientY = e.clientY;
        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }
        let scaleX = sigCanvas.width / r.width;
        let scaleY = sigCanvas.height / r.height;
        return {
            x: (clientX - r.left) * scaleX,
            y: (clientY - r.top) * scaleY
        };
    }

    function startDraw(e) {
        if (!sigCtx) return;
        e.preventDefault();
        isDrawing = true;
        let coords = getCanvasCoords(e);
        lastX = coords.x;
        lastY = coords.y;
        sigCtx.beginPath();
        sigCtx.moveTo(lastX, lastY);
    }

    function moveDraw(e) {
        if (!isDrawing || !sigCtx) return;
        e.preventDefault();
        let coords = getCanvasCoords(e);
        sigCtx.lineTo(coords.x, coords.y);
        sigCtx.stroke();
        lastX = coords.x;
        lastY = coords.y;
        hasDrawn = true;
    }

    function stopDraw(e) {
        if (!isDrawing || !sigCtx) return;
        isDrawing = false;
        sigCtx.closePath();
    }

    if (sigCanvas) {
        sigCanvas.addEventListener('mousedown', startDraw);
        sigCanvas.addEventListener('mousemove', moveDraw);
        window.addEventListener('mouseup', stopDraw);
        sigCanvas.addEventListener('touchstart', startDraw, { passive: false });
        sigCanvas.addEventListener('touchmove', moveDraw, { passive: false });
        window.addEventListener('touchend', stopDraw);
    }

    function clearCanvas() {
        if (!sigCanvas || !sigCtx) return;
        sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
        hasDrawn = false;
    }

    $('#btn-company-sig-clear').on('click', function () {
        clearCanvas();
    });

    // Buka Canvas TTD
    $('.btn-open-company-sig').on('click', function () {
        let pihak = $(this).data('pihak');
        $('#company-sig-target-pihak').val(pihak);

        let nama = (pihak == 1) ? $('#form_pihak1_nama').val() : $('#form_mengetahui_nama').val();
        let role = (pihak == 1) ? $('#form_pihak1_jabatan').val() : $('#form_mengetahui_jabatan').val();
        let badgeText = (pihak == 1) ? 'PIHAK PERTAMA (HRD)' : 'MENGETAHUI (PIMPINAN)';
        let badgeClass = (pihak == 1) ? 'badge bg-primary px-2 py-1 mb-1' : 'badge bg-dark px-2 py-1 mb-1';

        $('#company-sig-target-name').text(nama || '-');
        $('#company-sig-target-role').text(role || '-');
        $('#company-sig-badge').attr('class', badgeClass).text(badgeText);

        $('#modal-company-sig').modal('show');
    });

    $('#modal-company-sig').on('shown.bs.modal', function () {
        initCanvas();
        clearCanvas();
    });

    // Terapkan Tanda Tangan dari Kanvas ke Form
    $('#btn-company-sig-apply').on('click', function () {
        if (!hasDrawn) {
            Swal.fire({ text: 'Silakan goreskan tanda tangan pada kanvas terlebih dahulu!', icon: 'warning' });
            return;
        }

        let pihak = $('#company-sig-target-pihak').val();
        let dataUrl = sigCanvas.toDataURL('image/png');

        if (pihak == 1) {
            $('#input_pihak1_signature').val(dataUrl);
            $('#view-sig-p1').attr('src', dataUrl).removeClass('d-none');
            $('#placeholder-sig-p1').addClass('d-none');
            $('#btn-delete-sig-p1').removeClass('d-none');
        } else if (pihak == 3) {
            $('#input_mengetahui_signature').val(dataUrl);
            $('#view-sig-p3').attr('src', dataUrl).removeClass('d-none');
            $('#placeholder-sig-p3').addClass('d-none');
            $('#btn-delete-sig-p3').removeClass('d-none');
        }

        $('#modal-company-sig').modal('hide');
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Tanda tangan siap disimpan bersama formulir profil.',
            showConfirmButton: false,
            timer: 2500
        });
    });

    // Hapus TTD
    $('#btn-delete-sig-p1, #btn-delete-sig-p3').on('click', function () {
        let pihak = $(this).data('pihak');
        let compId = $('input[name="id"]').val();
        let targetName = (pihak == 1) ? $('#form_pihak1_nama').val() : $('#form_mengetahui_nama').val();

        Swal.fire({
            title: 'Hapus Tanda Tangan?',
            text: 'Tanda tangan ' + targetName + ' akan dihapus dari profil perusahaan ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.value) {
                $.ajax({
                    type: 'POST',
                    url: '/utilitas/company-profile/delete-ttd',
                    data: { id: compId, pihak: pihak },
                    dataType: 'json',
                    success: function (r) {
                        if (r.status) {
                            if (pihak == 1) {
                                $('#input_pihak1_signature').val('');
                                $('#view-sig-p1').attr('src', '').addClass('d-none');
                                $('#placeholder-sig-p1').removeClass('d-none');
                                $('#btn-delete-sig-p1').addClass('d-none');
                            } else {
                                $('#input_mengetahui_signature').val('');
                                $('#view-sig-p3').attr('src', '').addClass('d-none');
                                $('#placeholder-sig-p3').removeClass('d-none');
                                $('#btn-delete-sig-p3').addClass('d-none');
                            }
                            Swal.fire({ text: r.message, icon: 'success', timer: 1500, showConfirmButton: false });
                        }
                    }
                });
            }
        });
    });

    // Simpan Form Utama
    $('#btn-save-company-profile').on('click', function () {
        let form = $('#form-company-profile')[0];
        let formData = new FormData(form);

        Swal.fire({
            title: 'Menyimpan Profil Perusahaan...',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: 'POST',
            url: '/utilitas/company-profile/save',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (res.status) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({ text: res.message || 'Gagal menyimpan profil perusahaan.', icon: 'error' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ text: 'Terjadi kesalahan sistem saat menyimpan profil.', icon: 'error' });
            }
        });
    });
});
</script>
<?= $this->endSection('script'); ?>
