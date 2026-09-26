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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Modules\Referensi\Models\KaryawanModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\PerusahaanModel;

class Penggajian extends BaseController
{
  protected $views = '\Modules\SDM\Views';
  protected $urlv  = 'sdm/penggajian';
  
  protected $mabsen;
  protected $mkaryawan;
  protected $mgaji;
  protected $dnow;
  protected $mPerusahaan;

  function __construct()
  {
      $this->MOD_ALIAS = "MOD_SDM_PENGGAJIAN";
      $this->mabsen = new Mabsensi();
      $this->mabsen = new Mabsensi();
      $this->mkaryawan = new KaryawanModel();
      $this->mgaji = new Mpenggajian();
      $this->mPerusahaan = new PerusahaanModel;
      $this->dnow        = date('Y-m-d H:i:s');
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
        return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Penggajian";

    $sortPerusahaan = [
            [
                'field' => 'nama_perusahaan',
                'dir' => 'ASC'
            ]
        ];
    $this->data['superadmin'] = true;
    $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
    $this->data['user_perusahaan'] = null;
    
    return view($this->views . '\penggajian_list', $this->data);
  }

  public function lists()
  {
      $start   = $this->request->getPost('start');
      $limit   = $this->request->getPost('length');
      $filters = $this->request->getPost('filter');
      $order   = $this->request->getPost('order');
      $id_perusahaan = $this->request->getPost('id_perusahaan');

    $params = [
        'id_perusahaan' => $id_perusahaan
    ];
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
              'nama_perusahaan' => $row->nama_perusahaan,
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
      $stdData->id_perusahaan = null;
      $stdData->status = 1;

      $Ldetail = "";

      $is_read = false;
      $isFinal = false;

      if($id) {
        $stdData = $this->mgaji->getData($id);  
        $stdData->periode_awal  = fdate_eng_to_ind($stdData->periode_awal);
        $stdData->periode_akhir  = fdate_eng_to_ind($stdData->periode_akhir);
        $stdData->type  = $stdData->type;
        $stdData->id_perusahaan = $stdData->id_perusahaan;
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
              $is_detail[$i]->total = ($r->gaji_harian + $r->uang_lembur + $r->bonus + $r->premi + $r->jml_sample) - $r->potongan;
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
            $stdData->id_perusahaan = trim($this->request->getPost('id_perusahaan'));

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
                $dataIn["id_perusahaan"] = $stdData->id_perusahaan;
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
    $sortPerusahaan = [
    [
        'field' => 'nama_perusahaan',
        'dir' => 'ASC'
        ]
    ];
    $this->data['superadmin'] = true;
    $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
    $this->data['user_perusahaan'] = null;
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
              $dtIn['jml_sample'] = $r['jml_sample'];
              
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
    $id_perusahaan = $this->request->getPost('id_perusahaan');
    $status = false;
    $msg = "Laporan Penggajian tidak ditemukan !";
    $data = [];


    $params['tgl_mulai'] = \fdate_ind_to_eng($tgl_mulai);
    $params['tgl_akhir'] = \fdate_ind_to_eng($tgl_akhir);
    $params['type'] = $type;
    $params['id_perusahaan'] = $id_perusahaan;
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

        $data_laporan[$i]->gaji_jam = round((float)($data_laporan[$i]->gaji_jam ?? 0));
        $data_laporan[$i]->uang_lembur = round((float)($data_laporan[$i]->uang_lembur ?? 0));
        $data_laporan[$i]->bonus = round((float)($data_laporan[$i]->bonus ?? 0));
        $data_laporan[$i]->jml_sample = round((float)($data_laporan[$i]->jml_sample ?? 0));
        $data_laporan[$i]->potongan = round((float)($data_laporan[$i]->potongan ?? 0));
        $data_laporan[$i]->premi = round((float)($data_laporan[$i]->premi ?? 0));

        $data_laporan[$i]->total = round(($data_laporan[$i]->uang_lembur + $data_laporan[$i]->gaji_jam + $data_laporan[$i]->bonus + $data_laporan[$i]->premi + $data_laporan[$i]->jml_sample) - $data_laporan[$i]->potongan);
      
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


  
  function printRekap_gaji(){
    $id = $this->request->getGet('data_id');
    $id = decrypt($id);

    $stdData = $this->mgaji->getData($id);  
    $stdData->periode_awal  = fdate_eng_to_ind($stdData->periode_awal);
    $stdData->periode_akhir  = fdate_eng_to_ind($stdData->periode_akhir);
    $this->data['row'] = $stdData;

    $params_det['id_sdm_gaji'] = $id;
    $list = $this->mgaji->getDataDet(null, 0, 9999, null, null, $params_det);  

    $this->data['detail'] = $list;
    return view($this->views.'\vprint_sdm_rekap', $this->data);
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
               ->setCellValue('D4', 'BANK')
               ->setCellValue('E4', 'NO REKENING')
               ->setCellValue('F4', 'EMAIL')
               ->setCellValue('G4', 'HADIR')
               ->setCellValue('H4', 'IZIN')
               ->setCellValue('I4', 'SAKIT')
               ->setCellValue('J4', 'JAM KERJA')
               ->setCellValue('K4', 'GAJI/UPAH')
               ->setCellValue('L4', 'SAMPLE/PERBAIKAN')
               ->setCellValue('M4', 'LEMBUR HK')
               ->setCellValue('N4', 'LEMBUR HL')
               ->setCellValue('O4', 'PREMI KEHADIRAN')
               ->setCellValue('P4', 'LEMBUR')
               ->setCellValue('Q4', 'PENAMBAHAN')
               ->setCellValue('R4', 'POTONGAN')
               ->setCellValue('S4', 'GAJI/UPAH');

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
        $gets->getStyle('A4:R4')->applyFromArray($styleArray_header);
        $sheets->getActiveSheet()->mergeCells('K2:R2');

        // set Center title
        $sheets->getActiveSheet()->getStyle('K2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(45);
          $gets->getColumnDimension('C')->setWidth(35);
          $gets->getColumnDimension('D')->setWidth(20);
          $gets->getColumnDimension('E')->setWidth(20);
          $gets->getColumnDimension('F')->setWidth(25);
          $gets->getColumnDimension('G')->setWidth(15);
          $gets->getColumnDimension('H')->setWidth(15);
          $gets->getColumnDimension('I')->setWidth(15);
          $gets->getColumnDimension('J')->setWidth(15);
          $gets->getColumnDimension('K')->setWidth(30);
          $gets->getColumnDimension('L')->setWidth(20);
          $gets->getColumnDimension('M')->setWidth(15);
          $gets->getColumnDimension('N')->setWidth(15);
          $gets->getColumnDimension('O')->setWidth(20);
          $gets->getColumnDimension('P')->setWidth(20);
          $gets->getColumnDimension('Q')->setWidth(20);
          $gets->getColumnDimension('R')->setWidth(20);
          $gets->getColumnDimension('S')->setWidth(30);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:S4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:S4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('List Gaji');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L','M','N','O','P','Q','R','S'
        );

        for ($i=0; $i < 19 ; $i++) { 

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
        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $gaji = !empty($r) ? $r->gaji_harian : 0;
            $lembur = !empty($r) ? $r->uang_lembur  : 0;
            $bonus = !empty($r) ? $r->bonus  : 0;
            

            $jml_pendapatan = $gaji + $lembur + $bonus + $r->premi + $r->jml_sample;
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
                    ->setCellValue('D'.$ix, !empty($r->nama_bank) ? $r->nama_bank : "-")
                    ->setCellValue('E'.$ix, !empty($r->no_rekening) ? $r->no_rekening : "-")
                    ->setCellValue('F'.$ix, !empty($r->email) ? $r->email : "-")
                    ->setCellValue('G'.$ix, !empty($r->hadir) ? $r->hadir : 0)
                    ->setCellValue('H'.$ix, !empty($r->izin) ? $r->izin : 0)
                    ->setCellValue('I'.$ix, !empty($r->sakit) ? $r->sakit : 0)
                    ->setCellValue('J'.$ix, !empty($r->jam_kerja) ? $r->jam_kerja : 0)
                    ->setCellValue('K'.$ix, !empty($r->gaji_harian) ? $r->gaji_harian : 0)
                    ->setCellValue('L'.$ix, !empty($r->jml_sample) ? $r->jml_sample : 0)
                    ->setCellValue('M'.$ix, !empty($r->lembur) ? $r->lembur : 0)
                    ->setCellValue('N'.$ix, !empty($r->lembur_we) ? $r->lembur_we : 0)
                    ->setCellValue('O'.$ix, !empty($r->premi) ? $r->premi : 0)
                    ->setCellValue('P'.$ix, !empty($r->uang_lembur) ? $r->uang_lembur : 0)
                    ->setCellValue('Q'.$ix, !empty($r->bonus) ? $r->bonus : 0)
                    ->setCellValue('R'.$ix, !empty($r->potongan) ? $r->potongan : 0)
                    ->setCellValue('S'.$ix, !empty($total_pendapatan) ? $total_pendapatan : 0);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':S'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("K" . $ix .":L" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0');
            $sheets->getActiveSheet()->getStyle("N" . $ix .":S" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0');

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        
        $gets->getStyle('A'.$length.':S'.$length)->applyFromArray($stylexArrayFooter);

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('J' . $length, '=SUM(J' . $startRow . ':J' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('K' . $length, '=SUM(K' . $startRow . ':K' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('L' . $length, '=SUM(L' . $startRow . ':L' . $length-1 . ')');

       $gets->getStyle("K" . $length .":L" . $length )->getNumberFormat()
               ->setFormatCode('#,##0');

       $sheets->setActiveSheetIndex(0)->setCellValue('M' . $length, '=SUM(M' . $startRow . ':M' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)->setCellValue('N' . $length, '=SUM(N' . $startRow . ':N' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)->setCellValue('O' . $length, '=SUM(O' . $startRow . ':O' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)->setCellValue('P' . $length, '=SUM(P' . $startRow . ':P' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)->setCellValue('Q' . $length, '=SUM(Q' . $startRow . ':Q' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)->setCellValue('R' . $length, '=SUM(R' . $startRow . ':R' . $length-1 . ')');
       $sheets->setActiveSheetIndex(0)->setCellValue('S' . $length, '=SUM(S' . $startRow . ':S' . $length-1 . ')');

        $gets->getStyle("N" . $length .":S" . $length )->getNumberFormat()
               ->setFormatCode('#,##0');
        
        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); // download file 
        exit;
    }

    public function printRekapExcel_gaji(){
        $fileName = "rekap-penggajian-".date('Ymd-His').".xlsx";

        $id = $this->request->getGet('data_id');
        $id = decrypt($id);

        $stdData = $this->mgaji->getData($id);  
        $params_det['id_sdm_gaji'] = $id;
        $list = $this->mgaji->getDataDet(null, 0, 9999, null, null, $params_det);  

        $upahList = [];
        $kasbonList = [];

        foreach ($list as $r) {
            if ((float)($r->gaji ?? 0) > 0) {
                $upahList[] = $r;
            }
            if ((float)($r->potongan ?? 0) > 0) {
                $kasbonList[] = $r;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Penggajian');

        $blueHeader = 'BDD7EE';
        $pinkHeader = 'F8CBAD';

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '595959'],
                ],
            ],
        ];

        // Set Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(4);
        $sheet->getColumnDimension('E')->setWidth(5);
        $sheet->getColumnDimension('F')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(18);

        // Headers (Row 1)
        $sheet->setCellValue('A1', '☐');
        $sheet->setCellValue('B1', 'UPAH HARIAN & MESIN');
        $sheet->setCellValue('C1', date('d-m-y'));

        $sheet->setCellValue('E1', '☐');
        $sheet->setCellValue('F1', 'PEMBAYARAN KASBON');
        $sheet->setCellValue('G1', '');

        // Header formatting
        $sheet->getStyle('A1:C1')->getFont()->setName('Calibri')->setSize(11)->setBold(true);
        $sheet->getStyle('E1:G1')->getFont()->setName('Calibri')->setSize(11)->setBold(true);

        $sheet->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($blueHeader);
        $sheet->getStyle('E1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($pinkHeader);

        $sheet->getStyle('A1:C1')->applyFromArray($thinBorder);
        $sheet->getStyle('E1:G1')->applyFromArray($thinBorder);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Populate Upah Rows
        $rowU = 2;
        foreach ($upahList as $u) {
            $sheet->setCellValue('A' . $rowU, '☐');
            $sheet->setCellValue('B' . $rowU, strtoupper(!empty($u->full_name) ? $u->full_name : $u->nip));
            $sheet->setCellValue('C' . $rowU, (float)$u->gaji);

            $sheet->getStyle('A' . $rowU)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowU)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $rowU . ':C' . $rowU)->applyFromArray($thinBorder);
            $rowU++;
        }

        // Total Upah Row
        $sheet->setCellValue('A' . $rowU, '☐');
        $sheet->setCellValue('B' . $rowU, 'TOTAL');
        $sheet->setCellValue('C' . $rowU, '=SUM(C2:C' . ($rowU - 1) . ')');

        $sheet->getStyle('A' . $rowU)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $rowU . ':C' . $rowU)->getFont()->setName('Calibri')->setSize(11)->setBold(true);
        $sheet->getStyle('C' . $rowU)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $rowU . ':C' . $rowU)->applyFromArray($thinBorder);

        // Populate Kasbon Rows
        $rowK = 2;
        foreach ($kasbonList as $k) {
            $sheet->setCellValue('E' . $rowK, '☐');
            $sheet->setCellValue('F' . $rowK, strtoupper(!empty($k->full_name) ? $k->full_name : $k->nip));
            $sheet->setCellValue('G' . $rowK, (float)$k->potongan);

            $sheet->getStyle('E' . $rowK)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowK)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E' . $rowK . ':G' . $rowK)->applyFromArray($thinBorder);
            $rowK++;
        }

        // Total Transfer Row for Kasbon
        $sheet->setCellValue('E' . $rowK, '☐');
        $sheet->setCellValue('F' . $rowK, 'TOTAL TRANSFER');
        $sheet->setCellValue('G' . $rowK, '=SUM(G2:G' . ($rowK - 1) . ')');

        $sheet->getStyle('E' . $rowK)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $rowK . ':G' . $rowK)->getFont()->setName('Calibri')->setSize(11)->setBold(true);
        $sheet->getStyle('G' . $rowK)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $rowK . ':G' . $rowK)->applyFromArray($thinBorder);

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output');
        exit;
    }

    public function syncTbmpTpo()
    {
        if (!$this->auth->loggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $tglFromRaw   = trim($this->request->getPost('tgl_mulai') ?? '');
        $tglToRaw     = trim($this->request->getPost('tgl_akhir') ?? '');
        $idPerusahaan = (int) $this->request->getPost('id_perusahaan');

        if (empty($tglFromRaw) || empty($tglToRaw)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Harap tentukan periode tanggal awal dan akhir!']);
        }
        if (empty($idPerusahaan)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Harap pilih perusahaan terlebih dahulu!']);
        }

        $tglAwal  = fdate_ind_to_eng($tglFromRaw);
        $tglAkhir = fdate_ind_to_eng($tglToRaw);

        $db = \Config\Database::connect();

        // 1. Filter dokumen Approved (status = 1) pada rentang tanggal & perusahaan
        $builder = $db->table('trans_barang_header tbh');
        $builder->select('tbh.id, tbh.kode_transaksi, tbh.id_proses, tbh.id_cmt, tbh.tanggal');
        $builder->where('tbh.status', 1);
        $builder->where('tbh.tanggal >=', $tglAwal);
        $builder->where('tbh.tanggal <=', $tglAkhir);

        if ($idPerusahaan == 2) {
            $builder->like('tbh.kode_transaksi', 'BTMC', 'after');
        } else {
            $builder->notLike('tbh.kode_transaksi', 'BTMC', 'after');
        }

        $headers = $builder->get()->getResult();

        // Hitung juga jumlah dokumen TBMP yang masih DRAFT (status = 0)
        $builderDraft = $db->table('trans_barang_header tbh');
        $builderDraft->where('tbh.status', 0);
        $builderDraft->where('tbh.tanggal >=', $tglAwal);
        $builderDraft->where('tbh.tanggal <=', $tglAkhir);

        if ($idPerusahaan == 2) {
            $builderDraft->like('tbh.kode_transaksi', 'BTMC', 'after');
        } else {
            $builderDraft->notLike('tbh.kode_transaksi', 'BTMC', 'after');
        }

        $draftCount = $builderDraft->countAllResults();

        if (empty($headers)) {
            return $this->response->setJSON([
                'status'        => true,
                'message'       => "Tidak ada dokumen BTM/BTMC Approved pada periode $tglFromRaw s.d $tglToRaw.",
                'total_dokumen' => 0,
                'synced_count'  => 0,
                'draft_count'   => $draftCount
            ]);
        }

        $syncedCount = 0;
        $totalDoc = count($headers);

        foreach ($headers as $h) {
            $tbmpSummary = $db->table('trans_barang_masuk_produksi')
                ->select('SUM(qty) as total_qty, SUM(qty * COALESCE(harga, 0)) as total_nilai')
                ->where('id_header', $h->id)
                ->get()->getRow();

            if (empty($tbmpSummary) || (float)$tbmpSummary->total_qty <= 0) {
                continue;
            }

            $totalQtyTbmp   = (float)$tbmpSummary->total_qty;
            $totalNilaiTbmp = (float)$tbmpSummary->total_nilai;

            $tpoRows = $db->table('trans_produksi_operator')
                ->where('kode_transaksi', $h->kode_transaksi)
                ->get()->getResult();

            if (empty($tpoRows)) {
                $tbmpDetails = $db->table('trans_barang_masuk_produksi')
                    ->select('kode_sales_order, kode_ukuran, SUM(qty) as qty, AVG(COALESCE(harga, 0)) as harga, SUM(qty * COALESCE(harga, 0)) as total')
                    ->where('id_header', $h->id)
                    ->groupBy('kode_sales_order, kode_ukuran')
                    ->get()->getResult();

                foreach ($tbmpDetails as $det) {
                    $db->table('trans_produksi_operator')->insert([
                        'kode_transaksi' => $h->kode_transaksi,
                        'id_operator'    => $h->id_cmt,
                        'id_proses'      => $h->id_proses,
                        'tgl_transaksi'  => $h->tanggal,
                        'qty'            => (float)$det->qty,
                        'harga'          => (float)$det->harga,
                        'harga_total'    => (float)$det->total,
                        'active'         => 1,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'created_by'     => session()->get('user_id') ?? 1
                    ]);
                }
                $syncedCount++;
            } else {
                $totalQtyTpo   = 0;
                $totalNilaiTpo = 0;
                foreach ($tpoRows as $tp) {
                    $totalQtyTpo   += (float)$tp->qty;
                    $totalNilaiTpo += (float)$tp->harga_total;
                }

                if (abs($totalQtyTpo - $totalQtyTbmp) > 0.001 || abs($totalNilaiTpo - $totalNilaiTbmp) > 0.01) {
                    if (count($tpoRows) == 1) {
                        $db->table('trans_produksi_operator')
                            ->where('id', $tpoRows[0]->id)
                            ->update([
                                'qty'         => $totalQtyTbmp,
                                'harga'       => ($totalQtyTbmp > 0 ? ($totalNilaiTbmp / $totalQtyTbmp) : 0),
                                'harga_total' => $totalNilaiTbmp,
                                'updated_at'  => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        $tbmpDetails = $db->table('trans_barang_masuk_produksi')
                            ->select('kode_sales_order, kode_ukuran, SUM(qty) as qty, AVG(COALESCE(harga, 0)) as harga, SUM(qty * COALESCE(harga, 0)) as total')
                            ->where('id_header', $h->id)
                            ->groupBy('kode_sales_order, kode_ukuran')
                            ->get()->getResult();

                        $tmpl = $tpoRows[0];
                        $db->table('trans_produksi_operator')->where('kode_transaksi', $h->kode_transaksi)->delete();

                        foreach ($tbmpDetails as $det) {
                            $db->table('trans_produksi_operator')->insert([
                                'id_produksi'                => $tmpl->id_produksi,
                                'id_walkorder_proses_ukuran' => $tmpl->id_walkorder_proses_ukuran,
                                'id_ukuran'                  => $tmpl->id_ukuran,
                                'tgl_transaksi'              => $h->tanggal,
                                'id_operator'                => $h->id_cmt,
                                'qty'                        => (float)$det->qty,
                                'harga'                      => (float)$det->harga,
                                'harga_total'                => (float)$det->total,
                                'active'                     => 1,
                                'created_by'                 => session()->get('user_id') ?? 1,
                                'created_at'                 => date('Y-m-d H:i:s'),
                                'id_proses'                  => $h->id_proses,
                                'id_warna'                   => $tmpl->id_warna,
                                'kode_transaksi'             => $h->kode_transaksi,
                                'id_category'                => $tmpl->id_category,
                                'berat'                      => $tmpl->berat ?? 0,
                                'print_type'                 => $tmpl->print_type ?? 1
                            ]);
                        }
                    }
                    $syncedCount++;
                } else {
                    foreach ($tpoRows as $tp) {
                        if (((float)$tp->harga_total == 0) && (float)$tp->harga > 0 && (float)$tp->qty > 0) {
                            $db->table('trans_produksi_operator')
                                ->where('id', $tp->id)
                                ->update(['harga_total' => ((float)$tp->qty * (float)$tp->harga)]);
                            $syncedCount++;
                        }
                    }
                }
            }
        }

        // 2. Query Summary Per Karyawan / CMT setelah sinkronisasi (divalidasi sesuai ref_karyawan perusahaannya)
        $wherePerusahaanSql = ($idPerusahaan == 2) ? "tbh.kode_transaksi LIKE 'BTMC%'" : "tbh.kode_transaksi NOT LIKE 'BTMC%'";
        $whereKaryawanPerusahaan = ($idPerusahaan == 2) 
            ? "AND (rk.id_perusahaan = 2 OR (rk.id IS NULL AND rk2.id_perusahaan = 2))"
            : "AND (rk.id_perusahaan = 1 OR rk.id_perusahaan IS NULL OR (rk.id IS NULL AND (rk2.id_perusahaan = 1 OR rk2.id_perusahaan IS NULL)))";

        $summaryKaryawan = $db->query("
            SELECT 
                tbh.id_cmt as id_operator,
                COALESCE(rk.nip, rk2.nip, '-') as nip,
                COALESCE(ro.nama_operator, rk.full_name, rk2.full_name, 'Operator ' || tbh.id_cmt) as nama_karyawan,
                COALESCE(rk.posisi, rk2.posisi, '-') as posisi,
                SUM(tbmp.qty) as qty_tbmp,
                SUM(tbmp.qty * COALESCE(tbmp.harga, 0)) as nilai_tbmp
            FROM trans_barang_masuk_produksi tbmp
            JOIN trans_barang_header tbh ON tbh.id = tbmp.id_header
            LEFT JOIN ref_operator ro ON ro.id = tbh.id_cmt
            LEFT JOIN ref_karyawan rk ON rk.id_operator = tbh.id_cmt AND rk.active = 1
            LEFT JOIN ref_karyawan rk2 ON rk2.id = tbh.id_cmt AND rk2.active = 1
            WHERE tbh.status = 1
              AND tbh.tanggal >= '$tglAwal' AND tbh.tanggal <= '$tglAkhir'
              AND $wherePerusahaanSql
              $whereKaryawanPerusahaan
            GROUP BY tbh.id_cmt, rk.nip, rk2.nip, ro.nama_operator, rk.full_name, rk2.full_name, rk.posisi, rk2.posisi
            ORDER BY nilai_tbmp DESC
        ")->getResultArray();

        $grandTbmp = 0;
        $grandTpo = 0;

        foreach ($summaryKaryawan as &$row) {
            $tpoOp = $db->query("
                SELECT SUM(tpo.qty) as qty_tpo, SUM(tpo.harga_total) as nilai_tpo
                FROM trans_produksi_operator tpo
                JOIN trans_barang_header tbh ON tbh.kode_transaksi = tpo.kode_transaksi
                WHERE tbh.status = 1
                  AND tbh.id_cmt = {$row['id_operator']}
                  AND tbh.tanggal >= '$tglAwal' AND tbh.tanggal <= '$tglAkhir'
                  AND $wherePerusahaanSql
            ")->getRow();

            $row['qty_tpo']     = (float)($tpoOp->qty_tpo ?? 0);
            $row['nilai_tpo']   = (float)($tpoOp->nilai_tpo ?? 0);
            $row['nilai_tbmp']  = (float)$row['nilai_tbmp'];
            $diff               = abs($row['nilai_tpo'] - $row['nilai_tbmp']);
            $row['status']      = ($diff < 1) ? 'MATCH 100%' : 'Selisih Rp ' . number_format($diff, 0, ',', '.');

            $grandTbmp += $row['nilai_tbmp'];
            $grandTpo  += $row['nilai_tpo'];
        }

        // 3. Query Detail Dokumen TBMP yang masih Draft untuk Perusahaan terpilih (nama CMT persis seperti di BTM)
        $draftList = $db->query("
            SELECT 
                tbh.id,
                tbh.kode_transaksi,
                tbh.tanggal,
                COALESCE(ro.nama_operator, rk.full_name, rk2.full_name, 'Operator ' || tbh.id_cmt) as nama_cmt,
                COALESCE(p.nama, '-') as nama_proses,
                COALESCE(SUM(tbmp.qty), 0) as total_qty,
                COALESCE(SUM(tbmp.qty * COALESCE(tbmp.harga, 0)), 0) as total_nilai
            FROM trans_barang_header tbh
            LEFT JOIN trans_barang_masuk_produksi tbmp ON tbmp.id_header = tbh.id
            LEFT JOIN ref_operator ro ON ro.id = tbh.id_cmt
            LEFT JOIN ref_karyawan rk ON rk.id_operator = tbh.id_cmt AND rk.active = 1
            LEFT JOIN ref_karyawan rk2 ON rk2.id = tbh.id_cmt AND rk2.active = 1
            LEFT JOIN _jenis_proses_produksi p ON p.id = tbh.id_proses
            WHERE tbh.status = 0
              AND tbh.tanggal >= '$tglAwal' AND tbh.tanggal <= '$tglAkhir'
              AND $wherePerusahaanSql
            GROUP BY tbh.id, tbh.kode_transaksi, tbh.tanggal, ro.nama_operator, rk.full_name, rk2.full_name, tbh.id_cmt, p.nama
            ORDER BY tbh.tanggal ASC, tbh.kode_transaksi ASC
        ")->getResultArray();

        $draftCount = count($draftList);

        return $this->response->setJSON([
            'status'           => true,
            'message'          => "Sinkronisasi Berhasil!",
            'total_dokumen'    => $totalDoc,
            'synced_count'     => $syncedCount,
            'draft_count'      => $draftCount,
            'summary_karyawan' => $summaryKaryawan,
            'grand_tbmp'       => $grandTbmp,
            'grand_tpo'        => $grandTpo,
            'draft_list'       => $draftList
        ]);
    }
}