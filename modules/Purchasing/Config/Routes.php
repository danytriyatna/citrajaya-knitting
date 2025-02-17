<?php

$routes->group('purchasing/purchase-order', ['namespace' => 'Modules\Purchasing\Controllers'], static function ($routes) {
  $routes->get('/', 'PurchaseOrder::index');
  $routes->get('print/(:any)', 'PurchaseOrder::print/$1');
  $routes->post('list', 'PurchaseOrder::lists');
  $routes->post('save', 'PurchaseOrder::save');
  $routes->get('form', 'PurchaseOrder::form');
  $routes->get('form/(:any)', 'PurchaseOrder::form/$1');
  $routes->get('check-unit-price', 'PurchaseOrder::checkUnitPrice');
});

$routes->group('purchasing/receive-item', ['namespace' => 'Modules\Purchasing\Controllers'], static function ($routes) {
  $routes->get('/', 'ReceiveItem::index');
  $routes->get('form', 'ReceiveItem::form');
  $routes->get('print/(:any)', 'ReceiveItem::print/$1');
  $routes->post('list', 'ReceiveItem::lists');
  $routes->post('list-barang', 'ReceiveItem::listsBarang');
  $routes->post('save', 'ReceiveItem::save');
  $routes->get('form/(:any)', 'ReceiveItem::form/$1');
  $routes->get('check-lot', 'ReceiveItem::checkLotsNo');
});

$routes->group('purchasing/purchase-payment', ['namespace' => 'Modules\Purchasing\Controllers'], static function ($routes) {
  $routes->get('/', 'PurchasePayment::index');
  $routes->get('print/(:any)', 'PurchasePayment::print/$1');
  $routes->get('form', 'PurchasePayment::form');
  $routes->post('list', 'PurchasePayment::lists');
  $routes->post('save', 'PurchasePayment::save');
  $routes->get('form/(:any)', 'PurchasePayment::form/$1');
  $routes->get('list-payment', 'PurchasePayment::getDataPayment');
});
