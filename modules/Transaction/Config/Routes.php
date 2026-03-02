<?php

$routes->group('trans/sample', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'Sample::index');
  $routes->get('detail/(:any)', 'Sample::detail/$1');
  $routes->get('detail-qty/(:any)', 'Sample::detailQtyUkuran/$1/$1');
  $routes->post('list', 'Sample::lists');
  $routes->post('save', 'Sample::save');
  $routes->post('save-detail', 'Sample::saveDetail');
  $routes->get('delete/list(:any)', 'Sample::deleteList/$1');
  $routes->post('generate', 'Sample::generateQRCode');
  $routes->get('generate', 'Sample::getQrcode');
  $routes->post('delete/detail', 'Sample::deleteDetailList');
  $routes->post('get-style-konsumen', 'Sample::getDataStyleKonsumen');
  $routes->get('print/(:any)', 'Sample::print/$1');
});

$routes->group('trans/sales-order', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'SalesOrder::index');
  $routes->get('detail/(:any)', 'SalesOrder::detail/$1');
  $routes->get('detail-qty/(:any)', 'SalesOrder::detailQtyUkuran/$1/$1');
  $routes->post('list', 'SalesOrder::lists');
  $routes->post('save', 'SalesOrder::save');
  $routes->post('save-detail', 'SalesOrder::saveDetail');
  $routes->get('delete/list/(:any)', 'SalesOrder::deleteList/$1');
  $routes->get('delete/detail/(:any)', 'SalesOrder::deleteDetailList/$1');
  $routes->post('generate', 'SalesOrder::generateQRCode');
  $routes->get('generate', 'SalesOrder::getQrcode');
  $routes->post('getSample', 'SalesOrder::getSampleBuyer');
  $routes->get('print/(:any)', 'SalesOrder::print/$1');
  $routes->get('view', 'SalesOrder::view');
  $routes->get('print_excel_lists/(:any)/(:any)/(:any)', 'SalesOrder::print_excel_lists/$1/$2/$3');
});

$routes->group('trans/delivery-order', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'DeliveryOrder::index');
  $routes->post('list', 'DeliveryOrder::lists');
  $routes->post('list_produksi', 'DeliveryOrder::lists_produksi');
  $routes->get('add', 'DeliveryOrder::form');
  $routes->post('add', 'DeliveryOrder::form');
  $routes->get('form/(:any)', 'DeliveryOrder::form/$1');
  $routes->post('form/(:any)', 'DeliveryOrder::form/$1');
  $routes->post('det_produksi', 'DeliveryOrder::getDataProduksi');
  $routes->post('cari_produk', 'DeliveryOrder::getDataProduksiItem');
  $routes->get('print/(:any)', 'DeliveryOrder::print/$1');
  $routes->get('print_excel_lists/(:any)/(:any)/(:any)', 'DeliveryOrder::print_excel_lists/$1/$2/$3');
});

$routes->group('trans/work-order', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'WorkOrder::index');
  $routes->post('list', 'WorkOrder::lists');
  $routes->post('list_warna', 'WorkOrder::lists_detail');
  $routes->get('form/(:any)', 'WorkOrder::form/$1');
  $routes->post('save-warna', 'WorkOrder::saveWarna');
  $routes->post('save-data', 'WorkOrder::save');
  $routes->get('print/(:any)', 'WorkOrder::print/$1');
  $routes->get('generate', 'WorkOrder::getQrcode');
});

$routes->group('trans/production', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'Production::index');
  $routes->get('form', 'Production::form');
  $routes->get('form/(:any)', 'Production::form/$1');
  $routes->post('save', 'Production::save');
  $routes->post('list', 'Production::lists');
  $routes->post('list_ukuran', 'Production::lists_ukuran');
  $routes->post('list_ukuran_prod', 'Production::getDataProduksiUkuran');
  $routes->post('list_detail', 'Production::getDataListProd');
  $routes->post('cari_produk', 'Production::getDataProduksiItem');
  $routes->post('src_produk', 'Production::getCariProduk');
});

$routes->group('trans/sales-invoice', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'SalesInvoice::index');
  $routes->post('list', 'SalesInvoice::lists');
  $routes->post('list_produksi', 'SalesInvoice::lists_produksi');
  $routes->get('add', 'SalesInvoice::form');
  $routes->post('add', 'SalesInvoice::form');
  $routes->get('form/(:any)', 'SalesInvoice::form/$1');
  $routes->get('delete/(:any)', 'SalesInvoice::delete/$1');
  $routes->post('form/(:any)', 'SalesInvoice::form/$1');
  $routes->post('get_order', 'SalesInvoice::walkorder_user');
  $routes->post('cari_produk', 'SalesInvoice::getDataProduksiItem');
  $routes->get('print/(:any)', 'SalesInvoice::print/$1');
  $routes->get('print_excel_lists/(:any)/(:any)', 'SalesInvoice::print_excel_lists/$1/$2');
});

$routes->group('trans/customer-receipt', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'CustomerReceipt::index');
  $routes->post('list', 'CustomerReceipt::lists');
  $routes->get('add', 'CustomerReceipt::form');
  $routes->post('add', 'CustomerReceipt::form');
  $routes->get('form/(:any)', 'CustomerReceipt::form/$1');
  $routes->post('form/(:any)', 'CustomerReceipt::form/$1');
  $routes->post('getInvoice', 'CustomerReceipt::get_invoice');
  $routes->get('delete/(:any)', 'CustomerReceipt::delete/$1');
  $routes->get('print/(:any)', 'CustomerReceipt::print/$1');
});

$routes->group('trans/item-transfer', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'ItemTransfer::index');
  $routes->get('form_static', 'ItemTransfer::form_static_v2');
  $routes->post('list', 'ItemTransfer::lists');
  $routes->post('list-ref', 'ItemTransfer::lists_ref');
  $routes->post('list-so', 'ItemTransfer::listsSO');
  $routes->get('data-so', 'ItemTransfer::dataSO');
  $routes->get('print/(:any)', 'ItemTransfer::print/$1');
  $routes->get('form', 'ItemTransfer::form_static');
  $routes->get('edit/(:any)', 'ItemTransfer::form_static/$1');
  $routes->post('save', 'ItemTransfer::save');
  $routes->post('list-barang', 'ItemTransfer::lists_persediaan');
  $routes->get('print_excel_lists/(:any)/(:any)', 'ItemTransfer::print_excel_lists/$1/$2');

   $routes->post('cari_produk', 'ItemTransfer::getDataProduksiItem');
  $routes->post('src_produk', 'ItemTransfer::getCariProduk');
});

$routes->group('trans/receive-item', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'ReceiveItem::index');
  $routes->get('form', 'ReceiveItem::form');
});

$routes->group('trans/issue-item', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'IssueItem::index');
  $routes->get('form', 'IssueItem::form');
});
$routes->group('trans/incoming-goods', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'BarangMasuk::index');
  $routes->post('list', 'BarangMasuk::lists');
  $routes->get('form', 'BarangMasuk::form');
  $routes->get('edit/(:any)', 'BarangMasuk::form/$1');
  $routes->post('last-stock', 'IncomingGoods::getLastStock');
  $routes->post('save', 'BarangMasuk::save');
  $routes->get('print/(:any)', 'BarangMasuk::print/$1');
  $routes->get('print-faktur/(:any)', 'BarangMasuk::print_faktur/$1');
  $routes->get('print_excel_lists/(:any)/(:any)', 'BarangMasuk::print_excel_lists/$1/$2');
});
$routes->group('trans/outgoing-goods', ['namespace' => 'Modules\Transaction\Controllers'], static function ($routes) {
  $routes->get('/', 'BarangKeluar::index');
  $routes->post('list', 'BarangKeluar::lists');
  $routes->post('list-barang', 'BarangKeluar::listsBarang');
  $routes->get('form', 'BarangKeluar::form');
  $routes->get('edit/(:any)', 'BarangKeluar::form/$1');
  $routes->post('save', 'BarangKeluar::save');
  $routes->get('print/(:any)', 'BarangKeluar::print/$1');
});
