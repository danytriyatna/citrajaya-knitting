<?php

namespace Modules\SDM\Controllers;

use CodeIgniter\Controller;
use Modules\SDM\Models\Mabsensi;
use App\Controllers\BaseController;
use Modules\SDM\Models\Mpenggajian;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\SatuanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\KaryawanModel;
use Modules\Referensi\Models\JenisBarangModel;

class Penggajian extends BaseController
{
  protected $views = '\Modules\SDM\Views';
  protected $urlv  = 'sdm/penggajian';
  
  protected $mabsen;
  protected $mkaryawan;
  protected $mgaji;
  protected $dnow;

  function __construct()
  {
      $this->MOD_ALIAS = "MOD_SDM_PENGGAJIAN";
      $this->mabsen = new Mabsensi();
      $this->mabsen = new Mabsensi();
      $this->mkaryawan = new KaryawanModel();
      $this->mgaji = new Mpenggajian();
      $this->dnow        = date('Y-m-d H:i:s');
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
        return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Penggajian";
    
    return view($this->views . '\penggajian_list', $this->data);
  }

  public function lists()
  {
      $start   = $this->request->getPost('start');
      $limit   = $this->request->getPost('length');
      $filters = $this->request->getPost('filter');
      $order   = $this->request->getPost('order');
      // $tahun   = $this->request->getPost('tahun');

      // $params['tahun'] = $tahun;
      
      $params = [];
      $results = $this->mgaji->getData(null, $start, $limit, $order, $filters, $params);
      $totalfiltered = $this->mgaji->getDataCnt($filters, $params);
      $totaldata = $this->mgaji->getDataCnt(null, $params);
      $maxpage = ceil($totalfiltered / $limit);
      $build_array = array(
          "last_page" => $maxpage,
          "recordsTotal" => $totaldata,
          "recordsFiltered" => $totalfiltered,
          "data" => array()
      );

      foreach ($results as $row) {
          $id = encrypt($row->id);

          $atr_edit = null; $atr_del = null;

          if ($this->_edit) {
              $atr_edit['title'] = 'Edit';
              $atr_edit['url'] = $this->urlv.'/form/';
              $atr_edit['class'] = '';
          }
          if ($this->_delete) {
              $atr_del['title'] = 'Hapus';
              $atr_del['url'] = $this->urlv.'/delete/';
              $atr_del['class'] = '';
              // $atr_del['onclick'] = "return confirm('Hapus data ?')";
          }
          
          $btnAction = btn_action_group($id, $atr_edit, $atr_del['url']);

          
          $periode_awal = "";
          if(!empty($row->periode_awal)){
              $periode_awal = fdate_eng_to_ind($row->periode_awal);
          }

          $periode_akhir = "";
          if(!empty($row->periode_akhir)){
              $periode_akhir = fdate_eng_to_ind($row->periode_akhir);
          }
          
          $status = '-';
          if($row->status == 2){
            $status = "Approved";
          }else{
            $status = "Draft";
          }

          $type = '-';
          if($row->type == 2){
            $type = "CMT";
          }else{
            $type = "NON-CMT";
          }
          // $total_pay = $this->mtrans_pay_det->get_total_bayar($row->sl_customer_receipt_id);
          array_push($build_array['data'], array(
              'aksi' => $btnAction,
              'kode_gaji' => $row->kode_gaji,
              'periode_awal' => $periode_awal,
              'periode_akhir' => $periode_akhir,
              'keterangan' => $row->keterangan,
              'status' => $status,
              'type' => $type,
          ));

      }
      
      return $this->response->setJSON($build_array);
  }

  function form($id = ''){
      $this->data['titlehead'] = "Form Penggajian";

      $this->data['titlehead'] = "Input Penggajian";
      if ($id != ""){
          $this->data['id'] = $id;
          $id = decrypt($id);
          $this->data['titlehead'] = "Edit Penggajian";
      }

      $stdData = new \stdClass();
      $stdData->kode_gaji = '';
      $stdData->periode_awal = date('d-m-Y');
      $stdData->periode_akhir = date('d-m-Y');
      $stdData->keterangan = '';
      $stdData->type = null;
      $stdData->status = 1;

      $Ldetail = "";

      $is_read = false;
      $isFinal = false;

      if($id) {
        $stdData = $this->mgaji->getData($id);  
        $stdData->periode_awal  = fdate_eng_to_ind($stdData->periode_awal);
        $stdData->periode_akhir  = fdate_eng_to_ind($stdData->periode_akhir);
        $stdData->type  = $stdData->type;
        $this->data['row'] = $stdData;

        // set detail array
            $params_det['id_sdm_gaji'] = $id;
            $is_detail = $this->mgaji->getDataDet(null,0, 9999, 0, 0, $params_det);  
            
            // $i = 1;
            // foreach ($is_detail as $r) {
            //     $inv_id = encrypt($r->id_invoice);
            //     $isi = [];
            //     $isi["seq"] = $i++;
            //     $isi["id"] = $r->id;
            //     $isi["id_sdm_gaji"] = $r->id_sdm_gaji;
            //     $isi["remain_item"] = !empty($r->remain_item) ? $r->remain_item : $r->grand_total;
            //     $isi["pph"] = $r->pph;
            //     $isi["pay_item"] = $r->pay_item;

            //     $detail_array[] = $isi;
            // }
            $i = 0;
            foreach  ($is_detail as $r) {
              $is_detail[$i]->gaji_jam = $r->gaji_harian;
              // $is_detail[$i]->total = $r->gaji;
              $is_detail[$i]->total = ($r->gaji_harian + $r->uang_lembur + $r->bonus + $r->premi) - $r->potongan;
              $i++;
            }

            $detail_array = $is_detail;
            
            
            $Ldetail = json_encode($detail_array);


            $is_read = true;
        // end auditor

    }

    $this->data['row'] = $stdData;

    if($_POST)
		{ 
      // dd($_POST);
            $stdData->periode_awal = trim($this->request->getPost('filter_tgl_from'));
            $stdData->periode_akhir = trim($this->request->getPost('filter_tgl_to'));
            $stdData->keterangan = trim($this->request->getPost('keterangan'));
            $stdData->type = trim($this->request->getPost('filter_cmt'));

            $action  =  trim($this->request->getPost('actionf'));
            $Ldetail =  trim($this->request->getPost('detailData'));
				
            $this->validation->setRules([
                'filter_tgl_from' => ['label' => 'Periode Awal', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'filter_tgl_to' => ['label' => 'Periode Akhir', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()]
            ]);
            // dd($this->validation->withRequest($this->request)->run());
            if ($this->validation->withRequest($this->request)->run() === TRUE){
               
                $dataIn["periode_awal"] = fdate_ind_to_eng($stdData->periode_awal);
                $dataIn["periode_akhir"] = fdate_ind_to_eng($stdData->periode_akhir);
                $dataIn["type"] = $stdData->type;
                $dataIn["keterangan"] = $stdData->keterangan;
                $dataIn["status"] = $stdData->status;

                $dtDet = json_decode($Ldetail, true);
                $mtd   = "Simpan";
                if(!empty($id)){
                    $dataIn['updated_by'] =  $this->get_userid();
                    $dataIn['updated_at'] = date('Y-m-d H:i:s');
                    $mtd = "Update";
                    $dataIn['status'] = $stdData->status;
                    if($action == 'approve'){
                        $dataIn['status'] = 2;
                        $mtd = "Approve";
                    }else if($action == 'reject'){
                        $dataIn['status'] = 3;
                        $mtd = "Reject";
                    }
                    if (empty($dtDet[0]["id"])) {
                      $this->mgaji->deleteRecordMultipleColumn($this->mgaji->table2, ["id_sdm_gaji" => $id]);
                    }
                    $inUp = $this->update($id, $dataIn, $dtDet);
                }else{

                    $dataIn['kode_gaji'] =  $this->mgaji->generateNo("SL", $this->mgaji->table, "kode_gaji");
                    $dataIn['created_by'] = $this->get_userid();
                    $dataIn['created_at'] = date('Y-m-d H:i:s');
                    $dataIn['active'] = 1;
                    $dataIn['status'] = 1;
                    // dd($dtDet);
                    $inUp = $this->insert($dataIn, $dtDet);
                }
                
                if($inUp){
                    // return redirect()->to('/asesmen/sni_form/'.$idx);
                    $this->session->setFlashdata('message', "{$mtd} data berhasil.." );
                    return redirect()->to('/sdm/penggajian');
                }else{
                    $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
                }
            }else{
                $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", $this->validation->listErrors());
            }
			
		}

    $this->data['Ldetail'] =  $Ldetail;

    $show_save_btn = true; 
    $show_approve_btn = true; 
    $show_reject_btn = true;  
    // $show_final_btn = $isFinal; 
    $exist = 0;

    if($stdData->status == 2){
        $is_read = true;
        $show_save_btn = false; 
        $show_approve_btn = false; 
        $show_reject_btn = false; 
        // $show_final_btn = false; 
    }else if($stdData->status == 0 ){
        $show_approve_btn = false; 
        $show_reject_btn = false; 
    }else if(($stdData->status > 1) || $stdData->status == 3){
        $show_approve_btn = false; 
        $show_reject_btn = false; 
    }
    $this->data['disabled_input'] = $is_read;
    $show_approve_btn = false; 
    $show_reject_btn = false; 

    $this->data['show_save_btn'] = $show_save_btn;
    $this->data['show_approve_btn'] = $show_approve_btn;
    $this->data['show_reject_btn'] = $show_reject_btn;
    // dd($this->data);
    return view($this->views . '\penggajian_form', $this->data);
  }

  function insert($data, $detail){
    try {
      $now = date('Y-m-d H:i:s');
      $user_id = $this->get_userid();
        
      $this->db->transBegin();
        $id = $this->mgaji->insertRecordGetid($this->mgaji->table, $data);
        
        if(!empty($detail)){
          foreach ($detail as $r) {
              $dtIn["id_sdm_gaji"] = $id; 
              $dtIn["id_karyawan"] = $r["id_karyawan"]; 
              $dtIn["nip"] = $r["nip"]; 
              $dtIn["posisi"] = $r["posisi"]; 
              $dtIn["hadir"] = $r["hadir"]; 
              $dtIn["izin"] = $r["izin"]; 
              $dtIn["sakit"] = $r["sakit"]; 
              $dtIn["alpha"] = $r["alpha"]; 
              $dtIn["terlambat"] = $r["terlambat"]; 
              $dtIn["gaji_harian"] = $r["gaji_jam"]; 
              $dtIn["lembur"] = $r["lembur"]; 
              $dtIn["lembur_we"] = $r["lembur_we"]; 
              if (isset($r["premi"])) {
                $dtIn["premi"] = $r["premi"]; 
              }
              $dtIn["uang_lembur"] = $r["uang_lembur"]; 
              $dtIn["gaji"] = $r["total"]; 
              $dtIn["bonus"] = $r["bonus"]; 
              $dtIn["bonus_keterangan"] = $r["bonus_keterangan"]; 
              $dtIn["potongan"] = $r["potongan"]; 
              $dtIn['durasi_kerja'] = $r['jam_kerja'];
              $dtIn['jml_sample'] = $r['jml_sample'];
              
              $dtIn["active"] = 1;
              $dtIn["created_by"] = $user_id;
              $dtIn["created_at"] = $now;
              $this->mgaji->insertRecordGetid($this->mgaji->table2, $dtIn);
          }
        }
  
      if ($this->db->transStatus() === FALSE) {
          $this->db->transRollback();
          return FALSE;
      } else {
          $this->db->transCommit();
          return TRUE;
      }
    } catch (\Throwable $th) {
      dd($th);
      $this->db->transRollback();
      return FALSE;
    }
  }

  function update($id, $data, $detail){
    try {
      $now = $this->dnow;
      $user_id = $this->get_userid();

      $this->db->transBegin();

        $this->mgaji->updateRecord($this->mgaji->table, $data, 'id', $id);
        
        if(!empty($detail)){
          $detail_id = [];
          
          foreach ($detail as $r) {
              $dtIn["id_sdm_gaji"] = $id; 
              $dtIn["id_karyawan"] = $r["id_karyawan"]; 
              $dtIn["nip"] = $r["nip"]; 
              $dtIn["posisi"] = $r["posisi"]; 
              $dtIn["hadir"] = $r["hadir"]; 
              $dtIn["izin"] = $r["izin"]; 
              $dtIn["sakit"] = $r["sakit"]; 
              $dtIn["alpha"] = $r["alpha"]; 
              $dtIn["terlambat"] = $r["terlambat"]; 
              $dtIn["gaji_harian"] = $r["gaji_jam"]; 
              $dtIn["lembur"] = $r["lembur"]; 
              $dtIn["lembur_we"] = $r["lembur_we"]; 
              if (isset($r["premi"])) {
                $dtIn["premi"] = $r["premi"]; 
              }
              $dtIn["uang_lembur"] = $r["uang_lembur"]; 
              $dtIn["gaji"] = $r["total"]; 
              $dtIn["bonus"] = $r["bonus"]; 
              $dtIn["bonus_keterangan"] = $r["bonus_keterangan"]; 
              $dtIn["potongan"] = $r["potongan"]; 
              $dtIn['durasi_kerja'] = $r['jam_kerja'];
              
              if(empty($r["id"])){
                $dtIn["active"] = 1;
                $dtIn["created_by"] = $user_id;
                $dtIn["created_at"] = $now;
                $detail_id[] = $this->mgaji->insertRecordGetid($this->mgaji->table2, $dtIn);
              }else{
                $dtIn["active"] = 1;
                $dtIn["updated_by"] = $user_id;
                $dtIn["updated_at"] = $now;
                $this->mgaji->updateRecord($this->mgaji->table2, $dtIn, 'id', $r["id"]);

                $detail_id[] = $r["id"];
              }
          }

          if(!empty($detail_id)){
            $updd['active'] = 0;
            $updd["updated_by"] = $user_id;
            $updd["updated_at"] = $now;
            
            $this->db->table($this->mgaji->table2)
                     ->where('id_sdm_gaji', $id) // Kondisi pertama
                     ->whereNotIn('id', $detail_id) // Kondisi kedua
                     ->update($updd);
          }
        }
  
      if ($this->db->transStatus() === FALSE) {
          $this->db->transRollback();
          return FALSE;
      } else {
          $this->db->transCommit();
          return TRUE;
      }
    } catch (\Throwable $th) {
      //throw $th;
      dd($th);
    }
  }

  function getDataPenggajian(){
    $tgl_mulai = $this->request->getPost('tgl_mulai');
    $tgl_akhir = $this->request->getPost('tgl_akhir');
    $type = $this->request->getPost('type');
    $status = false;
    $msg = "Laporan Penggajian tidak ditemukan !";
    $data = [];


    $params['tgl_mulai'] = \fdate_ind_to_eng($tgl_mulai);
    $params['tgl_akhir'] = \fdate_ind_to_eng($tgl_akhir);
    $params['type'] = $type;
    $data_laporan = $this->mabsen->laporan_penggajian($params);
    // print_r($data_laporan);exit;
    if(!empty($data_laporan)){
      for ($i=0; $i < count($data_laporan); $i++) { 
        if ($type == 2) {
          $data_laporan[$i]->gaji_jam = $data_laporan[$i]->harga_total;
        }
        $data_laporan[$i]->uang_lembur = $data_laporan[$i]->gaji_lembur + $data_laporan[$i]->gaji_lembur_we;

        $data_laporan[$i]->jam_kerja = round($data_laporan[$i]->jam_kerja);

       
        
        if ($data_laporan[$i]->izin == "0" && $data_laporan[$i]->sakit == "0" && $data_laporan[$i]->alpha == "0" && $data_laporan[$i]->terlambat == "0") {
           $data_laporan[$i]->premi = $data_laporan[$i]->premi;
        }
        else {
           $data_laporan[$i]->premi = 0;
        }

        if ($data_laporan[$i]->premi == null) {
          $data_laporan[$i]->premi = 0;
        }

        $data_laporan[$i]->total = ($data_laporan[$i]->uang_lembur + $data_laporan[$i]->gaji_jam + $data_laporan[$i]->bonus + $data_laporan[$i]->premi) - $data_laporan[$i]->potongan;
      
      }
      $status = true;
      $msg = "Laporan Penggajian ditemukan !";
      $data = $data_laporan;
    }



    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = $data;
    return $this->response->setJSON($build_array);
  }

  function printSlip_gaji(){
    $id = $this->request->getGet('data_id');

    $id = decrypt($id);

    $stdData = $this->mgaji->getData($id);  
    $stdData->periode_awal  = fdate_eng_to_ind($stdData->periode_awal);
    $stdData->periode_akhir  = fdate_eng_to_ind($stdData->periode_akhir);
    $this->data['row'] = $stdData;

    $params_det['id_sdm_gaji'] = $id;
    $list = $this->mgaji->getDataDet(null,0, 9999, 0, 0, $params_det);  

    for ($i=0; $i < !empty($list) ; $i++) { 
      $list[$i]->gaji_jam = $list[$i]->gaji_harian;
      $list[$i]->total = $list[$i]->gaji;
      $i++;
    }
    // dd($list);
    $this->data['detail'] = $list;
    return view($this->views.'\vprint_sdm_all', $this->data);
  }


  function printSlip_gaji_karyawan(){
    $id = $this->request->getGet('data_id');
    $nip = $this->request->getGet('nip');

    $id = decrypt($id);
    
    $stdData = $this->mgaji->getData($id);  
    
    $stdData->periode_awal  = fdate_eng_to_ind($stdData->periode_awal);
    $stdData->periode_akhir  = fdate_eng_to_ind($stdData->periode_akhir);
    $this->data['row'] = $stdData;

    $params_det['id_sdm_gaji'] = $id;
    $params_det['nip'] = $nip;
    $this->data['detail'] = $this->mgaji->getDataDet(null,0, 9999, 0, 0, $params_det);  
    return view($this->views.'\vprint_sdm_kar', $this->data);
  }


  
  function printSlip_gaji_all(){
    $id = $this->request->getGet('data_id');

    $id = decrypt($id);

    $stdData = $this->mgaji->getData($id);  
    $stdData->periode_awal  = fdate_eng_to_ind($stdData->periode_awal);
    $stdData->periode_akhir  = fdate_eng_to_ind($stdData->periode_akhir);
    $this->data['row'] = $stdData;

    $params_det['id_sdm_gaji'] = $id;
    $this->data['detail'] = $this->mgaji->getDataDet(null,0, 9999, 0, 0, $params_det);  
   
    return view($this->views.'\vprint_sdm_kar', $this->data);
  }

  public function deactivate($id = NULL)
    {
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }
        
        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to($this->urlv);
        }
        $data = ['active' => 0];
        $deactivate = $this->mgaji->updateRecord($this->mgaji->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Data Ukuran Dinonaktifkan");        
            $this->session->setFlashdata('message', "Data Ukuran berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Ukuran gagal di Hapus !");
        }
		return redirect()->to($this->urlv);
    }

    public function delete($id = NULL)
    {
        if (!$this->auth->loggedIn() OR (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
			throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
		    return redirect()->to($this->urlv);
        }
        
        $data = ['active' => 0];
        $res = $this->mgaji->updateRecord($this->mgaji->table, $data, 'id', $id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id,$this->MOD_ALIAS,$id,"Penggajian Dihapus");        
            $this->session->setFlashdata('message', "Penggajian berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Penggajian gagal dihapus");
        }
		return redirect()->to($this->urlv);
    }

  function _get_message($msg_type, $message = '', $mode = 'success', $icons = 'check', $fadeOut = true)
  {
      $title = '';
      switch ($msg_type) {
          //--== SUCCESS
          case 'SUCCESS_INSERTED':
              $title = 'Tambah Berhasil';
              $message = 'Penambahan data berhasil dilakukan.';
              $icons = 'check';
              break;

          case 'SUCCESS_UPDATED':
              $title = 'Update Berhasil';
              $message = 'Pembaharuan data berhasil dilakukan.';
              $icons = 'check';
              break;

          case 'SUCCESS_DELETED':
              $title = 'Hapus Berhasil';
              $message = 'Penghapusan data berhasil dilakukan.';
              $icons = 'check';
              break;

          case 'SUCCESS_ACTIVATED':
              $title = 'Mengaktifkan Berhasil';
              $message = 'Pengaktifan data berhasil dilakukan.';
              $icons = 'check';
              break;

          case 'SUCCESS_DEACTIVATED':
              $title = 'Menonaktifkan Berhasil';
              $message = 'Penonaktifan data berhasil dilakukan.';
              $icons = 'check';
              break;

          //---== FAILED
          case 'FAILED_INSERTED':
              $title = 'Tambah Gagal';
              $message = 'Penambahan data gagal !';
              $mode = 'danger';
              $icons = '';
              break;

          case 'FAILED_UPDATED':
              $title = 'Update Gagal';
              $message = 'Pembaharuan data gagal !';
              $mode = 'danger';
              $icons = '';
              break;

          case 'FAILED_DELETED':
              $title = 'Hapus Gagal';
              $message = 'Penghapusan data gagal !';
              $mode = 'danger';
              $icons = '';
              break;

          case 'FAILED_ACTIVATED':
              $title = 'Mengaktifkan Gagal';
              $message = 'Pengaktifan data gagal !';
              $mode = 'danger';
              $icons = '';
              break;

          case 'FAILED_DEACTIVATED':
              $title = 'Menonaktifkan Gagal';
              $message = 'Penonaktifan data gagal !';
              $mode = 'danger';
              $icons = '';
              break;

          case 'ERROR_VALIDATION':
              $title = '';
              $mode = 'danger';
              $icons = '';
              $fadeOut = false;
              break;
      }

      $html = message_box($title, $message, $mode, $icons, $fadeOut);
      return $html;
  }

  public function printSlipExcel_gaji(){

        $fileName = "list-slip-gaji.xlsx";

        $id = $this->request->getGet('data_id');

        $id = decrypt($id);

        $stdData = $this->mgaji->getData($id);  
        $params_det['id_sdm_gaji'] = $id;
        $list = $this->mgaji->getDataDet(null,0, 9999, 0, 0, $params_det);  

        for ($i=0; $i < !empty($list) ; $i++) { 
          $list[$i]->gaji_jam = $list[$i]->gaji_harian;
          $list[$i]->total = $list[$i]->gaji;
          $i++;
        }

        $results = $list;
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $stdData->periode_awal))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $stdData->periode_akhir))));
        $gets->getStyle('K2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('K2', 'Slip Gaji Karyawan Periode '. $title)

               ->setCellValue('A4', 'NIK')
               ->setCellValue('B4', 'NAMA')
               ->setCellValue('C4', 'POSISI')
               ->setCellValue('D4', 'HADIR')
               ->setCellValue('E4', 'IZIN')
               ->setCellValue('F4', 'SAKIT')
               ->setCellValue('G4', 'JAM KERJA')
               ->setCellValue('H4', 'GAJI/UPAH')
               ->setCellValue('I4', 'SAMPLE/PERBAIKAN')
               ->setCellValue('J4', 'LEMBUR HK')
               ->setCellValue('K4', 'LEMBUR HL')
               ->setCellValue('L4', 'PREMI KEHADIRAN')
               ->setCellValue('M4', 'LEMBUR')
               ->setCellValue('N4', 'PENAMBAHAN')
               ->setCellValue('O4', 'POTONGAN')
               ->setCellValue('P4', 'GAJI/UPAH');

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
            
        $gets->getStyle('A4:P4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('K2:P2');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('K2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(45);
          $gets->getColumnDimension('C')->setWidth(35);
          $gets->getColumnDimension('D')->setWidth(15);
          $gets->getColumnDimension('E')->setWidth(15);
          $gets->getColumnDimension('F')->setWidth(15);
          $gets->getColumnDimension('G')->setWidth(15);
          $gets->getColumnDimension('H')->setWidth(30);
          $gets->getColumnDimension('I')->setWidth(20);
          $gets->getColumnDimension('J')->setWidth(15);
          $gets->getColumnDimension('K')->setWidth(15);
          $gets->getColumnDimension('L')->setWidth(20);
          $gets->getColumnDimension('M')->setWidth(20);
          $gets->getColumnDimension('N')->setWidth(20);
          $gets->getColumnDimension('O')->setWidth(20);
          $gets->getColumnDimension('P')->setWidth(30);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:P4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:P4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L','M','N','O','P'
        );

        for ($i=0; $i < 16 ; $i++) { 

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
        $length_sub = $ix;

        if(!empty($results)){
            $length += count($results);
        }

        $bckColor = "F2F2F2";

        $jam_kerja = 0;
        $gaji_harian = 0;
        $sample = 0;
        $lembur = 0;
        $lembur_we = 0;
        $uang_lembur = 0;
        $bonus = 0;
        $potongan = 0;
        $total_pendapatan_total = 0;
        $premi = 0;
        
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $gaji = !empty($r) ? $r->gaji_harian : 0;
            $lembur = !empty($r) ? $r->uang_lembur  : 0;
            $bonus = !empty($r) ? $r->bonus  : 0;
            

            $jml_pendapatan = $gaji + $lembur + $bonus + $r->premi;
            $potongan = !empty($r) ? $r->potongan  : 0;
            $total_pendapatan = $jml_pendapatan - $potongan;
            

            $jam_kerja = $jam_kerja + $r->jam_kerja;
            $gaji_harian = $gaji_harian + $r->gaji_harian;
            $sample = $sample + $r->jml_sample;
            $lembur = $lembur + $r->lembur;
            $lembur_we = $lembur_we + $r->lembur_we;
            $uang_lembur = $uang_lembur + $r->uang_lembur;
            $bonus = $bonus + $r->bonus;
            $potongan = $potongan + $r->potongan;
            $total_pendapatan_total = $total_pendapatan_total + $total_pendapatan;
            $premi = $premi + $r->premi;

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->nip) ? $r->nip : "-")
                    ->setCellValue('B'.$ix, !empty($r->full_name) ? $r->full_name : "-")
                    ->setCellValue('C'.$ix, !empty($r->posisi) ? $r->posisi : "-")
                    ->setCellValue('D'.$ix, !empty($r->hadir) ? $r->hadir : 0)
                    ->setCellValue('E'.$ix, !empty($r->izin) ? $r->izin : 0)
                    ->setCellValue('F'.$ix, !empty($r->sakit) ? $r->sakit : 0)
                    ->setCellValue('G'.$ix, !empty($r->jam_kerja) ? $r->jam_kerja : 0)
                    ->setCellValue('H'.$ix, !empty($r->gaji_harian) ? $r->gaji_harian : 0)
                    ->setCellValue('I'.$ix, !empty($r->jml_sample) ? $r->jml_sample : 0)
                    ->setCellValue('J'.$ix, !empty($r->lembur) ? $r->lembur : 0)
                    ->setCellValue('K'.$ix, !empty($r->lembur_we) ? $r->lembur_we : 0)
                    ->setCellValue('L'.$ix, !empty($r->premi) ? $r->premi : 0)
                    ->setCellValue('M'.$ix, !empty($r->uang_lembur) ? $r->uang_lembur : 0)
                    ->setCellValue('N'.$ix, !empty($r->bonus) ? $r->bonus : 0)
                    ->setCellValue('O'.$ix, !empty($r->potongan) ? $r->potongan : 0)
                    ->setCellValue('P'.$ix, !empty($total_pendapatan) ? $total_pendapatan : 0);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':P'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("H" . $ix .":I" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            $sheets->getActiveSheet()->getStyle("K" . $ix .":P" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':F'. $length);
        
        $gets->getStyle('A'.$length.':P'.$length)->applyFromArray($stylexArrayFooter);
        
       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('G'.$length, $jam_kerja);

       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('H'.$length, $gaji_harian);

       $gets->getStyle("H" . $length .":I" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');

       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('I'.$length, $sample);

       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('J'.$length, $lembur);

       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('K'.$length, $lembur_we);
        
        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('L'.$length, $premi);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('M'.$length, $uang_lembur);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('N'.$length, $bonus);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('O'.$length, $potongan);
        $sheets->setActiveSheetIndex(0)

                    ->setCellValue('P'.$length, $total_pendapatan_total);

        $gets->getStyle("K" . $length .":P" . $length )->getNumberFormat()
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
