<?php

namespace Modules\Purchasing\Controllers;

use DateTime;
use CodeIgniter\Controller;
use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Purchasing\Models\PurchaseModel;
use Modules\Purchasing\Models\PurchaseDetailModel;

class PurchaseOrder extends BaseController
{
  protected $views = '\Modules\Purchasing\Views';
  protected $mPO;
  protected $mPODetail;
  protected $urlv  = 'purchasing/purchase-order';

  function __construct()
  {
    $this->MOD_ALIAS = "MOD_PURCHASE_ORDER";
    $this->mPO = new PurchaseModel();
    $this->mPODetail = new PurchaseDetailModel();
  }

  public function index()
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }


    $this->data['titlehead'] = "Purchase Order";

    return view($this->views . '\purchase_order_list', $this->data);
  }

  public function lists()
  {
    $start      = $this->request->getPost('start');
    $limit      = $this->request->getPost('length');
    $filters    = $this->request->getPost('filter');
    $order      = $this->request->getPost('sort');
    $isReceive      = $this->request->getPost('isReceive');

    $params = [];
    $params['isReceive'] = $isReceive;

    $results = $this->mPO->getData(null, $start, $limit, $order, $filters, $params);
    $totalfiltered = $this->mPO->getDataCnt($filters, $params);
    $totaldata = $this->mPO->getDataCnt(null, $params);
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
      $btnAction = null;
      // if ($this->_edit) {
      $atr_edit['title'] = 'Edit';
      $atr_edit['url'] = $this->urlv . '/form/';
      $atr_edit['class'] = '';
      // }
      if ($row->status != 0) {
        $atr_other['title'] = 'Print';
        $atr_other['target'] = "blank";
        $atr_other['url'] = $this->urlv . '/print/';
        $atr_other['class'] = '';
        $atr_other['icon_class'] = 'fa-print';
      }

      // $atr_del['onclick'] = "return confirm('Hapus Data ?')";

      if ($atr_edit || $atr_other)
        $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);




      // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
      //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";
      $status = "";
      if ($row->status == 0) {
        $status = "<span class='badge bg-secondary'>Menunggu<br>Pembayaran</span>";
      } else if ($row->status == 1) {
        $status = "<span class='badge bg-info'>Dibayar Sebagian</span>";
      } else if ($row->status == 2) {
        $status = "<span class='badge bg-success'>Dibayar Penuh</span>";
      }
      $approve_status = "";
      if ($row->approve_status == 1) {
        $approve_status = "<span class='badge bg-info'>APPROVED</span>";
      } else {
        $approve_status = "<span class='badge bg-warning'>DRAFT</span>";
      } 
      array_push(
        $build_array["data"],
        array(
          "aksi" => $btnAction ? $btnAction : '',
          "id"   => ($id),
          "nama_vendor" => $row->nama_vendor,
          "po_no" => $row->po_no,
          // "term" => $row->term,
          "po_date" => fdate_eng_to_ind($row->po_date),
          "po_date_exp" => !empty($row->po_date_exp) ? fdate_eng_to_ind($row->po_date_exp) : null,
          "date_exc" => $row->date_exc,
          "qty" => $this->formatAngka($row->qty_payment) . "/" . $this->formatAngka($row->qty),
          "total" => $row->total,
          "total_payment" => $row->total_payment,
          "sisa" => (double)$row->total -  (double)$row->total_payment,
          "status" => $status,
          "approve_status" => $approve_status
        )
      );
    }
    return $this->response->setJSON($build_array);
  }

  function formatAngka($angka) {
      return rtrim(rtrim(number_format($angka, 2, ".", ""), "0"), ".");
  }

  public function form($id = null)
  {
    if (!$this->auth->loggedIn()) {
      return redirect()->to('/auth/login');
    }


    $this->data['id'] = $id;
    if ($id != "") {
      $id = decrypt($id);
      $resData = $this->mPO->getData($id);
      $resData->id_vendor = encrypt($resData->id_vendor);
      $poDate = date("d F Y", strtotime($resData->po_date));
      $poDateExp = !empty($resData->po_date_exp) ? date("d F Y", strtotime($resData->po_date_exp)) : null;
      $dateExc = date("d F Y", strtotime($resData->date_exc));
      $resData->po_date = $poDate;
      $resData->po_date_exp = $poDateExp;
      $resData->date_exc = $dateExc;

      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];

      $resDataDetail = $this->mPODetail->getData(null, 0, 99999, $sort, params: array("id_header" => $id));
      foreach ($resDataDetail as &$rowData) {
        $rowData->id_barang = encrypt($rowData->id_barang);
      }
      $this->data['resData'] = $resData;
      $this->data['detail'] = json_encode($resDataDetail);
    }


    $this->data['titlehead'] = "Form Purchase Order";
    $this->data['shipTo'] = null;
    $addNew = $this->mPO->getData(null, null, null, null, null, null, true);
    if (isset($addNew) && empty($id)) {
      $this->data['shipTo'] = $addNew->ship_to;
    }
    $resTerm = $this->mPO->getRefTerm();
    $resTax = $this->mPO->getRefTax();
    $this->data['term'] = $resTerm;
    $this->data['tax'] = $resTax;
    return view($this->views . '\purchase_order_form', $this->data);
  }

  public function save()
  {
    $msg    = "Data gagal disimpan !";
    $status = false;
    $id = $this->request->getPost('id');
    $id_vendor = $this->request->getPost('id_vendor');
    $po_date = $this->request->getPost('po_date');
    $po_date_exp = $this->request->getPost('po_date_exp');
    $date_exc = $this->request->getPost('date_exc');
    $id_term = $this->request->getPost('id_term');
    $ship_to = $this->request->getPost('ship_to');
    $keterangan = $this->request->getPost('keterangan');
    $total = $this->request->getPost('total');
    $qty = $this->request->getPost('qty');
    $dataDetail = $this->request->getPost('data');
    $buttonType = $this->request->getPost('buttonType');
    $approve_status = 0;
    if (isset($buttonType) && $buttonType == "approve") {
      $approve_status = 1;
    }
    if ($id != "") {
      $id = decrypt($id);
    }
    if ($id_vendor != "") {
      $id_vendor = decrypt($id_vendor);
    }
    $dataHeader = [
      "id_vendor" => $id_vendor,
      "po_date" => $po_date,
      "po_date_exp" => $po_date_exp,
      "date_exc" => $date_exc,
      "id_term" => $id_term,
      "ship_to" => $ship_to,
      "keterangan" => $keterangan,
      "qty_payment" => 0,
      "total_payment" => 0,
      "total" => $total,
      "qty" => $qty,
      "status" => 0,
      "approve_status" => $approve_status,
    ];
    if ($id) {
      $dataHeader['updated_at'] = date("Y-m-d H:i:s");
      $dataHeader['updated_by'] = $this->get_userid();
    } else {
      $dataHeader['created_at'] = date("Y-m-d H:i:s");
      $dataHeader['created_by'] = $this->get_userid();
    }
    // print_r($data);exit;
    $res = $this->mPO->trxInsertUpdateRecord($dataHeader, $id, $dataDetail);
    if ($res) {
      $status = true;
      $msg = "Data berhasil disimpan!";
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
    $dompdf = new DompdfGenerator();

    $this->data['data'] = [];
    if ($id != "") {
      $id = decrypt($id);
      // dd($id);
      // die;
      $resData = $this->mPO->getData($id);
      $sort = [
        [
          'field' => 'uk.id',
          'dir' => 'ASC'
        ]
      ];
      $resDataDetail = $this->mPODetail->getData(null, 0, 99999, $sort, params: array("id_header" => $id));
      $this->data['data'] = !empty($resData) ? $resData : [];
      $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
    }
    $html = view($this->views . '\purchase_order_print', $this->data);


    $dompdf->generate($html, 'po.pdf', true);
    exit;
  }

  public function checkUnitPrice()
  {

    $build_array = [];
    $build_array["code"] = 200;
    $build_array["status"] = false;
    $build_array["unit_price"] = null;

    $id_vendor = $this->request->getGet('id_vendor');
    $id_barang = $this->request->getGet('id_barang');

    $oldUnitPrice = $this->mPODetail->getUnitPrice($id_vendor, $id_barang);
    if (isset($oldUnitPrice)) {
      $build_array["unit_price"] = $oldUnitPrice->price;
      $build_array["status"] = true;
    }
    return $this->response->setJSON($build_array);
  }

  public function print_excel_lists($from_date, $to_date){

        $fileName = "PO-List.xlsx";

        $id = $this->request->getGet('data_id');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mPO->get_export($tanggal_sql_from, $tanggal_sql_to);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Purchase Order '. $title)

               ->setCellValue('A4', 'NOMOR PO')
               ->setCellValue('B4', 'TANGGAL PO')
               ->setCellValue('C4', 'NAMA VENDOR')
               ->setCellValue('D4', 'QTY PO')
               ->setCellValue('E4', 'QTY TERIMA')
               ->setCellValue('F4', 'NILAI PO')
               ->setCellValue('G4', 'DISKON')
               ->setCellValue('H4', 'NILAI PEMBAYARAN')
               ->setCellValue('I4', 'SISA PEMBAYARAN');

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
        $gets->getColumnDimension('A')->setWidth(25);
        $gets->getColumnDimension('B')->setWidth(20);
        $gets->getColumnDimension('C')->setWidth(40);
        $gets->getColumnDimension('D')->setWidth(20);
        $gets->getColumnDimension('E')->setWidth(20);
        $gets->getColumnDimension('F')->setWidth(35);
        $gets->getColumnDimension('G')->setWidth(25);
        $gets->getColumnDimension('H')->setWidth(35);
        $gets->getColumnDimension('I')->setWidth(35);
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

        $qty = 0;
        $amount = 0;
        $total_qty = 0;
        $total_qty_receive = 0;
        $total_po = 0;
        $total_payment = 0;
        $total_diskon = 0;
        $sisa = 0;
        
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $payment = !empty($r->total_payment) ? $r->total_payment : 0;
            $total = !empty($r->total) ? $r->total : 0;
            $diskon = !empty($r->diskon) ? $r->diskon : 0;
            $afterDiscount = $payment - $diskon;

            $total_qty += $r->qty; 
            $total_qty_receive += $r->qty_receive; 
            $total_po += $total; 
            $total_diskon += $diskon; 
            $total_payment += $afterDiscount; 
            $sisa += $total - $afterDiscount - $diskon; 

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->po_no) ? $r->po_no : "-")
                    ->setCellValue('B'.$ix, !empty($r->po_date) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->po_date)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->nama_vendor) ? $r->nama_vendor : "-")
                    ->setCellValue('D'.$ix, !empty($r->qty) ? $r->qty : '-')
                    ->setCellValue('E'.$ix, !empty($r->qty_receive) ? $r->qty_receive : '-')
                    ->setCellValue('F'.$ix, $total)
                    ->setCellValue('G'.$ix, $diskon)
                    ->setCellValue('H'.$ix, $afterDiscount)
                    ->setCellValue('I'.$ix, $total - $afterDiscount - $diskon);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("F" . $ix .":I" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':C'. $length);
        
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('D'.$length, $total_qty);

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('E'.$length, $total_qty_receive);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('F'.$length, $total_po);

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('G'.$length, $total_diskon);

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('H'.$length, $total_payment);

        $sheets->setActiveSheetIndex(0)
                      ->setCellValue('I'.$length, $sisa);

        $gets->getStyle('F'. $length .':I'. $length)->getNumberFormat()
                ->setFormatCode('#,##0.00');
        
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
