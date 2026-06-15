<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use Modules\Referensi\Models\WarnaModel;
use Modules\Referensi\Models\UkuranModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Transaction\Models\InvoiceModel;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Transaction\Models\WalkorderModel;
use Modules\Transaction\Models\ProductionModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Referensi\Models\ProsesProduksiModel;

class SalesInvoice extends BaseController
{
  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/sales-invoice';

  protected $mProduksi;
  protected $mSample;
  protected $mSalesOrder;
  protected $mWalkorder;
  protected $mPproduksi;
  protected $mDelivery;
  protected $mInvoice;
  protected $mkonsumen;
  protected $mUkuran;
  protected $mWarna;

  function __construct()
  {
    $this->MOD_ALIAS   = "MOD_TRANSAKSI_INVOICE";
    $this->mProduksi   = new ProductionModel();
    $this->mSample     = new SampleModel();
    $this->mSalesOrder = new SalesOrderModel();
    $this->mWalkorder  = new WalkorderModel();
    $this->mPproduksi  = new ProsesProduksiModel();
    $this->mDelivery   = new DeliveryModel();
    $this->mkonsumen   = new KonsumenModel();
    $this->mUkuran     = new UkuranModel();
    $this->mWarna      = new WarnaModel();
    $this->mInvoice    = new InvoiceModel();
  }

  public function index()
  {

    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Sales Invoice";


    return view($this->views . '\sales_invoice_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $params = [];

    $results = $this->mInvoice->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mInvoice->getDataCnt($filters, $params);
    $totaldata = $this->mInvoice->getDataCnt(null, $params);
    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $id = encrypt($row->id);

      $atr_edit  = null;
      $atr_del   = null;
      $atr_other   = null;
      $btnAction = null;
      // if ($this->_edit) {
      if (true) {
        $atr_edit['title'] = 'Edit';
        $atr_edit['url'] = $this->urlv . '/form/';
        $atr_edit['class'] = '';
      }
      // if ($this->_delete) {
      if ($row->status != 1) {
        $atr_del['title'] = 'Hapus';
        $atr_del['url'] = $this->urlv . '/delete/';
        $atr_del['class'] = '';
        $atr_del['onclick'] = "return confirm('Hapus Data ?')";
      }
      // if ($row->status == 2) {
      $atr_other['title'] = 'Print';
      $atr_other['target'] = "blank";
      $atr_other['url'] = $this->urlv . '/print/';
      $atr_other['class'] = '';
      $atr_other['icon_class'] = 'fa-print';
      // }
      // if ($atr_edit || $atr_del)
      $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);

      $status = $row->status  == 1 ? "Approved" : "Draft";
      // $status = "Approved";
      // $tipe   = $row->tipe_id == 1 ? "Sample" : "Sales Order";

      $total       = $row->total;
      $total_down_payment = !empty($row->total_down_payment)? $row->total_down_payment : 0;
      $pengiriman = !empty($row->pengiriman)? $row->pengiriman : 0;
      $pay_item = !empty($row->pay_item)? $row->pay_item : 0;
      $pay_item =  $pay_item < 0 ? 0 - $pay_item : $pay_item;
      $pay_item       = $pay_item+ $total_down_payment;
      // $pay_item       = $pay_item- $pengiriman;
      $diskon      = $row->diskon;
      $pph         = $row->pph;
      $pph_total   = $row->pph_total;
      $grand_total = $row->total;
      $sisa_bayar = $grand_total + $pengiriman - $pay_item;

      array_push(
        $build_array["data"],
        array(
          "id"                => ($id),
          "kode_invoice"      => ($row->kode_invoice),
          "konsumen_nama"     => $row->konsumen_nama,
          "keterangan"        => $row->keterangan,
          "total"             => $total,
          "diskon"            => $diskon,
          "pph"               => $pph,
          "grand_total"       => $grand_total,
          "tgl_transaksi"     => fdate_eng_to_ind($row->tgl_transaksi),
          "bayar"             => $pay_item,
          "pengiriman"        => $pengiriman,
          "sisa_bayar"        => $sisa_bayar,
          "status"            => $status,
          "aksi"              => $btnAction
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
    $this->data['titlehead'] = "Form Sales Invoice";
    if ($id != "") {
      $id = decrypt($id);
      $this->data['titlehead'] = "Form Sales Invoice";
    }

    $stdData = new \stdClass();
    $stdData->kode_invoice = '';
    $stdData->si_no = '';
    $stdData->tgl_si = date('d-m-Y');
    $stdData->tgl_transaksi  = date('d-m-Y');
    $stdData->id_konsumen = '';
    $stdData->keterangan = '';
    $stdData->tgl_do = '';
    $stdData->ttl_inp = '';
    $stdData->pajak_inp = '';
    $stdData->ttl_harga_inp = '';
    $stdData->status = 0;
    $stdData->tgl_jatuh_tempo = date('d-m-Y');
    $stdData->rentang_waktu = 0;

    // variable detail data
    $dt_details = [];
    $dt_prods   = [];

    $status = 0;
    if (!empty($id)) {
      $stdData = $this->mInvoice->getData($id);
      $status  = $stdData->status;


      // $this->data['detail'] = json_encode($list_detail);

      $dt_details = [];
      // get produksi data
      $params['id_invoice'] = $stdData->id;
      $params['id_konsumen'] = $stdData->id_konsumen;
      $dt = $this->mInvoice->get_walkorder_konsumen_ori($params);
      if (!empty($dt)) {
        $message = "Berhasil mengambil data  ";
        $xdata = [];

        $rukuran = $this->mUkuran->getData(0, 0, 999);
        $ukuran = "";
        foreach ($rukuran as $iu) {
          $keyUkuran = $iu->key_ukuran;
          if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
          $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
        }

        foreach ($dt as $x) {

          $uang_dp =  ($x->tipe_id == 1) ? $x->uang_dp : $x->uang_dp;
          $uang_dp +=  ($x->tipe_id == 1) ? $x->uang_dp_2 : $x->uang_dp_2;
          $uang_dp = !empty($uang_dp) ? (float) $uang_dp : 0;

          $total = ($x->tipe_id == 1) ? $x->total_harga_delivery : $x->total_harga_delivery;
          $total = !empty($total) ? (float) $total : 0;

          $isi = [
            'bayar'             => true,
            'id_walkorder'      => $x->id,
            'tgl_transaksi'     => fdate_ind_to_eng($x->tgl_transaksi),
            'keterangan_style'  => $x->keterangan_style,
            'konsumen_nama'     => $x->konsumen_nama,
            'tipe_id'           => $x->tipe_id,
            'ref_id'            => ($x->tipe_id == 1) ? $x->id : $x->id,
            'ref_kode'          => ($x->tipe_id == 1) ? $x->kode : $x->kode,
            'ref_qty'           => ($x->tipe_id == 1) ? $x->qty : $x->qty,
            'ref_dp'            => $uang_dp,
            'ref_total'         => $total,
            'deliver_qty'       => $x->qty_dlv,
            'totals'            => $total - $uang_dp,
          ];

          $do_harga = 0;
          $do_harga = 0;

          $det_list = [];

          $idDetail = [];
          if (!empty($id_walkorder)) {
            $paramx['id_konsumen']  = $stdData->id_konsumen;
            $paramx['id_walkorder'] = $x->id;
            $paramx['ukuran'] = $ukuran;
            $idDetail = $this->mInvoice->getDetail_delivery($paramx);
          }

          $qty_do = 0;
          $total_harga = 0;

          foreach ($idDetail as $d) {

            $qty = 0;
            $ref_kode = "";
            $ref_id = 0;
            $ref_total = 0;
            $ref_dp = 0;

            $qty_do = $qty_do  + $d->qty_do;
            $total_harga = $total_harga  + $d->total_harga;

            $det_isi = [
              'ref_detail_id' => $d->ref_detail_id,
              'id_delivery' => $d->id_delivery,
              'delivery_kode' => $d->delivery_kode,
              'kode_warna' => $d->kode_warna,
              'tipe_id' => $d->tipe_id,
              'qty_do' => !empty($d->qty_do) ? $d->qty_do : 0,
              'total_harga' => !empty($d->total_harga) ? $d->total_harga : 0,
            ];

            foreach ($rukuran as $iu) {
              $keyUkuran = $iu->key_ukuran;

              if ($iu->key_ukuran == 'all') {
                $keyUkuran = 'all_';
                $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
              } else {
                $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
              }
            }

            $det_list[] = $det_isi;
          }
          // $isi['deliver_qty'] = $qty_do;
          // $isi['ref_total'] = $total_harga;
          // $isi['totals'] = $total_harga - $uang_dp;
          $isi['detail_data'] = $det_list;
          $dt_details[] = $isi;
        }
      }

      // $dt_details  = $dataProd;
      // dd($dt_details);

      $dt_details = json_encode($dt_details, true);
      $dt_prods = json_encode($dt_prods, true);
    }
    $this->data['row'] = $stdData;
    if (!empty($_POST)) {
      // dd($_POST);
      if (true) {

        $stdData->si_no = trim($this->request->getPost('si_no'));
        $stdData->tgl_si = trim($this->request->getPost('tgl_si'));
        $stdData->id_konsumen = trim($this->request->getPost('select_buyer'));
        $stdData->ttl_inp = trim($this->request->getPost('ttl_inp'));
        $stdData->pajak_inp = trim($this->request->getPost('pajak_inp'));
        $stdData->ttl_harga_inp  = trim($this->request->getPost('ttl_harga_inp'));
        $stdData->keterangan  = trim($this->request->getPost('keterangan'));
        $stdData->tgl_jatuh_tempo  = trim($this->request->getPost('tgl_jatuh_tempo'));
        $stdData->status  = trim($this->request->getPost('status'));
        // $stdData->rentang_waktu  = trim($this->request->getPost('rentang_waktu'));

        $dt_details = trim($this->request->getPost('dt_details'));

        $dt_details = json_decode($dt_details, true);
        $data['tgl_transaksi'] = \fdate_ind_to_eng($stdData->tgl_si);
        $data['keterangan'] = $stdData->keterangan;
        $data['id_konsumen'] = $stdData->id_konsumen;
        $data['tgl_jatuh_tempo'] = \fdate_ind_to_eng($stdData->tgl_jatuh_tempo);
        $data['rentang_waktu'] = $stdData->rentang_waktu;
        $data['status'] = $stdData->status;

        $data['total'] = $stdData->ttl_inp;
        $data['pph'] = 11;
        $data['pph_total'] = $stdData->pajak_inp;
        $data['grand_total'] = $stdData->ttl_harga_inp;

        if (!empty($id)) {
          $data['updated_at'] = date('Y-m-d H:i:s');
          $data['updated_by'] = $this->get_userid();

          $inUp = $this->update($id, $data, $dt_details);
        } else {
          $data['active'] = 1;
          $data['kode_invoice'] = $this->mSample->generateNo("SI", "trans_invoice", "kode_invoice", "SI");
          $data['created_at'] = date('Y-m-d H:i:s');
          $data['created_by'] = $this->get_userid();

          $inUp = $this->insert($data, $dt_details);
        }

        if ($inUp) {
          $this->session->setFlashdata('message', "Simpan data berhasil..");
          return redirect()->to('/trans/sales-invoice');
        } else {
          // $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
          $this->session->setFlashdata('error', "Ulangi simpan data !");
        }
      } else {
        $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", $this->validation->listErrors());
      }
    }

    $view_read = false;
    // if($status != 1){
    //   $view_read = true;
    // }
    $show_save_btn = true;
    if (!empty($id)) {
      $show_save_btn = false;
      $view_read = true;
    }

    $this->data['view_read']     = $view_read;
    $this->data['buyer']         = $this->mkonsumen->where("active", 1)->findAll();
    $this->data['dt_details']    = $dt_details; // = json_decode($dt_details, true);
    $this->data['dt_prods']      = $dt_prods; // = json_decode($dt_prods, true);
    $this->data['status']        = $status;
    $this->data['show_save_btn'] = $show_save_btn;
    return view($this->views . '\sales_invoice_form', $this->data);
  }

  function insert($dataIn, $detail)
  {
    $tgl = date('Y-m-d H:i:s');
    $userId = $this->get_userid();

    $this->db->transBegin();

    $id = $this->mInvoice->insertRecordGetid($this->mInvoice->table, $dataIn);

    $qty = 0;
    $delivery_id = [];
    if (!empty($detail)) {

      $builderx = $this->mInvoice->table($this->mInvoice->table2);
      $builderx->where("id_invoice", $id);
      $builderx->delete();
      foreach ($detail as $item) {
        if (!empty($item['bayar'])) {
          $hasil = explode(",", $item['list_delivery']);
          foreach ($hasil as $key => $value) {
            if (!in_array($value, $delivery_id)) {
                $delivery_id[] = $value;
            } 
          }
          $ddata = [];
          $ddata['id_invoice'] = $id;
          $ddata['id_ref'] = $item['ref_id'];
          $ddata['kode_ref'] = $item['ref_kode'];
          $ddata['tipe_id'] = $item['tipe_id'];
          // $ddata['qty'] = $item['tipe_id'];
          $ddata['qty_do'] = $item['deliver_qty'];
          $ddata['down_payment'] = $item['ref_dp'];

          $ddata['total'] = $item['totals'];
          $ddata['grand_total'] = $item['totals'];

          $ddata['created_at'] = $tgl;
          $ddata['created_by'] = $userId;
          $id_detail = $this->mInvoice->insertRecordGetid($this->mInvoice->table2, $ddata);
          $ddataDel['invoice_status'] = $userId;

          foreach ($item['detail_data'] as $xdetail) {
            $dddata = [];
            $dddata['id_invoice'] = $id;
            $dddata['id_invoice_detail'] = $id_detail;
            $dddata['id_delivery'] = $xdetail['id_delivery'];
            $dddata['qty'] = $xdetail['qty_do'];
            $dddata['total'] = $xdetail['total_harga'];
            $this->mInvoice->insertRecordGetid($this->mInvoice->table3, $dddata);
          }
        }
      }
      
      if (count($delivery_id) > 0) {
          foreach ($delivery_id as $key => $value) {
            $status['invoice_status'] = true;
            $status['id_invoice'] = $id;
            $this->mDelivery->updateRecord($this->mDelivery->table, $status, "id", $value);
          }
      }
    }


    $this->mcommon->setLog($this->auth->getUserId(), $this->MOD_ALIAS, $id, "Insert Delivery");

    if ($this->db->transStatus() === FALSE) {
      $this->db->transRollback();
      return FALSE;
    } else {
      $this->db->transCommit();
      return TRUE;
    }
  }



  function update($id, $dataIn, $detail)
  {
    $tgl = date('Y-m-d H:i:s');
    $userId = $this->get_userid();

    $this->db->transBegin();
    $this->mInvoice->updateRecord($this->mInvoice->table, $dataIn, 'id', $id);

    $qty = 0;

    if (!empty($detail)) {
      foreach ($detail as $key => $value) {
        $getDetailRow = $this->mInvoice->getDataDetailUpdate($id, $value['ref_id'], $value['tipe_id']);
        $detData = [];
        $detData['down_payment'] = $value['ref_dp'];
        $detData['total'] = $value['ref_total'];
        $detData['grand_total'] = $value['totals'];
        $detData['qty_do'] = $value['ref_qty'];
        $this->mInvoice->updateRecord($this->mInvoice->table2, $detData, 'id', $getDetailRow->id);

        // if ($dataIn['status' == 1]) {
        //   $status['invoice_status'] = true;
        //   $this->mDelivery->updateRecord($this->mDelivery->table, $status, "id_invoice", $id);
        // }
      }

      // $builderx = $this->mInvoice->table($this->mInvoice->table2);
      // $builderx->where("id_invoice", $id);
      // $builderx->delete();
      // foreach ($detail as $item) {
      //   if (!empty($item['bayar'])) {
      //     $hasil = explode(",", $item['list_delivery']);
      //     foreach ($hasil as $key => $value) {
      //       if (!in_array($value, $delivery_id)) {
      //           $delivery_id[] = $value;
      //       } 
      //     }
      //     $ddata = [];
      //     $ddata['id_invoice'] = $id;
      //     $ddata['id_ref'] = $item['ref_id'];
      //     $ddata['kode_ref'] = $item['ref_kode'];
      //     $ddata['tipe_id'] = $item['tipe_id'];
      //     // $ddata['qty'] = $item['tipe_id'];
      //     $ddata['qty_do'] = $item['deliver_qty'];
      //     $ddata['down_payment'] = $item['ref_dp'];

      //     $ddata['total'] = $item['totals'];
      //     $ddata['grand_total'] = $item['totals'];

      //     $ddata['created_at'] = $tgl;
      //     $ddata['created_by'] = $userId;
      //     $id_detail = $this->mInvoice->insertRecordGetid($this->mInvoice->table2, $ddata);
      //     $ddataDel['invoice_status'] = $userId;

      //     foreach ($item['detail_data'] as $xdetail) {
      //       $dddata = [];
      //       $dddata['id_invoice'] = $id;
      //       $dddata['id_invoice_detail'] = $id_detail;
      //       $dddata['id_delivery'] = $xdetail['id_delivery'];
      //       $dddata['qty'] = $xdetail['qty_do'];
      //       $dddata['total'] = $xdetail['total_harga'];
      //       $this->mInvoice->insertRecordGetid($this->mInvoice->table3, $dddata);
      //     }
      //   }
      // }
      
      // if (count($delivery_id) > 0) {
      //     foreach ($delivery_id as $key => $value) {
      //       $status['invoice_status'] = true;
      //       $status['id_invoice'] = $id;
      //       $this->mDelivery->updateRecord($this->mDelivery->table, $status, "id", $value);
      //     }
      // }
    }


    // $this->mcommon->setLog($this->auth->getUserId(), $this->MOD_ALIAS, $id, "Insert Delivery");

    if ($this->db->transStatus() === FALSE) {
      $this->db->transRollback();
      return FALSE;
    } else {
      $this->db->transCommit();
      return TRUE;
    }
  }

  public function delete($id = NULL)
    {
        $this->db->transBegin();

        if ($id != null && $id != "") {
            $id = decrypt($id);
        } else {
            $this->session->setFlashdata('err', "Data tidak ditemukan !");
            return redirect()->to('/trans/sales-invoice');
        }

        
        $this->mInvoice->deleteRecord($this->mInvoice->table2, 'id_invoice', $id);
        $this->mInvoice->deleteRecord($this->mInvoice->table3, 'id_invoice', $id);  
        
        $this->mDelivery->updateRecords($this->mDelivery->table, ['invoice_status' => false, 'id_invoice' => null], ['id_invoice' => $id]);
        $this->mInvoice->deleteRecord($this->mInvoice->table, 'id', $id);
        // $id = (int) $id;

        if ($this->db->transStatus() === FALSE) {
          $this->db->transRollback();
          $this->session->setFlashdata('err', "Data gagal dihapus !");
        } else {
          $this->db->transCommit();
          $this->session->setFlashdata('message', "Data berhasil dihapus !");
        }
        
        return redirect()->to('/trans/sales-invoice');
    }


  // get data produksi
  public function walkorder_user_bck()
  {
    $konsumen_id = $this->request->getPost("konsumen_id");
    $status = false;
    $message = "Gagal mengambil data Konsumen";
    $data = [];

    $params['konsumen_id'] = $konsumen_id;
    // $dt = $this->mInvoice->get_walkorder_konsumen($params);
    $dt = $this->mInvoice->get_walkorder_konsumen_ori($params);
    if (!empty($dt)) {
      $status = true;
      $message = "Berhasil mengambil data  ";
      $xdata = [];

      $rukuran = $this->mUkuran->getData(0, 0, 999);
      $ukuran = "";
      foreach ($rukuran as $iu) {
        $keyUkuran = $iu->key_ukuran;
        if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
        $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
      }

      foreach ($dt as $x) {

        $uang_dp =  ($x->tipe_id == 1) ? $x->dp_sample : $x->dp_so;
        $uang_dp = !empty($uang_dp) ? (float) $uang_dp : 0;

        $total = ($x->tipe_id == 1) ? $x->total_sample : $x->total_so;
        $total = !empty($total) ? (float) $total : 0;

        $isi = [
          'id_walkorder'      => $x->id,
          'tgl_transaksi'     => $x->tgl_transaksi,
          'keterangan_style'  => $x->keterangan_style,
          'konsumen_nama'     => $x->konsumen_nama,
          'tipe_id'           => $x->tipe_id,
          'ref_id'            => ($x->tipe_id == 1) ? $x->sample_id : $x->so_id,
          'ref_kode'          => ($x->tipe_id == 1) ? $x->kode_sample : $x->kode_sales_order,
          'ref_qty'           => ($x->tipe_id == 1) ? $x->qty_sample : $x->qty_so,
          'ref_dp'            => $uang_dp,
          // 'ref_total'         => $total,
          // 'deliver_qty'       => $x->deliver_qty,
          // 'totals'            => $total - $uang_dp ,
        ];

        $do_harga = 0;
        $do_harga = 0;

        $det_list = [];

        $paramx['id_konsumen']  = $konsumen_id;
        $paramx['id_walkorder'] = $x->id;
        $paramx['ukuran'] = $ukuran;
        $paramx['get'] =  1;
        $idDetail = $this->mInvoice->getDetail_delivery($paramx);

        $qty_do = 0;
        $total_harga = 0;

        foreach ($idDetail as $d) {

          $qty = 0;
          $ref_kode = "";
          $ref_id = 0;
          $ref_total = 0;
          $ref_dp = 0;

          $qty_do = $qty_do  + $d->qty_do;
          $total_harga = $total_harga  + $d->total_harga;

          $det_isi = [
            'ref_detail_id' => $d->ref_detail_id,
            'id_delivery' => $d->id_delivery,
            'delivery_kode' => $d->delivery_kode,
            'kode_warna' => $d->kode_warna,
            'tipe_id' => $d->tipe_id,
            'qty_do' => !empty($d->qty_do) ? $d->qty_do : 0,
            'total_harga' => !empty($d->total_harga) ? $d->total_harga : 0,
          ];

          foreach ($rukuran as $iu) {
            $keyUkuran = $iu->key_ukuran;

            if ($iu->key_ukuran == 'all') {
              $keyUkuran = 'all_';
              $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
            } else {
              $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
            }
          }

          $det_list[] = $det_isi;
        }
        $isi['deliver_qty'] = $qty_do;
        $isi['ref_total'] = $total_harga;
        $isi['totals'] = $total_harga - $uang_dp;
        $isi['detail_data'] = $det_list;
        $xdata[] = $isi;
      }

      $data = $xdata;
    }

    $build_array['status']  = $status;
    $build_array['message'] = $message;
    $build_array['data']    = $data;
    return $this->response->setJSON($build_array);
  }

  public function walkorder_user()
  {
    $konsumen_id = $this->request->getPost("konsumen_id");
    $status = false;
    $message = "Konsumen belum mempunyai Order/Sample";
    $data = [];

    $params['id_konsumen'] = $konsumen_id;
    $params['id_walkorder'] = 1;
    // $dt = $this->mInvoice->get_walkorder_konsumen($params);
    $dt = $this->mInvoice->get_walkorder_konsumen_ori_new($params);
    if (!empty($dt)) {
      $status = true;
      $message = "Berhasil mengambil data  ";
      $xdata = [];

      $rukuran = $this->mUkuran->getData(0, 0, 999);
      $ukuran = "";
      foreach ($rukuran as $iu) {
        $keyUkuran = $iu->key_ukuran;
        if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
        $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
      }

      foreach ($dt as $x) {

        $uang_dp =  ($x->tipe_id == 1) ? $x->uang_dp : $x->uang_dp;
        $uang_dp +=  ($x->tipe_id == 1) ? $x->uang_dp_2 : $x->uang_dp_2;
        $uang_dp = !empty($uang_dp) ? (float) $uang_dp : 0;

        $total = ($x->tipe_id == 1) ? $x->total_harga_delivery : $x->total_harga_delivery;
        $total = !empty($total) ? (float) $total : 0;

        $isi = [
          'id_walkorder'      => $x->id_walkorder,
          'tgl_transaksi'     => \fdate_eng_to_ind($x->tgl_transaksi),
          'keterangan_style'  => $x->keterangan_style,
          'list_delivery'     => $x->list_delivery,
          'konsumen_nama'     => $x->konsumen_nama,
          'tipe_id'           => $x->tipe_id,
          'ref_id'            => ($x->tipe_id == 1) ? $x->id : $x->id,
          'ref_kode'          => ($x->tipe_id == 1) ? $x->kode : $x->kode,
          'ref_qty'           => ($x->tipe_id == 1) ? $x->qty : $x->qty,
          'ref_dp'            => $uang_dp,
          'ref_total'         => $total,
          'deliver_qty'       => $x->qty_dlv,
          'totals'            => $total - $uang_dp,
        ];

        $do_harga = 0;
        $do_harga = 0;

        $det_list = [];

        $idDetail = [];
        if (!empty($x->id_walkorder)) {
          $paramx['id_konsumen']  = $konsumen_id;
          $paramx['id_walkorder'] = $x->id;
          $paramx['ukuran'] = $ukuran;
          $paramx['get'] =  1;
          $idDetail = $this->mInvoice->getDetail_delivery($paramx);
        }

        $qty_do = 0;
        $total_harga = 0;

        foreach ($idDetail as $d) {

          $qty = 0;
          $ref_kode = "";
          $ref_id = 0;
          $ref_total = 0;
          $ref_dp = 0;

          $qty_do = $qty_do  + $d->qty_do;
          $total_harga = $total_harga  + $d->total_harga;

          $det_isi = [
            'ref_detail_id' => $d->ref_detail_id,
            'id_delivery' => $d->id_delivery,
            'delivery_kode' => $d->delivery_kode,
            'kode_warna' => $d->kode_warna,
            'tipe_id' => $d->tipe_id,
            'qty_do' => !empty($d->qty_do) ? $d->qty_do : 0,
            'total_harga' => !empty($d->total_harga) ? $d->total_harga : 0,
          ];

          foreach ($rukuran as $iu) {
            $keyUkuran = $iu->key_ukuran;

            if ($iu->key_ukuran == 'all') {
              $keyUkuran = 'all_';
              $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
            } else {
              $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
            }
          }

          $det_list[] = $det_isi;
        }
        //  $isi['deliver_qty'] = $qty_do;
        //  $isi['ref_total'] = $total_harga;
        //  $isi['totals'] = $total_harga - $uang_dp;
        $isi['detail_data'] = $det_list;
        $xdata[] = $isi;
      }

      $data = $xdata;
    }

    $build_array['status']  = $status;
    $build_array['message'] = $message;
    $build_array['data']    = $data;
    return $this->response->setJSON($build_array);
  }


  function getDataProduksi()
  {
    $id_walkorder = $this->request->getPost("id_walkorder");
    $id_produksi  = $this->request->getPost("produkds");

    $status = false;
    $msg    = "Konsumen belum mempunyai data pengiriman !";
    $data   = [];

    try {
      $results = $this->mProduksi->getData($id_produksi);

      if (!empty($results)) {
        $data['produksi']         = $results;


        $prm['id_walkorder'] = $id_walkorder;
        $rukuran = $this->mUkuran->getData(0, 0, 999);
        $ukuran = "";
        foreach ($rukuran as $iu) {
          $keyUkuran = $iu->key_ukuran;
          if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
          $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
        }
        $prm['ukuran'] = $ukuran;

        $dataProd = [];
        $rsProd = $this->mProduksi->getProduksilast($prm);

        if (!empty($rsProd)) {
          foreach ($rsProd as $item) {
            $isi = array(
              "ref_detail_id" => ($item->ref_detail_id),
              "id_walkorder"  => ($item->id_walkorder),
              "colordasar"    => ($item->kode_warna)
            );

            $qty = 0;
            foreach ($rukuran as $iu) {
              $keyUkuran = $iu->key_ukuran;
              $indx      = $iu->key_ukuran;
              if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';

              $isi[$indx] = $item->$keyUkuran;

              $qty = $qty +  $item->$keyUkuran;
            }
            $isi['qty'] = $qty;
            $isi['qty_prod'] = 0;
            $isi['qty_remain'] = $qty;
            array_push(
              $dataProd,
              $isi
            );
          }
        }

        $data['detail_produksi']  = $dataProd;

        $status = true;
        $msg    = "Berhasil mengambil data produksi !";
      }
    } catch (\Throwable $th) {
      //throw $th;
      $msg    = "Gagal mengambil data produksi !";
    }

    $build_array['status']  = $status;
    $build_array['message'] = $msg;
    $build_array['data']    = $data;

    return $this->response->setJSON($build_array);
  }

  public function getDataProduksiItem()
  {
    $barcode_code = $this->request->getPost("kata_kunci");
    $id_walkorder = $this->request->getPost("id_walkorder");
    $id_produksi  = $this->request->getPost("id_produksi");

    $status = false;
    $msg = "Data warna ukuran tidak ditemukan !";
    $data = [];
    $slc  = [];

    // if(!empty($barcode_code)){
    $params['last_proses']  = 1;
    $params['id_walkorder'] = $id_walkorder;
    $params['kata_kunci'] = $barcode_code;
    $result = $this->mWalkorder->getListProduksiUkuran($params);
    if (!empty($result)) {
      foreach ($result as $r) {
        $isi = [];
        $isi["id_walkorder"]  = $r->id_walkorder;
        $isi["ref_detail_id"] = $r->ref_detail_id;
        $isi["id_ukuran"]     = $r->id_ukuran;
        $isi["id_warna"]      = $r->id_warna;
        $isi["qty"]           = $r->qty_prod;
        $isi["qty_prod"]      = 0;
        $isi["kata_kunci"]    =  "(" . $r->kode_warna . ") " . $r->kode_ukuran;
        $isi["kode_warna"]    = $r->kode_warna;
        $isi["kode_ukuran"]   = $r->kode_ukuran;
        $isi["key_ukuran"]    = $r->key_ukuran;
        $data[] = $isi;


        $isi_slc = [];
        $isi_slc["id"]    = $r->ref_detail_id;
        $isi_slc["idx"]   = $r->kode_warna;
        $isi_slc["label"] = "(" . $r->kode_warna . ") " . $r->kode_ukuran;
        $isi_slc["value"] = $r->ref_detail_id;
        $isi_slc["data"]  = $isi;
        $slc[] = $isi_slc;
      }
      $status = true;
      $msg = "Data produk ditemukan !";
    }

    // }

    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = $data;
    $build_array["slc"] = $slc;
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
      $resData = $this->mInvoice->getData($id);
      $status = $resData->status;

      $dt_details = [];
      // get produksi data
      $params['id_invoice'] = $resData->id;
      $params['id_konsumen'] = $resData->id_konsumen;
      $dtails = $this->mInvoice->getDataDetailNew(null, null, null, null. null, null, $params);
      $ukuranSize = array_column($this->mUkuran->getData(null, null, 99999), 'key_ukuran');
      $dtails_so = [];
      $kode_ref = [];
      $deskripsi = [];
      $ref_po = [];
      $warna = [];
      $ukuranAll = [];
      $total_dp = 0;
      $total_dp_2 = 0;
      $total_pengiriman = 0;
      $sub_total = 0;
      if (!empty($dtails)) {
        foreach ($dtails as $key_det => $value_det) {
          if (!empty($value_det->uang_dp) && $value_det->uang_dp > 0) {
            $total_dp += (float)$value_det->uang_dp;
          }
          if (!empty($value_det->uang_dp_2) && $value_det->uang_dp_2 > 0) {
            $total_dp_2 += (float)$value_det->uang_dp_2;
          }
          if (!empty($value_det->pengiriman) && $value_det->pengiriman > 0) {
            $total_pengiriman += (float)$value_det->pengiriman;
          }
          if ($value_det->tipe_id == 1) {
            $dtails_so = $this->mSample->getDataDetailSample_crostab_si($value_det->id_ref, $id);
          }
          else {
            $dtails_so = $this->mSalesOrder->getDataDetailSalesOrder_crostab_si($value_det->id_ref, $id);
            // dd($value_det->id_ref, $id);
          }
          $warna_det = [];
          if (!empty($dtails_so)) {
             $kode_ref[] = $value_det->kode_ref;
             $deskripsi[] = $value_det->style."/".$value_det->deskripsi;
             $ref_po[] = !empty($value_det->ref_po) ? $value_det->ref_po : null;
            //  dd($dtails_so);
             foreach ($dtails_so as $keyso => $valueso) {
                $rowsToDisplay = [];
                $qty_total = 0;
                foreach ($ukuranSize as $ind => $size) {
                  $hargaSatuan = 'harga_satuan_'.$size;
                  $sizeOld = $size;
                    if ($size == "all") {
                        $size = "all_";
                    }
                    if (!empty($valueso->$size)) {
                        $qty = (int)$valueso->$size;
                        if ($size == "all_") {
                            $size = "all";
                            
                        }
                        else if ($size == "sm") {
                            $size = "s/m";
                            
                        }
                        else if ($size == "ml") {
                            $size = "m/l";
                            
                        }
                        else if ($size == "lxl") {
                            $size = "l/xl";
                        }
                        else if ($size == "xxl") {
                            $size = "2xl";
                        }
                        else if ($size == "xxxl") {
                            $size = "3xl";
                        }
                        else if ($size == "xxxxl") {
                            $size = "4xl";
                            
                        }
                        else if ($size == "xxxxxl") {
                            $size = "5xl";
                            
                        }
                        else if ($size == "xxxxxxl") {
                            $size = "6xl";
                            
                        }
                        $qty_total += $qty;
                        $rowsToDisplay[] = ['size' => strtoupper($size), 'qty' => $qty, 'harga_satuan' => $valueso->$hargaSatuan];
                        if (!in_array(strtoupper($size), $ukuranAll)) {
                          // Jika belum ada, tambahkan ke array
                              $ukuranAll[] = strtoupper($size);
                          }
                    }
                }

                $groupedByPrice = [];
                foreach ($rowsToDisplay as $item) {
                    $harga = $item['harga_satuan'];
                    $groupedByPrice[$harga][] = $item;
                }

                // 2. Lakukan looping untuk setiap kelompok harga yang ditemukan
                foreach ($groupedByPrice as $harga => $daftarUkuran) {
                    
                    // Hitung qty_total khusus untuk kelompok harga ini saja
                    $qty_total_per_harga = array_sum(array_column($daftarUkuran, 'qty'));

                    // Masukkan ke dalam array warna_det sebagai baris terpisah
                    // $warna_det[] = [
                    //     "warna"      => $valueso->colour,
                    //     "keterangan" => $valueso->keterangan,
                    //     "ukuran"     => $daftarUkuran, // Isinya hanya ukuran yang harganya sama
                    //     "qty_total"  => $qty_total_per_harga,
                    //     "harga_ref"  => $harga // Opsional: untuk penanda harga di baris ini
                    // ];
                    $sub_total += $valueso->total_harga;
                    $warna_det[] = [
                      "warna" => $valueso->colour,
                      "keterangan" => $valueso->keterangan,
                      "ukuran" => $daftarUkuran,
                      "qty_total" => $qty_total_per_harga,
                      "harga_satuan" => $harga,
                      "total_harga" => $harga * $qty_total_per_harga,
                    ];
                }
            }
            usort($warna_det, function($a, $b) {
                // Ambil harga dari item pertama di dalam array ukuran masing-masing baris
                $hargaA = (int)$a['ukuran'][0]['harga_satuan'];
                $hargaB = (int)$b['ukuran'][0]['harga_satuan'];

                // Urutkan dari harga terkecil ke terbesar
                return $hargaA <=> $hargaB;
            });

            $final_warna_det = [];
            $lastPrice = null;

            foreach ($warna_det as $item) {
                $currentPrice = (int)$item['ukuran'][0]['harga_satuan'];

                // Jika harga berubah (dan bukan baris pertama), tambahkan baris kosong
                if ($lastPrice !== null && $currentPrice !== $lastPrice) {
                    
                    // Buat baris kosong dengan field yang sama tapi isinya NULL
                    $emptyRow = [];
                    foreach ($item as $key => $val) {
                        $emptyRow[$key] = null;
                    }
                    
                    // Flag tambahan jika nanti di view ingin diberi styling khusus (opsional)
                    $emptyRow['is_separator'] = true; 

                    $final_warna_det[] = $emptyRow;
                }

                $final_warna_det[] = $item;
                $lastPrice = $currentPrice;
            }
            $warna[$value_det->kode_ref] = $final_warna_det;
          }
        }
      }
      $dt = $this->mInvoice->get_walkorder_konsumen_ori($params);
      if (!empty($dt)) {

        $rukuran = $this->mUkuran->getData(0, 0, 999);
        $ukuran = "";
        foreach ($rukuran as $iu) {
          $keyUkuran = $iu->key_ukuran;
          if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
          $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
        }

        foreach ($dt as $x) {

          $uang_dp =  ($x->tipe_id == 1) ? $x->uang_dp : $x->uang_dp;
          $uang_dp +=  ($x->tipe_id == 1) ? $x->uang_dp_2 : $x->uang_dp_2;
          $uang_dp = !empty($uang_dp) ? (float) $uang_dp : 0;

          $total = ($x->tipe_id == 1) ? $x->total_harga : $x->total_harga;
          $total = !empty($total) ? (float) $total : 0;

          $isi = [
            'bayar'             => true,
            'id_walkorder'      => $x->id,
            'tgl_transaksi'     => fdate_ind_to_eng($x->tgl_transaksi),
            'keterangan_style'  => $x->keterangan_style,
            'konsumen_nama'     => $x->konsumen_nama,
            'tipe_id'           => $x->tipe_id,
            'ref_id'            => ($x->tipe_id == 1) ? $x->id : $x->id,
            'ref_kode'          => ($x->tipe_id == 1) ? $x->kode : $x->kode,
            'ref_qty'           => ($x->tipe_id == 1) ? $x->qty : $x->qty,
            'ref_dp'            => $uang_dp,
            'ref_total'         => $total,
            'deliver_qty'       => $x->qty_dlv,
            'totals'            => $total - $uang_dp,
          ];

          $do_harga = 0;
          $do_harga = 0;

          $det_list = [];

          $idDetail = [];
          if (!empty($id_walkorder)) {
            $paramx['id_konsumen']  = $resData->id_konsumen;
            $paramx['id_walkorder'] = $x->id;
            $paramx['ukuran'] = $ukuran;
            $idDetail = $this->mInvoice->getDetail_delivery($paramx);
          }

          $qty_do = 0;
          $total_harga = 0;

          foreach ($idDetail as $d) {

            $qty = 0;
            $ref_kode = "";
            $ref_id = 0;
            $ref_total = 0;
            $ref_dp = 0;

            $qty_do = $qty_do  + $d->qty_do;
            $total_harga = $total_harga  + $d->total_harga;

            $det_isi = [
              'ref_detail_id' => $d->ref_detail_id,
              'id_delivery' => $d->id_delivery,
              'delivery_kode' => $d->delivery_kode,
              'kode_warna' => $d->kode_warna,
              'tipe_id' => $d->tipe_id,
              'qty_do' => !empty($d->qty_do) ? $d->qty_do : 0,
              'total_harga' => !empty($d->total_harga) ? $d->total_harga : 0,
            ];

            foreach ($rukuran as $iu) {
              $keyUkuran = $iu->key_ukuran;

              if ($iu->key_ukuran == 'all') {
                $keyUkuran = 'all_';
                $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
              } else {
                $det_isi[$keyUkuran] = !empty($d->$keyUkuran) ? $d->$keyUkuran : 0;
              }
            }

            $det_list[] = $det_isi;
          }
          // $isi['deliver_qty'] = $qty_do;
          // $isi['ref_total'] = $total_harga;
          // $isi['totals'] = $total_harga - $uang_dp;
          $isi['detail_data'] = $det_list;
          $dt_details[] = $isi;
        }
      }
      $params['kode_ukuran'] = $ukuranAll;
      $params['kode_ukuran'] = array_map(function($val) {
          return strtoupper(trim($val));
      }, $params['kode_ukuran']);
      $getSeqUkuran = $this->mUkuran->getDataUkuranByKey(null, null, null, null, null, $params);
      $kodeUkuranSaja = array_column($getSeqUkuran, 'kode_ukuran');
      $kodeUkuranSaja = array_map(function($val) {
          return strtoupper(trim($val));
      }, $kodeUkuranSaja);
      $this->data['data'] = !empty($resData) ? $resData : [];
      // $this->data['detail'] = !empty($dt_details) ? $dt_details : [];
      $this->data['detail'] = !empty($dtails) ? $dtails : [];
      $this->data['detail_so'] = !empty($dtails_so) ? $dtails_so : [];
      $this->data['ukuran'] = !empty($kodeUkuranSaja) ? $kodeUkuranSaja : [];
      $this->data['kode_ref'] = !empty($kode_ref) ? $kode_ref : [];
      $this->data['deskripsi'] = !empty($deskripsi) ? $deskripsi : [];
      $this->data['ref_po'] = !empty($ref_po) ? $ref_po : [];
      $this->data['data_detail'] = !empty($warna) ? $warna : [];
      $this->data['total_dp'] = !empty($total_dp) ? $total_dp : 0;
      $this->data['total_dp_2'] = !empty($total_dp_2) ? $total_dp_2 : 0;
      $this->data['total_pengiriman'] = !empty($total_pengiriman) ? $total_pengiriman : 0;
      $this->data['sub_total'] = !empty($sub_total) ? $sub_total : 0;
    }
    $html = view($this->views . '\sales_invoice_print_new', $this->data);


    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
    exit;
  }

  public function print_excel_lists($from_date, $to_date){

        $fileName = "SI-List.xlsx";

        $id = $this->request->getGet('data_id');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mInvoice->get_export($tanggal_sql_from, $tanggal_sql_to);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Sales Invoice '. $title)

               ->setCellValue('A4', 'NO INVOICE')
               ->setCellValue('B4', 'TANGGAL INVOICE')
               ->setCellValue('C4', 'BUYER')
               ->setCellValue('D4', 'NILAI INVOICE')
               ->setCellValue('E4', 'PEMBAYARAN')
               ->setCellValue('F4', 'SISA PEMBAYARAN');

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
        $gets->getStyle('A4:F4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:F3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:F2');
        $sheets->getActiveSheet()->mergeCells('A2:F2');
        // $sheets->getActiveSheet()->mergeCells('A4:F4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
        $gets->getColumnDimension('A')->setWidth(20);
        $gets->getColumnDimension('B')->setWidth(20);
        $gets->getColumnDimension('C')->setWidth(40);
        $gets->getColumnDimension('D')->setWidth(35);
        $gets->getColumnDimension('E')->setWidth(35);
        $gets->getColumnDimension('F')->setWidth(35);

        $gets->getStyle('A4:F4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets->getStyle('A4:F4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F'
        );

        for ($i=0; $i < 6 ; $i++) { 

                $sheets->getActiveSheet()->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('C5D9F1');
                $sheets->getActiveSheet()->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getEndColor()->setARGB('C5D9F1');
            
            // $sheets->getActiveSheet()->mergeCells($indexs[$i].'2');

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

        $total_grand_total = 0;
        $total_pay_item = 0;
        $total_sisa = 0;
        
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $total_down_payment = !empty($r->total_down_payment)? $r->total_down_payment : 0;
            $pay_item = !empty($r->pay_item)? $r->pay_item : 0;
            $pay_item =  $pay_item < 0 ? 0 - $pay_item : $pay_item;
            $pay_item       = $pay_item+ $total_down_payment;
            $grand_total = $r->total;
            $sisa_bayar = $grand_total - $pay_item;

            $total_grand_total += $grand_total; 
            $total_pay_item += $pay_item; 
            $total_sisa += $sisa_bayar;

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->kode_invoice) ? $r->kode_invoice : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->konsumen_nama) ? $r->konsumen_nama : '-')
                    ->setCellValue('D'.$ix, $grand_total)
                    ->setCellValue('E'.$ix, $pay_item)
                    ->setCellValue('F'.$ix, $sisa_bayar);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':F'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("D" . $ix .":F" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':C'. $length);
        
        $gets->getStyle('A'.$length.':F'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('D'.$length, $total_grand_total);

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('E'.$length, $total_pay_item);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('F'.$length, $total_sisa);

        $gets->getStyle('D'. $length .':F'. $length)->getNumberFormat()
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
}
