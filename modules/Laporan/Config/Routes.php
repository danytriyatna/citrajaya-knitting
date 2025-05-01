<?php

$routes->group('laporan/stock-card', ['namespace' => 'Modules\Laporan\Controllers'], static function ($routes) {
    $routes->get('/', 'LaporanStockCard::index');
    $routes->get('list', 'LaporanStockCard::getDataLaporanStockCard');
});
$routes->group('laporan/persediaan', ['namespace' => 'Modules\Laporan\Controllers'], static function ($routes) {
    $routes->get('/', 'LaporanPersediaan::index');
    $routes->get('list', 'LaporanPersediaan::getDataLaporanPersediaan');
    $routes->get('update-list', 'LaporanPersediaan::getUpdateDataLaporanPersediaan');
});
