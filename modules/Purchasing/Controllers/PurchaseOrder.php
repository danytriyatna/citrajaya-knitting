<?php

namespace Modules\Purchasing\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use DateTime;
use Modules\Purchasing\Models\PurchaseModel;
use Modules\Purchasing\Models\PurchaseDetailModel;
use App\Libraries\DompdfGenerator;

class PurchaseOrder extends BaseController
{
  protected $views = '\Modules\Purchasing\Views';
  protected $mPO;
  protected $mPODetail;
  protected $urlv  = 'purchasing/purchase-order';

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_PURCHASE_ORDER";
    $this->mPO = new PurchaseModel();
    $this->mPODetail = new PurchaseDetailModel();
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }


    $this->data['titlehead'] = "Purchase Order";

    return view($this->views . '\purchase_order_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $isReceive      = $this->request->getPost('isReceive');

    $params = [];
    $params['isReceive'] = $isReceive;

    $results = $this->mPO->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mPO->getDataCnt($filters, $params);
    $totaldata = $this->mPO->getDataCnt(null, $params);
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
      // if ($this->_edit) {
      $atr_edit['title'] = 'Edit';
      $atr_edit['url'] = $this->urlv . '/form/';
      $atr_edit['class'] = '';
      // }
      if ($row->status != 0) {
        $atr_other['title'] = 'Print';
        $atr_other['target'] = "blank";
        $atr_other['url'] = $this->urlv . '/print/';
        $atr_other['class'] = '';
        $atr_other['icon_class'] = 'fa-print';
      }

      // $atr_del['onclick'] = "return confirm('Hapus Data ?')";

      if ($atr_edit || $atr_other)
        $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);




      // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
      //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";
      $status = "";
      if ($row->status == 0) {
        $status = "<span class='badge bg-secondary'>Menunggu<br>Pembayaran</span>";
      } else if ($row->status == 1) {
        $status = "<span class='badge bg-info'>Dibayar Sebagian</span>";
      } else if ($row->status == 2) {
        $status = "<span class='badge bg-success'>Dibayar Penuh</span>";
      }
      $approve_status = "";
      if ($row->approve_status == 1) {
        $approve_status = "<span class='badge bg-info'>APPROVED</span>";
      } else {
        $approve_status = "<span class='badge bg-warning'>DRAFT</span>";
      } 
      array_push(
        $build_array["data"],
        array(
          "aksi" => $btnAction ? $btnAction : '',
          "id"   => ($id),
          "nama_vendor" => $row->nama_vendor,
          "po_no" => $row->po_no,
          "term" => $row->term,
          "po_date" => fdate_eng_to_ind($row->po_date),
          "date_exc" => $row->date_exc,
          "qty" => $this->formatAngka($row->qty_payment) . "/" . $this->formatAngka($row->qty),
          "total" => $row->total,
          "total_payment" => $row->total_payment,
          "sisa" => $row->total -  $row->total_payment,
          "status" => $status,
          "approve_status" => $approve_status
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  function formatAngka($angka) {
      return rtrim(rtrim(number_format($angka, 2, ".", ""), "0"), ".");
  }

  public function form($id = null)
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }


    $this->data['id'] = $id;
    if ($id != "") {
      $id = decrypt($id);
      $resData = $this->mPO->getData($id);
      $resData->id_vendor = encrypt($resData->id_vendor);
      $poDate = date("d F Y", strtotime($resData->po_date));
      $dateExc = date("d F Y", strtotime($resData->date_exc));
      $resData->po_date = $poDate;
      $resData->date_exc = $dateExc;

      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];

      $resDataDetail = $this->mPODetail->getData(null, 0, 99999, $sort, params: array("id_header" => $id));
      foreach ($resDataDetail as &$rowData) {
        $rowData->id_barang = encrypt($rowData->id_barang);
      }
      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
    }


    $this->data['titlehead'] = "Form Purchase Order";
    $this->data['shipTo'] = null;
    $addNew = $this->mPO->getData(null, null, null, null, null, null, true);
    if (isset($addNew) && empty($id)) {
      $this->data['shipTo'] = $addNew->ship_to;
    }
    $resTerm = $this->mPO->getRefTerm();
    $resTax = $this->mPO->getRefTax();
    $this->data['term'] = $resTerm;
    $this->data['tax'] = $resTax;
    return view($this->views . '\purchase_order_form', $this->data);
  }

  public function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $id = $this->request->getPost('id');
    $id_vendor = $this->request->getPost('id_vendor');
    $po_date = $this->request->getPost('po_date');
    $date_exc = $this->request->getPost('date_exc');
    $id_term = $this->request->getPost('id_term');
    $ship_to = $this->request->getPost('ship_to');
    $total = $this->request->getPost('total');
    $qty = $this->request->getPost('qty');
    $dataDetail = $this->request->getPost('data');
    $buttonType = $this->request->getPost('buttonType');
    $approve_status = 0;
    if (isset($buttonType) && $buttonType == "approve") {
      $approve_status = 1;
    }
    if ($id != "") {
      $id = decrypt($id);
    }
    if ($id_vendor != "") {
      $id_vendor = decrypt($id_vendor);
    }
    $dataHeader = [
      "id_vendor" => $id_vendor,
      "po_date" => $po_date,
      "date_exc" => $date_exc,
      "id_term" => $id_term,
      "ship_to" => $ship_to,
      "qty_payment" => 0,
      "total_payment" => 0,
      "total" => $total,
      "qty" => $qty,
      "status" => 0,
      "approve_status" => $approve_status,
    ];
    if ($id) {
      $dataHeader['updated_at'] = date("Y-m-d H:i:s");
      $dataHeader['updated_by'] = $this->get_userid();
    } else {
      $dataHeader['created_at'] = date("Y-m-d H:i:s");
      $dataHeader['created_by'] = $this->get_userid();
    }
    // print_r($data);exit;
    $res = $this->mPO->trxInsertUpdateRecord($dataHeader, $id, $dataDetail);
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
      $resData = $this->mPO->getData($id);
      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];
      $resDataDetail = $this->mPODetail->getData(null, 0, 99999, $sort, params: array("id_header" => $id));
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }
    $html = view($this->views . '\purchase_order_print', $this->data);


    $dompdf->generate($html, 'po.pdf', true);
    exit;
  }

  public function checkUnitPrice()
  {

    $build_array = [];
    $build_array["code"] = 200;
    $build_array["status"] = false;
    $build_array["unit_price"] = null;

    $id_vendor = $this->request->getGet('id_vendor');
    $id_barang = $this->request->getGet('id_barang');

    $oldUnitPrice = $this->mPODetail->getUnitPrice($id_vendor, $id_barang);
    if (isset($oldUnitPrice)) {
      $build_array["unit_price"] = $oldUnitPrice->price;
      $build_array["status"] = true;
    }
    return $this->response->setJSON($build_array);
  }
}
