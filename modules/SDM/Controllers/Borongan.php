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
use Modules\SDM\Models\Mborongan;
use Modules\Referensi\Models\ShiftModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\PerusahaanModel;

// user library spreadsheet for excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Borongan extends BaseController
{   
  protected $views = '\Modules\SDM\Views';

  protected $mabsen;
  protected $mkaryawan;
  protected $mshift;
  protected $mborongan;
  protected $mproses;
  protected $moperator;
  protected $mPerusahaan;

  function __construct()
  {
      $this->MOD_ALIAS = "MOD_SDM_BORONGAN";
      $this->mabsen = new Mabsensi();
      $this->mkaryawan = new KaryawanModel();
      $this->mshift = new ShiftModel(); 
      $this->mborongan = new Mborongan();
      $this->mproses = new ProsesProduksiModel();
      $this->moperator = new OperatorModel();
      $this->mPerusahaan = new PerusahaanModel;
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
        return redirect()->to('/auth/login');
    }

    $dataProses = $this->mproses->getData(null, 0, 99999);
    $dataOperator = $this->moperator->getData(null, 0, 99999);

    $this->data['titlehead'] = "Penggajian Borongan";
    $this->data['dnow'] = date('d-m-Y');
    $this->data['proses']    = $dataProses;
    $this->data['operator']    = $dataOperator;
    $sortPerusahaan = [
            [
                'field' => 'nama_perusahaan',
                'dir' => 'ASC'
            ]
        ];
    $this->data['superadmin'] = $this->auth->isSuperadmin();
    if (!$this->auth->isSuperadmin()) {
        if (empty($this->currentUser->id_perusahaan)) {
            $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => 1]);
        }
        else {
            $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan, null, ['id' => $this->currentUser->id_perusahaan]);
        }
        $this->data['user_perusahaan'] = !empty($this->currentUser->id_perusahaan) || $this->currentUser->id_perusahaan != 1 ? $this->currentUser->id_perusahaan : 1;
    }
    else {
        $this->data['perusahaan'] = $this->mPerusahaan->getData(null, 0, 99999, $sortPerusahaan);
        $this->data['user_perusahaan'] = null;
    }
    return view($this->views . '\borongan_list', $this->data);
  }

  public function lists()
  {
      $start      = $this->request->getPost('start');
      $limit      = $this->request->getPost('length');
      $filters    = $this->request->getPost('filter');
      $order      = $this->request->getPost('sort');
      $tgl_awal      = $this->request->getPost('tgl_awal');
      $tgl_akhir      = $this->request->getPost('tgl_akhir');
      $id_operator = $this->request->getPost('id_operator');
      $id_proses = $this->request->getPost('id_proses');
      $id_perusahaan = $this->request->getPost('id_perusahaan');

      // $params = [];

      $params['tgl_awal'] = \fdate_ind_to_eng($tgl_awal);
      $params['tgl_akhir'] = \fdate_ind_to_eng($tgl_akhir);
      $params['id_operator'] = $id_operator;
      $params['id_proses'] = $id_proses;    
      if(!empty($id_perusahaan)){
        $params['id_perusahaan'] = $id_perusahaan;
      }

      $results = $this->mborongan->getData(null, 0, 99999, $order, $filters, $params);
      $totalfiltered = $this->mborongan->getDataCnt($filters, $params);
      $totaldata = $this->mborongan->getDataCnt(null, $params);
      $maxpage = ceil($totalfiltered / $limit);
      // print_r($results);exit;
      $build_array = array(
          "last_page" => $maxpage,
          "recordsTotal" => $totaldata,
          "recordsFiltered" => $totalfiltered,
          "data" => array()
      );
      foreach ($results as $row) {
          // $id = encrypt($row->id);
          $btnAction = "<button data-id_proses='".$row->id_proses."' data-id_operator='".$row->id_operator."' class='btn btn-primary btn-print-new' onclick='printLaporan(this)'><i class='fas fa-print'></i>&nbsp; Print</button>";
          $keterangan_style = "";

          if(!empty($row->keterangan_style)){
            $keterangan_style = $row->keterangan_style;
          }

          if(!empty($row->keterangan)){
            $keterangan_style .= ' '. $row->keterangan;
          }
          $tgl_transaksi = \fdate_eng_to_ind_slas($row->tgl_transaksi);



          $harga = $row->harga_total / $row->qty;

          array_push(
              $build_array["data"],
              array(
                  "print" => $btnAction,
                  "keterangan_style" => $keterangan_style,
                  "nama_operator" => $row->nama_operator,
                  "id_proses" => $row->id_proses,
                  "proses" => $row->proses,
                  "harga" => $harga,
                  "qty_produksi" => $row->qty_produksi,
                  "qty_perbaikan" => $row->qty_perbaikan,
                  "harga_total" => $row->harga_total,
                  // "kode_prod" => $row->kode_prod,
                  "tgl_transaksi" => $tgl_transaksi,
                  "kode_transaksi" => $row->kode_transaksi,
              )
          );
      }
      return $this->response->setJSON($build_array);
  }

  function print_borongan(){
    $tgl_awal      = $this->request->getGet('tgl_awal');
    $tgl_akhir      = $this->request->getGet('tgl_akhir');
    $id_operator = $this->request->getGet('id_operator');
    $id_proses = $this->request->getGet('id_proses');

    $stdData = $this->moperator->getData($id_operator);

    $params['tgl_awal'] = \fdate_ind_to_eng($tgl_awal);
    $params['tgl_akhir'] = \fdate_ind_to_eng($tgl_akhir);
    $params['id_operator'] = $id_operator;
    // $params['id_proses'] = $id_proses;    

    $results = $this->mborongan->getData(null, 0, 99999, null, null, $params);
    foreach ($results as $row) {
        $keterangan_style = "";

        if(!empty($row->keterangan_style)){
          $keterangan_style = $row->keterangan_style;
        }

        if(!empty($row->keterangan)){
          $keterangan_style .= ' '. $row->keterangan;
        }
        $row->keterangan_style = $keterangan_style;
        $harga = $row->harga_total / $row->qty;
        $row->harga = $harga;
    }
    $this->data['row'] = $stdData;
    $this->data['results'] = $stdData;
    $this->data['detail'] = $results;
    $this->data['xrow'] = $params;
    return view($this->views.'\vprint_sdm_opreator', $this->data);
  }
}
