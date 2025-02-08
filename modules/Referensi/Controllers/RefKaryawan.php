<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\KaryawanModel;

class RefKaryawan extends BaseController
{
    protected $mkaryawan;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/karyawan';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_KARYAWAN";
     
        $this->mkaryawan = new KaryawanModel();
        $this->files  = new FileModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        
        $this->data['titlehead'] = "Master Data Karyawan";

        return view($this->views.'\karyawan\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mkaryawan->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mkaryawan->getDataCnt($filters, $params);
        $totaldata = $this->mkaryawan->getDataCnt(null, $params);
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
                $atr_edit['url'] = $this->urlv . '/edit/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = $this->urlv . '/delete/';
                $atr_del['class'] = '';
                $atr_del['onclick'] = "return confirm('Hapus Data ?')";
            }
            if($atr_edit || $atr_del)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del);

            // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
            //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

            array_push($build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    "id"   => ($id),
                    "nip" => $row->nip,
                    "full_name" => $row->full_name,
                    "email" => $row->email,
                    "alamat" => $row->alamat,
                    "posisi" => $row->posisi,
                    "tgl_bergabung" => !empty($row->tgl_bergabung) ? \fdate_eng_to_ind($row->tgl_bergabung) : "",
                    "jenis_kelamin" => $row->jenis_kelamin,
                    "jenis_kelamint" => ($row->jenis_kelamin == 1) ? "Laki - Laki" : "Perempuan",
                    "tgl_lahir" => !empty($row->tgl_lahir) ? \fdate_eng_to_ind($row->tgl_lahir) : "",
                    "tempat_lahir" => $row->tempat_lahir,
                    "no_hp" => $row->no_hp,
                    "upah_lembur" => $row->upah_lembur,
                    "upah_harian" => $row->upah_harian,
                    "upah_lembur_we" => $row->upah_lembur_we,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save(){
        $id           = $this->request->getPost('dataId');
        
        $nip =  $this->request->getPost('nip');
        $full_name =  $this->request->getPost('full_name');
        $email =  $this->request->getPost('email');
        $posisi =  $this->request->getPost('posisi');
        $tgl_bergabung =  $this->request->getPost('tgl_bergabung');
        $jenis_kelamin =  $this->request->getPost('jenis_kelamin');
        $jenis_kelamint =  $this->request->getPost('jenis_kelamint');
        $alamat =  $this->request->getPost('alamat');
        $tgl_lahir =  $this->request->getPost('tgl_lahir');
        $tempat_lahir =  $this->request->getPost('tempat_lahir');
        $no_hp =  $this->request->getPost('no_hp');
        $upah_lembur =  $this->request->getPost('upah_lembur');
        $upah_harian =  $this->request->getPost('upah_harian');
        $upah_lembur_we =  $this->request->getPost('upah_lembur_we');
        $upah_perjam =  $this->request->getPost('upah_perjam');


        $msg    = "Data gagal ditambahkan !";
        $status = false;

        // $tgl_lahir = \fdate_eng_to_ind_3($tgl_lahir);
        $tgl_bergabung = \fdate_ind_to_eng($tgl_bergabung);
        
        $arr_isi = [
            'nip' => $nip,
            'full_name' => $full_name,
            'email' => $email,
            'posisi' => $posisi,
            'tgl_bergabung' => $tgl_bergabung,
            'jenis_kelamin' => $jenis_kelamin,
            'alamat' => $alamat,
            // 'tgl_lahir' => $tgl_lahir,
            // 'tempat_lahir' => $tempat_lahir,
            'no_hp' => $no_hp,
            'upah_lembur' => $upah_lembur,
            'upah_harian' => $upah_harian,
            'upah_lembur_we' => $upah_lembur_we,
            'upah_perjam' => $upah_perjam
        ];

        
        if(empty($id)){
            $id = $this->mkaryawan->insertRecordGetid($this->mkaryawan->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        }else{
            $id = decrypt($id);
            $this->mkaryawan->updateRecord($this->mkaryawan->table, $arr_isi, 'id', $id);
            $msg    = "Data berhasil diupdate !";
            $status = true;
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;

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
        $activation = $this->mkaryawan->activate($id);
        if ($activation) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"User Diaktifkan");        
            $this->session->setFlashdata('message', "User berhasil di aktifkan");
        } else {
            $this->session->setFlashdata('err', "User gagal di aktifkan !");
        }
		return redirect()->to($this->urlv);

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
		    return redirect()->to($this->urlv);
        }
        $data = ['active' => 0];
        $deactivate = $this->mkaryawan->updateRecord($this->mkaryawan->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Data Konsumen Dinonaktifkan");        
            $this->session->setFlashdata('message', "Data Konsumen berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Konsumen gagal di Hapus !");
        }
		return redirect()->to($this->urlv);
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
		    return redirect()->to($this->urlv);
        }
        
        $res = $this->mkaryawan->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Master Konsumen Dihapus");        
            $this->session->setFlashdata('message', "Master Konsumen berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Konsumen gagal dihapus");
        }
		return redirect()->to($this->urlv);
    }
}
