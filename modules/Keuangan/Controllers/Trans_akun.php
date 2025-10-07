<?php

namespace Modules\Keuangan\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FileModel;
use Modules\Keuangan\Models\Mcoa;
use Modules\Keuangan\Models\Mtrans_akun;
use Modules\Keuangan\Models\Mtrans_akun_det;
use Modules\Referensi\Models\RekeningModel;

// user library spreadsheet for excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Xlsx_r;

class Trans_akun extends BaseController
{   
    private $url_v = "Modules\Keuangan\Views";
    private $url_in = "/keuangan/transaksi_akun";

    protected $mcoa;
    protected $mtrans_akun;
    protected $mtrans_det;
    protected $mrekening;

	function __construct()
    {
        $this->MOD_ALIAS = "MOD_TRANS_AKUN";
        $this->mcoa = new Mcoa();
        $this->mtrans_akun = new Mtrans_akun();
        $this->mtrans_det = new Mtrans_akun_det();
        $this->mrekening = new RekeningModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $list_tahun = $this->mtrans_akun->get_tahun();
        $ltahun = [];
        foreach ($list_tahun as $rt) {
            $ltahun[$rt->tahun] = $rt->tahun;
        }

        $this->data['tahun_trans'] = array(
            'name' => 'tahun_trans',
            'id' => 'tahun_trans',
            'options' => $ltahun,
            'class' => 'form-control'
        );

        $list_bulan = [];
        for ($i=0; $i < 12; $i++) { 
            $bulan = $i + 1;
            $list_bulan[$bulan] = bulan($bulan);
        }

        $this->data['slc_bulan'] = array(
            'name' => 'slc_bulan',
            'id' => 'slc_bulan',
            'options' => $list_bulan,
            'class' => 'form-control'
        );

		$this->data['titlehead'] = "Beban Biaya";
		return view($this->url_v.'\vakun_trans_list', $this->data);
	}

    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filters');
        $order      = $this->request->getPost('order');

        $tahun      = $this->request->getPost('tahun');
        $bulan      = $this->request->getPost('bulan');
        
        $params['tahun'] = $tahun;
        $params['bulan'] = $bulan;

        $results = $this->mtrans_akun->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mtrans_akun->getDataCnt($filters, $params);
        $totaldata = $this->mtrans_akun->getDataCnt(null, $params);
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            $id = encrypt($row->trans_akun_id);

            $atr_edit = null; $atr_del = null; $atr_bar = null;

            $is_edit = true; $is_del = true; $is_view = false;
            
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->url_in.'/edit/';
                $atr_edit['class'] = '';
            }

            if ($this->_delete) {
                $atr_del['title'] = 'Hapus';
                $atr_del['url'] = $this->url_in.'/delete/';
                $atr_del['class'] = '';
                $atr_del['onclick'] = "return confirm('Hapus data ?')";
            }

            $btnAction = btn_action_group($id, $atr_edit, $atr_del);

            $date = "";

            $status = "<span class='badge bg-danger'>Draft</span>";

            if (!empty($row->status) && $row->status == 1) {
                $status = "<span class='badge bg-success'>Approved</span>";
            }

            if(!empty($row->trans_akun_date)){
                $date = date('d-m-Y', strtotime($row->trans_akun_date));
            }

            array_push($build_array['data'], array(
               'aksi'            => $btnAction,
               'trans_akun_kode' => $row->trans_akun_kode,
               'ref_rekening'    => $row->rekening_no . " - " . $row->rekening_bank,
               'trans_akun_date' => $date,
               'total'           => $row->total,
               'status'           => $status,
               'keterangan'      => $row->keterangan
            ));

        }

        $output = $build_array["data"];

        $build_array["data"] = $output;
       
        return $this->response->setJSON($build_array);
    }

    public function form($id = null)
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['id'] = $id;

        $this->data['titlehead'] = "Input Beban Biaya";
        if ($id != ""){
            $id = decrypt($id);
            $this->data['titlehead'] = "Edit Beban Biaya";
        }

		$isDisable = false;

        $stdData = new \stdClass();
        $stdData->trans_akun_date = date("d-m-Y");
        $stdData->trans_akun_kode = '';
        $stdData->ref_rekening_id = '';
        $stdData->ref_so_sp = '';
        $stdData->keterangan = '';
        $stdData->status = '';
        $Ldetail = '';
        
        if($id) {
            $stdData = $this->mtrans_akun->getData($id);  
            $stdData->trans_akun_date = !empty($stdData->trans_akun_date) ? \fdate_eng_to_ind_4($stdData->trans_akun_date) : "";  
            $dtData = $this->mtrans_det->getData(null, 0, 9999, null, null, $id);  
            $builds = [];
            $i = 1;
            foreach ($dtData as $r) {
                $isi = [];
                $isi['id'] = $r->trans_akun_det_id;
                $isi['coa_id'] = $r->coa_id;
                $isi['coa_nama'] = $r->coa_kode . " " . $r->coa_nama;
                $isi['keterangan'] = $r->keterangan;
                $isi['jumlah'] = $r->jumlah;
                $isi['seq'] = $i++;
                $builds[] = $isi;
            }
            
            $Ldetail = \json_encode($builds);
            $isDisable = true;
        }

        if($_POST)
		{
            $stdData->trans_akun_date = trim($this->request->getPost('trans_akun_date'));
            $stdData->ref_rekening_id = trim($this->request->getPost('ref_rekening_id'));
            $stdData->ref_so_sp = trim($this->request->getPost('ref_so_sp'));
            $stdData->keterangan = trim($this->request->getPost('keterangan'));
            $Ldetail = trim($this->request->getPost('Ldetail'));
            $stdData->status = trim($this->request->getPost('status'));

           
            $this->validation->setRules([
                'trans_akun_date' => ['label' => 'Tanggal', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'ref_rekening_id' => ['label' => 'Kas/Bank', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                // 'ref_so_sp' => ['label' => 'Ref. SO/Sampel', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'Ldetail' => ['label' => 'Detail', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                // 'no_hp' => ['label' => 'No. Telp.', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()]
            ]);
            
            if ($this->validation->withRequest($this->request)->run() === TRUE)
            {

                $dataIn['trans_akun_date'] = !empty($stdData->trans_akun_date)? fdate_ind_to_eng($stdData->trans_akun_date) : "";
                // $dataIn['coa_parent_id'] = !empty($stdData->coa_parent_id) ? $stdData->coa_parent_id : null;
                $dataIn['ref_rekening_id'] = $stdData->ref_rekening_id;
                $dataIn['ref_so_sp'] = $stdData->ref_so_sp;
                $dataIn['keterangan'] = $stdData->keterangan;
                $dataIn['status'] = $stdData->status;

                $dtDet = \json_decode($Ldetail);
                
                $mtd = "Simpan";
                if(!empty($id)){

                    $dataIn['updated_by'] =  $this->get_userid();
                    $dataIn['updated_date'] = date('Y-m-d H:i:s');
                    $mtd = "Update";
                    
                    $inUp = $this->update($id, $dataIn, $dtDet);
                }else{
                    $dataIn['trans_akun_kode'] = $this->mtrans_akun->generateAutoNo();
                    $dataIn['created_by'] = $this->get_userid();
                    $dataIn['created_date'] = date('Y-m-d H:i:s');
                    $dataIn['active'] = 1;
                    $inUp = $this->insert($dataIn, $dtDet);
                }

                if($inUp){
                    // return redirect()->to('/asesmen/sni_form/'.$idx);
                    $this->session->setFlashdata('message', "{$mtd} data berhasil.." );
                    return redirect()->to('/keuangan/transaksi_akun');
                }else{
                     $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
                    $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
                }
            }else{
                $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", $this->validation->listErrors());
            }
        }

        $dt_rek = $this->mrekening->getData(null, 0, 999);
        $dt_so_sp = $this->mtrans_akun->get_nomor_sample_dan_sales(null, 0, 999);
        $list_rekening[''] = 'Pilih Rekening';
        foreach ($dt_rek as $r) {
            $list_rekening[$r->id] = $r->rekening_no ." - ". $r->rekening_bank;
        }
        $list_so_sp[''] = 'Pilih Referensi';
        foreach ($dt_so_sp as $r) {
            $list_so_sp[$r->nomor] = $r->nomor;
        }
        $this->data['ref_rekening_id'] = array(
            'name' => 'ref_rekening_id',
            'id' => 'ref_rekening_id',
            'value' => set_value('ref_rekening_id', $stdData->ref_rekening_id),
            'options' => $list_rekening,
            'class' => 'form-control'
        );

        $this->data['trans_akun_kode'] = array(
            'id' => 'trans_akun_kode',
            'name'  => 'trans_akun_kode',
            'type' => 'text',
            'readonly' => '',
            'class' => 'form-control',
            'placeholder' => 'No. Transaksi',
            'value' => set_value('trans_akun_kode', $stdData->trans_akun_kode)
        );

        $this->data['trans_akun_date'] = array(
            'id' => 'trans_akun_date',
            'name'  => 'trans_akun_date',
            'type' => 'text',
            'class' => 'form-control datepickerx',
            'placeholder' => 'Tanggal',
            'value' => set_value('trans_akun_date', $stdData->trans_akun_date)
        );

        $this->data['keterangan'] = array(
            'id' => 'keterangan',
            'name'  => 'keterangan',
            'type' => 'text',
            'rows' => '3',
            'class' => 'form-control',
            'placeholder' => 'Keterangan',
            'value' => set_value('keterangan', $stdData->keterangan)
        );

        $this->data['ref_so_sp'] = array(
            'name' => 'ref_so_sp',
            'id' => 'ref_so_sp',
            'value' => set_value('ref_so_sp', $stdData->ref_so_sp),
            'options' => $list_so_sp,
            'class' => 'form-control'
        );

        $this->data['Ldetail'] = array(
            'Ldetail'=> set_value('Ldetail', $Ldetail)
        );
        $this->data['status'] = isset($stdData->status) ? $stdData->status : null;
        $show_save_btn = true;
        if($isDisable && $this->data['status'] == 1){
            $this->data['ref_rekening_id']['disabled'] = null;
            $this->data['trans_akun_date']['readonly'] = null;
            $this->data['keterangan']['readonly'] = null;
            $show_save_btn = false;
        }

        if (empty($stdData->status)) {
            $isDisable = false;
        }

        $this->data["disabled_input"] = $isDisable;
        $this->data["show_save_btn"] = $show_save_btn;

		return view($this->url_v.'\vakun_trans_form', $this->data);
	}

    function insert($dataIn, $dtDetail){
        $user_id = $this->get_userid();
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();

        $id = $this->mtrans_akun->insertRecordGetid($this->mtrans_akun->table, $dataIn);
        
        $total = 0;
        foreach ($dtDetail as $r) {
            $dtIn["trans_akun_id"] = $id;
            $dtIn["coa_id"] = $r->coa_id;
            $dtIn["jumlah"] = $r->jumlah;
            $dtIn["keterangan"] = $r->keterangan;
            $dtIn["created_by"] = $user_id;
            $dtIn["created_date"] = $now;
            $this->mtrans_det->insertRecordGetid($this->mtrans_det->table, $dtIn);

            $total += (float) $r->jumlah;
        }

        $dtUp['total'] = $total;
        $this->mtrans_akun->updateRecord($this->mtrans_akun->table, $dtUp, 'id', $id);

        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            return FALSE;
        } else {
            $this->db->transCommit();
            return TRUE;
        }
    }

    function update($id, $dataIn, $dtDetail){
        $user_id = $this->get_userid();
        $now = date('Y-m-d H:i:s');

        $this->db->transBegin();

        $total = 0;
        $id_det = [];
        foreach ($dtDetail as $r) {
            $dtIn["trans_akun_id"] = $id;
            $dtIn["coa_id"] = $r->coa_id;
            $dtIn["jumlah"] = $r->jumlah;
            $dtIn["keterangan"] = $r->keterangan;
            $dtIn["created_by"] = $user_id;
            $dtIn["created_date"] = $now;
            if (!empty($r->id)) {
                $inUp = $this->mtrans_akun->updateRecord($this->mtrans_det->table, $dtIn, 'id', $r->id);
                $id_det[] = $r->id;
            }
            else {
                $id_det[] = $this->mtrans_det->insertRecordGetid($this->mtrans_det->table, $dtIn);
            }
            $total += (float) $r->jumlah;
        }
        $inUp = $this->mtrans_akun->deleteRecordCondition($this->mtrans_det->table, "id", $id_det, "trans_akun_id", $id);

        $dataIn['total'] = $total;
        $this->mtrans_akun->updateRecord($this->mtrans_akun->table, $dataIn, 'id', $id);
        // if(!empty){

        // }

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
                
        // $res = $this->porudk->deleteRecord($this->porudk->table, 'porudkt_id', $id);
        // if ($res) {
        //     $this->session->setFlashdata('message', "Data berhasil dihapus !");
        // } else {
        //     $this->session->setFlashdata('err', "Data gagal dihapus !");
        // }
        return redirect()->to('/keuangan/transaksi_akun');
    }

    public function deactived($id){
        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int) $id;
        $dataIn['active'] = 0;
        $dataIn['updated_by'] =  $this->auth->getUserId();
        $dataIn['updated_date'] = date('Y-m-d H:i:s');
        $res = $this->mtrans_akun->updateRecord($this->mtrans_akun->table, $dataIn, 'trans_akun_id',$id);
        if ($res) {
            $this->session->setFlashdata('message', "Data berhasil dihapus !");
        } else {
            $this->session->setFlashdata('err', "Data gagal dihapus !");
        }
        return redirect()->to('/keuangan/transaksi_akun');
    }

    public function get_node_org()
    {
        $out = [];
        // $id = decrypt($id);
        $results = $this->mcoa->getData(null, 0, 999999, null, null, null, 1);
        
        if (!empty($results)) {
            // build tree
            $output = [];
            foreach ($results as $row) {
                // $compCode_sap = $row->compCode_sap;
                $arr = [];
                $arr['id'] = $row->coa_id;
                $arr['parent_id'] = $row->parent_id;
                $arr['unit_name'] = " {$row->kode} - {$row->nama}";
                $output[] = $arr;
            }
            // $out = build_tree($output, 'parent_id', 'id', null);
            $out = ($output);
        }

        return $this->response->setJSON(json_encode($out));
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

    // buat fungsi  export excel
    public function exp_mutasi($start_bulan, $start_tahun){

        $fileName = "beban-biaya-".bulan($start_bulan)."-".time().".xlsx";

        $params['bulan'] = $start_bulan;
        $params['tahun'] = $start_tahun;
        $results = $this->mtrans_akun->getData(null, 0, 9999, null, null, $params);

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Beban Biaya ')

               ->setCellValue('A4', 'No. Transaksi')
               ->setCellValue('B4', 'Tgl Transaksi')
               ->setCellValue('C4', 'Akun Bank')
               ->setCellValue('D4', 'Keterangan Transaksi')
               ->setCellValue('E4', 'Akun')
               ->setCellValue('F4', 'Keterangan Nilai')
               ->setCellValue('G4', 'Nilai');

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
            
        $gets->getStyle('A4:G4')->applyFromArray($styleArray_header);
        
        // set mergecell
        $sheets->getActiveSheet()->mergeCells('A2:G2');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(20);
          $gets->getColumnDimension('C')->setWidth(20);
          $gets->getColumnDimension('D')->setWidth(40);
          $gets->getColumnDimension('E')->setWidth(20);
          $gets->getColumnDimension('F')->setWidth(40);
          $gets->getColumnDimension('G')->setWidth(20);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:G4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:G4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'
        );

        for ($i=0; $i < 7 ; $i++) { 

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
        }else{
            $length = 0;
        }

        $bckColor = "F2F2F2";
        $ig = $ix;
        $ip = $ix;
        
        $g_id = null; 
        $p_id = null; 

        for ($xx = 0; $xx < count($results) ; $xx++) { 
            $r = $results[$xx];

            $oder_date = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( $r->trans_akun_date ); 

            $gets->getStyle('B' . $ix)
                        ->getNumberFormat()
                        ->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME );
            
            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, $r->trans_akun_kode)
                    ->setCellValue('B'.$ix, $oder_date) 
                    ->setCellValue('C'.$ix, $r->rekening_no . " - " . $r->rekening_bank)
                    ->setCellValue('D'.$ix, $r->keterangan);
            
            $dtData = $this->mtrans_det->getData(null, 0, 9999, null, null, $r->trans_akun_id);
            // dd($dtData);
            if(!empty($dtData)){
                foreach ($dtData as $xr) {
                    $sheets->setActiveSheetIndex(0)->setCellValue('E' . $ix, $xr->coa_kode . " " . $xr->coa_nama); 
                    $sheets->setActiveSheetIndex(0)->setCellValue('F' . $ix, $xr->keterangan); 
                    $sheets->setActiveSheetIndex(0)->setCellValue('G' . $ix, $xr->jumlah); 
                    $gets->getStyle('A'.$ix.':G'.$ix)->applyFromArray($stylexArray);
                    $ix++;
                }
            }else{
                $gets->getStyle('A'.$ix.':G'.$ix)->applyFromArray($stylexArray);
                $ix++;
            }
                  
           
        }
        
        
        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
		
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); // download file 
        exit;
    }

    public function exp_mutasi_($start_bulan, $start_tahun)
    {

        $date = date('d-m-y-'.substr((string)microtime(), 1, 8));
        $date = str_replace(".", "", $date);
        $filename = "beban-biaya-".bulan($start_bulan)."-".time().".xlsx";

        $params['bulan'] = $start_bulan;
        $params['tahun'] = $start_tahun;
        $results = $this->mtrans_akun->getData(null, 0, 9999, null, null, $params);


        $spreadsheet = new Spreadsheet();

        $styleTitle = [
            'font' => [
                'bold'  =>  true,
                'size'  =>  14,
                'name'  =>  'Arial'
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
        ];

        ## BAGIAN TITLE ##
        $sheetTitle = $spreadsheet->getActiveSheet();
        $sheetTitle->mergeCells('A1:E2');
        $sheetTitle->getStyle('A1:E2')->applyFromArray($styleTitle);
        $sheetTitle->setCellValue('A1', "Beban Biaya");

        ## BAGIAN HEADER PADA EXCEL ##
        $sheetHeader = $spreadsheet->getActiveSheet();
        $styleHeader = [
            'font' => [
                'bold'  =>  true,
                'size'  =>  11,
                'name'  =>  'Arial'
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '32CD32')
            ]
        ];

        $sheetHeader->getColumnDimension('A')->setWidth(18);
        $sheetHeader->getColumnDimension('B')->setWidth(18);
        $sheetHeader->getColumnDimension('C')->setWidth(30);
        $sheetHeader->getColumnDimension('D')->setWidth(30);
        $sheetHeader->getColumnDimension('E')->setWidth(18);
        $sheetHeader->getColumnDimension('F')->setWidth(18);
        $sheetHeader->getColumnDimension('G')->setWidth(25);
        $sheetHeader->getStyle('A3:G3')->applyFromArray($styleHeader);

        $sheetHeader->setCellValue('A3', 'No. Transaksi');
        $sheetHeader->setCellValue('B3', 'Tanggal');
        $sheetHeader->setCellValue('C3', 'Akun Bank');
        $sheetHeader->setCellValue('D3', 'Keterangan Transaksi');
        $sheetHeader->setCellValue('E3', 'Akun');
        $sheetHeader->setCellValue('F3', 'Keterangan Nilai');
        $sheetHeader->setCellValue('G3', 'Nilai');


         ## BAGIAN ROW PADA EXCEL ##
         $sheetIsi = $spreadsheet->getActiveSheet();
  
         $StylesheetIsiBorder = [
             'borders' => [
                 'allBorders' => [
                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                     'color' => ['rgb' => '000000']
                 ]
             ]
         ];
 
         $StylesheetIsiColorGreen = [
             'fill' => [
                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                 'startColor' => array('argb' => '32CD32')
             ]
         ];

        $trans_fake_date = 0;
        $rows = 4;
        $start = 0;

        $Awal = 0;
        $Akhir = 0;


        foreach ($results as $r) {
             $sheetIsi->getStyle('A' . $rows . ':G' . $rows)->applyFromArray($StylesheetIsiBorder);
             $tgl = !empty($r->trans_akun_date) ? fdate_eng_to_ind_3($r->trans_akun_date) : '-';
             
             
             $sheetIsi->setCellValue('A' . $rows, $r->trans_akun_kode);
             $sheetIsi->setCellValue('B' . $rows, $tgl); 
             $sheetIsi->setCellValue('C' . $rows, $r->rekening_no . " - " . $r->rekening_bank); 
             $sheetIsi->setCellValue('D' . $rows, $r->keterangan); 
             

             $dtData = $this->mtrans_det->getData(null, 0, 9999, null, null, $r->trans_akun_id);

             if(!empty($dtData)){
                foreach ($dtData as $xr) {
                    $sheetIsi->setCellValue('E' . $rows, $xr->kode . " " . $xr->nama); 
                    $sheetIsi->setCellValue('F' . $rows, $xr->keterangan); 
                    $sheetIsi->setCellValue('G' . $rows, $xr->jumlah); 
                    $rows++;
                }
             }else{
                $rows++;
             }
        }

        
        ## FOOTER SHEET ##
        $sheetFooter1 = $spreadsheet->getActiveSheet();
        $sheetFooter2 = $spreadsheet->getActiveSheet();
        $StylesheetFooterBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'font' => [
                'bold'  =>  true,
                'size'  =>  11,
                'name'  =>  'Arial'
            ],
        ];

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); // download file 
        exit;


    }
}
