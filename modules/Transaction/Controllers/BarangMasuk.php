<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\BarangMasukModel;
use Modules\Transaction\Models\BarangMasukDetailModel;
use Modules\Transaction\Models\ItemTransferDetailModel;
use Modules\Transaction\Models\ItemTransferModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\KonsumenModel;


class BarangMasuk extends BaseController
{
    protected $mBarang;
    protected $mRef;
    protected $mRefDet;
    protected $mJenisBarang;
    protected $mSatuan;
    protected $mBarangMasuk;
    protected $mTrf;
    protected $mTrfDet;
    protected $mGudang;
    protected $mOperator;
    protected $mkonsumen;

    protected $views = '\Modules\Transaction\Views';
    protected $urlv  = 'trans/incoming-goods';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_TRANSAKSI_BARANG_MASUK";
        $this->mBarang = new BarangModel();
        $this->mJenisBarang = new JenisBarangModel();
        $this->mSatuan = new SatuanModel();
        $this->mBarangMasuk = new IncomingGoodsModel();
        $this->mRef = new BarangMasukModel();
        $this->mRefDet = new BarangMasukDetailModel();
        $this->mGudang = new GudangModel();
        $this->mTrf = new ItemTransferModel();
        $this->mTrfDet = new ItemTransferDetailModel();
        $this->files  = new FileModel();
        $this->mOperator = new OperatorModel();
        $this->mkonsumen = new KonsumenModel();
    }

    public function index()
    {

        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Transaksi Data Barang Masuk ";

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
        return view($this->views . '\barang_masuk_list', $this->data);
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
            $atr_other = null;
            $btnAction = null;
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->urlv . '/edit/';
                $atr_edit['class'] = '';
            }
            // if ($this->_delete) {
            //     $atr_del['title'] = 'Hapus';
            //     $atr_del['url'] = $this->urlv . '/delete/';
            //     $atr_del['class'] = '';
            //     $atr_del['onclick'] = "return confirm('Hapus Data ?')";
            // }
            if ($row->status != 0 && $row->id_kategori == 3) {
                $atr_other['title'] = 'Print';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            }

            if ($row->status != 0 && $row->id_kategori == 12) {
                $atr_other['title'] = 'Print Faktur';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print-faktur/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            }

            if ($atr_edit || $atr_other)
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
                    "proses" => $row->proses,
                    "nama_operator" => $row->nama_operator,
                    "status" => $status
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
            $resData->id_buyer = !empty($resData->id_buyer) ? encrypt($resData->id_buyer) : null;
            $sort = [
                [
                    'field' => 'uk.id',
                    'dir' => 'ASC'
                ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            foreach ($resDataDetail as &$rowData) {
                $rowData->id_barang = encrypt($rowData->id_barang);
            }
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            

            $this->data['resData'] = $resData;
            $this->data['detail'] = json_encode($resDataDetail);

            if($resData->id_kategori == 12){
                $resDataDetSO = !empty($results) ? $this->mRef->getDataDetSO($id) : null;
                $this->data['dataSO'] = json_encode($resDataDetSO);
            }else{
                $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
                $this->data['dataSO'] = json_encode($resDataDetSO);
            }
        }
        $reDataKategori = $this->mBarangMasuk->getRefKategoriPersedian();
        $sortGudang = [
            [
                'field' => 'nama_gudang',
                'dir' => 'ASC'
            ]
        ];
        $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
        $this->data['kategori']    = $reDataKategori;
        $this->data['gudang']    = $resDataGudang;
        // dd($this->data['resData']);
        $this->data['titlehead'] = "Form Barang Masuk";

        $sortOperator = [
        [
            'field' => 'nama_operator',
            'dir' => 'ASC'
        ]
        ];
        $dataOperator = $this->mOperator->getData(null, 0, 99999, $sortOperator);
        $this->data['operator']    = $dataOperator;
        // dd($this->data);
        return view($this->views . '\barang_masuk_form', $this->data);
    }

    function save()
    {

        $msg    = "Data gagal disimpan !";
        $status = false;
        $id = $this->request->getPost('id');
        $tanggal = $this->request->getPost('tanggal');
        $statusData = $this->request->getPost('status');
        $id_gudang = $this->request->getPost('id_gudang');
        $id_buyer = $this->request->getPost('id_buyer');
        $id_kategori = $this->request->getPost('id_kategori');
        $nama = $this->request->getPost('nama');
        $keterangan = $this->request->getPost('keterangan');
        $dataDetail = $this->request->getPost('data');
        $no_ref_trf = $this->request->getPost('no_ref_trf');

        $id_proses = $this->request->getPost("id_proses");
        $id_cmt = $this->request->getPost("id_cmt");
        $nomor_mesin = $this->request->getPost("nomor_mesin");
        $jam_mesin = $this->request->getPost("jam_mesin");
        $nilai_mesin = $this->request->getPost("nilai_mesin");

        $dataProduksi = $this->request->getPost("dataProduksi");

        if ($id != "") {
            $id = decrypt($id);
        }
        if ($id_buyer != "") {
            $id_buyer = decrypt($id_buyer);
        }

        $dataHeader = [
            // "id_buyer" => !empty($id_buyer) ? $id_buyer : null,
            "tanggal" => $tanggal,
            "status" => $statusData,
            "jenis_transaksi" => 1,
            "id_gudang" => $id_gudang,
            "id_kategori" => $id_kategori,
            "no_ref_trf" => $no_ref_trf,
            "keterangan" => $keterangan,
            "nama" => $nama,
            // "id_proses" => $id_proses,
            // "id_cmt" => $id_cmt,
            // "jml_qc" => $jml_qc,
            // "jml_mesin" => $jml_mesin,
            // "jml_lain" => $jml_lain,
        ];

        if(!empty($id_buyer)){
            $dataHeader['id_buyer'] = $id_buyer;
        }

        if($id_kategori == 12){
            $dataHeader['id_proses'] = $id_proses;
            $dataHeader['id_cmt'] = !empty($id_cmt) ? $id_cmt : -11 ;
            if (!empty($nomor_mesin)) {
                $dataHeader['nomor_mesin'] = $nomor_mesin;
            }
            if (!empty($jam_mesin)) {
                $dataHeader['jam_mesin'] = $jam_mesin;
            }
            if (!empty($nilai_mesin)) {
                $dataHeader['nilai_mesin'] = $nilai_mesin;
            }
        }

        if ($id) {
            $dataHeader['updated_at'] = date("Y-m-d H:i:s");
            $dataHeader['updated_by'] = $this->get_userid();
        } else {
            $dataHeader['created_at'] = date("Y-m-d H:i:s");
            $dataHeader['created_by'] = $this->get_userid();
        }
        // print_r(json_encode($dataHeader));exit;
        $res = $this->mRef->trxInsertUpdateRecord($dataHeader, $id, $dataDetail, $dataProduksi);
        if ($res['status']) {
            $status = true;
            $msg = "Data berhasil disimpan!";
        }else{
            $msg = $res['message'];
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
            $resData = $this->mRef->getData($id);

            $sort = [
            [
                'field' => 'uk.id',
                'dir' => 'ASC'
            ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
            $this->data['data'] = !empty($resData) ? $resData : [];
            $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
        }
        $html = view($this->views . '\barang_masuk_print', $this->data);

        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
        exit;
    }


    public function print_faktur($id = null)
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
            $resData = $this->mRef->getData($id);

            $sort = [
            [
                'field' => 'uk.id',
                'dir' => 'ASC'
            ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            
            $dtKonsumen = [];
            $resDataDetSO = !empty($results) ? $this->mRef->getDataDetSO($id) : null;
            if(!empty($resDataDetSO)){
                $id_konsumen = $resDataDetSO[0]->id_konsumen;
                $dtKonsumen = $this->mkonsumen->getData($id_konsumen);
            }
            $this->data['dataSO'] = ($resDataDetSO);
            
            $this->data['dtKonsumen'] = !empty($dtKonsumen) ? $dtKonsumen : [];
            $this->data['data'] = !empty($resData) ? $resData : [];
            // $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
            // dd($this->data);
        }
        $html = view($this->views . '\barang_masuk_faktur_print', $this->data);

        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
        exit;
    }
}
