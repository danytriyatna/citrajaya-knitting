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
        $params['search'] = $this->request->getGet('search');

        $tahun = $this->request->getGet('tahun');
        $bulan = $this->request->getGet('bulan');

        // $resData = $this->mLaporan->getLaporanPersediaan($idJenisBarang, $filter_gudang, $tahun, $bulan);
        $resData = $this->mLaporan->getDataGudang($idJenisBarang, $filter_gudang, $tahun, $bulan, $params);

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

        $results = $this->mLaporan->getDataGudangPrint($idJenisBarang, $filter_gudang, $tahun, $bulan);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->setActiveSheetIndex(0);
        $title = $nama_bulan." ".$tahun;
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $gets
               ->setCellValue('A2', 'Laporan Persediaan '. $title)

               ->setCellValue('A4', 'JENIS BARANG')
               ->setCellValue('B4', 'KODE BARANG')
               ->setCellValue('C4', 'KODE WARNA')
               ->setCellValue('D4', 'NAMA BARANG')
               ->setCellValue('E4', 'UNIT')
               ->setCellValue('F4', 'PACK')
               ->setCellValue('G4', 'LOT')
               ->setCellValue('h4', 'QTY AWAL')
               ->setCellValue('I4', 'QTY MASUK')
               ->setCellValue('J4', 'QTY KELUAR')
               ->setCellValue('K4', 'QTY AKHIR')
               ->setCellValue('L4', 'NILAI')
               ->setCellValue('M4', 'TANGGAL');

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
            
        $gets->freezePane('E5');
        $gets->getStyle('A4:M4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $gets->mergeCells('A2:I2');
        $gets->mergeCells('A2:M2');
        // $gets->mergeCells('A4:I4');
        // $gets->mergeCells('A5:C5');

        // set Center title
        $gets->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
        $gets->getColumnDimension('A')->setWidth(20);
        $gets->getColumnDimension('B')->setWidth(17);
        $gets->getColumnDimension('C')->setWidth(25);
        $gets->getColumnDimension('D')->setWidth(35);
        $gets->getColumnDimension('E')->setWidth(15);
        $gets->getColumnDimension('F')->setWidth(10);
        $gets->getColumnDimension('G')->setWidth(12);
        $gets->getColumnDimension('H')->setWidth(15);
        $gets->getColumnDimension('I')->setWidth(15);
        $gets->getColumnDimension('J')->setWidth(15);
        $gets->getColumnDimension('K')->setWidth(15);
        $gets->getColumnDimension('L')->setWidth(20);
        $gets->getColumnDimension('M')->setWidth(17);

        $gets->getStyle('A4:M4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets->getStyle('A4:M4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Laporan Persediaan');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H', 'I', 'J', 'K', 'L', 'M'
        );

        for ($i=0; $i < 13 ; $i++) { 

                $gets->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('C5D9F1');
                $gets->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getEndColor()->setARGB('C5D9F1');
            
            // $gets->mergeCells($indexs[$i].'2');

            $gets->getStyle($indexs[$i].'4')
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

            $nilai = !empty($r->price) ? $r->price * $r->saldo_akhir : 0;

            $gets
                    ->setCellValue('A'.$ix, !empty($r->nama_jenis_barang) ? $r->nama_jenis_barang : '-')
                    ->setCellValue('B'.$ix, !empty($r->kode_barang) ? $r->kode_barang : '-')
                    ->setCellValue('C'.$ix, !empty($r->kode_warna) ? $r->kode_warna : '-')
                    ->setCellValue('D'.$ix, !empty($r->barang) ? $r->barang : '-')
                    ->setCellValue('E'.$ix, !empty($r->nama_unit) ? $r->nama_unit : '-')
                    ->setCellValue('F'.$ix, !empty($r->pack_name) ? $r->pack_name : '-')
                    ->setCellValue('G'.$ix, !empty($r->lot_no) ? $r->lot_no : '-')
                    ->setCellValue('H'.$ix, !empty($r->saldo_awal) ? $r->saldo_awal : 0)
                    ->setCellValue('I'.$ix, !empty($r->masuk) ? $r->masuk : 0)
                    ->setCellValue('J'.$ix, !empty($r->keluar) ? $r->keluar : 0)
                    ->setCellValue('K'.$ix, !empty($r->saldo_akhir) ? $r->saldo_akhir : 0)
                    ->setCellValue('L'.$ix, $nilai)
                    ->setCellValue('M'.$ix, !empty($r->tanggal) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-");
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':M'.$ix)->applyFromArray($stylexArray);
            // }

            $gets->getStyle("L" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

          $ix++;
        }
        $gets->setCellValue('A'.$length, "TOTAL");

        $gets->mergeCells('A'. $length .':E'. $length);
        
        $gets->getStyle('A'.$length.':M'.$length)->applyFromArray($stylexArrayFooter);
        
        $gets
                      ->setCellValue('F' . $length, '=COUNT(F' . $startRow . ':F' . $length-1 . ')');

        $gets
                      ->setCellValue('H' . $length, '=SUM(H' . $startRow . ':H' . $length-1 . ')');

        $gets
                    ->setCellValue('I' . $length, '=SUM(I' . $startRow . ':I' . $length-1 . ')');

        $gets
                    ->setCellValue('J' . $length, '=SUM(J' . $startRow . ':J' . $length-1 . ')');

        $gets
                      ->setCellValue('K' . $length, '=SUM(K' . $startRow . ':K' . $length-1 . ')');

        $gets
                      ->setCellValue('L' . $length, '=SUM(L' . $startRow . ':L' . $length-1 . ')');

        $gets->getStyle('L'. $length)->getNumberFormat()
                ->setFormatCode('#,##0.00');

        
        $results = $this->mLaporan->getDataGudangPrintPack($idJenisBarang, $filter_gudang, $tahun, $bulan);

        $sheets->createSheet(); 
        $gets2 = $sheets->setActiveSheetIndex(1);
        $title = $nama_bulan." ".$tahun;
        $gets2->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);

        $packHeaders = getPackHeaders($results);
        $maxPacks    = count($packHeaders);

        $fixedCols   = 6; // A sampai F
        $startPack   = $fixedCols + 1; // kolom G dst untuk pack dinamis

        // Hitung index kolom untuk setelah pack
        $colTotalPack = $startPack + $maxPacks;
        $colQtyAkhir  = $colTotalPack + 1;
        $colNilai     = $colQtyAkhir + 1;
        $colTanggal   = $colNilai + 1;
        $lastColIndex = $colTanggal;

        $colPackStart  = $this->colToLetter($startPack);
        $colPackEnd    = $this->colToLetter($startPack + $maxPacks - 1);
        $colTotalPackL = $this->colToLetter($colTotalPack);
        $colQtyAkhirL  = $this->colToLetter($colQtyAkhir);
        $colNilaiL     = $this->colToLetter($colNilai);
        $colTanggalL   = $this->colToLetter($colTanggal);
        $lastCol       = $this->colToLetter($lastColIndex);

        // Semua kolom header sebagai array
        $allCols = [];
        for ($i = 1; $i <= $lastColIndex; $i++) {
            $allCols[] = $this->colToLetter($i);
        }

        // ── Title ──
        $gets2->setCellValue('A2', 'Laporan Persediaan per Pack ' . $title);
        $gets2->mergeCells("A2:{$lastCol}2");
        $gets2->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
        $gets2->getStyle('A2')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);

        // ── Freeze ──
        $gets2->freezePane('G6');

        // ── Merge header row 4 dan 5 untuk kolom fixed ──
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
            $gets2->mergeCells("{$col}4:{$col}5");
        }

        // Merge kolom pack jadi satu judul di row 4
        if ($maxPacks > 1) {
            $gets2->mergeCells("{$colPackStart}4:{$colPackEnd}4");
        }

        // Merge kolom setelah pack (row 4 dan 5)
        foreach ([$colTotalPackL, $colQtyAkhirL, $colNilaiL, $colTanggalL] as $col) {
            $gets2->mergeCells("{$col}4:{$col}5");
        }

        // ── Set nilai header row 4 ──
        $gets2->setCellValue('A4', 'JENIS BARANG')
            ->setCellValue('B4', 'KODE BARANG')
            ->setCellValue('C4', 'KODE WARNA')
            ->setCellValue('D4', 'NAMA BARANG')
            ->setCellValue('E4', 'UNIT')
            ->setCellValue('F4', 'LOT')
            ->setCellValue("{$colPackStart}4", 'PACK')
            ->setCellValue("{$colTotalPackL}4", 'TOTAL PACK')
            ->setCellValue("{$colQtyAkhirL}4", 'QTY AKHIR')
            ->setCellValue("{$colNilaiL}4", 'NILAI')
            ->setCellValue("{$colTanggalL}4", 'TANGGAL');

        // ── Set nama pack di row 5 ──
        foreach ($packHeaders as $i => $packName) {
            $col = $this->colToLetter($startPack + $i);
            $gets2->setCellValue("{$col}5", $packName);
        }

        // ── Styling header row 4 dan 5 ──
        foreach ($allCols as $col) {
            foreach (['4', '5'] as $row) {
                $cellRef = "{$col}{$row}";

                // Background biru
                $gets2->getStyle($cellRef)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C5D9F1');
                $gets2->getStyle($cellRef)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getEndColor()->setARGB('C5D9F1');

                // Alignment
                $gets2->getStyle($cellRef)
                    ->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);

                // Font
                $gets2->getStyle($cellRef)->getFont()
                    ->setName('Arial Narrow')->setSize('12')->setBold(true);

                // Border
                $gets2->getStyle($cellRef)->applyFromArray($styleArray_header);

                // Protection
                $gets2->getStyle($cellRef)->getProtection()
                    ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            }
        }

        // ── Lebar kolom fixed ──
        $gets2->getColumnDimension('A')->setWidth(20);
        $gets2->getColumnDimension('B')->setWidth(15);
        $gets2->getColumnDimension('C')->setWidth(25);
        $gets2->getColumnDimension('D')->setWidth(35);
        $gets2->getColumnDimension('E')->setWidth(12);
        $gets2->getColumnDimension('F')->setWidth(10);

        // Lebar kolom pack dinamis
        for ($i = 0; $i < $maxPacks; $i++) {
            $gets2->getColumnDimension($this->colToLetter($startPack + $i))->setWidth(12);
        }

        // Lebar kolom setelah pack
        $gets2->getColumnDimension($colTotalPackL)->setWidth(15);
        $gets2->getColumnDimension($colQtyAkhirL)->setWidth(15);
        $gets2->getColumnDimension($colNilaiL)->setWidth(15);
        $gets2->getColumnDimension($colTanggalL)->setWidth(15);

        // ── Title sheet ──
        $gets2->setTitle('Laporan Persediaan per Pack');

        // ── ROW ISI ──
        $rowNum = 6; // mulai dari baris 6
        $totalPackFooter = array_fill(0, $maxPacks, 0); // untuk sum per pack di footer
        $totalQtyAkhir   = 0;

        foreach ($results as $row) {
            
            $packs = parsePacks($row->pack_data);

            $gets2->setCellValue("A{$rowNum}", $row->nama_jenis_barang)
                ->setCellValue("B{$rowNum}", $row->kode_barang)
                ->setCellValue("C{$rowNum}", $row->kode_warna)
                ->setCellValue("D{$rowNum}", $row->barang)
                ->setCellValue("E{$rowNum}", $row->nama_unit)
                ->setCellValue("F{$rowNum}", $row->lot_no);

            // Kolom pack dinamis
            foreach ($packHeaders as $i => $packName) {
                $col = $this->colToLetter($startPack + $i);
                $qty = isset($packs[$packName]) ? $packs[$packName] : 0;
                $gets2->setCellValue("{$col}{$rowNum}", $qty);

                // Akumulasi untuk footer
                $totalPackFooter[$i] += $qty;

                // Style angka
                $gets2->getStyle("{$col}{$rowNum}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');
                $gets2->getStyle("{$col}{$rowNum}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // Total pack per baris (sum semua pack di baris ini)
            $totalPackRow = array_sum(array_values($packs));
            $totalQtyAkhir += $row->saldo_akhir;

            $gets2->setCellValue("{$colTotalPackL}{$rowNum}", count($packs))
                ->setCellValue("{$colQtyAkhirL}{$rowNum}", $row->saldo_akhir)
                ->setCellValue("{$colNilaiL}{$rowNum}", !empty($row->price) ? $row->price * $row->saldo_akhir : 0)
                ->setCellValue("{$colTanggalL}{$rowNum}", formatTanggalIndonesia($row->tanggal));

            // Style angka untuk total pack, qty akhir, nilai
            foreach ([$colTotalPackL, $colQtyAkhirL, $colNilaiL] as $col) {
                $gets2->getStyle("{$col}{$rowNum}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.##');
                $gets2->getStyle("{$col}{$rowNum}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // Border per baris
            $gets2->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")
                ->applyFromArray($styleArray_header);

            // Font per baris
            $gets2->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")
                ->getFont()->setName('Arial Narrow')->setSize(10);

            $rowNum++;
        }

        $footerRow = $rowNum;
        $dataStart = 6; // baris data mulai dari row 6
        $dataEnd   = $rowNum - 1; // baris data terakhir

        // Merge label TOTAL
        $gets2->mergeCells("A{$footerRow}:F{$footerRow}");
        $gets2->setCellValue("A{$footerRow}", 'TOTAL');
        $gets2->getStyle("A{$footerRow}")
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Rumus SUM per kolom pack dinamis
        foreach ($packHeaders as $i => $packName) {
            $col = $this->colToLetter($startPack + $i);
            $gets2->setCellValue("{$col}{$footerRow}", "=SUM({$col}{$dataStart}:{$col}{$dataEnd})");
            $gets2->getStyle("{$col}{$footerRow}")
                ->getNumberFormat()->setFormatCode('#,##0.##');
            $gets2->getStyle("{$col}{$footerRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        }

        // Rumus SUM TOTAL PACK dan QTY AKHIR
        $gets2->setCellValue("{$colTotalPackL}{$footerRow}", "=SUM({$colTotalPackL}{$dataStart}:{$colTotalPackL}{$dataEnd})");
        $gets2->setCellValue("{$colQtyAkhirL}{$footerRow}",  "=SUM({$colQtyAkhirL}{$dataStart}:{$colQtyAkhirL}{$dataEnd})");

        // Kosongkan nilai dan tanggal
        $gets2->setCellValue("{$colNilaiL}{$footerRow}", '');
        $gets2->setCellValue("{$colTanggalL}{$footerRow}", '');

        // Style footer
        foreach ([$colTotalPackL, $colQtyAkhirL] as $col) {
            $gets2->getStyle("{$col}{$footerRow}")
                ->getNumberFormat()->setFormatCode('#,##0.##');
            $gets2->getStyle("{$col}{$footerRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        }

        $gets2->getStyle("A{$footerRow}:{$lastCol}{$footerRow}")
            ->getFont()->setName('Arial Narrow')->setSize(10)->setBold(true);

        $gets2->getStyle("A{$footerRow}:{$lastCol}{$footerRow}")
            ->applyFromArray($styleArray_header);

        // Background footer
        foreach ($allCols as $col) {
            $gets2->getStyle("{$col}{$footerRow}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('C5D9F1');
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

    function colToLetter($n) {
        $letter = '';
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)) . $letter;
            $n = intdiv($n, 26);
        }
        return $letter;
    }
}
