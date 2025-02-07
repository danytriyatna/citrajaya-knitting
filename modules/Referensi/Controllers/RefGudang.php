<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\OperatorModel;

class RefGudang extends BaseController
{
    protected $mGudang;
    protected $mOperator;
    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/gudang';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_GUDANG";
        $this->mOperator = new OperatorModel();
        $this->mGudang = new GudangModel();
        $this->files  = new FileModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        $sortCMT = [
            [
                'field' => 'nama_operator',
                'dir' => 'ASC'
            ]
        ];
        $dataCMT = $this->mOperator->getData(null, 0, 99999, $sortCMT);
        $this->data['titlehead'] = "Master Data Gudang";
        $this->data['cmt'] = $dataCMT;

        return view($this->views . '\gudang\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mGudang->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mGudang->getDataCnt($filters, $params);
        $totaldata = $this->mGudang->getDataCnt(null, $params);
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
                    "nama_gudang" => $row->nama_gudang,
                    "tipe" => $row->tipe == 1 ? "NON CMT" : "CMT",
                    "id_tipe" => $row->tipe,
                    "cmt" => $row->nama_operator,
                    "id_cmt" => $row->id_cmt,
                    "keterangan" => $row->keterangan,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save()
    {
        $id         = $this->request->getPost('dataId');
        $nama_gudang = $this->request->getPost('nama_gudang');
        $keterangan = $this->request->getPost('keterangan');
        $tipe = $this->request->getPost('tipe');
        $cmt = $this->request->getPost('cmt');



        $msg    = "Data gagal ditambahkan !";
        $status = false;

        $arr_isi = [
            'nama_gudang' => $nama_gudang,
            'keterangan' => $keterangan,
            'tipe' => $tipe,
            'id_cmt' => !empty($cmt) ? $cmt : null,
        ];


        if (empty($id)) {
            $arr_isi['created_at'] = date("Y-m-d H:i:s");
            $arr_isi['created_by'] = $this->get_userid();
            $this->mGudang->insertRecordGetid($this->mGudang->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        } else {
            $arr_isi['updated_at'] = date("Y-m-d H:i:s");
            $arr_isi['updated_by'] = $this->get_userid();
            $id = decrypt($id);
            $this->mGudang->updateRecord($this->mGudang->table, $arr_isi, 'id', $id);
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
        $activation = $this->mGudang->activate($id);
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
        $deactivate = $this->mGudang->updateRecord($this->mGudang->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Data Gudang Dinonaktifkan");
            $this->session->setFlashdata('message', "Data Gudang berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Gudang gagal di Hapus !");
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

        $res = $this->mGudang->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Master Gudang Dihapus");
            $this->session->setFlashdata('message', "Master Gudang berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Gudang gagal dihapus");
        }
        return redirect()->to($this->urlv);
    }
}
