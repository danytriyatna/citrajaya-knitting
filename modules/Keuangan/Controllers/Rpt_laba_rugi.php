<?php

namespace Modules\Keuangan\Controllers;

// user library spreadsheet for excel
use App\Controllers\BaseController;
use App\Models\FileModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;
use Modules\Keuangan\Models\Mcoa;
use Modules\Keuangan\Models\Mlaba_rugi;
use Modules\Keuangan\Models\Mtrans_akun_det;
use Modules\Keuangan\Models\Mtrans_akun;
use Modules\Referensi\Models\RekeningModel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Xlsx_r;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Rpt_laba_rugi extends BaseController
{   
    private $url_v = "Modules\Keuangan\Views";
    private $url_in = "/keuangan/laporan_laba";

    protected $mcoa;
    protected $mtrans_akun;
    protected $mtrans_det;
    protected $mrekening;
    protected $mlaba;

	function __construct()
    {
        $this->MOD_ALIAS = "MOD_LAPORAN_LABA";
        $this->mcoa = new Mcoa();
        $this->mtrans_akun = new Mtrans_akun();
        $this->mtrans_det = new Mtrans_akun_det();
        $this->mrekening = new RekeningModel();
        $this->mlaba = new Mlaba_rugi();
    }

	public function index()
	{
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $dt_rek = $this->mlaba->getTahun_invoice();
        // dd($dt_rek);
        // $list_tahun[''] = 'Pilih Tahun';
        foreach ($dt_rek as $r) {
            $list_tahun[$r->tahun] = $r->tahun;
        }
        // dd($list_tahun);
        $this->data['slc_tahun'] = array(
            'name' => 'slc_tahun',
            'id' => 'slc_tahun',
            'options' => $list_tahun,
            'class' => 'form-control'
        );
        
        $tahun = !empty($this->request->getGet("tahun")) ? $this->request->getGet("tahun") : date('Y');
        $bulan = !empty($this->request->getGet("bulan")) ? $this->request->getGet("bulan") : date('m');

        $this->data['tahun'] = $tahun;
        $this->data['bulan'] = $bulan;
        // dd($this->data['oke']);
        $this->data['list_coa'] = $this->mcoa->getData(null, 0, 9999, null, null, null, null, '');
        $this->data['mlaba'] = $this->mlaba;
		$this->data['titlehead'] = "Laporan Laba Rugi";
		return view($this->url_v.'\vakun_rep_laba', $this->data);
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
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );
        // 
        foreach ($results as $row) {
            $coa_nama =  $row->kode ." - " .$row->nama;
            if($row->level == 1){
                $coa_nama = "<b>". $row->kode ." - " .$row->nama. "</b>";
            }
            array_push($build_array['data'], array(
               'coa_kode' => $row->kode,
               'id' => $row->id,
               'parent_id' => $row->parent_id,
               'coa_nama' => $coa_nama,
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

    public function exp_laba($bulan, $tahun){
        $ref_rekening = "all";
        

        $date = DateTime::createFromFormat('!m', $bulan); // !m → hanya bulan
        $nama_bulan = $date->format('F');

        $resultsSO = $this->mcoa->get_mutasi_export_so($bulan, $tahun);
        $resultsCR = $this->mcoa->get_mutasi_export_cr($bulan, $tahun);
        $resultsPB = $this->mcoa->get_mutasi_export_pb($bulan, $tahun);
        $resultsBiaya = $this->mcoa->get_mutasi_export_biaya($bulan, $tahun);

        // gabung semua data
        $results_all = array_merge($resultsSO, $resultsCR);

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

        $fileName = "Laporan laba Rugi Detail $nama_bulan.xlsx";

        $saldo = $this->mcoa->get_mutasi_history($getBulan, $getTahun);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = $nama_bulan." ".$tahun;
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
        $sheets->getActiveSheet()->freezePane('E5');
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Laporan Detail Laba Rugi '. $title)

               ->setCellValue('A4', 'Tipe Bayar')
               ->setCellValue('B4', 'Tanggal')
               ->setCellValue('C4', 'Transaksi')
               ->setCellValue('D4', 'Akun Kode')
               ->setCellValue('E4', 'Kode')
               ->setCellValue('F4', 'Nama Akun')
               ->setCellValue('G4', 'Keterangan')
               ->setCellValue('H4', 'Masuk')
               ->setCellValue('I4', 'Keluar')
               ->setCellValue('J4', 'Saldo');

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

            $stylexArrayFootertext = [
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
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // teks rata kanan
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // opsional biar rapi di tengah secara vertikal
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'C5D9F1', // 💡 warna kuning muda, format argb = AARRGGBB
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
            
        $gets->getStyle('A4:J4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:J3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:J2');
        $sheets->getActiveSheet()->mergeCells('A2:J2');
        // $sheets->getActiveSheet()->mergeCells('A4:J4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(25);
          $gets->getColumnDimension('B')->setWidth(18);
          $gets->getColumnDimension('C')->setWidth(20);
          $gets->getColumnDimension('D')->setWidth(20);
          $gets->getColumnDimension('E')->setWidth(10);
          $gets->getColumnDimension('F')->setWidth(40);
          $gets->getColumnDimension('G')->setWidth(60);
          $gets->getColumnDimension('H')->setWidth(25);
          $gets->getColumnDimension('I')->setWidth(25);
          $gets->getColumnDimension('J')->setWidth(25);
        //   $gets->getColumnDimension('O')->setWidth(20);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:J4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:J4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H', 'I', 'J'
        );

        for ($i=0; $i < 10 ; $i++) { 

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

        // $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('A1', "Tipe Bayar");
        // $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('B1', $text_masuk);

        // $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('D1', "Periode");
        // $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('E1', $nama_bulan." ".$tahun);
        // $sheets->getActiveSheet()->getStyle("F")
        //     ->getAlignment()
        //     ->setWrapText(true);

        // $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('H3', "Saldo Awal");
        // $sheets->setActiveSheetIndex(0)
        //     ->setCellValue('I3', !empty($saldo) ? $saldo->saldo : 0);
        //     $gets->getStyle("I3" )->getNumberFormat()
        //     ->setFormatCode('#,##0.00');

        $startRow = $ix;
        for ($xx = 0; $xx < count($results_all) ; $xx++) { 
            
            $r = $results_all[$xx];
            if ($r->type == "Sales Order") {
                $uang_dp = 0;
                $uang_dp_2 = 0;
                
                $tgl_transaksi = $r->tgl_transaksi;
                if ($tgl_transaksi) {
                    $date = new DateTime($tgl_transaksi);
                    
                    // Cek apakah bulan dan tahun cocok
                    if ($date->format('n') == $bulan && $date->format('Y') == $tahun) {
                        $uang_dp = !empty($r->uang_dp) ? $r->uang_dp : 0;
                        $sheets->setActiveSheetIndex(0)
                            ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                            ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                            ->setCellValue('C'.$ix, "Sales Order")
                            ->setCellValue('D'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : "-")
                            ->setCellValue('E'.$ix, "-")
                            ->setCellValue('F'.$ix, "-")
                            ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
                            ->setCellValue('H'.$ix, $uang_dp);
                        $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                        $gets->getStyle('A'.$ix.':I'.$ix)->applyFromArray($stylexArray);
                    } 
                }
                

                if (!empty($r->tgl_transaksi_2)) {
                    $tgl_transaksi_2 = $r->tgl_transaksi_2;
                    if ($tgl_transaksi_2) {
                        $date = new DateTime($tgl_transaksi_2);
                        
                        // Cek apakah bulan dan tahun cocok
                        if ($date->format('n') == $bulan && $date->format('Y') == $tahun) {
                            if ($tgl_transaksi) {
                                $date_1 = new DateTime($tgl_transaksi);
                                if ($date_1->format('n') == $bulan && $date_1->format('Y') == $tahun) {
                                    $length++;
                                    $ix++;
                                }
                            }
                            $uang_dp_2 = !empty($r->uang_dp_2) ? $r->uang_dp_2 : 0;
                            $sheets->setActiveSheetIndex(0)
                                ->setCellValue('A'.$ix, !empty($r->tipe_bayar_2) ? $r->tipe_bayar_2 : "-")
                                ->setCellValue('B'.$ix, !empty($r->tgl_transaksi_2) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi_2)))) : "-")
                                ->setCellValue('C'.$ix, "Sales Order")
                                ->setCellValue('D'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : "-")
                                ->setCellValue('E'.$ix, "-")
                                ->setCellValue('F'.$ix, "-")
                                ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
                                ->setCellValue('H'.$ix, $uang_dp_2);
                            $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                        } 
                    }
                }

                $total_uang_dp = $uang_dp + $uang_dp_2;
                $grand_total_masuk += $total_uang_dp;
                $grand_total_sub_masuk += $total_uang_dp;
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

            $gets->getStyle('A'.$ix.':J'.$ix)->applyFromArray($stylexArray);

            $ix++;
        }

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total Penjualan");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);
        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);
        
        //    $sheets->setActiveSheetIndex(0)
        //                 ->setCellValue('H'.$length, $grand_total_masuk);
        $rowPenjualan = $length;
        $sheets->setActiveSheetIndex(0)
            ->setCellValue('J' . $length, '=SUM(H' . $startRow . ':H' . $length-1 . ')');

        $gets->getStyle("J" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');
        
        // $sheets->setActiveSheetIndex(0)
        //             ->setCellValue('I'.$length, $grand_total);

        // $sheets->setActiveSheetIndex(0)
        //     ->setCellValue('I' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        // $gets->getStyle("I" . $length )->getNumberFormat()
        //        ->setFormatCode('#,##0.00');
        $ix = $length + 1;
        $startRow = $ix;
        for ($xx=0; $xx < count($resultsPB); $xx++) {
            $r = $resultsPB[$xx]; 
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
            
            $gets->getStyle('A'.$ix.':J'.$ix)->applyFromArray($stylexArray);

            $ix++;
        }
        $length = $ix;

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total Beban Pembelian");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);

        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);

        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $rowPembelian = $length;
        $sheets->setActiveSheetIndex(0)
            ->setCellValue('J' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        $gets->getStyle("J" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');

        $length++;

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Laba Kotor");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);

        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);

        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $rowLabaKotor = $length;
        $sheets->setActiveSheetIndex(0)
            ->setCellValue('J' . $length, '=J' . $rowPenjualan . '- J' . $rowPembelian);

        $gets->getStyle("J" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');

        $ix = $length + 1;
        $startRow = $ix;
        for ($xx=0; $xx < count($resultsBiaya); $xx++) {
            $r = $resultsBiaya[$xx]; 
            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                    ->setCellValue('B'.$ix, !empty($r->trans_akun_date) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->trans_akun_date)))) : "-")
                    ->setCellValue('C'.$ix, "Beban biaya")
                    ->setCellValue('D'.$ix, !empty($r->trans_akun_kode) ? $r->trans_akun_kode : "-")
                    ->setCellValue('E'.$ix, !empty($r->kode) ? $r->kode : "-")
                    ->setCellValue('F'.$ix, !empty($r->nama) ? $r->nama : "-")
                    ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : 0)
                    ->setCellValue('I'.$ix, !empty($r->jumlah) ? $r->jumlah : 0);
                $sheets->getActiveSheet()->getStyle("I" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            
            $gets->getStyle('A'.$ix.':J'.$ix)->applyFromArray($stylexArray);

            $ix++;
        }
        $length = $ix;

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total Beban Operasional");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);

        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);

        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $rowBiaya = $length;
        $sheets->setActiveSheetIndex(0)
            ->setCellValue('J' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        $gets->getStyle("J" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');

        $length++;

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Laba Bersih");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);

        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);

        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $sheets->setActiveSheetIndex(0)
            ->setCellValue('J' . $length, '=J' . $rowLabaKotor . '- J' . $rowBiaya);

        $gets->getStyle("J" . $length )->getNumberFormat()
               ->setFormatCode('#,##0.00');

        // $startRow++;

        // $sheets->setActiveSheetIndex(0)
        //        ->setCellValue('A'.$length, "Saldo Akhir");

        // $sheets->getActiveSheet()->mergeCells('A'. $length .':G'. $length);
        
        // $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFooter);
        
        // $sheets->getActiveSheet()->mergeCells('H'. $length .':I'. $length);
        
        // $saldo = !empty($saldo->saldo) ? $saldo->saldo : 0;
        
        // // $sheets->setActiveSheetIndex(0)
        // //             ->setCellValue('H'.$length, $saldo + $grand_total_masuk - $grand_total);

        // $sheets->setActiveSheetIndex(0)
        //     ->setCellValue('H' . $length, '=(I3+H' . $length-1 . '-I' . $length-1 . ')');

        // $gets->getStyle("H" . $length )->getNumberFormat()
        //        ->setFormatCode('#,##0.00');
        
        // $isiSaldo = [
        //     'coa_id' => $ref_masuk,
        //     'month' => $bulan,
        //     'year' => $tahun,
        //     'saldo' => $saldo + $grand_total_masuk - $grand_total
        // ];
        // $saldo_new = $this->mcoa->get_mutasi_history($bulan, $tahun, $ref_masuk);
        // if (!empty($saldo_new)) {
        //     $saldo_id = $this->mcoa->updateRecord($this->mcoa->table2, $isiSaldo, "id", $saldo_new->id);
        // }
        // else {
        //     $saldo_id = $this->mcoa->insertRecordGetid($this->mcoa->table2, $isiSaldo);
        // }

        
        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output'); // download file 
        exit;
    }

    public function exp_laba_rugi($bulan, $tahun){
        $fileName = "laporan-laba-periode-{$bulan}-{$tahun}.xlsx";

        $results = $this->mcoa->getData(null, 0, 9999);
        // dd($results);
    
        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Laporan Laba Rugi  Periode ' . bulan($bulan) . ' '. $tahun)
            //    ->setCellValue('A4', 'Nomor Akun')
               ->setCellValue('A4', 'Nama Akun')
               ->setCellValue('B4', 'Jumlah');

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
            
        $gets->getStyle('A4:C4')->applyFromArray($styleArray_header);
        // $gets->getStyle('B4:C4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('A2:C2');
        $sheets->getActiveSheet()->mergeCells('B4:C4');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(60);
          $gets->getColumnDimension('B')->setWidth(20);
          $gets->getColumnDimension('C')->setWidth(20);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:C4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:C4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'
        );

        for ($i=0; $i < 3 ; $i++) { 

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

        $pdpt_sw = $this->mlaba->getDataPendapatan(1, $tahun, $bulan);
        $pdpt_ju = 0;//$this->mlaba->getDataPendapatan(2, $tahun, $bulan);
        $pdpt_ttl = $pdpt_sw + $pdpt_ju;

        $pngl_sw = $this->mlaba->getDataPengeluaran(1, 2, $tahun, $bulan);
        $pngl_ju = 0;//$this->mlaba->getDataPengeluaran(1, 1, $tahun, $bulan);
        $pngl_spt = 0;//$this->mlaba->getDataPengeluaran(2, 2, $tahun, $bulan);

        $spas = "    ";
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$ix, "4000 - Sales")
               ->setCellValue('B'.$ix, "");
        $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$ix, $spas . " 4001 - Pendapatan")
               ->setCellValue('B'.$ix, ($pdpt_sw));
        $ix = $ix + 1;
        // $sheets->setActiveSheetIndex(0)
        //        ->setCellValue('A'.$ix, $spas ."    4011 - Sewa")
        //        ->setCellValue('B'.$ix, ($pdpt_sw));
        // $ix = $ix + 1;
        // $sheets->setActiveSheetIndex(0)
        //        ->setCellValue('A'.$ix, $spas ."    4012 - Penjualan")
        //        ->setCellValue('B'.$ix, ($pdpt_ju));

        // $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
                ->setCellValue('A'.$ix, $spas ."Total Pendapatan")
                ->setCellValue('C'.$ix, $pdpt_ttl);

        $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
                ->setCellValue('A'.$ix, $spas . " 4002 - Pengeluaran")
                ->setCellValue('B'.$ix,  $pngl_sw);
        $ix = $ix + 1;
        // // $sheets->setActiveSheetIndex(0)
        // //         ->setCellValue('A'.$ix, $spas ."    4021 - Sewa")
        // //         ->setCellValue('B'.$ix, $pngl_sw);
        // // $ix = $ix + 1;
        // // $sheets->setActiveSheetIndex(0)
        // //         ->setCellValue('A'.$ix, $spas ."    4022 - Pembelian")
        // //         ->setCellValue('B'.$ix,  $pngl_ju);

        // // $ix = $ix + 1;
        // $sheets->setActiveSheetIndex(0)
        //         ->setCellValue('A'.$ix, $spas ."    4023 - Pembelian Sparepart")
        //         ->setCellValue('B'.$ix, $pngl_spt);
        
        //         $ix = $ix + 1;
        $pngl_ttl = $pngl_sw + $pngl_ju + $pngl_spt;


        for ($rw=5; $rw < $ix ; $rw++) { 
            $sheets->getActiveSheet()->getStyle('B' . $rw . ":C" . $rw )->getNumberFormat()
                   ->setFormatCode('#,##0.00');

            $gets->getStyle('A'.$rw.':C'.$rw)->applyFromArray($stylexArray);
        }

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$ix, "Total Pengeluaran")
               ->setCellValue('C'.$ix, $pngl_ttl); 
       
        $gets->getStyle('A'.$ix.':C'.$ix)->applyFromArray($stylexArray);

        $gets->getStyle('B' . $ix . ":C" . $ix )->getNumberFormat()
               ->setFormatCode('#,##0.00');
        $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$ix, "Total Sale")
               ->setCellValue('C'.$ix, $pdpt_ttl - $pngl_ttl); 
       
        $gets->getStyle('A'.$ix.':C'.$ix)->applyFromArray($stylexArray);

        $gets->getStyle('B' . $ix . ":C" . $ix )->getNumberFormat()
               ->setFormatCode('#,##0.00');

        $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$ix, "5000 - Fix Cost")
               ->setCellValue('B'.$ix, "");
       
            //    dd(count($results) );
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            $r = $results[$xx];

            $spasi = "    ";
            if( $r->level == 2){
            $spasi = $spasi . $spasi;
            } else if( $r->level == 3){
            $spasi = $spasi . $spasi . $spasi;
            } 

            $coa_ttl = $this->mlaba->getDataTransaksi($r->coa_id, $tahun, $bulan);
            if($coa_ttl > 0){
                $pngl_ttl +=  $coa_ttl;
                $sheets->setActiveSheetIndex(0)
                        ->setCellValue('A'.$ix,  $spasi.$r->kode." - ".$r->nama)
                        ->setCellValue('B'.$ix,  $coa_ttl);
                        // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

                // if($length > 0 && $ix === $length - 1){
                //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
                // }else{
                    $gets->getStyle('A'.$ix.':C'.$ix)->applyFromArray($stylexArray);
                // }

                $sheets->getActiveSheet()->getStyle('B' . $ix . ":C" . $ix )->getNumberFormat()
                        ->setFormatCode('#,##0.00');

                        
                $ix++;
            }
        }

        // $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
                ->setCellValue('A'.$ix, "Total Fix Cost")
                ->setCellValue('C'.$ix, $pngl_ttl);
        $gets->getStyle('A'.$ix.':C'.$ix)->applyFromArray($stylexArray);

        $gets->getStyle('B' . $ix . ":C" . $ix )->getNumberFormat()
                ->setFormatCode('#,##0.00'); 

        $ix = $ix + 1;
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$ix, "Laba Bersih")
               ->setCellValue('C'.$ix, $pdpt_ttl - $pngl_ttl); 
       
        $gets->getStyle('A'.$ix.':C'.$ix)->applyFromArray($stylexArray);

        $gets->getStyle('B' . $ix . ":C" . $ix )->getNumberFormat()
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
