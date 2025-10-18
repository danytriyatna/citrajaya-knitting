<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SecModulSeeder extends Seeder
{
	public function run()
	{
		$data = [
			[
				'id'		=> 1,
				'name' 		=> 'Home',
				'alias'    	=> 'MOD_HOME',
				'url'		=> '/',
				'icon_cls'	=> 'icon-home',
				'seq'		=> 0,
				'pid'		=> 0,
				'publish'	=> 1,
				'group'		=> 'main',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 1,
				'name' 		=> 'Dashboard',
				'alias'    	=> 'MOD_DASHBOARD',
				'url'		=> '/dashboard',
				'icon_cls'	=> 'icon-speedometer',
				'seq'		=> 1,
				'pid'		=> 0,
				'publish'	=> 1,
				'group'		=> 'main',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 2,
				'name' 		=> 'Utilitas',
				'alias'    	=> 'MOD_UTILITY',
				'url'		=> '#',
				'icon_cls'	=> 'icon-grid',
				'seq'		=> 99,
				'pid'		=> 0,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 3,
				'name' 		=> 'Daftar Pengguna',
				'alias'    	=> 'MOD_USERMANAGE',
				'url'		=> 'utilitas/users',
				'icon_cls'	=> '',
				'seq'		=> 1,
				'pid'		=> 2,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 4,
				'name' 		=> 'Group / Role',
				'alias'    	=> 'MOD_ROLEMANAGE',
				'url'		=> 'utilitas/roles',
				'icon_cls'	=> '',
				'seq'		=> 2,
				'pid'		=> 2,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 5,
				'name' 		=> 'Atur Otorisasi',
				'alias'    	=> 'MOD_PRIVMANAGE',
				'url'		=> 'utilitas/privileges',
				'icon_cls'	=> '',
				'seq'		=> 3,
				'pid'		=> 2,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 6,
				'name' 		=> 'Daftar Modul',
				'alias'    	=> 'MOD_MODULEMANAGE',
				'url'		=> 'utilitas/modules',
				'icon_cls'	=> '',
				'seq'		=> 4,
				'pid'		=> 2,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 7,
				'name' 		=> 'Log Aktivitas',
				'alias'    	=> 'MOD_LOGACTIVITY',
				'url'		=> 'utilitas/log-activity',
				'icon_cls'	=> '',
				'seq'		=> 5,
				'pid'		=> 2,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 8,
				'name' 		=> 'Setting Situs',
				'alias'    	=> 'MOD_SITUSMANAGE',
				'url'		=> 'utilitas/setting-situs',
				'icon_cls'	=> '',
				'seq'		=> 99,
				'pid'		=> 2,
				'publish'	=> 1,
				'group'		=> 'utility',
				'is_sapage'	=> 0,
			],
			[
				'id'		=> 9,
				'name' 		=> 'Go To',
				'alias'    	=> 'MOD_GOTO',
				'url'		=> '#',
				'icon_cls'	=> 'icon-cursor',
				'seq'		=> 100,
				'pid'		=> 0,
				'publish'	=> 1,
				'group'		=> 'vip',
				'is_sapage'	=> 0,
			],
			[
				'id' 		=> 10,
				'name' 		=> 'Login Sebagai',
				'alias' 	=> 'MOD_LOGINAS',
				'url' 		=> 'go-to/login-sebagai',
				'icon_cls' 	=> '',
				'seq' 		=> 1,
				'pid' 		=> 9,
				'publish' 	=> 1,
				'group' 	=> 'vip',
				'is_sapage' => 0
			]
		];

		$this->db->table('sec_modul')->insertBatch($data);
	}
}
