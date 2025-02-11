<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\RekeningModel;

class RefRekening extends BaseController
{
    protected $mRef;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/rekening';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_REKENING";

        $this->mRef = new RekeningModel();
        $this->files  = new FileModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Master Tipe Pembayaran";

        return view($this->views . '\rekening\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mRef->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mRef->getDataCnt($filters, $params);
        $totaldata = $this->mRef->getDataCnt(null, $params);
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
                    "rekening_no" => $row->rekening_no,
                    "rekening_bank" => $row->rekening_bank,
                    "rekening_an" => $row->rekening_an,
                    "keterangan" => $row->keterangan,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save()
    {
        $id         = $this->request->getPost('dataId');
        $rekening_an = $this->request->getPost('rekening_an');
        $rekening_bank = $this->request->getPost('rekening_bank');
        $rekening_no = $this->request->getPost('rekening_no');
        $keterangan = $this->request->getPost('keterangan');



        $msg    = "Data gagal ditambahkan !";
        $status = false;

        $arr_isi = [
            'rekening_no' => $rekening_no,
            'rekening_an' => $rekening_an,
            'rekening_bank' => $rekening_bank,
            'keterangan' => $keterangan,
        ];


        if (empty($id)) {
            $arr_isi['created_at'] = date("Y-m-d H:i:s");
            $arr_isi['created_by'] = $this->get_userid();
            $this->mRef->insertRecordGetid($this->mRef->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        } else {
            $arr_isi['updated_at'] = date("Y-m-d H:i:s");
            $arr_isi['updated_by'] = $this->get_userid();
            $id = decrypt($id);
            $this->mRef->updateRecord($this->mRef->table, $arr_isi, 'id', $id);
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
        $activation = $this->mRef->activate($id);
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
        // if ($id == 1) {
        //     return redirect()->to($this->urlv);
        // }
        $data = ['active' => 0];
        $deactivate = $this->mRef->updateRecord($this->mRef->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Data Rekening Dinonaktifkan");
            $this->session->setFlashdata('message', "Data Rekening berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Rekening gagal di Hapus !");
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
        // if ($id == 1) {
        //     return redirect()->to($this->urlv);
        // }

        $res = $this->mRef->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Master Rekening Dihapus");
            $this->session->setFlashdata('message', "Master Rekening berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Rekening gagal dihapus");
        }
        return redirect()->to($this->urlv);
    }
}
