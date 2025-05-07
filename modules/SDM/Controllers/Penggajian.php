<?php

namespace Modules\SDM\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Referensi\Models\KaryawanModel;
use Modules\SDM\Models\Mabsensi;
use Modules\SDM\Models\Mpenggajian;

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
                    
                    $this->mgaji->deleteRecordMultipleColumn($this->mgaji->table2, ["id_sdm_gaji" => $id]);
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
              dd($this->validation->listErrors());
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
}
