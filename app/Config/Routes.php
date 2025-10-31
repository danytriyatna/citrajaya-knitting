<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index',  ['filter' => 'otorisasi:MOD_HOME']);
$routes->get('/dashboard', 'Dashboard::index',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->post('/req_captcha', 'Auth::req_captcha');
$routes->post('/validate_captcha', 'Auth::validate_captcha');
$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::proses_register');
$routes->get('/forgot_password', 'Auth::forgot_password');

$routes->post('/dashboard/list_order', 'Dashboard::lists',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->post('/dashboard/list_invoice', 'Dashboard::lists_inv',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->post('/dashboard/list_po', 'Dashboard::lists_po',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->post('/dashboard/list_saldo', 'Dashboard::lists_saldo',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->get('/dashboard/update_saldo/(:any)/(:any)', 'Dashboard::update_saldo/$1/$2',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->get('/dashboard/list_laba', 'Dashboard::lists_laba',  ['filter' => 'otorisasi:MOD_DASHBOARD']);
$routes->get('/dashboard/list_grafik', 'Dashboard::lists_grafik',  ['filter' => 'otorisasi:MOD_DASHBOARD']);

$routes->post('api/login', 'API\Auth::login');

$routes->group('api/v1', ['filter' => 'JWTAuth', 'namespace' => 'App\Controllers\API'], function($routes)
{
	$routes->get('get-user', 'Testjwt::user', ['namespace' => 'App\Controllers\API\user']);
	// $routes->get('get-user', 'user/Testjwt::user');
	
	// masukan route api disini jika memerlukan protect JWT
});

$routes->get('/auth/login', 'Auth::login', ['filter' => 'admin-auth']);

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Include Modules Routes Files
 * --------------------------------------------------------------------
 */
if (file_exists(ROOTPATH.'modules')) {
	$modulesPath = ROOTPATH.'modules/';
	$modules = scandir($modulesPath);

	foreach ($modules as $module) {
		if ($module === '.' || $module === '..') continue;
		if (is_dir($modulesPath) . '/' . $module) {
			$routesPath = $modulesPath . $module . '/Config/Routes.php';
			if (file_exists($routesPath)) {
				require($routesPath);
			} else {
				continue;
			}
		}
	}
}