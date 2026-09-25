<?php

$routes->group('sdm/absensi', ['namespace' => 'Modules\SDM\Controllers'], static function ($routes) {
	$routes->get('/', 'Absensi::index');
	$routes->post('list', 'Absensi::lists');
	$routes->post('dataGenerate', 'Absensi::generate_absen_karyawan');
	$routes->post('saveData', 'Absensi::simpanData');
	$routes->post('importData', 'Absensi::import_excel');
	$routes->get('export_absensi', 'Absensi::print_absensi_karyawan');
});

$routes->group('sdm/penggajian', ['namespace' => 'Modules\SDM\Controllers'], static function ($routes) {
	$routes->get('/', 'Penggajian::index');
	$routes->post('get_laporan', 'Penggajian::getDataPenggajian');
	$routes->post('list', 'Penggajian::lists');
	$routes->get('add', 'Penggajian::form');
	$routes->post('add', 'Penggajian::form');
	$routes->get('form/(:any)', 'Penggajian::form/$1');
	$routes->post('form/(:any)', 'Penggajian::form/$1');
	$routes->get('delete/(:any)', 'Penggajian::delete/$1');
	$routes->get('generate', 'Penggajian::printSlip_gaji');
	$routes->get('generate-excel', 'Penggajian::printSlipExcel_gaji');
	$routes->get('generate-rekap', 'Penggajian::printRekap_gaji');
	$routes->get('generate-rekap-excel', 'Penggajian::printRekapExcel_gaji');
	$routes->get('generate_kar', 'Penggajian::printSlip_gaji_karyawan');
	$routes->post('sync_tbmp_tpo', 'Penggajian::syncTbmpTpo');
});

$routes->group('sdm/borongan', ['namespace' => 'Modules\SDM\Controllers'], static function ($routes) {
	$routes->get('/', 'Borongan::index');
	$routes->post('list', 'Borongan::lists');
	$routes->get('generate_kar', 'Borongan::print_borongan');
});

$routes->group('sdm/lembur', ['namespace' => 'Modules\SDM\Controllers'], static function ($routes) {
	$routes->get('/', 'Lembur::index');
	$routes->post('list', 'Lembur::lists');
	$routes->get('add', 'Lembur::form');
	$routes->get('form', 'Lembur::form');
	$routes->get('form/(:any)', 'Lembur::form/$1');
	$routes->get('edit/(:any)', 'Lembur::form/$1');
	$routes->post('save', 'Lembur::save');
	$routes->post('approve1', 'Lembur::approve1');
	$routes->post('approve2', 'Lembur::approve2');
	$routes->post('cancel-approve', 'Lembur::cancelApprove');
	$routes->get('delete/(:any)', 'Lembur::delete/$1');
	$routes->post('delete', 'Lembur::delete');
	$routes->post('list-karyawan', 'Lembur::listKaryawan');
	$routes->post('sync-absensi', 'Lembur::syncAbsensi');
	$routes->get('print/(:any)', 'Lembur::printDoc/$1');
	$routes->get('print/(:any)/(:any)', 'Lembur::printDoc/$1/$2');
});

$routes->group('sdm/target-harian', ['namespace' => 'Modules\SDM\Controllers'], static function ($routes) {
	$routes->get('/', 'TargetHarian::index');
	$routes->get('', 'TargetHarian::index');
	$routes->post('list', 'TargetHarian::lists');
	$routes->get('form', 'TargetHarian::form');
	$routes->get('form/(:any)', 'TargetHarian::form/$1');
	$routes->get('get-doc-code', 'TargetHarian::get_doc_code');
	$routes->add('ping', 'TargetHarian::ping');
	$routes->post('save', 'TargetHarian::save');
	$routes->post('delete/(:any)', 'TargetHarian::delete/$1');
	$routes->get('print-rekap-tim', 'TargetHarian::printRekapTim');
	$routes->get('print-rapor-karyawan', 'TargetHarian::printRaporKaryawan');
	$routes->get('print/(:any)', 'TargetHarian::printDoc/$1');
	$routes->get('panduan', 'TargetHarian::panduan');
});

