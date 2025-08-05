<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\VendorModel;

class RefVendor extends BaseController
{
    protected $mVendor;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/vendor';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_VENDOR";

        $this->mVendor = new  VendorModel();
        $this->files  = new FileModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Master Data Vendor";

        return view($this->views . '\vendor\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mVendor->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mVendor->getDataCnt($filters, $params);
        $totaldata = $this->mVendor->getDataCnt(null, $params);
        $maxpage = ceil($totalfiltered / $limit);

        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            $id = encrypt($row->id);

            $atr_edit = null;
            $atr_del = null;
            $btnAction = null;
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->urlv . '/edit/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = $this->urlv . '/delete/';
                $atr_del['class'] = '';
                $atr_del['onclick'] = "return confirm('Hapus Data ?')";
            }
            if ($atr_edit || $atr_del)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del);

            // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
            //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

            array_push(
                $build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    "id"   => ($id),
                    "nama" => $row->nama,
                    "alamat" => $row->alamat,
                    "email" => $row->email,
                    "no_hp" => $row->no_hp,
                    "pic" => $row->pic,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    // public function form()
    // {
    //     if (!$this->auth->loggedIn()) {
    //         return redirect()->to('/auth/login');
    //     } elseif(!$this->auth->isSuperAdmin() && !$this->auth->isAdmin()){
    // 		throw new \Exception('You must be an administrator to view this page.');
    //     }

    //     $id = 0;
    //     $this->data['id'] = current_url(true)->getSegment(4);
    //     if (trim($this->data['id']) != ""){
    //         $id = decrypt($this->data['id']);
    //         $this->data['titlehead'] = "Edit User";
    //     }else{
    //         $this->data['titlehead'] = "Input User";
    //     }

    //     //validate form input
    //     $this->validation->setRules([
    //         'nip'               => ['label' => 'NIP', 'rules' => 'required|trim'],
    //         'full_name'         => ['label' => 'Nama Lengkap', 'rules' => 'required|trim'],
    //         //'prefix'            => ['label' => 'Sapaan', 'rules' => 'required'],
    //         'username'          => ['label' => 'Username', 'rules' => 'required'],
    //         'email'             => ['label' => 'Email', 'rules' => 'required|valid_email'],
    //         'role_id'           => ['label' => 'Role', 'rules' => 'required'],
    //     ]);

    //     if (trim($this->data['id']) == "") {
    //         $this->validation->setRule('password', 'Password', 'required|min_length[8]|matches[password_confirm]');
    //         $this->validation->setRule('password_confirm', 'Konfirmasi password', 'required');
    //     }

    //     $showPhoto = "";

    //     if( $id > 0 AND !$this->request->getPost('id')) {
    //         // retrieve data for edit
    //         $user = $this->users->getUsers($id);    
    //         $this->data['user'] = $user;     
    //         $showPhoto = ($this->files->getFiles($user->file_id_photo))?$this->files->getFiles($user->file_id_photo)->file_name:"";
    //     }else{
    //         $user = new \stdClass();            
    //         $user->nip = '';
    //         $user->full_name = '';
    //         $user->frefix = '';
    //         $user->file_id_photo = '';
    //         $user->username = '';
    //         $user->email = '';
    //         $user->role_id = '';
    //     }

    //     if (isset($_POST) && !empty($_POST))
    //     {         
    //         $nip            = $this->request->getPost('nip');
    //         $full_name      = $this->request->getPost('full_name');
    //         //$prefix         = $this->request->getPost('prefix');
    //         $username       = $this->request->getPost('username');
    //         $password       = $this->request->getPost('password');
    //         $email          = $this->request->getPost('email');
    //         $role_id        = $this->request->getPost('role_id');
    //         $file_id_photo_old  = $this->request->getPost('file_id_photo_old');

    //         if( $id > 0 AND $this->request->getPost('id')) { // update

    //             $user->role_id = $role_id;
    //             $data = array(
    //                 'username' => $username,
    //                 'nip' => $nip,
    //                 'full_name' => $full_name,
    //                 //'prefix' => $prefix,
    //                 'email' => $email,
    //             );

    //             if ($this->request->getPost('password')) {
    //                 $data['password'] = trim($this->request->getPost('password'));
    //             }

    //             if($this->request->getFile('file_id_photo')->getName()){
    //                 $fileLogo       = $this->request->getFile('file_id_photo');
    //                 $fileName       = $fileLogo->getRandomName();
    //                 $originName     = $fileLogo->getName();
    //                 $fileType       = $fileLogo->getMimeType();
    //                 $fileSize       = $fileLogo->getSize();

    //                 $files = new FileModel();

    //                 if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
    //                     $this->session->setFlashdata('err', "<br>File <b>Photo</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>");
    //                     return redirect()->to('/utilitas/users/edit/'.$this->data['id']);
    //                 }

    //                 $files->insert([
    //                     'file_name' => $fileName,
    //                     'file_size' => $fileSize,
    //                     'file_type' => $fileType,
    //                     'file_name_origin' => $originName,
    //                     'active'    => 1,
    //                 ]);

    //                 $file_id_photo = $files->insertID();
    //                 $fileLogo->move(WRITEPATH.'uploads/users/', $fileName);

    //                 if($file_id_photo_old !=""){
    //                     $nama_file = $files->where('id',$file_id_photo_old)->get()->getRow()->file_name;
    //                     if (file_exists(WRITEPATH.'uploads/users/'.$nama_file)) {
    //                         unlink(WRITEPATH.'uploads/users/'.$nama_file);
    //                     }

    //                     $files->delete(['id' => $file_id_photo_old]);
    //                 }
    //             }

    //             if($this->request->getFile('file_id_photo')->getName()){
    //                 $data['file_id_photo'] = $file_id_photo;
    //             }

    //             if ($this->validation->withRequest($this->request)->run() === TRUE) {

    //                 $this->auth->update($id, $data);
    //                 $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Update User");        
    //                 $this->users->updateRole($id, array('role_id' => $role_id));

    //                 $this->session->setFlashdata('message', "Update user berhasil..");
    //                 return redirect()->to("/utilitas/users");

    //             } else {
    //                 //set the flash data error message if there is one
    //                 // $this->data['errmsg'] = (validation_errors() ? validation_errors() : ($this->mauth->errors() ? $this->mauth->errors() : ""));
    //                 $this->data['errmsg'] = $this->validation->listErrors();
    //                 $this->data['message'] = $this->session->getFlashdata('message');
    //                 $this->session->setFlashdata('err', $this->data['errmsg']);
    //                 $this->data['username_edit'] = $username;

    //                 $user->username = $this->request->getPost('username');
    //                 $user->nip = $this->request->getPost('nip');
    //                 $user->full_name = $this->request->getPost('full_name');
    //                 //$user->prefix = $this->request->getPost('prefix');
    //                 $user->email = $this->request->getPost('email');
    //                 $user->role_id = $this->request->getPost('role_id');

    //                 $this->data['user'] = $user;

    //             }

    //         } else { // insert
    //             $additional_data = array();

    //             if($this->request->getFile('file_id_photo')->getName()){
    //                 $fileLogo       = $this->request->getFile('file_id_photo');
    //                 $fileName       = $fileLogo->getRandomName();
    //                 $originName     = $fileLogo->getName();
    //                 $fileType       = $fileLogo->getMimeType();
    //                 $fileSize       = $fileLogo->getSize();

    //                 $files = new FileModel();

    //                 if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
    //                     $this->session->setFlashdata('err', "<br>File <b>Photo</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>");
    //                     return redirect()->to('/utilitas/users/add');
    //                 }

    //                 $files->insert([
    //                     'file_name' => $fileName,
    //                     'file_size' => $fileSize,
    //                     'file_type' => $fileType,
    //                     'file_name_origin' => $originName,
    //                     'active'    => 1,
    //                 ]);

    //                 $file_id_photo = $files->insertID();
    //                 $fileLogo->move(WRITEPATH.'uploads/users/', $fileName);
    //             }

    //             $additional_data["nip"] = $nip;
    //             $additional_data["full_name"] = $full_name;
    //             //$additional_data["prefix"] = $prefix;

    //             if($this->request->getFile('file_id_photo')->getName()){
    //                 $additional_data['file_id_photo'] = $file_id_photo;
    //             }

    //             $groups[] = $role_id;

    //             if ($this->validation->withRequest($this->request)->run() === TRUE) {
    //                 $regis = $this->auth->register($username, $password, $email, $additional_data, $groups);
    //                 $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$regis['id'],"Tambah User");        
    //                 $this->session->setFlashdata('message', "Tambah user berhasil..");
    //                 return redirect()->to("/utilitas/users");
    //             } else {
    //                 //set the flash data error message if there is one
    //                 $this->data['errmsg'] = $this->validation->listErrors();
    //                 $this->data['message'] = $this->session->getFlashdata('message');
    //                 $this->session->setFlashdata('err', $this->data['errmsg']);       

    //                 $user->username = $this->request->getPost('username');
    //                 $user->nip = $this->request->getPost('nip');
    //                 $user->full_name = $this->request->getPost('full_name');
    //                 //$user->prefix = $this->request->getPost('prefix');
    //                 $user->email = $this->request->getPost('email');
    //                 $user->role_id = $this->request->getPost('role_id');
    //                 $user->photo = $this->request->getPost('photo');
    //                // $user->prefix = $this->request->getPost('prefix');

    //                 $this->data['user'] = $user;
    //             }

    //         }

    //     }//endif POST

    //     //display the create user form
    //     $this->data['nip'] = array(
    //         'name' => 'nip',
    //         'id' => 'nip',
    //         'type' => 'text',
    //         'value' => set_value('nip', $user->nip),
    //         'class' => 'form-control',
    //         'placeholder' => 'Ketikkan NIP pengguna',
    //         'required' => 'true'
    //     );

    //     $this->data['full_name'] = array(
    //         'name' => 'full_name',
    //         'id' => 'full_name',
    //         'type' => 'text',
    //         'value' => set_value('full_name', $user->full_name),
    //         'class' => 'form-control',
    //         'placeholder' => 'Ketikkan nama lengkap pengguna',
    //         'required' => 'true'
    //     );

    //     $this->data['password'] = array(
    //         'name' => 'password',
    //         'id' => 'password',
    //         'type' => 'password',
    //         'class' => 'form-control',
    //         'placeholder' => 'Ketikkan password pengguna',
    //         'autocomplete' => 'new-password',
    //         'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$'
    //     );

    //     $this->data['password_confirm'] = array(
    //         'name' => 'password_confirm',
    //         'id' => 'password_confirm',
    //         'type' => 'password',
    //         'class' => 'form-control',
    //         'placeholder' => 'Ketikkan ulang password pengguna',
    //         'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$'
    //     );

    //     $this->data['email'] = array(
    //         'name' => 'email',
    //         'id' => 'email',
    //         'type' => 'email',
    //         'value' => set_value('email', $user->email),
    //         'class' => 'form-control',
    //         'placeholder' => 'Ketikkan alamat email pengguna',
    //         'required' => 'true',
    //     );

    //     $this->data['email_edit'] = $user->email;

    //     $this->data['username'] = array(
    //         'name' => 'username',
    //         'id' => 'username',
    //         'type' => 'text',
    //         'value' => set_value('username', $user->username),
    //         'class' => 'form-control',
    //         'placeholder' => 'Ketikkan username pengguna',
    //         'required' => 'true'
    //     );

    //     $this->data['file_id_photo'] = array(
    //         'name'  => 'file_id_photo',
    //         'id'    => 'file_id_photo',
    //         'type'  => 'text',
    //         'class' => 'form-control',
    //         'accept' => 'image/webp, image/jpeg, image/png'
    //     );

    //     $this->data['file_id_photo_old'] = ($user->file_id_photo)?$user->file_id_photo:"";
    //     $this->data['view_photo'] = $showPhoto;
    //     $this->data['username_edit'] = $user->username;
    //     $this->data['role_id'] = set_value('role_id', $user->role_id);
    //     $this->data['csrf'] = $this->_get_sess_csrf();

    //     // option role or group user
    //     $data_roles = [];
    //     $data_roles = $this->users->getRoles();
    //     foreach ($data_roles as $row) {
    //         $list_role[$row->id] = $row->description;
    //     }
    //     $this->data['list_role'] = $list_role;

    //     // $list_prefix["Bpk."] = "Bpk."; 
    //     // $list_prefix["Ibu."] = "Ibu."; 

    //     // $this->data['list_prefix'] = $list_prefix;

    //     return view('\Modules\Utility\Views\users_form', $this->data);
    // }

    function save()
    {
        $id           = $this->request->getPost('dataId');
        $namaKonsumen = $this->request->getPost('nama');
        $alamat       = $this->request->getPost('alamat');
        $email        = $this->request->getPost('email');
        $no_hp        = $this->request->getPost('no_hp');
        $pic        = $this->request->getPost('pic');


        $msg    = "Data gagal ditambahkan !";
        $status = false;

        $arr_isi = [
            'nama'   => $namaKonsumen,
            'alamat' => $alamat,
            'email'  => $email,
            'no_hp'  => $no_hp,
            'pic'  => $pic,
        ];


        if (empty($id)) {
            $this->mVendor->insertRecordGetid($this->mVendor->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        } else {
            $id = decrypt($id);
            $this->mVendor->updateRecord($this->mVendor->table, $arr_isi, 'id', $id);
            $msg    = "Data berhasil diupdate !";
            $status = true;
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;

        return $this->response->setJSON($build_array);
    }

    public function activate($id)
    {
        if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
            throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        $activation = $this->mVendor->activate($id);
        if ($activation) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "User Diaktifkan");
            $this->session->setFlashdata('message', "User berhasil di aktifkan");
        } else {
            $this->session->setFlashdata('err', "User gagal di aktifkan !");
        }
        return redirect()->to($this->urlv);
    }

    public function deactivate($id = NULL)
    {
        if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
            throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
            return redirect()->to($this->urlv);
        }
        $data = ['active' => 0];
        $deactivate = $this->mVendor->updateRecord($this->mVendor->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Data Konsumen Dinonaktifkan");
            $this->session->setFlashdata('message', "Data Konsumen berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Konsumen gagal di Hapus !");
        }
        return redirect()->to($this->urlv);
    }

    public function delete($id = NULL)
    {
        if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
            throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
            return redirect()->to($this->urlv);
        }

        $res = $this->mVendor->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Master Konsumen Dihapus");
            $this->session->setFlashdata('message', "Master Konsumen berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Konsumen gagal dihapus");
        }
        return redirect()->to($this->urlv);
    }
}
