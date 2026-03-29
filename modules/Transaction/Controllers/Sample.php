<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use Modules\Transaction\Models\SampleModel;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\UkuranModel;
use Modules\Referensi\Models\WarnaModel;
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
use Modules\Transaction\Models\WalkorderModel;
use Modules\Transaction\Models\ProductionModel;
use Modules\Referensi\Models\RekeningModel;

class Sample extends BaseController
{
  protected $mSample;
  protected $mkonsumen;
  protected $files;
  protected $mUkuran;
  protected $mWarna;
  protected $mworkOrder;
  protected $mRekening;
  protected $mProduksi;

  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/sample';

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_TRANSAKSI_SAMPLE";
    $this->mUkuran = new UkuranModel();
    $this->mSample = new SampleModel();
    $this->mkonsumen = new KonsumenModel();
    $this->mWarna = new WarnaModel();
    $this->files  = new FileModel();
    $this->mworkOrder = new WalkorderModel();
    $this->mRekening   = new RekeningModel();
    $this->mProduksi = new ProductionModel();
  }


  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Sample";
    $this->data['buyer'] = $this->mkonsumen->where("active", 1)->findAll();
    $this->data['ukuran'] = $this->mUkuran->where("active", 1)->findAll();
    $this->data['warna'] = $this->mWarna->where("active", 1)->findAll();
    return view($this->views . '\sample_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $params = [];

    $results = $this->mSample->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mSample->getDataCnt($filters, $params);
    $totaldata = $this->mSample->getDataCnt(null, $params);
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
      if ($atr_edit || $atr_del) $btnAction = btn_action_group($id, $atr_edit, $atr_del);

      // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
      //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sample'] = $row->id;
      $dtUkuran = $this->mSample->getUkuranTrans($pru);

      $detail = (!empty($dtUkuran)) ? $this->mSample->getDataDetailSample_crostab($row->id) : [];

      array_push(
        $build_array["data"],
        array(
          "id"   => ($id),
          "nama" => $row->nama,
          "tgl_transaksi" => $row->tgl_transaksi,
          "kode_sample" => $row->kode_sample,
          "tgl_deadline" => $row->tgl_deadline,
          "deskripsi" => $row->deskripsi,
          "style" => $row->style,
          "status" => $row->status == 0 ? "Draft" : "Approval",
          "file_gambar" => !empty($row->file_name) ? base_url() . "uploads/sample/"  . $row->file_name : "",
          "uang_dp" => !empty($row->uang_dp) ? \format_angka($row->uang_dp) : 0,
          // "detail" => $this->mSample->getDataDetailSample($row->id)
          "detail" => $detail,
          "key_ukuran" => $dtUkuran
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  function detail($id)
  {
    $id = decrypt($id);
    $results = $this->mSample->getData($id);

    $pru['use'] = 1; // ambil ukuran yang digunnakan order 
    $pru['id_sample'] = $id;
    $dtUkuran = $this->mSample->getUkuranTrans($pru);

    $detail = (!empty($dtUkuran)) ? $this->mSample->getDataDetailSample_crostab($results->id) : [];

    $prg['id_sample_det'] = $results->id;
    $dtGram = $this->mSample->getData_gram(null, 0, 9999, null, null, $prg);

    if(!empty($dtGram)){
      for ($i=0; $i < count($dtGram) ; $i++) { 
        $kodeWarna = "Warna ";
        if($i == 0) { 
          $kodeWarna = $kodeWarna . 'A';
        } else if($i == 1) { 
          $kodeWarna = $kodeWarna . 'B';
        } else if($i == 2) { 
          $kodeWarna = $kodeWarna . 'C';
        } else if($i == 3) { 
          $kodeWarna = $kodeWarna . 'D';
        } else if($i == 4) { 
          $kodeWarna = $kodeWarna . 'E';
        } else if($i == 5) { 
          $kodeWarna = $kodeWarna . 'F';
        } else if($i == 6) { 
          $kodeWarna = $kodeWarna . 'G';
        } else if($i == 7) { 
          $kodeWarna = $kodeWarna . 'H';
        }

        $dtGram[$i]->kode_warna = $kodeWarna . ' - ' . $dtGram[$i]->kode_warna;
      }
    }

    $build_array =  array(
      "id"   => encrypt($results->id),
      "keterangan" => $results->keterangan,
      "id_konsumen" => $results->id_konsumen,
      "tgl_transaksi" => $results->tgl_transaksi,
      "kode_sample" => $results->kode_sample,
      "tgl_deadline" => $results->tgl_deadline,
      "deskripsi" => $results->deskripsi,
      "gambar_id" => $results->gambar_id,
      "style" => $results->style,
      "status" => $results->status,
      "uang_dp" =>  !empty($results->uang_dp) ? $results->uang_dp : 0,
      "file_gambar" => !empty($results->file_name) ? base_url() . "uploads/sample/" . $results->file_name : "",
      // "detail" => $this->mSample->getDataDetailSample($results->id),
      "detail" => $detail,
      "key_ukuran" => $dtUkuran,
      "detail_gram" => $dtGram
    );
    return $this->response->setJSON($build_array);
  }


  function detailQtyUkuran($idSample, $idSampleDet)
  {
    $id = !empty($idSample) ? decrypt($idSample) : 0;
    $idSampleDet = !empty($idSampleDet) ? $idSampleDet : 0;
    $results = $this->mSample->getData($id);

    $prg['id_sample_det'] = $idSampleDet;
    $dtGram = $this->mSample->getData_gram(null, 0, 9999, null, null, $prg);

    if(!empty($dtGram)){
      for ($i=0; $i < count($dtGram) ; $i++) { 
        $kodeWarna = "Warna ";
        if($i == 0) { 
          $kodeWarna = $kodeWarna . 'A';
        } else if($i == 1) { 
          $kodeWarna = $kodeWarna . 'B';
        } else if($i == 2) { 
          $kodeWarna = $kodeWarna . 'C';
        } else if($i == 3) { 
          $kodeWarna = $kodeWarna . 'D';
        } else if($i == 4) { 
          $kodeWarna = $kodeWarna . 'E';
        } else if($i == 5) { 
          $kodeWarna = $kodeWarna . 'F';
        } else if($i == 6) { 
          $kodeWarna = $kodeWarna . 'G';
        } else if($i == 7) { 
          $kodeWarna = $kodeWarna . 'H';
        }

        $dtGram[$i]->kode_warna = $kodeWarna . ' - ' . $dtGram[$i]->kode_warna;
      }
    }
    // print_r($prg);exit;
    $build_array =  array(
      "id"   => encrypt($results->id),
      "keterangan" => $results->keterangan,
      "nama" => $results->nama,
      "tgl_transaksi" => $results->tgl_transaksi,
      "kode_sample" => $results->kode_sample,
      "tgl_deadline" => $results->tgl_deadline,
      "deskripsi" => $results->deskripsi,
      "file_gambar" => !empty($results->file_name) ? base_url() . "uploads/sample/" . $results->file_name : "",
      "detail" => $this->mSample->getDataDetailSampleWarna($id, $idSampleDet),
      "detailUkuran" =>  $this->mSample->getDataDetailSampleUkuran($id, $idSampleDet),
      "detail_gram" => $dtGram
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
    $fileIdSampleOld = $this->request->getPost('fileIdSampleOld');
    $qty = !empty($this->request->getPost('qty')) && $this->request->getPost('qty') != "NaN" ? $this->request->getPost('qty') : null;
    $hargaTotal = $this->request->getPost('hargaTotal');
    $stat = $this->request->getPost('status');
    $style = $this->request->getPost('style');
    $submit_data = $this->request->getPost('status');

    $this->validation->setRules([
      'idKonsumen '               => ['label' => 'Pilih Buyer', 'rules' => 'required'],
      'tglTransaksi'         => ['label' => 'Tanggal Transaksi', 'rules' => 'required'],
      'tglDeadline'          => ['label' => 'Tanggal Deadline', 'rules' => 'required'],
      'deskripsi'             => ['label' => 'Deskripsi', 'rules' => 'required|trim'],
    ]);


    if (!empty($this->request->getFile('fileSample'))) {
      $fileSample       = $this->request->getFile('fileSample');
      $fileName       = $fileSample->getRandomName();
      $originName     = $fileSample->getName();
      $fileType       = $fileSample->getMimeType();
      $fileSize       = $fileSample->getSize();


      if (!in_array($fileType, ['image/png', 'image/jpeg', 'image/webp'])) {
        $build_array['message'] = "<br>File <b>Sample</b> hanya menerima tipe file <b>*.webp</b> <b>*.jpg</b>, atau <b>*.png</b>";
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

      $fileIdSample = $this->files->insertID();
      $fileSample->move(WRITEPATH . 'uploads/sample/', $fileName);

      if ($fileIdSampleOld != "") {
        $nama_file =  $this->files->where('id', $fileIdSampleOld)->get()->getRow()->file_name;
        unlink(WRITEPATH . 'uploads/sample/' . $nama_file);

        $this->files->delete(['id' => $fileIdSampleOld]);
      }
    } else {
      $fileIdSample = !empty($fileIdSampleOld) ? $fileIdSampleOld : null;
    }

    $msg    = "Data gagal ditambahkan !";
    $status = false;
    $arr_isi = [
      'id_konsumen' => $idKonsumen,
      'keterangan' => $keterangan,
      'deskripsi' => $deskripsi,
      'tgl_transaksi' => $tglTransaksi,
      'tgl_deadline' => $tglDeadline,
      'total_harga' => $hargaTotal,
      'style' => $style,
      'qty' => $qty,
      'active' => 1,
      'status' => $stat,
      'gambar_id' => !empty($fileIdSample) ? $fileIdSample : null
    ];


    if (empty($id)) {

      // menyimpan style 
      $prm_syle['id_konsumen'] = $idKonsumen;
      $prm_syle['kode_style'] = $style;
      $cek_style = $this->mkonsumen->getDataStyle(0, 0, 1, null, null, $prm_syle);
      if (empty($cek_style)) {
        $in_style['id_konsumen'] = $idKonsumen;
        $in_style['kode_style'] = $style;
        $in_style['keterangan_style'] = $style;
        $this->mSample->insertRecordGetid('ref_konsumen_style', $in_style);
      }
      $arr_isi['created_at'] = date("Y-m-d H:i:s");
      $arr_isi['kode_sample'] = $this->mSample->generateNo("SPL", "trans_sample", "kode_sample");
      $this->mSample->insertRecordGetid($this->mSample->table, $arr_isi);
      $msg    = "Data berhasil ditambahkan !";
      $status = true;
    } else {
      $arr_isi['updated_at'] = date("Y-m-d H:i:s");
      if ($stat == 0) {
        unset($arr_isi['status']);
      }
      $id = decrypt($id);

      $getTotalSO = $this->mSample->getTotalUkuranSample($id);
      if (!empty($getTotalSO)) {
        $arr_isi['qty'] = $getTotalSO->qty;
        $arr_isi['total_harga'] = $getTotalSO->harga_total;
      }
      $this->mSample->updateRecord($this->mSample->table, $arr_isi, 'id', $id);
      
      //auto update qty in WO dan PROD
      $paramsWO['ref_id'] = $id;
      $paramsWO['tipe_id'] = 1;
      $getWO = $this->mworkOrder->getData(null, null, null, null, null, $paramsWO);
      if (!empty($getWO)) {
        foreach ($getWO as $keyWO => $valueWO) {
          $allQty = $this->mSample->getTotal_qty($id, 1);
          $wo_data = [
            'ref_id' => $id,
            'id_konsumen' => $idKonsumen,
            'keterangan_style' => $style,
            'keterangan' => $deskripsi,
            'tgl_deadline' => $tglDeadline,
            'qty' => !empty($allQty) ? $allQty : 0,
            'tipe_id' => 1,
            'file_id' => !empty($fileIdSample) ? $fileIdSample : null,
            'updated_at' => date("Y-m-d H:i:s")
          ];
          $this->mworkOrder->updateRecord($this->mworkOrder->table, $wo_data, 'id', $valueWO->id);

          // $data_warna = $this->mSample->getDataDetailSample_ori($id);
          // if (!empty($id) && $id != 'null') {

          //   $params_wo['tipe_id'] = 1;
          //   $params_wo['ref_id']  = $id;
          //   $ref_sample_wo = $this->mworkOrder->getData(null, 0, 1, null, null, $params_wo);
          //   if (!empty($ref_sample_wo)) {

          //     if (!empty($data_warna)) {
          //       foreach ($data_warna as $xrow) {

          //         // get detail wo 
          //         $prms_sample['id_warna_1'] = $xrow->id_warna_1;
          //         // $prms_sample['id_warna_2'] = $xrow->id_warna_2;
          //         if (!empty($xrow->id_warna_2)) $prms_sample['id_warna_2'] = $xrow->id_warna_2;
          //         if (!empty($xrow->id_warna_3)) $prms_sample['id_warna_3'] = $xrow->id_warna_3;
          //         if (!empty($xrow->id_warna_4)) $prms_sample['id_warna_4'] = $xrow->id_warna_4;
          //         $data_detail = $this->mSample->getDataDetailSample_ori($id, $prms_sample);
          //         if (!empty($data_detail)) {
          //           $params_wod['ref_detail_id'] = $data_detail[0]->id;
          //           $params_wod['tipe_id'] = 1;
          //           $params_wod['single'] = true;
          //           $params_wod['id_walkorder']  = $ref_sample_wo[0]->id;
          //           $data_detail_wo = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $params_wod);
          //           // $prgram['id_sample_det'] = $data_detail[0]->id;
          //           // $dtGram = $this->mSample->getData_gram(null, 0, 9999, null,  null, $prgram);
          //           if (!empty($data_detail_wo)) {

          //             $qty_wodet =  $this->mSample->getTotal_qty($xrow->id, 2);
          //             $gram = 0;
          //             $gram_nd = 0;
          //             $kg = 0;
          //             $loss = 0;
          //             $kg_loss = 0;
          //             $total = 0;
          //             $kuota = 0;
          //             $kuota_tambah = 0;

          //             if (!empty($data_detail_wo->gram)) {
          //               $gram = $data_detail_wo->gram;
          //               $gram_nd = $gram * $qty_wodet;
          //               $kg = $gram_nd / 1000;
          //               $loss = $data_detail_wo->loss;
          //               $kg_loss = ($kg * $loss) / 100;
          //               $total = $kg +  $kg_loss;

          //               $kuota = $data_detail_wo->kuota;
          //               $kuota_tambah = $kuota - $total;
          //             }

          //             $detail_wo_get = [
          //               'id_walkorder' => $valueWO->id,
          //               'ref_detail_id' => $xrow->id,
          //               'tipe_id' => 2,
          //               'single' => true,
          //             ];
                      
          //             $getWODet = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $detail_wo_get);
          //             if (empty($getWODet)) {
          //               $detail_wo = [
          //                 'id_walkorder' => $valueWO->id,
          //                 'ref_detail_id' => $xrow->id,
          //                 'qty' => $qty_wodet,
          //                 'tipe_id' => 2,
          //                 'gram'         => $gram,
          //                 'gram_nd'      => $gram_nd,
          //                 'kg'           => $kg,
          //                 'loss'         => $loss,
          //                 'kg_loss'      => $kg_loss,
          //                 'total'        => $total,
          //                 'kuota'        => $kuota,
          //                 'kuota_tambah' => $kuota_tambah,
          //                 'created_at' => date("Y-m-d H:i:s")
          //               ];

          //               $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

          //               for ($i = 0; $i < 8; $i++) {
          //                 $field_name = 'id_warna_' . ($i + 1);
          //                 if (!empty($xrow->$field_name)) {

          //                   $params_d['id_walkorder_detail'] = $data_detail_wo->id;
          //                   $params_d['id_warna'] = $xrow->$field_name;
          //                   $data_detail = $this->mworkOrder->getData_warna(null, 0, 1, null, null, $params_d);

          //                   $xgram = 0;
          //                   $xgram_nd = 0;
          //                   $xkg = 0;
          //                   $xloss = 0;
          //                   $xkg_loss = 0;
          //                   $xtotal = 0;
          //                   $xkuota = 0;
          //                   $xkuota_tambah = 0;

          //                   if(!empty($data_detail)) {
          //                     if (!empty($data_detail[0]->gram)) {
          //                       $xgram = $data_detail[0]->gram;
          //                       $xgram_nd = $xgram * $qty_wodet;
          //                       $xkg = $xgram_nd / 1000;
          //                       $xloss = $data_detail[0]->loss;
          //                       $xkg_loss = ($xkg * $xloss) / 100;
          //                       $xtotal = $xkg +  $xkg_loss;
      
          //                       $xkuota = $data_detail[0]->kuota;
          //                       $xkuota_tambah = $xkuota - $xtotal;
          //                     }
      
          //                     $isi_warna = [
          //                       'id_walkorder_detail' => $wo_det_id,
          //                       'id_warna' => $xrow->$field_name,
          //                       'persen'       => $data_detail[0]->persen,
          //                       'gram'         => $xgram,
          //                       'gram_nd'      => $xgram_nd,
          //                       'kg'           => $xkg,
          //                       'kg_loss'      => $xkg_loss,
          //                       'total'        => $xtotal,
          //                       'kuota'        => $xkuota,
          //                       'kuota_tambah' => $xkuota_tambah,
          //                       'loss'         => $xloss,
          //                       'created_at' => date("Y-m-d H:i:s")
          //                     ];
      
          //                     $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
          //                   }else{
          //                     $isi_warna = [
          //                       'id_walkorder_detail' => $wo_det_id,
          //                       'id_warna' => $xrow->$field_name,
          //                       'created_at' => date("Y-m-d H:i:s")
          //                     ];
          //                     $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
          //                   }
          //                 }
          //               }
          //             }

          //           } else {
          //             $params_wod['ref_detail_id'] = $xrow->id;
          //             $params_wod['tipe_id'] = 1;
          //             $params_wod['single'] = true;
          //             $params_wod['id_walkorder']  = $valueWO->id;

          //             $data_detail_wo = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $params_wod);
          //             $detail_wo = [
          //               'id_walkorder' => $valueWO->id,
          //               'ref_detail_id' => $xrow->id,
          //               'qty' => $this->mSample->getTotal_qty($xrow->id, 1),
          //               'tipe_id' => 1,
          //               'created_at' => date("Y-m-d H:i:s")
          //             ];
          //             if(!empty($data_detail_wo) > 0) {
          //               $this->mworkOrder->updateRecord($this->mworkOrder->table2, $detail_wo, 'id', $data_detail_wo->id);

          //               for ($i = 0; $i < 8; $i++) {
          //                 $field_name = 'id_warna_' . ($i + 1);
          //                 if (!empty($xrow->$field_name)) {
          //                   $params_warna = [
          //                     'id_walkorder_detail' => $data_detail_wo->id,
          //                     'id_warna' => $xrow->$field_name,
          //                     'single' => true,
          //                   ];
          //                   $data_detail_warna = $this->mworkOrder->getData_warna(null, 0, 1, null, null, $params_warna);
          //                   if (!empty($data_detail_warna)) {
          //                     $isi_warna = [
          //                       'id_walkorder_detail' => $data_detail_wo->id,
          //                       'id_warna' => $xrow->$field_name,
          //                     ];
          //                     $this->mworkOrder->updateRecord($this->mworkOrder->table5, $isi_warna, 'id', $data_detail_warna->id);
          //                   }
          //                 }
          //               }
          //             }
          //             else {
          //               $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

          //               for ($i = 0; $i < 8; $i++) {
          //                 $field_name = 'id_warna_' . ($i + 1);
          //                 if (!empty($xrow->$field_name)) {
          //                   $isi_warna = [
          //                     'id_walkorder_detail' => $wo_det_id,
          //                     'id_warna' => $xrow->$field_name,
          //                     'created_at' => date("Y-m-d H:i:s")
          //                   ];
          //                   $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
          //                 }
          //               }
          //             }
          //           }
          //         } else {
          //           $detail_wo = [
          //             'id_walkorder' => $valueWO->id,
          //             'ref_detail_id' => $xrow->id,
          //             'tipe_id' => 1,
          //           ];
          //           $getWODet = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $detail_wo);
          //           if (empty($getWODet)) {

          //             $detail_wo = [
          //               'id_walkorder' => $valueWO->id,
          //               'ref_detail_id' => $xrow->id,
          //               'qty' => $this->mSample->getTotal_qty($xrow->id, 2),
          //               'tipe_id' => 1,
          //               'created_at' => date("Y-m-d H:i:s")
          //             ];

          //             $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

          //             for ($i = 0; $i < 8; $i++) {
          //               $field_name = 'id_warna_' . ($i + 1);
          //               if (!empty($xrow->$field_name)) {
          //                 $isi_warna = [
          //                   'id_walkorder_detail' => $wo_det_id,
          //                   'id_warna' => $xrow->$field_name,
          //                   'created_at' => date("Y-m-d H:i:s")
          //                 ];
          //                 $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
          //               }
          //             }
          //           }
          //         }
          //       }
          //     }
          //   }
          // } 
          // else {
          //   if (!empty($data_warna)) {
          //     foreach ($data_warna as $xrow) {
          //       $detail_wo = [
          //         'id_walkorder' => $valueWO->id,
          //         'ref_detail_id' => $xrow->id,
          //         'tipe_id' => 2,
          //       ];

          //       $getWODet = $this->mworkOrder->getData_detail(null, 0, 1, null, null, $detail_wo);
          //       if (empty($getWODet)) {
          //         $detail_wo = [
          //           'id_walkorder' => $valueWO->id,
          //           'ref_detail_id' => $xrow->id,
          //           'qty' => $this->mSalesOrder->getTotal_qty($xrow->id, 2),
          //           'tipe_id' => 2,
          //           'created_at' => date("Y-m-d H:i:s")
          //         ];

          //         $wo_det_id = $this->mworkOrder->insertRecordGetid($this->mworkOrder->table2, $detail_wo);

          //         for ($i = 0; $i < 8; $i++) {
          //           $field_name = 'id_warna_' . ($i + 1);
          //           if (!empty($xrow->$field_name)) {
          //             $isi_warna = [
          //               'id_walkorder_detail' => $wo_det_id,
          //               'id_warna' => $xrow->$field_name,
          //               'created_at' => date("Y-m-d H:i:s")
          //             ];
          //             $this->mworkOrder->insertRecordGetid($this->mworkOrder->table5, $isi_warna);
          //           }
          //         }
          //       }
          //     }
          //   }
          // }

          $paramsPD['id_walkorder'] = $valueWO->id;
          $paramsPD['tipe_id'] = 1;
          $getPD = $this->mProduksi->getData(null, null, null, null, null, $paramsPD);
          if (!empty($getPD)) {
            foreach ($getPD as $keyPD => $valuePD) {
              $wo_data = [
                'id_konsumen' => $idKonsumen,
                'tgl_deadline' => $tglDeadline,
                'keterangan_style' => $style,
                'keterangan' => $deskripsi,
                'qty' => !empty($allQty) ? $allQty : 0,
                'tipe_id' => 1,
                'file_id' => !empty($fileIdSample) ? $fileIdSample : null,
                'updated_at' => date("Y-m-d H:i:s")
              ];
              $arr_pd_update['qty'] = !empty($allQty) ? $allQty : 0;
              $this->mProduksi->updateRecord($this->mProduksi->table, $arr_pd_update, 'id', $valuePD->id);
            }
          }
        }
      }
      
      $res = $this->mSample->trxSubmitSample($arr_isi, $id);
      if ($res) {
        $msg    = "Data berhasil diupdate !";
        $status = true;
      }
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  function saveDetail()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $idSample = $this->request->getPost('idSample');
    $idSampleDet = $this->request->getPost('idSampleDet');
    $warna1 = $this->request->getPost('warna1');
    $warna2 = $this->request->getPost('warna2');
    $warna3 = $this->request->getPost('warna3');
    $warna4 = $this->request->getPost('warna4');
    $warna5 = $this->request->getPost('warna5');
    $warna6 = $this->request->getPost('warna6');
    $warna7 = $this->request->getPost('warna7');
    $warna8 = $this->request->getPost('warna8');
    $dataUkuran = $this->request->getPost('dataUkuran');
    $dataGram = $this->request->getPost('dataGram');
    $dataWarna = [
      "id_warna_1" => !empty($warna1) ? $warna1 : null,
      "id_warna_2" => !empty($warna2) ? $warna2 : null,
      "id_warna_3" => !empty($warna3) ? $warna3 : null,
      "id_warna_4" => !empty($warna4) ? $warna4 : null,
      "id_warna_5" => !empty($warna5) ? $warna5 : null,
      "id_warna_6" => !empty($warna6) ? $warna6 : null,
      "id_warna_7" => !empty($warna7) ? $warna7 : null,
      "id_warna_8" => !empty($warna8) ? $warna8 : null,

      "id_sample" => (int)decrypt($idSample),
      "id" => !empty($idSampleDet) ? $idSampleDet :  null,
    ];
    $res = $this->mSample->trxInsertUpdateRecord($dataWarna, $dataUkuran, $dataGram);

    if ($res === true) {
      $status = true;
      $msg = "Data berhasil disimpan!";
    }
    else {
        $status = false;
        $msg = $res;
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

    $res = $this->mSample->deleteRecord($this->mSample->table, 'id', $id);
    if ($res) {
      $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Sample Dihapus");
      $this->session->setFlashdata('message', "Sample berhasil dihapus");
    } else {
      $this->session->setFlashdata('err', "Sample gagal dihapus");
    }

    return redirect()->to($this->urlv);
  }
  public function deleteDetailList()
  {
    if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
      throw new \Exception('You must be an administrator to view this page.');
    }


    $id = (int)$this->request->getPost('id');
    $msg    = "Data gagal dihapus !";
    $status = false;
    $res = $this->mSample->deleteRecord("trans_sample_det", 'id', $id);
    $resDel = $this->mSample->deleteRecord("trans_sample_ukuran", 'id_sample_det', $id);
    if ($resDel) {
      $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Sample Dihapus");
      $status = true;
      $msg = "Data berhasil dihapus!";
    }
    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  public function generateQRCode()
  {

    $data = $this->request->getPost('data');
    if (empty($data)) {
      return $this->response->setStatusCode(400)->setBody("QR Code Failed Generated");
    }
    $data = json_decode((string)$data);
    $resData = $this->mSample->getDataDetailSampleUkuranById($data->id);

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

    /* Data */
    // $hex_data   = bin2hex($id);
    // $save_name  = $hex_data. '_'. time() . '.png';
    $warnaNew = str_replace('/', '_', $warna);
    $save_name  = $warnaNew . '-' . $noSample . '.png';

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
    ];

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

  // fungsi untuk autocomplete 
  public function getDataStyleKonsumen()
  {
    $kata_kunci  = $this->request->getPost("kata_kunci");
    $id_konsumen = $this->request->getPost("id_konsumen");

    $status = false;
    $msg = "Data Style Konsumen tidak ditemukan !";
    $slc  = [];

    try {
      $params['id_konsumen'] = $id_konsumen;
      $params['kata_kunci']  = $kata_kunci;
      $result = $this->mkonsumen->getDataStyle(null, 0, 9999, null, null, $params);
      if (!empty($result)) {
        foreach ($result as $r) {
          $isi_slc = [];
          $isi_slc["id"]    = 0;
          $isi_slc["idx"]   = 0;
          $isi_slc["value"] = $r->kode_style;
          $isi_slc["label"] = $r->keterangan_style;
          $isi_slc["data"]  = [];
          $slc[] = $isi_slc;
        }
        $status = true;
        $msg = "Data style ditemukan !";
      }
    } catch (\Throwable $th) {
      //throw $th;
      $msg = "Gagal mengambil data Style Konsumen!";
    }

    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = [];
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
      $resData = $this->mSample->getData($id);
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sample'] = $id;
      $dtUkuran = $this->mSample->getUkuranTrans($pru);

      $resDataDetail = (!empty($dtUkuran)) ? $this->mSample->getDataDetailSample_crostab($id) : [];
      $keysUkuran = !empty($resDataDetail) ? array_keys(get_object_vars($resDataDetail[0])) : [];

      // Tentukan key mana yang merupakan ukuran (filter selain `id`, `no`, `colordasar`, `colour`, dan `total_harga`)
      $excludeKeys = ["id", "no", "colordasar", "colour", "total_harga"];
      $ukuranKeysInc = array_values(array_diff($keysUkuran, $excludeKeys));

      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['ukuran'] = !empty($ukuranKeysInc) ? $ukuranKeysInc : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }
    $html = view($this->views . '\sample_print', $this->data);


    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
    exit;
  }
}
