<?php

namespace App\Controllers;

use App\Models\NotifikasiModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */

class BaseController extends Controller
{
	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = ['path', 'form', 'html', 'text', 'encrypter', 'utils_helper'];

	protected $MOD_ALIAS = null;
	protected $auth = null;
	protected $menulib = null;
	protected $ciqrcode = null;
	protected $mauth = null;
	protected $situs = null;
	protected $mcommon = null;
	protected $files = null;
	protected $db = null;
	protected $currentUser = null;
	protected $role = null;
	protected $encrypter = null;
	protected $crud;
	protected $_new, $_edit, $_delete, $_print, $_approve, $_view;
	protected $session;
	protected $validation;
	protected $data;

	protected $_userid;
	protected $_username;
	protected $_useremail;
	protected $_userfullname;
	protected $_roleid;
	protected $_rolename;

	/**
	 * Constructor.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.: $this->session = \Config\Services::session();
		$this->db      		= \Config\Database::connect();
		$this->session 		= \Config\Services::session();
		$this->auth 		= new \App\Libraries\CIonAuth();
		$this->menulib 		= new \App\Libraries\MenuLib();
		$this->mauth 		= new \IonAuth\Models\IonAuthModel();
		$this->mcommon 		= new \App\Models\Mcommon();
		$this->files 		= new \App\Models\FileModel();
		$this->situs 		= new \Modules\Utility\Models\SitusModel();
		$this->validation 	= \Config\Services::validation();

		$this->currentUser 	= $this->auth->user()->row();
		$this->role = $this->mauth->getUsersGroups()->getRow();

		$this->data['currentUser'] = $this->currentUser;
		$this->auth->setSession($this->role);
		$this->_checkAuthorization($this->MOD_ALIAS);
		$this->setMenu();
		$this->setSitus();
	}

	public function __get($property)
	{
		if ($property === 'ciqrcode') {
			if ($this->ciqrcode === null) {
				$this->ciqrcode = new \App\Libraries\CIQRCode();
			}
			return $this->ciqrcode;
		}
		if ($property === 'encrypter') {
			if ($this->encrypter === null) {
				$this->encrypter = \Config\Services::encrypter();
			}
			return $this->encrypter;
		}
		return parent::__get($property);
	}

	protected function _checkAuthorization($MOD_ALIAS)
	{
		$this->MOD_ALIAS = $MOD_ALIAS;
		$this->_view = false;
		$isAuthorized = false;
		$user_id = $this->session->get('user_id');
		$role_id = $this->session->get('role_id');

		if (!$this->auth->loggedIn()) {
			return redirect()->to('/auth/login');
		} else {
			if ($this->MOD_ALIAS == "MOD_HOME" or $this->mcommon->checkMenuAccess($role_id, $this->MOD_ALIAS)) {
				$isAuthorized = true;
				$this->_view = true;
			}
		}

		$this->_userid = $user_id; //$this->session->get('user_id');
		$this->_username = $this->session->get('identity');
		$this->_useremail = $this->session->get('email');
		$this->_roleid = $this->session->get('role_id');
		$this->_rolename = $this->session->get('role_name ');



		if (!$isAuthorized) {
			return redirect()->to('/');
		} else {
			$this->crud = $this->mcommon->getMenuAccessCRUD($role_id, $this->MOD_ALIAS);
			if ($this->crud != null) {
				$this->_new = $this->crud->allow_new;
				$this->_edit = $this->crud->allow_edit;
				$this->_delete = $this->crud->allow_delete;
				$this->_print = $this->crud->allow_print;
				$this->_approve = $this->crud->allow_approve;
			} else {
				$this->_new = false;
				$this->_edit =  false;
				$this->_delete =  false;
				$this->_print =  false;
				$this->_approve =  false;
			}
		}
	}

	public function setSitus()
	{
		$cache = \Config\Services::cache();
		$situsData = $cache->get('site_branding_data');
		if ($situsData === null) {
			$situs = $this->situs->getData();
			$situsData = [
				'name_app' => ($situs) ? $situs->name_app : "NO DATA",
				'deskripsi' => ($situs) ? $situs->description : "NO DATA",
				'judul' => ($situs) ? $situs->title : "NO DATA",
				'foot' => ($situs) ? $situs->footer : "NO DATA",
				'logo' => ($situs && $situs->file_id_logo && ($f = $this->files->getFiles($situs->file_id_logo))) ? $f->file_name : "",
				'logo_text' => ($situs && $situs->file_id_logo_text && ($f = $this->files->getFiles($situs->file_id_logo_text))) ? $f->file_name : "",
			];
			$cache->save('site_branding_data', $situsData, 3600);
		}

		$this->data['name_app'] = $situsData['name_app'];
		$this->data['deskripsi'] = $situsData['deskripsi'];
		$this->data['judul'] = $situsData['judul'];
		$this->data['foot'] = $situsData['foot'];
		$this->data['logo'] = $situsData['logo'];
		$this->data['logo_text'] = $situsData['logo_text'];

		$avatar = "";
		if ($this->currentUser && !empty($this->currentUser->file_id_photo)) {
			$f = $this->files->getFiles($this->currentUser->file_id_photo);
			if ($f) {
				$avatar = $f->file_name;
			}
		}
		$this->data['avatar'] = $avatar;
	}

	public function setMenu()
	{
		$role_id = $this->session->get('role_id');
		$cache = \Config\Services::cache();
		$cacheKey = 'user_menu_html_' . $role_id;
		$menu = $cache->get($cacheKey);

		if ($menu === null) {
			$menu = $this->menulib->showMenu();
			$cache->save($cacheKey, $menu, 3600);
		}

		if ($this->session->get('mode_penyamaran')) {
			$addMenu = "<li>
                <a href='" . base_url('go-to/admin') . "' class='waves-effect waves-dark'>
                <i class='icon-cursor'></i>
                <span class='hide-menu'> Go to Admin </span>
                </a>
            </li>";

			$menu = substr_replace($menu, $addMenu, -5, 0);
		}

		$this->data['menus'] = $menu;
	}

	public function _get_sess_csrf()
	{
		$key = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set('csrfkey', $key);
		$this->session->set('csrfvalue', $value);
		return array($key => $value);
	}

	public function validation_msg_error()
	{
		$validasi_error = [
			'required' => '{field} harus diisi',
			'min_length' => '{field} minimal harus 8 karakter',
			'matches' => '{field} dan konfirmasi password tidak sama',
			'numeric' => '{field} harus diisi dengan angka',
			'is_unique' => '{field} sudah terdaftar, silahkan masukan {field} yang lain'
		];

		return $validasi_error;
	}


	public function get_userid()
	{
		return $this->_userid;
	}

	public function get_username()
	{
		return $this->_username;
	}

	public function get_useremail()
	{
		return $this->_useremail;
	}

	public function get_userfullname()
	{
		return $this->_userfullname;
	}

	public function get_roleid()
	{
		return $this->_roleid;
	}

	public function get_rolename()
	{
		return $this->_rolename;
	}

}
