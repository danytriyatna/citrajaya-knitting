<?php

$routes->get('/profile', '\Modules\Utility\Controllers\Users::profile');
$routes->post('/profile', '\Modules\Utility\Controllers\Users::profile');

$routes->get('/ganti-password', '\Modules\Utility\Controllers\Users::changePassword');
$routes->post('/ganti-password', '\Modules\Utility\Controllers\Users::changePassword');

$routes->get('utilitas/users', '\Modules\Utility\Controllers\Users::index',  ['filter' => 'otorisasi:MOD_USERMANAGE']);
$routes->post('utilitas/users/lists', '\Modules\Utility\Controllers\Users::lists');
$routes->get('utilitas/users/add', '\Modules\Utility\Controllers\Users::form',  ['filter' => 'otorisasi:MOD_USERMANAGE']);
$routes->post('utilitas/users/add', '\Modules\Utility\Controllers\Users::form');
$routes->get('utilitas/users/edit/(:any)', '\Modules\Utility\Controllers\Users::form/$1',  ['filter' => 'otorisasi:MOD_USERMANAGE']);
$routes->post('utilitas/users/edit/(:any)', '\Modules\Utility\Controllers\Users::form/$1');
$routes->get('utilitas/users/delete/(:any)', '\Modules\Utility\Controllers\Users::delete/$1');
$routes->get('utilitas/users/activate/(:any)', '\Modules\Utility\Controllers\Users::activate/$1');
$routes->get('utilitas/users/deactivate/(:any)', '\Modules\Utility\Controllers\Users::deactivate/$1');
$routes->get('utilitas/users/list-by-role', '\Modules\Utility\Controllers\Users::getDataByRoleId');

$routes->get('utilitas/roles', '\Modules\Utility\Controllers\Roles::index', ['filter' => 'otorisasi:MOD_ROLEMANAGE'] );
$routes->post('utilitas/roles/lists', '\Modules\Utility\Controllers\Roles::lists');
$routes->get('utilitas/roles/add', '\Modules\Utility\Controllers\Roles::form', ['filter' => 'otorisasi:MOD_ROLEMANAGE'] );
$routes->post('utilitas/roles/add', '\Modules\Utility\Controllers\Roles::form');
$routes->get('utilitas/roles/edit/(:any)', '\Modules\Utility\Controllers\Roles::form/$1', ['filter' => 'otorisasi:MOD_ROLEMANAGE'] );
$routes->post('utilitas/roles/edit/(:any)', '\Modules\Utility\Controllers\Roles::form/$1');
$routes->get('utilitas/roles/delete/(:any)', '\Modules\Utility\Controllers\Roles::delete/$1');
$routes->get('utilitas/roles/activate/(:any)', '\Modules\Utility\Controllers\Roles::activate/$1');
$routes->get('utilitas/roles/deactivate/(:any)', '\Modules\Utility\Controllers\Roles::deactivate/$1');

$routes->get('utilitas/modules', '\Modules\Utility\Controllers\Modules::index', ['filter' => 'otorisasi:MOD_MODULEMANAGE']);
$routes->post('utilitas/modules/lists', '\Modules\Utility\Controllers\Modules::lists');
$routes->get('utilitas/modules/add', '\Modules\Utility\Controllers\Modules::form', ['filter' => 'otorisasi:MOD_MODULEMANAGE']);
$routes->post('utilitas/modules/add', '\Modules\Utility\Controllers\Modules::form');
$routes->post('utilitas/modules/edit/(:any)', '\Modules\Utility\Controllers\Modules::form/$1');
$routes->get('utilitas/modules/edit/(:any)', '\Modules\Utility\Controllers\Modules::form/$1', ['filter' => 'otorisasi:MOD_MODULEMANAGE']);
$routes->get('utilitas/modules/delete/(:any)', '\Modules\Utility\Controllers\Modules::delete/$1');
$routes->get('utilitas/modules/activate/(:any)', '\Modules\Utility\Controllers\Modules::activate/$1');
$routes->get('utilitas/modules/deactivate/(:any)', '\Modules\Utility\Controllers\Modules::deactivate/$1');

$routes->get('utilitas/privileges', '\Modules\Utility\Controllers\Privileges::index', ['filter' => 'otorisasi:MOD_PRIVMANAGE']);
$routes->get('utilitas/privileges/getprivby/(:any)', '\Modules\Utility\Controllers\Privileges::getPrivBy/$1');
$routes->post('utilitas/privileges/save', '\Modules\Utility\Controllers\Privileges::save');

$routes->get('utilitas/log-activity', '\Modules\Utility\Controllers\Logaktivitas::index', ['filter' => 'otorisasi:MOD_LOGACTIVITY']);
$routes->post('utilitas/log-activity/lists', '\Modules\Utility\Controllers\Logaktivitas::lists');

$routes->get('utilitas/setting-situs', '\Modules\Utility\Controllers\Situs::index', ['filter' => 'otorisasi:MOD_SITUSMANAGE']);
$routes->post('utilitas/setting-situs/save', '\Modules\Utility\Controllers\Situs::save');
$routes->post('utilitas/setting-situs/get-colors', '\Modules\Utility\Controllers\Situs::get_colors');

// Pengaturan Asisten AI (Khusus Superadmin di Modul Utilitas)
$routes->get('utilitas/asisten-ai', '\Modules\Assistant\Controllers\Kania::settings', ['filter' => 'otorisasi:MOD_ASISTEN_AI']);
$routes->post('utilitas/asisten-ai/save', '\Modules\Assistant\Controllers\Kania::saveSettings', ['filter' => 'otorisasi:MOD_ASISTEN_AI']);

// Company Profile / Profil Perusahaan & Pejabat Penandatangan
$routes->get('utilitas/company-profile', '\Modules\Utility\Controllers\CompanyProfile::index', ['filter' => 'otorisasi:MOD_COMPANY_PROFILE']);
$routes->post('utilitas/company-profile/save', '\Modules\Utility\Controllers\CompanyProfile::save');
$routes->post('utilitas/company-profile/delete-ttd', '\Modules\Utility\Controllers\CompanyProfile::deleteTtd');