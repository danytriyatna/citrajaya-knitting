<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\BarangKeluarDetailModel;
use Modules\Transaction\Models\BarangKeluarModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\ItemTransferDetailModel;
use Modules\Transaction\Models\ItemTransferModel;
use Modules\Transaction\Models\OutgoingGoodsModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $start      = !empty($this->request->getPost('start')) ? (int)$this->request->getPost('start') : 0;
        $limit      = !empty($this->request->getPost('length')) ? (int)$this->request->getPost('length') : 10;
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mRef->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mRef->getDataCnt($filters, $params);
        $totaldata = $this->mRef->getDataCnt(null, $params);
        $maxpage = ($limit > 0) ? ceil($totalfiltered / $limit) : 1;

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
            $atr_other['title'] = 'Print';
            $atr_other['target'] = "blank";
            $atr_other['url'] = $this->urlv . '/print/';
            $atr_other['class'] = '';
            $atr_other['icon_class'] = 'fa-print';
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
            if (!empty($resDataDetail) && is_array($resDataDetail)) {
                $woSisaMap = [];
                if (!empty($resData->no_ref_wo)) {
                    $woDataRows = $this->mRefDetSO->getDataByWalkorder($resData->no_ref_wo, $resData->id_gudang, $id);
                    if (!empty($woDataRows)) {
                        foreach ($woDataRows as $wRow) {
                            $key = $wRow->id_barang . '|' . $wRow->lot_id . '|' . $wRow->pack_id;
                            $woSisaMap[$key] = (float) $wRow->qty_sisa;
                        }
                    }
                }

                foreach ($resDataDetail as &$rowData) {
                    if ($rowData->qty_sisa === null || $rowData->qty_sisa === '') {
                        $key = $rowData->id_barang . '|' . $rowData->lot_id . '|' . $rowData->pack_id;
                        if (isset($woSisaMap[$key])) {
                            $rowData->qty_sisa = $woSisaMap[$key];
                        } else {
                            $rowData->qty_sisa = $rowData->qty;
                        }
                    }
                }
                unset($rowData);
            }
            // foreach ($resDataDetail as &$rowData) {
            //     $rowData->id_barang = encrypt($rowData->id_barang);
            // }
            if (!empty($resData->no_ref_wo)) {
                $db = \Config\Database::connect();
                $resDataDetSO = $db->table('trans_walkorder wo')
                    ->select("
                        wo.id_konsumen,
                        wo.ref_kode as kode_sales_order,
                        (CASE WHEN wo.tipe_id = 1 THEN ts.style ELSE tso.style END) as style,
                        (CASE WHEN wo.tipe_id = 1 THEN ts.deskripsi ELSE tso.deskripsi END) as deskripsi,
                        c.nama as buyer,
                        wo.keterangan_style as color,
                        wo.qty,
                        (CASE WHEN wo.tipe_id = 1 THEN ts.total_harga ELSE tso.total_harga END) as amount
                    ")
                    ->join("ref_konsumen c", "c.id = wo.id_konsumen", "left")
                    ->join("trans_sample ts", "ts.id = wo.ref_id AND wo.tipe_id = 1", "left")
                    ->join("trans_sales_order tso", "tso.id = wo.ref_id AND wo.tipe_id = 2", "left")
                    ->where("wo.kode_walkorder", $resData->no_ref_wo)
                    ->get()->getResult();
            } else {
                $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
                $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
            }
            $this->data['resData'] = $resData;
            $this->data['detail'] = json_encode($resDataDetail);
            $this->data['dataSO'] = json_encode($resDataDetSO);
            // Info "BTM (Kg)" - Sisa Berat (Kg) yang belum dibukukan pemakaiannya di BTK
            // untuk No. WO dokumen ini, supaya sudah terisi begitu form Edit dibuka.
            // Rumus: Total BTM Masuk - Total BTK Approved Sebelumnya + Total Retur
            $rawBtm = !empty($resData->no_ref_wo) ? $this->mRefDetSO->getTotalBeratByWalkorder($resData->no_ref_wo, $resData->id_gudang, $id) : 0;
            $this->data['btmKg'] = $rawBtm > 0 ? number_format((float) $rawBtm, 2, ',', '.') : '0';
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
        $this->data['role_id'] = session()->get('role_id');

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
        $no_ref_wo = $this->request->getPost('no_ref_wo');
        if ($id != "") {
            $id = decrypt($id);
        }
        if ($id_vendor != "") {
            $id_vendor = decrypt($id_vendor);
        }

        // Validasi server-side: untuk kategori "Pemakaian Bahan Baku atau Barang" (id=5),
        // No. WO wajib diisi sebelum data bisa di-approve (status=1). Validasi di JS bisa
        // dilewati (mis. request langsung ke endpoint ini), jadi harus dipastikan juga di sini.
        if ($id_kategori == 5 && $statusData == 1 && empty($no_ref_wo)) {
            $build_array['message'] = "No. WO wajib diisi untuk kategori Pemakaian Bahan Baku atau Barang sebelum data di-approve!";
            $build_array['status']  = false;
            return $this->response->setJSON($build_array);
        }

        // Validasi server-side: untuk kategori "Pemakaian Bahan Baku atau Barang" (id=5),
        // Total Pemakaian tidak boleh melebihi Total Qty Sisa (baik Tambah Baru maupun Edit).
        if ($id_kategori == 5) {
            $totalQtyPakai = 0;
            $totalQtySisa = 0;
            if (!empty($dataDetail) && is_array($dataDetail)) {
                foreach ($dataDetail as $det) {
                    $q = (float)($det['qty'] ?? 0);
                    $s = (float)($det['qty_sisa'] ?? 0);
                    $totalQtyPakai += $q;
                    $totalQtySisa += $s;

                    if ($s > 0 && $q > ($s + 0.01)) {
                        $namaBarang = $det['nama_barang'] ?? 'Barang';
                        $build_array['message'] = "Qty pemakaian untuk {$namaBarang} (" . number_format($q, 2) . ") tidak boleh melebihi Qty Sisa (" . number_format($s, 2) . ")!";
                        $build_array['status']  = false;
                        return $this->response->setJSON($build_array);
                    }
                }
            }

            if ($totalQtySisa > 0 && $totalQtyPakai > ($totalQtySisa + 0.01)) {
                $build_array['message'] = "Total Pemakaian (" . number_format($totalQtyPakai, 2) . ") tidak boleh melebihi Total Qty Sisa (" . number_format($totalQtySisa, 2) . ")!";
                $build_array['status']  = false;
                return $this->response->setJSON($build_array);
            }
        }

        // Kunci harga: pastikan harga setiap barang keluar selalu mengikuti harga resmi
        // perolehan lot (trans_receive_detail) atau ref_barang, tidak boleh dimanipulasi user.
        if (!empty($dataDetail) && is_array($dataDetail)) {
            $db = \Config\Database::connect();
            foreach ($dataDetail as $idx => $det) {
                $idBarang = !empty($det['id_barang']) ? (is_numeric($det['id_barang']) ? (int)$det['id_barang'] : decrypt($det['id_barang'])) : 0;
                $lotNo = trim((string)($det['lot_no'] ?? ''));
                $authenticPrice = null;

                if (!empty($lotNo) && $lotNo !== '-') {
                    $rowRec = $db->table('trans_receive_detail trd')
                        ->select('trd.price')
                        ->join('trans_receive_header trh', 'trh.id = trd.id_header')
                        ->where('trd.id_barang', $idBarang)
                        ->where('trd.lot_no', $lotNo)
                        ->where('trd.price >', 0)
                        ->where('trd.active', 1)
                        ->orderBy('trh.id', 'DESC')
                        ->get()->getRow();
                    if (!empty($rowRec) && !empty($rowRec->price) && (float)$rowRec->price > 0) {
                        $authenticPrice = (float)$rowRec->price;
                    }
                }

                if (empty($authenticPrice) && $idBarang > 0) {
                    $refB = $db->table('ref_barang')
                        ->select('harga_satuan')
                        ->where('id', $idBarang)
                        ->get()->getRow();
                    if (!empty($refB) && !empty($refB->harga_satuan) && (float)$refB->harga_satuan > 0) {
                        $authenticPrice = (float)$refB->harga_satuan;
                    }
                }

                if (!empty($authenticPrice) && $authenticPrice > 0) {
                    $dataDetail[$idx]['price'] = $authenticPrice;
                }
            }
        }

        if ($statusData == 1 && !empty($dataDetail) && is_array($dataDetail)) {
            foreach ($dataDetail as $det) {
                if (isset($det['price']) && (float)$det['price'] <= 0) {
                    $build_array['message'] = "Harga barang tidak boleh 0 pada saat approve dokumen!";
                    $build_array['status']  = false;
                    return $this->response->setJSON($build_array);
                }
            }
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
            "no_ref_wo" => $no_ref_wo,
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
        try {
            $res = $this->mRef->trxInsertUpdateRecord($dataHeader, $id, $dataDetail);
            if ($res) {
                $status = true;
                $msg = "Data berhasil disimpan!";
            }
        } catch (\Throwable $e) {
            // \Throwable supaya PHP fatal error/TypeError ikut tertangkap juga
            // (\Exception saja tidak menangkap \Error), jadi respons ke browser
            // selalu JSON valid, bukan halaman error 500.
            $status = false;
            $msg = $e->getMessage();
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;
        return $this->response->setJSON($build_array);
    }

    // TAMBAHAN (2026-08-09): "Batalkan Approve" - membalik semua efek approve
    // (stok trans_lots/trans_barang) & kembalikan status dokumen ke Draft.
    // Lihat BarangKeluarModel::cancelApprove().
    //
    // Dibatasi HANYA utk role Superadmin (1), Admin Aplikasi (2), dan Admin
    // Keuangan (4) - permintaan eksplisit, terlepas dari izin allow_approve
    // yang mungkin diberikan ke role lain lewat menu Roles. Dicek jg di sisi
    // tombol (barang_keluar_form.php) supaya role lain tidak lihat tombolnya
    // sama sekali, tapi pengecekan di sini yang jadi penentu utama krn
    // tombol saja bisa di-bypass dari luar UI.
    public function cancelApprove()
    {
        $msg    = "Approve gagal dibatalkan!";
        $status = false;
        $id = $this->request->getPost('id');

        if (!in_array((int) session()->get('role_id'), [1, 2, 4], true)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Hanya Superadmin, Admin Aplikasi, dan Admin Keuangan yang dapat membatalkan Approve!',
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
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
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
        $dompdf->setPaper('A4', 'potrait');

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
            // $params['id_gudang'] =
            $resDataDetail = $this->mRefDet->getDataPrint(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang, "kode_wo" => $resData->no_ref_wo));
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;

            $this->data['data'] = !empty($resData) ? $resData : [];
            $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
        }
        $html = view($this->views . '\barang_keluar_print', $this->data);

        $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
    exit;
    }

    public function print_excel_lists($from_date, $to_date){

        $fileName = "Barang Keluar List.xlsx";

        $id = $this->request->getGet('data_id');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mRef->get_export($tanggal_sql_from, $tanggal_sql_to);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Barang Keluar (BTK) '. $title)

               ->setCellValue('A4', 'NOMOR TRANSAKSI')
               ->setCellValue('B4', 'TANGGAL')
               ->setCellValue('C4', 'TIPE')
               ->setCellValue('D4', 'GUDANG')
               ->setCellValue('E4', 'REF. TRANSFER')
               ->setCellValue('F4', 'REF. WO')
               ->setCellValue('G4', 'ITEM KODE')
               ->setCellValue('H4', 'ITEM DESKRIPSI')
               ->setCellValue('I4', 'LOT')
               ->setCellValue('J4', 'QTY')
               ->setCellValue('K4', 'UNIT')
               ->setCellValue('L4', 'HPP')
               ->setCellValue('M4', 'JUMLAH');

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
            
        $sheets->getActiveSheet()->freezePane('C5');
        $gets->getStyle('A4:M4')->applyFromArray($styleArray_header);

        $sheets->getActiveSheet()->mergeCells('A2:M2');
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        $gets->getColumnDimension('A')->setWidth(18);
        $gets->getColumnDimension('B')->setWidth(20);
        $gets->getColumnDimension('C')->setWidth(40);
        $gets->getColumnDimension('D')->setWidth(30);
        $gets->getColumnDimension('E')->setWidth(18);
        $gets->getColumnDimension('F')->setWidth(18);
        $gets->getColumnDimension('G')->setWidth(20);
        $gets->getColumnDimension('H')->setWidth(27);
        $gets->getColumnDimension('I')->setWidth(15);
        $gets->getColumnDimension('J')->setWidth(15);
        $gets->getColumnDimension('K')->setWidth(17);
        $gets->getColumnDimension('L')->setWidth(17);
        $gets->getColumnDimension('M')->setWidth(17);

        $gets->getStyle('A4:M4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets->getStyle('A4:M4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);


        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L','M'
        );

        for ($i=0; $i < 13 ; $i++) {

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

        $length = $ix;

        if(!empty($results)){
            $length += count($results);
        }

        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            // Jumlah = Qty x HPP - dihitung dari angka aslinya (bukan dari
            // teks yg sudah diformat "-"/0 di kolom J/L), supaya tidak salah
            // kali kalau salah satu kosong.
            $jumlah = (!empty($r->qty) ? (float) $r->qty : 0) * (!empty($r->price) ? (float) $r->price : 0);

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->kode_transaksi) ? $r->kode_transaksi : "-")
                    ->setCellValue('B'.$ix, !empty($r->tanggal) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->kategori) ? $r->kategori : "-")
                    ->setCellValue('D'.$ix, !empty($r->nama_gudang) ? $r->nama_gudang : "-")
                    ->setCellValue('E'.$ix, !empty($r->no_ref_trf) ? $r->no_ref_trf : '-')
                    ->setCellValue('F'.$ix, !empty($r->no_ref_wo) ? $r->no_ref_wo : '-')
                    ->setCellValue('G'.$ix, !empty($r->kode_barang) ? $r->kode_barang : '-')
                    ->setCellValue('H'.$ix, !empty($r->nama_barang) ? $r->nama_barang : '-')
                    ->setCellValue('I'.$ix, !empty($r->lot_no) ? $r->lot_no : '-')
                    ->setCellValue('J'.$ix, !empty($r->qty) ? $r->qty : '-')
                    ->setCellValue('K'.$ix, !empty($r->nama_satuan) ? $r->nama_satuan : '-')
                    ->setCellValue('L'.$ix, !empty($r->price) ? $r->price : 0)
                    ->setCellValue('M'.$ix, $jumlah);

            $gets->getStyle('A'.$ix.':M'.$ix)->applyFromArray($stylexArray);

            $sheets->getActiveSheet()->getStyle("L" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            $sheets->getActiveSheet()->getStyle("M" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ix++;
        }
        
        $gets->getStyle('A:M')->getAlignment()->setWrapText(true);
        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); // download file 
        exit;
    }

    // public function print($id = null)
    // {
    //     if (!$this->auth->loggedIn()) {
    //         return redirect()->to('/auth/login');
    //     }
    //     $dompdf = new \Dompdf\Dompdf();
    //     // Set Dompdf options for portrait orientation
    //     $dompdf->setPaper('A4', 'portrait');

    //     $this->data['data'] = [];
    //     if ($id != "") {
    //         $id = decrypt($id);
    //         // dd($id);
    //         // die;
    //         $resData = $this->mRef->getData($id);

    //         $sort = [
    //             [
    //                 'field' => 'uk.id',
    //                 'dir' => 'ASC'
    //             ]
    //         ];
    //         $params['id_gudang'] =
    //             $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang));
    //         $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
    //         $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
    //         $this->data['data'] = !empty($resData) ? $resData : [];
    //         $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
    //         $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    //     }
    //     $html = view($this->views . '\barang_keluar_print', $this->data);

    //     $dompdf->loadHtml($html);
    // $dompdf->render();
    // $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
    // exit;
    // }

    public function panduan()
    {
        $this->data['guide_title']        = "Panduan Barang Keluar";
        $this->data['guide_subtitle']     = "Modul Transaksi Pengeluaran Barang Keluar (Outgoing Goods / BTK)";
        $this->data['guide_version']      = "1.0 (Resmi)";
        $this->data['guide_release_date'] = "22 Agustus 2026";
        $this->data['guide_module']       = "Persediaan & Produksi / Transaksi";
        $this->data['back_url']           = base_url('trans/outgoing-goods');

        return view($this->views . '\vbarang_keluar_guide', $this->data);
    }
}
