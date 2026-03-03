<?php

namespace Modules\Transaction\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use CodeIgniter\Controller;
use DateTime;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Referensi\Models\UkuranModel;
use Modules\Referensi\Models\WarnaModel;
use Modules\Transaction\Models\ProductionModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Transaction\Models\WalkorderModel;

class WorkOrder extends BaseController
{
  protected $mWalkorder;
  protected $mkonsumen;
  protected $files;
  protected $mUkuran;
  protected $mWarna;
  protected $mSample;
  protected $mSalesOrder;
  protected $mPproduksi;
  protected $mProduksi;
  protected $mGudang;

  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/work-order';

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_TRANSAKSI_WALKORDER";

    $this->mWalkorder = new WalkorderModel();
    $this->mkonsumen = new KonsumenModel();
    $this->files  = new FileModel();
    $this->mUkuran = new UkuranModel();
    $this->mWarna = new WarnaModel();
    $this->mSample = new SampleModel();
    $this->mSalesOrder = new SalesOrderModel();
    $this->mPproduksi = new ProsesProduksiModel();
    $this->mProduksi = new ProductionModel();
    $this->mGudang = new GudangModel();
  }


  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['titlehead'] = "Work Order";

    
    $this->data['warna'] = $this->mWarna->where("active", 1)->findAll();
    $this->data['ukuran'] = $this->mUkuran->where("active", 1)->findAll();
    $this->data['role_id'] = session()->get('role_id');

    return view($this->views . '\work_order_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $filter_trans = $this->request->getPost('filter_trans');

    $params = [];

    if(!empty($filter_trans)){
      $params['tipe_id'] = $filter_trans;
    }

    $results = $this->mWalkorder->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mWalkorder->getDataCnt($filters, $params);
    $totaldata = $this->mWalkorder->getDataCnt(null, $params);
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
      // if ($this->_edit) {
      if (true) {
        $atr_edit['title'] = 'Edit';
        $atr_edit['url'] = $this->urlv . '/form/';
        $atr_edit['class'] = '';
      }
      // if ($this->_delete) {
      if (false) {
        $atr_del['title'] = 'Hapus';
        $atr_del['url'] = $this->urlv . '/delete/';
        $atr_del['class'] = '';
        $atr_del['onclick'] = "return confirm('Hapus Data ?')";
      }

      $url_edit   = $this->urlv . '/form/' . $id;
      $url_delete = $this->urlv . '/delete/' . $id;

      // if ($atr_edit || $atr_del)
      $btnAction = btn_action_group($id, $atr_edit, $atr_del);

      // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
      //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";

      $status = $row->status == 1 ? "Draft" : "Approved";
      $tipe = $row->tipe_id == 1 ? "Sample" : "Sales Order";

      $qty = $row->qty;
      $qty_prod = $this->mWalkorder->getCnt_produksi($row->id);


      if($row->tipe_id == 1){
        $ref_data = $this->mSample->getData($row->ref_id);
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $row->ref_id;
        $dtUkuran = $this->mSample->getUkuranTrans($pru);
        $detail = (!empty($dtUkuran)) ? $this->mSample->getDataDetailSample_crostab($row->ref_id) : [];
        if (!empty($detail)) {
          for ($i = 0; $i < count($detail); $i++) {
            $drow = $detail[$i];
            $allQty = $this->mSample->getTotal_qty($drow->id, 2);
            $detail[$i]->qty      = $allQty;
          }
        }

        $file_gambar = !empty($ref_data->file_name) ? base_url() . "uploads/sample/"  . $ref_data->file_name : "";
      }else{
        $ref_data = $this->mSalesOrder->getData($row->ref_id);
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $row->ref_id;
        $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
        $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($row->ref_id) : [];
        if (!empty($detail)) {
          for ($i = 0; $i < count($detail); $i++) {
            $drow = $detail[$i];
            $allQty = $this->mSalesOrder->getTotal_qty($drow->id, 2);
            $detail[$i]->qty      = $allQty;
          }
        }

        $file_gambar = !empty($ref_data->file_name) ? base_url() . "uploads/sales_order/"  . $ref_data->file_name : "";
      }

      array_push(
        $build_array["data"],
        array(
          "id"                => ($id),
          "ref_kode"          => ($row->ref_kode),
          "ref_id"            => ($row->ref_id),
          "konsumen_nama"     => $row->konsumen_nama,
          "kode_walkorder"    => $row->kode_walkorder,
          "qty"               => $row->qty,
          "tipe"              => $tipe,
          "qty_prod"          => $qty_prod,
          "file_name"          => $file_gambar,
          "qty_remain"        => $qty - $qty_prod,
          "tgl_deadline"      => fdate_eng_to_ind($row->tgl_deadline),
          "tgl_deadline_dua"  => !empty($row->tgl_deadline_dua) ? $row->tgl_deadline_dua : '',
          "tgl_transaksi"     => fdate_eng_to_ind($row->tgl_transaksi),
          "keterangan_style"  => $row->deskripsi,
          "style"  => $row->style,
          "status"            => $status,
          "aksi"              => $btnAction,
          "detail" => $detail,
          "key_ukuran" => $dtUkuran,
          "url_edit" => $url_edit,
          "url_delete" => $url_delete,
          "ref_data" => $ref_data
        )
      );
    };
    return $this->response->setJSON($build_array);
  }

  function form($id = null)
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['id'] = $id;
    $this->data['titlehead'] = "Form Work Order";
    if ($id != "") {
      $id = decrypt($id);
      $this->data['titlehead'] = "Form Work Order";
    }

    $status = 1;
    if (!empty($id)) {
      $stdData = $this->mWalkorder->getData($id);
      
      $data_detail = [];
      if ($stdData->tipe_id == 1) {
        $list_detail = $this->mSample->getDataDetailSample_crostab($stdData->ref_id);
        $stdData->file_gambar = !empty($stdData->file_name) ? base_url() . "uploads/sample/"  . $stdData->file_name : "";

        $pru['use'] = 1;// ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $stdData->ref_id;
        $dtUkuran = $this->mSample->getUkuranTrans($pru);
        
      } else {
        $list_detail = $this->mSalesOrder->getDataDetailSalesOrder_crostab($stdData->ref_id);
        $stdData->file_gambar = !empty($stdData->file_name) ? base_url() . "uploads/sales_order/"  . $stdData->file_name : "";

        $pru['use'] = 1;// ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $stdData->ref_id;
        $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
      }

      if (!empty($list_detail)) {
        for ($i = 0; $i < count($list_detail); $i++) {
          $drow = $list_detail[$i];
          if ($stdData->tipe_id == 1) {
            $allQty = $this->mSample->getTotal_qty($drow->id, 2);
          } else {
            $allQty = $this->mSalesOrder->getTotal_qty($drow->id, 2);
          }
          $list_detail[$i]->qty      = $allQty;
          $list_detail[$i]->qty_prod = 0;
        }
      }

      $this->data['row']    = $stdData;
      $this->data['detail'] = json_encode($list_detail);
      $this->data['dtUkuran'] = json_encode($dtUkuran);

      $status = $stdData->status;
    }

    if (isset($_POST)) {
    }

    $proces_data = $this->mPproduksi->getData(null, 0, 999);
    $params_wo['id_walkorder'] = $id;
    $proces_saved = $this->mWalkorder->getData_proses(0, 0, 9999, null, null, $params_wo);
      
    for ($i=0; $i < count($proces_data); $i++) { 
      // Select proces_saved by key value from id_proses
      $proces_data[$i]->harga = 0;
      foreach ($proces_saved as $saved) {
        if ($saved->id_proses == $proces_data[$i]->id) {
          $proces_data[$i]->harga = !empty($saved->harga) ? $saved->harga : 0;
          break;
        }
      }
    }
    $sortGudang = [
        [
            'field' => 'nama_gudang',
            'dir' => 'ASC'
        ]
    ];
    $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
    $this->data['gudang']    = $resDataGudang;

    $this->data['proses'] = $proces_data;
    $this->data['proses_saved'] = json_encode($proces_saved);
    $this->data['status'] = $status;
    $this->data['warna'] = $this->mWarna->where("active", 1)->findAll();
    $this->data['ukuran'] = $this->mUkuran->where("active", 1)->findAll();

    return view($this->views . '/work_order_form', $this->data);
  }

  public function lists_detail()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $wo_id = $this->request->getPost('woid');

    $params = [];
    $params['id_walkorder'] = decrypt($wo_id);
    
    $results = $this->mWalkorder->getData_detail(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mWalkorder->getDataCnt_detail($filters, $params);
    $totaldata = $this->mWalkorder->getDataCnt_detail(null, $params);
    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $id = encrypt($row->id);

      $params_d['id_walkorder_detail'] = $row->id;
      $data_detail = $this->mWalkorder->getData_warna(null, 0, 9999, null, null, $params_d);

      array_push(
        $build_array["data"],
        array(
          "id"      => ($id),
          "wdasar"  => ($row->wdasar),
          "qty"     => $row->qty,
          "gram"    => $row->gram,
          "gram_nd" => $row->gram_nd,
          "kg"      => $row->kg,
          "kg_loss" => $row->kg_loss,
          "total"   => $row->total,
          "loss"    => $row->loss,
          "kuota"   => $row->qty - $row->qty_do,
          "kuota_tambah"    => $row->kuota_tambah,
          'details' => !empty($data_detail) ? $data_detail : []
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  function saveWarna()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;

    $detail_id    = $this->request->getPost('detail');
    $detail_qty   = $this->request->getPost('detail_qty');
    $detail_loss  = $this->request->getPost('detail_loss');
    $list_data    = $this->request->getPost('warna_data');


    $detail_id = \decrypt($detail_id);
    $det_grams = 0;
    $det_grams_nd = 0;
    $det_kg = 0;
    $det_kg_loss = 0;
    $det_total = 0;
    $det_kuota = 0;
    $det_kuotat = 0;

    $this->db->transBegin();
    $list_data = json_decode($list_data, true);

    try {
      if (!empty($list_data)) {
        foreach ($list_data as $r) {
          $warna_id = $r['id'];

          $det_grams = $det_grams + $r['gram'];
          $det_grams_nd = $det_grams_nd + $r['gram_nd'];
          $det_kg = $det_kg + $r['kg'];
          $det_kg_loss = $det_kg_loss + $r['kg_loss'];
          $det_total = $det_total + $r['total'];
          $det_kuota = $det_kuota + $r['kuota'];
          $det_kuotat = $det_kuotat + $r['kuota_tambah'];

          $warna_isi = [
            'persen'       => $r['persen'],
            'gram'         => $r['gram'],
            'gram_nd'      => $r['gram_nd'],
            'kg'           => $r['kg'],
            'kg_loss'      => $r['kg_loss'],
            'total'        => $r['total'],
            'kuota'        => $r['kuota'],
            'kuota_tambah' => $r['kuota_tambah'],
            'loss'         => $detail_loss,
            'updated_at'   => date('Y-m-d H:i:s')
          ];

          $this->mWalkorder->updateRecord($this->mWalkorder->table5, $warna_isi, 'id', $warna_id);
        }
      }

      $detail_isi = [
        'gram'         => $det_grams,
        'gram_nd'      => $det_grams_nd,
        'kg'           => $det_kg,
        'loss'         => $detail_loss,
        'kg_loss'      => $det_kg_loss,
        'total'        => $det_total,
        'kuota'        => $det_kuota,
        'kuota_tambah' => $det_kuotat,
        'updated_at'   => date('Y-m-d H:i:s')
      ];

      $this->mWalkorder->updateRecord($this->mWalkorder->table2, $detail_isi, 'id', $detail_id);

      if ($this->db->transStatus() === FALSE) {
        $this->db->transRollback();
      } else {
        $this->db->transCommit();
        $msg    = "Data berhasil disimpan !";
        $status = true;
      }
    } catch (\Throwable $th) {
      //throw $th;
      $this->db->transRollback();
      // $msg    = $th;
    }

    $build_array['message'] = $msg;
    $build_array['status']  = $status;

    return $this->response->setJSON($build_array);
  }

  function save()
  {
    $dataid      = $this->request->getPost('dataid');
    $list_proses = $this->request->getPost('listproses');
    $status_data = $this->request->getPost('status_data');
    $id_gudang = $this->request->getPost('id_gudang');

    $data_ukuran_input = $this->request->getPost('data_ukuran');
    $data_ukuran_warna = $this->request->getPost('data_ukuran_warna');

    $msg    = "Data gagal disimpan !";
    $status = false;

    try {
    $dataid = \decrypt($dataid);
    $data        = $this->mWalkorder->getData($dataid);
    
    $data_ukuran_input = json_decode($data_ukuran_input, true);
    $data_ukuran_warna = json_decode($data_ukuran_warna, true);

    $this->db->transBegin();

    $update_stat['id_gudang'] = $id_gudang;
    $this->mWalkorder->updateRecord($this->mWalkorder->table, $update_stat, 'id', $dataid);


    $builder_proses = $this->db->table($this->mWalkorder->table3);
    $builder_proses->where("id_walkorder", $dataid);
    $builder_proses->where("approved_int", 0);
    $builder_proses->delete();
    
    // $data_ukuran = $this->mUkuran->getData(0, 0, 999);
    if ($data->tipe_id == 1) {
      $pru['use'] = 1;// ambil ukuran yang digunnakan order 
      $pru['id_sample'] = $data->ref_id;
      $data_ukuran = $this->mSample->getUkuranTrans($pru);
    }else{
      $pru['use'] = 1;// ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $data->ref_id;
      $data_ukuran = $this->mSalesOrder->getUkuranTrans($pru);
    }
    
    $i = 1;
    $list_proses = json_decode($list_proses, true);
    $proses_arr = [];
    foreach ($list_proses as $item) {
      // print_r($item);exit;
      $isiProses = [
        'id_walkorder' => $dataid,
        'id_proses' => $item['proses'],
        'harga' => !empty($item['harga']) ? $item['harga'] : 0,
        'created_at' => date('Y-m-d H:i:s')
      ];
      
      if(!empty($status_data) || $data->status == 2){
        $isiProses['approved_int'] = 1;
        $prm_proses['id_walkorder'] = $dataid;
        $prm_proses['id_proses'] = $item['proses'];
        $data_proses = $this->mWalkorder->getData_proses(null, 0, 1, null, null, $prm_proses);
        
        if(!empty($data_proses)){
          $proses_id = $data_proses[0]->id;
          $updates = $this->mWalkorder->updateRecord($this->mWalkorder->table3, $isiProses, 'id', $proses_id);
          
          $proses_arr[] = $proses_id; 

          foreach ($data_ukuran_warna as $xuk) {
            foreach ($data_ukuran as $x) {
              $isiProses_det = [
                'id_walkorder_proses' => $proses_id,
                'id_ukuran'           => $x->id_ukuran,
                'ref_detail_id'       => $xuk['id'],
                'created_at'          => date('Y-m-d H:i:s')
              ];
              
              $key_ukuran = $x->key_ukuran == 'all' ? 'all_' : $x->key_ukuran;
              if ($i == 1) {
                // $isiProses_det['qty'] = !empty($data_ukuran_input['bottom'][$x->key_ukuran]) ? $data_ukuran_input['bottom'][$x->key_ukuran] : 0;
                $isiProses_det['qty'] = !empty($xuk[$key_ukuran]) ? $xuk[$key_ukuran] : 0;
              } else {
                $isiProses_det['qty'] = 0;
              }

              $prsUkuran['id_walkorder_proses'] = $proses_id;
              $prsUkuran['id_ukuran'] = $x->id_ukuran;
              $prsUkuran['ref_detail_id'] = $xuk['id'];
              $getProsesUkuran =  $this->mWalkorder->getData_proses_ukuran(null, 0, 1, null, null, $prsUkuran);
              if (empty($getProsesUkuran)) {
                $this->mWalkorder->insertRecordGetid($this->mWalkorder->table4, $isiProses_det);
              }
            }
          }
        }else{
          $proses_id = $this->mWalkorder->insertRecordGetid($this->mWalkorder->table3, $isiProses);  
          $proses_arr[] = $proses_id;
          foreach ($data_ukuran_warna as $xuk) {
            foreach ($data_ukuran as $x) {
              $isiProses_det = [
                'id_walkorder_proses' => $proses_id,
                'id_ukuran'           => $x->id_ukuran,
                'ref_detail_id'       => $xuk['id'],
                'created_at'          => date('Y-m-d H:i:s')
              ];
              
              $key_ukuran = $x->key_ukuran == 'all' ? 'all_' : $x->key_ukuran;
              if ($i == 1) {
                // $isiProses_det['qty'] = !empty($data_ukuran_input['bottom'][$x->key_ukuran]) ? $data_ukuran_input['bottom'][$x->key_ukuran] : 0;
                $isiProses_det['qty'] = !empty($xuk[$key_ukuran]) ? $xuk[$key_ukuran] : 0;
              } else {
                $isiProses_det['qty'] = 0;
              }
              
              $this->mWalkorder->insertRecordGetid($this->mWalkorder->table4, $isiProses_det);
            }
          }
        }
      }else{
        $proses_id = $this->mWalkorder->insertRecordGetid($this->mWalkorder->table3, $isiProses);

        if (!empty($status_data)) {
          // foreach ($data_ukuran as $x) {
          //   $isiProses_det = [
          //     'id_walkorder_proses' => $proses_id,
          //     'id_ukuran'           => $x->id,
          //     'created_at'          => date('Y-m-d H:i:s')
          //   ];

          //   $key_ukuran = $x->key_ukuran;
          //   if ($i == 1) {
          //     $isiProses_det['qty'] = !empty($data_ukuran_input['bottom'][$x->key_ukuran]) ? $data_ukuran_input['bottom'][$x->key_ukuran] : 0;
          //   } else {
          //     $isiProses_det['qty'] = 0;
          //   }

          //   $this->mWalkorder->insertRecordGetid($this->mWalkorder->table4, $isiProses_det);
          // }
          // print_r($data_ukuran);exit;
          foreach ($data_ukuran_warna as $xuk) {
            foreach ($data_ukuran as $x) {
              $isiProses_det = [
                'id_walkorder_proses' => $proses_id,
                'id_ukuran'           => $x->id_ukuran,
                'ref_detail_id'       => $xuk['id'],
                'created_at'          => date('Y-m-d H:i:s')
              ];
              
              $key_ukuran = $x->key_ukuran == 'all' ? 'all_' : $x->key_ukuran;
              if ($i == 1) {
                // $isiProses_det['qty'] = !empty($data_ukuran_input['bottom'][$x->key_ukuran]) ? $data_ukuran_input['bottom'][$x->key_ukuran] : 0;
                $isiProses_det['qty'] = !empty($xuk[$key_ukuran]) ? $xuk[$key_ukuran] : 0;
              } else {
                $isiProses_det['qty'] = 0;
              }
              
              $this->mWalkorder->insertRecordGetid($this->mWalkorder->table4, $isiProses_det);
            }
          }
        }
      }

      $i++;
    }

    if (!empty($status_data)) {
      $update_stat['status'] = 2;
      $this->mWalkorder->updateRecord($this->mWalkorder->table, $update_stat, 'id', $dataid);

      // insert to produksi 
      $data_wo = [
        'id_walkorder' => $dataid,
        'tipe_id' => $data->tipe_id,
        'kode_walkorder' => $data->kode_walkorder,
        'kode_prod' =>  $this->mSample->generateNo("PRD", "trans_produksi", "kode_prod"),
        'id_konsumen' => $data->id_konsumen,
        'qty' => $data->qty,
        'file_id' => $data->file_id,
        'keterangan_style' => $data->keterangan_style,
        'keterangan' => $data->keterangan,
        'tgl_transaksi' => date('Y-m-d'),
        'tgl_deadline' => $data->tgl_deadline,
        'status' => 1
      ];

      $this->mProduksi->insertRecordGetid($this->mProduksi->table, $data_wo);
    }

    if ($this->db->transStatus() === FALSE) {
      $this->db->transRollback();
    } else {
      $this->db->transCommit();
      $msg    = "Data berhasil disimpan !";
      $status = true;
    }
    } catch (\Throwable $th) {
      //throw $th;
      $this->db->transRollback();
      print_r($th);exit;
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
      $resData = $this->mWalkorder->getData($id);
      $salesOrder = $this->mSalesOrder->getData($resData->ref_id);
      $ukuran = array_column($this->mUkuran->getData(null, null, 99999), 'key_ukuran');
      if (!empty($salesOrder)) {
        $salesOrderDet = $this->mSalesOrder->getDataDetailSalesOrder_crostab($resData->ref_id);
        $walkOrderDet = $this->mWalkorder->getData_warna_print($id);
        
      }
      else {
        $salesOrder = $this->mSample->getData($resData->ref_id);
        $salesOrderDet = $this->mSample->getDataDetailSample_crostab($resData->ref_id);
        $walkOrderDet = $this->mWalkorder->getData_warna_print($id);
      }
      // $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      // $pru['id_sales_order'] = $id;
      // $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);

      // $resDataDetail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($id) : [];
      // $keysUkuran = !empty($resDataDetail) ? array_keys(get_object_vars($resDataDetail[0])) : [];

      // // Tentukan key mana yang merupakan ukuran (filter selain `id`, `no`, `colordasar`, `colour`, dan `total_harga`)
      // $excludeKeys = ["id", "no", "colordasar", "colour", "total_harga"];
      // $ukuranKeysInc = array_values(array_diff($keysUkuran, $excludeKeys));

      $this->data['ukuran'] = !empty($ukuran) ? $ukuran : [];
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['sales_order'] = !empty($salesOrder) ? $salesOrder : [];
      $this->data['sales_order_det'] = !empty($salesOrderDet) ? $salesOrderDet : [];
      $this->data['walk_order_det'] = !empty($walkOrderDet) ? $walkOrderDet : [];
      // $this->data['ukuran'] = !empty($ukuranKeysInc) ? $ukuranKeysInc : [];
      // $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }

    
    $html = view($this->views . '\work_order_print', $this->data);


    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
    exit;
  }

  function getQrcode()
  {
    
    $ukuran = $this->request->getGet("ukuran");
    $ukuran_text = $this->request->getGet("ukuran_text");
    $qty = $this->request->getGet("qty");
    $qtyp = $this->request->getGet("qtyp");
    $print_type = $this->request->getGet("print_type");
    $noSample = $this->request->getGet("noSample");
    $deskripsi = $this->request->getGet("deskripsi");
    $buyer = $this->request->getGet("buyer");
    $warna = $this->request->getGet("warna");
    $trans = $this->request->getGet("trans");
    $style = $this->request->getGet("style");

    $pr_warna['id_sales_order_det'] = $trans;
    $pr_warna['key_ukuran'] = $ukuran;
    if (strtoupper(substr($noSample, 0, 3)) === 'SOD') {
        // ...
        $data_warna = $this->mSalesOrder->getDataDetailSalesOrderUkuranById($pr_warna);
        if (!empty($data_warna)) {
          $data_so = $this->mSalesOrder->getData($data_warna[0]->id_sales_order);
        }
    }
    else {
      $pr_warna['id_sample_det'] = $trans;
      $data_warna = $this->mSample->getDataDetailSalesOrderUkuranById($pr_warna);
      if (!empty($data_warna)) {
        $data_so = $this->mSample->getData($data_warna[0]->id_sample);
      }
        
    }
    $warna_array = [];
    if (!empty($data_warna)) {
      foreach ($data_warna as $key => $value) {
        for ($i=0; $i < 8; $i++) { 
          $index = $i+1;
          if ($value->{'warna_' . $index}) {
            $warna_array[] = $value->{'warna_' . $index};
          }
        }
      }
    }

    $dateTime = [];

    $save_name = [];
    $warnaNew = str_replace('/', '_', $warna);
    
    for ($i = 0; $i < $qtyp; $i++) {
      $now = new DateTime();
      $dateTime[] = $now->format('Y m d H-i-s-u');
      $save_name[]  = $warnaNew . '-' . $noSample .'-'. time() . '-' . $dateTime[$i] . '.png';
    }


    $path = FCPATH . "uploads/media/qrcode/";

    // Hapus semua file di dalam folder dalam satu baris
    array_map('unlink', glob("$path/*.*"));

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

    $print_type = $print_type == 1 ? "PRODUKSI" : "PERBAIKAN"; 
    

    $data = [
      'ukuran' => $ukuran,
      'ukuran_text' => $ukuran_text,
      'qty' => $qty,
      'qtyp' => $qtyp,
      'noSample' => $noSample,
      'deskripsi' => $deskripsi,
      'buyer' => $buyer,
      'warna' => $warna,
      'data_warna' => $warna_array,
      'stylex' => $style,
      'stylex' => $style,
      'style' => !empty($data_so->stylex) ? $data_so->stylex : $data_so->style,
      'desc' => !empty($data_so) ? $data_so->deskripsi : null,
      'kode_qr' => !empty($data_so->kode_sales_order) ? $data_so->kode_sales_order : $data_so->kode_sample,
      'date_time' => $dateTime,
      'print_type' => $print_type
    ];

    
    if (count($warna_array) > 1) {
        $ururan_warna = '';
        for ($i=0; $i < count($warna_array) ; $i++) { 
          if ($i != 0) {
            $index = $i + 1;
            $data['warna_' . $index] = !empty($warna_array[$i]) ? trim($warna_array[$i]) : '-';
            $ururan_warna .= !empty($warna_array[$i]) ? ";".trim($warna_array[$i]) : ';-';
          }
        }
        $params['data']     = $noSample . ';' . $ukuran . ';' . $warna . ';' . $qty . $ururan_warna;
    }
    else {
      $params['data']     = $noSample . ';' . $ukuran . ';' . $warna . ';' . $qty;
    }
    /* QR Data  */
    $params['level']    = 'L';
    $params['size']     = 5;
    $kodeQR = [];
    $dataPrams = $params['data'];
    // dd(FCPATH . $config['imagedir'] . $save_name[0], $save_name[0]);
    for ($i = 0; $i < $qtyp; $i++) {
      $kodeQR[] = $save_name[$i];
      $params['savename'] = FCPATH . $config['imagedir'] . $save_name[$i];  
      $params['data'] = $dataPrams . ';' . $dateTime[$i];  
      $oks = $this->ciqrcode->generate($params);
    }
    /* Return Data */
    // $url = base_url() . "/uploads/media/qrcode/" . $save_name;

    $this->data["data"] = $data;
    $this->data["fileName"] = $kodeQR;
    return view($this->views . '\vprint_kartu_produksi', $this->data);
  }
}
