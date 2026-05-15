<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\OperatorModel;

class RefOperator extends BaseController
{
    protected $mOperator;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/operator';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_OPERATOR";

        $this->mOperator = new OperatorModel();
        $this->files  = new FileModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Master Data CMT";

        return view($this->views . '\operator\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mOperator->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mOperator->getDataCnt($filters, $params);
        $totaldata = $this->mOperator->getDataCnt(null, $params);
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
                    "nama_operator" => $row->nama_operator,
                    "no_hp" => $row->no_hp,
                    "alamat" => $row->alamat,
                    "tgl_bergabung" => $row->tgl_bergabung,
                    "harga" => $row->harga,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save()
    {
        $id         = $this->request->getPost('dataId');
        $no_hp = $this->request->getPost('no_hp');
        $nama_operator = $this->request->getPost('nama_operator');
        $tgl_bergabung = $this->request->getPost('tgl_bergabung');
        $alamat = $this->request->getPost('alamat');
        $harga = $this->request->getPost('harga');


        $msg    = "Data gagal ditambahkan !";
        $status = false;

        $arr_isi = [
            'nama_operator' => $nama_operator,
            'no_hp' => $no_hp,
            'tgl_bergabung' => !empty($tgl_bergabung) ? $tgl_bergabung : null,
            'alamat' => $alamat,
            'harga' => !empty($harga) ? str_replace(['Rp', '.', ','], '', $harga) : null,
        ];


        if (empty($id)) {
            $this->mOperator->insertRecordGetid($this->mOperator->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        } else {
            $id = decrypt($id);
            $this->mOperator->updateRecord($this->mOperator->table, $arr_isi, 'id', $id);
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
        $activation = $this->mOperator->activate($id);
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
        $deactivate = $this->mOperator->updateRecord($this->mOperator->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Data Ukuran Dinonaktifkan");
            $this->session->setFlashdata('message', "Data Ukuran berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Ukuran gagal di Hapus !");
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

        $res = $this->mOperator->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Master Ukuran Dihapus");
            $this->session->setFlashdata('message', "Master Ukuran berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Ukuran gagal dihapus");
        }
        return redirect()->to($this->urlv);
    }

    public function getOperator()
    {
        $id_perusahaan = $this->request->getPost('id_perusahaan');

        $params = [
            'id_perusahaan' => $id_perusahaan
        ];

        $data = $this->mOperator->getData(null, 0, 99999, null, null, $params);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $data
        ]);
    }
}
