<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use CodeIgniter\Controller;
use Modules\Laporan\Models\LaporanPersediaanModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\PackModel;
use Modules\Referensi\Models\PerusahaanModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\BarangMasukDetailModel;
use Modules\Transaction\Models\BarangMasukModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\ItemTransferDetailModel;
use Modules\Transaction\Models\ItemTransferModel;
use Modules\Transaction\Models\SalesOrderModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ItemTransfer extends BaseController
{
  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/item-transfer';
  protected $mBarang;
  protected $mRef;
  protected $mRefDet;
  protected $mJenisBarang;
  protected $mSatuan;
  protected $mBarangMasuk;
  protected $mGudang;
  protected $mSalesOrder;
  protected $mOperator;
  protected $mRefPersediaan;
  protected $mPack;
  protected $mPerusahaan;

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_TRANSAKSI_BARANG_MASUK";
    $this->mBarang = new BarangModel();
    $this->mJenisBarang = new JenisBarangModel();
    $this->mSatuan = new SatuanModel();
    $this->mBarangMasuk = new IncomingGoodsModel();
    $this->mRef = new ItemTransferModel();
    $this->mRefDet = new ItemTransferDetailModel();
    $this->mGudang = new GudangModel();
    $this->mSalesOrder = new SalesOrderModel();
    $this->mOperator = new OperatorModel();
    $this->mRefPersediaan = new LaporanPersediaanModel();
    $this->mPack = new PackModel();
    $this->mPerusahaan = new PerusahaanModel();
  }

  public function index()
  {

    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Item Transfer";

    $sortPerusahaan = [
      [
        'field' => 'nama_perusahaan',
        'dir' => 'ASC'
      ]
    ];

    $this->data['superadmin'] = $this->auth->isSuperadmin();
    if (!$this->auth->isSuperadmin()) {
      if (empty($this->currentUser->id_perusahaan)) {
        $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => 1]);
      } else {
        $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => $this->currentUser->id_perusahaan]);
      }
      $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) ? $this->currentUser->id_perusahaan : 1;
      $this->data['lock_perusahaan'] = true;
    } else {
      $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
      $this->data['user_perusahaan'] = null;
      $this->data['lock_perusahaan'] = false;
    }

    return view($this->views . '\item_transfer_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $isApprove      = $this->request->getPost('isApprove');

    $params = [];
    if ($isApprove) {
      $params['status'] = "2";
    }

    $id_perusahaan = $this->request->getPost('id_perusahaan');
    if (!empty($id_perusahaan)) {
      $params['id_perusahaan'] = $id_perusahaan;
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
      // if ($row->status != 0) {
        $atr_other['title'] = 'Print';
        $atr_other['target'] = "blank";
        $atr_other['url'] = $this->urlv . '/print/';
        $atr_other['class'] = '';
        $atr_other['icon_class'] = 'fa-print';
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
          "gudang_asal" => $row->gudang_asal,
          "gudang_tujuan" => $row->gudang_tujuan,
          "nama_operator" => !empty($row->nama_operator) ? $row->nama_operator : "NON CMT",
          "keterangan" => $row->keterangan,
          "status" => $status,
          "id_gudang_tujuan" =>  $row->id_gudang_tujuan
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function lists_ref()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $isApprove      = $this->request->getPost('isApprove');
    $kategori = $this->request->getPost('kategori');
    $id_perusahaan = $this->request->getPost('id_perusahaan');

    $params = [];
    if ($isApprove) {
      $params['status'] = "2";
    }

    if (!empty($id_perusahaan)) {
      $params['id_perusahaan'] = $id_perusahaan;
    }

    // Jangan batasi kaku ref_produksi agar semua transfer approved (baik yang
    // membawa SPK produksi ref_produk = 1 maupun transfer barang/sample biasa ref_produk = 0)
    // dapat dijadikan referensi di Barang Masuk
    $ref_produksi = $this->request->getPost('ref_produksi');
    if (!empty($ref_produksi)) {
      $params['ref_produksi'] = $ref_produksi;
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
      if ($row->status != 0) {
        $atr_other['title'] = 'Print';
        $atr_other['target'] = "blank";
        $atr_other['url'] = $this->urlv . '/print/';
        $atr_other['class'] = '';
        $atr_other['icon_class'] = 'fa-print';
      }
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
          "gudang_asal" => $row->gudang_asal,
          "gudang_tujuan" => $row->gudang_tujuan,
          "nama_operator" => !empty($row->nama_operator) ? $row->nama_operator : "NON CMT",
          "keterangan" => $row->keterangan,
          "no_proses" => $row->no_proses,
          "proses" => $row->proses,
          "id_proses" => $row->id_proses,
          "id_cmt" => $row->id_cmt,
          "status" => $status
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function listsSO()
  {
    $id_proses      = $this->request->getPost('id_proses');
    $start          = $this->request->getPost('start');
    $limit          = $this->request->getPost('length');
    $filters        = $this->request->getPost('filter');
    $order          = $this->request->getPost('sort');
    $id_gudang_asal = $this->request->getPost('id_gudang_asal') ?: $this->request->getPost('gudang_asal');
    $id_perusahaan  = $this->request->getPost('id_perusahaan');

    // Auto-detect id_perusahaan dari gudang asal yang dipilih di form
    if (!empty($id_gudang_asal)) {
        $gudangRow = $this->mGudang->getData($id_gudang_asal);
        if (!empty($gudangRow) && !empty($gudangRow->id_perusahaan)) {
            $id_perusahaan = (int)$gudangRow->id_perusahaan;
        }
    }

    if (empty($id_perusahaan)) {
        $id_perusahaan = session()->get('id_perusahaan') ?: ($this->currentUser->id_perusahaan ?? 1);
    }
    $params = [];

    $results = $this->mRef->getUkuranTrans(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mRef->getDataSOUkuranCnt($filters, $params);
    $totaldata = $this->mRef->getDataSOUkuranCnt(null, $params);
    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $isRajutProses = ($id_proses == 1 || in_array((int)$id_proses, [1, 29, 30, 31, 32]));

      if ((int)$id_perusahaan === 2) {
        // === PERUSAHAAN 2 (CITRA KNITT) - BERBASIS BTM APPROVED P2 ===
        // Sumber Kuota : Total BTM Masuk yang SUDAH DI-APPROVE di P2 (BTMC...)
        // Pengurang    : Total TRFC yang SUDAH DIKELUARKAN dari P2 (TRFC...)
        $btmMasukP2 = $this->mRef->getDataBtmP2($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color, $row->style);
        $trfKeluarP2 = $this->mRef->getDataPengurangP2($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color, null, $row->style);
        $sisa = $btmMasukP2 - $trfKeluarP2;
      } else {
        // === PERUSAHAAN 1 (PT CJK) ===
        if ($isRajutProses) {
          // --- P1: PROSES RAJUT (AWAL) ---
          // Sumber Kuota : Sales Order (SO)
          // Pengurang    : Total Surat Jalan Transfer P1 (TRF...) yang sudah dibuat
          $getSisa = $this->mRef->getDataPengurang($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color, null, $row->style);
          $sisa = $row->qty;
          if (!empty($getSisa)) {
            $sisa -= $getSisa;
          }
        } else {
          // --- P1: PROSES SELAIN RAJUT (LANJUTAN SEPERTI LINGKING, OBRAS, WASHING, STEAM, DLL.) ---
          // Sumber Kuota : Total BTM Hasil Rajut Masuk Gudang P1 yang Approved
          // Pengurang    : Total Surat Jalan Transfer P1 (TRF...) yang sudah dibuat untuk proses lanjutan
          $getProsesIdRajut = $this->mRef->getDataRajut($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color, null, $row->style);
          $getSisa = $this->mRef->getDataPengurang($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color, null, $row->style);
          $sisa = $getProsesIdRajut;
          if (!empty($getSisa)) {
            $sisa -= $getSisa;
          }
        }
      }

      if (empty($sisa) || $sisa < 0) {
        $sisa = 0;
      }
      array_push(
        $build_array["data"],
        array(
          "tipe" => $row->tipe,
          "ref_detail_id" => $row->ref_detail_id,
          "tipe_text" => $row->tipe_text,
          "id_konsumen" => $row->id_konsumen,
          "kode_sales_order" => $row->kode_transaksi,
          "style" => $row->style,
          "deskripsi" => $row->deskripsi,
          "buyer" => $row->buyer,
          "color" => $row->color,
          "qty" => $row->qty,
          "amount" => $row->amount,
          "kode_ukuran" => $row->kode_ukuran,
          "qty_ref" => $sisa,
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function form_static($id = null)
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

      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang_asal));
      $resDataDetSO = $this->mRefDet->getDataDetSO($id);
      
      // Deteksi entitas perusahaan dari transaksi atau gudang asal
      $trxPerusahaan = !empty($resData->id_perusahaan) ? (int)$resData->id_perusahaan : null;
      if (empty($trxPerusahaan) && !empty($resData->id_gudang_asal)) {
          $gudangRow = $this->mGudang->getData($resData->id_gudang_asal);
          if (!empty($gudangRow) && !empty($gudangRow->id_perusahaan)) {
              $trxPerusahaan = (int)$gudangRow->id_perusahaan;
          }
      }
      if (empty($trxPerusahaan) && !empty($resData->kode_transaksi)) {
          if (str_starts_with($resData->kode_transaksi, 'TRFC')) {
              $trxPerusahaan = 2;
          } elseif (str_starts_with($resData->kode_transaksi, 'TRF')) {
              $trxPerusahaan = 1;
          }
      }
      if (!empty($trxPerusahaan)) {
          $resData->id_perusahaan = $trxPerusahaan;
      }

      foreach ($resDataDetSO as $key => $row) {
        $isRajutProses = ($resData->id_proses == 1 || in_array((int)$resData->id_proses, [1, 29, 30, 31, 32]));

        if ($trxPerusahaan === 2) {
          // === PERUSAHAAN 2 (CITRA KNITT) - BERBASIS BTM APPROVED P2 ===
          $btmMasukP2 = $this->mRef->getDataBtmP2($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $row->style);
          $trfKeluarP2 = $this->mRef->getDataPengurangP2($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $id, $row->style);
          $sisa = $btmMasukP2 - $trfKeluarP2;
        } else {
          // === PERUSAHAAN 1 (PT CJK) ===
          if ($isRajutProses) {
            $params['kode_transaksi'] = $row->kode_sales_order;
            $params['kode_ukuran'] = $row->kode_ukuran;
            $params['all_color'] = $row->color;
            $getUkuranTrans = $this->mRef->getUkuranTrans(null, null, null, null, null, $params);
            $getSisa = $this->mRef->getDataPengurang($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $id, $row->style);
            $sisa = 0;
            if (!empty($getUkuranTrans[0]->qty)) {
              $sisa = (float)$getUkuranTrans[0]->qty;
            }
            if (!empty($getSisa)) {
              $sisa -= $getSisa;
            }
          } else {
            $getProsesIdRajut = $this->mRef->getDataRajut($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $id, $row->style);
            $getSisa = $this->mRef->getDataPengurang($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $id, $row->style);
            $sisa = $getProsesIdRajut;
            if (!empty($getSisa)) {
              $sisa -= $getSisa;
            }
          }
        }

        if (empty($sisa) || $sisa < 0) {
          $sisa = 0;
        }

        $row->qty_ref = $sisa;
      }

      // dd($resDataDetail);

      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
      $this->data['dataSO'] = json_encode($resDataDetSO);
    }
    $this->data['titlehead'] = "Item Transfer";
    $sortGudang = [
      [
        'field' => 'nama_gudang',
        'dir' => 'ASC'
      ]
    ];
    $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
    $resDataProses = $this->mRef->getDataProses();
    $sortOperator = [
      [
        'field' => 'nama_operator',
        'dir' => 'ASC'
      ]
    ];
    $dataOperator = $this->mOperator->getData(null, 0, 99999, $sortOperator);
    $this->data['operator']    = $dataOperator;
    $this->data['gudang']    = $resDataGudang;
    $this->data['proses']    = $resDataProses;
    $this->data['role_id'] = session()->get('role_id');

    $sortPack = [
          [
              'field' => 'id',
              'dir' => 'ASC'
          ]
    ];
    $dataPack = $this->mPack->getData(null, 0, 99999, $sortPack);
    $this->data['pack'] = $dataPack;

    // Smart default gudang asal berdasarkan perusahaan user login
    $userPerusahaan = !empty($this->currentUser->id_perusahaan) ? $this->currentUser->id_perusahaan : session()->get('id_perusahaan');
    // Data list perusahaan untuk dropdown form
    $sortPerusahaan = [
      [
        'field' => 'nama_perusahaan',
        'dir' => 'ASC'
      ]
    ];
    $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
    if (!empty($resData)) {
        if (!empty($resData->id_perusahaan)) {
            $trxPerusahaan = (int)$resData->id_perusahaan;
        } elseif (!empty($resData->id_gudang_asal)) {
            $gudangRow = $this->mGudang->getData($resData->id_gudang_asal);
            if (!empty($gudangRow) && !empty($gudangRow->id_perusahaan)) {
                $trxPerusahaan = (int)$gudangRow->id_perusahaan;
            }
        }
        if (empty($trxPerusahaan) && !empty($resData->kode_transaksi)) {
            $trxPerusahaan = str_starts_with($resData->kode_transaksi, 'TRFC') ? 2 : 1;
        }
        $resData->id_perusahaan = $trxPerusahaan;
    } else {
        $trxPerusahaan = !empty($userPerusahaan) ? (int)$userPerusahaan : 1;
    }
    $defaultGudangAsal = ($trxPerusahaan === 2) ? 60 : 1;
    $this->data['default_gudang_asal'] = $defaultGudangAsal;
    $this->data['selected_perusahaan'] = $trxPerusahaan;

    return view($this->views . '\item_transfer_form_static', $this->data);
  }

  public function form_static_v2()
  {

    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Item Transfer (Static)";

    return view($this->views . '\item_transfer_form_static_v2', $this->data);
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

      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));

      // foreach ($resDataDetail as &$rowData) {
      //     $rowData->id_barang = encrypt($rowData->id_barang);
      // }


      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
    }

    $this->data['titlehead'] = "Form Item Transfer";
    $sortGudang = [
      [
        'field' => 'nama_gudang',
        'dir' => 'ASC'
      ]
    ];
    $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);

    $this->data['gudang']    = $resDataGudang;
    return view($this->views . '\item_transfer_form', $this->data);
  }

  function dataSO()
  {
    $noWO = $this->request->getGet("noWO");
    if (!empty($noWO)) {
      $idGudang = $this->request->getGet("idGudang");
      $idHeader = $this->request->getGet("idHeader");

      $resDataDetSODet = $this->mRefDet->getDataByWalkorder($noWO, $idGudang, $idHeader);
      $btmKg = $this->mRefDet->getTotalBeratByWalkorder($noWO, $idGudang, $idHeader);

      $db = \Config\Database::connect();
      $woData = $db->table('trans_walkorder wo')
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
        ->where("wo.kode_walkorder", $noWO)
        ->get()->getResult();

      $data = [
        'status'    => true,
        'dataSO'    => !empty($woData) ? $woData : [],
        'dataSODet' => !empty($resDataDetSODet) ? $resDataDetSODet : [],
        'btm_kg'    => $btmKg,
      ];
      return $this->response->setJSON($data);
    }

    $noSO = $this->request->getGet("noSO");
    $id_proses = $this->request->getGet("id_proses");
    $id_cmt = $this->request->getGet("id_cmt");
    $id_perusahaan = $this->request->getGet("id_perusahaan");
    $kode_so = $this->request->getGet("kode_so");

    $data = [];
    $data['status'] = true;

    // 1. KETIKA REFERENSI TRANSFER DIPILIH ($noSO terisi):
    // Berlaku untuk P1 maupun P2. Barang dicek KETAT sesuai referensi dokumen transfer tersebut!
    if (!empty($noSO)) {
      $results = $this->mRef->getDataByNoTrf($noSO);
      $resDataDetSO = !empty($results) ? $this->mRefDet->getDataDetSORef($results->id) : null;
      $resDataDetSODet = !empty($results) ? $this->mRefDet->getDataDetail($results->id) : null;
      $data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : null;
      $data['dataSODet'] = !empty($resDataDetSODet) ? $resDataDetSODet : null;
      return $this->response->setJSON($data);
    }

    // 2. KETIKA TANPA REFERENSI TRANSFER ($noSO kosong):
    // Pisahkan logika P1 dan P2 secara tegas agar tidak saling mempengaruhi!
    if ((int)$id_perusahaan === 2) {
      // KHUSUS P2 (Citra Knitt):
      // Tidak mengikat ke data transfer dan tidak mengikat ke WO.
      // Mengambil daftar acuan barang langsung dari Sales Order aktif (trans_sales_order & trans_sample).
      $soItems = $this->mRef->getDataSOFromSalesOrder($id_proses, $kode_so);
      $data['dataSO'] = !empty($soItems) ? $soItems : [];
      $data['dataSODet'] = null;
      return $this->response->setJSON($data);
    }

    // KHUSUS P1 (PT Citra Jaya Kniting):
    // Tetap pada alur aslinya yang berbasis transfer.
    $results = $this->mRef->getDataByProsesAndOperatorArray($id_proses, $id_cmt, $id_perusahaan);
    $resDataDetSO = [];
    $resDataDetSODet = [];
      
      if (!empty($results)) {
        $dataDetSO = $this->mRefDet->getDataDetSORef($results, true);
        $dataDetSODet = $this->mRefDet->getDataDetail($results, true);

        if (!empty($dataDetSO)) {
            if (is_array($dataDetSO)) {
                $resDataDetSO = array_merge($resDataDetSO, $dataDetSO);
            } else {
                $resDataDetSO[] = $dataDetSO;
            }
        }
        if (!empty($dataDetSODet)) {
            if (is_array($dataDetSODet)) {
                $resDataDetSODet = array_merge($resDataDetSODet, $dataDetSODet);
            } else {
                $resDataDetSODet[] = $dataDetSODet;
            }
        }
      }
      $grouped = [];

      foreach ($resDataDetSO as $row) {
          $row->keterangan = null;
          $key = implode('|', [
              $row->kode_sales_order,
              $row->id_konsumen,
              $row->style,
              $row->color,
              $row->kode_ukuran,
              $row->id_proses
          ]);

          if (!isset($grouped[$key])) {
              // simpan baris pertama
              $grouped[$key] = $row;
              $grouped[$key]->qty_kirim = (int)$row->qty_kirim;
              $grouped[$key]->qty = (int)$row->qty;

              if ($id_perusahaan == 2) {
                  $grouped[$key]->harga = ((int)$row->id_proses === (int)$id_proses)
                      ? $row->harga
                      : 0;
              }
          } else {
              // jumlahkan qty jika sudah ada
              $grouped[$key]->qty_kirim += (int)$row->qty_kirim;
              $grouped[$key]->qty += (int)$row->qty;

              if ($id_perusahaan == 2) {
                  if ((int)$row->id_proses === (int)$id_proses) {
                      $grouped[$key]->harga = $row->harga;
                  } elseif (!isset($grouped[$key]->harga) || $grouped[$key]->harga == 0) {
                      $grouped[$key]->harga = 0;
                  }
              }
          }

          // qty_terima tetap satu (sama untuk semua header)
          $grouped[$key]->qty_terima = (int)$row->qty_terima;

          // hitung ulang sisa
          $grouped[$key]->qty_sisa =
              $grouped[$key]->qty_kirim - $grouped[$key]->qty_terima;
      }

      foreach ($grouped as $key => $value) {
        $value->qty_kirim = $value->qty_sisa;
      }
      $data['dataSO'] = array_values($grouped);
      $data['dataSODet'] = count($resDataDetSODet) > 0 ? $resDataDetSODet : null;
    // if (!empty($resDataDetSO)) {
    //   foreach ($resDataDetSO as &$rowData) {
    //     $rowData->qty_sisa = encrypt($rowData->id_barang);
    //   }
    // }
    
    return $this->response->setJSON($data);
  }

  function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $id = $this->request->getPost('id');
    $tanggal = $this->request->getPost('tanggal');
    $statusData = $this->request->getPost('status');
    $oldStatus = $this->request->getPost('statusFrom') ? $this->request->getPost('statusFrom') : 0;
    $id_gudang_asal = $this->request->getPost('id_gudang_asal');
    $id_gudang_tujuan = $this->request->getPost('id_gudang_tujuan');
    $id_cmt = $this->request->getPost('id_cmt');
    $id_proses = $this->request->getPost('id_proses');
    $keterangan = $this->request->getPost('keterangan');
    $tipe = $this->request->getPost('tipe');
    $dataDetail = $this->request->getPost('data');
    $dataSO = $this->request->getPost('dataSO');

    $refProduksi = $this->request->getPost('ref_produksi');

    if (empty($id_proses)) {
      $build_array['message'] = "Proses harus dipilih!";
      $build_array['status'] = false;
      return $this->response->setJSON($build_array);
    }

    if (empty($id_cmt)) {
      $build_array['message'] = "CMT harus dipilih!";
      $build_array['status'] = false;
      return $this->response->setJSON($build_array);
    }

    if (empty($id_gudang_asal)) {
      $build_array['message'] = "Gudang Asal (Transfer From) harus dipilih!";
      $build_array['status'] = false;
      return $this->response->setJSON($build_array);
    }

    if (empty($id_gudang_tujuan)) {
      $build_array['message'] = "Gudang Tujuan (Transfer To) harus dipilih!";
      $build_array['status'] = false;
      return $this->response->setJSON($build_array);
    }

    if (empty($dataDetail) && empty($dataSO)) {
      $build_array['message'] = "Detail transfer tidak boleh kosong! Tambahkan minimal 1 item SO atau Benang sebelum menyimpan.";
      $build_array['status'] = false;
      return $this->response->setJSON($build_array);
    }

    if ($id != "") {
      $id = decrypt($id);
    }

    // Auto-detect id_perusahaan dari gudang asal atau parameter form
    $id_perusahaan = $this->request->getPost('id_perusahaan');
    if (!empty($id_gudang_asal)) {
        $gudangRow = $this->mGudang->getData($id_gudang_asal);
        if (!empty($gudangRow) && !empty($gudangRow->id_perusahaan)) {
            $id_perusahaan = (int)$gudangRow->id_perusahaan;
        }
    }
    if (empty($id_perusahaan)) {
        $id_perusahaan = session()->get('id_perusahaan') ?: ($this->currentUser->id_perusahaan ?? 1);
    }

    $dataHeader = [
      "id_perusahaan" => $id_perusahaan,
      "id_gudang_asal" => !empty($id_gudang_asal) ? $id_gudang_asal : null,
      "id_gudang_tujuan" => !empty($id_gudang_tujuan) ? $id_gudang_tujuan : null,
      "id_cmt" => !empty($id_cmt) ? $id_cmt : null,
      "id_proses" => !empty($id_proses) ? $id_proses : null,
      "tanggal" => $tanggal,
      "status" => $statusData,
      "keterangan" => $keterangan,
      "tipe" => $tipe,
      "ref_produk" => $refProduksi
    ];

    if ($id) {
      $dataHeader['updated_at'] = date("Y-m-d H:i:s");
      $dataHeader['updated_by'] = $this->get_userid();
    } else {
      $dataHeader['created_at'] = date("Y-m-d H:i:s");
      $dataHeader['created_by'] = $this->get_userid();
    }
    // Kunci harga: pastikan harga setiap item transfer selalu mengikuti harga resmi
    // perolehan lot (trans_receive_detail) atau ref_barang, tidak boleh dimanipulasi user.
    if (!empty($dataDetail) && is_array($dataDetail)) {
        $db = \Config\Database::connect();
        foreach ($dataDetail as $idx => $det) {
            $idBarang = !empty($det['id_barang']) ? (is_numeric($det['id_barang']) ? (int)$det['id_barang'] : decrypt($det['id_barang'])) : 0;
            $lotNo = trim((string)($det['lot_no'] ?? ''));
            $authenticPrice = null;

            if (empty($lotNo) || $lotNo === '-') {
                if (!empty($det['lot_id'])) {
                    $lotObj = $db->table('trans_lots')->where('id', $det['lot_id'])->get()->getRow();
                    if (!empty($lotObj) && !empty($lotObj->lot_no)) {
                        $lotNo = trim((string)$lotObj->lot_no);
                    }
                }
            }

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

    try {
      $res = $this->mRef->trxInsertUpdateRecord($dataHeader, $id, $dataDetail, $dataSO, $oldStatus);
      if ($res) {
        $status = true;
        $msg = "Data berhasil disimpan!";
        $savedId = is_numeric($res) ? $res : (!empty($id) ? (is_numeric($id) ? $id : decrypt($id)) : null);
        if (!empty($savedId)) {
          $build_array['id'] = $savedId;
          $build_array['encrypted_id'] = encrypt($savedId);
        }
      }
    } catch (\Throwable $e) {
      $status = false;
      $msg = $e->getMessage();
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;
    return $this->response->setJSON($build_array);
  }

  // "Batalkan Approve" - membalik semua efek approve (stok & tracking produksi) & kembalikan status ke Draft
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
      $resDataDetSO = $this->mRefDet->getDataDetSO($id);


      $resDataDetail = $this->mRefDet->getDataPrint(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang_tujuan));
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];

    }

    // dd($this->data);
    $html = view($this->views . '\item_transfer_print', $this->data);

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
    exit;
  }

  public function lists_persediaan()
  {
      $start      = $this->request->getPost('start');
      $limit      = $this->request->getPost('length');
      $filters    = $this->request->getPost('filter');
      $order      = $this->request->getPost('sort');

      $idGudang      = $this->request->getPost('idGudang');
      $idWo      = $this->request->getPost('kode_walkorder');
      $params = [];
      if (!empty($idWo)) {
        $params['kode_walkorder'] = $idWo;
      }
      if ($idGudang != "") {

          $params['id_gudang'] = $idGudang;
      } else {
          $build_array = array(
              "data" => array()
          );
          return $this->response->setJSON($build_array);
      }

      $results = $this->mRefPersediaan->getDataPersediaanBarang(null, $start, $limit, $order, $filters, $params);
      $totalfiltered = $this->mRefPersediaan->getDataPersediaanBarangCnt($filters, $params);
      $totaldata = $this->mRefPersediaan->getDataPersediaanBarangCnt(null, $params);
      $maxpage = ceil($totalfiltered / $limit);

      $build_array = array(
          "last_page" => $maxpage,
          "recordsTotal" => $totaldata,
          "recordsFiltered" => $totalfiltered,
          "data" => array()
      );

      foreach ($results as $row) {
          $id = encrypt($row->id_barang);
          $harga = !empty($row->price) ? $row->price * $row->qty : 0;
          $harga_satuan = !empty($row->price) ? $row->price : 0;
          array_push(
              $build_array["data"],
              array(
                  "id"   => $row->id_barang,
                  "nama_barang" => $row->nama_barang,
                  "kode_barang" => $row->kode_barang,
                  "nama_satuan" => $row->nama_satuan,
                  "harga_satuan" => $harga_satuan,
                  "total_harga" => $harga,
                  "qty" => $row->qty,
                  "lot_no" => $row->lot_no,
                  "lot_id" => $row->lot_id,
                  "pack_id" => $row->pack_id,
                  "pack_name" => $row->pack_name,
                  "id_barang" => $row->id_barang,
                  "month" => $row->month,
              )
          );
      }
      return $this->response->setJSON($build_array);
  }


    function getDataProduksiItem(){
      $kata_kunci = $this->request->getPost('kata_kunci');
      $id_proses = $this->request->getPost('id_proses');
      $id_perusahaan = $this->request->getPost('id_perusahaan');
      $id_gudang_asal = $this->request->getPost('id_gudang_asal');
      $id_header = $this->request->getPost('id_header');
      if (!empty($id_header)) {
          $id_header = is_numeric($id_header) ? $id_header : decrypt($id_header);
      } else {
          $id_header = null;
      }

      // Auto-detect id_perusahaan dari gudang asal yang dipilih di form
      if (!empty($id_gudang_asal)) {
          $gudangRow = $this->mGudang->getData($id_gudang_asal);
          if (!empty($gudangRow) && !empty($gudangRow->id_perusahaan)) {
              $id_perusahaan = (int)$gudangRow->id_perusahaan;
          }
      }
      if (empty($id_perusahaan)) {
          $id_perusahaan = session()->get('id_perusahaan') ?: ($this->currentUser->id_perusahaan ?? 1);
      }
      
      $status = false;
      $msg = "Data barang tidak ditemukan !";
      $data  = [];
      $slc  = [];

      try {

        $kt_exp = explode(";", $kata_kunci);
    
        if (count($kt_exp) > 1) {
            $params['kode_transaksi'] = trim($kt_exp[0]);
            if (!empty($kt_exp[1])) $params['key_ukuran'] = trim($kt_exp[1]);
            if (!empty($kt_exp[2])) {
                $rawColor = trim($kt_exp[2]);
                if (strpos($rawColor, '~') !== false) {
                    $params['all_color'] = $rawColor;
                } else {
                    $params['color'] = $rawColor;
                }
            }
        } else {
            $params['kata_kunci'] = $kata_kunci;
        }
        $results = $this->mRef->getUkuranTrans(null, 0, 999, null, null, $params);
        foreach ($results as $r) {
          $isRajutProses = ($id_proses == 1 || in_array((int)$id_proses, [1, 29, 30, 31, 32]));

          if ((int)$id_perusahaan === 2) {
            // === PERUSAHAAN 2 (CITRA KNITT) - BERBASIS BTM APPROVED P2 ===
            $btmMasukP2 = $this->mRef->getDataBtmP2($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $r->style);
            $trfKeluarP2 = $this->mRef->getDataPengurangP2($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
            $sisa = $btmMasukP2 - $trfKeluarP2;
          } else {
            // === PERUSAHAAN 1 (PT CJK) ===
            if ($isRajutProses) {
              $getSisa = $this->mRef->getDataPengurang($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
              $sisa = $r->qty;
              if (!empty($getSisa)) {
                $sisa -= $getSisa;
              }
            } else {
              $getProsesIdRajut = $this->mRef->getDataRajut($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
              $getSisa = $this->mRef->getDataPengurang($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
              $sisa = $getProsesIdRajut;
              if (!empty($getSisa)) {
                $sisa -= $getSisa;
              }
            }
          }

          if (empty($sisa) || $sisa < 0) {
            $sisa = 0;
          }

          $isi = [];
          $isi = [
            "tipe"             => $r->tipe,
            "tipe_text"        => $r->tipe_text,
            "id_konsumen"      => $r->id_konsumen,
            "kode_sales_order" => $r->kode_transaksi,
            "style"            => $r->style,
            "deskripsi"        => $r->deskripsi,
            "color"            => $r->color,
            "buyer"            => $r->buyer,
            "qty"              => $r->qty,
            "qty_ref"          => $sisa,
            "amount"           => $r->amount,
            "kode_ukuran"      => $r->kode_ukuran,
            "kata_kunci"       => $r->kode_transaksi . " - (" . $r->color . ") " . $r->kode_ukuran,
          ];
          $data[] = $isi;

          $isi_slc = [];
          $isi_slc["id"]    = $r->id_konsumen;
          $isi_slc["idx"]   = $r->color;
          $isi_slc["label"] = $r->kode_transaksi . " - (" . $r->color . ") " . $r->kode_ukuran;
          $isi_slc["value"] = $r->id_konsumen;
          $isi_slc["data"]  = $isi;
          $slc[] = $isi_slc;
        }

        $status = true;
        $msg = "Berhasil pengambilan data !";

      } catch (\Throwable $th) {
        //throw $th;
        print_r($th);exit;
      }


      $build_array["status"] = $status;
      $build_array["msg"] = $msg;
      $build_array["data"] = $data;
      $build_array["slc"] = $slc;
      return $this->response->setJSON($build_array); 
    }

    function getCariProduk(){
      $kata_kunci = $this->request->getPost('kata_kunci');
      $id_proses = $this->request->getPost('id_proses');
      $id_perusahaan = $this->request->getPost('id_perusahaan');
      $id_gudang_asal = $this->request->getPost('id_gudang_asal');
      $id_header = $this->request->getPost('id_header');
      if (!empty($id_header)) {
          $id_header = is_numeric($id_header) ? $id_header : decrypt($id_header);
      } else {
          $id_header = null;
      }

      // Auto-detect id_perusahaan dari gudang asal yang dipilih di form
      if (!empty($id_gudang_asal)) {
          $gudangRow = $this->mGudang->getData($id_gudang_asal);
          if (!empty($gudangRow) && !empty($gudangRow->id_perusahaan)) {
              $id_perusahaan = (int)$gudangRow->id_perusahaan;
          }
      }
      if (empty($id_perusahaan)) {
          $id_perusahaan = session()->get('id_perusahaan') ?: ($this->currentUser->id_perusahaan ?? 1);
      }

      $status = false;
      $msg = "Data barang tidak ditemukan !";
      $data  = [];
      $slc  = [];

      try {

        $kt_exp = explode(";",$kata_kunci);

        $last_item = end($kt_exp);
        /**
         * Penjelasan Regex Baru:
         * ^\d{4} \d{2} \d{2}   : Tanggal (Tahun Bulan Hari)
         * \d{2}-\d{2}-\d{2}    : Jam-Menit-Detik
         * -\d{6}               : Mikrodetik
         * \(\d+\)              : Karakter "(" diikuti satu atau lebih digit lalu ")"
         * $                    : Akhir string
         */
        $pattern = '/^(\d{4} \d{2} \d{2} \d{2}-\d{2}-\d{2}-\d{6})\((\d+)\)$/';

        $hanya_tanggal = null;
        $isi_kurung = null;

        if (preg_match($pattern, trim($last_item), $matches)) {
            $isi_kurung = $matches[2];
            array_pop($kt_exp); // Hapus elemen terakhir jika cocok
        }
        
        $qty = $kt_exp[3]; 
        $params = [];
        if (!empty($kt_exp[0])) {
            $params['kode_transaksi'] = trim($kt_exp[0]);
        }
        if (!empty($kt_exp[1])) {
            $params['key_ukuran'] = trim($kt_exp[1]);
        }
        if (!empty($kt_exp[2])) {
            $rawColor = trim($kt_exp[2]);
            if (strpos($rawColor, '~') !== false) {
                $params['all_color'] = $rawColor;
            } else {
                $params['color'] = $rawColor;
            }
        }
        if (!empty($kt_exp[4])) {
          if($kt_exp[4] != '-'){
            $params['color2'] = trim($kt_exp[4]);
          }
        }
        if (!empty($kt_exp[5])) {
          if($kt_exp[5] != '-'){
            $params['color3'] = trim($kt_exp[5]);
          }
        }
        if (!empty($kt_exp[6])) {
          if($kt_exp[6] != '-'){
            $params['color4'] = trim($kt_exp[6]);
          }
        }
        if (!empty($kt_exp[7])) {
          if($kt_exp[7] != '-'){
            $params['color5'] = trim($kt_exp[7]);
          }
        }
        if (!empty($kt_exp[8])) {
          if($kt_exp[8] != '-'){
            $params['color6'] = trim($kt_exp[8]);
          }
        }
        if (!empty($kt_exp[9])) {
          if($kt_exp[9] != '-'){
            $params['color7'] = trim($kt_exp[9]);
          }
        }
        if (!empty($kt_exp[10])) {
          if($kt_exp[10] != '-'){
            $params['color8'] = trim($kt_exp[10]);
          }
        }

        $results = $this->mRef->getUkuranTrans(null, 0, 999, null, null, $params);
        foreach ($results as $r) {
          $isRajutProses = ($id_proses == 1 || in_array((int)$id_proses, [1, 29, 30, 31, 32]));

          if ((int)$id_perusahaan === 2) {
            // === PERUSAHAAN 2 (CITRA KNITT) - BERBASIS BTM APPROVED P2 ===
            // Sumber Kuota : Total BTM Masuk yang SUDAH DI-APPROVE di P2 (BTMC...)
            // Pengurang    : Total TRFC yang SUDAH DIKELUARKAN dari P2 (TRFC...)
            $btmMasukP2 = $this->mRef->getDataBtmP2($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $r->style);
            $trfKeluarP2 = $this->mRef->getDataPengurangP2($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
            $sisa = $btmMasukP2 - $trfKeluarP2;
          } else {
            // === PERUSAHAAN 1 (PT CJK) ===
            if ($isRajutProses) {
              // --- P1: PROSES RAJUT (AWAL) ---
              // Sumber Kuota : Sales Order (SO)
              // Pengurang    : Total Surat Jalan Transfer P1 (TRF...) yang sudah dibuat
              $getSisa = $this->mRef->getDataPengurang($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
              $sisa = $r->qty;
              if (!empty($getSisa)) {
                $sisa -= $getSisa;
              }
            } else {
              // --- P1: PROSES SELAIN RAJUT (LANJUTAN SEPERTI LINGKING, OBRAS, WASHING, STEAM, DLL.) ---
              // Sumber Kuota : Total BTM Hasil Rajut Masuk Gudang P1 yang Approved
              // Pengurang    : Total Surat Jalan Transfer P1 (TRF...) yang sudah dibuat untuk proses lanjutan
              $getProsesIdRajut = $this->mRef->getDataRajut($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
              $getSisa = $this->mRef->getDataPengurang($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color, $id_header, $r->style);
              $sisa = $getProsesIdRajut;
              if (!empty($getSisa)) {
                $sisa -= $getSisa;
              }
            }
          }

          if (empty($sisa) || $sisa < 0) {
            $sisa = 0;
          }

          $isi = [];
          $isi = [
            "tipe"             => $r->tipe,
            "tipe_text"        => $r->tipe_text,
            "id_konsumen"      => $r->id_konsumen,
            "kode_sales_order" => $r->kode_transaksi,
            "style"            => $r->style,
            "deskripsi"        => $r->deskripsi,
            "color"            => $r->color,
            "buyer"            => $r->buyer,
            "qty"              => $qty,
            "qty_ref"          => $sisa,
            "amount"           => $r->amount,
            "kode_ukuran"      => $r->kode_ukuran,
            "kata_kunci"       => $r->kode_transaksi . " - (" . $r->color . ") " . $r->kode_ukuran,
            "print_type"       => !empty($isi_kurung) ? (int)$isi_kurung : 1,
          ];

          $data[] = $isi;
        }

        $status = true;
        $msg = "Berhasil pengambilan data !";

      } catch (\Throwable $th) {
        //throw $th;
        print_r($th);exit;
      }


      $build_array["status"] = $status;
      $build_array["msg"] = $msg;
      $build_array["data"] = $data;
      return $this->response->setJSON($build_array); 
    }

    public function print_excel_lists($from_date, $to_date){

        $fileName = "Monitoring Item Transfer-List.xlsx";

        $id = $this->request->getGet('data_id');
        $id_perusahaan = $this->request->getGet('id_perusahaan');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mRef->get_export($tanggal_sql_from, $tanggal_sql_to, $id_perusahaan);
        
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $sheets->setActiveSheetIndex(0)->setTitle('Monitoring');

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Rekapitulasi Transfer Barang '. $title)

               ->setCellValue('A4', 'KODE SO/SAMPLE')
               ->setCellValue('B4', 'TGL SO/SAMPLE')
               ->setCellValue('C4', 'TIPE')
               ->setCellValue('D4', 'BUYER')
               ->setCellValue('E4', 'STYLE')
               ->setCellValue('F4', 'PERUSAHAAN')
               ->setCellValue('G4', 'PROSES')
               ->setCellValue('H4', 'CMT')
               ->setCellValue('I4', 'WARNA')
               ->setCellValue('J4', 'UKURAN')
                ->setCellValue('K4', 'QTY SO/SPL')
                ->setCellValue('L4', 'QTY KIRIM')
                ->setCellValue('M4', 'QTY TERIMA')
                ->setCellValue('N4', 'SELISIH')
                ->setCellValue('O4', 'STATUS REJECT');

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
                'alignment' => [
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
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
        $gets->getStyle('A:O')->getAlignment()->setWrapText(true);
        $gets->getStyle('A4:O4')->applyFromArray($styleArray_header);
        $sheets->getActiveSheet()->mergeCells('A2:O2');

        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        $gets->getColumnDimension('A')->setWidth(16);
        $gets->getColumnDimension('B')->setWidth(20);
        $gets->getColumnDimension('C')->setWidth(15);
        $gets->getColumnDimension('D')->setWidth(27);
        $gets->getColumnDimension('E')->setWidth(25);
        $gets->getColumnDimension('F')->setWidth(25);
        $gets->getColumnDimension('G')->setWidth(35);
        $gets->getColumnDimension('H')->setWidth(35);
        $gets->getColumnDimension('I')->setWidth(60);
        $gets->getColumnDimension('J')->setWidth(13);
        $gets->getColumnDimension('K')->setWidth(15);
        $gets->getColumnDimension('L')->setWidth(15);
        $gets->getColumnDimension('M')->setWidth(15);
        $gets->getColumnDimension('N')->setWidth(15);
        $gets->getColumnDimension('O')->setWidth(18);
          
        $gets->getStyle('A4:O4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets->getStyle('A4:O4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L','M','N','O'
        );

        for ($i=0; $i < 15 ; $i++) { 

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
        
        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];
            
            $selisih = $r->qty - $r->qty_terima;
            $tipeStr = ($r->print_type == 2 || $r->print_type == '2') ? 'PERBAIKAN' : 'PRODUKSI';
            $perusahaanStr = !empty($r->perusahaan) ? $r->perusahaan : ((int)($r->id_perusahaan ?? 1) === 2 ? 'Citra Knitt' : 'PT Citra Jaya Kniting');
            $isRejectStr = (!empty($r->is_reject) && $r->is_reject == 1) ? 'REJECT' : '-';

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->kode) ? $r->kode : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, $tipeStr)
                    ->setCellValue('D'.$ix, !empty($r->buyer) ? $r->buyer : "-")
                    ->setCellValue('E'.$ix, !empty($r->style) ? $r->style : "-")
                    ->setCellValue('F'.$ix, $perusahaanStr)
                    ->setCellValue('G'.$ix, !empty($r->proses) ? $r->proses : '-')
                    ->setCellValue('H'.$ix, !empty($r->nama_operator) ? $r->nama_operator : '-')
                    ->setCellValue('I'.$ix, !empty($r->color) ? $r->color : '-')
                    ->setCellValue('J'.$ix, !empty($r->kode_ukuran) ? $r->kode_ukuran : '-')
                    ->setCellValue('K'.$ix, !empty($r->qty_ref) ? $r->qty_ref : 0)
                    ->setCellValue('L'.$ix, !empty($r->qty) ? $r->qty : 0)
                    ->setCellValue('M'.$ix, !empty($r->qty_terima) ? $r->qty_terima : 0)
                    ->setCellValue('N'.$ix, !empty($selisih) ? $selisih : 0)
                    ->setCellValue('O'.$ix, $isRejectStr);

            $gets->getStyle('A'.$ix.':O'.$ix)->applyFromArray($stylexArray);
            $gets->getStyle('C'.$ix)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $gets->getStyle('O'.$ix)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':J'. $length);
        
        $gets->getStyle('A'.$length.':O'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('K' . $length, '=SUM(K' . $startRow . ':K' . ($length-1) . ')');
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('L' . $length, '=SUM(L' . $startRow . ':L' . ($length-1) . ')');
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('M' . $length, '=SUM(M' . $startRow . ':M' . ($length-1) . ')');
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('N' . $length, '=SUM(N' . $startRow . ':N' . ($length-1) . ')');
               
        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); 
        exit;
    }

    public function print_excel_trans($from_date, $to_date){

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
          'alignment' => [
              'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
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
        'font' => [
            'bold' => true, // Tambahkan ini agar otomatis bold
            'name' => 'Arial Narrow',
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
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

      $fileName = "Transaksi Item Transfer.xlsx";

      $id_perusahaan = $this->request->getGet('id_perusahaan');

      $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
      $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

      $results = $this->mRef->get_export_bahan_baku($tanggal_sql_from, $tanggal_sql_to, $id_perusahaan);

      $results2 = $this->mRef->get_export_bahan_jadi($tanggal_sql_from, $tanggal_sql_to, $id_perusahaan);

      $title = formatTanggalIndonesia($tanggal_sql_from)." - ".formatTanggalIndonesia($tanggal_sql_to);
      
      $sheets = new Spreadsheet;

      $sheet1 = $sheets->setActiveSheetIndex(0);
      $sheet1->setTitle('Transfer Item Bahan Jadi');

      $sheet1->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
      $sheet1->setCellValue('A2', 'Transfer Item Bahan Jadi '. $title);

      $sheet1->setCellValue('A4', 'NOMOR TRANSAKSI')
          ->setCellValue('B4', 'TANGGAL')
          ->setCellValue('C4', 'PERUSAHAAN')
          ->setCellValue('D4', 'TIPE')
          ->setCellValue('E4', 'GUDANG FROM')
          ->setCellValue('F4', 'GUDANG TO')
          ->setCellValue('G4', 'PROSES')
          ->setCellValue('H4', 'CMT')
          ->setCellValue('I4', 'SO/SAMPLE NO')
          ->setCellValue('J4', 'STYLE')
          ->setCellValue('K4', 'COLOUR')
          ->setCellValue('L4', 'UKURAN')
          ->setCellValue('M4', 'QTY')
          ->setCellValue('N4', 'KETERANGAN')
          ->setCellValue('O4', 'STATUS REJECT');

      $sheet1->freezePane('D5');
      $sheet1->getStyle('A:O')->getAlignment()->setWrapText(true);
      $sheet1->getStyle('A4:O4')->applyFromArray($styleArray_header);

      $sheet1->mergeCells('A2:O2');
      $sheet1->getStyle('A2')->getAlignment()
          ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      $sheet1->getColumnDimension('A')->setWidth(18);
      $sheet1->getColumnDimension('B')->setWidth(20);
      $sheet1->getColumnDimension('C')->setWidth(25);
      $sheet1->getColumnDimension('D')->setWidth(15);
      $sheet1->getColumnDimension('E')->setWidth(27);
      $sheet1->getColumnDimension('F')->setWidth(27);
      $sheet1->getColumnDimension('G')->setWidth(27);
      $sheet1->getColumnDimension('H')->setWidth(35);
      $sheet1->getColumnDimension('I')->setWidth(17);
      $sheet1->getColumnDimension('J')->setWidth(25);
      $sheet1->getColumnDimension('K')->setWidth(40);
      $sheet1->getColumnDimension('L')->setWidth(15);
      $sheet1->getColumnDimension('M')->setWidth(15);
      $sheet1->getColumnDimension('N')->setWidth(25);
      $sheet1->getColumnDimension('O')->setWidth(18);

      $indexs = array('A','B','C','D', 'E','F','G', 'H','I','J','K','L','M','N','O');
      for ($i=0; $i < 15 ; $i++) { 
          $sheet1->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('C5D9F1');
          $sheet1->getStyle($indexs[$i].'4')->getAlignment()
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          $sheet1->getStyle($indexs[$i].'4')->applyFromArray($styleArray_header);
      }

      $ix = 5;
      for ($xx = 0; $xx < count($results2) ; $xx++) { 
          $r = $results2[$xx];
          $tipeStr = ($r->print_type == 2 || $r->print_type == '2') ? 'PERBAIKAN' : 'PRODUKSI';
          $perusahaanStr = !empty($r->perusahaan) ? $r->perusahaan : ((int)($r->id_perusahaan ?? 1) === 2 ? 'Citra Knitt' : 'PT Citra Jaya Kniting');
          $isRejectStr = (!empty($r->is_reject) && $r->is_reject == 1) ? 'REJECT' : '-';

          $sheet1->setCellValue('A'.$ix, !empty($r->kode_transaksi) ? $r->kode_transaksi : "-")
              ->setCellValue('B'.$ix, !empty($r->tanggal) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-")
              ->setCellValue('C'.$ix, $perusahaanStr)
              ->setCellValue('D'.$ix, $tipeStr)
              ->setCellValue('E'.$ix, !empty($r->gudang_asal) ? $r->gudang_asal : "-")
              ->setCellValue('F'.$ix, !empty($r->gudang_tujuan) ? $r->gudang_tujuan : "-")
              ->setCellValue('G'.$ix, !empty($r->proses) ? $r->proses : '-')
              ->setCellValue('H'.$ix, !empty($r->nama_operator) ? $r->nama_operator : '-')
              ->setCellValue('I'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : '-')
              ->setCellValue('J'.$ix, !empty($r->style) ? $r->style : '-')
              ->setCellValue('K'.$ix, !empty($r->color) ? $r->color : '-')
              ->setCellValue('L'.$ix, !empty($r->kode_ukuran) ? $r->kode_ukuran : '-')
              ->setCellValue('M'.$ix, !empty($r->qty) ? $r->qty : '-')
              ->setCellValue('N'.$ix, !empty($r->keterangan) ? $r->keterangan : '')
              ->setCellValue('O'.$ix, $isRejectStr);

          $sheet1->getStyle('A'.$ix.':O'.$ix)->applyFromArray($stylexArray);
          $sheet1->getStyle('D'.$ix)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          $sheet1->getStyle('O'.$ix)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          $ix++;
      }

      //SHEET 2
      $sheets->createSheet(); 
      $gets = $sheets->setActiveSheetIndex(1);
      $gets->setTitle('Transfer Item Bahan Baku');
      
      $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
      $gets->setCellValue('A2', 'Transfer Item Bahan Baku '. $title);

      $gets->setCellValue('A4', 'NOMOR TRANSAKSI')
          ->setCellValue('B4', 'TANGGAL')
          ->setCellValue('C4', 'PERUSAHAAN')
          ->setCellValue('D4', 'GUDANG FROM')
          ->setCellValue('E4', 'GUDANG TO')
          ->setCellValue('F4', 'PROSES')
          ->setCellValue('G4', 'CMT')
          ->setCellValue('H4', 'ITEM KODE')
          ->setCellValue('I4', 'ITEM DESKRIPSI')
          ->setCellValue('J4', 'QTY')
          ->setCellValue('K4', 'UNIT')
          ->setCellValue('L4', 'LOT')
          ->setCellValue('M4', 'KETERANGAN');

      $gets->freezePane('D5');
      $gets->getStyle('A:M')->getAlignment()->setWrapText(true);
      $gets->getStyle('A4:M4')->applyFromArray($styleArray_header);

      $gets->mergeCells('A2:M2');
      $gets->getStyle('A2')->getAlignment()
          ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      
      $gets->getColumnDimension('A')->setWidth(18);
      $gets->getColumnDimension('B')->setWidth(20);
      $gets->getColumnDimension('C')->setWidth(25);
      $gets->getColumnDimension('D')->setWidth(27);
      $gets->getColumnDimension('E')->setWidth(27);
      $gets->getColumnDimension('F')->setWidth(27);
      $gets->getColumnDimension('G')->setWidth(35);
      $gets->getColumnDimension('H')->setWidth(18);
      $gets->getColumnDimension('I')->setWidth(27);
      $gets->getColumnDimension('J')->setWidth(15);
      $gets->getColumnDimension('K')->setWidth(17);
      $gets->getColumnDimension('L')->setWidth(15);
      $gets->getColumnDimension('M')->setWidth(25);

      // Styling Header Tabel
      $indexs = array('A','B','C','D', 'E','F','G', 'H','I','J','K','L','M');
      for ($i=0; $i < 13 ; $i++) { 
          $gets->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('C5D9F1');
          $gets->getStyle($indexs[$i].'4')->getAlignment()
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          $gets->getStyle($indexs[$i].'4')->applyFromArray($styleArray_header);
      }

      // --- LOOPING DATA ---
      $ix = 5;
      for ($xx = 0; $xx < count($results) ; $xx++) { 
          $r = $results[$xx];
          $perusahaanStr = !empty($r->perusahaan) ? $r->perusahaan : ((int)($r->id_perusahaan ?? 1) === 2 ? 'Citra Knitt' : 'PT Citra Jaya Kniting');

          $gets->setCellValue('A'.$ix, !empty($r->kode_transaksi) ? $r->kode_transaksi : "-")
              ->setCellValue('B'.$ix, !empty($r->tanggal) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-")
              ->setCellValue('C'.$ix, $perusahaanStr)
              ->setCellValue('D'.$ix, !empty($r->gudang_asal) ? $r->gudang_asal : "-")
              ->setCellValue('E'.$ix, !empty($r->gudang_tujuan) ? $r->gudang_tujuan : "-")
              ->setCellValue('F'.$ix, !empty($r->proses) ? $r->proses : '-')
              ->setCellValue('G'.$ix, !empty($r->nama_operator) ? $r->nama_operator : '-')
              ->setCellValue('H'.$ix, !empty($r->kode_barang) ? $r->kode_barang : '-')
              ->setCellValue('I'.$ix, !empty($r->nama_barang) ? $r->nama_barang : '-')
              ->setCellValue('J'.$ix, !empty($r->qty) ? $r->qty : '-')
              ->setCellValue('K'.$ix, !empty($r->nama_satuan) ? $r->nama_satuan : '-')
              ->setCellValue('L'.$ix, !empty($r->lot_no) ? $r->lot_no : '-')
              ->setCellValue('M'.$ix, !empty($r->keterangan) ? $r->keterangan : '');

          $gets->getStyle('A'.$ix.':M'.$ix)->applyFromArray($stylexArray);
          
          $ix++;
      }

      // Set agar ketika file dibuka, sheet pertama yang muncul adalah sheet 'Test Kosong'
      $sheets->setActiveSheetIndex(0);

      $writer = new Xlsx($sheets);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
      header('Cache-Control: max-age=0');
      ob_end_clean();
      $writer->save('php://output'); 
      exit;
  }

  public function panduan()
  {
    $this->data['guide_title'] = "Panduan Operasional Item Transfer";
    $this->data['back_url'] = base_url($this->urlv);
    $this->data['menu_name'] = "Persediaan & Produksi > Item Transfer";
    $this->data['updated_at'] = "29 Agustus 2026";
    return view($this->views . '\vitem_transfer_guide', $this->data);
  }
}


