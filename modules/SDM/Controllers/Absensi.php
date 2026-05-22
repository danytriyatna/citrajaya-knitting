<?php

namespace Modules\SDM\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Referensi\Models\KaryawanModel;
use Modules\SDM\Models\Mabsensi;
use Modules\Referensi\Models\ShiftModel;

// user library spreadsheet for excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Absensi extends BaseController
{
  protected $views = '\Modules\SDM\Views';

  protected $mabsen;
  protected $mkaryawan;
  protected $mshift;


  function __construct()
  {
      $this->MOD_ALIAS = "MOD_SDM_ABSENSI";
      $this->mabsen = new Mabsensi();
      $this->mkaryawan = new KaryawanModel();
      $this->mshift = new ShiftModel(); 
      
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
        return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Absensi";
    $this->data['dnow'] = date('d-m-Y');
    return view($this->views . '\absensi_list', $this->data);
  }

  public function lists()
  {
      $start      = $this->request->getPost('start');
      $limit      = $this->request->getPost('length');
      $filters    = $this->request->getPost('filter');
      $order      = $this->request->getPost('sort');
      $tgl_absen      = $this->request->getPost('tgl_absen');

      // $params = [];

      $params['tgl_absen'] = \fdate_ind_to_eng($tgl_absen);

      $results = $this->mabsen->getData(null, $start, $limit, $order, $filters, $params);
      $totalfiltered = $this->mabsen->getDataCnt($filters, $params);
      $totaldata = $this->mabsen->getDataCnt(null, $params);
      $maxpage = ceil($totalfiltered / $limit);

      $build_array = array(
          "last_page" => $maxpage,
          "recordsTotal" => $totaldata,
          "recordsFiltered" => $totalfiltered,
          "data" => array()
      );

      foreach ($results as $row) {
          $id = encrypt($row->id);

          // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
          //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

          // nip
          // full_name
          // posisi
          // upah_lembur
          // upah_harian
          // upah_lembur_we

          // id_karyawan
          // posisi

          $status_kehadiran = "";
          $tanggal_merah = "<input type='checkbox' name='tanggal_merah[]' value='0'>";

          if($row->status_kehadiran == 1){
            $status_kehadiran = "Hadir";
          } else if($row->status_kehadiran == 2){
            $status_kehadiran = "Izin";
          } if($row->status_kehadiran == 3){
            $status_kehadiran = "Sakit";
          } if($row->status_kehadiran == 4){
            $status_kehadiran = "Tanpa Keterangan";
          }

          $status_lembur = "";
          if($row->status_lembur == 0){
            $status_lembur = "-";
          } else if($row->status_lembur == 1){
            $status_lembur = "Lembur Weekday";
          } if($row->status_lembur == 2){
            $status_lembur = "Lembur Weekend/Hari Libur";
          }

          $inHour = '';
          if(!empty($row->jam_masuk)){
            $inHour = date('H:i', strtotime($row->jam_masuk));
          }
          $outHour = '';
          if(!empty($row->jam_keluar)){
            $outHour = date('H:i', strtotime($row->jam_keluar));
          }
          if ($row->tanggal_merah == 1) {
            $tanggal_merah = "<input type='checkbox' name='tanggal_merah[]' value='1' checked>";
          }
              
          $tgl_absen = \fdate_eng_to_ind($row->tgl_absen);
          array_push(
              $build_array["data"],
              array(
                  "id"   => ($id),
                  "nip" => $row->nip,
                  "full_name" => $row->full_name,
                  "id_karyawan" => $row->id_karyawan,
                  "posisi" => $row->posisi,
                  "upah_lembur" => $row->upah_lembur,
                  "upah_harian" => $row->upah_harian,
                  "upah_lembur_we" => $row->upah_lembur_we,
                  "tgl_absen" => $tgl_absen,
                  "jam_masuk" => $inHour,
                  "jam_keluar" => $outHour,
                  "status_kehadiran" => $status_kehadiran,
                  "hari_hadir" => $row->hari_hadir,
                  "keterangan_kehadiran" => $row->keterangan_kehadiran,
                  "status_lembur" => $status_lembur,
                  "terlambat" => $row->terlambat,
                  "jml_lembur" => $row->jml_lembur,
                  "keterangan_lembur" => $row->keterangan_lembur,
                  "id_shift" => $row->id_shift,
                  "nama_shift" => $row->nama_shift,
                  "jadwal_masuk" => $row->jadwal_masuk,
                  "jadwal_pulang" => $row->jadwal_pulang,
                  "potongan" => $row->potongan,
                  "potongan_keterangan" => $row->potongan_keterangan,
                  "bonus" => $row->bonus,
                  "bonus_keterangan" => $row->bonus_keterangan,
                  "tanggal_merah" => $row->tanggal_merah,
              )
          );
      }
      return $this->response->setJSON($build_array);
  }

  public function generate_absen_karyawan(){
    $tgl_absen = $this->request->getPost('tgl_absen');

    $status = false;
    $msg = "Absensi karyawan tidak ditemukan !";
    $data = [];

    $data_karyawan = $this->mkaryawan->getData(null, 0, 9999);

    $params['tgl_absen'] = \fdate_ind_to_eng($tgl_absen);

    $dt_absen = $this->mabsen->getData(null, 0, 9999, null, null, $params);

    $dt_shift = $this->mshift->getData(1);
    if(!empty($data_karyawan ) && empty($dt_absen)){  
        $tgl_absen = fdate_ind_to_eng($tgl_absen);
        foreach ($data_karyawan  as $dt) {
          $isi = [];
          $isi['id_karyawan'] = $dt->id;
          $isi['posisi'] = $dt->posisi;
          $isi['id_shift'] = 1;
          $isi['status_kehadiran'] = 1;
          $isi['hari_hadir'] = 1;
          $isi['jam_masuk'] = ($dt_shift) ? $tgl_absen . ' ' . $dt_shift->jam_masuk : $tgl_absen . ' ' . '08:00';
          $isi['jam_keluar'] = ($dt_shift) ? $tgl_absen . ' ' .$dt_shift->jam_pulang : $tgl_absen . ' ' . '08:00';
          $isi['jadwal_masuk'] = ($dt_shift) ? $dt_shift->jam_masuk : '08:00';
          $isi['jadwal_pulang'] = ($dt_shift) ? $dt_shift->jam_pulang : '17:00';
          $isi['tgl_absen'] = fdate_ind_to_eng($tgl_absen);
          $this->mabsen->insertRecordGetid($this->mabsen->table, $isi);
        }

        $status = true;
        $msg = "Absensi karyawan tidak ditemukan !";
    }else{
      $msg = "Absensi pada tgl tesebut sudah ada !";
    }

    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = $data;
    return $this->response->setJSON($build_array);

  }

  function simpanData(){
    $tgl_absen = $this->request->getPost('tgl_absen');
    $data_list = $this->request->getPost('data_list');

    $status = false;
    $msg = "Absensi karyawan tidak ditemukan !";
    $data = [];

    if(!empty($data_list)){
      $data_list = json_decode($data_list, true);
      $tgl_absen = \fdate_ind_to_eng($tgl_absen);
      foreach ($data_list as $key => $x) {
        $id = \decrypt($x['id']);
        $status_kehadiran = null;
        $durasi_kerja = 8 * 60;
        $jadwal_masuk = "08:00";
        $jadwal_pulang = "17:00";
        $id_shift = 1;
        // ged data shift
        $prms['nama_shift'] = $x['nama_shift'];
        $dt_shift = $this->mshift->getData(null, 0, 1, null, null, $prms);
        if(!empty($dt_shift)){
          $dt_shift = $dt_shift[0]; 
          $id_shift = $dt_shift->id;
          $jadwal_masuk = $dt_shift->jam_masuk;
          $jadwal_pulang = $dt_shift->jam_pulang;
          $result1 = $this->getCircularTimeDiff($jadwal_masuk, $jadwal_pulang);
          $result2 = $this->getCircularTimeDiff($x['jam_masuk'], $x['jam_keluar']);

          // if ($result2['minutes'] > $result1['minutes']) {
          //   $durasi_kerja = $result1['minutes'];
          // }
          // else {
          //   $durasi_kerja = $result2['minutes'];
          // }

          // if($durasi_kerja >= 5){
          //   // $durasi_kerja = $durasi_kerja - 60;
          // } else if($durasi_kerja < 0){
          //   $durasi_kerja = 0;
          // }
          
          if(!empty($x['jam_masuk'])){
            // $jam_awal  = new DateTime($dt_shift->jam_masuk);
            // $jam_akhir = new DateTime($x['jam_keluar']);
            
            // $interval = $jam_awal->diff($jam_akhir);
            // $durasi_kerja = ($interval->h * 60) + $interval->i; // Konversi ke menit

            // $timestamp_masuk = strtotime($dt_shift->jam_masuk);
            // $timestamp_keluar = strtotime($x['jam_keluar']);
            $timestamp_masuk = strtotime($x['jam_masuk']);
            
            $time_jadwalmasuk = strtotime($dt_shift->jam_masuk);

            if($timestamp_masuk < $time_jadwalmasuk){
              $timestamp_masuk = $time_jadwalmasuk;
            }

            $timestamp_keluar = strtotime($dt_shift->jam_pulang);

            if($timestamp_masuk > $timestamp_keluar){
              $x_masuk = $timestamp_keluar;
              $x_keluar = $timestamp_masuk;
              
              $timestamp_keluar = $x_keluar;
              $timestamp_masuk = $x_masuk;
            }else if(!empty($x['jam_keluar'])){  
              if($timestamp_keluar > $x['jam_keluar']){
                $timestamp_keluar = strtotime($x['jam_keluar']);
              }
            }

            // Hitung selisih dalam detik
            $selisih_detik = $timestamp_keluar - $timestamp_masuk;

            $durasi_kerja = floor($selisih_detik / 60); // 1 menit = 60 detik

            if($durasi_kerja >= 5){
              // $durasi_kerja = $durasi_kerja - 60;
            } else if($durasi_kerja < 0){
              $durasi_kerja = 0;
            }
          }
        }
        if($x['status_kehadiran'] == "Hadir"){
          $status_kehadiran = 1;
        } else if($x['status_kehadiran'] == "Izin"){
          $status_kehadiran = 2;
        } if($x['status_kehadiran'] == "Sakit"){
          $status_kehadiran = 3;
        } if($x['status_kehadiran'] == "Tanpa Keterangan"){
          $status_kehadiran = 4;
        }

        $status_lembur = $x['status_lembur'];
        if($x['status_lembur'] == "-"){
          $status_lembur = 0;
        } else if($x['status_lembur'] == "Lembur Weekday"){
          $status_lembur = 1;
        } if($x['status_lembur'] == "Lembur Weekend/Hari Libur"){
          $status_lembur = 2;
        }

        $tanggal_merah = $x['tanggal_merah'];
        if($x['tanggal_merah'] == false){
          $tanggal_merah = 0;
        } else {
          $tanggal_merah = 1;
          $durasi_kerja += 60;
        } 
        // dd($durasi_kerja);
        $hari_hadir = 0;
        if(!empty($x['hari_hadir'])){
          $hari_hadir = (int) $x['hari_hadir'];
        }

        $isi = [];
        $isi['jam_masuk'] = $tgl_absen . ' ' . $x['jam_masuk'];
        $isi['jam_keluar'] = $tgl_absen . ' ' . $x['jam_keluar'];
        $isi['status_kehadiran'] = $status_kehadiran;
        $isi['hari_hadir'] = $hari_hadir;
        $isi['keterangan_kehadiran'] = $x['keterangan_kehadiran'];
        $isi['status_lembur'] = $status_lembur;
        $isi['tanggal_merah'] = $tanggal_merah;
        if (empty($x['terlambat'])) {
          $isi['terlambat'] = null;
        }
        else {
          $isi['terlambat'] = $x['terlambat'];
        }
        $isi['jml_lembur'] = $x['jml_lembur'];
        $isi['keterangan_lembur'] = $x['keterangan_lembur'];
        $isi['bonus'] = $x['bonus'];
        $isi['bonus_keterangan'] = $x['bonus_keterangan'];
        $isi['potongan'] = $x['potongan'];
        $isi['potongan_keterangan'] = $x['potongan_keterangan'];

        $isi['id_shift'] = $id_shift;
        $isi['jadwal_masuk'] = $jadwal_masuk;
        $isi['jadwal_pulang'] = $jadwal_pulang; 
        $durasi_tambahan = 0;
        
        $getDurasi = $this->mabsen->getData($id);
        if (!empty($getDurasi)) {
          $durasi_kerja = $getDurasi->durasi_kerja;
          $oldTanggalMerah = $getDurasi->tanggal_merah == 0 ? false : true;
          if ($oldTanggalMerah != $x['tanggal_merah']) {
            if ($x['tanggal_merah'] == true) {
              $durasi_kerja += 60;
            }
            else {
              $durasi_kerja -= 60;
            }
          }
          $isi['durasi_kerja'] = $durasi_kerja;
        }
        $this->mabsen->updateRecord($this->mabsen->table, $isi, 'id', $id);
      }

      $status = true;
       $msg = "Absensi karyawan berhasil disimpan !";
    }

    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = $data;
    return $this->response->setJSON($build_array);
  }

  function getCircularTimeDiff($time1, $time2) {
    $t1 = strtotime($time1);
    $t2 = strtotime($time2);

    // Hitung selisih absolut
    $diff = abs($t2 - $t1);

    // Bungkus dalam 24 jam
    if ($diff > 12 * 3600) { // jika lebih dari 12 jam
        $diff = (24 * 3600) - $diff;
    }

    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $seconds = $diff % 60;

    return [
        'seconds' => $diff,
        'minutes' => floor($diff / 60),
        'formatted' => sprintf("%02d jam %02d menit %02d detik", $hours, $minutes, $seconds),
    ];
}

  // import langsung tanpa validasi
  function import_excel() 
  {
      $file_excel   = $this->request->getFile('fileImport');
      $tgl_absen    = $this->request->getPost('tglAbsen');

      
      $ext = $file_excel->getClientExtension();
      
      if($ext == 'xls') {
          $render = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
      } else {
          $render = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
      }
      // $inputFileType = IOFactory::identify($file_excel);
      // print_r($inputFileType);exit;
      $status = false;
      $msg = "Simpan data gagal!";

      $spreadsheet = $render->load($file_excel);

      $data = $spreadsheet->getActiveSheet()->toArray();
      // print_r(count($data));exit;
      $i = 1;
      $build_array = [];

      $this->db->transBegin();
      $ckds = "";

      $tgl_absen = fdate_ind_to_eng($tgl_absen);
      $this->mabsen->deleteRecord($this->mabsen->table, 'tgl_absen', ($tgl_absen));
      $inNip = [];
      foreach ($data as $xr) {
        
          if($i >= 3){
            
              $shift = $xr[2];
              $nip = $xr[3];
              $jamInJd = $xr[8];
              $jamIn = $xr[9];

              $terlambat = $xr[10];

              $jamInOutJd = $xr[12];
              $jamOut = $xr[13];

              $lemburA = !empty($xr[14]) ? $xr[14] / 60 : 0;
              $lemburD = $xr[15];
              $lemburAk = $xr[16];

              // lakukan penginputan atau save data sesuai dengan nik
              if(!empty($nip)){
                $pr_kr['nip'] = $nip;
                $get_karyawan = $this->mkaryawan->getData(null, 0, 1, null, null, $pr_kr);
                $id_shift = 1;
                $durasi_kerja = $xr[14];
                // $durasi_kerja = 8 * 60;
                $jadwal_masuk = "08:00";
                $jadwal_pulang = "17:00";
                $prms['nama_shift'] = $shift;
                
                $dt_shift = $this->mshift->getData(null, 0, 1, null, null, $prms);
                if(!empty($dt_shift)){
                  $dt_shift = $dt_shift[0]; 
                  $id_shift = $dt_shift->id;
                  $jadwal_masuk = $dt_shift->jam_masuk;
                  $jadwal_pulang = $dt_shift->jam_pulang;
                  if(!empty($jamIn)){
                    // $jam_awal  = new DateTime($dt_shift->jam_masuk);
                    // $jam_akhir = new DateTime($jamOut);
                    
                    // $interval = $jam_awal->diff($jam_akhir);
                    // $durasi_kerja = ($interval->h * 60) + $interval->i; // Konversi ke menit

                    // Konversi waktu ke timestamp
                    // $timestamp_masuk = strtotime($dt_shift->jam_masuk);
                    // $timestamp_keluar = strtotime($jamOut);

                    $timestamp_masuk = strtotime($jamIn);

                    $time_jadwalmasuk = strtotime($dt_shift->jam_masuk);

                    if($timestamp_masuk < $time_jadwalmasuk){
                      $timestamp_masuk = $time_jadwalmasuk;
                    }

                    $timestamp_keluar = strtotime($dt_shift->jam_pulang);

                    // Hitung selisih dalam detik
                    $selisih_detik = $timestamp_masuk - $timestamp_keluar;

                    // $durasi_kerja = floor($selisih_detik / 60); // 1 menit = 60 detikif

                    // if($durasi_kerja >= 5){
                    //   $durasi_kerja = $durasi_kerja - 60;
                    // } else if($durasi_kerja < 0){
                    //   $durasi_kerja = 0;
                    // }
                  }
                }

                if(!empty($get_karyawan)){
                  $inNip[] = $nip;
                  $dt = $get_karyawan[0];
                  
                  $isi = [];
                  $isi['id_karyawan'] = $dt->id;
                  $isi['posisi'] = $dt->posisi;
                  $isi['tgl_absen'] = ($tgl_absen);

                  $status_kehadiran = null;
                  if (stripos($shift, "tidak hadir") !== false) {
                    $status_kehadiran = 4;
                    
                  }
                  else if(!empty($jamIn)){
                    $status_kehadiran = 1;
                  } else {
                    $status_kehadiran = 0;
                  }

                  $status_lembur = 0;
                  $jam_lembur = 0;
                  if(!empty($lemburAk)){
                    $status_lembur = 1;
                    $jam_lembur = ($lemburAk / 60);

                    $jam_lembur = round($jam_lembur); 
                  }
                  $isi['jam_masuk'] = $tgl_absen . ' ' . $jamIn;
                  $isi['jam_keluar'] = $tgl_absen . ' ' . $jamOut;
                  $isi['status_kehadiran'] = $status_kehadiran;
                  $isi['keterangan_kehadiran'] = '-';
                  $isi['hari_hadir'] = 1;
                  
                  $isi['terlambat'] = $terlambat;
                  $isi['status_lembur'] = $status_lembur;
                  $isi['jml_lembur'] = $jam_lembur;
                  $isi['keterangan_lembur'] = '-';
                  // keterangan shift 
                  $isi['id_shift'] = $id_shift;
                  $isi['durasi_kerja'] = $durasi_kerja;
                  $isi['jadwal_masuk'] = $jadwal_masuk;
                  $isi['jadwal_pulang'] = $jadwal_pulang; 

                  $this->mabsen->insertRecordGetid($this->mabsen->table, $isi);
                }
              }

          }

          $i++;
      }
      
      // generaate all karyawan
      $param_all['not_nip'] = $inNip;
      $data_karyawan = $this->mkaryawan->getData(null, 0, 9999, null, null, $param_all);
      // $params['tgl_absen'] = \fdate_ind_to_eng($tgl_absen);
    // $dt_absen = $this->mabsen->getData(null, 0, 9999, null, null, $params);
        foreach ($data_karyawan  as $dt) {
          $isi = [];
          $isi['id_karyawan'] = $dt->id;
          $isi['posisi'] = $dt->posisi;
          $isi['tgl_absen'] = fdate_ind_to_eng($tgl_absen);
          $this->mabsen->insertRecordGetid($this->mabsen->table, $isi);
        }

      if ($this->db->transStatus() === FALSE) {
          $this->db->transRollback();
      } else {
          $this->db->transCommit();
          $status = true;
          $msg = "Simpan data berhasil";
      }


      $build_array['status'] = $status;
      $build_array['message'] = $msg;

      return $this->response->setJSON($build_array);

  }
}
