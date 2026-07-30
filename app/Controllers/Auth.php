<?php 

namespace App\Controllers;
use Modules\Utility\Models\UserModel;
use App\Models\FileModel;

class Auth extends \IonAuth\Controllers\Auth
{
	protected $email;
	protected $auth;
	protected $user;
	protected $files;
	protected $situs;
	protected $session;

	function __construct()
	{
		parent::__construct();
		helper('path');        
		$this->session 		= \Config\Services::session();
		$this->email 		= \Config\Services::email();
		$this->auth 		= new \App\Libraries\CIonAuth();
		$this->validation 	= \Config\Services::validation();
		$this->user 		= new UserModel;
		$this->files		= new FileModel();
		$this->situs 		= new \Modules\Utility\Models\SitusModel();

		$cache = \Config\Services::cache();
		$situsAuthData = $cache->get('auth_site_data');

		if ($situsAuthData === null) {
			$situs = $this->situs->getData();
			$showBackgroundImg = ($situs && $situs->file_id_background_img) ? $this->files->getFiles($situs->file_id_background_img) : null;
			$situsAuthData = [
				'name_app' => ($situs) ? $situs->name_app : "",
				'deskripsi' => ($situs) ? $situs->description : "",
				'judul' => ($situs) ? $situs->title : "",
				'foot' => ($situs) ? $situs->footer : "",
				'view_background_img' => ($showBackgroundImg) ? $showBackgroundImg->file_name : ""
			];
			$cache->save('auth_site_data', $situsAuthData, 3600);
		}

		$this->data['name_app'] = $situsAuthData['name_app'];
		$this->data['deskripsi'] = $situsAuthData['deskripsi'];
		$this->data['judul'] = $situsAuthData['judul'];
		$this->data['foot'] = $situsAuthData['foot'];
		$this->data['view_background_img'] = $situsAuthData['view_background_img'];
	}

	protected $viewsFolder = 'auth';

	public function login()
	{
		$this->data['titlehead'] = 'Login';

		$this->validation->setRule('identity', str_replace(':', '', 'Email/Username:'), 'required');
		$this->validation->setRule('password', str_replace(':', '', 'Password:'), 'required');

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			if ($this->ionAuth->login($this->request->getVar('identity'), $this->request->getVar('password')))
			{
				$this->session->setFlashdata('message', $this->ionAuth->messages());
				return redirect()->to('/')->withCookies();
			}
			else
			{
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->back()->withInput();
			}
		}
		else
		{
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$this->data['identity'] = [
				'name'  => 'identity',
				'id'    => 'identity',
				'type'  => 'text',
				'value' => set_value('identity'),
			];

			$this->data['password'] = [
				'name' => 'password',
				'id'   => 'password',
				'type' => 'password',
			];

			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'login', $this->data);
		}
	}

	public function register() 
	{
		if ($this->auth->loggedIn()) {
            return redirect()->to('/');
        }

		$this->data['titlehead'] = "Registrasi User";

		return view('auth/register', $this->data);
	}

	public function proses_register() 
	{
		if ($this->auth->loggedIn()) {
            return redirect()->to('/');
        }

		$validasi_error = [
			'required' => '{field} harus diisi',
			'min_length' => '{field} minimal harus 8 karakter',
			'matches' => '{field} dan konfirmasi password tidak sama',
			'is_unique' => '{field} sudah terdaftar, silahkan masukan {field} yang lain'
		];

		$this->validation->setRules([
            'full_name'         => ['label' => 'Nama Lengkap', 'rules' => 'required|trim','errors' => $validasi_error],
            'email'             => ['label' => 'Email', 'rules' => 'required|valid_email|is_unique[sec_user.email]','errors' => $validasi_error],
            'username'          => ['label' => 'Username', 'rules' => 'required|is_unique[sec_user.username]','errors' => $validasi_error],
            'password'          => ['label' => 'Password', 'rules' => 'required|min_length[8]|matches[password_confirm]','errors' => $validasi_error],
            'password_confirm'  => ['label' => 'Konfirmasi Password', 'rules' => 'required','errors' => $validasi_error],
        ]);

		if ($this->validation->withRequest($this->request)->run())
		{       
            $email = $this->request->getPost('email');
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
			$additional_data["full_name"] = $this->request->getPost('full_name');
			
			$res = $this->auth->register($username, $password, $email, $additional_data);

			if($res){
				$message = view('auth/email/activate', $res);
				$this->email->clear();
				$this->email->setTo($res['email']);
				$this->email->setSubject("Aktivasi Akun");
				$this->email->setMessage($message);
				
				if($this->email->send()){
					$this->session->setFlashdata('message', "Registrasi berhasil, verifikasi akun sudah dikirim melalui email, Cek folder spam anda jika email aktivasi tidak ada di inbox");
					return redirect()->to('/auth/login');
				}
			}

			$this->session->setFlashdata('err', "Terjadi Kegagalan saat registrasi!");
			return redirect()->to('/register');
		}else{
			$this->data['errmsg'] = $this->validation->listErrors();
			$this->data['message'] = $this->session->getFlashdata('message');
			$this->session->setFlashdata('err', $this->data['errmsg']); 

			$this->session->setFlashdata('full_name', $this->request->getPost('full_name')); 
			$this->session->setFlashdata('email', $this->request->getPost('email')); 
			$this->session->setFlashdata('username', $this->request->getPost('username')); 

			return redirect()->to('/register');

		}
	}

    public function forgot_password()
	{
		$this->data['title'] = lang('Auth.forgot_password_heading');

        // setting validation rules by checking whether identity is username or email
		if ($this->configIonAuth->identity !== 'email')
		{
			$this->validation->setRule('identity', lang('Auth.forgot_password_identity_label'), 'required');
		}
		else
		{
			$this->validation->setRule('identity', lang('Auth.forgot_password_validation_email_label'), 'required|valid_email');
		}

		if (! ($this->request->getPost() && $this->validation->withRequest($this->request)->run()))
		{
			$this->data['type'] = $this->configIonAuth->identity;
			// setup the input
			$this->data['identity'] = [
				'name' => 'identity',
				'id'   => 'identity',
			];

			if ($this->configIonAuth->identity !== 'email')
			{
				$this->data['identity_label'] = lang('Auth.forgot_password_identity_label');
			}
			else
			{
				$this->data['identity_label'] = lang('Auth.forgot_password_email_identity_label');
			}

			// set any errors and display the form
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'forgot_password', $this->data);
		}
		else
		{
			$identityColumn = $this->configIonAuth->identity;
			$identity = $this->ionAuth->where($identityColumn, $this->request->getPost('identity'))->users()->row();

			if (empty($identity))
			{
				if ($this->configIonAuth->identity !== 'email')
				{
					$this->ionAuth->setError('Auth.forgot_password_identity_not_found');
				}
				else
				{
					$this->ionAuth->setError('Auth.forgot_password_email_not_found');
				}

				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to('/auth/forgot_password');
			}
			// run the forgotten password method to email an activation code to the user
			$forgotten = $this->ionAuth->forgottenPassword($identity->{$this->configIonAuth->identity});
            
            $message = "<h1>Reset Password</h1><br> 
            Anda telah melakukan permintaan untuk reset password. <br>
            Klik link berikut untuk melakukan reset password <a href=".base_url()."/auth/reset_password/".$forgotten['forgottenPasswordCode']." target='_blank'>Reset Password</a>";
            $sendEmail = $this->sendEmail($identity->email,'Reset Password',$message);

			if ($forgotten && $sendEmail)
			{
				// if there were no errors
				$this->session->setFlashdata('message', $this->ionAuth->messages());
				return redirect()->to('/auth/login'); //we should display a confirmation page here instead of the login page
			}
			else
			{
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to('/auth/forgot_password');
			}
		}
	}

	public function logout()
	{
		$this->data['titlehead'] = 'Logout';

		$this->ionAuth->logout();

		$this->session->setFlashdata('message', $this->ionAuth->messages());
		return redirect()->to('/auth/login')->withCookies();
	}

  private function sendEmail($to, $title, $message)
	{
		$this->email->setTo($to);
		$this->email->setSubject($title);
		$this->email->setMessage($message);

		if(! $this->email->send()){
			return false;
		}else{
			return true;
		}
	}

	public function req_captcha()
	{
		$cap = substr(str_shuffle("123456789"), 0, 4);
		$_SESSION['numcha'] = $cap;
		return $this->response->setContentType('text/plain')->setStatusCode(200)->setBody($cap);
	}

	public function validate_captcha()
	{
		$numcha = $this->request->getVar('numcha');

		if ($_SESSION['numcha'] === $numcha) {
			return $this->response->setStatusCode(200)->setJson(['status' => true]);
		} else {
			return $this->response->setStatusCode(401)->setJson(['status' => false]);
		}
	}
}
