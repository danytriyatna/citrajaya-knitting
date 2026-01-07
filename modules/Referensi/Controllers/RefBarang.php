<?php

namespace Modules\Referensi\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Referensi\Models\WarnaModel;

class RefBarang extends BaseController
{
    protected $mBarang;
    protected $mJenisBarang;
    protected $mSatuan;
    protected $mWarna;

    protected $views = '\Modules\Referensi\Views';
    protected $urlv  = 'master-data/barang';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_REFERENSI_BARANG";
        $this->mBarang = new BarangModel();
        $this->mJenisBarang = new JenisBarangModel();
        $this->mSatuan = new SatuanModel();
        $this->mWarna = new WarnaModel();
        $this->files  = new FileModel();
    }

    public function index()
    {

        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Master Data Barang";

        $sortSatuan = [
            [
                'field' => 'nama_satuan',
                'dir' => 'ASC'
            ]
        ];
        $sortWarna = [
            [
                'field' => 'kode_warna',
                'dir' => 'ASC'
            ]
        ];
        $sortJenisBarang = [
            [
                'field' => 'nama_jenis_barang',
                'dir' => 'ASC'
            ]
        ];

        $dataSatuan = $this->mSatuan->getData(null, 0, 99999, $sortSatuan);
        $dataWarna = $this->mWarna->getData(null, 0, 99999, $sortWarna);
        $dataJenisBarang = $this->mJenisBarang->getData(null, 0, 99999, $sortJenisBarang);
        $this->data['satuan'] = $dataSatuan;
        $this->data['warna'] = $dataWarna;
        $this->data['jenisBarang'] = $dataJenisBarang;
        return view($this->views . '\barang\index', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mBarang->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mBarang->getDataCnt($filters, $params);
        $totaldata = $this->mBarang->getDataCnt(null, $params);
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
                    "nama_barang" => $row->nama_barang,
                    "kode_barang" => $row->kode_barang,
                    "harga_satuan" => $row->harga_satuan,
                    "stok_minimum" => $row->stok_minimum,
                    "nama_jenis_barang" => $row->nama_jenis_barang,
                    "nama_satuan" => $row->nama_satuan,
                    "id_satuan" => $row->id_satuan,
                    "id_warna" => $row->id_warna,
                    "kode_warna" => $row->kode_warna,
                    "id_jenis_barang" => $row->id_jenis_barang,
                    "keterangan" => $row->keterangan,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    function save()
    {
        $id         = $this->request->getPost('dataId');
        $nama_barang = $this->request->getPost('nama_barang');
        $id_jenis_barang = $this->request->getPost('id_jenis_barang');
        $id_satuan = $this->request->getPost('id_satuan');
        $id_warna = $this->request->getPost('id_warna');
        $harga_satuan = $this->request->getPost('harga_satuan');
        $stok_minimum = $this->request->getPost('stok_minimum');
        $keterangan = $this->request->getPost('keterangan');

        $msg    = "Data gagal ditambahkan !";
        $status = false;

        $arr_isi = [
            'nama_barang' => $nama_barang,
            'id_jenis_barang' => $id_jenis_barang,
            'id_satuan' => $id_satuan,
            'id_warna' => $id_warna,
            'harga_satuan' => $harga_satuan,
            'stok_minimum' => $stok_minimum,
            'keterangan' => $keterangan,
        ];


        if (empty($id)) {
            $arr_isi['created_at'] = date("Y-m-d H:i:s");
            $arr_isi['created_by'] = $this->get_userid();
            $arr_isi['kode_barang'] = $this->mBarang->generateKodeBarang();
            $this->mBarang->insertRecordGetid($this->mBarang->table, $arr_isi);
            $msg    = "Data berhasil ditambahkan !";
            $status = true;
        } else {
            $arr_isi['updated_at'] = date("Y-m-d H:i:s");
            $arr_isi['updated_by'] = $this->get_userid();
            $id = decrypt($id);
            $this->mBarang->updateRecord($this->mBarang->table, $arr_isi, 'id', $id);
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
        $activation = $this->mBarang->activate($id);
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

        $deactivate = $this->mBarang->updateRecord($this->mBarang->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Data Barang Dinonaktifkan");
            $this->session->setFlashdata('message', "Data Barang berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Barang gagal di Hapus !");
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

        $res = $this->mBarang->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Master Ukuran Dihapus");
            $this->session->setFlashdata('message', "Master Ukuran berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Ukuran gagal dihapus");
        }
        return redirect()->to($this->urlv);
    }
}
