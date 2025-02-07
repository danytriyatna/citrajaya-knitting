<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use Modules\Transaction\Models\ProductionModel;
use Modules\Transaction\Models\WalkorderModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Referensi\Models\KonsumenModel;

class Production extends BaseController
{
  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/production';
  protected $mProduksi;
  protected $mSample;
  protected $mSalesOrder;
  protected $mWalkorder;
  protected $mPproduksi;
  protected $mOperator;
  protected $mdelivery;
  protected $mkonsumen;

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_TRANSAKSI_PRODUKSI";
    $this->mProduksi = new ProductionModel();
    $this->mSample = new SampleModel();
    $this->mSalesOrder = new SalesOrderModel();
    $this->mWalkorder = new WalkorderModel();
    $this->mPproduksi = new ProsesProduksiModel();
    $this->mOperator = new OperatorModel();
    $this->mdelivery = new DeliveryModel();
    $this->mkonsumen = new KonsumenModel();
    
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Production";

    return view($this->views . '\production_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $params = [];

    $results = $this->mProduksi->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mProduksi->getDataCnt($filters, $params);
    $totaldata = $this->mProduksi->getDataCnt(null, $params);
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
      // if ($this->_edit) {
      if (true) {
        $atr_edit['title'] = 'Edit';
        $atr_edit['url'] = $this->urlv . '/form/';
        $atr_edit['class'] = '';
      }
      // if ($this->_delete) {
      if (false) {
        $atr_del['title'] = 'Hapus';
        $atr_del['url'] = $this->urlv . '/delete/';
        $atr_del['class'] = '';
        $atr_del['onclick'] = "return confirm('Hapus Data ?')";
      }
      // if ($atr_edit || $atr_del)
      $btnAction = btn_action_group($id, $atr_edit, $atr_del);

      // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
      //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

      $status = $row->status == 1 ? "Draft" : "Approved";
      $tipe = $row->tipe_id == 1 ? "Sample" : "Sales Order";

      $qty = $row->qty;
      $qty_prod = $this->mWalkorder->getCnt_produksi($row->id_walkorder);

      array_push(
        $build_array["data"],
        array(
          "id"                => ($id),
          "ref_kode"          => ($row->kode_walkorder),
          "konsumen_nama"     => $row->konsumen_nama,
          "kode_prod"         => $row->kode_prod,
          "qty"               => $row->qty,
          "tipe"              => $tipe,
          "qty_prod"          => $qty_prod,
          "qty_remain"        => $qty - $qty_prod,
          "tgl_deadline"      => fdate_eng_to_ind($row->tgl_deadline),
          "tgl_transaksi"     => fdate_eng_to_ind($row->tgl_transaksi),
          "keterangan_style"  => $row->keterangan_style,
          "status"            => $status,
          "aksi"              => $btnAction
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function lists_ukuran()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $tipeId      = $this->request->getPost('tipe_id');
    $refId      = $this->request->getPost('ref_id');
    $id_proses      = $this->request->getPost('id_proses');
    if ($refId != "") {
      $refId = decrypt($refId);
    }
    if ($tipeId != "") {
      $tipeId = decrypt($tipeId);
    }


    $params = [];
    if ($tipeId == 1) {
      $params = [];
      $params['id_sample'] = $refId;
      $results = $this->mSample->getDataUkuran(null, $start, $limit, $order, $filters, $params, $id_proses);
      $totalfiltered = $this->mSample->getDataUkuranCnt($filters, $params);
      $totaldata = $this->mSample->getDataUkuranCnt(null, $params);
    } else {
      $params = [];
      $params['id_sales_order'] = $refId;
      $results = $this->mSalesOrder->getDataUkuran(null, $start, $limit, $order, $filters, $params, $id_proses);
      $totalfiltered = $this->mSalesOrder->getDataUkuranCnt($filters, $params, $id_proses);
      $totaldata = $this->mSalesOrder->getDataUkuranCnt(null, $params, $id_proses);
    }


    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $id = encrypt($row->id);

      // get harga tarif proses 
      $prm['id_konsumen'] = $row->id_konsumen;
      $prm['style'] = $row->keterangan_style;
      $prm['id_proses'] = $id_proses;
      $get_harga = $this->mkonsumen->getDataHarga(null, 0, 9999, null, null, $prm);
      $harga_proses = 0;
      if(!empty($get_harga)){
        $harga_proses = $get_harga[0]->harga_borongan;
      }
      array_push(
        $build_array["data"],
        array(
          "id"                  => ($id),
          "kode_warna"          => ($row->kode_warna),
          "kode_ukuran"         => $row->kode_ukuran,
          "id_ukuran"           => $row->id_ukuran,
          "id_walkorder_proses" => $row->id_walkorder_proses,
          "id_warna"            => $row->id_warna,
          "qty"                 => $row->qty,
          "harga_satuan"        => $row->harga_satuan,
          "harga_proses"        => $row->harga_proses,
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

    
    $this->data['dnow'] = formatTanggalIndonesia(date('Y-m-d'));
    $this->data['id'] = $id;
    if ($id != "") {
      $id = decrypt($id);
    }

    if (!empty($id)) {
      $data_detail = [];
      $resData = $this->mProduksi->getData($id);
      $stdData = $this->mWalkorder->getData($resData->id_walkorder);
      if ($stdData->tipe_id == 1) {
        // $list_detail = $this->mSample->getDataDetailSample($stdData->ref_id);
        $list_detail = $this->mSample->getDataDetailSample_crostab($stdData->ref_id);
        $resData->file_gambar = !empty($resData->file_name) ? base_url() . "uploads/sample/"  . $resData->file_name : "";

        $pru['use'] = 1;// ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $stdData->ref_id;
        $dtUkuran = $this->mSample->getUkuranTrans($pru);
      } else {
        // $list_detail = $this->mSalesOrder->getDataDetailSalesOrder($stdData->ref_id);
        $list_detail = $this->mSalesOrder->getDataDetailSalesOrder_crostab($stdData->ref_id);
        $resData->file_gambar = !empty($resData->file_name) ? base_url() . "uploads/sales_order/"  . $resData->file_name : "";

        $pru['use'] = 1;// ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $stdData->ref_id;
        $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
      }

      if (!empty($list_detail)) {
        for ($i = 0; $i < count($list_detail); $i++) {
          $drow = $list_detail[$i];
          if ($stdData->tipe_id == 1) {
            $allQty = $this->mSample->getTotal_qty($drow->id, 2);
          } else {
            $allQty = $this->mSalesOrder->getTotal_qty($drow->id, 2);
          }
          $list_detail[$i]->qty      = $allQty;
          $list_detail[$i]->qty_prod = 0;
        }
      }

      $prms['id_walkorder'] = $resData->id_walkorder;
      $dataProses = $this->mProduksi->getDataProsesProd($prms);
      // dd($resData->id_walkorder);
      // get last qty ( untuk mengambil data yang suddah dikirim )
      $parms['last_proses'] = 1;
      $parms['id_walkorder'] = $resData->id_walkorder;
      $dataLast = $this->mProduksi->getDataProsesProd($parms);
      $this->data['last_data'] = !empty($dataLast) ? $dataLast[0] : [];

      $qty_kirim = 0;
      if(!empty($id)){
        $param_dlv['id_produksi'] = $id;
        $data_pengirimasn = $this->mdelivery->getData(null, 0, 9999, null, null, $param_dlv);

        if(!empty($data_pengirimasn)){
            foreach ($data_pengirimasn as $rd) {
              $qty_kirim += $rd->qty_delv;
            }
        }
      }
      
      $sort = [
        [
          'field' => 'nama_operator',
          'dir' => 'ASC'
        ]
      ];
 
      $dataOperator = $this->mOperator->getData(null, 0, 99999, $sort);

      $this->data['qty_kirim'] = $qty_kirim;
      $this->data['proses']    = $dataProses;
      $this->data['operator']    = $dataOperator;
      // $this->data['listProd']    = json_encode($detailProd);
      $this->data['row']    = $resData;
      $this->data['id_produksi'] = encrypt($resData->id);
      $this->data['id_walkorder'] = encrypt($resData->id_walkorder);
      $this->data['tipe_id'] = encrypt($stdData->tipe_id);
      $this->data['ref_id'] = encrypt($stdData->ref_id);
      $this->data['detail'] = json_encode($list_detail);
      $this->data['dtUkuran'] = json_encode($dtUkuran);

      $status = $stdData->status;
    }

    $this->data['titlehead'] = "Form Production";

    return view($this->views . '\production_form', $this->data);
  }

  public function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $idProduksi = $this->request->getPost('idProduksi');
    $idWorkOrder = $this->request->getPost('idWorkOrder');
    if ($idProduksi != "") {
      $idProduksi = decrypt($idProduksi);
    }
    if ($idWorkOrder != "") {
      $idWorkOrder = decrypt($idWorkOrder);
    }
    $data = $this->request->getPost('data');  
    // print_r($data);exit;
    $res = $this->mProduksi->trxInsertUpdateRecord($data, $idProduksi, $idWorkOrder);
    if ($res) {
      $status = true;
      $msg = "Data berhasil disimpan!";
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  public function getDataListProd()
  {
    $build_array['message'] = "data tidak ditemukan";
    $build_array['status']  = false;
    $id = $this->request->getPost('id');

    $id_proses = $this->request->getPost('proses');

    if ($id != "") {
      $id = decrypt($id);
    }
    $tglTransaksi = $this->request->getPost('tglTransaksi');
    
    $tglTransaksi = !empty($tglTransaksi) ? \fdate_ind_to_eng($tglTransaksi) : '';
    $detailProd = $this->mProduksi->getDataOperatorProd($id, $tglTransaksi, $id_proses);
    if (!empty($detailProd)) {
      $build_array['message'] = "data ditemukan";
      $build_array['status']  = true;
      for ($i=0; $i < count($detailProd) ; $i++) { 
        $detailProd[$i]->date = \fdate_eng_to_ind($detailProd[$i]->date);
      }
      $build_array['data'] = json_encode($detailProd);
    }

    return $this->response->setJSON($build_array);
  }

  function getDataProduksiUkuran(){
    $id_walkorder = $this->request->getPost('walkorders');
    $id_proses = $this->request->getPost('proses');

    $id_walkorder = \decrypt($id_walkorder);
    $params['id_walkorder'] = $id_walkorder;
    $params['id_proses'] = $id_proses;
    $result = $this->mWalkorder->getListProduksiUkuran($params);

    $data    = [];
    $msg     = "Pengambilan data berhasil";
    $status  = true;

    foreach ($result as $row) {
      $id = encrypt($row->id);
      
      $qty_prod = !empty($row->qty_prod) ? $row->qty_prod : 0;
      $qty = $row->qty - $qty_prod;

      // get harga tarif proses 
      $prm['id_konsumen'] = $row->id_konsumen;
      $prm['style'] = $row->keterangan_style;
      $prm['id_proses'] = $id_proses;
      $get_harga = $this->mkonsumen->getDataHarga(null, 0, 9999, null, null, $prm);
      $harga_proses = 0;
      if(!empty($get_harga)){
        $harga_proses = $get_harga[0]->harga_borongan;
      }

      array_push(
        $data,
        array(
          "id"                  => ($id),
          "kode_warna"          => ($row->kode_warna),
          "kode_ukuran"         => $row->kode_ukuran,
          "id_ukuran"           => $row->id_ukuran,
          "id_walkorder_proses" => $row->id_walkorder_proses,
          "id_warna"            => $row->id_warna,
          "ref_detail_id"       => $row->ref_detail_id,
          "qty"                 => $qty,
          "harga_satuan"        => $row->harga,
          'harga_proses'        => $harga_proses
        )
      );
    }

    $build_array['data']    = $data;
    $build_array['message'] = $msg;
    $build_array['status']  = $status;
    return $this->response->setJSON($build_array);
  }


  // fungsi untuk scan barcode atau auto complete 
  public function getDataProduksiItem(){
      $barcode_code = $this->request->getPost("kata_kunci");
      $id_walkorder = $this->request->getPost("id_walkorder");
      $id_produksi  = $this->request->getPost("id_produksi");
      $proses  = $this->request->getPost("proses");

      $id_produksi  = \decrypt($id_produksi);
      $id_walkorder  = \decrypt($id_walkorder);


      $status = false;
      $msg = "Data warna ukuran tidak ditemukan !";
      $data = [];
      $slc  = [];

      // if(!empty($barcode_code)){
          // $params['last_proses']  = 1;
          $params['id_walkorder'] = $id_walkorder;
          $params['kata_kunci'] = $barcode_code;
          $params['id_proses'] = $proses;
          $result = $this->mWalkorder->getListProduksiUkuran($params);
          if(!empty($result)){
              foreach ($result as $r) {

                  // get harga tarif proses 
                  $prm['id_konsumen'] = $r->id_konsumen;
                  $prm['style'] = $r->keterangan_style;
                  $prm['id_proses'] = $proses;
                  $get_harga = $this->mkonsumen->getDataHarga(null, 0, 9999, null, null, $prm);
                  $harga_proses = 0;
                  if(!empty($get_harga)){
                    $harga_proses = $get_harga[0]->harga_borongan;
                  }

                  $isi = [];
                  $isi["id_walkorder"]  = $r->id_walkorder;
                  $isi["id_walkorder_proses"]  = $r->id;
                  $isi["ref_detail_id"] = $r->ref_detail_id;
                  $isi["id_ukuran"]     = $r->id_ukuran;
                  $isi["id_warna"]      = $r->id_warna;
                  $isi["qty"]           = $r->qty;
                  $isi["qty_prod"]      = 0;
                  $isi["kata_kunci"]    =  "(".$r->kode_warna.") " . $r->kode_ukuran;
                  $isi["kode_warna"]    = $r->kode_warna;
                  $isi["kode_ukuran"]   = $r->kode_ukuran;
                  $isi["key_ukuran"]    = $r->key_ukuran;
                  $isi["harga_proses"]    = $r->harga_proses;
                  $data[] = $isi;

                  $isi_slc = [];
                  $isi_slc["id"]    = $r->ref_detail_id;
                  $isi_slc["idx"]   = $r->kode_warna;
                  $isi_slc["label"] = "(".$r->kode_warna.") " . $r->kode_ukuran . " | jumlah " . $r->qty;
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



  function getCariProduk(){
    $kata_kunci = $this->request->getPost('kata_kunci');

    $id_walkorder = $this->request->getPost("id_walkorder");
    $id_produksi  = $this->request->getPost("id_produksi");
    $proses  = $this->request->getPost("proses");
    
    $status = false;
    $msg = "Data barang tidak ditemukan !";
    $data  = [];

    try {
      $id_produksi  = \decrypt($id_produksi);
      $id_walkorder  = \decrypt($id_walkorder);

      $kt_exp = explode(";",$kata_kunci);

      $kunci_jadi = $kt_exp[1] . ' ' . $kt_exp[2]; 

      $qty = $kt_exp[3]; 
      
      $params['id_walkorder'] = $id_walkorder;
      // $params['kata_kunci'] = $kunci_jadi;
      $params['id_proses'] = $proses;
      $params['key_ukuran'] = $kt_exp[1];
      $params['kode_warna'] = $kt_exp[2];
      // print_r($params);exit;
      $result = $this->mWalkorder->getListProduksiUkuran($params);

      foreach ($result as $r) {

        // get harga tarif proses 
        $prm['id_konsumen'] = $r->id_konsumen;
        $prm['style'] = $r->keterangan_style;
        $prm['id_proses'] = $proses;
        $get_harga = $this->mkonsumen->getDataHarga(null, 0, 9999, null, null, $prm);
        $harga_proses = 0;
        if(!empty($get_harga)){
          $harga_proses = $get_harga[0]->harga_borongan;
        }

        $isi = [];
        $isi["id_walkorder"]  = $r->id_walkorder;
        $isi["id_walkorder_proses"]  = $r->id;
        $isi["ref_detail_id"] = $r->ref_detail_id;
        $isi["id_ukuran"]     = $r->id_ukuran;
        $isi["id_warna"]      = $r->id_warna;
        $isi["qty"]           = $r->qty;
        $isi["qty_prod"]      = $qty;
        $isi["kata_kunci"]    =  "(".$r->kode_warna.") " . $r->kode_ukuran;
        $isi["kode_warna"]    = $r->kode_warna;
        $isi["kode_ukuran"]   = $r->kode_ukuran;
        $isi["key_ukuran"]    = $r->key_ukuran;
        $isi["harga_proses"]    = $r->harga_proses;
        $data[] = $isi;
      }

      $status = true;
      $msg = "Berhasil pengambilan data !";

    } catch (\Throwable $th) {
      //throw $th;
      // print_r($th);exit;
    }


    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = $data;
    return $this->response->setJSON($build_array); 
  }
}
