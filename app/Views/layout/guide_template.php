<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $guide_title ?? 'Panduan Operasional Sistem' ?> | Citra Jaya Knitting</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 15mm 15mm 15mm;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 11pt;
      line-height: 1.6;
      color: #2c3e50;
      margin: 0;
      padding: 20px;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      background-color: #f8fafc;
    }
    .guide-container {
      max-width: 900px;
      margin: 0 auto;
      background: #ffffff;
      padding: 35px 45px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    .no-print {
      background: #e0f2fe;
      border: 1px solid #7dd3fc;
      padding: 12px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-print {
      background: #0284c7;
      color: white;
      border: none;
      padding: 8px 18px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 13px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .btn-print:hover { background: #0369a1; }
    .btn-back {
      background: #64748b;
      color: white;
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .btn-back:hover { background: #475569; }
    @media print {
      body { background-color: #fff; padding: 0; }
      .guide-container { max-width: 100%; padding: 0; box-shadow: none; }
      .no-print { display: none !important; }
      .page-break { page-break-before: always; }
    }
    .header-doc {
      border-bottom: 3px solid #f1b500;
      padding-bottom: 14px;
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .header-doc h1 {
      margin: 0;
      font-size: 20pt;
      color: #1e293b;
      font-weight: 800;
    }
    .header-doc .subtitle {
      font-size: 12pt;
      color: #64748b;
      margin-top: 4px;
      font-weight: 600;
    }
    .header-doc .meta {
      text-align: right;
      font-size: 9.5pt;
      color: #64748b;
    }
    .section-title {
      font-size: 13pt;
      color: #0f172a;
      border-left: 4px solid #f1b500;
      padding-left: 10px;
      margin-top: 25px;
      margin-bottom: 12px;
      font-weight: 700;
    }
    .step-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 14px 18px;
      margin-bottom: 15px;
    }
    .step-header {
      font-weight: 700;
      font-size: 11.5pt;
      color: #0369a1;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
    }
    .step-num {
      background: #0284c7;
      color: white;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 10pt;
      margin-right: 8px;
      font-weight: bold;
    }
    .badge-code {
      background: #e2e8f0;
      color: #0f172a;
      padding: 2px 6px;
      border-radius: 4px;
      font-family: Consolas, monospace;
      font-size: 9.5pt;
      font-weight: 600;
    }
    .alert-tip {
      background: #f0fdf4;
      border-left: 4px solid #22c55e;
      padding: 12px 16px;
      border-radius: 4px;
      margin: 14px 0;
      font-size: 10pt;
      color: #166534;
    }
    .alert-warning-box {
      background: #fffbeb;
      border-left: 4px solid #f59e0b;
      padding: 12px 16px;
      border-radius: 4px;
      margin: 14px 0;
      font-size: 10pt;
      color: #92400e;
    }
    table.guide-table {
      width: 100%;
      border-collapse: collapse;
      margin: 12px 0;
      font-size: 10pt;
    }
    table.guide-table th {
      background: #f1b500;
      color: #000;
      padding: 8px 10px;
      border: 1px solid #d9a300;
      text-align: left;
    }
    table.guide-table td {
      padding: 8px 10px;
      border: 1px solid #cbd5e1;
      vertical-align: top;
    }
    .footer-doc {
      margin-top: 40px;
      padding-top: 10px;
      border-top: 1px solid #e2e8f0;
      font-size: 9pt;
      color: #94a3b8;
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>
<body>

  <div class="guide-container">
    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print">
      <div>
        <strong>📘 Buku Panduan Pengguna (User Guide):</strong> <?= $guide_subtitle ?? 'Panduan Operasional' ?>
      </div>
      <div>
        <a href="<?= $back_url ?? 'javascript:history.back()' ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Aplikasi</a>
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak / Simpan sebagai PDF</button>
      </div>
    </div>

    <!-- Document Header -->
    <div class="header-doc">
      <div>
        <h1>PANDUAN OPERASIONAL SISTEM</h1>
        <div class="subtitle"><?= $guide_subtitle ?? 'Modul Sistem' ?></div>
        <div style="font-size: 10pt; color: #64748b; margin-top: 2px;">PT. CITRA JAYA KNITTING</div>
      </div>
      <div class="meta">
        <strong>Versi Dokumen:</strong> <?= $guide_version ?? '1.0 (Resmi)' ?><br>
        <strong>Tanggal Rilis:</strong> <?= $guide_release_date ?? date('d F Y') ?><br>
        <strong>Modul:</strong> <?= $guide_module ?? 'Sistem' ?>
      </div>
    </div>

    <!-- Main Content Section -->
    <?= $this->renderSection('guide_content'); ?>

    <!-- Document Footer -->
    <div class="footer-doc">
      <div>PT. CITRA JAYA KNITTING &bull; Sistem Informasi Produksi & Manufaktur</div>
      <div>Dokumen Resmi Operasional</div>
    </div>
  </div>

</body>
</html>
