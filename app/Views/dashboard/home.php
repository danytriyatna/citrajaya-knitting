
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
            <li class="breadcrumb-item"><a href="./"><?= $name_app ?></a></li>
            <li class="breadcrumb-item active"><?= $titlehead ?></li>
          </ol>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="px-4 py-5 my-5 text-center">
    <img class="d-block mx-auto mb-4" src="assets/images/logo-image.png" alt="" height="157">
    <h1 class="display-5 fw-bold">Selamat Datang di Aplikasi Citra Jaya Knitting</h1>
    <div class="col-lg-6 mx-auto">
      <!-- <p class="lead mb-4">Quickly design and customize responsive mobile-first sites with Bootstrap, the world’s most popular front-end open source toolkit, featuring Sass variables and mixins, responsive grid system, extensive prebuilt components, and powerful JavaScript plugins.</p> -->
      <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
        <!-- <button type="button" class="btn btn-primary btn-lg px-4 gap-3">Primary button</button>
        <button type="button" class="btn btn-outline-secondary btn-lg px-4">Secondary</button> -->
      </div>
    </div>
  </div>
        </div>
    </div>
  </div>
<?= $this->endSection('content'); ?>

<?= $this->section('script') ?>
<?= $this->endSection('script') ?>