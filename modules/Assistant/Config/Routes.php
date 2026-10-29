<?php

$routes->group('assistant', ['namespace' => 'Modules\Assistant\Controllers'], static function ($routes) {
    // Endpoint Webhook yang dipanggil oleh WhatsApp Gateway Service saat ada chat masuk
    $routes->post('webhook', 'Kania::webhook');

    // Halaman Scan QR Code WhatsApp KANIA
    $routes->get('qr', 'Kania::qr');

    // Endpoint Test Interaktif melalui Browser (untuk pengujian langsung tanpa WA)
    $routes->get('test', 'Kania::testView');
    $routes->post('test-ask', 'Kania::testAsk');

    // Status service
    $routes->get('status', 'Kania::status');

    // Halaman Pengaturan WhatsApp & AI Bot via Web
    $routes->get('settings', 'Kania::settings');
    $routes->post('settings-save', 'Kania::saveSettings');
});
