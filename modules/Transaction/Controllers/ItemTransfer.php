<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\BarangMasukModel;
use Modules\Transaction\Models\ItemTransferModel;
use Modules\Transaction\Models\BarangMasukDetailModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Transaction\Models\ItemTransferDetailModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Referensi\Models\OperatorModel;

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
  }

  public function index()
  {

    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Item Transfer";

    return view($this->views . '\item_transfer_list', $this->data);
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
          "status" => $status
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

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang_tujuan));
      $resDataDetSO = $this->mRefDet->getDataDetailSO($id);
      $dataSO = [];
      foreach ($resDataDetSO as $rowData) {
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $rowData->id_so;
        $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
        $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($rowData->id_so) : [];
        if (!empty($detail)) {
          for ($i = 0; $i < count($detail); $i++) {
            $drow = $detail[$i];
            $allQty = $this->mSalesOrder->getTotal_qty($drow->id, 2);
            $detail[$i]->qty      = $allQty;
          }
        }
        $rowData->id = encrypt($rowData->id_so);
        $rowData->detail = $detail;
        $rowData->key_ukuran = $dtUkuran;
        $dataSO[] = $rowData;
      }


      // foreach ($resDataDetail as &$rowData) {
      //     $rowData->id_barang = encrypt($rowData->id_barang);
      // }



      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
      $this->data['dataSO'] = json_encode($dataSO);
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

  function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $id = $this->request->getPost('id');
    $tanggal = $this->request->getPost('tanggal');
    $statusData = $this->request->getPost('status');
    $id_gudang_asal = $this->request->getPost('id_gudang_asal');
    $id_gudang_tujuan = $this->request->getPost('id_gudang_tujuan');
    $id_cmt = $this->request->getPost('id_cmt');
    $id_proses = $this->request->getPost('id_proses');
    $keterangan = $this->request->getPost('keterangan');
    $tipe = $this->request->getPost('tipe');
    $dataDetail = $this->request->getPost('data');
    $dataSO = $this->request->getPost('dataSO');
    if ($id != "") {
      $id = decrypt($id);
    }

    $dataHeader = [
      "id_gudang_asal" => !empty($id_gudang_asal) ? $id_gudang_asal : null,
      "id_gudang_tujuan" => !empty($id_gudang_tujuan) ? $id_gudang_tujuan : null,
      "id_cmt" => !empty($id_cmt) ? $id_cmt : null,
      "id_proses" => !empty($id_proses) ? $id_proses : null,
      "tanggal" => $tanggal,
      "status" => $statusData,
      "keterangan" => $keterangan,
      "tipe" => $tipe,
    ];
    if ($id) {
      $dataHeader['updated_at'] = date("Y-m-d H:i:s");
      $dataHeader['updated_by'] = $this->get_userid();
    } else {
      $dataHeader['created_at'] = date("Y-m-d H:i:s");
      $dataHeader['created_by'] = $this->get_userid();
    }
    // print_r($data);exit;
    $res = $this->mRef->trxInsertUpdateRecord($dataHeader, $id, $dataDetail, $dataSO);
    if ($res) {
      $status = true;
      $msg = "Data berhasil disimpan!";
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
    $dompdf = new DompdfGenerator();

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
      $resDataDetSO = $this->mRefDet->getDataDetailSO($id);
      $dataSO = [];
      foreach ($resDataDetSO as $rowData) {
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $rowData->id_so;
        $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
        $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($rowData->id_so) : [];
        if (!empty($detail)) {
          for ($i = 0; $i < count($detail); $i++) {
            $drow = $detail[$i];
            $allQty = $this->mSalesOrder->getTotal_qty($drow->id, 2);
            $detail[$i]->qty      = $allQty;
          }
        }

        $rowData->id = encrypt($rowData->id_so);
        $rowData->detail = $detail;
        $keysUkuran = !empty($detail) ? array_keys(get_object_vars($detail[0])) : [];
        $excludeKeys = ["id", "no", "colordasar", "colour", "total_harga", "qty"];
        $ukuranKeysInc = array_values(array_diff($keysUkuran, $excludeKeys));
        $rowData->ukuran = !empty($ukuranKeysInc) ? $ukuranKeysInc : [];
        $dataSO[] = $rowData;
      }

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang_tujuan));
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['dataSO'] = !empty($dataSO) ? $dataSO : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }
    $html = view($this->views . '\item_transfer_print', $this->data);

    $dompdf->generate($html, 'rec_item.pdf', true);
    exit;
  }
}
