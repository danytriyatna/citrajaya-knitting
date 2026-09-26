<!DOCTYPE html>
<html lang="en">

<head>
  <base href="<?= base_url(); ?>">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="title" property="og:title" content="<?= $name_app ?>">
  <meta name="image" property="og:image" content="assets/images/og-image.webp">
  <meta name="description" property="og:description" content="<?= $deskripsi ?>">
  <meta name="author" property="og:author" content="Pejuang Rupiah">
  <link rel="icon" type="image/png" sizes="64x64" href="assets/images/favicon.png">
  <title><?= (isset($titlehead) ? $titlehead : ""); ?> &mdash; <?= $judul ?></title>
  <link href="<?= auto_version('assets/css/style.min.css') ?>" rel="stylesheet">
  <!-- Tabulator - https://tabulator.info/docs/5.4 -->
  <link href="assets/node_modules/tabulator-tables/dist/css/tabulator_bootstrap5.min.css" rel="stylesheet">
  <!-- DataTables - https://datatables.net/ -->
  <link href="assets/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="assets/node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css" rel="stylesheet">
  <!-- select2 - https://select2.org/ -->
  <link href="assets/node_modules/select2/dist/css/select2.min.css" rel="stylesheet">
  <!-- sweetalert2 - https://sweetalert2.github.io/ -->
  <link href="assets/node_modules/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
  <!-- toastr - https://github.com/CodeSeven/toastr -->
  <link href="assets/node_modules/toastr/build/toastr.min.css" rel="stylesheet">
  <!-- Bootstrap Datepicker - https://bootstrap-datepicker.readthedocs.io/en/latest/index.html -->
  <link href="assets/node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
  <!-- Custom CSS, generated from /sass -->
  <!-- politespace - https://github.com/filamentgroup/politespace -->
  <link href="assets/node_modules/politespace/dist/politespace.css" rel="stylesheet">

  <link href="assets/node_modules/jquery-ui-bundle/jquery-ui.min.css" rel="stylesheet">
  <link href="assets/node_modules/jquery-ui-bundle/jquery-ui.theme.min.css" rel="stylesheet">

  <link href="<?= auto_version('assets/css/select2totree.css') ?>" rel="stylesheet">

  <link href="<?= auto_version('assets/css/custom.min.css') ?>" rel="stylesheet">
  <!-- Highlight kuning muda utk field pengisian/editable, semua menu (lihat komentar di file) -->
  <link href="<?= auto_version('assets/css/editable-highlight.css') ?>" rel="stylesheet">
  <script>
    var baseUrl = '<?= base_url() ?>'
  </script>
</head>

<body class="skin-blue fixed-layout custom-layout-0">

  <?= $this->renderSection('modal') ?>

  <div class="preloader">
    <div class="loader">
      <div class="loader__figure"></div>
      <p class="loader__label"><?= $judul ?></p>
    </div>
  </div>

  <div id="main-wrapper">

    <header class="topbar">
      <?= $this->include('layout/_topbar') ?>
    </header>

    <?= $this->include('layout/_sidebar') ?>

    <div class="page-wrapper">
      <main role="main" class="flex-shrink-0">
        <div class="preloader iloader d-none">
          <div class="loader">
            <div class="loader__figure"></div>
          </div>
        </div>
        <?= $this->renderSection('content') ?>
      </main>
    </div>

    <?= $this->include('layout/_footer') ?>

    <a href="javascript:void(0)" class="scroll-top" style="display: none;">
      <i class="fa fa-chevron-up"></i>
    </a>

  </div>
  <script>
    const BASE_URL = '<?= base_url(); ?>';
  </script>
  <script src="assets/node_modules/jquery/dist/jquery.min.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <!-- Tabulator - https://tabulator.info/docs/5.4 -->
  <script src="assets/node_modules/tabulator-tables/dist/js/tabulator.min.js"></script>
  <!-- DataTables - https://datatables.net/ -->
  <script src="assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="assets/node_modules/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
  <script src="assets/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
  <script src="assets/node_modules/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
  <!-- select2 - https://select2.org/ -->
  <script src="assets/node_modules/select2/dist/js/select2.min.js"></script>
  <!-- ECharts - https://echarts.apache.org/handbook/en/get-started/ -->
  <script src="assets/node_modules/echarts/dist/echarts.min.js"></script>
  <!-- sweetalert2 - https://sweetalert2.github.io/ -->
  <script src="assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
  <!-- toastr - https://github.com/CodeSeven/toastr -->
  <script src="assets/node_modules/toastr/build/toastr.min.js"></script>
  <!-- CKEditor 4 - https://ckeditor.com/ckeditor-4/ -->
  <script src="assets/node_modules/ckeditor4/ckeditor.js"></script>
  <!-- Bootstrap Datepicker - https://bootstrap-datepicker.readthedocs.io/en/latest/index.html -->
  <script src="assets/node_modules/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="assets/node_modules/bootstrap-datepicker/js/locales/bootstrap-datepicker.id.js"></script>
  <!-- politespace - https://github.com/filamentgroup/politespace -->
  <script src="assets/node_modules/politespace/dist/politespace.js"></script>
  <script src="assets/node_modules/politespace/dist/politespace-init.js"></script>

  <!-- Theme JS -->
  <script src="assets/js/perfect-scrollbar.jquery.min.js"></script>
  <script src="assets/js/waves.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/script.min.js"></script>
  <script src="<?= auto_version('assets/js/custom.js') ?>"></script>
  <script src="assets/js/jquery.mask.min.js"></script>
  <script src="assets/node_modules/jquery-ui-bundle/jquery-ui.min.js"></script>
  <script src="assets/js/select2totree.js"></script>
  <!-- Custom JS -->

  <script>
    showWelcomeToast("<?= $currentUser->full_name ?>");

    function formatLocaleDate(localeDate) {

      var months = {
        "Januari": "01",
        "January": "01",
        "Februari": "02",
        "February": "02",
        "Maret": "03",
        "March": "03",
        "April": "04",
        "Mei": "05",
        "May": "05",
        "Juni": "06",
        "June": "06",
        "Juli": "07",
        "July": "07",
        "Agustus": "08",
        "August": "08",
        "September": "09",
        "Oktober": "10",
        "October": "10",
        "November": "11",
        "Desember": "12",
        "December": "12",
      };

      var parts = localeDate.split(" ");
      var day = parts[0].padStart(2, '0');
      var month = months[parts[1]];
      var year = parts[2];

      return `${year}-${month}-${day}`;
    }

    function formatterDate($date) {
      let newDate = new Date($date);
      const options = {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      };
      const formattedDate = newDate.toLocaleDateString('id-ID', options);
      return formattedDate
    }
  </script>

  <?php
  ob_start();
  $this->renderSection('script');
  $renderedScript = ob_get_clean();

  // Otomatis tambahkan parameter versi (?v=filemtime) pada semua script lokal
  // agar browser pengguna otomatis mengunduh file baru setiap ada update,
  // tanpa perlu repot CTRL+F5 atau CTRL+Shift+R.
  if (!empty($renderedScript)) {
      $renderedScript = preg_replace_callback(
          '/<script\s+([^>]*?)src=["\']([^"\']+)["\']([^>]*)>/i',
          function ($matches) {
              $preAttr  = $matches[1];
              $src      = $matches[2];
              $postAttr = $matches[3];

              // Abaikan library CDN / external URL murni
              if (preg_match('/^(https?:)?\/\//i', $src)) {
                  $baseUrl = function_exists('base_url') ? base_url() : '';
                  if (empty($baseUrl) || !str_contains($src, $baseUrl)) {
                      return $matches[0];
                  }
              }

              $newSrc = function_exists('auto_version') ? auto_version($src) : $src;
              return "<script {$preAttr}src=\"{$newSrc}\"{$postAttr}>";
          },
          $renderedScript
      );
  }

  echo $renderedScript;
  ?>

</body>

</html>