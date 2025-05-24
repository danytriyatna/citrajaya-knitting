<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use Modules\Laporan\Models\LaporanPersediaanModel;
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
  protected $mRefPersediaan;
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
    $isApprove      = $this->request->getPost('isApprove');

    $params = [];
    if ($isApprove) {
      $params['status'] = "2";
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

    $params = [];
    if ($isApprove) {
      $params['status'] = "2";
    }

    if(!empty($kategori)){
      if($kategori == 12){
        $params['ref_produksi'] = 1;
      }else{
        $params['ref_produksi'] = 2;
      }
    }else{
      $params['ref_produksi'] = 2;
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
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

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
      array_push(
        $build_array["data"],
        array(
          "tipe" => $row->tipe,
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
      $resDataDetSO = $this->mRefDet->getDataDetSO($id);

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
    $noSO = $this->request->getGet("noSO");
    $data = [];
    $results = $this->mRef->getDataByNoTrf($noSO);


    $data['status'] = true;
    $resDataDetSO = !empty($results) ? $this->mRefDet->getDataDetSO($results->id) : null;
    $resDataDetSODet = !empty($results) ? $this->mRefDet->getDataDetail($results->id) : null;
    // if (!empty($resDataDetSO)) {
    //   foreach ($resDataDetSO as &$rowData) {
    //     $rowData->qty_sisa = encrypt($rowData->id_barang);
    //   }
    // }
    $data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : null;
    $data['dataSODet'] = !empty($resDataDetSODet) ? $resDataDetSODet : null;
    return $this->response->setJSON($data);
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

    $refProduksi = $this->request->getPost('ref_produksi');


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
      "ref_produk" => $refProduksi
    ];

    if ($id) {
      $dataHeader['updated_at'] = date("Y-m-d H:i:s");
      $dataHeader['updated_by'] = $this->get_userid();
    } else {
      $dataHeader['created_at'] = date("Y-m-d H:i:s");
      $dataHeader['created_by'] = $this->get_userid();
    }
    // print_r($dataDetail);exit;
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


      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang_tujuan));
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];

    }

    // dd($this->data);
    $html = view($this->views . '\item_transfer_print', $this->data);

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
    exit;
  }

  public function lists_persediaan()
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
            array_push(
                $build_array["data"],
                array(
                    "id"   => $row->id_barang,
                    "nama_barang" => $row->nama_barang,
                    "kode_barang" => $row->kode_barang,
                    "nama_satuan" => $row->nama_satuan,
                    "harga_satuan" => $row->harga_satuan,
                    "qty" => $row->qty,
                    "lot_no" => $row->lot_no,
                    "lot_id" => $row->lot_id,
                    "id_barang" => $row->id_barang,
                    "month" => $row->month,
                )
            );
        }
        return $this->response->setJSON($build_array);
    }


    function getDataProduksiItem(){
      $kata_kunci = $this->request->getPost('kata_kunci');
      
      $status = false;
      $msg = "Data barang tidak ditemukan !";
      $data  = [];
      $slc  = [];

      try {

        $kt_exp = explode(";",$kata_kunci);
    
        $params['kata_kunci'] = $kata_kunci;
        $results = $this->mRef->getUkuranTrans(null, 0, 999, null, null, $params);
        // print_r($results);exit;
        foreach ($results as $r) {

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
      
      $status = false;
      $msg = "Data barang tidak ditemukan !";
      $data  = [];
      $slc  = [];

      try {

        $kt_exp = explode(";",$kata_kunci);
        // print_r($kt_exp);exit;
        // $kunci_jadi = $kt_exp[1] . ' ' . $kt_exp[2]; 

        $qty = $kt_exp[3]; 
        $params = [];
        if (!empty($kt_exp[0])) {
            $params['kode_transaksi'] = $kt_exp[0];
        }
        if (!empty($kt_exp[1])) {
            $params['kode_ukuran'] = ($kt_exp[1]);
        }
        if (!empty($kt_exp[2])) {
            $params['color'] = $kt_exp[2];
        }
        $results = $this->mRef->getUkuranTrans(null, 0, 999, null, null, $params);
// print_r($results);exit;
        foreach ($results as $r) {

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
            "qty"              => $qty,//$r->qty,
            "amount"           => $r->amount,
            "kode_ukuran"      => $r->kode_ukuran,
            "kata_kunci"       => $r->kode_transaksi . " - (" . $r->color . ") " . $r->kode_ukuran,
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
}
