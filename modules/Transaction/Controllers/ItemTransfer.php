<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\SatuanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Transaction\Models\BarangMasukModel;
use Modules\Transaction\Models\ItemTransferModel;
use Modules\Laporan\Models\LaporanPersediaanModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\BarangMasukDetailModel;
use Modules\Transaction\Models\ItemTransferDetailModel;

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
    $id_proses      = $this->request->getPost('id_proses');
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
      $getSisa = $this->mRef->getDataPengurang($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color);
      if ($id_proses == 1) {
        $sisa = $row->qty;
      }
      else {
        $getProsesIdRajut = $this->mRef->getDataRajut($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $row->color);
        $sisa = $getProsesIdRajut;
      }
      if (!empty($getSisa)) {
        
        $sisa -= $getSisa;
      }
      if (empty($sisa)) {
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

      $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false, "id_gudang" => $resData->id_gudang_tujuan));
      $resDataDetSO = $this->mRefDet->getDataDetSO($id);
      foreach ($resDataDetSO as $key => $row) {
        
        $getSisa = $this->mRef->getDataPengurang($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $row->id);
        $params['kode_transaksi'] = $row->kode_sales_order;
        $params['kode_ukuran'] = $row->kode_ukuran;
        $params['all_color'] = $row->color;
        $getUkuranTrans = $this->mRef->getUkuranTrans(null, null, null, null, null, $params);
        if ($resData->id_proses == 1) {
          // $sisa = $row->qty;
          $sisa = 0;
          if (!empty($getUkuranTrans[0]->qty)) {
            # code...
            $sisa = $getUkuranTrans[0]->qty;
          }
        }
        else {
          $getProsesIdRajut = $this->mRef->getDataRajut($resData->id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_sales_order, $row->color, $row->id);
          $sisa = $getProsesIdRajut;
        }
        if (!empty($getSisa)) {
          
          $sisa -= $getSisa;
        }
        $row->qty_ref = $sisa;
        // $row->qty_ref = $getUkuranTrans[0]->qty - $sisa;
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
    $id_proses = $this->request->getGet("id_proses");
    $id_cmt = $this->request->getGet("id_cmt");
    if (isset($noSO)) {
      $results = $this->mRef->getDataByNoTrf($noSO);
    }
    else {
      $results = $this->mRef->getDataByProsesAndOperator($id_proses, $id_cmt);
    }
    $data = [];
    


    $data['status'] = true;
    if (isset($noSO)) {
      $resDataDetSO = !empty($results) ? $this->mRefDet->getDataDetSO($results->id) : null;
      $resDataDetSODet = !empty($results) ? $this->mRefDet->getDataDetail($results->id) : null;
      $data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : null;
      $data['dataSODet'] = !empty($resDataDetSODet) ? $resDataDetSODet : null;
    }
    else {
      $resDataDetSO = [];
      $resDataDetSODet = [];

      if (!empty($results)) {
          foreach ($results as $value) {
              $dataDetSO = $this->mRefDet->getDataDetSO($value->id);
              $dataDetSODet = $this->mRefDet->getDataDetail($value->id);

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
      }
      $grouped = [];

      foreach ($resDataDetSO as $row) {

          // buat key unik berdasarkan identitas barang
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
          } else {
              // jumlahkan qty jika sudah ada
              $grouped[$key]->qty_kirim += (int)$row->qty_kirim;
              $grouped[$key]->qty += (int)$row->qty;
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
    }
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
      $id_proses = $this->request->getPost('id_proses');
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
        
        // dd(trim($kt_exp[0]), trim($kt_exp[1]), trim($kt_exp[2]), trim($kt_exp[3]), trim($kt_exp[4]));
        // print_r($kt_exp);exit;
        // $kunci_jadi = $kt_exp[1] . ' ' . $kt_exp[2]; 

        $qty = $kt_exp[3]; 
        $params = [];
        if (!empty($kt_exp[0])) {
            $params['kode_transaksi'] = trim($kt_exp[0]);
        }
        if (!empty($kt_exp[1])) {
            $params['key_ukuran'] = trim($kt_exp[1]);
        }
        if (!empty($kt_exp[2])) {
            $params['color'] = trim($kt_exp[2]);
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

        // print_r($params);exit;
        $results = $this->mRef->getUkuranTrans(null, 0, 999, null, null, $params);
        // print_r($results);exit;
        foreach ($results as $r) {
          $getSisa = $this->mRef->getDataPengurang($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color);
          if ($id_proses == 1) {
            $sisa = $r->qty;
          }
          else {
            $getProsesIdRajut = $this->mRef->getDataRajut($id_proses, $r->id_konsumen, $r->kode_ukuran, $r->kode_transaksi, $r->color);
            $sisa = $getProsesIdRajut;
          }
          // if ($row->kode_transaksi == 'SOD260100033') {
          //     dd($id_proses, $row->id_konsumen, $row->kode_ukuran, $row->kode_transaksi, $getSisa);
          //   }
          if (!empty($getSisa)) {
            
            $sisa -= $getSisa;
          }
          if (empty($sisa)) {
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
            "qty"              => $qty,//$r->qty,
            "qty_ref"              => $sisa,//$r->qty,
            "amount"           => $r->amount,
            "kode_ukuran"      => $r->kode_ukuran,
            "kata_kunci"       => $r->kode_transaksi . " - (" . $r->color . ") " . $r->kode_ukuran,
            "print_type"       => $isi_kurung,
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

        $fileName = "Item Transfer-List.xlsx";

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
               ->setCellValue('A2', 'Rekapitulasi Transfer Barang '. $title)

               ->setCellValue('A4', 'KODE SO/SAMPLE')
               ->setCellValue('B4', 'TGL SO/SAMPLE')
               ->setCellValue('C4', 'BUYER')
               ->setCellValue('D4', 'STYLE')
               ->setCellValue('E4', 'PROSES')
               ->setCellValue('F4', 'CMT')
               ->setCellValue('G4', 'WARNA')
               ->setCellValue('H4', 'UKURAN')
               ->setCellValue('I4', 'QTY SO/SPL')
               ->setCellValue('J4', 'QTY KIRIM')
               ->setCellValue('K4', 'QTY TERIMA')
               ->setCellValue('L4', 'SELISIH');

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
        $gets->getStyle('A4:L4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('A2:L2');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(20);
          $gets->getColumnDimension('C')->setWidth(30);
          $gets->getColumnDimension('D')->setWidth(25);
          $gets->getColumnDimension('E')->setWidth(35);
          $gets->getColumnDimension('F')->setWidth(35);
          $gets->getColumnDimension('G')->setWidth(50);
          $gets->getColumnDimension('H')->setWidth(15);
          $gets->getColumnDimension('I')->setWidth(17);
          $gets->getColumnDimension('J')->setWidth(17);
          $gets->getColumnDimension('K')->setWidth(17);
          $gets->getColumnDimension('L')->setWidth(17);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:L4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:L4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L'
        );

        for ($i=0; $i < 12 ; $i++) { 

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
        // for ($i=1; $i <= 4 ; $i++) { 
        //     // declaration image
        //     $isR = $ix * $is;
        //     if($is > 0){
        //         $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        //         $drawing->setName('Paid');
        //         $drawing->setDescription('Paid');
        //         $drawing->setPath(ROOTPATH . 'public/assets/images/text-excel.png');
        //         $drawing->setCoordinates('C'.$isR);
        //         $drawing->setOffsetX(85);
        //         $drawing->setRotation(-35);
        //         // $drawing->getShadow()->setVisible(false);
        //         // $drawing->getShadow()->setDirection(45);
        //         $drawing->setHeight(65);
        //         $drawing->setWorksheet($gets);
        //     }

        //     $is += $ix;
        // }
        
        
        $length = $ix;

        if(!empty($results)){
            $length += count($results);
        }

        $qty = 0;
        $amount = 0;
        
        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];
            
            $selisih = $r->qty - $r->qty_terima;

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->kode) ? $r->kode : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->buyer) ? $r->buyer : "-")
                    ->setCellValue('D'.$ix, !empty($r->style) ? $r->style : "-")
                    ->setCellValue('E'.$ix, !empty($r->proses) ? $r->proses : '-')
                    ->setCellValue('F'.$ix, !empty($r->nama_operator) ? $r->nama_operator : '-')
                    ->setCellValue('G'.$ix, !empty($r->color) ? $r->color : '-')
                    ->setCellValue('H'.$ix, !empty($r->kode_ukuran) ? $r->kode_ukuran : '-')
                    ->setCellValue('I'.$ix, !empty($r->qty_ref) ? $r->qty_ref : 0)
                    ->setCellValue('J'.$ix, !empty($r->qty) ? $r->qty : 0)
                    ->setCellValue('K'.$ix, !empty($r->qty_terima) ? $r->qty_terima : 0)
                    ->setCellValue('L'.$ix, !empty($selisih) ? $selisih : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':L'.$ix)->applyFromArray($stylexArray);
            // }

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':H'. $length);
        
        $gets->getStyle('A'.$length.':L'.$length)->applyFromArray($stylexArrayFooter);
        
       $sheets->setActiveSheetIndex(0)
                      ->setCellValue('I' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)
                      ->setCellValue('J' . $length, '=SUM(J' . $startRow . ':J' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)
                      ->setCellValue('K' . $length, '=SUM(K' . $startRow . ':K' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)
                      ->setCellValue('L' . $length, '=SUM(L' . $startRow . ':L' . $length-1 . ')');
               
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


