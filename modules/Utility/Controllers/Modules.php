<?php

namespace Modules\Utility\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use Modules\Utility\Models\ModuleModel;

class Modules extends BaseController
{
    protected $modules;

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_MODULEMANAGE";

        $this->modules = new ModuleModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        
        $this->data['titlehead'] = "Daftar Modul";

        return view('\Modules\Utility\Views\modules', $this->data);
    }

    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        // $results = $this->modules->findAll();
        $res_modules = $this->modules->getData(null, $start, $limit, $order, $filters);
        $results = $this->modules->sortParentchild($res_modules);
        $totalfiltered = $this->modules->getDataCnt($filters);
        $totaldata = $this->modules->getDataCnt();
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
                $atr_edit['url'] = 'utilitas/modules/edit/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = 'utilitas/modules/delete/';
                $atr_del['class'] = '';
                $atr_del['del_msg'] = 'Anda yakin ingin menghapus modul ini?';
            }
            if($atr_edit || $atr_del)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del);

            $aktif =  ($row->publish) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/modules/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan modul ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
                "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/modules/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan modul ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

            array_push($build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    "name" => $row->treename,
                    "alias" => $row->alias,
                    "url" => $row->url,
                    "urutan" => $row->seq,
                    "icon" => $row->icon_cls,
                    "group" => $row->group,
                    "active" => $aktif,
                )
            );

        }
        
        return $this->response->setJSON($build_array);
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

        $data['publish'] = 1;
        $activation = $this->modules->update($id, $data);

        if ($activation) {
            $this->mcommon->clearMenuCache();
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Modul Diaktifkan");        
            $this->session->setFlashdata('message', "Modul berhasil di aktifkan...");
        } else {
            $this->session->setFlashdata('err', "Modul gagal di aktifkan !");
        }
		return redirect()->to('/utilitas/modules');

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
        
        $data['publish'] = 0;
        $deactivate = $this->modules->update($id, $data);

        if ($deactivate) {
            $this->mcommon->clearMenuCache();
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Modul Dinonaktifkan");        
            $this->session->setFlashdata('message', "Modul berhasil di non-aktifkan...");
        } else {
            $this->session->setFlashdata('err', "Modul gagal di non-aktifkan !");
        }
		return redirect()->to('/utilitas/modules');
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
            $this->data['titlehead'] = "Edit Modul";
        }else{
            $this->data['titlehead'] = "Input Modul";
        }

        //validate form input
        $this->validation->setRules([
            'name'              => ['label' => 'Nama Modul', 'rules' => 'required|trim'],
            'url'               => ['label' => 'URL', 'rules' => 'required|trim'],
            'icon_cls'          => ['label' => 'Icon Class', 'rules' => 'trim'],
            'seq'               => ['label' => 'Urutan', 'rules' => 'required|trim|numeric'],
            'pid'               => ['label' => 'Parent Modul', 'rules' => 'trim'],
            'group'             => ['label' => 'Kelompok Modul', 'rules' => 'trim'],
            'alias'             => ['label' => 'Modul Alias', 'rules' => 'required'],
        ]);

        if( $id > 0 AND !$this->request->getPost('id')) {
            // retrieve data for edit
            $module = $this->modules->getModules($id);      
            $this->data['module'] = $module;     
        }else{
            $module = new \stdClass();            
            $module->name = '';
            $module->alias = '';                       
            $module->url = '';                       
            $module->icon_cls = '';                       
            $module->seq = '';                       
            $module->pid = '';                       
            $module->publish = '';                       
            $module->group = '';            
        }

        if (isset($_POST) && !empty($_POST))
        {           
            $name       = $this->request->getPost('name');
            $alias      = strtoupper($this->request->getPost('alias'));
            $url        = $this->request->getPost('url');
            $icon_cls   = $this->request->getPost('icon_cls');
            $seq        = $this->request->getPost('seq');
            $pid        = $this->request->getPost('pid');
            $group      = $this->request->getPost('group');
            $data = array (
                  'name'        => $name,
                  'alias'       => $alias,         
                  'url'         => $url,         
                  'icon_cls'    => $icon_cls,         
                  'seq'         => $seq,         
                  'pid'         => $pid,         
                  'group'       => $group,
                  'is_sapage'   => 0        
            );
                   
            //check to see if we are updating
            if( $id > 0 AND $this->request->getPost('id')) { // update
                if ($this->validation->withRequest($this->request)->run() === TRUE AND $this->modules->updateRecord($this->modules->table,$data, 'id',$id))
                {       
                    $this->modules->insertRolePriv($id);
                    $this->mcommon->clearMenuCache();
                    $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Update Modul");        
                    $this->session->setFlashdata('message', "Update modul berhasil.." );
		            return redirect()->to('/utilitas/modules');
                }else{
                    //set the flash data error message if there is one
                    $this->data['errmsg'] = $this->validation->listErrors();
                    $this->data['message'] = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('err', $this->data['errmsg']); 
                    
                    $module = new \stdClass();
                    $module->name = $this->request->getPost('name');
                    $module->alias = $this->request->getPost('alias');
                    $module->url = $this->request->getPost('url');
                    $module->icon_cls = $this->request->getPost('icon_cls');
                    $module->seq = $this->request->getPost('seq');
                    $module->pid = $this->request->getPost('pid');
                    $module->group = $this->request->getPost('group');
                    $this->data['module'] = $module;      
                }
                
                          
            } else { // insert
                $data['publish'] = 1;
                
                if ($this->validation->withRequest($this->request)->run() === TRUE  )
                {
                    $id = $this->modules->insertRecordGetid($this->modules->table,$data);
                    
                    $this->modules->insertRolePriv($id);
                    $this->mcommon->clearMenuCache();
                    $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Tambah Modul");        
                    $this->session->setFlashdata('message', "Tambah modul berhasil..");
                    return redirect()->to("/utilitas/modules");
                }else{
                    //set the flash data error message if there is one
                    $this->data['errmsg'] = $this->validation->listErrors();
                    $this->data['message'] = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('err', $this->data['errmsg']);       

                    $module = new \stdClass();
                    $module->name = $this->request->getPost('name');
                    $module->alias = $this->request->getPost('alias');
                    $module->url = $this->request->getPost('url');
                    $module->icon_cls = $this->request->getPost('icon_cls');
                    $module->seq = $this->request->getPost('seq');
                    $module->pid = $this->request->getPost('pid');
                    $module->group = $this->request->getPost('group');
                    $this->data['module'] = $module;                        
                } 
            }
            
        }//endif POST

        //display the create modul form                                              
        $this->data['module_name'] = array(
                'name'  => 'name',
                'id'    => 'name',
                'type'  => 'text',
                'value' => set_value('name', $module->name),
                'class' => 'form-control',
                'placeholder' => 'Ketikkan nama modul',
                'required' => 'true'
        );
        
        $this->data['module_alias'] = array(
                'name'  => 'alias',
                'id'    => 'alias',
                'type'  => 'text',
                'value' => set_value('alias', $module->alias),
                'class' => 'form-control',
                'placeholder' => 'Ketikkan alias modul',
                'required' => 'true'
        );
        $this->data['module_alias_edit'] =  $module->alias;
        
        $this->data['module_url'] = array(
                'name'  => 'url',
                'id'    => 'url',
                'type'  => 'text',
                'value' => set_value('url', $module->url),
                'class' => 'form-control',
                'placeholder' => 'Ketikkan URL modul',
                'required' => 'true'
        );
        
        $this->data['mod_icon_cls'] = array(
                'name'  => 'icon_cls',
                'id'    => 'icon_cls',
                'type'  => 'text',
                'value' => set_value('icon_cls', $module->icon_cls),
                'class' => 'form-control',
                'placeholder' => 'Ketikkan icon class modul'
        );
        
        $this->data['mod_seq'] = array(
                'name'  => 'seq',
                'id'    => 'seq',
                'type'  => 'number',
                'value' => set_value('seq', $module->seq),
                'class' => 'form-control',
                'placeholder' => 'Ketikkan nomor urut modul',
                'required' => 'true',
                'onblur' => 'checkNumber(this)',
                'onkeyup' => 'checkNumber(this)'
        );
        
        $this->data['mod_group'] = array(
                'name'  => 'group',
                'id'    => 'group',
                'type'  => 'text',
                'value' => set_value('group', $module->group),
                'class' => 'form-control',
                'placeholder' => 'Ketikkan nama kelompok modul'                
        );
               
        // option modules parent              
        $_by['publish']=1;
        $data_modules  = $this->modules->getModules(null,$_by);
        $modules_sort  = $this->modules->sortParentchild($data_modules);
        $list_pmod ['0'] = 'Root';
        foreach ($modules_sort as $row) {
            $list_pmod[$row->id] = $row->treename;
        }
        $this->data['list_pmod'] =  $list_pmod;
        
        $this->data['csrf'] = $this->_get_sess_csrf();
        return view('\Modules\Utility\Views\modules_form', $this->data);
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
		    return redirect()->to('/utilitas/modules');
        }       
                
        $res = $this->modules->delete($id);
        if ($res)
        {     
            $this->modules->deleteRecord('sec_role_priv','module_id',$id);
            $this->mcommon->clearMenuCache();
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Hapus Modul");        
            $this->session->setFlashdata('message', "Modul berhasil dihapus..."); 
        }
        else
        {                     
            $this->session->setFlashdata('err', "Modul gagal dihapus !"); 
        }

		return redirect()->to('/utilitas/modules');
    }
}
