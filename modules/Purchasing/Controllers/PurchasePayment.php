<?php

namespace Modules\Purchasing\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use CodeIgniter\Pager\PagerRenderer;
use Modules\Purchasing\Models\PaymentModel;
use Modules\Purchasing\Models\PaymentDetailModel;
use Modules\Referensi\Models\RekeningModel;


class PurchasePayment extends BaseController
{

  protected $mRef;
  protected $mRefDet;
  protected $mRekening;
  protected $urlv  = 'purchasing/purchase-payment';
  protected $views = '\Modules\Purchasing\Views';
  function __construct()
  {
    $this->MOD_ALIAS = "MOD_PURCHASE_PAYMENT";
    $this->mRef = new PaymentModel();
    $this->mRefDet = new PaymentDetailModel();
    $this->mRekening   = new RekeningModel();
  }
  public function index()
  {

    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Purchase Payment";

    return view($this->views . '\purchase_payment_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $isReceive      = $this->request->getPost('isReceive');

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
      // if ($this->_edit) {
      $atr_edit['title'] = 'Edit';
      $atr_edit['url'] = $this->urlv . '/form/';
      $atr_edit['class'] = '';
      if ($row->status == 1) {
        $atr_other['title'] = 'Print';
        $atr_other['target'] = "blank";
        $atr_other['url'] = $this->urlv . '/print/';
        $atr_other['class'] = '';
        $atr_other['icon_class'] = 'fa-print';
      }
      // }
      // if ($this->_delete) {
      //   $atr_del['title'] = 'Hapus';
      //   $atr_del['url'] = $this->urlv . '/delete/';
      //   $atr_del['class'] = '';
      //   $atr_del['onclick'] = "return confirm('Hapus Data ?')";
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
          "nama_vendor" => $row->nama_vendor,
          "pay_no" => $row->pay_no,
          "pay_date" => fdate_eng_to_ind($row->pay_date),
          "hutang" => $row->hutang,
          "total_bayar" => $row->total_bayar,
          "sisa_bayar" => $row->sisa_bayar,
          "status" => $status
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function getDataPayment()
  {

    $build_array = [];
    $build_array["code"] = 200;
    $build_array["status"] = false;

    $id_vendor = $this->request->getGet('idVendor');
    if ($id_vendor != null) {
      $id_vendor = decrypt($id_vendor);
    }

    $resData = $this->mRefDet->getDataDetail($id_vendor);
    if (empty($resData)) {
      $resData = $this->mRefDet->getDataPO($id_vendor);
    }
    foreach ($resData as &$rowData) {
      $rowData->do_date = date('d-m-Y', strtotime($rowData->do_date));
      $rowData->po_date = date('d-m-Y', strtotime($rowData->po_date));
    }
    $build_array["message"] = "Data ditemukan";
    $build_array["data"] =  !empty($resData) ? $resData : [];
    $build_array["status"] = true;

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
      $resData->id_vendor = encrypt($resData->id_vendor);
      $payDate = date("d F Y", strtotime($resData->pay_date));

      $resData->pay_date = $payDate;


      $resDataDetail = $this->mRefDet->getDataDetail($id);
      foreach ($resDataDetail as &$rowData) {
        $rowData->do_date = date('d-m-Y', strtotime($rowData->do_date));
        $rowData->po_date = date('d-m-Y', strtotime($rowData->po_date));
      }
      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
    }

    $this->data['titlehead'] = "Form Purchase Payment";
    $this->data['rekening_list'] = $this->mRekening->getData(null, 0, 9999);
    return view($this->views . '\purchase_payment_form', $this->data);
  }

  public function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $id = $this->request->getPost('id');
    $id_vendor = $this->request->getPost('id_vendor');
    $pp_date = $this->request->getPost('pp_date');
    $id_rek = $this->request->getPost('id_rek');
    $totalBayar = $this->request->getPost('totalBayar');
    $hutang = $this->request->getPost('hutang');
    $diskon = $this->request->getPost('diskon');
    // $grandTotal = $this->request->getPost('grandTotal');
    // $sisaBayar = $this->request->getPost('sisaBayar');
    $dataDetail = $this->request->getPost('data');
    if ($id != "") {
      $id = decrypt($id);
    }
    if ($id_vendor != "") {
      $id_vendor = decrypt($id_vendor);
    }

    $dataHeader = [
      "id_vendor" => $id_vendor,
      "pay_date" => $pp_date,
      "id_rek" => $id_rek,
      "total_bayar" => $totalBayar,
      "sisa_bayar" => $diskon == null ? $hutang - $totalBayar - $diskon : $hutang - $totalBayar,
      "hutang" => $hutang,
      "diskon" => $diskon,
      // "grand_total" => $totalBayar - $diskon,
      "status" => 1
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

      $resDataDetail = $this->mRefDet->getDataDetail($id);
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }

    $html = view($this->views . '\purchase_payment_print', $this->data);


    $dompdf->generate($html, 'paymeny.pdf', true);
    exit;
  }
}
