<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;

use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\UkuranModel;
use Modules\Referensi\Models\WarnaModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Transaction\Models\WalkorderModel;
use App\Models\FileModel;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

class SalesOrder extends BaseController
{
  protected $mSalesOrder;
  protected $mSample;
  protected $mkonsumen;
  protected $files;
  protected $mUkuran;
  protected $mWarna;
  protected $mworkOrder;

  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/sales_order';

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
  }


  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "SalesOrder";
    $this->data['buyer'] = $this->mkonsumen->where("active", 1)->findAll();
    $this->data['ukuran'] = $this->mUkuran->where("active", 1)->findAll();
    $this->data['warna'] = $this->mWarna->where("active", 1)->findAll();

    return view($this->views . '\sales_order_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $params = [];

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

    foreach ($results as $row) {
      $id = encrypt($row->id);

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

      $status = $row->status == 1 ? "Draft" : "Approved";

      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $row->id;
      $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
      $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($row->id) : [];
      array_push(
        $build_array["data"],
        array(
          "id"   => ($id),
          "id_sample"   => ($row->id_sample),
          "nama" => $row->nama,
          "tgl_transaksi" => $row->tgl_transaksi,
          "kode_sales_order" => $row->kode_sales_order,
          "tgl_deadline" => $row->tgl_deadline,
          "deskripsi" => $row->deskripsi,
          "uang_dp" => !empty($row->uang_dp) ? \format_angka($row->uang_dp) : 0,
          "status"  => $status,
          "file_gambar" => !empty($row->file_name) ? base_url() . "uploads/sales_order/"  . $row->file_name : "",
          // "detail" => $this->mSalesOrder->getDataDetailSalesOrder($row->id)
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
    $kodeOrder = $this->request->getGet('kodeOrder');

    $data = $this->mSalesOrder->getDataSO($kodeOrder);

    if (!empty($data)) {
      $id = !empty($data) ? $data->id : null;
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $id;
      $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
      $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($id) : [];
    } else {
      $data = $this->mSample->getDataSample($kodeOrder);
      if (!empty($data)) {
        $id = !empty($data) ? $data->id : null;
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $id;
        $dtUkuran = $this->mSample->getUkuranTrans($pru);

        $detail = (!empty($dtUkuran)) ? $this->mSample->getDataDetailSample_crostab($id) : [];
      }
    }

    return $this->response->setJSON(array("data" => $detail, "ukuran" => $dtUkuran));
  }

  function detail($id)
  {
    $id = decrypt($id);
    $results = $this->mSalesOrder->getData($id);
    $status = $results->status == 1 ? "Draft" : "Approved";

    $pru['use'] = 1; // ambil ukuran yang digunnakan order 
    $pru['id_sales_order'] = $id;
    $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
    $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($results->id) : [];

    $build_array =  array(
      "id"   => encrypt($results->id),
      "keterangan" => $results->keterangan,
      "id_konsumen" => $results->id_konsumen,
      "tgl_transaksi" => $results->tgl_transaksi,
      "kode_sales_order" => $results->kode_sales_order,
      "tgl_deadline" => $results->tgl_deadline,
      "deskripsi" => $results->deskripsi,
      "gambar_id" => $results->gambar_id,
      "file_gambar" => !empty($results->file_name) ? base_url() . "uploads/sales_order/" . $results->file_name : "",
      "id_sample" => $results->id_sample,
      "status" => $status,
      "uang_dp" =>  !empty($results->uang_dp) ? $results->uang_dp : 0,
      "detail" => $detail,
      "key_ukuran" => $dtUkuran
    );
    return $this->response->setJSON($build_array);
  }

  function detailQtyUkuran($idSalesOrder, $idSalesOrderDet)
  {
    $id = !empty($idSalesOrder) ? decrypt($idSalesOrder) : 0;
    $idSalesOrderDet = !empty($idSalesOrderDet) ? $idSalesOrderDet : 0;
    $results = $this->mSalesOrder->getData($id);
    $build_array =  array(
      "id"   => encrypt($results->id),
      "keterangan" => $results->keterangan,
      "nama" => $results->nama,
      "tgl_transaksi" => $results->tgl_transaksi,
      "kode_sales_order" => $results->kode_sales_order,
      "tgl_deadline" => $results->tgl_deadline,
      "deskripsi" => $results->deskripsi,
      "file_gambar" => !empty($results->file_name) ? base_url() . "uploads/sales_order/" . $results->file_name : "",
      "detail" => $this->mSalesOrder->getDataDetailSampleWarna($id, $idSalesOrderDet),
      "detailUkuran" =>  $this->mSalesOrder->getDataDetailSampleUkuran($id, $idSalesOrderDet)
    );
    return $this->response->setJSON($build_array);
  }


  function save()
  {
    $id         = $this->request->getPost('id');
    $idKonsumen         = $this->request->getPost('idKonsumen');
    $tglTransaksi = $this->request->getPost('tglTransaksi');
    $tglDeadline = $this->request->getPost('tglDeadline');
    $deskripsi = $this->request->getPost('deskripsi');
    $keterangan = $this->request->getPost('deskripsi');
    $fileIdSalesOrderOld = $this->request->getPost('fileIdSalesOrderOld');
    $noSalesOrder = $this->request->getPost('noSalesOrder');
    $sampleId = $this->request->getPost('samples');
    $submit_data = $this->request->getPost('submit_data');
    $uang_dp = $this->request->getPost('uang_dp');


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
      // 'id_sample' => $sampleId,
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
      }
      $this->mSalesOrder->updateRecord($this->mSalesOrder->table, $arr_isi, 'id', $id);

      if (!empty($submit_data)) {
        $allQty = $this->mSalesOrder->getTotal_qty($id, 1);
        $wo_data = [
          'kode_walkorder' => $this->mworkOrder->generete_kode(),
          'ref_id' => $id,
          'ref_kode' => $noSalesOrder,
          'id_konsumen' => $idKonsumen,
          'keterangan_style' => $keterangan,
          'tgl_deadline' => $tglDeadline,
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
                $isiProses = [
                  'id_walkorder' => $wo_id,
                  'id_proses' => $pro->id_proses,
                  'created_at' => date('Y-m-d H:i:s')
                ];

                $proses_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table3, $isiProses);
              }
            }

            if (!empty($data_warna)) {
              foreach ($data_warna as $xrow) {

                // get detail wo 
                $prms_sample['id_warna_1'] = $xrow->id_warna_1;
                // $prms_sample['id_warna_2'] = $xrow->id_warna_2;
                if (!empty($xrow->id_warna_2)) $prms_sample['id_warna_2'] = $xrow->id_warna_2;
                if (!empty($xrow->id_warna_3)) $prms_sample['id_warna_3'] = $xrow->id_warna_3;
                if (!empty($xrow->id_warna_4)) $prms_sample['id_warna_4'] = $xrow->id_warna_4;
                $data_detail = $this->mSample->getDataDetailSample_ori($sampleId, $prms_sample);

                if (!empty($data_detail)) {
                  $params_wod['ref_detail_id'] = $data_detail[0]->id;
                  $params_wod['tipe_id'] = 1;
                  $params_wod['id_walkorder']  = $ref_sample_wo[0]->id;
                  $data_detail_wo = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $params_wod);

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

                    for ($i = 0; $i < 8; $i++) {
                      $field_name = 'id_warna_' . ($i + 1);
                      if (!empty($xrow->$field_name)) {

                        $params_d['id_walkorder_detail'] = $data_detail_wo[0]->id;
                        $params_d['id_warna'] = $xrow->$field_name;
                        $data_detail = $this->mworkOrder->getData_warna(null, 0, 1, null, null, $params_d);

                        $xgram = 0;
                        $xgram_nd = 0;
                        $xkg = 0;
                        $xloss = 0;
                        $xkg_loss = 0;
                        $xtotal = 0;
                        $xkuota = 0;
                        $xkuota_tambah = 0;

                        if (!empty($data_detail[0]->gram)) {
                          $xgram = $data_detail[0]->gram;
                          $xgram_nd = $xgram * $qty_wodet;
                          $xkg = $xgram_nd / 1000;
                          $xloss = $data_detail[0]->loss;
                          $xkg_loss = ($xkg * $xloss) / 100;
                          $xtotal = $xkg +  $xkg_loss;

                          $xkuota = $data_detail[0]->kuota;
                          $xkuota_tambah = $xkuota - $xtotal;
                        }

                        $isi_warna = [
                          'id_walkorder_detail' => $wo_det_id,
                          'id_warna' => $xrow->$field_name,
                          'persen'       => $data_detail[0]->persen,
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
                      $field_name = 'id_warna_' . ($i + 1);
                      if (!empty($xrow->$field_name)) {
                        $isi_warna = [
                          'id_walkorder_detail' => $wo_det_id,
                          'id_warna' => $xrow->$field_name,
                          'created_at' => date("Y-m-d H:i:s")
                        ];
                        $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
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
                    $field_name = 'id_warna_' . ($i + 1);
                    if (!empty($xrow->$field_name)) {
                      $isi_warna = [
                        'id_walkorder_detail' => $wo_det_id,
                        'id_warna' => $xrow->$field_name,
                        'created_at' => date("Y-m-d H:i:s")
                      ];
                      $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
                    }
                  }
                }
              }
            }
          }
        } else {
          if (!empty($data_warna)) {
            foreach ($data_warna as $xrow) {
              $detail_wo = [
                'id_walkorder' => $wo_id,
                'ref_detail_id' => $xrow->id,
                'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
                'tipe_id' => 2,
                'created_at' => date("Y-m-d H:i:s")
              ];

              $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

              for ($i = 0; $i < 8; $i++) {
                $field_name = 'id_warna_' . ($i + 1);
                if (!empty($xrow->$field_name)) {
                  $isi_warna = [
                    'id_walkorder_detail' => $wo_det_id,
                    'id_warna' => $xrow->$field_name,
                    'created_at' => date("Y-m-d H:i:s")
                  ];
                  $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
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
    $msg    = "Data gagal disimpan !";
    $status = false;
    $idSalesOrder = $this->request->getPost('idSalesOrder');
    $idSalesOrderDet = $this->request->getPost('idSalesOrderDet');
    $warna1 = $this->request->getPost('warna1');
    $warna2 = $this->request->getPost('warna2');
    $warna3 = $this->request->getPost('warna3');
    $warna4 = $this->request->getPost('warna4');
    $warna5 = $this->request->getPost('warna5');
    $warna6 = $this->request->getPost('warna6');
    $warna7 = $this->request->getPost('warna7');
    $warna8 = $this->request->getPost('warna8');
    $dataUkuran = $this->request->getPost('dataUkuran');
    $dataWarna = [
      "id_warna_1" => !empty($warna1) ? $warna1 : null,
      "id_warna_2" => !empty($warna2) ? $warna2 : null,
      "id_warna_3" => !empty($warna3) ? $warna3 : null,
      "id_warna_4" => !empty($warna4) ? $warna4 : null,
      "id_warna_5" => !empty($warna5) ? $warna5 : null,
      "id_warna_6" => !empty($warna6) ? $warna6 : null,
      "id_warna_7" => !empty($warna7) ? $warna7 : null,
      "id_warna_8" => !empty($warna8) ? $warna8 : null,

      "id_sales_order" => (int)decrypt($idSalesOrder),
      "id" => !empty($idSalesOrderDet) ? $idSalesOrderDet :  null,
    ];

    $res = $this->mSalesOrder->trxInsertUpdateRecord($dataWarna, $dataUkuran);
    if ($res) {
      $status = true;
      $msg = "Data berhasil disimpan!";
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  public function deleteList($id = NULL)
  {
    if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
      throw new \Exception('You must be an administrator to view this page.');
    }

    if ($id != null && $id != "") {
      $id = decrypt($id);
    }

    $id = (int)$id;
    $msg    = "Data gagal dihapus !";
    $status = false;
    $res = $this->mSalesOrder->deleteRecord($this->mSalesOrder->table, 'id', $id);
    if ($res) {
      $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "SalesOrder Dihapus");
      $this->session->setFlashdata('message', "SalesOrder berhasil dihapus");
    } else {
      $this->session->setFlashdata('err', "SalesOrder gagal dihapus");
    }


    return redirect()->to($this->urlv);
  }
  public function deleteDetailList($id = NULL)
  {
    if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
      throw new \Exception('You must be an administrator to view this page.');
    }

    if ($id != null && $id != "") {
      $id = decrypt($id);
    }

    $id = (int)$id;
    $msg    = "Data gagal dihapus !";
    $status = false;
    $res = $this->mSalesOrder->deleteRecord("trans_sales_order_det", 'id', $id);
    $resDel = $this->mSalesOrder->deleteRecord("trans_sales_order_ukuran", 'id_sales_order_det', $id);
    if ($resDel) {
      $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "SalesOrder Dihapus");
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

    if (!empty($buyerId)) {
      $params = [
        'id_konsumen' => $buyerId
      ];
      $data_sample = $this->mSample->getData(0, 0, 99999, null, null, $params);
      if (!empty($data_sample)) {
        $msg = "Berhasil mengambil data sample !";
        $status = true;
        $data = $data_sample;
      }
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;
    $build_array['data']    = $data;
    return $this->response->setJSON($build_array);
  }

  public function generateQRCode()
  {

    $data = $this->request->getPost('data');
    if (empty($data)) {
      return $this->response->setStatusCode(400)->setBody("QR Code Failed Generated");
    }
    $data = json_decode((string)$data);
    $resData = $this->mSalesOrder->getDataDetailSalesOrderUkuranById($data->id);

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

    $trans = decrypt($trans);
    /* Data */
    // $hex_data   = bin2hex($id);
    // $save_name  = $hex_data. '_'. time() . '.png';
    $save_name  = $warna . '-' . $noSample . '.png';
    $data_warna = $this->mSalesOrder->getDataDetailSalesOrder($trans);
    // dd($data);
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
    $this->ciqrcode->initialize($config);

    $data = [
      'ukuran' => $ukuran,
      'qty' => $qty,
      'qtyp' => $qtyp,
      'noSample' => $noSample,
      'deskripsi' => $deskripsi,
      'buyer' => $buyer,
      'warna' => $warna,
      'warna_2' => ''
    ];

    if(!empty($data_warna)){
      $wrn = explode(' - ', $data_warna[0]->colour);

      $data['warna_2'] = trim($wrn[1]);
    }

    /* QR Data  */
    $params['data']     = $noSample . ';' . $ukuran . ';' . $warna . ';' . $qty; //json_encode($data) ;//base_url() . "/produk/edit/" . encrypt($id);
    $params['level']    = 'L';
    $params['size']     = 10;
    $params['savename'] = FCPATH . $config['imagedir'] . $save_name;

    $oks = $this->ciqrcode->generate($params);

    /* Return Data */


    // dd($oks);
    $url = base_url() . "/uploads/media/qrcode/" . $save_name;

    $this->data["data"] = $data;
    $this->data["fileName"] = $save_name;
    return view($this->views . '\vprint_qrcode', $this->data);
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
      $resData = $this->mSalesOrder->getData($id);
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $id;
      $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);

      $resDataDetail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($id) : [];
      $keysUkuran = !empty($resDataDetail) ? array_keys(get_object_vars($resDataDetail[0])) : [];

      // Tentukan key mana yang merupakan ukuran (filter selain `id`, `no`, `colordasar`, `colour`, dan `total_harga`)
      $excludeKeys = ["id", "no", "colordasar", "colour", "total_harga"];
      $ukuranKeysInc = array_values(array_diff($keysUkuran, $excludeKeys));

      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['ukuran'] = !empty($ukuranKeysInc) ? $ukuranKeysInc : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }
    $html = view($this->views . '\sales_order_print', $this->data);


    $dompdf->generate($html, 'sales_order.pdf', true);
    exit;
  }
}
