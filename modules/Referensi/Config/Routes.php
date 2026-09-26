<?php
$routes->group("master-data/warna", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefWarna::index');

    $routes->post('list', 'RefWarna::lists');
    $routes->post('simpan', 'RefWarna::save');
    $routes->get('delete/(:any)', 'RefWarna::deactivate/$1');
});

$routes->group("master-data/ukuran", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefUkuran::index');

    $routes->post('list', 'RefUkuran::lists');
    $routes->post('simpan', 'RefUkuran::save');
    $routes->get('delete/(:any)', 'RefUkuran::deactivate/$1');
});


$routes->group("master-data/proses", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefProsesProduksi::index');
    $routes->post('list', 'RefProsesProduksi::lists');
    $routes->post('simpan', 'RefProsesProduksi::save');
    $routes->get('delete/(:any)', 'RefProsesProduksi::deactivate/$1');
    $routes->post('delete/(:any)', 'RefProsesProduksi::deactivate/$1');
    $routes->post('delete', 'RefProsesProduksi::deactivate');
    $routes->get('deactivate/(:any)', 'RefProsesProduksi::deactivate/$1');
    $routes->post('deactivate/(:any)', 'RefProsesProduksi::deactivate/$1');
});

$routes->group("master-data/konsumen", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefKonsumen::index');

    $routes->post('list', 'RefKonsumen::lists');
    $routes->post('simpan', 'RefKonsumen::save');
    $routes->post('simpanHarga', 'RefKonsumen::saveHarga');
    $routes->get('delete/(:any)', 'RefKonsumen::deactivate/$1');

    $routes->post('get_data_style', 'RefKonsumen::getStyle_data');
    $routes->post('get_data_proses', 'RefKonsumen::getStyleHarga_data');
});

$routes->group("master-data/karyawan", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefKaryawan::index');

    $routes->post('list', 'RefKaryawan::lists');
    $routes->post('simpan', 'RefKaryawan::save');
    // FIX (2026-08-08): sebelumnya delete/(:any) diarahkan ke deactivate()
    // - jadi tombol Hapus sama persis efeknya dengan toggle Nonaktif. Sekarang
    // delete() adalah status terpisah (active=2, "Dihapus", tidak pernah
    // tampil lagi di list), beda dari deactivate() (active=0, "Nonaktif",
    // masih tampil & bisa diaktifkan lagi).
    $routes->get('delete/(:any)', 'RefKaryawan::delete/$1');
    // TAMBAHAN (2026-08-08): route utk toggle status Aktif/Nonaktif di kolom
    // Status - sebelumnya belum terdaftar sama sekali, jadi klik badge-nya
    // 404 ("Master-data is not a valid controller name").
    $routes->get('activate/(:any)', 'RefKaryawan::activate/$1');
    $routes->get('deactivate/(:any)', 'RefKaryawan::deactivate/$1');

    $routes->post('get_data_style', 'RefKaryawan::getStyle_data');

    // Route Kontrak Kerja Karyawan
    $routes->post('check-kontrak', 'RefKaryawan::checkKontrak');
    $routes->post('generate-kontrak', 'RefKaryawan::generateKontrak');
    $routes->get('download-kontrak/(:any)', 'RefKaryawan::downloadKontrak/$1');
    $routes->post('upload-kontrak', 'RefKaryawan::uploadKontrak');
    $routes->post('simpan-ttd-digital', 'RefKaryawan::simpanTtdDigital');
    $routes->post('hapus-ttd-digital', 'RefKaryawan::hapusTtdDigital');
});

$routes->group("master-data/operator", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefOperator::index');

    $routes->post('list', 'RefOperator::lists');
    $routes->post('simpan', 'RefOperator::save');
    $routes->post('get_operator', 'RefOperator::getOperator');
    $routes->get('delete/(:any)', 'RefOperator::deactivate/$1');
});
$routes->group("master-data/gudang", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefGudang::index');

    $routes->post('list', 'RefGudang::lists');
    $routes->post('simpan', 'RefGudang::save');
    $routes->get('delete/(:any)', 'RefGudang::deactivate/$1');
});
$routes->group("master-data/satuan", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefSatuan::index');

    $routes->post('list', 'RefSatuan::lists');
    $routes->post('simpan', 'RefSatuan::save');
    $routes->get('delete/(:any)', 'RefSatuan::deactivate/$1');
});
$routes->group("master-data/rekening", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefRekening::index');

    $routes->post('list', 'RefRekening::lists');
    $routes->post('simpan', 'RefRekening::save');
    $routes->get('delete/(:any)', 'RefRekening::deactivate/$1');
    $routes->get('get_coa_list', 'RefRekening::get_coa_list');
});
$routes->group("master-data/jenis_barang", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefJenisBarang::index');

    $routes->post('list', 'RefJenisBarang::lists');
    $routes->post('simpan', 'RefJenisBarang::save');
    $routes->get('delete/(:any)', 'RefJenisBarang::deactivate/$1');
});
$routes->group("master-data/barang", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefBarang::index');

    $routes->post('list', 'RefBarang::lists');
    $routes->post('simpan', 'RefBarang::save');
    $routes->get('delete/(:any)', 'RefBarang::deactivate/$1');
});

$routes->group('master-data/produk', ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefProduk::index');
    $routes->get('form', 'RefProduk::form');
});
$routes->group("master-data/vendor", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefVendor::index');

    $routes->post('list', 'RefVendor::lists');
    $routes->post('simpan', 'RefVendor::save');
    $routes->get('delete/(:any)', 'RefVendor::deactivate/$1');
});

$routes->group("master-data/target-proses", ['namespace' => 'Modules\Referensi\Controllers'], static function ($routes) {
    $routes->get('/', 'RefTargetProses::index');

    $routes->post('list', 'RefTargetProses::lists');
    $routes->post('simpan', 'RefTargetProses::save');
    $routes->get('delete/(:any)', 'RefTargetProses::deactivate/$1');
    $routes->get('get_active_list', 'RefTargetProses::getActiveProsesList');
});

