<?php

// -- module ref daftar akun
$routes->group('keuangan/daftar_akun', ['namespace' => '\Modules\Keuangan\Controllers\Ref_coa'], function ($routes){
    $routes->get('/', '\Modules\Keuangan\Controllers\Ref_coa');
    $routes->post('list', '\Modules\Keuangan\Controllers\Ref_coa::lists');
    $routes->get('add', '\Modules\Keuangan\Controllers\Ref_coa::form');
    $routes->post('add', '\Modules\Keuangan\Controllers\Ref_coa::form');
    $routes->get('edit/(:any)', '\Modules\Keuangan\Controllers\Ref_coa::form/$1');
    $routes->post('edit/(:any)', '\Modules\Keuangan\Controllers\Ref_coa::form/$1');
    $routes->get('delete/(:any)', '\Modules\Keuangan\Controllers\Ref_coa::delete/$1');    
    $routes->get('get_org/(:any)', '\Modules\Keuangan\Controllers\Ref_coa::get_node_org/$1');    
});

// -- module transaksi beban biaya (kas keluar)
$routes->group('keuangan/transaksi_akun', ['namespace' => '\Modules\Keuangan\Controllers\Trans_akun'], function ($routes){
    $routes->get('/', '\Modules\Keuangan\Controllers\Trans_akun');
    $routes->post('list', '\Modules\Keuangan\Controllers\Trans_akun::lists');
    $routes->get('add', '\Modules\Keuangan\Controllers\Trans_akun::form');
    $routes->post('add', '\Modules\Keuangan\Controllers\Trans_akun::form');
    $routes->get('edit/(:any)', '\Modules\Keuangan\Controllers\Trans_akun::form/$1');
    $routes->post('edit/(:any)', '\Modules\Keuangan\Controllers\Trans_akun::form/$1');
    $routes->get('delete/(:any)', '\Modules\Keuangan\Controllers\Trans_akun::delete/$1');  
    $routes->get('get_org', '\Modules\Keuangan\Controllers\Trans_akun::get_node_org'); 
    $routes->get('get_reportx/(:any)', '\Modules\Keuangan\Controllers\Trans_akun::exp_mutasi/$1/$2');
    $routes->get('exp_mutasi/(:any)/(:any)', '\Modules\Keuangan\Controllers\Trans_akun::exp_mutasi/$1/$2');
});

// -- module transaksi kas/bank masuk
$routes->group('keuangan/transaksi_masuk', ['namespace' => '\Modules\Keuangan\Controllers\Trans_masuk'], function ($routes){
    $routes->get('/', '\Modules\Keuangan\Controllers\Trans_masuk');
    $routes->post('list', '\Modules\Keuangan\Controllers\Trans_masuk::lists');
    $routes->get('add', '\Modules\Keuangan\Controllers\Trans_masuk::form');
    $routes->post('add', '\Modules\Keuangan\Controllers\Trans_masuk::form');
    $routes->get('edit/(:any)', '\Modules\Keuangan\Controllers\Trans_masuk::form/$1');
    $routes->post('edit/(:any)', '\Modules\Keuangan\Controllers\Trans_masuk::form/$1');
    $routes->get('delete/(:any)', '\Modules\Keuangan\Controllers\Trans_masuk::delete/$1');  
    $routes->get('get_org', '\Modules\Keuangan\Controllers\Trans_masuk::get_node_org'); 
    $routes->get('exp_mutasi/(:any)/(:any)', '\Modules\Keuangan\Controllers\Trans_masuk::exp_mutasi/$1/$2');
});

// -- module transaksi beban biaya
$routes->group('keuangan/laporan_mutasi', ['namespace' => '\Modules\Keuangan\Controllers\Rpt_mutasi'], function ($routes){
    $routes->get('/', '\Modules\Keuangan\Controllers\Rpt_mutasi');
    $routes->post('list', '\Modules\Keuangan\Controllers\Rpt_mutasi::lists');
    $routes->post('preview_beban', '\Modules\Keuangan\Controllers\Rpt_mutasi::preview_beban');
    $routes->post('preview_mutasi', '\Modules\Keuangan\Controllers\Rpt_mutasi::preview_mutasi');
    $routes->get('getExcel/(:any)', '\Modules\Keuangan\Controllers\Rpt_mutasi::exp_mutasi/$1');
    $routes->get('getExcelNew/(:any)/(:any)/(:any)', '\Modules\Keuangan\Controllers\Rpt_mutasi::exp_mutasi_new/$1/$2/$3');
    $routes->get('getExcelAll/(:any)/(:any)/(:any)/(:any)', '\Modules\Keuangan\Controllers\Rpt_mutasi::exp_mutasi_all/$1/$2/$3/$4');

});

// -- module laba rugi
$routes->group('keuangan/laporan_laba', ['namespace' => '\Modules\Keuangan\Controllers\Rpt_laba_rugi'], function ($routes){
    $routes->get('/', '\Modules\Keuangan\Controllers\Rpt_laba_rugi');
    $routes->post('list', '\Modules\Keuangan\Controllers\Rpt_laba_rugi::lists');
    $routes->post('preview', '\Modules\Keuangan\Controllers\Rpt_laba_rugi::preview');
    $routes->get('getExcel/(:any)/(:any)/(:any)/(:any)', '\Modules\Keuangan\Controllers\Rpt_laba_rugi::exp_laba/$1/$2/$3/$4');
});

