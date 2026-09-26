<?php namespace Config;

class IonAuth extends \IonAuth\Config\IonAuth
{
    // set your specific config
    // public $siteTitle                = 'Example.com';       // Site Title, example.com
    // public $adminEmail               = 'admin@example.com'; // Admin Email, admin@example.com
    // public $emailTemplates           = 'App\\Views\\auth\\email\\';
    // ...
	public $tables = [
		'users'          => 'sec_user',
		'groups'         => 'sec_role',
		'users_groups'   => 'sec_user_role',
		'login_attempts' => 'sec_login_attempts',
	];

    public $join = [
		'users'  => 'user_id',
		'groups' => 'role_id',
	];

	public $superAdminGroup = 'superadmin';
	public $adminGroup      = 'ADMIN APLIKASI';
	public $identity    	= 'username';             
	public $emailActivation = true;   
	
}