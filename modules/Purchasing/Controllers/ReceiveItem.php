<?php

namespace Modules\Purchasing\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use DateTime;
use Modules\Purchasing\Models\ReceiveItemModel;
use Modules\Purchasing\Models\ReceiveItemDetailModel;
use Modules\Purchasing\Models\PurchaseDetailModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Transaction\Models\IncomingGoodsModel;

class ReceiveItem extends BaseController
{
  protected $views = '\Modules\Purchasing\Views';

  protected $mRef;
  protected $mRefDet;
  protected $mPODetail;
  protected $mGudang;
  protected $mBarangMasuk;
  protected $urlv  = 'purchasing/receive-item';
  function __construct()
  {
    $this->MOD_ALIAS = "MOD_RECEIVE_ITEM";
    $this->mRef = new ReceiveItemModel();
    $this->mRefDet = new ReceiveItemDetailModel();
    $this->mPODetail = new PurchaseDetailModel();
    $this->mGudang = new GudangModel();
    $this->mBarangMasuk = new IncomingGoodsModel();
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Receive Item";

    return view($this->views . '\receive_item_list', $this->data);
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
      $atr_other = null;
      $atr_del = null;
      $btnAction = null;
      // if ($this->_edit) {
      $atr_edit['title'] = 'Edit';
      $atr_edit['url'] = $this->urlv . '/form/';
      $atr_edit['class'] = '';
      // }
      // if ($this->_delete) {
      //   $atr_del['title'] = 'Hapus';
      //   $atr_del['url'] = $this->urlv . '/delete/';
      //   $atr_del['class'] = '';
      //   $atr_del['onclick'] = "return confirm('Hapus Data ?')";
      // }
      if ($row->status != 0) {
        $atr_other['title'] = 'Print';
        $atr_other['target'] = "blank";
        $atr_other['url'] = $this->urlv . '/print/';
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
      $rec_date = date("Y-m-d", strtotime($row->rec_date));
      array_push(
        $build_array["data"],
        array(
          "aksi" => $btnAction ? $btnAction : '',
          "id"   => ($id),
          "nama_vendor" => $row->nama_vendor,
          "po_no" => $row->po_no,
          "rec_no" => $row->rec_no,
          // "date_exc" => fdate_eng_to_ind($row->date_exc),
          "date_exc" => fdate_eng_to_ind($row->date_exc),
          "rec_date" => fdate_eng_to_ind($rec_date),
          "qty" => $row->qty,
          "ship_to" => $row->ship_to,
          "form_no" => $row->form_no,
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
    $idHeader      = $this->request->getPost('idHeader');

    $params = [];
    if ($idHeader != "") {
      $idHeader = decrypt($idHeader);
      $params['id_header'] = $idHeader;
    } else {
      $params['id_header'] = encrypt("nof found");
    }
    $params['isReceive'] = true;
    $results = $this->mPODetail->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mPODetail->getDataCnt($filters, $params);
    $totaldata = $this->mPODetail->getDataCnt(null, $params);
    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $id = encrypt($row->id_barang);

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
          // "id"   => ($id),
          "id"   => $row->id_barang,
          "nama_barang" => $row->nama_barang,
          "kode_barang" => $row->kode_barang,
          "nama_satuan" => $row->nama_unit,
          "qty" => $row->qty - $row->qty_receive,
          "price" => $row->price,
          "qty_receive" => $row->qty_receive,
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function checkLotsNo()
  {

    $build_array = [];
    $build_array["code"] = 200;
    $build_array["status"] = false;

    $noLotNo = $this->request->getGet('lot_no');
    $idBarang = $this->request->getGet('id_barang');


    $resData = $this->mBarangMasuk->getLotNo($noLotNo, null);

    if (!empty($resData)) {
      if ($resData->id_barang != $idBarang) {
        $build_array["message"] = "Lot No. sudah dipakai oleh barang lain";
        $build_array["status"] = true;
      }
    }
    return $this->response->setJSON($build_array);
  }

  public function form($id = null)
  {
    $this->data['id'] = $id;
    if ($id != "") {
      $id = decrypt($id);
      $resData = $this->mRef->getData($id);
      $resData->id_po = encrypt($resData->id_po);

      $recDate = date("d F Y", strtotime($resData->rec_date));
      $dateExc = date("d F Y", strtotime($resData->date_exc));
      $resData->rec_date = $recDate;
      $resData->date_exc = $dateExc;

      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
      // foreach ($resDataDetail as &$rowData) {
      //   $rowData->id_barang = encrypt($rowData->id_barang);
      // }

      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
    }
    $this->data['titlehead'] = "Form Receive Item";
    $sortGudang = [
      [
        'field' => 'nama_gudang',
        'dir' => 'ASC'
      ]
    ];
    $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
    $this->data['gudang']    = $resDataGudang;
    return view($this->views . '\receive_item_form', $this->data);
  }

  public function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $id = $this->request->getPost('id');
    $id_po = $this->request->getPost('id_po');
    $rec_date = $this->request->getPost('rec_date');
    $receive_date = $this->request->getPost('receive_date');
    $statusData = $this->request->getPost('status');
    $form_no = $this->request->getPost('form_no');
    $qty = $this->request->getPost('qty');
    $namaVendor = $this->request->getPost('namaVendor');
    $dataDetail = $this->request->getPost('data');
    if ($id != "") {
      $id = decrypt($id);
    }
    if ($id_po != "") {
      $id_po = decrypt($id_po);
    }
    $dataHeader = [
      "id_po" => $id_po,
      "rec_date" => $receive_date,
      // "receive_date" => $receive_date,
      "status" => $statusData,
      "form_no" => $form_no,
      "qty" => $qty,
      "nama_vendor" => $namaVendor
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

      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }
    $html = view($this->views . '\receive_item_print', $this->data);

    $dompdf->generate($html, 'rec_item.pdf', true);
    exit;
  }
}
