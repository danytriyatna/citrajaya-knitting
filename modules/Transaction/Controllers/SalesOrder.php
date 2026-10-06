<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use App\Models\FileModel;
use CodeIgniter\Controller;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\RekeningModel;
use Modules\Referensi\Models\UkuranModel;
use Modules\Referensi\Models\WarnaModel;
use Modules\Transaction\Models\ProductionModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Transaction\Models\WalkorderModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesOrder extends BaseController
{
  protected $mSalesOrder;
  protected $mSample;
  protected $mkonsumen;
  protected $files;
  protected $mUkuran;
  protected $mWarna;
  protected $mworkOrder;
  protected $mRekening;
  protected $mProduksi;
  protected $mBarang;

  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/sales-order';

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_TRANSAKSI_SALES_ORDER";

    $this->mSalesOrder = new SalesOrderModel();
    $this->mkonsumen = new KonsumenModel();
    $this->files  = new FileModel();
    $this->mUkuran = new UkuranModel();
    $this->mWarna = new WarnaModel();
    $this->mSample = new SampleModel();
    $this->mworkOrder = new WalkorderModel();
    $this->mRekening   = new RekeningModel();
    $this->mProduksi = new ProductionModel();
    $this->mBarang = new BarangModel();
  }


  public function index()
  {
    if (!$this->auth->loggedIn() || !$this->_view) {
      return redirect()->to('/auth/login');
    }

    $cache = \Config\Services::cache();
    $this->data['titlehead'] = "SalesOrder";
    $this->data['buyer'] = $cache->remember('ref_konsumen_active', 3600, fn() => $this->mkonsumen->where("active", 1)->findAll());
    $this->data['type'] = $this->data['buyer'];
    $this->data['ukuran'] = $cache->remember('ref_ukuran_active', 3600, fn() => $this->mUkuran->where("active", 1)->findAll());
    $this->data['warna'] = $cache->remember('ref_warna_active', 3600, fn() => $this->mWarna->where("active", 1)->findAll());
    $this->data['rekening_list'] = $cache->remember('ref_rekening_active', 3600, fn() => $this->mRekening->where("active", 1)->findAll());
    $this->data['role_id'] = session()->get('role_id');
    $this->data['new_access'] = $this->_new;
    $this->data['edit_access'] = $this->_edit;
    $this->data['delete_access'] = $this->_delete;
    $this->data['print_access'] = $this->_print;
    // FIX (2026-08-11): tombol Approval tampil utk Superadmin (role_id 1) & Admin Aplikasi (role_id 2).
    $roleId = (int) session()->get('role_id');
    $this->data['approve_access'] = in_array($roleId, [1, 2]) || $this->auth->isAdmin() || $this->auth->isSuperadmin();
    $this->data['barang'] = $cache->remember('ref_barang_active', 3600, fn() => $this->mBarang->where("active", 1)->orderBy("nama_barang", 'asc')->findAll());
    
    return view($this->views . '\sales_order_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $params = [];
    $id_konsumen = $this->request->getPost('id_konsumen');
    $statusFilter = $this->request->getPost('status');
    $fromDate = $this->request->getPost('from_date');
    $toDate = $this->request->getPost('to_date');

    if (!empty($id_konsumen) && $id_konsumen !== 'all') {
      $params['id_konsumen'] = $id_konsumen;
    }
    if (!empty($statusFilter) && $statusFilter !== 'all') {
      $params['status'] = (int)$statusFilter;
    }
    if (!empty($fromDate)) {
      $params['from_date'] = $fromDate;
    }
    if (!empty($toDate)) {
      $params['to_date'] = $toDate;
    }

    $results = $this->mSalesOrder->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mSalesOrder->getDataCnt($filters, $params);
    $totaldata = $this->mSalesOrder->getDataCnt(null, $params);
    $maxpage = ceil($totalfiltered / $limit);
    
    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    $soDetailsMap = [];
    $allDetIds = [];

    // Phase 1: Collect details and IDs
    foreach ($results as $key => $row) {
      $pru = ['use' => 1, 'id_sales_order' => $row->id];
      $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
      $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($row->id, $dtUkuran) : [];
      if (!empty($detail)) {
        foreach ($detail as $drow) {
          if (!empty($drow->id)) {
            $allDetIds[] = $drow->id;
          }
        }
      }
      $soDetailsMap[$row->id] = [
        'dtUkuran' => $dtUkuran,
        'detail' => $detail
      ];
    }

    // Phase 2: Batch query total qty for all collected detail IDs
    $qtyMap = !empty($allDetIds) ? $this->mSalesOrder->getBatchTotalQtyByDetIds(array_unique($allDetIds)) : [];

    // Phase 3: Populate build_array
    foreach ($results as $key => $row) {
      $id = encrypt($row->id);
      $currentStatus = (int)$row->status;
      if ($currentStatus === 2 || $currentStatus === 3) {
        $evalStatus = $this->mSalesOrder->evaluateAndSyncClosedStatus($row->id);
        if ($evalStatus !== null) {
          $currentStatus = $evalStatus;
        }
      }

      $status = $currentStatus === 1 ? "Draft" : ($currentStatus === 3 ? "Closed" : "Approved");

      $dtUkuran = $soDetailsMap[$row->id]['dtUkuran'];
      $detail = $soDetailsMap[$row->id]['detail'];

      if (!empty($detail)) {
        for ($i = 0; $i < count($detail); $i++) {
          $dId = $detail[$i]->id;
          $detail[$i]->qty = isset($qtyMap[$dId]) ? $qtyMap[$dId] : 0;
        }
      }

      array_push(
        $build_array["data"],
        array(
          "id"   => ($id),
          "id_sample"   => ($row->id_sample),
          "nama" => $row->nama,
          "tgl_transaksi" => $row->tgl_transaksi,
          "kode_sales_order" => $row->kode_sales_order,
          "tgl_deadline" => $row->tgl_deadline,
          "tgl_deadline_dua" => $row->tgl_deadline_dua,
          "deskripsi" => $row->deskripsi,
          "stylex" => $row->stylex,
          "style" => $row->style,
          "ref_po" => $row->ref_po,
          "style_cnt_order" => $row->style_cnt,
          "uang_dp" => !empty($row->uang_dp) ? \format_angka($row->uang_dp) : 0,
          "uang_dp_2" => !empty($row->uang_dp_2) ? \format_angka($row->uang_dp_2) : 0,
          "pengiriman" => !empty($row->pengiriman) ? \format_angka($row->pengiriman) : 0,
          "status"  => $status,
          "status_raw" => $currentStatus,
          "file_gambar" => !empty($row->file_name) ? base_url() . "uploads/sales_order/"  . $row->file_name : "",
          "detail" => $detail,
          "key_ukuran" => $dtUkuran
        )
      );
    }
    
    return $this->response->setJSON($build_array);
  }

  function view()
  {
    $detail = [];
    $dtUkuran = [];
    $idSample = $this->request->getGet('idSample');
    $kodeOrder = $this->request->getGet('kodeOrder');
    if (!empty($kodeOrder) && strpos($kodeOrder, ':') !== false) {
      $kodeOrder = trim(explode(':', $kodeOrder)[0]);
    }
    $soId = $this->request->getGet('soId');

    $sampleUkuran = [];
    if (!empty($idSample)) {
      $id = $idSample;
      $pru['use'] = 1;
      $pru['id_sample'] = $id;
      $sampleUkuran = $this->mSample->getUkuranTrans($pru);
      $detail = (!empty($sampleUkuran)) ? $this->mSample->getDataDetailSample_crostab($id) : [];
    } else if (!empty($kodeOrder)) {
      $dataSO = $this->mSalesOrder->getDataSO($kodeOrder);
      if (!empty($dataSO)) {
        $id = $dataSO->id;
        $pru['use'] = 1;
        $pru['id_sales_order'] = $id;
        $sampleUkuran = $this->mSalesOrder->getUkuranTrans($pru);
        $detail = (!empty($sampleUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($id, $sampleUkuran) : [];
      } else {
        $dataSample = $this->mSample->getDataSample($kodeOrder);
        if (!empty($dataSample)) {
          $id = $dataSample->id;
          $pru['use'] = 1;
          $pru['id_sample'] = $id;
          $sampleUkuran = $this->mSample->getUkuranTrans($pru);
          $detail = (!empty($sampleUkuran)) ? $this->mSample->getDataDetailSample_crostab($id) : [];
        }
      }
    }

    $soUkuran = [];
    if (!empty($soId)) {
      $soUkuran = $this->mSalesOrder->getUkuranTrans(['use' => 1, 'id_sales_order' => decrypt($soId)]);
    }

    // Merge ukuran dari referensi Sample dan Sales Order
    $mergedUkuranMap = [];
    foreach ($sampleUkuran as $uk) {
      $mergedUkuranMap[$uk->key_ukuran] = $uk;
    }
    foreach ($soUkuran as $uk) {
      $mergedUkuranMap[$uk->key_ukuran] = $uk;
    }
    $dtUkuran = array_values($mergedUkuranMap);

    if (empty($soId)) {
      // ✅ SAAT TAMBAH BARU: Set seluruh QTY warna dari sampel ke 0
      if (!empty($detail)) {
        foreach ($detail as &$drow) {
          foreach ($dtUkuran as $uk) {
            $keyUk = ($uk->key_ukuran == 'all') ? 'all_' : $uk->key_ukuran;
            if (isset($drow->$keyUk)) {
              $drow->$keyUk = 0;
            }
          }
          $drow->total_harga = 0;
        }
      }
    } else {
      // ✅ SAAT EDIT:
      // 1. Tampilkan SELURUH warna yang sudah tersimpan di SO ini (prioritas utama).
      //    Walaupun sampelnya dirubah atau warna itu dihapus dari sampel, yang sudah tersimpan di SO tetap tampil.
      $existingDetail = $this->mSalesOrder->getDataDetailSalesOrder_crostab(decrypt($soId), $dtUkuran);
      $finalDetail = [];
      $processedColorMap = [];

      if (!empty($existingDetail)) {
        foreach ($existingDetail as $ex) {
          $finalDetail[] = $ex;
          $processedColorMap[$ex->colour] = true;
        }
      }

      // 2. Jika ada warna baru dari Sampel yang belum ada di SO, tambahkan sebagai baris baru dengan QTY = 0.
      if (!empty($detail)) {
        foreach ($detail as $drow) {
          if (!isset($processedColorMap[$drow->colour])) {
            foreach ($dtUkuran as $uk) {
              $keyUk = ($uk->key_ukuran == 'all') ? 'all_' : $uk->key_ukuran;
              if (isset($drow->$keyUk)) {
                $drow->$keyUk = 0;
              }
            }
            $drow->total_harga = 0;
            $finalDetail[] = $drow;
            $processedColorMap[$drow->colour] = true;
          }
        }
      }
      $detail = $finalDetail;
    }

    return $this->response->setJSON(array("data" => $detail, "ukuran" => $dtUkuran));
  }

  function detail($id)
  {
    $id = decrypt($id);
    $results = $this->mSalesOrder->getData($id);
    $status = $results->status == 1 ? "Draft" : "Approved";

    $sampleUkuran = [];
    if (!empty($results->id_sample)) {
      $sampleUkuran = $this->mSample->getUkuranTrans(['use' => 1, 'id_sample' => $results->id_sample]);
    }
    $soUkuran = $this->mSalesOrder->getUkuranTrans(['use' => 1, 'id_sales_order' => $id]);
    $mergedUkuranMap = [];
    foreach ($sampleUkuran as $uk) {
      $mergedUkuranMap[$uk->key_ukuran] = $uk;
    }
    foreach ($soUkuran as $uk) {
      $mergedUkuranMap[$uk->key_ukuran] = $uk;
    }
    $dtUkuran = array_values($mergedUkuranMap);

    $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($results->id, $dtUkuran) : [];

    $build_array =  array(
      "id"   => encrypt($results->id),
      "style" => $results->style,
      "stylex" => $results->stylex,
      "style_cnt_order" => $results->style_cnt,
      "keterangan" => $results->keterangan,
      "id_konsumen" => $results->id_konsumen,
      "tgl_transaksi" => $results->tgl_transaksi,
      "kode_sales_order" => $results->kode_sales_order,
      "ref_po" => $results->ref_po,
      "tgl_deadline" => $results->tgl_deadline,
      "tgl_deadline_dua" => $results->tgl_deadline_dua,
      "deskripsi" => $results->deskripsi,
      "gambar_id" => $results->gambar_id,
      "file_gambar" => !empty($results->file_name) ? base_url() . "uploads/sales_order/" . $results->file_name : "",
      "id_sample" => $results->id_sample,
      "status" => $status,
      "uang_dp" =>  !empty($results->uang_dp) ? $results->uang_dp : 0,
      "tgl_dp" => $results->tgl_dp,
      "type_dp" => $results->type_dp,
      "uang_dp_2" =>  !empty($results->uang_dp_2) ? $results->uang_dp_2 : 0,
      "tgl_dp_2" => $results->tgl_dp_2,
      "type_dp_2" => $results->type_dp_2,
      "pengiriman" =>  !empty($results->pengiriman) ? $results->pengiriman : 0,
      "detail" => $detail,
      "key_ukuran" => $dtUkuran
    );
    return $this->response->setJSON($build_array);
  }

  function detailQtyUkuran($idSalesOrder, $idSalesOrderDet)
  {
    $id = 0;
    if (!empty($idSalesOrder) && $idSalesOrder != 'null' && $idSalesOrder != 'undefined') {
      if (is_numeric($idSalesOrder)) {
        $id = (int)$idSalesOrder;
      } else {
        try {
          $dec = decrypt($idSalesOrder);
          $id = is_numeric($dec) ? (int)$dec : 0;
        } catch (\Throwable $e) {
          $id = 0;
        }
      }
    }
    $idSalesOrderDet = (int)$idSalesOrderDet;

    if (!empty($id)) {
      // ✅ Kasus SO yang sudah tersimpan (Edit SO)
      $results = $this->mSalesOrder->getData($id);
      $db = \Config\Database::connect();
      
      // Ambil single detail row dari trans_sales_order_det secara langsung
      $detail = $db->table('trans_sales_order_det')->where('id', $idSalesOrderDet)->where('id_sales_order', $id)->get()->getRow();
      $detailUkuran = [];

      if (!empty($detail)) {
        for ($k = 1; $k <= 8; $k++) {
          $wKey = "id_warna_" . $k;
          $bKey = "id_barang_" . $k;
          if (!empty($detail->$bKey) && empty($detail->$wKey)) {
            $detail->$wKey = $this->mSalesOrder->getWarnaIdByBarangId($detail->$bKey);
          } else if (empty($detail->$bKey) && !empty($detail->$wKey)) {
            $detail->$bKey = $this->mSalesOrder->getBarangIdByWarnaId($detail->$wKey);
          }
        }

        $detailUkuran = $this->mSalesOrder->getDataDetailSampleUkuran($id, $idSalesOrderDet);
      } else {
        // Fallback: Jika $idSalesOrderDet adalah ID dari trans_sample_det (karena combo baru dari sampel)
        $sampleDet = $db->table('trans_sample_det')->where('id', $idSalesOrderDet)->get()->getRow();
        if (!empty($sampleDet)) {
          $detail = new \stdClass();
          $detail->id = $sampleDet->id;
          for ($k = 1; $k <= 8; $k++) {
            $bKey = "id_barang_" . $k;
            $wKey = "id_warna_" . $k;
            $detail->$bKey = $sampleDet->$bKey;
            $detail->$wKey = $sampleDet->$wKey;
            if (!empty($detail->$bKey) && empty($detail->$wKey)) {
              $detail->$wKey = $this->mSalesOrder->getWarnaIdByBarangId($detail->$bKey);
            } else if (empty($detail->$bKey) && !empty($detail->$wKey)) {
              $detail->$bKey = $this->mSalesOrder->getBarangIdByWarnaId($detail->$wKey);
            }
          }
          if (!empty($results->id_sample)) {
            $detailUkuran = $this->mSample->getDataDetailSampleUkuran($results->id_sample, $sampleDet->id);
          }
          if (empty($detailUkuran)) {
            $detailUkuran = $this->mSalesOrder->getDataDetailSampleUkuran($id, 0);
          }
        }
      }
    } else {
      // ✅ Kasus SO Baru (Form Tambah SO Baru) - $idSalesOrderDet merujuk pada id trans_sample_det
      $sdRow = $this->mSample->getDataDetailSampleWarna(null, $idSalesOrderDet);
      $detail = new \stdClass();
      $detailUkuran = [];
      $results = new \stdClass();
      $results->id = 0;
      $results->keterangan = '';
      $results->nama = '';
      $results->tgl_transaksi = date('Y-m-d');
      $results->kode_sales_order = '';
      $results->tgl_deadline = date('Y-m-d');
      $results->tgl_deadline_dua = '';
      $results->deskripsi = '';
      $results->file_name = '';

      if (!empty($sdRow)) {
        $detail->id = $sdRow->id;
        $sampleHeader = $this->mSample->getData($sdRow->id_sample);
        if (!empty($sampleHeader)) {
          $results->kode_sales_order = $sampleHeader->kode_sample;
          $results->deskripsi = $sampleHeader->deskripsi;
          $results->tgl_transaksi = $sampleHeader->tgl_transaksi;
          $results->tgl_deadline = $sampleHeader->tgl_deadline;
          $results->nama = $sampleHeader->nama;
          $results->file_name = $sampleHeader->file_name;
        }

        for ($k = 1; $k <= 8; $k++) {
          $wKey = "id_warna_" . $k;
          $bKey = "id_barang_" . $k;
          $detail->$wKey = !empty($sdRow->$wKey) ? $sdRow->$wKey : null;
          $detail->$bKey = !empty($sdRow->$bKey) ? $sdRow->$bKey : null;

          if (!empty($detail->$bKey) && empty($detail->$wKey)) {
            $detail->$wKey = $this->mSalesOrder->getWarnaIdByBarangId($detail->$bKey);
          } else if (empty($detail->$bKey) && !empty($detail->$wKey)) {
            $detail->$bKey = $this->mSalesOrder->getBarangIdByWarnaId($detail->$wKey);
          }
        }

        $detailUkuran = $this->mSample->getDataDetailSampleUkuran($sdRow->id_sample, $idSalesOrderDet);
      }
    }

    helper('my');
    $build_array = array(
      "id" => (!empty($results->id) && function_exists('encrypt')) ? encrypt($results->id) : (!empty($results->id) ? $results->id : ''),
      "keterangan" => $results->keterangan ?? '',
      "nama" => $results->nama ?? '',
      "tgl_transaksi" => $results->tgl_transaksi ?? '',
      "kode_sales_order" => $results->kode_sales_order ?? '',
      "tgl_deadline" => $results->tgl_deadline ?? '',
      "tgl_deadline_dua" => $results->tgl_deadline_dua ?? '',
      "deskripsi" => $results->deskripsi ?? '',
      "file_gambar" => !empty($results->file_name) ? base_url() . "uploads/sales_order/" . $results->file_name : "",
      "detail" => $detail,
      "detailUkuran" => $detailUkuran
    );
    return response()->setJSON($build_array);
  }


  function save()
  {
    $id = $this->request->getPost('id');
    if (!$this->auth->loggedIn() || (!$this->_new && empty($id))) {
      $build_array['message'] = 'Anda tidak memiliki akses untuk tambah data!';
      $build_array['status']  = false;
      return $this->response->setJSON($build_array);
    }
    else if (!$this->_edit) {
      $build_array['message'] = 'Anda tidak memiliki akses untuk edit data!';
      $build_array['status']  = false;
      return $this->response->setJSON($build_array);
    }
    $idKonsumen         = $this->request->getPost('idKonsumen');
    $tglTransaksi = $this->request->getPost('tglTransaksi');
    $tglDeadline = $this->request->getPost('tglDeadline');
    $tglDeadlineDua = $this->request->getPost('tglDeadlineDua');
    $deskripsi = $this->request->getPost('deskripsi');
    $keterangan = $this->request->getPost('deskripsi');
    $fileIdSalesOrderOld = $this->request->getPost('fileIdSalesOrderOld');
    $noSalesOrder = $this->request->getPost('noSalesOrder');
    $sampleId = $this->request->getPost('samples');
    $submit_data = $this->request->getPost('submit_data');
    // FIX (2026-08-08): approve Sales Order sekarang HANYA boleh oleh
    // Superadmin & Admin Aplikasi, terlepas dari izin allow_approve yang
    // mungkin diberikan ke role lain lewat menu Roles - permintaan eksplisit
    // supaya role lain sama sekali tidak bisa approve.
    $roleIdSession = (int) session()->get('role_id');
    if ($submit_data == 1 && !in_array($roleIdSession, [1, 2]) && !$this->auth->isAdmin() && !$this->auth->isSuperadmin()) {
      $build_array['message'] = 'Hanya Superadmin dan Admin Aplikasi yang dapat melakukan APPROVE!';
      $build_array['status']  = false;
      return $this->response->setJSON($build_array);
    }
    $uang_dp = $this->request->getPost('uang_dp');
    $uang_dp_2 = $this->request->getPost('uang_dp_2');
    $pengiriman = $this->request->getPost('pengiriman');
    $tgl_dp = $this->request->getPost('tgl_dp');
    $type_dp = $this->request->getPost('type_dp');
    $tgl_dp_2 = $this->request->getPost('tgl_dp_2');
    $type_dp_2 = $this->request->getPost('type_dp_2');
    if ($type_dp == 'null' || empty($type_dp)) {
      $type_dp = null;
    }
    if ($type_dp_2 == 'null' || empty($type_dp_2)) {
      $type_dp_2 = null;
    }
    $style = $this->request->getPost('style');
    $ref_po = $this->request->getPost('ref_po');
    $repeat = $this->request->getPost('repeat');
    if (strpos($tgl_dp, 'undefined') !== false || empty($tgl_dp))  {
      $tgl_dp = null;
    }
    if (strpos($tgl_dp_2, 'undefined') !== false || empty($tgl_dp_2))  {
      $tgl_dp_2 = null;
    }
    if (strpos($tglDeadlineDua, 'undefined') !== false || empty($tglDeadlineDua))  {
      $tglDeadlineDua = null;
    }

    $this->validation->setRules([
      'idKonsumen '               => ['label' => 'Pilih Buyer', 'rules' => 'required'],
      'tglTransaksi'         => ['label' => 'Tanggal Transaksi', 'rules' => 'required'],
      'tglDeadline'          => ['label' => 'Tanggal Deadline', 'rules' => 'required'],
      'deskripsi'             => ['label' => 'Deskripsi', 'rules' => 'required|trim'],
    ]);

    if (!empty($this->request->getFile('fileSalesOrder'))) {
      $fileSalesOrder       = $this->request->getFile('fileSalesOrder');
      $fileName       = $fileSalesOrder->getRandomName();
      $originName     = $fileSalesOrder->getName();
      $fileType       = $fileSalesOrder->getMimeType();
      $fileSize       = $fileSalesOrder->getSize();


      if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
        $build_array['message'] = "<br>File <b>SalesOrder</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>";
        $build_array['status']  = false;
        return $this->response->setJSON($build_array);
      }

      $this->files->insert([
        'file_name' => $fileName,
        'file_size' => $fileSize,
        'file_type' => $fileType,
        'file_name_origin' => $originName,
        'active'    => 1,
      ]);

      $fileIdSalesOrder = $this->files->insertID();
      $fileSalesOrder->move(WRITEPATH . 'uploads/sales_order/', $fileName);

      if ($fileIdSalesOrderOld != "") {
        // $nama_file =  $this->files->where('id', $fileIdSalesOrderOld)->get()->getRow()->file_name;
        // unlink(WRITEPATH . 'uploads/sales_order/' . $nama_file);

        // $this->files->delete(['id' => $fileIdSalesOrderOld]);
      }
    } else {
      $fileIdSalesOrder = $fileIdSalesOrderOld;
    }

    $msg    = "Data gagal ditambahkan !";
    $status = false;
    $arr_isi = [
      'id_konsumen' => $idKonsumen,
      'keterangan' => $keterangan,
      'deskripsi' => $deskripsi,
      'tgl_transaksi' => $tglTransaksi,
      'tgl_deadline' => $tglDeadline,
      'tgl_deadline_dua' => $tglDeadlineDua,
      'uang_dp' => $uang_dp,
      'tgl_dp' => $tgl_dp,
      'type_dp' => $type_dp,
      'uang_dp_2' => $uang_dp_2,
      'tgl_dp_2' => $tgl_dp_2,
      'type_dp_2' => $type_dp_2,
      'pengiriman' => $pengiriman,
      'style' => $style,
      'ref_po' => $ref_po,
      'style_cnt' => $repeat,
      // 'kode_sales_order' => $noSalesOrder,
      'active' => 1,
      // 'status' => 1,
      'gambar_id' => !empty($fileIdSalesOrder) ? $fileIdSalesOrder : null
    ];

    // print_r($sampleId != 'null');exit;

    if (!empty($sampleId) && $sampleId != 'null') {
      $arr_isi['id_sample'] = $sampleId;
    }


    $this->db->transBegin();

    if (empty($id)) {
      $arr_isi['status'] = 1;
      $arr_isi['created_at'] = date("Y-m-d H:i:s");
      $arr_isi['kode_sales_order'] = $this->mSalesOrder->generete_kode();
      // print_r($arr_isi);exit;
      $hid = $this->mSalesOrder->insertRecordGetid($this->mSalesOrder->table, $arr_isi);

      if (!empty($sampleId) && $sampleId != 'null') {
        $data_detail = $this->mSample->getDataDetailSample_ori($sampleId);
        if (!empty($data_detail)) {

          $head_qty = 0;
          $head_total = 0;
          foreach ($data_detail as $r) {
            $b1 = !empty($r->id_barang_1) ? $r->id_barang_1 : (!empty($r->id_warna_1) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_1) : null);
            $b2 = !empty($r->id_barang_2) ? $r->id_barang_2 : (!empty($r->id_warna_2) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_2) : null);
            $b3 = !empty($r->id_barang_3) ? $r->id_barang_3 : (!empty($r->id_warna_3) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_3) : null);
            $b4 = !empty($r->id_barang_4) ? $r->id_barang_4 : (!empty($r->id_warna_4) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_4) : null);
            $b5 = !empty($r->id_barang_5) ? $r->id_barang_5 : (!empty($r->id_warna_5) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_5) : null);
            $b6 = !empty($r->id_barang_6) ? $r->id_barang_6 : (!empty($r->id_warna_6) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_6) : null);
            $b7 = !empty($r->id_barang_7) ? $r->id_barang_7 : (!empty($r->id_warna_7) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_7) : null);
            $b8 = !empty($r->id_barang_8) ? $r->id_barang_8 : (!empty($r->id_warna_8) ? $this->mSalesOrder->getBarangIdByWarnaId($r->id_warna_8) : null);

            $arr_isid = [
              'id_sales_order' => $hid,
              'keterangan' => '',
              'id_warna_1' => $r->id_warna_1,
              'id_warna_2' => $r->id_warna_2,
              'id_warna_3' => $r->id_warna_3,
              'id_warna_4' => $r->id_warna_4,
              'id_warna_5' => $r->id_warna_5,
              'id_warna_6' => $r->id_warna_6,
              'id_warna_7' => $r->id_warna_7,
              'id_warna_8' => $r->id_warna_8,
              'id_barang_1' => $b1,
              'id_barang_2' => $b2,
              'id_barang_3' => $b3,
              'id_barang_4' => $b4,
              'id_barang_5' => $b5,
              'id_barang_6' => $b6,
              'id_barang_7' => $b7,
              'id_barang_8' => $b8,
              'total_harga' => $r->total_harga,
            ];
            $hidd = $this->mSalesOrder->insertRecordGetid('trans_sales_order_det', $arr_isid);
            $data_details = $this->mSample->getDataDetailSampleUkuran($sampleId, $r->id);
            if (!empty($data_details)) {
              foreach ($data_details as $rx) {
                $arr_isidx = [
                  'id_sales_order' => $hid,
                  'id_sales_order_det' => $hidd,
                  'id_ukuran' => $rx->id_ukuran,
                  'qty' => $rx->qty,
                  'harga_satuan' => $rx->harga_satuan,
                  'harga_total' => $rx->harga_total,
                ];

                $head_qty = $head_qty + (!empty($rx->qty)) ? (int) $rx->qty : 0;
                $head_total = $head_total + (!empty($rx->harga_total)) ? (float) $rx->harga_total : 0;
                $this->mSalesOrder->insertRecordGetid('trans_sales_order_ukuran', $arr_isidx);
              }
            }
          }

          $head_up['qty'] = $head_qty;
          $head_up['total_harga'] = $head_total;
          $this->mSalesOrder->updateRecord($this->mSalesOrder->table, $head_up, 'id', $hid);
        }
      }
    } else {
      $arr_isi['updated_at'] = date("Y-m-d H:i:s");
      $id = decrypt($id);
      if (!empty($submit_data)) {
        $arr_isi['status'] = 2;
        $checkSampleStatus = $this->mSample->getData($sampleId);
        if (!empty($checkSampleStatus) && $checkSampleStatus->status == 0) {
          $this->db->transRollback();
          $msg    = "Sample Belum Approved, tidak dapat approve Sales Order!";
          $build_array['message'] = $msg;
          $build_array['status']  = false;
          return $this->response->setJSON($build_array);
        }
      }
      $getTotalSO = $this->mSalesOrder->getTotalUkuranSO($id);
      if (!empty($getTotalSO)) {
        $arr_isi['qty'] = $getTotalSO->qty;
        $arr_isi['total_harga'] = $getTotalSO->harga_total;
      }
      $this->mSalesOrder->updateRecord($this->mSalesOrder->table, $arr_isi, 'id', $id);

      //auto update qty in WO dan PROD
      $paramsWO['ref_id'] = $id;
      $paramsWO['tipe_id'] = 2;
      $getWO = $this->mworkOrder->getData(null, null, null, null, null, $paramsWO);
      if (!empty($getWO)) {
        foreach ($getWO as $keyWO => $valueWO) {
          $allQty = $this->mSalesOrder->getTotal_qty($id, 1);
          $wo_data = [
            'ref_id' => $id,
            'id_konsumen' => $idKonsumen,
            'keterangan_style' => $style,
            'keterangan' => $deskripsi,
            'tgl_deadline' => $tglDeadline,
            'qty' => !empty($allQty) ? $allQty : 0,
            'tipe_id' => 2,
            'file_id' => !empty($fileIdSalesOrder) ? $fileIdSalesOrder : null,
            'updated_at' => date("Y-m-d H:i:s")
          ];
          $this->mworkOrder->updateRecord($this->mworkOrder->table, $wo_data, 'id', $valueWO->id);
          
          $data_warna = $this->mSalesOrder->getDataDetailSalesOrder_ori($id);
          if (!empty($sampleId) && $sampleId != 'null') {

            $params_wo['tipe_id'] = 1;
            $params_wo['ref_id']  = $sampleId;
            $ref_sample_wo = $this->mworkOrder->getData(null, 0, 1, null, null, $params_wo);
            if (!empty($ref_sample_wo)) {

              if (!empty($data_warna)) {
                foreach ($data_warna as $xrow) {

                  // Lewati colorway yang belum ada qty order-nya (opsi warna yang
                  // terdaftar di Sales Order tapi belum benar-benar dikonfirmasi/dipesan).
                  // Supaya tabel gramasi Work Order hanya menampilkan warna yang memang
                  // di-order, sama seperti fix yang sudah diterapkan pada
                  // SampleModel::syncWorkOrderFromSample().
                  $qtyColorwaySO = $this->mSalesOrder->getTotal_qty($xrow->id, 2);
                  if (empty($qtyColorwaySO) || $qtyColorwaySO <= 0) {
                    continue;
                  }

                  // get detail wo
                  $prms_sample = [];
                  $prms_sample['id_barang_1'] = $xrow->id_barang_1;
                  // $prms_sample['id_barang_2'] = $xrow->id_barang_2;
                  if (!empty($xrow->id_barang_2)) $prms_sample['id_barang_2'] = $xrow->id_barang_2;
                  if (!empty($xrow->id_barang_3)) $prms_sample['id_barang_3'] = $xrow->id_barang_3;
                  if (!empty($xrow->id_barang_4)) $prms_sample['id_barang_4'] = $xrow->id_barang_4;
                  if (!empty($xrow->id_barang_5)) $prms_sample['id_barang_5'] = $xrow->id_barang_5;
                  if (!empty($xrow->id_barang_6)) $prms_sample['id_barang_6'] = $xrow->id_barang_6;
                  if (!empty($xrow->id_barang_7)) $prms_sample['id_barang_7'] = $xrow->id_barang_7;
                  if (!empty($xrow->id_barang_8)) $prms_sample['id_barang_8'] = $xrow->id_barang_8;
                  $data_detail = $this->mSample->getDataDetailSample_ori($sampleId, $prms_sample);
                 
                  if (!empty($data_detail)) {
                    $params_wod = [];
                    $params_wod['ref_detail_id'] = $data_detail[0]->id;
                    $params_wod['tipe_id'] = 1;
                    $params_wod['single'] = true;
                    $params_wod['id_walkorder']  = $ref_sample_wo[0]->id;
                    $data_detail_wo = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $params_wod);
                    $all_sample_gram = $this->mSample->getData_gram(null, 0, 8, null, null, ['id_sample_det' => $data_detail[0]->id]);
                    // $prgram['id_sample_det'] = $data_detail[0]->id;
                    // $dtGram = $this->mSample->getData_gram(null, 0, 9999, null,  null, $prgram);
                    if (!empty($data_detail_wo)) {

                      $qty_wodet =  $this->mSalesOrder->getTotal_qty($xrow->id, 2);
                      $gram = 0;
                      $gram_nd = 0;
                      $kg = 0;
                      $loss = 0;
                      $kg_loss = 0;
                      $total = 0;
                      $kuota = 0;
                      $kuota_tambah = 0;

                      if (!empty($data_detail_wo->gram)) {
                        $gram = $data_detail_wo->gram;
                        $gram_nd = $gram * $qty_wodet;
                        $kg = $gram_nd / 1000;
                        $loss = $data_detail_wo->loss;
                        $kg_loss = ($kg * $loss) / 100;
                        $total = $kg +  $kg_loss;

                        $kuota = $data_detail_wo->kuota;
                        $kuota_tambah = $kuota - $total;
                      }

                      $detail_wo_get = [
                        'id_walkorder' => $valueWO->id,
                        'ref_detail_id' => $xrow->id,
                        'tipe_id' => 2,
                        'single' => true,
                      ];
                      
                      $getWODet = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $detail_wo_get);
                      if (empty($getWODet)) {
                        $detail_wo = [
                          'id_walkorder' => $valueWO->id,
                          'ref_detail_id' => $xrow->id,
                          'qty' => $qty_wodet,
                          'tipe_id' => 2,
                          'gram'         => $gram,
                          'gram_nd'      => $gram_nd,
                          'kg'           => $kg,
                          'loss'         => $loss,
                          'kg_loss'      => $kg_loss,
                          'total'        => $total,
                          'kuota'        => $kuota,
                          'kuota_tambah' => $kuota_tambah,
                          'created_at' => date("Y-m-d H:i:s")
                        ];

                        $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

                        $params_sample_warna = [
                          'id_walkorder_detail' => $data_detail_wo->id,
                        ];
                        $all_sample_warna = $this->mworkOrder->getData_warna(null, 0, 8, null, null, $params_sample_warna);
                        $slot_idx = 0;

                        for ($i = 0; $i < 8; $i++) {
                          $field_name = 'id_barang_' . ($i + 1);
                          if (!empty($xrow->$field_name)) {

                            // Ambil data warna Sample berdasarkan urutan slot terisi ($slot_idx),
                            // bukan indeks fisik ($i) atau hanya filter id_barang,
                            // agar slot yang memakai id_barang sama tetap memiliki gramasi independen.
                            $data_detail_row = $all_sample_warna[$slot_idx] ?? null;

                            $xgram = 0;
                            $xgram_nd = 0;
                            $xkg = 0;
                            $xloss = 0;
                            $xkg_loss = 0;
                            $xtotal = 0;
                            $xkuota = 0;
                            $xkuota_tambah = 0;
                            $xpersen = 0;

                            if(!empty($data_detail_row)) {
                              $xpersen = $data_detail_row->persen ?? 0;
                              if (!empty($data_detail_row->gram)) {
                                $xgram = $data_detail_row->gram;
                                $xgram_nd = $xgram * $qty_wodet;
                                $xkg = $xgram_nd / 1000;
                                $xloss = $data_detail_row->loss;
                                $xkg_loss = ($xkg * $xloss) / 100;
                                $xtotal = $xkg +  $xkg_loss;
      
                                $xkuota = $data_detail_row->kuota;
                                $xkuota_tambah = $xkuota - $xtotal;
                              }
                            } else {
                              $gram_sample_row = $all_sample_gram[$slot_idx] ?? null;
                              if (!empty($gram_sample_row) && !empty($gram_sample_row->gram)) {
                                $xgram = $gram_sample_row->gram;
                                $xgram_nd = $xgram * $qty_wodet;
                                $xkg = $xgram_nd / 1000;
                                $xloss = $gram_sample_row->loss;
                                $xkg_loss = ($xkg * $xloss) / 100;
                                $xtotal = $xkg +  $xkg_loss;
                              }
                            }

                            $isi_warna = [
                              'id_walkorder_detail' => $wo_det_id,
                              'id_barang' => $xrow->$field_name,
                              'persen'       => $xpersen,
                              'gram'         => $xgram,
                              'gram_nd'      => $xgram_nd,
                              'kg'           => $xkg,
                              'kg_loss'      => $xkg_loss,
                              'total'        => $xtotal,
                              'kuota'        => $xkuota,
                              'kuota_tambah' => $xkuota_tambah,
                              'loss'         => $xloss,
                              'created_at' => date("Y-m-d H:i:s")
                            ];
                            $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);

                            $slot_idx++;
                          }
                        }
                      }

                    } else {
                      $params_wod['ref_detail_id'] = $xrow->id;
                      $params_wod['tipe_id'] = 2;
                      $params_wod['single'] = true;
                      $params_wod['id_walkorder']  = $valueWO->id;

                      $data_detail_wo = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $params_wod);
                      $detail_wo = [
                        'id_walkorder' => $valueWO->id,
                        'ref_detail_id' => $xrow->id,
                        'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                        'tipe_id' => 2,
                        'created_at' => date("Y-m-d H:i:s")
                      ];
                      if(!empty($data_detail_wo) > 0) {
                        $this->mworkOrder->updateRecord($this->mworkOrder->table2, $detail_wo, 'id', $data_detail_wo->id);

                        $existing_warna_list = $this->mworkOrder->getData_warna(null, 0, 8, null, null, ['id_walkorder_detail' => $data_detail_wo->id]);
                        $slot_idx = 0;

                        for ($i = 0; $i < 8; $i++) {
                          $field_name = 'id_barang_' . ($i + 1);
                          if (!empty($xrow->$field_name)) {
                            $cur_warna = $existing_warna_list[$slot_idx] ?? null;
                            if (!empty($cur_warna)) {
                              $isi_warna = [
                                'id_walkorder_detail' => $data_detail_wo->id,
                                'id_barang' => $xrow->$field_name,
                              ];
                              $this->mworkOrder->updateRecord($this->mworkOrder->table5, $isi_warna, 'id', $cur_warna->id);
                            }
                            $slot_idx++;
                          }
                        }

                        // Sinkronkan otomatis gramasi dan total WO sesuai Qty SO terbaru
                        $this->mSalesOrder->syncWoGramasiFromSoDet($xrow->id);
                      }
                      else {
                        $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

                        for ($i = 0; $i < 8; $i++) {
                          $field_name = 'id_barang_' . ($i + 1);
                          if (!empty($xrow->$field_name)) {
                            $isi_barang = [
                              'id_walkorder_detail' => $wo_det_id,
                              'id_barang' => $xrow->$field_name,
                              'created_at' => date("Y-m-d H:i:s")
                            ];
                            $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);
                          }
                        }
                      }
                    }
                  } else {
                    $detail_wo = [
                      'id_walkorder' => $valueWO->id,
                      'ref_detail_id' => $xrow->id,
                      'tipe_id' => 2,
                    ];
                    $getWODet = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $detail_wo);
                    if (empty($getWODet)) {

                      $detail_wo = [
                        'id_walkorder' => $valueWO->id,
                        'ref_detail_id' => $xrow->id,
                        'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                        'tipe_id' => 2,
                        'created_at' => date("Y-m-d H:i:s")
                      ];

                      $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

                      for ($i = 0; $i < 8; $i++) {
                        $field_name = 'id_barang_' . ($i + 1);
                        if (!empty($xrow->$field_name)) {
                          $isi_barang = [
                            'id_walkorder_detail' => $wo_det_id,
                            'id_barang' => $xrow->$field_name,
                            'created_at' => date("Y-m-d H:i:s")
                          ];
                          $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);
                        }
                      }
                    }
                  }
                }
              }
            }
          } 
          else {
            if (!empty($data_warna)) {
              foreach ($data_warna as $xrow) {

                // Lewati colorway yang belum ada qty order-nya (lihat catatan di atas).
                $qtyColorwaySO = $this->mSalesOrder->getTotal_qty($xrow->id, 2);
                if (empty($qtyColorwaySO) || $qtyColorwaySO <= 0) {
                  continue;
                }

                $detail_wo = [
                  'id_walkorder' => $valueWO->id,
                  'ref_detail_id' => $xrow->id,
                  'tipe_id' => 2,
                ];

                $getWODet = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $detail_wo);
                if (empty($getWODet)) {
                  $detail_wo = [
                    'id_walkorder' => $valueWO->id,
                    'ref_detail_id' => $xrow->id,
                    'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                    'tipe_id' => 2,
                    'created_at' => date("Y-m-d H:i:s")
                  ];

                  $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

                  for ($i = 0; $i < 8; $i++) {
                    $field_name = 'id_barang_' . ($i + 1);
                    if (!empty($xrow->$field_name)) {
                      $isi_barang = [
                        'id_walkorder_detail' => $wo_det_id,
                        'id_barang' => $xrow->$field_name,
                        'created_at' => date("Y-m-d H:i:s")
                      ];
                      $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);
                    }
                  }
                }
              }
            }
          }
          $paramsPD['id_walkorder'] = $valueWO->id;
          $paramsPD['tipe_id'] = 2;
          $getPD = $this->mProduksi->getData(null, null, null, null, null, $paramsPD);
          if (!empty($getPD)) {
            foreach ($getPD as $keyPD => $valuePD) {
              $wo_data = [
                'id_konsumen' => $idKonsumen,
                'tgl_deadline' => $tglDeadline,
                'keterangan_style' => $style,
                'keterangan' => $deskripsi,
                'qty' => !empty($allQty) ? $allQty : 0,
                'tipe_id' => 2,
                'file_id' => !empty($fileIdSalesOrder) ? $fileIdSalesOrder : null,
                'updated_at' => date("Y-m-d H:i:s")
              ];
              $arr_pd_update['qty'] = !empty($allQty) ? $allQty : 0;
              $this->mProduksi->updateRecord($this->mProduksi->table, $arr_pd_update, 'id', $valuePD->id);
            }
          }
        }
      }
      
      if (!empty($submit_data)) {
        $allQty = $this->mSalesOrder->getTotal_qty($id, 1);
        $wo_data = [
          'kode_walkorder' => $this->mworkOrder->generete_kode(),
          'ref_id' => $id,
          'ref_kode' => $noSalesOrder,
          'id_konsumen' => $idKonsumen,
          'keterangan_style' => $style,
          'keterangan' => $deskripsi,
          'tgl_deadline' => $tglDeadline,
          // 'tgl_deadline_dua' => $tglDeadlineDua,
          'tgl_transaksi' => date("Y-m-d"),
          // 'id_style' => $id,
          'qty' => !empty($allQty) ? $allQty : 0,
          'tipe_id' => 2,
          'file_id' => !empty($fileIdSalesOrder) ? $fileIdSalesOrder : null,
          'status' => 1,
          'created_at' => date("Y-m-d H:i:s")
        ];

        $wo_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table, $wo_data);

        // detail data 
        $data_warna = $this->mSalesOrder->getDataDetailSalesOrder_ori($id);
        // print_r($data_warna);
        // exit;
        if (!empty($sampleId) && $sampleId != 'null') {

          $params_wo['tipe_id'] = 1;
          $params_wo['ref_id']  = $sampleId;
          $ref_sample_wo = $this->mworkOrder->getData(null, 0, 1, null, null, $params_wo);
          
          if (!empty($ref_sample_wo)) {

            // input proses 
            $params_wo['id_walkorder'] = $ref_sample_wo[0]->id;
            $proces_wo = $this->mworkOrder->getData_proses(0, 0, 9999, null, null, $params_wo);
            if (!empty($proces_wo)) {
              foreach ($proces_wo as $pro) {
                $getPrefWoProses = $this->mworkOrder->getDataDetailWOApproveSO($style, $pro->id_proses, $wo_id, 2);
                $isiProses = [
                  'id_walkorder' => $wo_id,
                  'id_proses' => $pro->id_proses,
                  'harga' => !empty($getPrefWoProses) ? $getPrefWoProses->harga : 0,
                  'created_at' => date('Y-m-d H:i:s')
                ];

                $proses_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table3, $isiProses);
              }
            }
            
            if (!empty($data_warna)) {
              foreach ($data_warna as $xrow) {

                // Lewati colorway yang belum ada qty order-nya (lihat catatan di atas).
                $qtyColorwaySO = $this->mSalesOrder->getTotal_qty($xrow->id, 2);
                if (empty($qtyColorwaySO) || $qtyColorwaySO <= 0) {
                  continue;
                }

                // get detail wo
                $prms_sample = [];
                $prms_sample['id_barang_1'] = $xrow->id_barang_1;
                // $prms_sample['id_barang_2'] = $xrow->id_barang_2;
                if (!empty($xrow->id_barang_2)) $prms_sample['id_barang_2'] = $xrow->id_barang_2;
                if (!empty($xrow->id_barang_3)) $prms_sample['id_barang_3'] = $xrow->id_barang_3;
                if (!empty($xrow->id_barang_4)) $prms_sample['id_barang_4'] = $xrow->id_barang_4;
                if (!empty($xrow->id_barang_5)) $prms_sample['id_barang_5'] = $xrow->id_barang_5;
                if (!empty($xrow->id_barang_6)) $prms_sample['id_barang_6'] = $xrow->id_barang_6;
                if (!empty($xrow->id_barang_7)) $prms_sample['id_barang_7'] = $xrow->id_barang_7;
                if (!empty($xrow->id_barang_8)) $prms_sample['id_barang_8'] = $xrow->id_barang_8;
                $data_detail = $this->mSample->getDataDetailSample_ori($sampleId, $prms_sample);
                
                if (!empty($data_detail)) {
                  $params_wod = [];
                  $params_wod['ref_detail_id'] = $data_detail[0]->id;
                  $params_wod['tipe_id'] = 1;
                  $params_wod['id_walkorder']  = $ref_sample_wo[0]->id;
                  $data_detail_wo = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $params_wod);
                  $all_sample_gram = $this->mSample->getData_gram(null, 0, 8, null, null, ['id_sample_det' => $data_detail[0]->id]);
                  
                  // $prgram['id_sample_det'] = $data_detail[0]->id;
                  // $dtGram = $this->mSample->getData_gram(null, 0, 9999, null,  null, $prgram);

                  $qty_wodet =  $this->mSalesOrder->getTotal_qty($xrow->id, 2);
                  if (!empty($data_detail_wo)) {


                    $gram = 0;
                    $gram_nd = 0;
                    $kg = 0;
                    $loss = 0;
                    $kg_loss = 0;
                    $total = 0;
                    $kuota = 0;
                    $kuota_tambah = 0;

                    if (!empty($data_detail_wo[0]->gram)) {
                      $gram = $data_detail_wo[0]->gram;
                      $gram_nd = $gram * $qty_wodet;
                      $kg = $gram_nd / 1000;
                      $loss = $data_detail_wo[0]->loss;
                      $kg_loss = ($kg * $loss) / 100;
                      $total = $kg +  $kg_loss;

                      $kuota = $data_detail_wo[0]->kuota;
                      $kuota_tambah = $kuota - $total;
                    }

                    $detail_wo = [
                      'id_walkorder' => $wo_id,
                      'ref_detail_id' => $xrow->id,
                      'qty' => $qty_wodet,
                      'tipe_id' => 2,
                      'gram'         => $gram,
                      'gram_nd'      => $gram_nd,
                      'kg'           => $kg,
                      'loss'         => $loss,
                      'kg_loss'      => $kg_loss,
                      'total'        => $total,
                      'kuota'        => $kuota,
                      'kuota_tambah' => $kuota_tambah,
                      'created_at' => date("Y-m-d H:i:s")
                    ];

                    $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

                    $params_sample_warna = [
                      'id_walkorder_detail' => $data_detail_wo[0]->id,
                    ];
                    $all_sample_warna = $this->mworkOrder->getData_warna(null, 0, 8, null, null, $params_sample_warna);
                    $slot_idx = 0;

                    for ($i = 0; $i < 8; $i++) {
                      $field_name = 'id_barang_' . ($i + 1);
                      if (!empty($xrow->$field_name)) {

                        // Ambil data warna Sample berdasarkan urutan slot terisi ($slot_idx),
                        // bukan indeks fisik ($i) atau hanya filter id_barang,
                        // agar slot yang memakai id_barang sama tetap memiliki gramasi independen.
                        $data_detail_row = $all_sample_warna[$slot_idx] ?? null;
                        
                        $xgram = 0;
                        $xgram_nd = 0;
                        $xkg = 0;
                        $xloss = 0;
                        $xkg_loss = 0;
                        $xtotal = 0;
                        $xkuota = 0;
                        $xkuota_tambah = 0;
                        $xpersen = 0;
                        
                        if(!empty($data_detail_row)) {
                          $xpersen = $data_detail_row->persen ?? 0;
                          if (!empty($data_detail_row->gram)) {
                            $xgram = $data_detail_row->gram;
                            $xgram_nd = $xgram * $qty_wodet;
                            $xkg = $xgram_nd / 1000;
                            $xloss = $data_detail_row->loss;
                            $xkg_loss = ($xkg * $xloss) / 100;
                            $xtotal = $xkg +  $xkg_loss;
  
                            $xkuota = $data_detail_row->kuota;
                            $xkuota_tambah = $xkuota - $xtotal;
                          }
                        } else {
                          $gram_sample_row = $all_sample_gram[$slot_idx] ?? null;
                          if (!empty($gram_sample_row) && !empty($gram_sample_row->gram)) {
                            $xgram = $gram_sample_row->gram;
                            $xgram_nd = $xgram * $qty_wodet;
                            $xkg = $xgram_nd / 1000;
                            $xloss = $gram_sample_row->loss;
                            $xkg_loss = ($xkg * $xloss) / 100;
                            $xtotal = $xkg +  $xkg_loss;
                          }
                        }

                        $isi_barang = [
                          'id_walkorder_detail' => $wo_det_id,
                          'id_barang' => $xrow->$field_name,
                          'persen'       => $xpersen,
                          'gram'         => $xgram,
                          'gram_nd'      => $xgram_nd,
                          'kg'           => $xkg,
                          'kg_loss'      => $xkg_loss,
                          'total'        => $xtotal,
                          'kuota'        => $xkuota,
                          'kuota_tambah' => $xkuota_tambah,
                          'loss'         => $xloss,
                          'created_at' => date("Y-m-d H:i:s")
                        ];

                        $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);

                        $slot_idx++;
                      }
                    }
                  } else {
                    $detail_wo = [
                      'id_walkorder' => $wo_id,
                      'ref_detail_id' => $xrow->id,
                      'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                      'tipe_id' => 2,
                      'created_at' => date("Y-m-d H:i:s")
                    ];

                    $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);
                    $slot_idx = 0;

                    for ($i = 0; $i < 8; $i++) {

                      $xgram = 0;
                      $xgram_nd = 0;
                      $xkg = 0;
                      $xloss = 0;
                      $xkg_loss = 0;
                      $xtotal = 0;
                      $xkuota = 0;
                      $xkuota_tambah = 0;

                      $field_name = 'id_barang_' . ($i + 1);
                      if (!empty($xrow->$field_name)) {
                        $gram_sample_row = $all_sample_gram[$slot_idx] ?? null;
                        if (!empty($gram_sample_row) && !empty($gram_sample_row->gram)) {
                          $xgram = $gram_sample_row->gram;
                          $xgram_nd = $xgram * $qty_wodet;
                          $xkg = $xgram_nd / 1000;
                          $xloss = $gram_sample_row->loss;
                          $xkg_loss = ($xkg * $xloss) / 100;
                          $xtotal = $xkg +  $xkg_loss;
                        }

                        $isi_barang = [
                          'id_walkorder_detail' => $wo_det_id,
                          'id_barang' => $xrow->$field_name,
                          'gram'         => $xgram,
                          'gram_nd'      => $xgram_nd,
                          'kg'           => $xkg,
                          'kg_loss'      => $xkg_loss,
                          'total'        => $xtotal,
                          'kuota'        => $xkuota,
                          'kuota_tambah' => $xkuota_tambah,
                          'loss'         => $xloss,
                          'created_at' => date("Y-m-d H:i:s")
                        ];
                        $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);

                        $slot_idx++;
                      }
                    }
                  }
                } else {
                  $detail_wo = [
                    'id_walkorder' => $wo_id,
                    'ref_detail_id' => $xrow->id,
                    'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                    'tipe_id' => 2,
                    'created_at' => date("Y-m-d H:i:s")
                  ];

                  $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

                  for ($i = 0; $i < 8; $i++) {
                    $field_name = 'id_barang_' . ($i + 1);
                    if (!empty($xrow->$field_name)) {
                      $isi_barang = [
                        'id_walkorder_detail' => $wo_det_id,
                        'id_barang' => $xrow->$field_name,
                        'created_at' => date("Y-m-d H:i:s")
                      ];
                      $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);
                    }
                  }
                }
              }
            }
          }
        } else {
          if (!empty($data_warna)) {
            foreach ($data_warna as $xrow) {

              // Lewati colorway yang belum ada qty order-nya (lihat catatan di atas).
              $qtyColorwaySO = $this->mSalesOrder->getTotal_qty($xrow->id, 2);
              if (empty($qtyColorwaySO) || $qtyColorwaySO <= 0) {
                continue;
              }

              $detail_wo = [
                'id_walkorder' => $wo_id,
                'ref_detail_id' => $xrow->id,
                'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                'tipe_id' => 2,
                'created_at' => date("Y-m-d H:i:s")
              ];

              $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

              for ($i = 0; $i < 8; $i++) {
                $field_name = 'id_barang_' . ($i + 1);
                if (!empty($xrow->$field_name)) {
                  $isi_barang = [
                    'id_walkorder_detail' => $wo_det_id,
                    'id_barang' => $xrow->$field_name,
                    'created_at' => date("Y-m-d H:i:s")
                  ];
                  $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_barang);
                }
              }
            }
          }
        }
      }
    }

    if ($this->db->transStatus() === FALSE) {
      $this->db->transRollback();
    } else {
      $this->db->transCommit();
      $msg    = "Data berhasil ditambahkan !";
      $status = true;
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  function saveDetail()
  {
    if (!$this->auth->loggedIn() || !$this->_edit) {
      $build_array['message'] = 'Anda tidak memiliki akses untuk edit data!';
      $build_array['status']  = false;
      return $this->response->setJSON($build_array);
    }
    $msg    = "Data gagal disimpan !";
    $status = false;
    $idSalesOrder = $this->request->getPost('idSalesOrder');
    $idSalesOrderDet = $this->request->getPost('idSalesOrderDet');
    $barang1 = $this->request->getPost('barang1');
    $barang2 = $this->request->getPost('barang2');
    $barang3 = $this->request->getPost('barang3');
    $barang4 = $this->request->getPost('barang4');
    $barang5 = $this->request->getPost('barang5');
    $barang6 = $this->request->getPost('barang6');
    $barang7 = $this->request->getPost('barang7');
    $barang8 = $this->request->getPost('barang8');
    $dataUkuran = $this->request->getPost('dataUkuran');
    $targetSoId = (int)decrypt($idSalesOrder);
    $detExists = false;
    if (!empty($idSalesOrderDet)) {
      $checkDet = \Config\Database::connect()->table('trans_sales_order_det')
        ->where('id', (int)$idSalesOrderDet)
        ->where('id_sales_order', $targetSoId)
        ->get()->getRow();
      if (!empty($checkDet)) {
        $detExists = true;
      }
    }

    $dataWarna = [
      "id_barang_1" => !empty($barang1) ? $barang1 : null,
      "id_barang_2" => !empty($barang2) ? $barang2 : null,
      "id_barang_3" => !empty($barang3) ? $barang3 : null,
      "id_barang_4" => !empty($barang4) ? $barang4 : null,
      "id_barang_5" => !empty($barang5) ? $barang5 : null,
      "id_barang_6" => !empty($barang6) ? $barang6 : null,
      "id_barang_7" => !empty($barang7) ? $barang7 : null,
      "id_barang_8" => !empty($barang8) ? $barang8 : null,

      "id_sales_order" => $targetSoId,
      "id" => $detExists ? (int)$idSalesOrderDet : null,
    ];

    $res = $this->mSalesOrder->trxInsertUpdateRecord($dataWarna, $dataUkuran);
    if ($res === true) {
      $status = true;
      $msg = "Data berhasil disimpan!";
    }
    else  {
      $msg = $res;
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  public function deleteList($id = NULL)
  {
    if (!$this->auth->loggedIn() or !$this->_delete) {
      $this->session->setFlashdata('err', 'Anda tidak memiliki akses untuk menghapus data.');
      return redirect()->to($this->urlv);
    }
    if ($id != null && $id != "") {
      $id = decrypt($id);
    }

    $id = (int)$id;
    $msg    = "Data gagal dihapus !";
    $status = false;
    $this->db->transBegin();
    $res = $this->mSalesOrder->deleteRecord($this->mSalesOrder->table, 'id', $id);

    $arrDelete['id_sales_order'] = $id;
    $this->mSalesOrder->deleteRecordMultipleColumn("trans_sales_order_det", $arrDelete);
    $this->mSalesOrder->deleteRecordMultipleColumn("trans_sales_order_ukuran", $arrDelete);
    if ($this->db->transStatus() === FALSE) {
      $this->session->setFlashdata('err', $this->db->error()['message']);
      $this->db->transRollback();
    } else {
      $this->db->transCommit();
      if ($res) {
        $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "SalesOrder Dihapus");
        $this->session->setFlashdata('message', "SalesOrder berhasil dihapus");
      } else {
        $this->session->setFlashdata('err', "SalesOrder gagal dihapus");
      }
    }
    return redirect()->to($this->urlv);
  }
  public function deleteDetailList($id = NULL)
  {
    if (!$this->auth->loggedIn() or (!$this->_delete)) {
      throw new \Exception('You must be an administrator to view this page.');
    }

    if ($id != null && $id != "") {
      // $id = decrypt($id);
    }

    $id = (int)$id;
    $msg    = "Data gagal dihapus !";
    $status = false;
    $res = $this->mSalesOrder->deleteRecord("trans_sales_order_det", 'id', $id);
    $resDel = $this->mSalesOrder->deleteRecord("trans_sales_order_ukuran", 'id_sales_order_det', $id);

    // Hapus juga trans_walkorder_detail & trans_walkorder_warna terkait jika ada
    $woDets = $this->mSalesOrder->db->table('trans_walkorder_detail')
      ->where('ref_detail_id', $id)
      ->where('tipe_id', 2)
      ->get()->getResult();
    foreach ($woDets as $wd) {
      $this->mSalesOrder->db->table('trans_walkorder_warna')->where('id_walkorder_detail', $wd->id)->delete();
      $this->mSalesOrder->db->table('trans_walkorder_detail')->where('id', $wd->id)->delete();
      $totalWoQtyRow = $this->mSalesOrder->db->table('trans_walkorder_detail')->selectSum('qty')->where('id_walkorder', $wd->id_walkorder)->get()->getRow();
      $this->mSalesOrder->db->table('trans_walkorder')->where('id', $wd->id_walkorder)->update([
        'qty' => !empty($totalWoQtyRow->qty) ? (float)$totalWoQtyRow->qty : 0,
        'updated_at' => date('Y-m-d H:i:s')
      ]);
    }

    if ($res || $resDel) {
      $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "SalesOrder Detail Dihapus");
      $status = true;
      $msg = "Data berhasil dihapus!";
    }
    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  public function getSampleBuyer()
  {
    $buyerId = $this->request->getPost("buyers");

    $msg = "Gagal mengambil data sample !";
    $status = false;
    $data = [];
    $cnt = 0;

    if (!empty($buyerId)) {
      $params = [
        'id_konsumen' => $buyerId,
        'activedt' => 1
      ];
      $data_sample = $this->mSample->getData(0, 0, 99999, null, null, $params);
      if (!empty($data_sample)) {
        $msg = "Berhasil mengambil data sample !";
        $status = true;

        for ($i=0; $i < count($data_sample) ; $i++) { 
          $r = $data_sample[$i];
          $prx['style'] = $r->style;
          $prx['id_konsumen'] = $buyerId;
          $cntx = $this->mSalesOrder->getDataCnt(null, $prx);
          $data_sample[$i]->style_cnt_order = !empty($cntx) ? ($cntx + 1) : 1;
        }

        $data = $data_sample;

        
      }
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;
    $build_array['data']    = $data;
    $build_array['cnt']    = $cnt;
    return $this->response->setJSON($build_array);
  }

  public function generateQRCode()
  {

    $data = $this->request->getPost('data');
    if (empty($data)) {
      return $this->response->setStatusCode(400)->setBody("QR Code Failed Generated");
    }
    $data = json_decode((string)$data);
    $resData = []; //$this->mSalesOrder->getDataDetailSalesOrderUkuranById($data->id);

    try {

      $writer = new PngWriter();
      $qrCode = new QrCode(
        data: encrypt($data->id),
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::Low,
        size: 300,
        margin: 10,
        roundBlockSizeMode: RoundBlockSizeMode::Margin,
        foregroundColor: new Color(0, 0, 0),
        backgroundColor: new Color(255, 255, 255)
      );

      $result = $writer->write($qrCode);
      // $writer->validateResult($result, 'QRcode Failed Generated');
      $base64QrCode = base64_encode($result->getString());
      $build_array['file_base64'] = $base64QrCode;
      $build_array['ext']  = 'png';
      $build_array['file_name']  = $resData->warna1 . "_" . $data->ukuran . "_" . $data->harga_satuan;
      $build_array['status']  = true;
      $build_array['message'] = "QR Code Behasil digenerate";
      return $this->response->setJSON($build_array);
    } catch (\Exception $e) {
      $build_array['status']  = false;
      $build_array['message'] = "QR Code Gagal digenerate";
      return $this->response->setJSON($build_array);
    }


    // header('Content-Type: ' . $result->getMimeType());
    // header('Content-Disposition: attachment; filename="qrcode.png"');
    // echo $result->getString();
  }

  function getQrcode()
  {
    
    $ukuran = $this->request->getGet("ukuran");
    $qty = $this->request->getGet("qty");
    $qtyp = $this->request->getGet("qtyp");
    $noSample = $this->request->getGet("noSample");
    $deskripsi = $this->request->getGet("deskripsi");
    $buyer = $this->request->getGet("buyer");
    $warna = $this->request->getGet("warna");
    $trans = $this->request->getGet("trans");
    $style = $this->request->getGet("style");

    // $trans = decrypt($trans);
    /* Data */
    // $hex_data   = bin2hex($id);
    // $save_name  = $hex_data. '_'. time() . '.png';
    $warnaNew = str_replace('/', '_', $warna);
    $save_name  = $warnaNew . '-' . $noSample .'-'. time() . '.png';

    // $pr_warna['key_ukuran'] = $ukuran;
    $pr_warna['id_sales_order_det'] = $trans;
    $data_warna = $this->mSalesOrder->getDataDetailSalesOrderUkuranById($pr_warna);
    
    /* QR Code File Directory Initialize */
    $dir = 'uploads/media/qrcode/';
    if (!file_exists($dir)) {
      mkdir($dir, 0775, true);
    }

    /* QR Configuration  */
    $config['cacheable']    = true;
    $config['imagedir']     = $dir;
    $config['quality']      = true;
    $config['size']         = '1024';
    $config['black']        = [255, 255, 255];
    $config['white']        = [255, 255, 255];
    // Lazy-init: property ini terdeklarasi langsung di BaseController (bukan undefined),
    // jadi magic __get() di BaseController tidak pernah terpanggil dan nilainya tetap null.
    if ($this->ciqrcode === null) {
      $this->ciqrcode = new \App\Libraries\CIQRCode();
    }
    $this->ciqrcode->initialize($config);

    $data = [
      'ukuran' => $ukuran,
      'qty' => $qty,
      'qtyp' => $qtyp,
      'noSample' => $noSample,
      'deskripsi' => $deskripsi,
      'buyer' => $buyer,
      'warna' => $warna,
      'style' => $style,
      'warna_2' => '',
      'warna_3' => '',
      'warna_4' => '',
      'warna_5' => '',
      'warna_6' => '',
      'warna_7' => '',
      'warna_8' => '',
    ];

    if (!empty($data_warna)) {
      $data['warna_2'] = !empty($data_warna[0]->warna_2) ? trim($data_warna[0]->warna_2) : '-';
      $data['warna_3'] = !empty($data_warna[0]->warna_3) ? trim($data_warna[0]->warna_3) : '-';
      $data['warna_4'] = !empty($data_warna[0]->warna_4) ? trim($data_warna[0]->warna_4) : '-';
      $data['warna_5'] = !empty($data_warna[0]->warna_5) ? trim($data_warna[0]->warna_5) : '-';
      $data['warna_6'] = !empty($data_warna[0]->warna_6) ? trim($data_warna[0]->warna_6) : '-';
      $data['warna_7'] = !empty($data_warna[0]->warna_7) ? trim($data_warna[0]->warna_7) : '-';
      $data['warna_8'] = !empty($data_warna[0]->warna_8) ? trim($data_warna[0]->warna_8) : '-';
      $params['data']     = $noSample . ';' . $ukuran . ';' . $warna . ';' . $qty . ';' . $data['warna_2'] . ';' . $data['warna_3'] . ';' . $data['warna_4'] . ';' . $data['warna_5'] . ';' . $data['warna_6'] . ';' . $data['warna_7'] . ';' . $data['warna_8']; //json_encode($data) ;//base_url() . "/produk/edit/" . encrypt($id);
    }else{
      $params['data']     = $noSample . ';' . $ukuran . ';' . $warna . ';' . $qty; //json_encode($data) ;//base_url() . "/produk/edit/" . encrypt($id);
    }
    /* QR Data  */
    $params['level']    = 'L';
    $params['size']     = 10;
    $params['savename'] = FCPATH . $config['imagedir'] . $save_name;

    $oks = $this->ciqrcode->generate($params);

    /* Return Data */
    $url = base_url() . "/uploads/media/qrcode/" . $save_name;

    $this->data["data"] = $data;
    $this->data["fileName"] = $save_name;
    return view($this->views . '\vprint_qrcode', $this->data);
  }

  public function print($id = null)
  {
    if (!$this->auth->loggedIn() || !$this->_print) {
      $this->session->setFlashdata('err', 'Anda tidak memiliki akses untuk print data.');
      return redirect()->to($this->urlv);
    }
    $dompdf = new \Dompdf\Dompdf();
    // Set Dompdf options for portrait orientation
    $dompdf->setPaper('A4', 'portrait');

    $this->data['data'] = [];
    if ($id != "") {
      $id = decrypt($id);
      // die;
      $resData = $this->mSalesOrder->getData($id);
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $id;
      $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);

      $resDataDetail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($id) : [];
      $keysUkuran = !empty($resDataDetail) ? array_keys(get_object_vars($resDataDetail[0])) : [];

      $excludeKeys = ["id", "no", "colordasar", "keterangan", "colour", "total_harga", "total_satuan", "sumber_warna"];

      $ukuranKeysInc = array_diff($keysUkuran, $excludeKeys);

      $ukuranKeysInc = preg_grep('/^harga_satuan_/', $ukuranKeysInc, PREG_GREP_INVERT);

      $ukuranKeysInc = array_values($ukuranKeysInc);

      $sizes = ['xs', 's', 'sm', 'ml', 'm', 'l', 'lxl', 'xl', 'xxl', 'xxxl', 'xxxxl', 'xxxxxl', 'jumbo', 'all', 'xxxxxxl', 'xxxxxxxl', 'xxxxxxxxl', 'xxxxxxxxxl', 'xxxxxxxxxxl'];

      foreach ($resDataDetail as $row) {
          $priceGroups = [];
          foreach ($sizes as $size) {
              $priceField = "harga_satuan_" . $size;
              $priceValue = isset($row->$priceField) ? (int)$row->$priceField : 0;
              if ($priceValue > 0) {
                  $priceGroups[$priceValue][] = $size;
              }
          }

          foreach ($priceGroups as $price => $groupedSizes) {
              $newRow = clone $row;
              $newRow->total_satuan = (string) $price;
              $currentTotalHarga = 0;

              foreach ($sizes as $size) {
                  if (in_array($size, $groupedSizes)) {
                      if ($size == 'all') {
                        $size = 'all_';
                      }
                      $qty = (int) $row->$size;
                      $currentTotalHarga += ($qty * $price);
                  } else {
                    if ($size == 'all') {
                        $size = 'all_';
                      }
                      $newRow->$size = "0";
                      $pField = "harga_satuan_" . $size;
                      if(isset($newRow->$pField)) unset($newRow->$pField);
                  }
              }
              $newRow->total_harga = (string) $currentTotalHarga;
              $tempData[] = $newRow;
          }
      }

      usort($tempData, function($a, $b) {
          return (int)$a->total_satuan <=> (int)$b->total_satuan;
      });

      $finalData = [];
      $lastPrice = null;

      foreach ($tempData as $index => $item) {
          if ($lastPrice !== null && $item->total_satuan !== $lastPrice) {
              $separator = new \stdClass();
              foreach ($item as $key => $val) {
                  $separator->$key = null; 
              }
              $separator->is_separator = true; 
              
              $finalData[] = $separator;
          }

          $finalData[] = $item;
          $lastPrice = $item->total_satuan;
      }

      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['ukuran'] = !empty($ukuranKeysInc) ? $ukuranKeysInc : [];
      $this->data['detail'] = !empty($resDataDetail) ? $finalData : [];
    }
    
    $html = view($this->views . '\sales_order_print', $this->data);


    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
    exit;
  }

  public function print_excel_lists($from_date, $to_date, $buyer){
        if (!$this->auth->loggedIn() || !$this->_print) {
          $this->session->setFlashdata('err', 'Anda tidak memiliki akses untuk print data.');
          return redirect()->to($this->urlv);
        }
        $fileName = "SO-List.xlsx";

        if ($buyer == 'all') {
          $buyer = null;
        }

        $id = $this->request->getGet('data_id');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mSalesOrder->get_export($tanggal_sql_from, $tanggal_sql_to, $buyer);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Sales Order (APPROVED ONLY) '. $title)

               ->setCellValue('A4', 'SO NO')
               ->setCellValue('B4', 'TANGGAL SO')
               ->setCellValue('C4', 'TANGGAL DEADLINE')
               ->setCellValue('D4', 'BUYER')
               ->setCellValue('E4', 'STYLE')
               ->setCellValue('F4', 'DESKRIPSI')
               ->setCellValue('G4', 'NOMOR INVOICE')
               ->setCellValue('H4', 'QTY')
               ->setCellValue('I4', 'PROSES PRODUKSI')
               ->setCellValue('J4', 'HASIL PRODUKSI')
               ->setCellValue('K4', 'QTY DO')
               ->setCellValue('L4', 'NILAI SO')
               ->setCellValue('M4', 'NILAI DP')
               ->setCellValue('N4', 'NILAI INVOICE')
               ->setCellValue('O4', 'SISA TAGIHAN')
               ->setCellValue('P4', 'PEMBAYARAN')
               ->setCellValue('Q4', 'SISA PEMBAYARAN');

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
        $gets->getStyle('A4:Q4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:Q3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:Q2');
        $sheets->getActiveSheet()->mergeCells('A2:Q2');
        // $sheets->getActiveSheet()->mergeCells('A4:Q4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
        $gets->getColumnDimension('A')->setWidth(20);
        $gets->getColumnDimension('B')->setWidth(20);
        $gets->getColumnDimension('C')->setWidth(20);
        $gets->getColumnDimension('D')->setWidth(35);
        $gets->getColumnDimension('E')->setWidth(35);
        $gets->getColumnDimension('F')->setWidth(40);
        $gets->getColumnDimension('G')->setWidth(30);
        $gets->getColumnDimension('H')->setWidth(20);
        $gets->getColumnDimension('I')->setWidth(20);
        $gets->getColumnDimension('J')->setWidth(20);
        $gets->getColumnDimension('K')->setWidth(20);
        $gets->getColumnDimension('L')->setWidth(35);
        $gets->getColumnDimension('M')->setWidth(35);
        $gets->getColumnDimension('N')->setWidth(35);
        $gets->getColumnDimension('O')->setWidth(35);
        $gets->getColumnDimension('P')->setWidth(35);
        $gets->getColumnDimension('Q')->setWidth(35);

        $gets->getStyle('A4:Q4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets->getStyle('A4:Q4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I', 'J','K', 'L', 'M', 'N', 'O', 'P', 'Q'
        );

        for ($i=0; $i < 17 ; $i++) { 

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

        $total_qty = 0;
        $total_so = 0;
        $total_dp = 0;
        $total_invoice = 0;
        $total_sisa = 0;
        $total_pembayaran = 0;
        $total_sisa_pembayaran = 0;
        $total_qty_prod = 0;
        $total_qty_hasil = 0;
        $total_qty_do = 0;

        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $qty = !empty($r->qty) ? $r->qty : 0;
            $qty_prod = !empty($r->qty_prod) ? $r->qty_prod : 0;
            $qty_do = !empty($r->qty_do) ? $r->qty_do : 0;
            $nilai_so = !empty($r->harga_total) ? $r->harga_total : 0;
            $nilai_dp = !empty($r->uang_dp) ? $r->uang_dp : 0;
            $nilai_dp_2 = !empty($r->uang_dp_2) ? $r->uang_dp_2 : 0;
            $nilai_invoice = !empty($r->nilai_invoice) ? $r->nilai_invoice : 0;
            $pembayaran = !empty($r->pembayaran) ? $r->pembayaran : 0;
            $sisa = $nilai_so - ($nilai_dp + $nilai_invoice); 
            $sisa_pembayaran = $nilai_so - ($nilai_dp + $nilai_dp_2 + $pembayaran);

            $total_qty += $qty; 
            $total_qty_prod += $qty_prod; 
            $total_qty_do += $qty_do; 
            $total_so += $nilai_so; 
            $total_dp += $nilai_dp ; 
            $total_invoice += $nilai_invoice; 
            $total_sisa += $sisa;
            $total_pembayaran += $pembayaran;
            $total_sisa_pembayaran += $sisa_pembayaran;

            $parms['last_proses'] = 1;
            $parms['id_walkorder'] = $r->id_walkorder_prod;
            if (!empty($r->id_walkorder_prod)) {
                $dataLast = $this->mProduksi->getDataProsesProd($parms);
                $last_data = !empty($dataLast) ? $dataLast[0] : [];
    
                $qty_hasil = 0;
                if (!empty($last_data)) {
                    // $qty_hasil = $last_data->qty_prod - $qty_kirim;
                    $qty_hasil = $last_data->qty_prod;
                }
                
            }
            else {
                $qty_hasil = 0;
            }

            !empty($qty_hasil) ? $total_qty_hasil += $qty_hasil : $total_qty_hasil += 0; 

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->tgl_deadline) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_deadline)))) : "-")
                    ->setCellValue('D'.$ix, !empty($r->nama) ? $r->nama : '-')
                    ->setCellValue('E'.$ix, !empty($r->style) ? $r->style : '-')
                    ->setCellValue('F'.$ix, !empty($r->deskripsi) ? $r->deskripsi : '-')
                    ->setCellValue('G'.$ix, !empty($r->kode_invoice) ? $r->kode_invoice : '-')
                    ->setCellValue('H'.$ix, $qty)
                    ->setCellValue('I'.$ix, $qty_prod)
                    ->setCellValue('J'.$ix, $qty_hasil)
                    ->setCellValue('K'.$ix, $qty_do)
                    ->setCellValue('L'.$ix, $nilai_so)
                    ->setCellValue('M'.$ix, $nilai_dp + $nilai_dp_2)
                    ->setCellValue('N'.$ix, $nilai_invoice)
                    ->setCellValue('O'.$ix, $sisa)
                    ->setCellValue('P'.$ix, $pembayaran)
                    ->setCellValue('Q'.$ix, $sisa_pembayaran);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':Q'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("L" . $ix .":Q" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

          $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':G'. $length);
        
        $gets->getStyle('A'.$length.':Q'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('H' . $length, '=SUM(H' . $startRow . ':H' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('I' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('J' . $length, '=SUM(J' . $startRow . ':J' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('K' . $length, '=SUM(K' . $startRow . ':K' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('L' . $length, '=SUM(L' . $startRow . ':L' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('M' . $length, '=SUM(M' . $startRow . ':M' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('N' . $length, '=SUM(N' . $startRow . ':N' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('O' . $length, '=SUM(O' . $startRow . ':O' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('P' . $length, '=SUM(P' . $startRow . ':P' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('Q' . $length, '=SUM(Q' . $startRow . ':Q' . $length-1 . ')');

        $gets->getStyle('L'. $length .':Q'. $length)->getNumberFormat()
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

    public function set_status()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        $idEnc = $this->request->getPost('id');
        $newStatus = (int)$this->request->getPost('status');
        $id = decrypt($idEnc);
        if (empty($id)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID Sales Order tidak valid']);
        }

        $so = $this->mSalesOrder->getData($id);
        if (!$so) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data Sales Order tidak ditemukan']);
        }

        if ((int)$so->status === 1 && $newStatus === 3) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sales Order Draft tidak dapat langsung di-Close. Mohon Approve terlebih dahulu.']);
        }

        $this->mSalesOrder->updateRecord($this->mSalesOrder->table, ['status' => $newStatus], 'id', $id);
        $statusLabel = $newStatus === 3 ? 'Closed' : 'Approved (Open)';
        $this->mcommon->setLog($this->auth->getUserId(), $this->MOD_ALIAS, $id, "Update Status SO ke {$statusLabel}");

        return $this->response->setJSON([
            'status' => true,
            'message' => "Sales Order {$so->kode_sales_order} berhasil diubah status menjadi {$statusLabel}."
        ]);
    }
}
