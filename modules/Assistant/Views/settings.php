<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-3">
  <!-- Header Title -->
  <div class="row mb-3 align-items-center">
    <div class="col-md-8">
      <h3 class="fw-bold text-dark mb-1"><i class="ti-settings me-2 text-primary"></i>Pengaturan Asisten AI & WhatsApp Gateway</h3>
      <p class="text-muted small mb-0">Kelola nomor WhatsApp, hak akses pengirim, nama bot, dan pantau status koneksi WhatsApp secara real-time.</p>
    </div>
    <div class="col-md-4 text-md-end mt-2 mt-md-0">
      <a href="<?= base_url('assistant/test') ?>" class="btn btn-outline-primary btn-sm me-1"><i class="ti-comments me-1"></i>Test Chat</a>
      <a href="<?= base_url('assistant/qr') ?>" target="_blank" class="btn btn-success btn-sm"><i class="ti-mobile me-1"></i>Scan QR Code</a>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="ti-check me-2"></i><?= session()->getFlashdata('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="row">
    <!-- Kolom Kiri: Form Konfigurasi -->
    <div class="col-lg-7 mb-4">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
          <h5 class="card-title mb-0 fw-bold text-dark"><i class="ti-server me-2 text-info"></i>Konfigurasi Asisten & Hak Akses</h5>
        </div>
        <div class="card-body p-4">
          <form action="<?= base_url('assistant/settings-save') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Nama Bot & Kata Pemicu -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-dark">Nama Asisten AI</label>
                <input type="text" name="botName" class="form-control" value="<?= esc($config->botName ?? 'KANIA') ?>" required>
                <div class="form-text small">Nama resmi asisten saat menjawab pesan.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-dark">Kata Pemicu (Trigger Word)</label>
                <input type="text" name="triggerWord" class="form-control" value="<?= esc($config->triggerWord ?? 'Kania') ?>" required>
                <div class="form-text small">Awalan pesan WA agar bot merespon (misal: "Kania").</div>
              </div>
            </div>

            <!-- Provider AI Utama & Failover Cadangan -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-dark">AI Engine Utama (Provider-Agnostic)</label>
                <select name="aiProvider" class="form-select">
                  <option value="groq" <?= ($config->aiProvider ?? 'groq') === 'groq' ? 'selected' : '' ?>>Groq Cloud (100% Free, Flagship 120B / Qwen)</option>
                  <option value="gemini" <?= ($config->aiProvider ?? '') === 'gemini' ? 'selected' : '' ?>>Google Gemini (Cadangan / Multimodal)</option>
                  <option value="openai_compatible" <?= ($config->aiProvider ?? '') === 'openai_compatible' ? 'selected' : '' ?>>Custom OpenAI-Compatible</option>
                </select>
                <div class="form-text small">Engine AI utama yang memproses penalaran & data CJK.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-dark">Model Groq / OpenAI-Compatible</label>
                <input type="text" name="groqModel" class="form-control" value="<?= esc($config->groqModel ?? 'openai/gpt-oss-120b') ?>" required>
                <div class="form-text small">Rekomendasi: `openai/gpt-oss-120b`, `qwen/qwen3.8-27b`, `openai/gpt-oss-20b`.</div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-dark">Model Cadangan Gemini</label>
                <input type="text" name="geminiModel" class="form-control" value="<?= esc($config->geminiModel ?? 'gemini-3.8-flash') ?>" required>
                <div class="form-text small">Aktif otomatis jika provider utama limit/offline.</div>
              </div>
              <div class="col-md-6 pt-md-4">
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" name="enableFallback" id="enableFallback" value="1" <?= !empty($config->enableFallback) ? 'checked' : '' ?>>
                  <label class="form-check-label fw-bold text-dark small" for="enableFallback">
                    Aktifkan Auto-Failover (Multi-Tier Fallback)
                  </label>
                </div>
                <div class="form-text small">Beralih otomatis ke Gemini jika Groq sedang antre.</div>
              </div>
            </div>

            <hr class="my-4 text-muted">

            <!-- Hak Akses & Whitelist Nomor WA -->
            <div class="mb-3">
              <label class="form-label fw-bold small text-dark">Daftar Nomor WhatsApp yang Diizinkan (Whitelist)</label>
              <textarea name="allowedPhoneNumbers" rows="3" class="form-control font-monospace" placeholder="085111470089, 081322900089"><?= esc(implode("\n", $config->allowedPhoneNumbers ?? [])) ?></textarea>
              <div class="form-text small">Tulis 1 nomor per baris atau pisahkan dengan koma (format lokal `08...` atau internasional `628...`).</div>
            </div>

            <!-- Toggle Wajib Kata Pemicu -->
            <div class="mb-3 form-check form-switch">
              <input class="form-check-input" type="checkbox" name="requireTriggerWord" id="requireTriggerWord" value="1" <?= !empty($config->requireTriggerWord) ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold text-dark small" for="requireTriggerWord">
                Wajib Awali Chat dengan Kata Pemicu (Trigger Word)
              </label>
              <div class="text-muted small">Jika <b>tidak dicentang (Direkomendasikan)</b>, setiap chat yang masuk ke nomor ini otomatis dijawab langsung oleh KANIA tanpa perlu menyebut nama bot terlebih dahulu.</div>
            </div>

            <!-- Toggle Izinkan Semua Nomor -->
            <div class="mb-4 form-check form-switch">
              <input class="form-check-input" type="checkbox" name="allowAnyNumber" id="allowAnyNumber" value="1" <?= !empty($config->allowAnyNumberIfEmptyWhitelist) ? 'checked' : '' ?>>
              <label class="form-check-label fw-bold text-dark small" for="allowAnyNumber">
                Buka Akses Bebas (Testing Mode)
              </label>
              <div class="text-muted small">Jika dicentang, semua nomor yang chat via WA akan dilayani tanpa dibatasi whitelist (sangat cocok untuk masa pengujian data).</div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary py-2 fw-bold"><i class="ti-save me-2"></i>Simpan Perubahan Pengaturan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan: Status Koneksi & Quick QR -->
    <div class="col-lg-5 mb-4">
      <!-- Status Gateway Card -->
      <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
          <h5 class="card-title mb-0 fw-bold text-dark"><i class="ti-signal me-2 text-success"></i>Status WhatsApp Gateway</h5>
        </div>
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="p-3 bg-light-primary rounded-circle me-3">
              <i class="ti-mobile fs-3 text-primary"></i>
            </div>
            <div>
              <div class="small text-muted">Nomor Bot Aktif (GENBI Core):</div>
              <div class="fw-bold fs-5 text-dark">0851-1147-0089</div>
            </div>
          </div>

          <div class="alert alert-light border py-2 px-3 small mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Service Port:</span>
              <span class="fw-bold">127.0.0.1:3300</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Mode Whitelist:</span>
              <span class="badge bg-<?= !empty($config->allowAnyNumberIfEmptyWhitelist) ? 'warning text-dark' : 'success' ?>">
                <?= !empty($config->allowAnyNumberIfEmptyWhitelist) ? 'Bebas (Testing)' : 'Strict Whitelist' ?>
              </span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Engine Analisis:</span>
              <span class="fw-bold text-primary">Executive Dynamic SQL</span>
            </div>
          </div>

          <div class="d-grid">
            <a href="<?= base_url('assistant/qr') ?>" target="_blank" class="btn btn-outline-success btn-sm py-2">
              <i class="ti-fullscreen me-1"></i>Buka Layar Scan QR WhatsApp
            </a>
          </div>
        </div>
      </div>

      <!-- Quick Tips Card -->
      <div class="card border-0 shadow-sm rounded-3 bg-light">
        <div class="card-body p-3 small text-muted">
          <div class="fw-bold text-dark mb-1"><i class="ti-info-alt me-1 text-primary"></i>Format Perintah WhatsApp:</div>
          <p class="text-muted small mb-2">Langsung tanyakan tanpa perlu mengetik kata pemicu. Contoh:</p>
          <p class="mb-1">• <b>Cek Stok:</b> <code>Cek stok katun putih</code></p>
          <p class="mb-1">• <b>Analisa SPK:</b> <code>Status SPK WRD2609001</code></p>
          <p class="mb-0">• <b>Laba Rugi:</b> <code>Estimasi laba rugi bulan ini</code></p>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
