<?php

namespace Modules\Keuangan\Controllers;

// user library spreadsheet for excel
use DateTime;
use App\Models\FileModel;
// use PhpOffice\PhpSpreadsheet\Reader\Csv;
use CodeIgniter\Controller;

use Modules\Keuangan\Models\Mcoa;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Keuangan\Models\Mtrans_akun;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\RekeningModel;
use Modules\Keuangan\Models\Mtrans_akun_det;
use Modules\Laporan\Models\LaporanStockCardModel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Xlsx_r;


class Rpt_mutasi extends BaseController
{   
    private $url_v = "Modules\Keuangan\Views";
    private $url_in = "/keuangan/laporan_mutasi";

    protected $mcoa;
    protected $mtrans_akun;
    protected $mtrans_det;
    protected $mrekening;
    protected $mRekening;
    protected $mLaporan;

	function __construct()
    {
        $this->MOD_ALIAS = "MOD_LAPORAN_BEBAN";
        $this->mcoa = new Mcoa();
        $this->mtrans_akun = new Mtrans_akun();
        $this->mtrans_det = new Mtrans_akun_det();
        $this->mrekening = new RekeningModel();
        $this->mRekening   = new RekeningModel();
        $this->mLaporan = new LaporanStockCardModel();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $dt_rek = $this->mtrans_akun->get_tahun();
        $list_tahun[''] = 'Pilih Tahun';
        foreach ($dt_rek as $r) {
            $list_tahun[$r->tahun] = $r->tahun;
        }
        $this->data['slc_tahun'] = array(
            'name' => 'slc_tahun',
            'id' => 'slc_tahun',
            'options' => $list_tahun,
            'class' => 'form-control'
        );

        $resTahun = $this->mLaporan->getTahun();
        $resBulan = $this->mLaporan->getBulan();
        $this->data['tahun']    = $resTahun;
        $this->data['bulan']    = $resBulan;

		$this->data['titlehead'] = "Laporan Mutasi Beban Biaya";
        $this->data['rekening_list'] = $this->mRekening->getData(null, 0, 9999);
        $this->data['coa_list'] = $this->mcoa->getData(null, 0, 9999, null, null, null, null, null, 8);
		return view($this->url_v.'\vakun_rep_mutasi', $this->data);
	}

    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filters');
        $order      = $this->request->getPost('order');
        $tahun      = $this->request->getPost('tahun');
        

        if(empty($tahun)){
            $tahun  = '9999';
        }

        $results = $this->mcoa->get_mutasi($tahun);
        $totalfiltered = $this->mcoa->getDataCnt($filters);
        $totaldata = $this->mcoa->getDataCnt();
        // $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => 1,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );
        // 
        foreach ($results as $row) {
            $nama =  $row->kode ." - " .$row->nama;
            if($row->level == 1){
                $nama = "<b>". $row->kode ." - " .$row->nama. "</b>";
            }
            array_push($build_array['data'], array(
               'coa_kode' => $row->kode,
               'id' => $row->id,
               'parent_id' => $row->parent_id,
               'coa_nama' => $nama,
               'bln1' => !empty($row->bln1) ? $row->bln1 : 0,
               'bln2' => !empty($row->bln2) ? $row->bln2 : 0,
               'bln3' => !empty($row->bln3) ? $row->bln3 : 0,
               'bln4' => !empty($row->bln4) ? $row->bln4 : 0,
               'bln5' => !empty($row->bln5) ? $row->bln5 : 0,
               'bln6' => !empty($row->bln6) ? $row->bln6 : 0,
               'bln7' => !empty($row->bln7) ? $row->bln7 : 0,
               'bln8' => !empty($row->bln8) ? $row->bln8 : 0,
               'bln9' => !empty($row->bln9) ? $row->bln9 : 0,
               'bln10' => !empty($row->bln10) ? $row->bln10 : 0,
               'bln11' => !empty($row->bln11) ? $row->bln11 : 0,
               'bln12' => !empty($row->bln12) ? $row->bln12 : 0
            ));

        }

        $output = $build_array["data"];
        $out = build_tree($output, 'parent_id', 'id', null);
        $build_array["data"] = $out;
       
        return $this->response->setJSON($build_array);
    }

    public function exp_mutasi($periode){
        $fileName = "laporan-mutasi-periode-{$periode}.xlsx";

        $results = $this->mcoa->get_mutasi($periode);

        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Laporan Mutasi Periode ' . $periode)

               ->setCellValue('A4', 'Nomor Akun')
               ->setCellValue('B4', 'Akun')
               ->setCellValue('C4', 'Januari')
               ->setCellValue('D4', 'Februrari')
               ->setCellValue('E4', 'Maret')
               ->setCellValue('F4', 'April')
               ->setCellValue('G4', 'Mei')
               ->setCellValue('H4', 'Juni')
               ->setCellValue('I4', 'Juli')
               ->setCellValue('J4', 'Agustus')
               ->setCellValue('K4', 'September')
               ->setCellValue('L4', 'Oktober')
               ->setCellValue('M4', 'November')
               ->setCellValue('N4', 'Desember');

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
            
        $gets->getStyle('A4:N4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('A2:N2');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(40);
          $gets->getColumnDimension('C')->setWidth(20);
          $gets->getColumnDimension('D')->setWidth(20);
          $gets->getColumnDimension('E')->setWidth(20);
          $gets->getColumnDimension('F')->setWidth(20);
          $gets->getColumnDimension('G')->setWidth(20);
          $gets->getColumnDimension('H')->setWidth(20);
          $gets->getColumnDimension('I')->setWidth(20);
          $gets->getColumnDimension('J')->setWidth(20);
          $gets->getColumnDimension('K')->setWidth(20);
          $gets->getColumnDimension('J')->setWidth(20);
          $gets->getColumnDimension('L')->setWidth(20);
          $gets->getColumnDimension('M')->setWidth(20);
          $gets->getColumnDimension('N')->setWidth(20);
        //   $gets->getColumnDimension('O')->setWidth(20);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:N4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:N4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'
        );

        for ($i=0; $i < 14 ; $i++) { 

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
        // for ($i=1; $i <= 4 ; $i++) { 
        //     // declaration image
        //     $isR = $ix * $is;
        //     if($is > 0){
        //         $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        //         $drawing->setName('Paid');
        //         $drawing->setDescription('Paid');
        //         $drawing->setPath(ROOTPATH . 'public/assets/images/text-excel.png');
        //         $drawing->setCoordinates('C'.$isR);
        //         $drawing->setOffsetX(85);
        //         $drawing->setRotation(-35);
        //         // $drawing->getShadow()->setVisible(false);
        //         // $drawing->getShadow()->setDirection(45);
        //         $drawing->setHeight(65);
        //         $drawing->setWorksheet($gets);
        //     }

        //     $is += $ix;
        // }
        
        
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

        $bln1 = 0;
        $bln2 = 0;
        $bln3 = 0;
        $bln4 = 0;
        $bln5 = 0;
        $bln6 = 0;
        $bln7 = 0;
        $bln8 = 0;
        $bln9 = 0;
        $bln10 = 0;
        $bln11 = 0;
        $bln12 = 0;

        for ($xx = 0; $xx < count($results) ; $xx++) { 
            $r = $results[$xx];

            $bln1 += !empty($r->bln1) ? (float) $r->bln1 : 0 ; 
            $bln2 += !empty($r->bln2) ? (float) $r->bln2 : 0 ; 
            $bln3 += !empty($r->bln3) ? (float) $r->bln3 : 0 ; 
            $bln4 += !empty($r->bln4) ? (float) $r->bln4 : 0 ; 
            $bln5 += !empty($r->bln5) ? (float) $r->bln5 : 0 ; 
            $bln6 += !empty($r->bln6) ? (float) $r->bln6 : 0 ; 
            $bln7 += !empty($r->bln7) ? (float) $r->bln7 : 0 ; 
            $bln8 += !empty($r->bln8) ? (float) $r->bln8 : 0 ; 
            $bln9 += !empty($r->bln9) ? (float) $r->bln9 : 0 ; 
            $bln10 += !empty($r->bln10) ? (float) $r->bln10 : 0 ; 
            $bln11 += !empty($r->bln11) ? (float) $r->bln11 : 0 ; 
            $bln12 += !empty($r->bln12) ? (float) $r->bln12 : 0 ; 
            
            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, ($r->level != 1) ? '       ' . $r->kode : ' '.$r->nama)
                    ->setCellValue('B'.$ix, $r->nama)
                    ->setCellValue('C'.$ix, !empty($r->bln1) ? $r->bln1 : 0)
                    ->setCellValue('D'.$ix, !empty($r->bln2) ? $r->bln2 : 0)
                    ->setCellValue('E'.$ix, !empty($r->bln3) ? $r->bln3 : 0)
                    ->setCellValue('F'.$ix, !empty($r->bln4) ? $r->bln4 : 0)
                    ->setCellValue('G'.$ix, !empty($r->bln5) ? $r->bln5 : 0)
                    ->setCellValue('H'.$ix, !empty($r->bln6) ? $r->bln6 : 0)
                    ->setCellValue('I'.$ix, !empty($r->bln7) ? $r->bln7 : 0)
                    ->setCellValue('J'.$ix, !empty($r->bln8) ? $r->bln8 : 0)
                    ->setCellValue('K'.$ix, !empty($r->bln9) ? $r->bln9 : 0)
                    ->setCellValue('L'.$ix, !empty($r->bln10) ? $r->bln10 : 0)
                    ->setCellValue('M'.$ix, !empty($r->bln11) ? $r->bln11 : 0)
                    ->setCellValue('N'.$ix, !empty($r->bln12) ? $r->bln12 : 0);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($stylexArray);
            // }

            if($r->level == 1){
                $gets->getStyle("A{$ix}:B{$ix}")->getFont()->setSize('11')->setBold(true);
            }

            $sheets->getActiveSheet()->getStyle('C' . $ix . ":N" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                    
            $ix++;
        }

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':b'. $length);
        $gets->getStyle('A'.$length.':N'.$length)->applyFromArray($stylexArray);
        
       

        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('C'.$length, !empty($bln1) ? $bln1 : 0)
                    ->setCellValue('D'.$length, !empty($bln2) ? $bln2 : 0)
                    ->setCellValue('E'.$length, !empty($bln3) ? $bln3 : 0)
                    ->setCellValue('F'.$length, !empty($bln4) ? $bln4 : 0)
                    ->setCellValue('G'.$length, !empty($bln5) ? $bln5 : 0)
                    ->setCellValue('H'.$length, !empty($bln6) ? $bln6 : 0)
                    ->setCellValue('I'.$length, !empty($bln7) ? $bln7 : 0)
                    ->setCellValue('J'.$length, !empty($bln8) ? $bln8 : 0)
                    ->setCellValue('K'.$length, !empty($bln9) ? $bln9 : 0)
                    ->setCellValue('L'.$length, !empty($bln10) ? $bln10 : 0)
                    ->setCellValue('M'.$length, !empty($bln11) ? $bln11 : 0)
                    ->setCellValue('N'.$length, !empty($bln12) ? $bln12 : 0);

        $gets->getStyle('C' . $length . ":N" . $length )->getNumberFormat()
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

    public function exp_mutasi_new($from_date, $to_date, $ref_rekening){
        if ($ref_rekening == "all") {
            $ref_rekening = null;
        }
        $fileName = "laporan-beban.xlsx";
        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mcoa->get_mutasi_export($tanggal_sql_from, $tanggal_sql_to, $ref_rekening);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Laporan Mutasi '. $title)

               ->setCellValue('A4', 'Tipe Bayar')
               ->setCellValue('B4', 'Tanggal')
               ->setCellValue('C4', 'Akun Kode')
               ->setCellValue('D4', 'Kode')
               ->setCellValue('E4', 'Nama Akun')
               ->setCellValue('F4', 'Keterangan')
               ->setCellValue('G4', 'Jumlah');

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
            
        $gets->getStyle('A4:G4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('A2:G2');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(25);
          $gets->getColumnDimension('B')->setWidth(20);
          $gets->getColumnDimension('C')->setWidth(25);
          $gets->getColumnDimension('D')->setWidth(15);
          $gets->getColumnDimension('E')->setWidth(45);
          $gets->getColumnDimension('F')->setWidth(60);
          $gets->getColumnDimension('G')->setWidth(30);
        //   $gets->getColumnDimension('O')->setWidth(20);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:G4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:G4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G'
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
        // for ($i=1; $i <= 4 ; $i++) { 
        //     // declaration image
        //     $isR = $ix * $is;
        //     if($is > 0){
        //         $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        //         $drawing->setName('Paid');
        //         $drawing->setDescription('Paid');
        //         $drawing->setPath(ROOTPATH . 'public/assets/images/text-excel.png');
        //         $drawing->setCoordinates('C'.$isR);
        //         $drawing->setOffsetX(85);
        //         $drawing->setRotation(-35);
        //         // $drawing->getShadow()->setVisible(false);
        //         // $drawing->getShadow()->setDirection(45);
        //         $drawing->setHeight(65);
        //         $drawing->setWorksheet($gets);
        //     }

        //     $is += $ix;
        // }
        
        
        $length = $ix;
        $length_sub = $ix;

        if(!empty($results)){
            $length += count($results);
        }

        $bckColor = "F2F2F2";
        $ig = $ix;
        $ip = $ix;
        
        $g_id = null; 
        $p_id = null; 

        $grand_total = 0;
        $grand_total_sub = 0;
        $ref_rekening_now = null;
        $ref_bank = null;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];
            if ($xx == 0) {
                $ref_rekening_now = $r->ref_rekening_id;
                $ref_bank = $r->tipe_bayar;
            }
            else if ($r->ref_rekening_id != $ref_rekening_now) {
                $sheets->setActiveSheetIndex(0)
                ->setCellValue('A'.$ix, $ref_bank);

                $sheets->setActiveSheetIndex(0)
                ->setCellValue('B'.$ix, "Total");
                
                $sheets->getActiveSheet()->mergeCells('B'. $ix .':F'. $ix);
                
                $gets->getStyle('A'.$ix)->applyFromArray($stylexArraySubFooter);
                $gets->getStyle('B'.$ix.':G'.$ix)->applyFromArray($stylexArraySubFooter2);
                
                
                
                $sheets->setActiveSheetIndex(0)
                ->setCellValue('G'.$ix, $grand_total_sub);
                
                $gets->getStyle("G" . $ix )->getNumberFormat()
                ->setFormatCode('#,##0.00');
                $grand_total_sub = 0;
                $ref_bank = $r->tipe_bayar;
                $ix++;
                $length++;
            }
            else {
                $ref_bank = $r->tipe_bayar;
            }
            $grand_total += !empty($r->jumlah) ? $r->jumlah : 0;
            $grand_total_sub += !empty($r->jumlah) ? $r->jumlah : 0;
            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                    ->setCellValue('B'.$ix, !empty($r->trans_akun_date) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->trans_akun_date)))) : "-")
                    ->setCellValue('C'.$ix, !empty($r->trans_akun_kode) ? $r->trans_akun_kode : "-")
                    ->setCellValue('D'.$ix, !empty($r->kode) ? $r->kode : 0)
                    ->setCellValue('E'.$ix, !empty($r->nama) ? $r->nama : 0)
                    ->setCellValue('F'.$ix, !empty($r->keterangan) ? $r->keterangan : 0)
                    ->setCellValue('G'.$ix, !empty($r->jumlah) ? $r->jumlah : 0);
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':G'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("G" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ref_rekening_now = $r->ref_rekening_id;
            $ix++;
            if ($xx == count($results) - 1) {
                $sheets->setActiveSheetIndex(0)
                ->setCellValue('A'.$ix, $ref_bank);

                $sheets->setActiveSheetIndex(0)
                ->setCellValue('B'.$ix, "Total");
                
                $sheets->getActiveSheet()->mergeCells('B'. $ix .':F'. $ix);
                
                $gets->getStyle('A'.$ix)->applyFromArray($stylexArraySubFooter);
                $gets->getStyle('B'.$ix.':G'.$ix)->applyFromArray($stylexArraySubFooter2);
                
                
                
                $sheets->setActiveSheetIndex(0)
                ->setCellValue('G'.$ix, $grand_total_sub);
                
                $gets->getStyle("G" . $ix )->getNumberFormat()
                ->setFormatCode('#,##0.00');
                $length++;
            }
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Grand Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':F'. $length);
        
        $gets->getStyle('A'.$length.':G'.$length)->applyFromArray($stylexArrayFooter);
        
       
        
        $sheets->setActiveSheetIndex(0)
                    ->setCellValue('G'.$length, $grand_total);

        $gets->getStyle("G" . $length )->getNumberFormat()
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

    public function exp_mutasi_all($bulan, $tahun, $ref_masuk, $text_masuk){
        $ref_rekening = "all";
        $fileName = "laporan-mutasi.xlsx";

        $date = DateTime::createFromFormat('!m', $bulan); // !m → hanya bulan
        $nama_bulan = $date->format('F');

        $results = $this->mcoa->get_mutasi_export_all($bulan, $tahun, $ref_masuk);
        $resultsSO = $this->mcoa->get_mutasi_export_so($bulan, $tahun, $ref_masuk);
        $resultsCR = $this->mcoa->get_mutasi_export_cr($bulan, $tahun, $ref_masuk);
        $resultsPB = $this->mcoa->get_mutasi_export_pb($bulan, $tahun, $ref_masuk);

        // gabung semua data
        $results_all = array_merge($results, $resultsSO, $resultsCR, $resultsPB);

        // sort berdasarkan tanggal (pastikan semua alias tanggal sama)
        usort($results_all, function ($a, $b) {
            return strtotime($a->tgl_transaksi) <=> strtotime($b->tgl_transaksi);
        });

        $getBulan = $bulan;
        $getTahun = $tahun;
        if ($bulan == 1) {
            $getBulan = 12;
            $getTahun = $tahun - 1;
        }
        else {
            $getBulan = $bulan - 1;
        }

        $saldo = $this->mcoa->get_mutasi_history($getBulan, $getTahun, $ref_masuk);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = $nama_bulan." ".$tahun;
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Laporan Mutasi '. $title)

               ->setCellValue('A4', 'Tipe Bayar')
               ->setCellValue('B4', 'Tanggal')
               ->setCellValue('C4', 'Transaksi')
               ->setCellValue('D4', 'Akun Kode')
               ->setCellValue('E4', 'Kode')
               ->setCellValue('F4', 'Nama Akun')
               ->setCellValue('G4', 'Keterangan')
               ->setCellValue('H4', 'Masuk')
               ->setCellValue('I4', 'Keluar');

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
                'alignment' => [
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
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
          $gets->getColumnDimension('C')->setWidth(20);
          $gets->getColumnDimension('D')->setWidth(25);
          $gets->getColumnDimension('E')->setWidth(15);
          $gets->getColumnDimension('F')->setWidth(45);
          $gets->getColumnDimension('G')->setWidth(60);
          $gets->getColumnDimension('H')->setWidth(30);
          $gets->getColumnDimension('I')->setWidth(30);
        //   $gets->getColumnDimension('O')->setWidth(20);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:I4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:I4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H', 'I'
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
        // for ($i=1; $i <= 4 ; $i++) { 
        //     // declaration image
        //     $isR = $ix * $is;
        //     if($is > 0){
        //         $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        //         $drawing->setName('Paid');
        //         $drawing->setDescription('Paid');
        //         $drawing->setPath(ROOTPATH . 'public/assets/images/text-excel.png');
        //         $drawing->setCoordinates('C'.$isR);
        //         $drawing->setOffsetX(85);
        //         $drawing->setRotation(-35);
        //         // $drawing->getShadow()->setVisible(false);
        //         // $drawing->getShadow()->setDirection(45);
        //         $drawing->setHeight(65);
        //         $drawing->setWorksheet($gets);
        //     }

        //     $is += $ix;
        // }
        
        
        $length = $ix;
        $length_sub = $ix;

        if(!empty($results_all)){
            $length += count($results_all);
        }

        $bckColor = "F2F2F2";
        $ig = $ix;
        $ip = $ix;
        
        $g_id = null; 
        $p_id = null; 

        $grand_total = 0;
        $grand_total_masuk = 0;
        $grand_total_sub = 0;
        $grand_total_sub_masuk = 0;
        $ref_rekening_now = null;
        $ref_bank = null;

        $sheets->setActiveSheetIndex(0)
                ->setCellValue('A1', "Tipe Bayar");
        $sheets->setActiveSheetIndex(0)
                ->setCellValue('B1', $text_masuk);

        $sheets->setActiveSheetIndex(0)
                ->setCellValue('D1', "Periode");
        $sheets->setActiveSheetIndex(0)
                ->setCellValue('E1', $nama_bulan." ".$tahun);
        $sheets->getActiveSheet()->getStyle("F")
            ->getAlignment()
            ->setWrapText(true);

        $sheets->setActiveSheetIndex(0)
                ->setCellValue('H3', "Saldo Awal");
        $sheets->setActiveSheetIndex(0)
            ->setCellValue('I3', !empty($saldo) ? $saldo->saldo : 0);
            $gets->getStyle("I3" )->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $startRow = $ix;
        for ($xx = 0; $xx < count($results_all) ; $xx++) { 
            
            $r = $results_all[$xx];
            // if ($xx == 0) {
            //     $ref_rekening_now = $r->ref_rekening_id;
            //     $ref_bank = $r->tipe_bayar;
            // }
            // else if ($r->ref_rekening_id != $ref_rekening_now) {
            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('A'.$ix, $ref_bank);

            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('B'.$ix, "Total");
                
            //     $sheets->getActiveSheet()->mergeCells('B'. $ix .':F'. $ix);
                
            //     $gets->getStyle('A'.$ix)->applyFromArray($stylexArraySubFooter);
            //     $gets->getStyle('B'.$ix.':H'.$ix)->applyFromArray($stylexArraySubFooter2);

            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('G'.$ix, $grand_total_sub_masuk);
                
            //     $gets->getStyle("G" . $ix )->getNumberFormat()
            //     ->setFormatCode('#,##0.00');
            //     $grand_total_sub_masuk = 0;

            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('H'.$ix, $grand_total_sub);
                
            //     $gets->getStyle("H" . $ix )->getNumberFormat()
            //     ->setFormatCode('#,##0.00');
            //     $grand_total_sub = 0;
            //     $ref_bank = $r->tipe_bayar;
            //     $ix++;
            //     $length++;
            // }
            // else {
            //     $ref_bank = $r->tipe_bayar;
            // }

            if ($r->type == "Beban Biaya") {
                if ($r->coa_id == $ref_masuk) {
                    $grand_total_masuk += !empty($r->jumlah) ? $r->jumlah : 0;
                    $grand_total_sub_masuk += !empty($r->jumlah) ? $r->jumlah : 0;
                    $sheets->setActiveSheetIndex(0)
                        ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                        ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                        ->setCellValue('C'.$ix, "Beban Biaya")
                        ->setCellValue('D'.$ix, !empty($r->trans_akun_kode) ? $r->trans_akun_kode : "-")
                        ->setCellValue('E'.$ix, !empty($r->kode) ? $r->kode : 0)
                        ->setCellValue('F'.$ix, !empty($r->nama) ? $r->nama : 0)
                        ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : 0)
                        ->setCellValue('H'.$ix, !empty($r->jumlah) ? $r->jumlah : 0);
                    $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                }
                else {
                    $grand_total += !empty($r->jumlah) ? $r->jumlah : 0;
                    $grand_total_sub += !empty($r->jumlah) ? $r->jumlah : 0;
                    $sheets->setActiveSheetIndex(0)
                        ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                        ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                        ->setCellValue('C'.$ix, "Beban Biaya")
                        ->setCellValue('D'.$ix, !empty($r->trans_akun_kode) ? $r->trans_akun_kode : "-")
                        ->setCellValue('E'.$ix, !empty($r->kode) ? $r->kode : 0)
                        ->setCellValue('F'.$ix, !empty($r->nama) ? $r->nama : 0)
                        ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : 0)
                        ->setCellValue('I'.$ix, !empty($r->jumlah) ? $r->jumlah : 0);
                    $sheets->getActiveSheet()->getStyle("I" . $ix )->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
            }

            else if ($r->type == "Sales Order") {
                $uang_dp = !empty($r->uang_dp) ? $r->uang_dp : 0;
                $uang_dp_2 = !empty($r->uang_dp_2) ? $r->uang_dp_2 : 0;
                $total_uang_dp = $uang_dp + $uang_dp_2;
                $grand_total_masuk += $total_uang_dp;
                $grand_total_sub_masuk += $total_uang_dp;
                $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, "Sales Order")
                    ->setCellValue('D'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : "-")
                    ->setCellValue('E'.$ix, "-")
                    ->setCellValue('F'.$ix, "-")
                    ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
                    ->setCellValue('H'.$ix, $total_uang_dp);
                $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
                ->setFormatCode('#,##0.00');
            }

            else if ($r->type == "Customer Receipt") {
                $grand_total_masuk += !empty($r->total_bayar) ? $r->total_bayar : 0;
                $grand_total_sub_masuk += !empty($r->total_bayar) ? $r->total_bayar : 0;
                $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, "Customer Receipt")
                    ->setCellValue('D'.$ix, !empty($r->kode_cr) ? $r->kode_cr : "-")
                    ->setCellValue('E'.$ix, "-")
                    ->setCellValue('F'.$ix, "-")
                    ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
                    ->setCellValue('H'.$ix, !empty($r->total_bayar) ? $r->total_bayar : 0);
                $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
                ->setFormatCode('#,##0.00');
            }

            else if ($r->type == "Pembayaran") {
                $grand_total += !empty($r->total_bayar) ? $r->total_bayar : 0;
                $grand_total_sub += !empty($r->total_bayar) ? $r->total_bayar : 0;
                $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, "Pembayaran")
                    ->setCellValue('D'.$ix, !empty($r->pay_no) ? $r->pay_no : "-")
                    ->setCellValue('E'.$ix, "-")
                    ->setCellValue('F'.$ix, "-")
                    ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : 0)
                    ->setCellValue('I'.$ix, !empty($r->total_bayar) ? $r->total_bayar : 0);
                $sheets->getActiveSheet()->getStyle("I" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
            
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);
            // }

            // $ref_rekening_now = $r->ref_rekening_id;
            $ix++;
            // if ($xx == count($results) - 1) {
            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('A'.$ix, $ref_bank);

            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('B'.$ix, "Total");
                
            //     $sheets->getActiveSheet()->mergeCells('B'. $ix .':F'. $ix);
                
            //     $gets->getStyle('A'.$ix)->applyFromArray($stylexArraySubFooter);
            //     $gets->getStyle('B'.$ix.':H'.$ix)->applyFromArray($stylexArraySubFooter2);

            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('G'.$ix, $grand_total_sub_masuk);
                
            //     $gets->getStyle("G" . $ix )->getNumberFormat()
            //     ->setFormatCode('#,##0.00');
                
            //     $sheets->setActiveSheetIndex(0)
            //     ->setCellValue('H'.$ix, $grand_total_sub);
                
            //     $gets->getStyle("H" . $ix )->getNumberFormat()
            //     ->setFormatCode('#,##0.00');
            //     $length++;
            // }
        }

        // for ($xx = 0; $xx < count($resultsSO) ; $xx++) { 
            
        //     $r = $resultsSO[$xx];
            
        //     $grand_total_masuk += !empty($r->uang_dp) ? $r->uang_dp : 0;
        //     $grand_total_sub_masuk += !empty($r->uang_dp) ? $r->uang_dp : 0;
        //     $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
        //         ->setCellValue('B'.$ix, !empty($r->tgl_dp) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_dp)))) : "-")
        //         ->setCellValue('C'.$ix, "Sales Order")
        //         ->setCellValue('D'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : "-")
        //         ->setCellValue('E'.$ix, "-")
        //         ->setCellValue('F'.$ix, "-")
        //         ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
        //         ->setCellValue('H'.$ix, !empty($r->uang_dp) ? $r->uang_dp : 0);
        //     $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
        //     ->setFormatCode('#,##0.00');

        //     $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);
        //     $ix++;
        // }

        // for ($xx = 0; $xx < count($resultsCR) ; $xx++) { 
            
        //     $r = $resultsCR[$xx];
            
        //     $grand_total_masuk += !empty($r->total_bayar) ? $r->total_bayar : 0;
        //     $grand_total_sub_masuk += !empty($r->total_bayar) ? $r->total_bayar : 0;
        //     $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
        //         ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
        //         ->setCellValue('C'.$ix, "Customer Receipt")
        //         ->setCellValue('D'.$ix, !empty($r->kode_cr) ? $r->kode_cr : "-")
        //         ->setCellValue('E'.$ix, "-")
        //         ->setCellValue('F'.$ix, "-")
        //         ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
        //         ->setCellValue('H'.$ix, !empty($r->total_bayar) ? $r->total_bayar : 0);
        //     $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
        //     ->setFormatCode('#,##0.00');

        //     $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);
        //     $ix++;
        // }

        // for ($xx = 0; $xx < count($resultsPB) ; $xx++) { 
            
        //     $r = $resultsPB[$xx];
            
        //     $grand_total += !empty($r->total_bayar) ? $r->total_bayar : 0;
        //     $grand_total_sub += !empty($r->total_bayar) ? $r->total_bayar : 0;
        //     $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
        //         ->setCellValue('B'.$ix, !empty($r->pay_date) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->pay_date)))) : "-")
        //         ->setCellValue('C'.$ix, "Pembayaran")
        //         ->setCellValue('D'.$ix, !empty($r->pay_no) ? $r->pay_no : "-")
        //         ->setCellValue('E'.$ix, "-")
        //         ->setCellValue('F'.$ix, "-")
        //         ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : 0)
        //         ->setCellValue('I'.$ix, !empty($r->total_bayar) ? $r->total_bayar : 0);
        //     $sheets->getActiveSheet()->getStyle("I" . $ix )->getNumberFormat()
        //         ->setFormatCode('#,##0.00');

        //     $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);
        //     $ix++;
        // }

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Grand Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':G'. $length);
        
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFooter);
        
    //    $sheets->setActiveSheetIndex(0)
    //                 ->setCellValue('H'.$length, $grand_total_masuk);

        $sheets->setActiveSheetIndex(0)
            ->setCellValue('H' . $length, '=SUM(H' . $startRow . ':H' . $length-1 . ')');

        $gets->getStyle("H" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');
        
        // $sheets->setActiveSheetIndex(0)
        //             ->setCellValue('I'.$length, $grand_total);

        $sheets->setActiveSheetIndex(0)
            ->setCellValue('I' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        $gets->getStyle("I" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');
        
        $length++;
        // $startRow++;

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Saldo Akhir");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':G'. $length);
        
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFooter);
        
        $sheets->getActiveSheet()->mergeCells('H'. $length .':I'. $length);
        
        $saldo = !empty($saldo->saldo) ? $saldo->saldo : 0;
        
        // $sheets->setActiveSheetIndex(0)
        //             ->setCellValue('H'.$length, $saldo + $grand_total_masuk - $grand_total);

        $sheets->setActiveSheetIndex(0)
            ->setCellValue('H' . $length, '=(I3+H' . $length-1 . '-I' . $length-1 . ')');

        $gets->getStyle("H" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');
        
        $isiSaldo = [
            'coa_id' => $ref_masuk,
            'month' => $bulan,
            'year' => $tahun,
            'saldo' => $saldo + $grand_total_masuk - $grand_total
        ];
        $saldo_new = $this->mcoa->get_mutasi_history($bulan, $tahun, $ref_masuk);
        if (!empty($saldo_new)) {
            $saldo_id = $this->mcoa->updateRecord($this->mcoa->table2, $isiSaldo, "id", $saldo_new->id);
        }
        else {
            $saldo_id = $this->mcoa->insertRecordGetid($this->mcoa->table2, $isiSaldo);
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

    public function formatValueMoney($number){
        $val = number_format($number,2,".",",");
        $setVal = 'Rp '.$val;
      
        return $setVal;
      }

    public function get_node_org()
    {
        $out = [];
        // $id = decrypt($id);
        $results = $this->mcoa->getData(null, 0, 999999, null, null);
        
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
            $out = build_tree($output, 'parent_id', 'id', null);
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


}
