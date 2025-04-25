<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\BarangKeluarModel;
use Modules\Transaction\Models\BarangKeluarDetailModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Transaction\Models\OutgoingGoodsModel;
use Modules\Transaction\Models\ItemTransferDetailModel;
use Modules\Transaction\Models\ItemTransferModel;

class BarangKeluar extends BaseController
{
    protected $mBarang;
    protected $mRef;
    protected $mRefDet;
    protected $mRefDetSO;
    protected $mJenisBarang;
    protected $mSatuan;
    protected $mBarangMasuk;
    protected $mGudang;
    protected $mBarangKeluar;
    protected $mTrf;
    protected $mTrfDet;
    protected $views = '\Modules\Transaction\Views';
    protected $urlv  = 'trans/outgoing-goods';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_TRANSAKSI_BARANG_KELUAR";
        $this->mBarang = new BarangModel();
        $this->mJenisBarang = new JenisBarangModel();
        $this->mSatuan = new SatuanModel();
        $this->mBarangMasuk = new IncomingGoodsModel();
        $this->mRefDet = new BarangKeluarDetailModel();
        $this->mRefDetSO = new ItemTransferDetailModel();
        $this->mRef = new BarangKeluarModel();
        $this->mGudang = new GudangModel();
        $this->mTrf = new ItemTransferModel();
        $this->mTrfDet = new ItemTransferDetailModel();
        $this->files  = new FileModel();
        $this->mBarangKeluar = new OutgoingGoodsModel();
    }

    public function index()
    {

        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Transaksi Data Barang Keluar ";

        $sortSatuan = [
            [
                'field' => 'nama_satuan',
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
        $dataJenisBarang = $this->mJenisBarang->getData(null, 0, 99999, $sortJenisBarang);
        $this->data['satuan'] = $dataSatuan;
        $this->data['jenisBarang'] = $dataJenisBarang;
        return view($this->views . '\barang_keluar_list', $this->data);
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
            $atr_other = null;
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->urlv . '/edit/';
                $atr_edit['class'] = '';
            }
            if ($row->status != 0 && $row->id_kategori == 8) {
                $atr_other['title'] = 'Print';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            }
            // if ($this->_delete) {
            //     $atr_del['title'] = 'Hapus';
            //     $atr_del['url'] = $this->urlv . '/delete/';
            //     $atr_del['class'] = '';
            //     $atr_del['onclick'] = "return confirm('Hapus Data ?')";
            // }
            if ($atr_edit || $atr_del)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);

            // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
            //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";
            $status = "";
            if ($row->status == 0) {
                $status = "<span class='badge bg-secondary'>Draft</span>";
            } else if ($row->status == 1) {
                $status = "<span class='badge bg-success'>Approved</span>";
            }
            array_push(
                $build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    "id"   => ($id),
                    "kode_transaksi" => $row->kode_transaksi,
                    "tanggal" => fdate_eng_to_ind($row->tanggal),
                    "kategori" => $row->kategori,
                    "nama_gudang" => $row->nama_gudang,
                    "keterangan" => $row->keterangan,
                    "status" => $status
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    public function listsBarang()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $idGudang      = $this->request->getPost('idGudang');

        $params = [];
        if ($idGudang != "") {

            $params['id_gudang'] = $idGudang;
        } else {
            $build_array = array(
                "data" => array()
            );
            return $this->response->setJSON($build_array);
        }

        $results = $this->mRef->getDataBarang(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mRef->getDataBarangCnt($filters, $params);
        $totaldata = $this->mRef->getDataBarangCnt(null, $params);
        $maxpage = ceil($totalfiltered / $limit);

        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            $id = encrypt($row->id_barang);
            array_push(
                $build_array["data"],
                array(
                    "id"   => $row->id_barang,
                    "nama_barang" => $row->nama_barang,
                    "kode_barang" => $row->kode_barang,
                    "nama_satuan" => $row->nama_satuan,
                    "qty" => $row->qty,
                    "lot_no" => $row->lot_no,
                    "lot_id" => $row->lot_id,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    public function form($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['id'] = $id;
        if ($id != "") {
            $id = decrypt($id);
            $resData = $this->mRef->getData($id);
            $tanggal = date("d F Y", strtotime($resData->tanggal));
            $resData->tanggal = $tanggal;
            $resData->id_vendor = !empty($resData->id_vendor) ? encrypt($resData->id_vendor) : null;
            $sort = [
                [
                    'field' => 'uk.id',
                    'dir' => 'ASC'
                ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang));
            // foreach ($resDataDetail as &$rowData) {
            //     $rowData->id_barang = encrypt($rowData->id_barang);
            // }
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
            $this->data['resData'] = $resData;
            $this->data['detail'] = json_encode($resDataDetail);
            $this->data['dataSO'] = json_encode($resDataDetSO);
        }
        $reDataKategori = $this->mBarangKeluar->getRefKategoriPersedian();
        $sortGudang = [
            [
                'field' => 'nama_gudang',
                'dir' => 'ASC'
            ]
        ];
        $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
        $this->data['kategori']    = $reDataKategori;
        $this->data['gudang']    = $resDataGudang;

        $this->data['titlehead'] = "Form Barang Keluar";

        return view($this->views . '\barang_keluar_form', $this->data);
    }

    function save()
    {

        $msg    = "Data gagal disimpan !";
        $status = false;
        $id = $this->request->getPost('id');
        $tanggal = $this->request->getPost('tanggal');
        $statusData = $this->request->getPost('status');
        $id_gudang = $this->request->getPost('id_gudang');
        $id_vendor = $this->request->getPost('id_vendor');
        $id_kategori = $this->request->getPost('id_kategori');
        $nama = $this->request->getPost('nama');
        $keterangan = $this->request->getPost('keterangan');
        $dataDetail = $this->request->getPost('data');
        $no_ref_trf = $this->request->getPost('no_ref_trf');
        if ($id != "") {
            $id = decrypt($id);
        }
        if ($id_vendor != "") {
            $id_vendor = decrypt($id_vendor);
        }
        $dataHeader = [
            "id_vendor" => !empty($id_vendor) ? $id_vendor : null,
            "tanggal" => $tanggal,
            "status" => $statusData,
            "jenis_transaksi" => 2,
            "id_gudang" => $id_gudang,
            "id_kategori" => $id_kategori,
            "keterangan" => $keterangan,
            "no_ref_trf" => $no_ref_trf,
            "nama" => $nama
        ];
        if ($id) {
            $dataHeader['updated_at'] = date("Y-m-d H:i:s");
            $dataHeader['updated_by'] = $this->get_userid();
        } else {
            $dataHeader['created_at'] = date("Y-m-d H:i:s");
            $dataHeader['created_by'] = $this->get_userid();
        }
        // print_r($data);exit;
        $res = $this->mRef->trxInsertUpdateRecord($dataHeader, $id, $dataDetail);
        if ($res) {
            $status = true;
            $msg = "Data berhasil disimpan!";
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;
        return $this->response->setJSON($build_array);
    }

    function getLastStock()
    {
        $idGudangTujuan = $this->request->getPost("idGudangTujuan");
        $idGudangAsal = $this->request->getPost("idGudangAsal");
        $idBarang = $this->request->getPost("idBarang");
        $data = [];
        $idBarang = decrypt($idBarang);
        $results = $this->mBarangMasuk->getLastStokBarang($idBarang, $idGudangTujuan);
        if (!empty($idGudangAsal)) {
            $resGudangAsal = $this->mBarangMasuk->getLastStokBarang($idBarang, $idGudangAsal);
        }
        $data['status'] = true;
        $data['stok'] = !empty($results) ? $results->stok : 0;
        $data['stokAsal'] = !empty($resGudangAsal) ? $resGudangAsal->stok : 0;

        return $this->response->setJSON($data);
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

    public function print($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        $dompdf = new \Dompdf\Dompdf();
        // Set Dompdf options for portrait orientation
        $dompdf->setPaper('A4', 'portrait');

        $this->data['data'] = [];
        if ($id != "") {
            $id = decrypt($id);
            // dd($id);
            // die;
            $resData = $this->mRef->getData($id);

            $sort = [
                [
                    'field' => 'uk.id',
                    'dir' => 'ASC'
                ]
            ];
            $params['id_gudang'] =
                $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang));
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
            $this->data['data'] = !empty($resData) ? $resData : [];
            $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
        }
        $html = view($this->views . '\barang_keluar_print', $this->data);

        $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
    exit;
    }
}
