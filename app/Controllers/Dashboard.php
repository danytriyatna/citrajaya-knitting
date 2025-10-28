<?php

namespace App\Controllers;

use App\Models\Mdashboard;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Transaction\Models\ProductionModel;

class Dashboard extends BaseController
{
    protected $mdashboard;
    protected $mProduksi;
    protected $mdelivery;

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_DASHBOARD";
        $this->mdashboard = new Mdashboard();
        $this->mProduksi = new ProductionModel();
        $this->mdelivery = new DeliveryModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } else {
            $this->data['titlehead'] = "Dashboard";

            return view('dashboard/index', $this->data); 
        }
    }

    public function lists()
    {
        $start   = $this->request->getPost('start');
        $limit   = $this->request->getPost('length');
        $filters = $this->request->getPost('filter');
        $order   = $this->request->getPost('order');
        $tracking_id   = $this->request->getPost('tracking_id');
        // $tahun   = $this->request->getPost('tahun');

        // $params['tahun'] = $tahun;
        $params = [];
        if ($tracking_id == 1) {
            $results = $this->mdashboard->getDataSample(null, $start, $limit, $order, $filters, $params);
            $totalfiltered = $this->mdashboard->getDataCntSample($filters, $params);
            $totaldata = $this->mdashboard->getDataCntSample(null, $params);
        }

        else {
            $results = $this->mdashboard->getData(null, $start, $limit, $order, $filters, $params);
            $totalfiltered = $this->mdashboard->getDataCnt($filters, $params);
            $totaldata = $this->mdashboard->getDataCnt(null, $params);
        }
        
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );
        // $total = 0;
        foreach ($results as $key => $row) {
            $id = encrypt($row->trans_id);

            $btnOrder = "";
            $btnProd = "";
            $btnDev = "";
            $btnGambar = "";

            $tipe = "-";
            
            if($row->tipe == 1){
                $link_order = base_url() . "/trans/sample?search=$row->trans_kode";
                $btnOrder = "<a class='btn btn-sm btn-primary' target='_blank' href=".$link_order." > ".$row->trans_kode." </a>";
                if (isset($row->file_name)) {
                    $link_gambar = base_url() . "uploads/sample/"  . $row->file_name;
                    $btnGambar = "<a class='btn btn-sm btn-secondary' target='_blank' href=".$link_gambar." > Foto Sample </a>";
                }
                
                $tipe = "Sample";
            }else{
                $link_order = base_url() . "/trans/sales-order?search=$row->trans_kode";
                $btnOrder = "<a class='btn btn-sm btn-primary' target='_blank' href=".$link_order." > ".$row->trans_kode." </a>";
                if (isset($row->file_name)) {
                    $link_gambar = base_url() . "uploads/sales_order/"  . $row->file_name;
                    $btnGambar = "<a class='btn btn-sm btn-secondary' target='_blank' href=".$link_gambar." > Foto SO </a>";
                }

                $tipe = "Sales Order";
            }
            
            if(!empty($row->id_prod)){
                $prod_id = encrypt($row->id_prod);
                $link_prod = base_url() . "/trans/production/form/" . $prod_id;

                $btnProd = "<a class='btn btn-sm btn-primary' target='_blank' href=".$link_prod." > ".$row->kode_prod." </a>";
            }

            if(!empty($row->id_dev)){
                $dev_id = encrypt($row->id_dev);
                $link_dev = base_url() . "/trans/delivery-order/form/" . $dev_id;

                $btnDev = "<a class='btn btn-sm btn-primary' target='_blank' href=".$link_dev." > ".$row->kode_dev." </a>";
            }
            
            $tgl_deadline = "";
            if(!empty($row->tgl_deadline)){
                $tgl_deadline = fdate_eng_to_ind($row->tgl_deadline);
            }

            $tgl_transaksi = "";
            if(!empty($row->tgl_transaksi)){
                $tgl_transaksi = fdate_eng_to_ind($row->tgl_transaksi);
            }

            $parms['last_proses'] = 1;
            $parms['id_walkorder'] = $row->id_walkorder;
            if (!empty($row->id_walkorder)) {
                $dataLast = $this->mProduksi->getDataProsesProd($parms);
                $last_data = !empty($dataLast) ? $dataLast[0] : [];
    
                $qty_kirim = 0;
                if(!empty($row->id_prod)){
                    $param_dlv['id_produksi'] = $row->id_prod;
                    $data_pengirimasn = $this->mdelivery->getData(null, 0, 9999, null, null, $param_dlv);
    
                    if(!empty($data_pengirimasn)){
                        foreach ($data_pengirimasn as $rd) {
                        $qty_kirim += $rd->qty_delv;
                        }
                    }
                }
    
                $qty_hasil = null;
                if (!empty($last_data)) {
                    $qty_hasil = $last_data->qty_prod - $qty_kirim;
                }
    
                $qty_sisa = (int) $row->qty - (int) $row->qty_prod;
                //   $qty_sisa_kirim = (int) $row->qty_prod - (int) $row->qty_kirim;
                $qty_sisa_kirim = (int) $row->qty - (int) $row->qty_kirim;
                
            }
            else {
                $qty_hasil = null;
                $qty_sisa = null;
                $qty_sisa_kirim = null;

            }

            // $total += ($row->uang_dp + $row->nilai_invoice);
            
            array_push($build_array['data'], array(
                'btnGambar' => $btnGambar,
                'btnOrder' => $btnOrder,
                'btnProd' => $btnProd,
                'btnDev' => $btnDev,
                'tipe' => $tipe,
                'nama' => $row->nama,
                'keterangan' => $row->style."/".$row->deskripsi,
                'tgl_transaksi' => $tgl_transaksi,
                'tgl_deadline' => $tgl_deadline,
                'qty' => $row->qty,
                'qty_prod' => $row->qty_prod,
                'qty_kirim' => $row->qty_kirim,
                'uang_dp' => $row->uang_dp,
                'harga_total' => $row->harga_total,
                'nilai_invoice' => $row->nilai_invoice,
                'qty_sisa' => $qty_sisa,
                'qty_hasil' => $qty_hasil,
                'qty_sisa_kirim' => $qty_sisa_kirim,
            ));

        }
        
        return $this->response->setJSON($build_array);
    }

    public function lists_inv()
    {
        $start   = $this->request->getPost('start');
        $limit   = $this->request->getPost('length');
        $filters = $this->request->getPost('filter');
        $order   = $this->request->getPost('order');
        // $tahun   = $this->request->getPost('tahun');

        // $params['tahun'] = $tahun;
        $params = [];
        $results = $this->mdashboard->getDataInvNew(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mdashboard->getDataInvCntNew($filters, $params);
        $totaldata = $this->mdashboard->getDataInvCntNew(null, $params);
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );
        
        foreach ($results as $row) {
            // $id = encrypt($row->id);

            // $btnInv = "";
            
            // $link_inv = base_url() . "/trans/sales-invoice/form/" . $id;
            // $btnInv = "<a class='btn btn-sm btn-primary' target='_blank' href=".$link_inv." > ".$row->kode_invoice." </a>";

            
            // $tgl_invoice = "";
            // if(!empty($row->tgl_invoice)){
            //     $tgl_invoice = fdate_eng_to_ind($row->tgl_invoice);
            // }

            // $tgl_jatuh_tempo = "";
            // if(!empty($row->tgl_jatuh_tempo)){
            //     $tgl_jatuh_tempo = fdate_eng_to_ind($row->tgl_jatuh_tempo);
            // }
            // $nilai_so = $row->nilai_so + $row->nilai_sample;
            // $total_down_payment = !empty($row->total_down_payment)? $row->total_down_payment : 0;
            // $pembayaran = !empty($row->pembayaran)? $row->pembayaran : 0;
            // // $pembayaran =  $pembayaran < 0 ? 0 - $pembayaran : $pembayaran;

            // $pembayaran = $pembayaran + $total_down_payment;
            // $sisa_bayar = (float) $nilai_so - (float) $pembayaran;
            // dd($row->nilai_so, $row->nilai_sample, $row->nilai_so + $row->nilai_sample);
            
            
            array_push($build_array['data'], array(
                // 'btnInv' => $btnInv,
                // 'tgl_invoice' => $tgl_invoice,
                'nama' => $row->buyer,
                // 'tgl_jatuh_tempo' => $tgl_jatuh_tempo,
                'nilai_so' => $row->nilai_so,
                'nilai_invoice' => $row->nilai_invoice,
                'pembayaran' => $row->pembayaran,
                'sisa_tagihan' => $row->sisa_tagihan,
                'sisa_pembayaran' => $row->sisa_pembayaran,
            ));

        }
        
        return $this->response->setJSON($build_array);
    }

    public function lists_po()
    {
        $start   = $this->request->getPost('start');
        $limit   = $this->request->getPost('length');
        $filters = $this->request->getPost('filter');
        $order   = $this->request->getPost('order');
        // $tahun   = $this->request->getPost('tahun');

        // $params['tahun'] = $tahun;
        $params = [];
        $results = $this->mdashboard->getDataPoNew(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mdashboard->getDataPoCntNew($filters, $params);
        $totaldata = $this->mdashboard->getDataPoCntNew(null, $params);
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            // $id = encrypt($row->id);

            // $btnPo = "";
            
            // $link_Po = base_url() . "/purchasing/purchase-order/form//" . $id;
            // $btnPo = "<a class='btn btn-sm btn-primary' target='_blank' href=".$link_Po." > ".$row->po_no." </a>";

            
            // $po_date = "";
            // $due_date = "";
            // if(!empty($row->tgl_po)){
            //     $po_date = fdate_eng_to_ind($row->tgl_po);
            //     if (!empty($row->days)) {
            //         $expr_date = date("Y-m-d", strtotime($row->tgl_po . " +".$row->days." days"));
            //         $due_date = fdate_eng_to_ind($expr_date);
            //     }
            // }

            // $date_exc = "";
            // if(!empty($row->date_exc)){
            //     $date_exc = fdate_eng_to_ind($row->date_exc);
            // }


            $sisa_bayar = (float) $row->total_bayar - (float) $row->dibayar;

            if ($sisa_bayar == 0) {
                continue;
            }
            
            
            array_push($build_array['data'], array(
                // 'btnPo' => $btnPo,
                // 'po_date' => $po_date,
                'nama' => $row->nama_vendor,
                // 'date_exc' => $date_exc,
                'total_bayar' => $row->total_bayar,
                'dibayar' => $row->dibayar,
                'sisa_bayar' => $sisa_bayar,
                // 'due_date' => $due_date,
            ));

        }
        
        return $this->response->setJSON($build_array);
    }

    public function lists_laba()
    {
        $month = $this->request->getGet('month');       // sama seperti $_GET['kategori']
            $year = $this->request->getGet('year');

        // $params['tahun'] = $tahun;
        $laba_kotor = 0;
        $format_laba_kotor = '0,00';

        $total_dp = $this->mdashboard->getDataDP($month, $year);
        if (is_null($total_dp) || $total_dp == 0) {
            $format_penjualan = '0,00';  // Atau bisa gunakan format lain
        } else {
            $laba_kotor += $total_dp;
        }

        $penjualan = $this->mdashboard->getDataPenjualan($month, $year);
        if (!empty($penjualan)) {
            $laba_kotor += $penjualan;// Atau bisa gunakan format lain
        } 

        $format_penjualan = '' . number_format( $laba_kotor, 2, ',', '.');

        $Pemakaian = $this->mdashboard->getDataPemakaian($month, $year);
            if (is_null($Pemakaian) || $Pemakaian == 0) {
                $format_pemakaian = '0,00';  // Atau bisa gunakan format lain
            } else {
                $format_pemakaian = '' . number_format($Pemakaian, 2, ',', '.');
                $laba_kotor -= $Pemakaian;
            }

            if ($laba_kotor != 0) {
                $format_laba_kotor = '' . number_format($laba_kotor, 2, ',', '.');
            }

        $biaya = $this->mdashboard->getDataBiaya($month, $year);

        $code = [];
        $nama_biaya = [];
        $harga_per_biaya = [];
        $total_biaya = 0;
        $format_total_biaya = '0,00';
        foreach ($biaya as $key => $value) {
            if (!in_array($value->kode, $code)) {
                $code[] = $value->kode;
                $nama_biaya[] = $value->nama;
                if (isset($value->bln1)) {
                    # code...
                    $harga_per_biaya[] = '' . number_format($value->bln1, 2, ',', '.');
                    $total_biaya += $value->bln1;
                }
                else {
                    $harga_per_biaya[] = '0,00'; 
                }
            } 
        }
        if ($total_biaya != 0) {
            $format_total_biaya = '' . number_format($total_biaya, 2, ',', '.');
        }

        $laba_bersih = $laba_kotor - $total_biaya;
        $format_laba_bersih = '' . number_format($laba_bersih, 2, ',', '.');

        $build_array = array(
            "penjualan" => $format_penjualan,
            "pemakaian" => $format_pemakaian,
            "laba_kotor" => $format_laba_kotor,
            "biaya" => $biaya,
            "code" => $code,
            "nama_biaya" => $nama_biaya,
            "harga_per_biaya" => $harga_per_biaya,
            "total_biaya" => $format_total_biaya,
            "laba_bersih" => $format_laba_bersih,
        );
        
        return $this->response->setJSON($build_array);
    }

    public function lists_grafik()
    {     // sama seperti $_GET['kategori']
        $year = $this->request->getGet('year');

        // $params['tahun'] = $tahun;
        $laba_kotor = 0;
        $format_laba_kotor = '0,00';
        $penjualan = $this->mdashboard->getGrafikDataPenjualan($year);
        $pemakaian = $this->mdashboard->getGrafikDataPemakaian($year);
        $dp = [];
        $operasional = [];
        for ($i=0; $i < 12; $i++) { 
            $biaya = $this->mdashboard->getDataBiaya($i+1, $year);
            $dp[] = $this->mdashboard->getDataDP($i+1, $year);

            $code = [];
            $nama_biaya = [];
            $total_biaya = 0;
            foreach ($biaya as $key => $value) {
                if (!in_array($value->kode, $code)) {
                    $code[] = $value->kode;
                    $nama_biaya[] = $value->nama;
                    if (isset($value->bln1)) {
                        $total_biaya += $value->bln1;
                    }
                } 
            }
            $operasional[] =  $total_biaya;
        }
        foreach ($dp as $key => $value) {
            $penjualan[$key] = $penjualan[$key] + $value;
        }

        $total = array_map(function($a, $b) {
            return $a + $b;
        }, $pemakaian, $operasional);
        
        $build_array = array(
            "penjualan" => $penjualan,
            "biaya" => $total,
        );
        
        return $this->response->setJSON($build_array);
    }
    
}
