<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use Modules\Referensi\Models\WarnaModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\UkuranModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Transaction\Models\WalkorderModel;
use Modules\Transaction\Models\ProductionModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Transaction\Models\BarangKeluarModel;
use Modules\Transaction\Models\IncomingGoodsModel;

class DeliveryOrder extends BaseController
{
  protected $views = '\Modules\Transaction\Views';
  protected $urlv  = 'trans/delivery-order';

  protected $mProduksi;
  protected $mSample;
  protected $mSalesOrder;
  protected $mWalkorder;
  protected $mPproduksi;
  protected $mDelivery;
  protected $mkonsumen;
  protected $mUkuran;
  protected $mWarna;
  protected $mBarangKeluar;
  protected $mBarang;

  function __construct()
  {
    $this->MOD_ALIAS   = "MOD_TRANSAKSI_DELIVERY";
    $this->mProduksi   = new ProductionModel();
    $this->mSample     = new SampleModel();
    $this->mSalesOrder = new SalesOrderModel();
    $this->mWalkorder  = new WalkorderModel();
    $this->mPproduksi  = new ProsesProduksiModel();
    $this->mDelivery   = new DeliveryModel();
    $this->mkonsumen   = new KonsumenModel();
    $this->mUkuran     = new UkuranModel();
    $this->mWarna      = new WarnaModel();
    $this->mBarang     = new BarangModel();
    $this->mBarangKeluar = new BarangKeluarModel();
  }

  public function index()
  {

    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }
    $this->data['titlehead'] = "Delivery Order";
    $this->data['buyer'] = $this->mkonsumen->where("active", 1)->findAll();


    return view($this->views . '\delivery_order_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');

    $params = [];

    $results = $this->mDelivery->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mDelivery->getDataCnt($filters, $params);
    $totaldata = $this->mDelivery->getDataCnt(null, $params);
    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $id = encrypt($row->id);

      $atr_edit  = null;
      $atr_del   = null;
      $atr_other   = null;
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

      $atr_other['title'] = 'Print';
      $atr_other['target'] = "blank";
      $atr_other['url'] = $this->urlv . '/print/';
      $atr_other['class'] = '';
      $atr_other['icon_class'] = 'fa-print';
      // if ($atr_edit || $atr_del)
      $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);

      $status = $row->status  == 1 ? "Draft" : "Approved";
      $tipe   = $row->tipe_id == 1 ? "Sample" : "Sales Order";

      $qty      = $row->sisa_qty;
      $qty_delv = $row->qty_delv;

      array_push(
        $build_array["data"],
        array(
          "id"                => ($id),
          "delivery_kode"     => ($row->delivery_kode),
          "produksi_kode"     => $row->produksi_kode,
          "konsumen_nama"     => $row->konsumen_nama,
          "alamat"            => $row->alamat,
          "kode_so"              => $row->kode_so,
          "qty"               => $qty,
          "qty_delv"          => $qty_delv,
          "qty_remain"        => $qty - $qty_delv,
          "tgl_transaksi"     => fdate_eng_to_ind($row->tgl_transaksi),
          "keterangan_style"  => $row->keterangan_style,
          "status"            => $status,
          "aksi"              => $btnAction
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  public function form($id = null)
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }

    $this->data['id'] = $id;
    $this->data['titlehead'] = "Form Delivery Order";
    if ($id != "") {
      $id = decrypt($id);
      $this->data['titlehead'] = "Form Delivery Order";
    }

    $stdData = new \stdClass();
    $stdData->delivery_kode = '';
    $stdData->do_no = '';
    $stdData->id_produksi = '';
    $stdData->id_walkorder = '';
    $stdData->kode_produksi = '';
    $stdData->kode_so = '';
    $stdData->tgl_do = date('d-m-Y');
    $stdData->keterangan_style = '';
    $stdData->select_buyer = '';
    $stdData->alamat_buyer  = '';
    $dt_details = [];
    $dt_prods = [];

    $status = 1;
    
    if (!empty($id)) {
      $stdData = $this->mDelivery->getData($id);
      $stdData->do_no = $stdData->delivery_kode;
      $stdData->id_produksi = $stdData->id_produksi;
      $stdData->id_walkorder = $stdData->id_walkorder;
      $stdData->kode_produksi = $stdData->produksi_kode;
      $stdData->kode_so = $stdData->kode_so;
      $stdData->tgl_do = $stdData->tgl_transaksi;
      $stdData->keterangan_style = $stdData->keterangan_style." (".$stdData->deskripsi.")";
      $stdData->select_buyer =  $stdData->id_konsumen;
      $stdData->alamat_buyer  =  $stdData->alamat;
      $status = $stdData->status;
      
      $dtWalkorder = $this->mWalkorder->getData($stdData->id_walkorder);
      // / $data_ukuran = $this->mUkuran->getData(0, 0, 999);
      if ($dtWalkorder->tipe_id == 1) {
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $dtWalkorder->ref_id;
        $data_ukuran = $this->mSample->getUkuranTrans($pru);
      } else {
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $dtWalkorder->ref_id;
        $data_ukuran = $this->mSalesOrder->getUkuranTrans($pru);
      }
      $this->data['dt_ukuran'] = json_encode($data_ukuran, true);
      
      // $this->data['row']    = $stdData;
      // $this->data['detail'] = json_encode($list_detail);
      
      $arr_qty = [];
      
      $prx['id_delivery'] = $stdData->id;
      $rs_prod = $this->mDelivery->getDataProduksi($prx);
      if (!empty($rs_prod)) {
        $qty_prod = [];
        foreach ($rs_prod as $xitem) {
          $xisi = [];
          $xisi['id'] = $xitem->id;
          $xisi['id_ukuran'] = $xitem->id_ukuran;
          $xisi['ref_detail_id'] = $xitem->ref_detail_id;
          $xisi['kode_warna'] = $xitem->kode_warna;
          $xisi['kode_ukuran'] = $xitem->kode_ukuran;
          $xisi['key_ukuran'] = $xitem->key_ukuran;
          $xisi['qty'] = $xitem->qty;
          $dt_prods[] = $xisi;
          if (!in_array($xitem->ref_detail_id, $qty_prod)) {
          // Jika belum ada, tambahkan ke array
              $qty_prod[] = $xitem->ref_detail_id;
              $qty_prod["qty_".$xitem->ref_detail_id] = $xitem->qty;
          }
          else {
            $qty_prod["qty_".$xitem->ref_detail_id] += $xitem->qty;
          }
          
          $arr_qty[$xitem->ref_detail_id] = $qty_prod["qty_".$xitem->ref_detail_id];
        }
      }
      
      $dt_details = [];
      $prm['id_walkorder'] = $stdData->id_walkorder;
      $rukuran = $this->mUkuran->getData(0, 0, 999);
      $ukuran = "";
      foreach ($rukuran as $iu) {
        $keyUkuran = $iu->key_ukuran;
        if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
        $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
      }
      $prm['ukuran'] = $ukuran;
      $dataProd = [];
      $rsProd = $this->mProduksi->getProduksilastV1($prm);
      
      if (!empty($rsProd)) {
        foreach ($rsProd as $item) {
          $params_prod['id_delivery'] = $stdData->id;
          $params_prod['ref_detail_id'] = $item->ref_detail_id;
          $getdProd = $this->mDelivery->getDataDetailProd($params_prod);
          $isi = array(
            "ref_detail_id" => ($item->ref_detail_id),
            "id_walkorder"  => ($item->id_walkorder),
            "colordasar"    => ($item->kode_warna),
          );

          $qty = 0;
          foreach ($rukuran as $iu) {
            $keyUkuran = $iu->key_ukuran;

            if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
            $indx      = $keyUkuran;
            $indxKey      = $keyUkuran."_key";
            $xharga    = $keyUkuran . '_hrg';
            $getQtyBefore = $this->mDelivery->getTotalQtyBefore($stdData->id_produksi, $id, $iu->id, $item->ref_detail_id);
            $qtyBeforeOn = !empty($getQtyBefore) ? $getQtyBefore : 0;
            $isi[$indx] = $item->$keyUkuran - $qtyBeforeOn;
            foreach ($getdProd as $key => $valueProd) {
              if ($valueProd->id_ukuran == $iu->id) {
                $isi['id'] = $valueProd->id;
                $isi[$indxKey] = $valueProd->qty_do;
              }
            }
            $isi[$xharga] = $item->$xharga;
            
            $qty = $qty +  $item->$keyUkuran - $qtyBeforeOn;
          }
          
          $isi['qty'] = $qty;
          $isi['qty_prod'] = !empty($arr_qty[$item->ref_detail_id]) ? $arr_qty[$item->ref_detail_id] : 0;
          $isi['qty_remain'] = $qty -  $isi['qty_prod'];
          array_push(
            $dataProd,
            $isi
          );
        }
      }
      $dt_details  = $dataProd;
      
      $dt_details = json_encode($dt_details, true);
      $dt_prods = json_encode($dt_prods, true);
    }
    $this->data['row']    = $stdData;
    if (!empty($_POST)) {
      if (true) {
        if (!empty($stdData->id_invoice)) {
          $this->session->setFlashdata('err', "DO Sudah ada Invoice tidak dapat update!");
          return redirect()->back();
        }
        $stdData->do_no = trim($this->request->getPost('do_no'));
        $stdData->id_produksi = trim($this->request->getPost('id_produksi'));
        $stdData->id_walkorder = trim($this->request->getPost('id_walkorder'));
        $stdData->kode_produksi = trim($this->request->getPost('kode_produksi'));
        $stdData->tgl_do = trim($this->request->getPost('tgl_do'));
        $stdData->keterangan_style = trim($this->request->getPost('keterangan_style'));
        $stdData->select_buyer = trim($this->request->getPost('select_buyer'));
        $stdData->alamat_buyer  = trim($this->request->getPost('alamat_buyer'));
        $dt_prods   = trim($this->request->getPost('data-details'));
        $dt_details = trim($this->request->getPost('data-prods'));

        $dt_details = json_decode($dt_details, true);
        $dt_prods = json_decode($dt_prods, true);

        $action = $this->request->getPost('actionf');



        $data['tgl_transaksi'] = fdate_ind_to_eng($stdData->tgl_do);
        $data['id_produksi'] = $stdData->id_produksi;
        $data['produksi_kode'] = $stdData->kode_produksi;
        $data['alamat'] = $stdData->alamat_buyer;
        $data['id_konsumen'] = $stdData->select_buyer;
        $data['id_walkorder'] = $stdData->id_walkorder;
        $data['status'] =  $action == "kirim" ? 2 : 1;
        if (!empty($status) && $status == 2) {
           $data['status'] = 2;
        }

        if (!empty($id)) {
          $data['updated_at'] = date('Y-m-d H:i:s');
          $data['updated_by'] = $this->get_userid();

          $inUp = $this->update($id, $data, $dt_details, $dt_prods);
        } else {
          $data['active'] = 1;
          $data['delivery_kode'] = $this->mSample->generateNo("DO", "trans_delivery", "delivery_kode", "DO");
          $data['created_at'] = date('Y-m-d H:i:s');
          $data['created_by'] = $this->get_userid();
          $inUp = $this->insert($data, $dt_details, $dt_prods);
        }

        if ($inUp) {
          $this->session->setFlashdata('message', "Simpan data berhasil..");
          return redirect()->to('/trans/delivery-order');
        } else {
          // $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
          $this->session->setFlashdata('error', "Ulangi simpan data !");
        }
      } else {
        $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", $this->validation->listErrors());
      }
    }

    $view_read = false;
    if ($status != 1) {
      $view_read = true;
    }

    $this->data['view_read'] = $view_read;
    $this->data['buyer'] = $this->mkonsumen->where("active", 1)->findAll();
    $this->data['dt_details'] = $dt_details; // = json_decode($dt_details, true);
    $this->data['dt_prods'] = $dt_prods; // = json_decode($dt_prods, true);
    $this->data['status'] = $status;

    return view($this->views . '\delivery_order_form', $this->data);
  }

  function insert($dataIn, $detail, $produksi)
  {
    $tgl = date('Y-m-d H:i:s');
    $userId = $this->get_userid();
    $this->db->transBegin();

    $id = $this->mDelivery->insertRecordGetid($this->mDelivery->table, $dataIn);

    $id_walkorder = $dataIn['id_walkorder'];
    $dtWalkorder = $this->mWalkorder->getData($id_walkorder);
    // / $data_ukuran = $this->mUkuran->getData(0, 0, 999);
    if ($dtWalkorder->tipe_id == 1) {
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sample'] = $dtWalkorder->ref_id;
      $data_ukuran = $this->mSample->getUkuranTrans($pru);
    } else {
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $dtWalkorder->ref_id;
      $data_ukuran = $this->mSalesOrder->getUkuranTrans($pru);
    }

    $qty = 0;
    if (!empty($detail)) {
      $builderx = $this->mDelivery->table($this->mDelivery->table2);
      $builderx->where("id_delivery", $id);
      $builderx->delete();
      foreach ($detail as $item) {
        $ddata = [];
        $ddata['id_delivery'] = $id;
        $ddata['ref_detail_id'] = $item['ref_detail_id'];
        $ddata['id_ukuran'] = $item['id_ukuran'];
        $ddata['qty'] = $item['qty'];
        $ddata['created_at'] = $tgl;
        $ddata['created_by'] = $userId;
        $this->mDelivery->insertRecordGetid($this->mDelivery->table2, $ddata);

        $qty = $qty + $item['qty'];
      }
    }

    $dataIn['qty'] = $qty;
    $this->mDelivery->updateRecord($this->mDelivery->table, $dataIn, 'id', $id);

    if (!empty($produksi)) {

      // $rukuran = $this->mUkuran->getData(0, 0, 999);
      foreach ($produksi as $keyx => $itemx) {
        $xdata = [];
        $xdata['id_delivery']   = $id;
        $xdata['ref_detail_id'] = $itemx['ref_detail_id'];
        
        $xdata['created_at']    = $tgl;
        $xdata['created_by']    = $userId;
        foreach ($data_ukuran as $keyiu => $iu) {
          $keyUkuran = $iu->key_ukuran;
          $indx      = $iu->key_ukuran;
          
          if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
          $xdata['qty'] = $itemx[$keyUkuran];
          $xdata['qty_do']          = (!empty($itemx[$keyUkuran."_key"])) ? $itemx[$keyUkuran."_key"] : 0;
          $xharga    = $keyUkuran . '_hrg';
          
          $xdata['id_ukuran']    = $iu->id_ukuran;
          $xdata['harga_satuan'] = $itemx[$xharga];

          $this->mDelivery->insertRecordGetid($this->mDelivery->table3, $xdata);
        }
      }
    }



    $this->mcommon->setLog($this->auth->getUserId(), $this->MOD_ALIAS, $id, "Insert Delivery");

    if ($this->db->transStatus() === FALSE) {
      $this->db->transRollback();
      return FALSE;
    } else {
      $this->db->transCommit();
      return TRUE;
    }
  }



  function update($id, $dataIn, $detail, $produksi)
  {
    $tgl = date('Y-m-d H:i:s');
    $userId = $this->get_userid();

    $this->db->transBegin();

    $this->mDelivery->updateRecord($this->mDelivery->table, $dataIn, 'id', $id);

    $qty = 0;
    $id_walkorder = $dataIn['id_walkorder'];
    $dtWalkorder = $this->mWalkorder->getData($id_walkorder);
    // / $data_ukuran = $this->mUkuran->getData(0, 0, 999);
    if ($dtWalkorder->tipe_id == 1) {
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sample'] = $dtWalkorder->ref_id;
      $data_ukuran = $this->mSample->getUkuranTrans($pru);
    } else {
      $pru['use'] = 1; // ambil ukuran yang digunnakan order 
      $pru['id_sales_order'] = $dtWalkorder->ref_id;
      $data_ukuran = $this->mSalesOrder->getUkuranTrans($pru);
    }
    if (!empty($detail)) {
      // $builderx = $this->db->table($this->mDelivery->table2);
      // $builderx->where("id_delivery", $id);
      // $builderx->delete();
      
      foreach ($detail as $item) {
        $ddata = [];
        $ddata['id_delivery'] = $id;
        $ddata['ref_detail_id'] = $item['ref_detail_id'];
        $ddata['id_ukuran'] = $item['id_ukuran'];
        $ddata['qty'] = $item['qty'];
        $rs_prod = $this->mDelivery->getDataProduksi(null, $item['id']);
        if (!empty($rs_prod)) {
          $ddata['updated_at'] = $tgl;
          $this->mDelivery->updateRecord("trans_delivery_detail", $ddata, 'id', $item['id']);
        }
        else {
          $ddata['created_at'] = $tgl;
          $ddata['created_by'] = $userId;
          $this->mDelivery->insertRecordGetid($this->mDelivery->table2, $ddata);
        }

        $qty = $qty + $item['qty'];

        if ($dataIn['status'] == 2) {
          // input output barang 

          $dtWo = $this->mWalkorder->getData($dataIn['id_walkorder']);

          $params_b['nama_barang'] = $dtWo->keterangan_style;
          $dtBarang = $this->mBarang->getData(null, 0, 1, null, null, $params_b);
          if(!empty($dtBarang)){
            $id_gudang = $dtWo->id_gudang;
            $id_barang = $dtBarang[0]->id;

            $mBarangMasuk = new IncomingGoodsModel();
            $arrParam =  [
              "id_barang" => $id_barang,
              "id_gudang" => $id_gudang,
            ];

            $resLotNo = $mBarangMasuk->getLotNo(null, $id_barang, null, $id_gudang);

            if (!empty($resLotNo)) {
              $idLots = $resLotNo->id;
              $this->mBarangKeluar->updateRecords('trans_lots', array("qty" => $resLotNo->qty - $item['qty']), array("id" => $idLots));
            } else {
              $idLots = 0;
            }

            $resData = $mBarangMasuk->getLastStokBarangBalances($id_barang, $id_gudang, $idLots);

            // $stokAwal = !empty($resData) ? $resData->stok : 0;
            $dataBarang = [
              "id_barang" => $id_barang,
              "jenis_transaksi" => 2,
              "jumlah" =>  $item['qty'],
              "tanggal" => date("Y-m-d H:i:s"),
              "id_gudang_asal" =>  !empty($id_gudang) ? $id_gudang : null,
              "nama" => 'Delivery',
              "id_kategori" => 11,
              "keterangan" => "Barang Keluar Produksi lewat delivery",
              "active" => 1,
              "tipe" => 1,
              "created_at" =>  date("Y-m-d H:i:s"),
              "lot_id" => $idLots,
              "kode_transaksi" => $this->mBarangKeluar->generateKodePersediaan(),
            ];
            $this->mBarangKeluar->insertRecordGetid('trans_barang', $dataBarang);
            $arrStockBalances = [
              "id_barang" => $id_barang,
              "id_gudang" => !empty($id_gudang) ? $id_gudang : null,
              "tanggal" => date("Y-m-d H:i:s"),
              "lot_id" => $idLots,
              "saldo_awal" => 0,
              "saldo_akhir" => $item['qty'],
              "active" => 1,
              "created_at" =>  date("Y-m-d H:i:s"),
            ];
            if (!empty($resData)) {
              $stock = !empty($rowData['qty_exist']) ? $rowData['qty_exist'] - $rowData->qty : 0;
              $this->mBarangKeluar->updateRecords('trans_barang_balances', array("saldo_akhir" => $stock), array("id" => $resData->id));
            } else {
              $this->mBarangKeluar->insertRecordGetid('trans_barang_balances', $arrStockBalances);
            }
          }
        }
      }
    }

    $dataIn['qty'] = $qty;
    $this->mDelivery->updateRecord($this->mDelivery->table, $dataIn, 'id', $id);
    if (!empty($produksi)) {

      // $rukuran = $this->mUkuran->getData(0, 0, 999);
      $builderv = $this->mDelivery->deleteRecord($this->mDelivery->table3, 'id_delivery', $id);
      
      foreach ($produksi as $itemx) {
        $xdata = [];
        $xdata['id_delivery']   = $id;
        $xdata['ref_detail_id'] = $itemx['ref_detail_id'];

        $xdata['created_at']    = $tgl;
        $xdata['created_by']    = $userId;
        foreach ($data_ukuran as $iu) {
          $keyUkuran = $iu->key_ukuran;
          $indx      = $iu->key_ukuran;

          if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
          $xdata['qty']          = $itemx[$keyUkuran];
          $xdata['qty_do']          = (!empty($itemx[$keyUkuran."_key"])) ? $itemx[$keyUkuran."_key"] : 0;
          $xharga    = $keyUkuran . '_hrg';

          $xdata['id_ukuran']    = $iu->id_ukuran;
          $xdata['harga_satuan'] = $itemx[$xharga];
          // if (!empty($value->id)) {
          //   $this->mDelivery->updateRecord($this->mDelivery->table3, $xdata, "id", $value->id);
          // } 
          // else {
          //   $xdataRef['id_delivery']   = $id;
          //   $xdataRef['ref_detail_id'] = $itemx['ref_detail_id'];
          //   $xdataRef['id_ukuran']    = $iu->id_ukuran;
          //   $getPRODOLD = $this->mDelivery->getDataDetailProd($xdataRef);
          // }
          $this->mDelivery->insertRecordGetid($this->mDelivery->table3, $xdata);
        }

        // $getPRODOLD = $this->mDelivery->getDataDetailProd($xdata);
        // if (!empty($getPRODOLD) && count($getPRODOLD) > 0) {
        //   foreach ($getPRODOLD as $key => $value) {
            
        //   }
          
        // }
      }
    }



    $this->mcommon->setLog($this->auth->getUserId(), $this->MOD_ALIAS, $id, "Update Delivery");

    if ($this->db->transStatus() === FALSE) {
      $this->db->transRollback();
      return FALSE;
    } else {
      $this->db->transCommit();
      return TRUE;
    }
  }


  // get data produksi
  public function lists_produksi()
  {
    $start       = $this->request->getPost('start');
    $limit       = $this->request->getPost('length');
    $filters     = $this->request->getPost('filter');
    $order       = $this->request->getPost('sort');
    $id_konsumen = $this->request->getPost('id_konsumen');
    
    $params = [];
    $params['id_konsumen'] = $id_konsumen;

    $results = $this->mProduksi->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mProduksi->getDataCnt($filters, $params);
    $totaldata = $this->mProduksi->getDataCnt(null, $params);
    $maxpage = ceil($totalfiltered / $limit);

    $build_array = array(
      "last_page" => $maxpage,
      "recordsTotal" => $totaldata,
      "recordsFiltered" => $totalfiltered,
      "data" => array()
    );

    foreach ($results as $row) {
      $id = ($row->id);

      $status = $row->status == 1 ? "Draft" : "Approved";
      $tipe = $row->tipe_id == 1 ? "Sample" : "Sales Order";

      // $qty = $row->qty;
      $qty = 0;
      $qty_prod = 0;

      if($row->tipe_id == 1){
        $ref_data = $this->mSample->getData($row->id_walkorder_ref);
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $row->id_walkorder_ref;
        $dtUkuran = $this->mSample->getUkuranTrans($pru);
        $detail = (!empty($dtUkuran)) ? $this->mSample->getDataDetailSample_crostab($row->id_walkorder_ref) : [];
        if (!empty($detail)) {
          for ($i = 0; $i < count($detail); $i++) {
            $drow = $detail[$i];
            $allQty = $this->mSample->getTotal_qty($drow->id, 2);
            $qty    += $allQty;
          }
        }
      }else{
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $row->id_walkorder_ref;
        $dtUkuran = $this->mSalesOrder->getUkuranTrans($pru);
        $detail = (!empty($dtUkuran)) ? $this->mSalesOrder->getDataDetailSalesOrder_crostab($row->id_walkorder_ref) : [];
        if (!empty($detail)) {
          for ($i = 0; $i < count($detail); $i++) {
            $drow = $detail[$i];
            $allQty = $this->mSalesOrder->getTotal_qty($drow->id, 2);
            $qty    += $allQty;
          }
        }
      }

      array_push(
        $build_array["data"],
        array(
          "id"                => ($id),
          "id_walkorder"      => ($row->id_walkorder),
          "ref_kode"          => ($row->kode_walkorder),
          "konsumen_nama"     => $row->konsumen_nama,
          "kode_walkorder_ref"         => $row->kode_walkorder_ref,
          "kode_prod"         => $row->kode_prod,
          // "qty"               => $row->qty,
          "qty"               => $qty,
          "tipe"              => $tipe,
          "qty_prod"          => $qty_prod,
          "qty_remain"        => $qty - $qty_prod,
          "tgl_deadline"      => fdate_eng_to_ind($row->tgl_deadline),
          "tgl_transaksi"     => fdate_eng_to_ind($row->tgl_transaksi),
          "keterangan_style"  => $row->keterangan_style,
          "deskripsi"  => $row->deskripsi,
          "status"            => $status,
        )
      );
    }
    return $this->response->setJSON($build_array);
  }


  function getDataProduksi()
  {
    $id_walkorder = $this->request->getPost("id_walkorder");
    $id_produksi  = $this->request->getPost("produkds");
    $status = false;
    $msg    = "Gagal mengambil data produksi !";
    $data   = [];

    $results = $this->mProduksi->getData($id_produksi);
    // print_r($results);exit;
    if (!empty($results)) {
      $data['produksi']         = $results;

      $dtWalkorder = $this->mWalkorder->getData($id_walkorder);
      // / $data_ukuran = $this->mUkuran->getData(0, 0, 999);
      if ($dtWalkorder->tipe_id == 1) {
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sample'] = $dtWalkorder->ref_id;
        $data_ukuran = $this->mSample->getUkuranTrans($pru);
      } else {
        $pru['use'] = 1; // ambil ukuran yang digunnakan order 
        $pru['id_sales_order'] = $dtWalkorder->ref_id;
        $data_ukuran = $this->mSalesOrder->getUkuranTrans($pru);
      }
      $data['data_ukuran'] = $data_ukuran;


      $prm['id_walkorder'] = $id_walkorder;
      $rukuran = $this->mUkuran->getData(0, 0, 999);
      $ukuran = "";
      foreach ($data_ukuran as $iu) {
        $keyUkuran = $iu->key_ukuran;
        if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
        $ukuran .= ($ukuran == "") ? $keyUkuran : ", " . $keyUkuran;
      }
      $prm['ukuran'] = $ukuran;
      // print_r($prm);exit;
      $dataProd = [];
      $rsProd = $this->mProduksi->getProduksilastV1($prm);
      if (!empty($rsProd)) {
        foreach ($rsProd as $item) {
          $isi = array(
            "ref_detail_id" => ($item->ref_detail_id),
            "id_walkorder"  => ($item->id_walkorder),
            "colordasar"    => ($item->kode_warna),
          );

          $qty = 0;
          $qty_delv = 0;
          foreach ($data_ukuran as $iu) {
            $keyUkuran = $iu->key_ukuran;

            if ($iu->key_ukuran == 'all') $keyUkuran = 'all_';
            $indx      = $keyUkuran;
            $xharga    = $keyUkuran . '_hrg';

            $prd['id_ukuran'] = $iu->id_ukuran;
            $prd['ref_detail_id'] = $item->ref_detail_id;
            $dt_deliv = $this->mDelivery->getAlldeliveryQty($prd);

            $qty_ukuran =  $item->$keyUkuran - $dt_deliv;
            $hrg_ukuran =  $item->$xharga;

            $isi[$indx]   = $qty_ukuran;
            $isi[$xharga] = $hrg_ukuran;

            $qty = $qty +  $qty_ukuran;
          }

          $isi['qty'] = $qty;
          $isi['qty_prod'] = 0;
          $isi['qty_remain'] = $qty;
          array_push(
            $dataProd,
            $isi
          );
        }
      }
      
      $data['detail_produksi']  = $dataProd;

      $status = true;
      $msg    = "Berhasil mengambil data produksi !";
    }

    $build_array['status']  = $status;
    $build_array['message'] = $msg;
    $build_array['data']    = $data;

    return $this->response->setJSON($build_array);
  }

  public function getDataProduksiItem()
  {
    $barcode_code = $this->request->getPost("kata_kunci");
    $id_walkorder = $this->request->getPost("id_walkorder");
    $id_produksi  = $this->request->getPost("id_produksi");

    $status = false;
    $msg = "Data warna ukuran tidak ditemukan !";
    $data = [];
    $slc  = [];

    // if(!empty($barcode_code)){
    $params['last_proses']  = 1;
    $params['id_walkorder'] = $id_walkorder;
    $params['kata_kunci'] = $barcode_code;
    $result = $this->mWalkorder->getListProduksiUkuran($params);
    if (!empty($result)) {
      foreach ($result as $r) {
        $isi = [];
        $isi["id_walkorder"]  = $r->id_walkorder;
        $isi["ref_detail_id"] = $r->ref_detail_id;
        $isi["id_ukuran"]     = $r->id_ukuran;
        $isi["id_warna_1"]      = $r->id_warna_1;
        $isi["id_warna_2"]      = $r->id_warna_2;
        $isi["id_warna_3"]      = $r->id_warna_3;
        $isi["id_warna_4"]      = $r->id_warna_4;
        $isi["id_warna_5"]      = $r->id_warna_5;
        $isi["id_warna_6"]      = $r->id_warna_6;
        $isi["id_warna_7"]      = $r->id_warna_7;
        $isi["id_warna_8"]      = $r->id_warna_8;
        $isi["qty"]           = $r->qty_prod;
        $isi["qty_prod"]      = 0;
        $isi["kata_kunci"]    =  "(" . $r->kode_warna . ") " . $r->kode_ukuran;
        $isi["kode_warna"]    = $r->kode_warna;
        $isi["kode_ukuran"]   = $r->kode_ukuran;
        $isi["key_ukuran"]    = $r->key_ukuran;
        $data[] = $isi;


        $isi_slc = [];
        $isi_slc["id"]    = $r->ref_detail_id;
        $isi_slc["idx"]   = $r->kode_warna;
        $isi_slc["label"] = "(" . $r->kode_warna . ") " . $r->kode_ukuran;
        $isi_slc["value"] = $r->ref_detail_id;
        $isi_slc["data"]  = $isi;
        $slc[] = $isi_slc;
      }
      $status = true;
      $msg = "Data produk ditemukan !";
    }

    // }

    $build_array["status"] = $status;
    $build_array["msg"] = $msg;
    $build_array["data"] = $data;
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
    $ukuranSize = array_column($this->mUkuran->getData(null, null, 99999), 'key_ukuran');
    $this->data['data'] = [];
    if ($id != "") {
      $id = decrypt($id);
      $resData = $this->mDelivery->getData($id);
      $arr_qty = [];

      $prx['id_delivery'] = $resData->id;
      $rs_prod = $this->mDelivery->getDataProduksi($prx);
      if (!empty($rs_prod)) {
        foreach ($rs_prod as $xitem) {
          $xisi = [];
          $xisi['id_ukuran'] = $xitem->id_ukuran;
          $xisi['ref_detail_id'] = $xitem->ref_detail_id;
          $xisi['kode_warna'] = $xitem->kode_warna;
          $xisi['keterangan'] = $xitem->keterangan;
          $xisi['kode_ukuran'] = $xitem->kode_ukuran;
          $xisi['qty'] = $xitem->qty;
          $dt_prods[] = $xisi;

          $arr_qty[$xitem->ref_detail_id] = $xitem->qty;
        }
      }

      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['detail'] = !empty($dt_prods) ? $dt_prods : [];
      $this->data['ukuran'] = !empty($ukuranSize) ? $ukuranSize : [];
    }
    $html = view($this->views . '\delivery_order_print', $this->data);


    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
    exit;
  }

  public function print_excel_lists($from_date, $to_date, $buyer){

        $fileName = "DO-List.xlsx";

        if ($buyer == 'all') {
          $buyer = null;
        }

        $id = $this->request->getGet('data_id');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mDelivery->get_export($tanggal_sql_from, $tanggal_sql_to, $buyer);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Delivery Order '. $title)

               ->setCellValue('A4', 'KODE DO')
               ->setCellValue('B4', 'TANGGAL DO')
               ->setCellValue('C4', 'KODE SO')
               ->setCellValue('D4', 'BUYER')
               ->setCellValue('E4', 'STYLE')
               ->setCellValue('F4', 'WARNA')
               ->setCellValue('G4', 'UKURAN')
               ->setCellValue('H4', 'QTY SO')
               ->setCellValue('I4', 'QTY DO');

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
        $gets->getStyle('A4:I4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('A2:I2');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
        $gets->getColumnDimension('A')->setWidth(17);
        $gets->getColumnDimension('B')->setWidth(20);
        $gets->getColumnDimension('C')->setWidth(17);
        $gets->getColumnDimension('D')->setWidth(35);
        $gets->getColumnDimension('E')->setWidth(35);
        $gets->getColumnDimension('F')->setWidth(70);
        $gets->getColumnDimension('G')->setWidth(15);
        $gets->getColumnDimension('H')->setWidth(20);
        $gets->getColumnDimension('I')->setWidth(20);

        $gets->getStyle('A4:I4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets->getStyle('A4:I4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I'
        );

        for ($i=0; $i < 9 ; $i++) { 

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

        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $qty = !empty($r->qty_so) ? $r->qty_so : 0;
            $qty_do = !empty($r->qty_do) ? $r->qty_do : 0;

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->delivery_kode) ? $r->delivery_kode : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : '-')
                    ->setCellValue('D'.$ix, !empty($r->nama) ? $r->nama : '-')
                    ->setCellValue('E'.$ix, !empty($r->style) ? $r->style : '-')
                    ->setCellValue('F'.$ix, !empty($r->kode_warna) ? $r->kode_warna : '-')
                    ->setCellValue('G'.$ix, !empty($r->kode_ukuran) ? $r->kode_ukuran : '-')
                    ->setCellValue('H'.$ix, $qty)
                    ->setCellValue('I'.$ix, $qty_do);


                $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);

            // $sheets->getActiveSheet()->getStyle("L" . $ix .":Q" . $ix )->getNumberFormat()
            //         ->setFormatCode('#,##0.00');

          $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':G'. $length);
        
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('H' . $length, '=SUM(H' . $startRow . ':H' . $length-1 . ')');

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('I' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        // $gets->getStyle('L'. $length .':Q'. $length)->getNumberFormat()
        //         ->setFormatCode('#,##0.00');
        
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
