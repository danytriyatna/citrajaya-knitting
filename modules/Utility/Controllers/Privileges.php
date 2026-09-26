<?php

namespace Modules\Utility\Controllers;

use App\Controllers\BaseController;
use Modules\Utility\Models\ModuleModel;
use Modules\Utility\Models\PrivilegeModel;

class Privileges extends BaseController
{
    protected $modules;
    protected $privileges;

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_PRIVMANAGE";  

        $this->modules    = new ModuleModel();
        $this->privileges = new PrivilegeModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        
        $this->data['titlehead'] = "Atur Otorisasi";

        $this->editPrivilege();

        return view('\Modules\Utility\Views\privilege', $this->data);
    }

    public function editPrivilege()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } elseif(!$this->auth->isSuperAdmin() && !$this->auth->isAdmin()){
			throw new \Exception('You must be an administrator to view this page.');
        }

        $list_roles[''] = '-- Pilih Role --';
        $data_roles = $this->modules->getRoles();
        foreach ($data_roles as $row) {
            $list_roles[$row->id] = $row->description;
        }

        $this->data['list_roles'] = $list_roles;
        $this->data['csrf'] = $this->_get_sess_csrf();
    }

    public function getPrivBy($role_id)
    {
        $build_array = array("data" => array());
        // if ($role_id != 0){
        //     $role_id = $this->encrypter->decrypt(hex2bin($role_id));
        // }
        // dd($role_id);
        
        if ($role_id != 0) {
            $data = $this->privileges->getPrivilege($role_id);
            $dataSort = $this->modules->sortParentChild($data);
           
            if (count($dataSort) > 0) {
                $i = 1;
                foreach ($dataSort as $row) {
                    $_checked = "";
                    if ($row->allow_view == 1)
                        $_checked = "CHECKED";

                    $Inp = sprintf("<input type='checkbox' value='%d' name='moduleid[]' id='moduleid-%d' %s />", $row->id, $row->id, $_checked);
                    
                    $_checked_new = "";
                    if ($row->allow_new == 1)
                        $_checked_new = "CHECKED";
                    
                    $InpNew = sprintf("<input type='checkbox' value='%d' name='auth_new[]' id='auth_new-%d' %s />", $row->id, $row->id, $_checked_new);
                    
                    $_checked_edit = "";
                    if ($row->allow_edit == 1)
                        $_checked_edit = "CHECKED";
                    
                    $InpEdit = sprintf("<input type='checkbox' value='%d' name='auth_edit[]' id='auth_edit-%d' %s />", $row->id, $row->id, $_checked_edit);
                    
                    $_checked_del = "";
                    if ($row->allow_delete == 1)
                        $_checked_del = "CHECKED";
                    
                    $InpDel = sprintf("<input type='checkbox' value='%d' name='auth_del[]' id='auth_del-%d' %s />", $row->id, $row->id, $_checked_del);
                    
                    $_checked_print = "";
                    if ($row->allow_print == 1)
                        $_checked_print = "CHECKED";
                    
                    $InpPrint = sprintf("<input type='checkbox' value='%d' name='auth_print[]' id='auth_print-%d' %s />", $row->id, $row->id, $_checked_print);
                    
                    $_checked_approve = "";
                    if ($row->allow_approve == 1)
                        $_checked_approve = "CHECKED";
                    
                    $InpApprove = sprintf("<input type='checkbox' value='%d' name='auth_approve[]' id='auth_approve-%d' %s />", $row->id, $row->id, $_checked_approve);
                    
                    array_push($build_array["data"],
                        array(
                            $row->treename,
                            $Inp,
                            $InpNew,
                            $InpEdit,
                            $InpDel,
                            $InpPrint,
                            $InpApprove
                        )
                    );
                    $i++;
                }
            }
        }
        echo json_encode($build_array);
    }

    public function save()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } elseif(!$this->auth->isSuperAdmin() && !$this->auth->isAdmin()){
			throw new \Exception('You must be an administrator to view this page.');
        }
        $this->validation->setRule('role_id', 'Nama Role', 'trim|required');

        if (isset($_POST) && !empty($_POST)) {
            $role_id = $this->request->getPost('role_id');
            // if (trim($role_id) != "")
            //     $role_id = $this->encrypter->decrypt(hex2bin($role_id));
            if ($this->validation->withRequest($this->request)->run() === TRUE ) {
                if ($this->privileges->updatePrivilege($role_id)) {
                    $this->mcommon->clearMenuCache($role_id);
                    $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$role_id,"Update Otorisasi");        
                    $this->session->setFlashdata('message', "Update otorisasi berhasil..");
                } else {
                    $this->data['errmsg'] = "Update otorisasi gagal !";
                    $this->session->setFlashdata('err', $this->data['errmsg']);
                }
            } else {
                $this->data['errmsg'] = ($this->validation->listErrors() ? $this->validation->listErrors() : "Update otorisasi gagal !");
                $this->data['message'] = $this->session->getFlashdata('message');
                $this->session->setFlashdata('err', $this->data['errmsg']);
            }
            return redirect()->to('/utilitas/privileges');
        }

    }

}
