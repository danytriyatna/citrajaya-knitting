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
	$routes->get('generate_kar', 'Penggajian::printSlip_gaji_karyawan');
});

$routes->group('sdm/borongan', ['namespace' => 'Modules\SDM\Controllers'], static function ($routes) {
	$routes->get('/', 'Borongan::index');
	$routes->post('list', 'Borongan::lists');
	$routes->get('generate_kar', 'Borongan::print_borongan');
});
