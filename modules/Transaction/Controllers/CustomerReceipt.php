<?php

namespace Modules\Transaction\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Libraries\DompdfGenerator;
use Modules\Transaction\Models\ProductionModel;
use Modules\Transaction\Models\WalkorderModel;
use Modules\Transaction\Models\SalesOrderModel;
use Modules\Transaction\Models\SampleModel;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Transaction\Models\InvoiceModel;
use Modules\Transaction\Models\CrModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\UkuranModel;
use Modules\Referensi\Models\WarnaModel;
use Modules\Referensi\Models\RekeningModel;

class CustomerReceipt extends BaseController
{
    protected $views = '\Modules\Transaction\Views';
    protected $urlv  = 'trans/customer-receipt';

    protected $mProduksi;
    protected $mSample;
    protected $mSalesOrder;
    protected $mWalkorder;
    protected $mPproduksi;
    protected $mDelivery;
    protected $mInvoice;
    protected $mkonsumen;
    protected $mUkuran;
    protected $mWarna;
    protected $mCr;
    protected $dnow;
    protected $mRekening;

    function __construct()
    {
        $this->MOD_ALIAS   = "MOD_TRANSAKSI_CR";
        $this->mProduksi   = new ProductionModel();
        $this->mSample     = new SampleModel();
        $this->mSalesOrder = new SalesOrderModel();
        $this->mWalkorder  = new WalkorderModel();
        $this->mPproduksi  = new ProsesProduksiModel();
        $this->mDelivery   = new DeliveryModel();
        $this->mkonsumen   = new KonsumenModel();
        $this->mUkuran     = new UkuranModel();
        $this->mWarna      = new WarnaModel();
        $this->mInvoice    = new InvoiceModel();
        $this->mCr         = new CrModel();
        $this->mRekening   = new RekeningModel();
        $this->dnow        = date('Y-m-d H:i:s');
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Customer Receipt";

        return view($this->views . '\customer_receipt_list', $this->data);
    }

    public function lists()
    {
        $start   = $this->request->getPost('start');
        $limit   = $this->request->getPost('length');
        $filters = $this->request->getPost('filters');
        $order   = $this->request->getPost('order');
        // $tahun   = $this->request->getPost('tahun');

        // $params['tahun'] = $tahun;
        $params = [];
        $results = $this->mCr->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mCr->getDataCnt($filters, $params);
        $totaldata = $this->mCr->getDataCnt(null, $params);
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
            $atr_other = null;

            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->urlv . '/form/';
                $atr_edit['class'] = '';
            }
            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = $this->urlv . '/delete/';
                $atr_del['class'] = '';
                $atr_del['onclick'] = "return confirm('Hapus data ?')";
            }
            if ($row->status == 2) {
                $atr_other['title'] = 'Print';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            }
            $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);


            $tgl_transaksi = "";
            if (!empty($row->tgl_transaksi)) {
                $tgl_transaksi = fdate_eng_to_ind($row->tgl_transaksi);
            }

            // $total_pay = $this->mtrans_pay_det->get_total_bayar($row->sl_customer_receipt_id);
            array_push($build_array['data'], array(
                'aksi' => $btnAction,
                'kode_cr' => $row->kode_cr,
                'tgl_transaksi' => $tgl_transaksi,
                'id_konsumen' => $row->id_konsumen,
                'nama_konsumen' => $row->nama_konsumen,
                'rekening_no' => $row->rekening_no,
                'rekening_bank' =>  $row->rekening_no . " | " . $row->rekening_bank,
                'pph' => $row->pph,
                'total_bayar' => $row->total_bayar,
            ));
        }

        return $this->response->setJSON($build_array);
    }

    public function form($id = null)
    {
        $this->data['titlehead'] = "Form Customer Receipt";

        $this->data['titlehead'] = "Input Customer Receipt";
        if ($id != "") {
            $id = decrypt($id);
            $this->data['titlehead'] = "Edit Customer Receipt";
        }

        $stdData = new \stdClass();
        $stdData->kode_cr = '';
        $stdData->tgl_transaksi = $this->dnow;
        $stdData->id_konsumen = '';
        $stdData->id_rekening = '';
        $stdData->pph = '';
        $stdData->total_bayar = '';
        $stdData->status = 1;

        $Ldetail = "";

        $is_read = false;
        $isFinal = false;

        if ($id) {
            $stdData = $this->mCr->getData($id);
            $stdData->tgl_transaksi  = fdate_eng_to_ind($stdData->tgl_transaksi);
            $this->data['row'] = $stdData;

            // set detail array
            $params_det['id_cr'] = $id;
            $is_detail = $this->mCr->getDataDet(null, 0, 9999, 0, 0, $params_det);
            $detail_array = [];
            $i = 1;
            foreach ($is_detail as $r) {
                $inv_id = encrypt($r->id_invoice);
                $isi = [];
                $isi["seq"] = $i++;
                $isi["bayar"] = true;
                $isi["id_invoice"] = $inv_id;
                $isi["kode_invoice_url"] = "<a target='_blank' href='" . base_url() . "/sales-invoice/form/" . $inv_id . "'>" . $r->kode_invoice . "</a>";
                $isi["kode_invoice"] = $r->kode_invoice;
                $isi["tgl_transaksi"] = fdate_eng_to_ind($r->tgl_transaksi);

                // $isi["total_item"] = $r->total_item;
                // $isi["pph"] = $r->pph;
                // // $isi["total"] = $r->total;
                // $isi["pay_item"] = $r->pay_item;

                $isi["total_item"] = $r->total_item;
                $isi["dp"] = $r->dp;
                $isi["pengiriman"] = $r->pengiriman;
                $isi["remain_item"] = !empty($r->remain_item) ? $r->remain_item : $r->grand_total;
                $isi["pph"] = $r->pph;
                $isi["pay_item"] = $r->pay_item;

                $detail_array[] = $isi;
            }


            $Ldetail = json_encode($detail_array);


            $is_read = true;
            // end auditor

        }

        if ($_POST) {
            // dd($_POST);
            // $stdData->kode_cr  = trim($this->request->getPost('kode_cr'));
            $stdData->tgl_transaksi  = trim($this->request->getPost('tgl_cr'));
            $stdData->id_konsumen  = trim($this->request->getPost('select_buyer'));
            $stdData->id_rekening  = trim($this->request->getPost('select_payment_type'));
            // $stdData->pph  = trim($this->request->getPost('pph'));
            // $stdData->total_bayar  = trim($this->request->getPost('total_bayar'));
            // $stdData->status  = trim($this->request->getPost('status'));

            $action  =  trim($this->request->getPost('actionf'));
            $Ldetail =  trim($this->request->getPost('Ldetail'));

            $this->validation->setRules([
                'tgl_cr' => ['label' => 'Tgl Pembayaran', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'select_buyer' => ['label' => 'Customer', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'select_payment_type' => ['label' => 'Bank', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'Ldetail' => ['label' => 'Item', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()]
            ]);
            // dd($this->validation->withRequest($this->request)->run());
            if ($this->validation->withRequest($this->request)->run() === TRUE) {

                $dataIn["tgl_transaksi"] = fdate_ind_to_eng($stdData->tgl_transaksi);
                $dataIn["id_konsumen"] = $stdData->id_konsumen;
                $dataIn["id_rekening"] = $stdData->id_rekening;
                // $dataIn["pph"] = $stdData->pph;
                // $dataIn["total_bayar"] = $stdData->total_bayar;
                $dataIn["status"] = $stdData->status;

                $dtDet = json_decode($Ldetail, true);
                $mtd   = "Simpan";
                if (!empty($id)) {
                    $dataIn['updated_by'] =  $this->get_userid();
                    $dataIn['updated_at'] = date('Y-m-d H:i:s');
                    $mtd = "Update";
                    $dataIn['status'] = $stdData->status;
                    if ($action == 'approve') {
                        $dataIn['status'] = 2;
                        $mtd = "Approve";
                    } else if ($action == 'reject') {
                        $dataIn['status'] = 3;
                        $mtd = "Reject";
                    }

                    $inUp = $this->update($id, $dataIn, $dtDet);
                } else {

                    $dataIn['kode_cr'] =  $this->mSample->generateNo("CR", "trans_customer_receipt", "kode_cr", "CR");
                    $dataIn['created_by'] = $this->get_userid();
                    $dataIn['created_at'] = date('Y-m-d H:i:s');
                    $dataIn['active'] = 1;
                    $dataIn['status'] = 2;
                    $inUp = $this->insert($dataIn, $dtDet);
                }

                if ($inUp) {
                    // return redirect()->to('/asesmen/sni_form/'.$idx);
                    $this->session->setFlashdata('message', "{$mtd} data berhasil..");
                    return redirect()->to('/trans/customer-receipt');
                } else {
                    $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
                }
            } else {
                $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", $this->validation->listErrors());
            }
        }

        $this->data['Ldetail'] = array(
            'Ldetail' => set_value('Ldetail', $Ldetail)
        );

        $this->data['rekening_list'] = $this->mRekening->getData(null, 0, 9999);
        $this->data['konsumen_list'] = $this->mkonsumen->getData(null, 0, 9999);

        $show_save_btn = true;
        $show_approve_btn = true;
        $show_reject_btn = true;
        // $show_final_btn = $isFinal; 
        $exist = 0;

        if ($stdData->status == 2) {
            $is_read = true;
            $show_save_btn = false;
            $show_approve_btn = false;
            $show_reject_btn = false;
            // $show_final_btn = false; 
        } else if ($stdData->status == 0) {
            $show_approve_btn = false;
            $show_reject_btn = false;
        } else if (($stdData->status > 1) || $stdData->status == 3) {
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
        return view($this->views . '\customer_receipt_form', $this->data);
    }

    private function insert($dataIn, $dataDet)
    {
        $now = date("Y-m-d H:i:s");
        $user_id = $this->get_userid();

        $this->db->transBegin();
        // dd($dataIn);
        $id = $this->mCr->insertRecordGetid($this->mCr->table, $dataIn);
        $bayar = 0;
        $pph = 0;
        if (!empty($dataDet)) {
            foreach ($dataDet as $r) {
                // dd($r);
                if($r["bayar"]){
                    // if (!empty($r["pay_item"]) && $r["pay_item"] > 0) {
                        
                    // }
                    $inv_id = decrypt($r["id_invoice"]);
                    $dtIn["id_invoice"] = $inv_id;
                    $dtIn["id_cr"] = $id;
                    $dtIn["total_item"] = $r["total_item"];
                    $dtIn["remain_item"] = $r["remain_item"];
                    // $dtIn["pph"] = $r["pph"];
                    $dtIn["total"] = $r["total"];
                    $dtIn["pay_item"] = $r["pay_item"];

                    $dtIn["active"] = 1;
                    $dtIn["created_by"] = $user_id;
                    $dtIn["created_at"] = $now;

                    $bayar = $bayar +  $r["pay_item"];
                    $pph = $pph +  $r["pph"];
                    // $dtInvs = $this->mtrans_invoice->getData($inv_id);
                    // $pays = $r["sl_pay_item"];
                    // $dtInv["sl_pay_item"] =  $dtInvs->sl_pay_item + $pays;
                    // if(empty($dtInvs->pph23)){
                    //     $dtInv["pph23"] =  $r["pph23"];
                    //     $dtIn["pph23"] = $r["pph23"];
                    // }
                    $arrInv = [
                        "payment_status" => 1,
                    ];
                    $arrParam =  [
                        "id_invoice" => $inv_id,
                    ];
                    $this->mInvoice->updateRecords($this->mInvoice->table2, $arrInv, $arrParam);
                    $this->mCr->insertRecordGetid($this->mCr->table2, $dtIn);
                    // dd("asas");
                    // $this->mtrans_invoice->updateRecord($this->mtrans_invoice->table, $dtInv, 'sl_invoice_id', $inv_id);
                }
            }
            $upd['total_bayar'] = $bayar;
            $upd['pph'] = $pph;
            $this->mCr->updateRecord($this->mCr->table, $upd, 'id', $id);
        }

        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            return FALSE;
        } else {
            $this->db->transCommit();
            return TRUE;
        }
    }

    public function delete($id = NULL)
    {
        $this->deactived($id);
        // if ($id != null && $id != "") {
        //     $id = decrypt($id);
        // }

        // $id = (int) $id;

        // $res = $this->asesmen->deleteRecord($this->asesmen->table, 'asesment_id', $id);
        // if ($res) {
        //     $this->session->setFlashdata('message', "Data berhasil dihapus !");
        // } else {
        //     $this->session->setFlashdata('err', "Data gagal dihapus !");
        // }
        return redirect()->to('/customer-receipt');
    }

    public function deactived($id)
    {
        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int) $id;
        // Trans Start
        $this->db->transBegin();
        $user_id = $this->auth->getUserId();
        $now = date('Y-m-d H:i:s');

        $dataIn['active'] = 0;
        $dataIn['updated_by'] =  $user_id;
        $dataIn['updated_at'] = $now;
        $res = $this->mCr->updateRecord($this->mCr->table, $dataIn, 'id', $id);

        $params_det['id_cr'] = $id;
        $is_detail = $this->mCr->getDataDet(null, 0, 9999, 0, 0, $params_det);

        $bayar = 0;
        foreach ($is_detail as $r) {
            $inv_id = $r->id_invoice;
            $dtInvs = $this->mInvoice->getData($inv_id);
            $pays = $r->pay_item;
            $pays23 = $r->pph;
            $dtInv["pay_item"]   =  $dtInvs->pay_item - $pays;
            $dtInv["pph"]        =  $dtInvs->pph - $pays23;
            $dtInv['updated_by'] =  $user_id;
            $dtInv['updated_at'] = $now;
            $this->mInvoice->updateRecord($this->mInvoice->table, $dtInv, 'id', $inv_id);

            $bayar += (float) $pays + (float) $pays23;
        }

        // $dtPay =  $this->mCr->getData($id);
        // if(!empty($bayar)){
        //     $konsumen_id = $dtPay->konsumen_id;
        //     $dtKons = $this->mkonsumen->getData($konsumen_id);
        //     if(!empty($dtKons)){
        //         $bayar = (float) $dtKons->limit_pakai + (float) $bayar;
        //         if($bayar >= 0){
        //             $dtUp["limit_pakai"] = $bayar;
        //         }else{
        //             $dtUp["limit_pakai"] = 0;
        //         }

        //         $this->mkonsumen->updateRecord($this->mkonsumen->table, $dtUp, 'konsumen_id', $konsumen_id);
        //     }
        // }

        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            $res = FALSE;
        } else {
            $this->db->transCommit();
            $res = TRUE;
        }
        // Trans End
        if ($res) {
            $this->session->setFlashdata('message', "Data berhasil dihapus !");
        } else {
            $this->session->setFlashdata('err', "Data gagal dihapus !");
        }
        return redirect()->to('/customer-receipt');
    }


    function get_invoice()
    {
        $id_konsumen = $this->request->getPost('id_konsumen');

        $status = false;
        $msg = "Invoice konsumen tidak ditemukan !";
        $data = [];
        if (!empty($id_konsumen)) {
            $params['id_konsumen'] = $id_konsumen;
            $dtInv = $this->mInvoice->getData(null, 0, 9999, null, null, $params);
            if (!empty($dtInv)) {
                $i = 0;
                foreach ($dtInv as $r) {
                    $inv_id = encrypt($r->id);
                    $isi = [];
                    $isi["seq"] = $i++;
                    $isi["bayar"] = false;
                    $isi["id_invoice"] = $inv_id;
                    $isi["kode_invoice_url"] = "<a target='_blank' href='" . base_url() . "/trans/sales-invoice/form/" . $inv_id . "'>" . $r->kode_invoice . "</a>";
                    $isi["kode_invoice"] = $r->kode_invoice;
                    $isi["tgl_transaksi"] = fdate_eng_to_ind($r->tgl_transaksi);

                    $isi["total_item"] = $r->total;
                    $params_det['id_invoice'] = $r->id;
                    $resDataDetail = $this->mCr->getDataDet(null, 0, 9999, 0, 0, $params_det);
                    $remain = !empty($r->remain_item) ? $r->remain_item : $r->grand_total;
                    if (!empty($resDataDetail) && count($resDataDetail) > 0) {
                        foreach ($resDataDetail as $key => $value) {
                            $remain -= $value->pay_item;
                        }
                    }
                    $remain = $remain;
                    $remain = !empty($r->pengiriman) ? $remain + $r->pengiriman : $remain;
                    $isi["remain_item"] = $remain;
                    $isi["pph"] = $r->pph_total;
                    $isi["dp"] = $r->total_down_payment;
                    $isi["pengiriman"] = $r->pengiriman;
                    $isi["total"] = $r->grand_total;
                    $isi["pay_item"] = $remain;

                    if ($remain > 0) {
                        $data[] = $isi;
                    }
                }

                $status = true;
                $msg = "Invoice konsumen ditemukan !";
            }
        }

        $build_array["status"] = $status;
        $build_array["msg"] = $msg;
        $build_array["data"] = $data;
        return $this->response->setJSON($build_array);
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
            $resData = $this->mCr->getData($id);
            $params_det['id_cr'] = $id;
            $resDataDetail = $this->mCr->getDataDet(null, 0, 9999, 0, 0, $params_det);
            $this->data['data'] = !empty($resData) ? $resData : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
        }
        $html = view($this->views . '\customer_receipt_print', $this->data);


        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('rec_item.pdf', ['Attachment' => false]);
        exit;
    }
}
