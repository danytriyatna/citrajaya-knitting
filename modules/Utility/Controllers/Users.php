<?php

namespace Modules\Utility\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Utility\Models\ModuleModel;
use Modules\Utility\Models\UserModel;
use Modules\Referensi\Models\PerusahaanModel;

class Users extends BaseController
{
    protected $users;
    protected $modules;
    protected $mPerusahaan;

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_USERMANAGE";
     
        $this->users = new UserModel();
        $this->modules = new ModuleModel();
        $this->mPerusahaan = new PerusahaanModel();
        $this->files = new FileModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        
        $this->data['titlehead'] = "Daftar Pengguna";

        $this->data['list_roles'] = $this->modules->getRoles();
        
        return view('\Modules\Utility\Views\users', $this->data);
    }

    public function profile()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Profile Pengguna";

        $this->validation->setRules([
            'full_name'         => ['label' => 'Nama Lengkap', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
            // 'username'          => ['label' => 'Username', 'rules' => 'required|is_unique[sec_user.username]', 'errors' =>  $this->validation_msg_error()],
            // 'email'             => ['label' => 'Email', 'rules' => 'required|valid_email|is_unique[sec_user.email]', 'errors' =>  $this->validation_msg_error()],
        ]);

		if($this->currentUser->user_id) {
            $users = $this->users->getUsers($this->currentUser->user_id); 
            $this->data['users'] = $users;
        }else{
            return redirect()->to('/auth/login');
        }

		if($_POST)
		{
			$full_name        = $this->request->getPost('full_name');
			$username         = $this->request->getPost('username');
			$email            = $this->request->getPost('email');
            
            $data = array (
                  'full_name'        => $full_name,
                //   'username'         => $username,
                //   'email'            => $email,
            );

            if ($this->validation->withRequest($this->request)->run() === TRUE AND $this->users->updateRecord($this->users->table,$data, 'id',$this->currentUser->user_id))
            {
                $this->session->setFlashdata('message', "Update profile berhasil.." );
                return redirect()->to('/profile');
            }else{
                $this->data['errmsg'] = $this->validation->listErrors();
                $this->data['message'] = $this->session->getFlashdata('message');
                $this->session->setFlashdata('err', $this->data['errmsg']); 
                
                $users->full_name       = $this->request->getPost('full_name');
                $users->username        = $this->request->getPost('username');
                $users->email           = $this->request->getPost('email');
            }
	
		}

        $this->data['username'] = array(
			'name'  => 'username',
			'id'    => 'username',
			'type'  => 'text',
			'value' => set_value('username', $users->username),
			'class' => 'form-control',
            'disabled' => 'disabled',
		);

        $this->data['email'] = array(
			'name'  => 'email',
			'id'    => 'email',
			'type'  => 'text',
			'value' => set_value('email', $users->email),
			'class' => 'form-control',
            'disabled' => 'disabled',
		);

        $this->data['full_name'] = array(
			'name'  => 'full_name',
			'id'    => 'full_name',
			'type'  => 'text',
			'value' => set_value('full_name', $users->full_name),
			'class' => 'form-control',
            'required' => '',
		);

        return view('\Modules\Utility\Views\profile', $this->data);
    }

    public function changePassword()
	{
		if (! $this->auth->loggedIn())
		{
			return redirect()->to('/auth/login');
		}
        
        $this->data['titlehead'] = "Ganti Password";
		
        $this->validation->setRules([
            'old'         => ['label' => 'Password Lama', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
            'new'         => ['label' => 'Password Baru', 'rules' => 'required|min_length[8]|matches[new_confirm]', 'errors' =>  $this->validation_msg_error()],
            'new_confirm' => ['label' => 'Konfirmasi Password', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
        ]);

		$user = $this->auth->user()->row();
        
        if($_POST)
		{
			$identity = $this->session->get('identity');

            if ($this->validation->withRequest($this->request)->run() === TRUE)
            {

                $change = $this->auth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'));

                if ($change)
                {
                    $this->session->setFlashdata('message', "Update password berhasil.." );
                    return redirect()->to('/auth/logout');
                }
                else
                {
                    $this->session->setFlashdata('err', $this->auth->errors());
                    return redirect()->to('/ganti-password');
                }

            }else{
                $this->data['errmsg'] = $this->validation->listErrors();
                $this->data['message'] = $this->session->getFlashdata('message');
                $this->session->setFlashdata('err', $this->data['errmsg']); 

				return redirect()->to('/ganti-password');
            }

		}
        
        $this->data['minPasswordLength'] = 8;
        $this->data['old'] = [
            'name' => 'old',
            'id'   => 'old',
            'type' => 'password',
            'class' => 'form-control',
            'required' => 'true'
        ];

        $this->data['new'] = [
            'name'    => 'new',
            'id'      => 'new',
            'type'    => 'password',
            'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$',
            // 'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
            'class' => 'form-control',
            'required' => 'true'
        ];

        $this->data['new_confirm'] = [
            'name'    => 'new_confirm',
            'id'      => 'new_confirm',
            'type'    => 'password',
            'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$',
            // 'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
            'class' => 'form-control',
            'required' => 'true'
        ];

        return view('\Modules\Utility\Views\change_password', $this->data);
	}

    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');
        $filter_role    = $this->request->getPost('filter_role');
        $filter_status  = $this->request->getPost('filter_status');

        $params = array(
            "filter_role" => $filter_role,
            "filter_status" => $filter_status
        );

        $results = $this->users->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->users->getDataCnt($filters, $params);
        $totaldata = $this->users->getDataCnt(null, $params);
        $maxpage = ceil($totalfiltered / $limit);

        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            $id = encrypt($row->id);

            $atr_edit = null; $atr_del = null; $btnAction = null;
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = 'utilitas/users/edit/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = 'utilitas/users/delete/';
                $atr_del['class'] = '';
                $atr_del['onclick'] = "return confirm('Hapus user ?')";
            }
            if($atr_edit || $atr_del)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del);

            $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
                "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

            array_push($build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    // "id" => $id,
                    "username" => $row->username,
                    "email" => $row->email,
                    "role" => $row->role_alias,
                    "nip" => $row->nip,
                    "full_name" => $row->full_name,
                    "last_login" => ($row->last_login) ? date("d M Y H:i:s", $row->last_login) : '',
                    "active" => $aktif,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    public function form()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } elseif(!$this->auth->isSuperAdmin() && !$this->auth->isAdmin()){
			throw new \Exception('You must be an administrator to view this page.');
        }

        $id = 0;
        $this->data['id'] = current_url(true)->getSegment(4);
        if (trim($this->data['id']) != ""){
            $id = decrypt($this->data['id']);
            $this->data['titlehead'] = "Edit User";
        }else{
            $this->data['titlehead'] = "Input User";
        }

        //validate form input
        $this->validation->setRules([
            'nip'               => ['label' => 'NIP', 'rules' => 'required|trim'],
            'full_name'         => ['label' => 'Nama Lengkap', 'rules' => 'required|trim'],
            //'prefix'            => ['label' => 'Sapaan', 'rules' => 'required'],
            'username'          => ['label' => 'Username', 'rules' => 'required'],
            'email'             => ['label' => 'Email', 'rules' => 'required|valid_email'],
            'role_id'           => ['label' => 'Role', 'rules' => 'required'],
        ]);

        if (trim($this->data['id']) == "") {
            $this->validation->setRule('password', 'Password', 'required|min_length[8]|matches[password_confirm]');
            $this->validation->setRule('password_confirm', 'Konfirmasi password', 'required');
        }

        $showPhoto = "";

        if( $id > 0 AND !$this->request->getPost('id')) {
            // retrieve data for edit
            $user = $this->users->getUsers($id);    
            $this->data['user'] = $user;     
            $showPhoto = ($this->files->getFiles($user->file_id_photo))?$this->files->getFiles($user->file_id_photo)->file_name:"";
        }else{
            $user = new \stdClass();            
            $user->nip = '';
            $user->full_name = '';
            $user->frefix = '';
            $user->file_id_photo = '';
            $user->username = '';
            $user->email = '';
            $user->role_id = '';
        }

        if (isset($_POST) && !empty($_POST))
        {         
            $nip            = $this->request->getPost('nip');
            $full_name      = $this->request->getPost('full_name');
            //$prefix         = $this->request->getPost('prefix');
            $username       = $this->request->getPost('username');
            $password       = $this->request->getPost('password');
            $email          = $this->request->getPost('email');
            $role_id        = $this->request->getPost('role_id');
            $id_perusahaan  = $this->request->getPost('id_perusahaan');
            $file_id_photo_old  = $this->request->getPost('file_id_photo_old');
                                   
            if( $id > 0 AND $this->request->getPost('id')) { // update
              
                $user->role_id = $role_id;
                $data = array(
                    'username' => $username,
                    'nip' => $nip,
                    'full_name' => $full_name,
                    'id_perusahaan' => $id_perusahaan,
                    //'prefix' => $prefix,
                    'email' => $email,
                );

                if ($this->request->getPost('password')) {
                    $data['password'] = trim($this->request->getPost('password'));
                }
                
                if($this->request->getFile('file_id_photo')->getName()){
                    $fileLogo       = $this->request->getFile('file_id_photo');
                    $fileName       = $fileLogo->getRandomName();
                    $originName     = $fileLogo->getName();
                    $fileType       = $fileLogo->getMimeType();
                    $fileSize       = $fileLogo->getSize();
            
                    $files = new FileModel();
                    
                    if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
                        $this->session->setFlashdata('err', "<br>File <b>Photo</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>");
                        return redirect()->to('/utilitas/users/edit/'.$this->data['id']);
                    }
            
                    $files->insert([
                        'file_name' => $fileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType,
                        'file_name_origin' => $originName,
                        'active'    => 1,
                    ]);
        
                    $file_id_photo = $files->insertID();
                    $fileLogo->move(WRITEPATH.'uploads/users/', $fileName);

                    if($file_id_photo_old !=""){
                        $nama_file = $files->where('id',$file_id_photo_old)->get()->getRow()->file_name;
                        if (file_exists(WRITEPATH.'uploads/users/'.$nama_file)) {
                            unlink(WRITEPATH.'uploads/users/'.$nama_file);
                        }

                        $files->delete(['id' => $file_id_photo_old]);
                    }
                }

                if($this->request->getFile('file_id_photo')->getName()){
                    $data['file_id_photo'] = $file_id_photo;
                }

                if ($this->validation->withRequest($this->request)->run() === TRUE) {

                    $this->auth->update($id, $data);
                    $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Update User");        
                    $this->users->updateRole($id, array('role_id' => $role_id));
                   
                    $this->session->setFlashdata('message', "Update user berhasil..");
                    return redirect()->to("/utilitas/users");

                } else {
                    //set the flash data error message if there is one
                    // $this->data['errmsg'] = (validation_errors() ? validation_errors() : ($this->mauth->errors() ? $this->mauth->errors() : ""));
                    $this->data['errmsg'] = $this->validation->listErrors();
                    $this->data['message'] = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('err', $this->data['errmsg']);
                    $this->data['username_edit'] = $username;

                    $user->username = $this->request->getPost('username');
                    $user->nip = $this->request->getPost('nip');
                    $user->full_name = $this->request->getPost('full_name');
                    //$user->prefix = $this->request->getPost('prefix');
                    $user->email = $this->request->getPost('email');
                    $user->role_id = $this->request->getPost('role_id');
                                   
                    $this->data['user'] = $user;
                    
                }
                
            } else { // insert
                $additional_data = array();
                
                if($this->request->getFile('file_id_photo')->getName()){
                    $fileLogo       = $this->request->getFile('file_id_photo');
                    $fileName       = $fileLogo->getRandomName();
                    $originName     = $fileLogo->getName();
                    $fileType       = $fileLogo->getMimeType();
                    $fileSize       = $fileLogo->getSize();
            
                    $files = new FileModel();

                    if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
                        $this->session->setFlashdata('err', "<br>File <b>Photo</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>");
                        return redirect()->to('/utilitas/users/add');
                    }
            
                    $files->insert([
                        'file_name' => $fileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType,
                        'file_name_origin' => $originName,
                        'active'    => 1,
                    ]);
        
                    $file_id_photo = $files->insertID();
                    $fileLogo->move(WRITEPATH.'uploads/users/', $fileName);
                }
                
                $additional_data["nip"] = $nip;
                $additional_data["full_name"] = $full_name;
                //$additional_data["prefix"] = $prefix;

                if($this->request->getFile('file_id_photo')->getName()){
                    $additional_data['file_id_photo'] = $file_id_photo;
                }
                               
                $groups[] = $role_id;
                
                if ($this->validation->withRequest($this->request)->run() === TRUE) {
                    $regis = $this->auth->register($username, $password, $email, $additional_data, $groups);
                    $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$regis['id'],"Tambah User");        
                    $this->session->setFlashdata('message', "Tambah user berhasil..");
                    return redirect()->to("/utilitas/users");
                } else {
                    //set the flash data error message if there is one
                    $this->data['errmsg'] = $this->validation->listErrors();
                    $this->data['message'] = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('err', $this->data['errmsg']);       

                    $user->username = $this->request->getPost('username');
                    $user->nip = $this->request->getPost('nip');
                    $user->full_name = $this->request->getPost('full_name');
                    //$user->prefix = $this->request->getPost('prefix');
                    $user->email = $this->request->getPost('email');
                    $user->role_id = $this->request->getPost('role_id');
                    $user->id_perusahaan = $this->request->getPost('id_perusahaan');
                    $user->photo = $this->request->getPost('photo');
                   // $user->prefix = $this->request->getPost('prefix');

                    $this->data['user'] = $user;
                }

            }
            
        }//endif POST

        //display the create user form
        $this->data['nip'] = array(
            'name' => 'nip',
            'id' => 'nip',
            'type' => 'text',
            'value' => set_value('nip', $user->nip),
            'class' => 'form-control',
            'placeholder' => 'Ketikkan NIP pengguna',
            'required' => 'true'
        );

        $this->data['full_name'] = array(
            'name' => 'full_name',
            'id' => 'full_name',
            'type' => 'text',
            'value' => set_value('full_name', $user->full_name),
            'class' => 'form-control',
            'placeholder' => 'Ketikkan nama lengkap pengguna',
            'required' => 'true'
        );

        $this->data['password'] = array(
            'name' => 'password',
            'id' => 'password',
            'type' => 'password',
            'class' => 'form-control',
            'placeholder' => 'Ketikkan password pengguna',
            'autocomplete' => 'new-password',
            'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$'
        );

        $this->data['password_confirm'] = array(
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'type' => 'password',
            'class' => 'form-control',
            'placeholder' => 'Ketikkan ulang password pengguna',
            'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$'
        );

        $this->data['email'] = array(
            'name' => 'email',
            'id' => 'email',
            'type' => 'email',
            'value' => set_value('email', $user->email),
            'class' => 'form-control',
            'placeholder' => 'Ketikkan alamat email pengguna',
            'required' => 'true',
        );

        $this->data['email_edit'] = $user->email;

        $this->data['username'] = array(
            'name' => 'username',
            'id' => 'username',
            'type' => 'text',
            'value' => set_value('username', $user->username),
            'class' => 'form-control',
            'placeholder' => 'Ketikkan username pengguna',
            'required' => 'true'
        );

        $this->data['file_id_photo'] = array(
            'name'  => 'file_id_photo',
            'id'    => 'file_id_photo',
            'type'  => 'text',
            'class' => 'form-control',
            'accept' => 'image/webp, image/jpeg, image/png'
        );

        $sortPerusahaan = [
            [
                'field' => 'nama_perusahaan',
                'dir' => 'ASC'
            ]
        ];
        $perusahaan = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
        foreach ($perusahaan as $row) {
            $list_perusahaan[$row->id] = $row->nama_perusahaan;
        }
        $this->data['perusahaan'] = $list_perusahaan;
        
        $this->data['file_id_photo_old'] = ($user->file_id_photo)?$user->file_id_photo:"";
        $this->data['view_photo'] = $showPhoto;
        $this->data['username_edit'] = $user->username;
        $this->data['role_id'] = set_value('role_id', $user->role_id);
        $this->data['csrf'] = $this->_get_sess_csrf();

        // option role or group user
        $data_roles = [];
        $data_roles = $this->users->getRoles();
        foreach ($data_roles as $row) {
            $list_role[$row->id] = $row->description;
        }
        $this->data['list_role'] = $list_role;

        // $list_prefix["Bpk."] = "Bpk."; 
        // $list_prefix["Ibu."] = "Ibu."; 
        
        // $this->data['list_prefix'] = $list_prefix;

        return view('\Modules\Utility\Views\users_form', $this->data);
    }

    public function activate($id)
    {
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        $activation = $this->mauth->activate($id);
        if ($activation) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"User Diaktifkan");        
            $this->session->setFlashdata('message', "User berhasil di aktifkan");
        } else {
            $this->session->setFlashdata('err', "User gagal di aktifkan !");
        }
		return redirect()->to('/utilitas/users');

    }

    public function deactivate($id = NULL)
    {
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }
        
        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to('/utilitas/users');
        }
        
        $deactivate = $this->mauth->deactivate($id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"User Dinonaktifkan");        
            $this->session->setFlashdata('message', "User berhasil di Non-aktifkan ");
        } else {
            $this->session->setFlashdata('err', "User gagal di Non-aktifkan !");
        }
		return redirect()->to('/utilitas/users');
    }

    public function delete($id = NULL)
    {
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to('/utilitas/users');
        }
        
        $res = $this->auth->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"User Dihapus");        
            $this->session->setFlashdata('message', "User berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "User gagal dihapus");
        }
		return redirect()->to('/utilitas/users');
    }

    public function getDataByRoleId()
    {
		$role_id = $this->request->getGet('role_id');
        $aktif = $this->request->getGet('aktif');

        $data = $this->users->getDataByRoleId($role_id, $aktif);
        
        return $this->response->setJSON(['status' => true, 'data' => $data]);

    }
}
