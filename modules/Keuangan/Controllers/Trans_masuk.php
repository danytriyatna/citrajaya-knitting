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

class Trans_masuk extends BaseController
{   
    private $url_v = "Modules\Keuangan\Views";
    private $url_in = "/keuangan/transaksi_masuk";

    protected $mcoa;
    protected $mtrans_akun;
    protected $mtrans_det;
    protected $mrekening;

	function __construct()
    {
        $this->MOD_ALIAS = "MOD_TRANS_AKUN_MASUK";
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
        if (empty($ltahun)) {
            $ltahun[date('Y')] = date('Y');
        }

        $this->data['tahun_trans'] = array(
            'name' => 'tahun_trans',
            'id' => 'tahun_trans',
            'options' => $ltahun,
            'selected' => (int)date('Y'),
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
            'selected' => (int)date('m'),
            'class' => 'form-control'
        );

		$this->data['titlehead'] = "Kas / Bank Masuk";
		return view($this->url_v.'\vakun_trans_masuk_list', $this->data);
	}

    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filters');
        if (empty($filters)) { $filters = $this->request->getPost('filter'); }
        $order      = $this->request->getPost('order');

        $tahun      = $this->request->getPost('tahun');
        $bulan      = $this->request->getPost('bulan');
        
        if (empty($tahun)) {
            $tahun = date('Y');
        }
        if (empty($bulan)) {
            $bulan = (int)date('m');
        }

        $params['tahun'] = $tahun;
        $params['bulan'] = $bulan;
        $params['tipe_transaksi'] = 2; // 2: Kas Masuk

        $results = $this->mtrans_akun->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mtrans_akun->getDataCnt($filters, $params);
        $totaldata = (!empty($filters) && !empty($filters[0]['value'])) ? $this->mtrans_akun->getDataCnt(null, $params) : $totalfiltered;
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );

        foreach ($results as $row) {
            $id = encrypt($row->trans_akun_id);

            $atr_edit = null; $atr_del = null;

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
               'status'          => $status,
               'keterangan'      => $row->keterangan
            ));
        }

        return $this->response->setJSON($build_array);
    }

    public function form($id = null)
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['id'] = $id;

        $this->data['titlehead'] = "Input Kas / Bank Masuk";
        if ($id != ""){
            $id = decrypt($id);
            $this->data['titlehead'] = "Edit Kas / Bank Masuk";
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
            $stdData->trans_akun_date = date("d F Y", strtotime($stdData->trans_akun_date));
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
                'ref_rekening_id' => ['label' => 'Kas/Bank Penerima', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
                'Ldetail' => ['label' => 'Detail', 'rules' => 'required', 'errors' =>  $this->validation_msg_error()],
            ]);
            
            if ($this->validation->withRequest($this->request)->run() === TRUE)
            {
                $dataIn['trans_akun_date'] = !empty($stdData->trans_akun_date)? fdate_ind_to_eng($stdData->trans_akun_date) : "";
                $dataIn['ref_rekening_id'] = $stdData->ref_rekening_id;
                $dataIn['ref_so_sp'] = $stdData->ref_so_sp;
                $dataIn['keterangan'] = $stdData->keterangan;
                $dataIn['status'] = $stdData->status;
                $dataIn['tipe_transaksi'] = 2; // 2: Kas Masuk

                $dtDet = \json_decode($Ldetail);
                
                $mtd = "Simpan";
                if(!empty($id)){
                    $dataIn['updated_by'] =  $this->get_userid();
                    $dataIn['updated_date'] = date('Y-m-d H:i:s');
                    $mtd = "Update";
                    
                    $inUp = $this->update($id, $dataIn, $dtDet);
                }else{
                    $dataIn['trans_akun_kode'] = $this->mtrans_akun->generateAutoNo("KM.");
                    $dataIn['created_by'] = $this->get_userid();
                    $dataIn['created_date'] = date('Y-m-d H:i:s');
                    $dataIn['active'] = 1;
                    $inUp = $this->insert($dataIn, $dtDet);
                }

                if($inUp){
                    $this->session->setFlashdata('message', "{$mtd} data berhasil.." );
                    return redirect()->to('/keuangan/transaksi_masuk');
                }else{
                    $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", "Ulangi simpan data !");
                }
            }else{
                $this->data['errmsg'] = $this->_get_message("ERROR_VALIDATION", $this->validation->listErrors());
            }
        }

        $order_rek = [
            ['field' => 'mc.kode', 'dir' => 'ASC']
        ];
        $dt_rek = $this->mrekening->getData(null, 0, 999, $order_rek);
        $dt_so_sp = $this->mtrans_akun->get_nomor_sample_dan_sales(null, 0, 999);
        $list_rekening[''] = '- Pilih Kas/Bank -';
        foreach ($dt_rek as $r) {
            $kode = !empty($r->coa_kode) ? $r->coa_kode : (!empty($r->rekening_no) ? $r->rekening_no : '-');
            $nama = !empty($r->coa_nama) ? $r->coa_nama : $r->rekening_bank;
            if (!empty($r->rekening_no) && !empty($r->coa_kode) && $r->rekening_no != $r->coa_kode && $r->rekening_no != '-') {
                $label = "{$kode} - {$nama} ({$r->rekening_no})";
            } else {
                $label = "{$kode} - {$nama}";
            }
            $list_rekening[$r->id] = $label;
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
            'placeholder' => 'No. Transaksi (KM.YYYYMM.XXX)',
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
            'placeholder' => 'Keterangan Penerimaan',
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

		return view($this->url_v.'\vakun_trans_masuk_form', $this->data);
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
        return redirect()->to('/keuangan/transaksi_masuk');
    }

    public function deactived($id){
        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int) $id;
        $dataIn['active'] = 0;
        $dataIn['updated_by'] =  $this->auth->getUserId();
        $dataIn['updated_date'] = date('Y-m-d H:i:s');
        $res = $this->mtrans_akun->updateRecord($this->mtrans_akun->table, $dataIn, 'id', $id);
        if ($res) {
            $this->session->setFlashdata('message', "Data berhasil dihapus !");
        } else {
            $this->session->setFlashdata('err', "Data gagal dihapus !");
        }
        return redirect()->to('/keuangan/transaksi_masuk');
    }

    public function get_node_org()
    {
        $out = [];
        $results = $this->mcoa->getData(null, 0, 999999, null, null, null, 1);
        
        if (!empty($results)) {
            $output = [];
            foreach ($results as $row) {
                $arr = [];
                $arr['id'] = $row->coa_id;
                $arr['parent_id'] = $row->parent_id;
                $arr['unit_name'] = " {$row->kode} - {$row->nama}";
                $output[] = $arr;
            }
            $out = ($output);
        }

        return $this->response->setJSON(json_encode($out));
    }

    function _get_message($msg_type, $message = '', $mode = 'success', $icons = 'check', $fadeOut = true)
    {
        $title = '';
        switch ($msg_type) {
            case 'SUCCESS_INSERTED': $title = 'Tambah Berhasil'; $message = 'Penambahan data berhasil dilakukan.'; $icons = 'check'; break;
            case 'SUCCESS_UPDATED': $title = 'Update Berhasil'; $message = 'Pembaharuan data berhasil dilakukan.'; $icons = 'check'; break;
            case 'SUCCESS_DELETED': $title = 'Hapus Berhasil'; $message = 'Penghapusan data berhasil dilakukan.'; $icons = 'check'; break;
            case 'ERROR_VALIDATION': $title = ''; $mode = 'danger'; $icons = ''; $fadeOut = false; break;
        }

        $html = message_box($title, $message, $mode, $icons, $fadeOut);
        return $html;
    }

    public function exp_mutasi($start_bulan, $start_tahun){
        $fileName = "kas-masuk-".bulan($start_bulan)."-".time().".xlsx";

        $params['bulan'] = $start_bulan;
        $params['tahun'] = $start_tahun;
        $params['tipe_transaksi'] = 2; // Kas Masuk
        $results = $this->mtrans_akun->getData(null, 0, 9999, null, null, $params);

        $sheets = new Spreadsheet;
        $gets = $sheets->getActiveSheet();
        
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Kas / Bank Masuk ')
               ->setCellValue('A4', 'No. Transaksi')
               ->setCellValue('B4', 'Tgl Transaksi')
               ->setCellValue('C4', 'Kas/Bank Penerima')
               ->setCellValue('D4', 'Keterangan Transaksi')
               ->setCellValue('E4', 'Akun Sumber')
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

        $gets->getStyle('A4:G4')->applyFromArray($stylexArray);
        $gets->getStyle('A4:G4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);

        $i = 5;
        $total = 0;
        foreach ($results as $r) {
            $dtData = $this->mtrans_det->getData(null, 0, 9999, null, null, $r->trans_akun_id);
            $rowspan = count($dtData);
            if ($rowspan > 0) {
                $endRow = $i + $rowspan - 1;
                $gets->mergeCells("A{$i}:A{$endRow}");
                $gets->mergeCells("B{$i}:B{$endRow}");
                $gets->mergeCells("C{$i}:C{$endRow}");
                $gets->mergeCells("D{$i}:D{$endRow}");

                $gets->setCellValue("A{$i}", $r->trans_akun_kode);
                $gets->setCellValue("B{$i}", date('d-m-Y', strtotime($r->trans_akun_date)));
                $gets->setCellValue("C{$i}", $r->rekening_no . " - " . $r->rekening_bank);
                $gets->setCellValue("D{$i}", $r->keterangan);

                foreach ($dtData as $d) {
                    $gets->setCellValue("E{$i}", $d->coa_kode . " - " . $d->coa_nama);
                    $gets->setCellValue("F{$i}", $d->keterangan);
                    $gets->setCellValue("G{$i}", $d->jumlah);
                    $gets->getStyle("G{$i}")->getNumberFormat()->setFormatCode('#,##0');
                    $gets->getStyle("A{$i}:G{$i}")->applyFromArray($styleArray);
                    $total += (float)$d->jumlah;
                    $i++;
                }
            }
        }

        $gets->setCellValue("A{$i}", "Total");
        $gets->mergeCells("A{$i}:F{$i}");
        $gets->setCellValue("G{$i}", $total);
        $gets->getStyle("G{$i}")->getNumberFormat()->setFormatCode('#,##0');
        $gets->getStyle("A{$i}:G{$i}")->applyFromArray($stylexArray);
        $gets->getStyle("A{$i}:G{$i}")->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);

        $writer = new Xlsx($sheets);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $fileName .'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}