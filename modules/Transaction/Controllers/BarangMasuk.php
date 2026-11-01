<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\PackModel;
use Modules\Referensi\Models\PerusahaanModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\BarangMasukDetailModel;
use Modules\Transaction\Models\BarangMasukModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\ItemTransferDetailModel;
use Modules\Transaction\Models\ItemTransferModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
    protected $mProses;
    protected $mPerusahaan;
    protected $mPack;

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
        $this->mProses = new ProsesProduksiModel();
        $this->mPerusahaan = new PerusahaanModel();
        $this->mPack = new PackModel();
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
        $sortPerusahaan = [
            [
                'field' => 'nama_perusahaan',
                'dir' => 'ASC'
            ]
        ];

        $dataSatuan = $this->mSatuan->getData(null, 0, 99999, $sortSatuan);
        $dataJenisBarang = $this->mJenisBarang->getData(null, 0, 99999, $sortJenisBarang);
        $this->data['satuan'] = $dataSatuan;
        $this->data['jenisBarang'] = $dataJenisBarang;
        $this->data['superadmin'] = $this->auth->isSuperadmin();
        if (!$this->auth->isSuperadmin()) {
            if (empty($this->currentUser->id_perusahaan)) {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => 1]);
            }
            else {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => $this->currentUser->id_perusahaan]);
            }
            $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) || $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
        }
        else {
            $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
            $this->data['user_perusahaan'] = null;
        }
        return view($this->views . '\barang_masuk_list', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $id_perusahaan = $this->request->getPost('id_perusahaan');

        $params = [
            'id_perusahaan' => $id_perusahaan
        ];

        if (!$this->auth->isSuperadmin()) {
            if (empty($this->currentUser->id_perusahaan)) {
                $params['id_perusahaan'] = 1;
            }
            else {
                $params['id_perusahaan'] = $this->currentUser->id_perusahaan;
            }
            $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) || $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
        }

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
            // if ($row->status != 0 && $row->id_kategori == 3) {
                $atr_other['title'] = 'Print';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            // }

            // if ($row->status != 0 && $row->id_kategori == 12) {
                $atr_other['title'] = 'Print Faktur';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print-faktur/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            // }

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
                    "no_ref_trf" => $row->no_ref_trf,
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
            $results = null;
            if (!empty($resData->no_ref_trf)) {
                $results = $this->mTrf->getData(null, 0, 1, null, null, ['kode_transaksi' => $resData->no_ref_trf]);
                if (is_array($results) && !empty($results)) {
                    $results = $results[0];
                }
            }
            else if (empty($resData->no_ref_trf) && isset($resData->id_proses) && isset($resData->id_cmt)) {
                $results = $this->mTrf->getData(null, 0, 1, null, null, ['id_proses' => $resData->id_proses, 'id_cmt' => $resData->id_cmt]);
                if (is_array($results) && !empty($results)) {
                    $results = $results[0];
                }
            }

            $this->data['resData'] = $resData;
            $this->data['detail'] = json_encode($resDataDetail);

            $resDataDetSO = null;
            if ($resData->id_kategori == 12 || $resData->id_kategori == 1 || $resData->id_kategori == 15) {
                $resDataDetSO = $this->mRef->getDataDetSO($id);
            } else if (!empty($results) && is_object($results) && !empty($results->id)) {
                $resDataDetSO = $this->mTrfDet->getDataDetSO($results->id);
            }
            if (!empty($resDataDetSO) && is_array($resDataDetSO)) {
                foreach ($resDataDetSO as &$rSO) {
                    $q = (float)($rSO->qty ?? 0);
                    $h = (float)($rSO->harga ?? 0);
                    $a = (float)($rSO->amount ?? 0);
                    if ($a == 0 && $q > 0 && $h > 0) {
                        $rSO->amount = $q * $h;
                    }
                }
            }
            $this->data['dataSO'] = json_encode($resDataDetSO);
        }
        $reDataKategori = $this->mBarangMasuk->getRefKategoriPersedian();
        $sortGudang = [
            [
                'field' => 'nama_gudang',
                'dir' => 'ASC'
            ]
        ];
        $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
        
        $sortProses = [
            [
                'field' => 'nama',
                'dir' => 'ASC'
                ]
            ];
        $resDataProses = $this->mProses->getData(null, 0, 99999, $sortProses);

        $sortOperator = [
            [
                'field' => 'nama_operator',
                'dir' => 'ASC'
            ]
        ];
        $sortPerusahaan = [
        [
            'field' => 'nama_perusahaan',
            'dir' => 'ASC'
            ]
        ];
        $resDataOperator = $this->mOperator->getData(null, 0, 99999, $sortOperator);
        $this->data['kategori']    = $reDataKategori;
        $this->data['gudang']    = $resDataGudang;
        $this->data['superadmin'] = $this->auth->isSuperadmin();
        if (!$this->auth->isSuperadmin()) {
            if (empty($this->currentUser->id_perusahaan)) {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => 1]);
            }
            else {
                $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => $this->currentUser->id_perusahaan]);
            }
            $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) || $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
        }
        else {
            $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
            $this->data['user_perusahaan'] = null;
        }
        $this->data['proses']    = $resDataProses;
        $this->data['data_cmt']    = $resDataOperator;
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
        $this->data['role_id'] = session()->get('role_id');

        $sortPack = [
            [
                'field' => 'id',
                'dir' => 'ASC'
            ]
        ];
        $dataPack = $this->mPack->getData(null, 0, 99999, $sortPack);
        $this->data['pack'] = $dataPack;
        // dd($this->data);
        return view($this->views . '\barang_masuk_form', $this->data);
    }

    function save()
    {

        $msg    = "Data gagal disimpan !";
        $status = false;
        $id = $this->request->getPost('id');
        $tanggal = $this->request->getPost('tanggal');
        if (!empty($tanggal)) {
            $parsedTgl = date('Y-m-d', strtotime(str_replace('/', '-', $tanggal)));
            if ($parsedTgl > date('Y-m-d')) {
                return $this->response->setJSON([
                    'status' => false,
                    'msg'    => 'Tanggal transaksi tidak boleh melebihi hari ini (' . date('d F Y') . ')!'
                ]);
            }
        }
        $statusData = $this->request->getPost('status');
        $id_gudang = $this->request->getPost('id_gudang');
        $id_perusahaan = $this->request->getPost('id_perusahaan');
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

        $today = date('Y-m-d');
        if (!empty($tanggal) && $tanggal > $today) {
            $build_array['message'] = "Tanggal transaksi tidak boleh melebihi hari ini ($today)!";
            $build_array['status'] = false;
            return $this->response->setJSON($build_array);
        }

        $dataHeader = [
            // "id_buyer" => !empty($id_buyer) ? $id_buyer : null,
            "tanggal" => $tanggal,
            "status" => $statusData,
            "jenis_transaksi" => 1,
            "id_gudang" => $id_gudang,
            "id_perusahaan" => $id_perusahaan,
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

        if($id_kategori == 12 || $id_kategori == 1 || $id_kategori == 15){
            // Fallback pengaman: jika no_ref_trf diisi namun id_proses / id_cmt kosong dari frontend
            if (!empty($no_ref_trf) && (empty($id_proses) || empty($id_cmt) || $id_cmt == -11)) {
                $trfRef = $this->db->table('trans_barang_trf_header')->where('kode_transaksi', $no_ref_trf)->get()->getRow();
                if (!empty($trfRef)) {
                    if (empty($id_proses) && !empty($trfRef->id_proses)) {
                        $id_proses = $trfRef->id_proses;
                    }
                    if ((empty($id_cmt) || $id_cmt == -11) && !empty($trfRef->id_cmt)) {
                        $id_cmt = $trfRef->id_cmt;
                    }
                }
            }

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

    // TAMBAHAN (2026-08-09): "Batalkan Approve" - membalik semua efek approve
    // (stok trans_lots/trans_barang, dan utk Tipe Penerimaan Hasil Produksi/
    // Sample-Perbaikan juga trans_barang_masuk_produksi/trans_produksi_operator/
    // qty_prod WO) & kembalikan status dokumen ke Draft. Lihat
    // BarangMasukModel::cancelApprove().
    //
    // TAMBAHAN (2026-08-09): dibatasi HANYA utk role Superadmin (1), Admin
    // Aplikasi (2), dan Admin Keuangan (4) - permintaan eksplisit, terlepas
    // dari izin allow_approve yang mungkin diberikan ke role lain lewat menu
    // Roles. Dicek jg di sisi tombol (barang_masuk_form.php) supaya role
    // lain tidak lihat tombolnya sama sekali, tapi pengecekan di sini yang
    // jadi penentu utama krn tombol saja bisa di-bypass dari luar UI.
    public function cancelApprove()
    {
        $msg    = "Approve gagal dibatalkan!";
        $status = false;
        $id = $this->request->getPost('id');

        if (!in_array((int) session()->get('role_id'), [1, 2, 4, 5], true)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Hanya Superadmin, Admin Aplikasi, Admin Keuangan, dan Admin Citraknitt yang dapat membatalkan Approve!',
            ]);
        }

        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        }

        try {
            $id = decrypt($id);
            $res = $this->mRef->cancelApprove($id, $this->get_userid());
            if ($res) {
                $status = true;
                $msg = "Approve berhasil dibatalkan, dokumen kembali ke Draft.";
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;
        return $this->response->setJSON($build_array);
    }

    /**
     * Cari harga transaksi Barang Masuk terakhir berdasarkan kombinasi
     * Proses + CMT (header) dan Style (detail) yang sama.
     * Khusus dipakai untuk auto-isi harga perusahaan "Citra Knitt" (id_perusahaan dikirim dari form).
     */
    function getLastPrice()
    {
        $id_proses = $this->request->getGet('id_proses');
        $id_cmt = $this->request->getGet('id_cmt');
        $style = $this->request->getGet('style');
        $id_perusahaan = $this->request->getGet('id_perusahaan');

        $result = $this->mRef->getLastHargaByProsesCmtStyle($id_proses, $id_cmt, $style, $id_perusahaan);

        $data['status'] = !empty($result);
        $data['harga'] = !empty($result) ? $result->harga : 0;
        $data['tgl_transaksi'] = !empty($result) ? $result->tgl_transaksi : null;

        return $this->response->setJSON($data);
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

            $resDataDetail = $this->mRefDet->getDataPrint(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            $results = $this->mTrf->getDataByProsesAndOperator($resData->id_proses, $resData->id_cmt);
            
            $dtKonsumen = [];
            $resDataDetSO = $this->mRef->getDataDetSO($id);
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
        $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
        exit;
    }

    public function print_excel_lists($from_date, $to_date){

        $fileName = "BTM-List.xlsx";

        $id_perusahaan = $this->request->getGet('id_perusahaan');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
        $id_perusahaan = !empty($id_perusahaan) ? $id_perusahaan : null;

        $results = $this->mRef->get_export($tanggal_sql_from, $tanggal_sql_to, $id_perusahaan);

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Barang Masuk (BTM) '. $title)

               ->setCellValue('A4', 'TANGGAL TRANSAKSI')
               ->setCellValue('B4', 'TIPE TRANSAKSI')
               ->setCellValue('C4', 'KODE TRANSAKSI')
               ->setCellValue('D4', 'JENIS TRANSAKSI')
               ->setCellValue('E4', 'NO REF TRANSFER')
               ->setCellValue('F4', 'KODE SALES ORDER')
               ->setCellValue('G4', 'NAMA BUYER')
               ->setCellValue('H4', 'STYLE')
               ->setCellValue('I4', 'DESKRIPSI')
               ->setCellValue('J4', 'GUDANG PENGIRIM')
               ->setCellValue('K4', 'NAMA PROSES')
               ->setCellValue('L4', 'NAMA CMT')
               ->setCellValue('M4', 'COLOR')
               ->setCellValue('N4', 'KODE UKURAN')
               ->setCellValue('O4', 'KETERANGAN')
               ->setCellValue('P4', 'NOMOR MESIN')
               ->setCellValue('Q4', 'JAM MESIN')
               ->setCellValue('R4', 'NILAI MESIN')
               ->setCellValue('S4', 'QTY')
               ->setCellValue('T4', 'HARGA')
               ->setCellValue('U4', 'AMOUNT')
               ->setCellValue('V4', 'STATUS')
               ->setCellValue('W4', 'TGL SCAN');

            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
            ];

            $stylexArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
            ];

            $stylexArrayFooter = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
                'font' => [
                    'bold' => true, // ✅ bikin teks tebal
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, // teks rata kanan
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'C5D9F1', // 💡 warna kuning muda, format argb = AARRGGBB
                    ],
                ],
            ];

            $stylexArraySubFooter = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
                'font' => [
                    'bold' => true, // ✅ bikin teks tebal
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, // teks rata kanan
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFD3D3D3', // 💡 warna kuning muda, format argb = AARRGGBB
                    ],
                ],
            ];

            $stylexArraySubFooter2 = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
                'font' => [
                    'bold' => true, // ✅ bikin teks tebal
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, // teks rata kanan
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFD3D3D3', // 💡 warna kuning muda, format argb = AARRGGBB
                    ],
                ],
            ];

            $styleArray_header = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
            ];

            $style_bodyRight = [
                'borders' => [
                    'right' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
            ];
            $style_bodyTop = [
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
            ];
            $style_bodyBottom = [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
            ];
            
        $sheets->getActiveSheet()->freezePane('D5');
        $gets->getStyle('A4:W4')->applyFromArray($styleArray_header);
        
        // set mergecell
        $sheets->getActiveSheet()->mergeCells('A2:W2');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(15);
          $gets->getColumnDimension('C')->setWidth(17);
          $gets->getColumnDimension('D')->setWidth(35);
          $gets->getColumnDimension('E')->setWidth(17);
          $gets->getColumnDimension('F')->setWidth(17);
          $gets->getColumnDimension('G')->setWidth(25);
          $gets->getColumnDimension('H')->setWidth(25);
          $gets->getColumnDimension('I')->setWidth(35);
          $gets->getColumnDimension('J')->setWidth(25);
          $gets->getColumnDimension('K')->setWidth(35);
          $gets->getColumnDimension('L')->setWidth(35);
          $gets->getColumnDimension('M')->setWidth(45);
          $gets->getColumnDimension('N')->setWidth(10);
          $gets->getColumnDimension('O')->setWidth(20);
          $gets->getColumnDimension('P')->setWidth(10);
          $gets->getColumnDimension('Q')->setWidth(10);
          $gets->getColumnDimension('R')->setWidth(10);
          $gets->getColumnDimension('S')->setWidth(15);
          $gets->getColumnDimension('T')->setWidth(15);
          $gets->getColumnDimension('U')->setWidth(30);
          $gets->getColumnDimension('V')->setWidth(10);
          $gets->getColumnDimension('W')->setWidth(25);

        // end set width
          $gets->getStyle('A4:W4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:W4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W'
        );

        for ($i=0; $i < 23 ; $i++) { 

                $sheets->getActiveSheet()->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('C5D9F1');
                $sheets->getActiveSheet()->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getEndColor()->setARGB('C5D9F1');

            $sheets->getActiveSheet()->getStyle($indexs[$i].'4')
                    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                    
            $gets->getStyle($indexs[$i].'4')->applyFromArray($styleArray_header);
           
        }

        $ix = 5;
        $is = 0;
        
        $length = $ix;

        if(!empty($results)){
            $length += count($results);
        }

        $qty = 0;
        $amount = 0;
        
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $qty += $r->qty;
            $amount += $r->amount;

            $status_data = "Draft";
            if (!empty($r->status) && $r->status == 1) {
                $status_data = "Approved";
            }

            $print_type = !empty($r->print_type) ? $r->print_type : '-';

            if ($print_type == 1) {
                $print_type = "PRODUKSI";
            }
            else if ($print_type == 2) {
                $print_type = "PERBAIKAN";
            }

            $tglTransaksiFormatted = !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : (!empty($r->tanggal) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-");

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, $tglTransaksiFormatted)
                    ->setCellValue('B'.$ix, $print_type)
                    ->setCellValue('C'.$ix, !empty($r->kode_transaksi) ? $r->kode_transaksi : "-")
                    ->setCellValue('D'.$ix, !empty($r->jenis_transaksi) ? $r->jenis_transaksi : "-")
                    ->setCellValue('E'.$ix, !empty($r->no_ref_trf) ? $r->no_ref_trf : '-')
                    ->setCellValue('F'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : '-')
                    ->setCellValue('G'.$ix, !empty($r->nama_buyer) ? $r->nama_buyer : '-')
                    ->setCellValue('H'.$ix, !empty($r->style) ? $r->style : '-')
                    ->setCellValue('I'.$ix, !empty($r->deskripsi) ? $r->deskripsi : '-')
                    ->setCellValue('J'.$ix, !empty($r->gudang_pengirim) ? $r->gudang_pengirim : '-')
                    ->setCellValue('K'.$ix, !empty($r->nama_proses) ? $r->nama_proses : '-')
                    ->setCellValue('L'.$ix, !empty($r->nama_cmt) ? $r->nama_cmt : '-')
                    ->setCellValue('M'.$ix, !empty($r->color) ? $r->color : '-')
                    ->setCellValue('N'.$ix, !empty($r->kode_ukuran) ? $r->kode_ukuran : '-')
                    ->setCellValue('O'.$ix, !empty($r->keterangan) ? $r->keterangan : '-')
                    ->setCellValue('P'.$ix, !empty($r->nomor_mesin) ? $r->nomor_mesin : '-')
                    ->setCellValue('Q'.$ix, !empty($r->jam_mesin) ? $r->jam_mesin : '-')
                    ->setCellValue('R'.$ix, !empty($r->nilai_mesin) ? $r->nilai_mesin : '-')
                    ->setCellValue('S'.$ix, !empty($r->qty) ? $r->qty : 0)
                    ->setCellValue('T'.$ix, !empty($r->harga) ? $r->harga : 0)
                    ->setCellValue('U'.$ix, !empty($r->amount) ? $r->amount : 0)
                    ->setCellValue('V'.$ix, $status_data)
                    ->setCellValue('W'.$ix, !empty($r->tgl_scan) ? $r->tgl_scan : '-');

            $gets->getStyle('A'.$ix.':W'.$ix)->applyFromArray($stylexArray);
            $gets->getStyle('B'.$ix)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheets->getActiveSheet()->getStyle("T" . $ix .":U" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':R'. $length);
        
        $gets->getStyle('A'.$length.':W'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('S'.$length, $qty);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('U'.$length, $amount);

        $gets->getStyle("U" . $length)->getNumberFormat()
                ->setFormatCode('#,##0.00');
       
        
        
        
        
        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); // download file 
        exit;
    }

    public function panduan()
    {
        $this->data['guide_title']        = "Panduan Barang Masuk";
        $this->data['guide_subtitle']     = "Modul Transaksi Penerimaan Barang Masuk (Incoming Goods)";
        $this->data['guide_version']      = "1.0 (Resmi)";
        $this->data['guide_release_date'] = "22 Agustus 2026";
        $this->data['guide_module']       = "Persediaan & Produksi / Transaksi";
        $this->data['back_url']           = base_url('trans/incoming-goods');

        return view($this->views . '\vbarang_masuk_guide', $this->data);
    }
}
