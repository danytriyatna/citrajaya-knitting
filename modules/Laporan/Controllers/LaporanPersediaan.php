<?php

namespace Modules\Laporan\Controllers;

use DateTime;
use App\Models\FileModel;
use App\Controllers\BaseController;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\SatuanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Laporan\Models\LaporanPersediaanModel;
use Modules\Transaction\Models\IncomingGoodsModel;

class LaporanPersediaan extends BaseController
{
    protected $mBarang;
    protected $mJenisBarang;
    protected $mSatuan;
    protected $mBarangMasuk;
    protected $mGudang;
    protected $mLaporan;

    protected $views = '\Modules\Laporan\Views';
    protected $urlv  = 'laporan/persediaan';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_LAPORAN_PERSEDIAAN";
        $this->mBarang = new BarangModel();
        $this->mJenisBarang = new JenisBarangModel();
        $this->mSatuan = new SatuanModel();
        $this->mBarangMasuk = new IncomingGoodsModel();
        $this->mGudang = new GudangModel();
        $this->mLaporan = new LaporanPersediaanModel();
        $this->files  = new FileModel();
    }

    public function index()
    {

        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Laporan Persediaan ";
        $sortGudang = [
            [
                'field' => 'nama_gudang',
                'dir' => 'ASC'
            ]
        ];
        $sortJenisBarang = [
            [
                'field' => 'nama_jenis_barang',
                'dir' => 'ASC'
            ]
        ];
        $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
        $resJenisBarang = $this->mJenisBarang->getData(null, 0, 99999, $sortJenisBarang);
        $resTahun = $this->mLaporan->getTahun();
        $resBulan = $this->mLaporan->getBulan();

        $this->data['gudang']    = $resDataGudang;
        $this->data['jenisBarang']    = $resJenisBarang;
        $this->data['tahun']    = $resTahun;
        $this->data['bulan']    = $resBulan;
        return view($this->views . '\laporan_persediaan', $this->data);
    }


    public function getDataLaporanPersediaan()
    {

        $build_array = [];
        $build_array["code"] = 200;
        $build_array["status"] = false;

        $idJenisBarang = $this->request->getGet('filter_jenis_id');

        $filter_gudang = $this->request->getGet('filter_gudang_id');

        $tahun = $this->request->getGet('tahun');
        $bulan = $this->request->getGet('bulan');

        // $resData = $this->mLaporan->getLaporanPersediaan($idJenisBarang, $filter_gudang, $tahun, $bulan);
        $resData = $this->mLaporan->getDataGudang($idJenisBarang, $filter_gudang, $tahun, $bulan);

        $build_array["message"] = "Data ditemukan";
        $build_array["data"] =  !empty($resData) ? $resData : [];
        $build_array["status"] = true;

        return $this->response->setJSON($build_array);
    }

    public function getUpdateDataLaporanPersediaan()
    {

        $build_array = [];
        $build_array["code"] = 200;
        $build_array["status"] = false;

        $idJenisBarang = $this->request->getGet('filter_jenis_id');

        $filter_gudang = $this->request->getGet('filter_gudang_id');

        $tahun = $this->request->getGet('tahun');
        $bulan = $this->request->getGet('bulan');

        $resUpdate = $this->mLaporan->updateDataHistory($idJenisBarang, $filter_gudang, $tahun, $bulan);

        // $resData = $this->mLaporan->getLaporanPersediaan($idJenisBarang, $filter_gudang, $tahun, $bulan);
        $resData = $this->mLaporan->getDataGudang($idJenisBarang, $filter_gudang, $tahun, $bulan);

        $build_array["message"] = "Data ditemukan";
        $build_array["data"] =  !empty($resData) ? $resData : [];
        $build_array["status"] = true;

        return $this->response->setJSON($build_array);
    }

    public function print_excel_lists(){

        $fileName = "Laporan Persediaan.xlsx";

        $idJenisBarang = $this->request->getGet('filter_jenis_id');

        $filter_gudang = $this->request->getGet('filter_gudang_id');

        $tahun = $this->request->getGet('tahun');
        $bulan = $this->request->getGet('bulan');

        $id = $this->request->getGet('data_id');

        $date = DateTime::createFromFormat('!m', $bulan); // !m → hanya bulan
        $nama_bulan = $date->format('F');

        $results = $this->mLaporan->getDataGudang($idJenisBarang, $filter_gudang, $tahun, $bulan);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = $nama_bulan." ".$tahun;
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Laporan Persediaan '. $title)

               ->setCellValue('A4', 'LOT')
               ->setCellValue('B4', 'JENIS BARANG')
               ->setCellValue('C4', 'BARANG')
               ->setCellValue('D4', 'QTY AWAL')
               ->setCellValue('E4', 'QTY MASUK')
               ->setCellValue('F4', 'QTY KELUAR')
               ->setCellValue('G4', 'QTY AKHIR')
               ->setCellValue('H4', 'NILAI')
               ->setCellValue('I4', 'TANGGAL');

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

            $stylexArrayLOT = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '1f1f1f'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 
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
        $gets->getColumnDimension('A')->setWidth(15);
        $gets->getColumnDimension('B')->setWidth(30);
        $gets->getColumnDimension('C')->setWidth(50);
        $gets->getColumnDimension('D')->setWidth(20);
        $gets->getColumnDimension('E')->setWidth(20);
        $gets->getColumnDimension('F')->setWidth(20);
        $gets->getColumnDimension('G')->setWidth(20);
        $gets->getColumnDimension('H')->setWidth(30);
        $gets->getColumnDimension('I')->setWidth(20);

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
        
        
        $length = $ix;

        if(!empty($results)){
            $length += count($results);
        }

        $startRow = $ix;
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];

            $nilai = !empty($r->price) ? $r->price : 0;

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->lot_no) ? $r->lot_no : "-")
                    ->setCellValue('B'.$ix, !empty($r->nama_jenis_barang) ? $r->nama_jenis_barang : '-')
                    ->setCellValue('C'.$ix, !empty($r->barang) ? $r->barang : '-')
                    ->setCellValue('D'.$ix, !empty($r->saldo_awal) ? $r->saldo_awal : 0)
                    ->setCellValue('E'.$ix, !empty($r->masuk) ? $r->masuk : 0)
                    ->setCellValue('F'.$ix, !empty($r->keluar) ? $r->keluar : 0)
                    ->setCellValue('G'.$ix, !empty($r->saldo_akhir) ? $r->saldo_akhir : 0)
                    ->setCellValue('H'.$ix, $nilai)
                    ->setCellValue('I'.$ix, !empty($r->tanggal) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-");
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':C'.$ix)->applyFromArray($stylexArray);
                $gets->getStyle('D'.$ix.':I'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("H" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

          $ix++;
        }
        // $sheets->setActiveSheetIndex(0)
        //        ->setCellValue('A'.$length, "Total");

        // $sheets->getActiveSheet()->mergeCells('A'. $length .':C'. $length);
        
        // $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFooter);
        
        // $sheets->setActiveSheetIndex(0)
        //               ->setCellValue('H' . $length, '=SUM(H' . $startRow . ':H' . $length-1 . ')');

        // $sheets->setActiveSheetIndex(0)
        //               ->setCellValue('D' . $length, '=SUM(D' . $startRow . ':D' . $length-1 . ')');

        // $sheets->setActiveSheetIndex(0)
        //             ->setCellValue('E' . $length, '=SUM(E' . $startRow . ':E' . $length-1 . ')');

        // $sheets->setActiveSheetIndex(0)
        //             ->setCellValue('F' . $length, '=SUM(F' . $startRow . ':F' . $length-1 . ')');

        // $sheets->setActiveSheetIndex(0)
        //               ->setCellValue('G' . $length, '=SUM(G' . $startRow . ':G' . $length-1 . ')');

        // $gets->getStyle('H'. $length)->getNumberFormat()
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
