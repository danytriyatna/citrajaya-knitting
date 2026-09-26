<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\ProsesProduksiModel;

class RefProsesProduksi extends BaseController
{
    protected $mproses;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/proses';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_PROSES";
     
        $this->mproses = new ProsesProduksiModel();
        $this->files  = new FileModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        
        $this->data['titlehead'] = "Master Data Proses";

        return view($this->views.'\proses\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mproses->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mproses->getDataCnt($filters, $params);
        $totaldata = $this->mproses->getDataCnt(null, $params);
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
                    "seq" => $row->seq,
                    "nama" => $row->nama,
                    "keterangan" => $row->keterangan,
                    "is_rajut" => (int)$row->is_rajut
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save(){
        $id         = $this->request->getPost('dataId');
        $nama = $this->request->getPost('nama');
        $keterangan = $this->request->getPost('keterangan');
        $noUrut = $this->request->getPost('seq');
        $isRajut = $this->request->getPost('is_rajut');
        $isRajutVal = (!empty($isRajut) && ($isRajut == '1' || $isRajut === 1 || $isRajut === true || $isRajut === 'true')) ? 1 : 0;

        $msg    = "Data gagal ditambahkan !";
        $status = false;

        $arr_isi = [
            'nama' => $nama, 
            'keterangan' => $keterangan,
            'seq' => $noUrut,
            'is_rajut' => $isRajutVal
        ];

        
        if(empty($id)){
            $this->mproses->insertRecordGetid($this->mproses->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        }else{
            $id = decrypt($id);
            $this->mproses->updateRecord($this->mproses->table, $arr_isi, 'id', $id);
            $msg    = "Data berhasil diupdate !";
            $status = true;
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;

        return $this->response->setJSON($build_array);
    }

    public function deactivate($id = NULL)
    {
        if (!$this->auth->loggedIn()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Silakan login terlebih dahulu.']);
            }
            return redirect()->to('/auth/login');
        }
        
        if (empty($id)) {
            $id = $this->request->getPost('id');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => 'ID data tidak valid.']);
            }
            return redirect()->to($this->urlv);
        }

        $data = ['active' => 0];
        $deactivate = $this->mproses->updateRecord($this->mproses->table, $data, 'id', $id);
        if ($deactivate) {
            $userId = $this->currentUser->user_id ?? 1;
            $this->mcommon->setLog($userId, $this->MOD_ALIAS, $id, "Data Proses Dinonaktifkan (Soft Delete)");        
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => true, 'message' => 'Data Proses berhasil dihapus!']);
            }
            $this->session->setFlashdata('message', "Data Proses berhasil di Hapus");
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Data Proses gagal dihapus!']);
            }
            $this->session->setFlashdata('err', "Data Proses gagal di Hapus !");
        }
		return redirect()->to($this->urlv);
    }

    public function delete($id = NULL)
    {
        return $this->deactivate($id);
    }
}
