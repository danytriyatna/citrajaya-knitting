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

                    public function preview()
    {
        $bulanFrom = $this->request->getPost('bulan_from');
        $tahunFrom = $this->request->getPost('tahun_from');
        $bulanTo   = $this->request->getPost('bulan_to');
        $tahunTo   = $this->request->getPost('tahun_to');

        if (empty($bulanFrom)) $bulanFrom = date('m');
        if (empty($tahunFrom)) $tahunFrom = date('Y');
        if (empty($bulanTo))   $bulanTo   = date('m');
        if (empty($tahunTo))   $tahunTo   = date('Y');

        $bulanFrom = str_pad($bulanFrom, 2, "0", STR_PAD_LEFT);
        $bulanTo   = str_pad($bulanTo, 2, "0", STR_PAD_LEFT);

        $resultsSO    = $this->mcoa->get_mutasi_export_so_laba($bulanFrom, $tahunFrom, $bulanTo, $tahunTo);
        $resultsCR    = $this->mcoa->get_mutasi_export_cr_laba($bulanFrom, $tahunFrom, $bulanTo, $tahunTo);
        $resultsBiaya = $this->mcoa->get_mutasi_export_biaya_laba($bulanFrom, $tahunFrom, $bulanTo, $tahunTo);

        // 1. Data Penjualan (SO + CR)
        $listPenjualan = [];
        $totalSO = 0;
        $totalCR = 0;

        foreach ($resultsSO as $r) {
            $nom = !empty($r->nominal) ? (float)$r->nominal : 0;
            $totalSO += $nom;
            $listPenjualan[] = [
                'tipe_bayar'      => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                'tgl_transaksi'   => !empty($r->tgl_transaksi) ? date('d-m-Y', strtotime($r->tgl_transaksi)) : "-",
                'raw_tgl'         => !empty($r->tgl_transaksi) ? $r->tgl_transaksi : "",
                'transaksi'       => "<span class='badge bg-info text-white'>Sales Order (DP)</span>",
                'kode_transaksi'  => !empty($r->kode_sales_order) ? $r->kode_sales_order : "-",
                'keterangan'      => !empty($r->keterangan) ? $r->keterangan : "-",
                'nominal'         => $nom
            ];
        }

        foreach ($resultsCR as $r) {
            $nom = !empty($r->nominal) ? (float)$r->nominal : 0;
            $totalCR += $nom;
            $listPenjualan[] = [
                'tipe_bayar'      => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                'tgl_transaksi'   => !empty($r->tgl_transaksi) ? date('d-m-Y', strtotime($r->tgl_transaksi)) : "-",
                'raw_tgl'         => !empty($r->tgl_transaksi) ? $r->tgl_transaksi : "",
                'transaksi'       => "<span class='badge bg-success'>Customer Receipt</span>",
                'kode_transaksi'  => !empty($r->kode_cr) ? $r->kode_cr : "-",
                'keterangan'      => !empty($r->keterangan) ? $r->keterangan : "-",
                'nominal'         => $nom
            ];
        }

        usort($listPenjualan, function ($a, $b) {
            return strtotime($a['raw_tgl']) <=> strtotime($b['raw_tgl']);
        });

        $totalPenjualan = $totalSO + $totalCR;

        // 2. Data Biaya Produksi Hybrid:
        // - Sebelum 2026-08-01: Pembayaran Pembelian (PO)
        // - Mulai 2026-08-01: Pemakaian Bahan Baku (BTK Approved)
        $startDate = "$tahunFrom-$bulanFrom-01";
        $lastDay = date("t", strtotime("$tahunTo-$bulanTo-01"));
        $endDate = "$tahunTo-$bulanTo-$lastDay";

        $cutoff = "2026-08-01";
        $listBiayaProduksi = [];
        $totalBiayaProduksi = 0;
        $totalPemakaianQty = 0;

        // A. Periode sebelum Agustus 2026 (PO)
        if ($startDate < $cutoff) {
            $poEnd = min($endDate, '2026-07-31');
            $poBuilder = $this->db->table("trans_po_pembayaran tpp");
            $poBuilder->select("
                'Pembayaran' AS type, 
                tpp.id_rek, 
                CONCAT('Pembayaran Pembelian', ' - ', rv.nama) AS keterangan, 
                CONCAT(rr.rekening_no, ' - ', rr.rekening_bank) AS tipe_bayar, 
                tpp.pay_date AS tgl_transaksi, 
                tpp.pay_no AS kode_transaksi, 
                tpp.total_bayar AS nominal
            ");
            $poBuilder->join("ref_rekening rr", "rr.id = tpp.id_rek", "left");
            $poBuilder->join("ref_vendor rv", "rv.id = tpp.id_vendor", "left");
            $poBuilder->where("tpp.active", 1);
            $poBuilder->where("tpp.pay_date >=", $startDate);
            $poBuilder->where("tpp.pay_date <=", $poEnd);
            $poBuilder->orderBy("tpp.pay_date", "ASC");
            $poRes = $poBuilder->get()->getResult();

            foreach ($poRes as $r) {
                $nom = (float)$r->nominal;
                $totalBiayaProduksi += $nom;
                $tglStr = !empty($r->tgl_transaksi) ? str_replace('/', '-', $r->tgl_transaksi) : "";
                $listBiayaProduksi[] = [
                    'tipe_bayar'      => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                    'tgl_transaksi'   => !empty($tglStr) ? date('d-m-Y', strtotime($tglStr)) : "-",
                    'raw_tgl'         => $tglStr,
                    'kode_transaksi'  => !empty($r->kode_transaksi) ? $r->kode_transaksi : "-",
                    'no_ref_wo'       => "-",
                    'nama_barang'     => $r->keterangan,
                    'lot_no'          => "-",
                    'qty'             => 0,
                    'harga'           => 0,
                    'nominal'         => $nom
                ];
            }
        }

        // B. Periode mulai Agustus 2026 (BTK)
        if ($endDate >= $cutoff) {
            $btkStart = max($startDate, $cutoff);
            $sqlBtk = "
                SELECT 
                    tbh.id as id_header,
                    tbh.kode_transaksi,
                    tbh.tanggal as tgl_transaksi,
                    tbh.no_ref_wo,
                    tbh.no_ref_trf,
                    COALESCE(rg.nama_gudang, 'Gudang') as tipe_bayar,
                    tbd.id_barang,
                    rb.kode_barang,
                    rb.nama_barang,
                    COALESCE(NULLIF(rb.keterangan, ''), rb.nama_barang) as ket_barang,
                    tbd.qty,
                    COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0) as price,
                    (tbd.qty * COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0)) as subtotal,
                    tbd.lot_no
                FROM trans_barang_detail tbd
                JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                LEFT JOIN ref_barang rb ON rb.id = tbd.id_barang
                LEFT JOIN ref_gudang rg ON rg.id = tbh.id_gudang
                WHERE tbh.jenis_transaksi = 2
                AND tbh.id_kategori IN (5, 16)
                AND tbh.status = 1
                AND tbh.active = 1
                AND tbh.tanggal >= '$btkStart'
                AND tbh.tanggal <= '$endDate'
                ORDER BY tbh.tanggal ASC, tbd.id ASC
            ";
            $btkRes = $this->db->query($sqlBtk)->getResult();

            foreach ($btkRes as $r) {
                $nom = (float)$r->subtotal;
                $qty = (float)$r->qty;
                $price = (float)$r->price;

                $totalBiayaProduksi += $nom;
                $totalPemakaianQty += $qty;

                $tglStr = !empty($r->tgl_transaksi) ? str_replace('/', '-', $r->tgl_transaksi) : "";
                $ket = !empty($r->nama_barang) ? $r->nama_barang : "-";
                if (!empty($r->ket_barang) && $r->ket_barang != $r->nama_barang) {
                    $ket .= " (" . $r->ket_barang . ")";
                }

                $listBiayaProduksi[] = [
                    'tipe_bayar'      => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                    'tgl_transaksi'   => !empty($tglStr) ? date('d-m-Y', strtotime($tglStr)) : "-",
                    'raw_tgl'         => $tglStr,
                    'kode_transaksi'  => !empty($r->kode_transaksi) ? $r->kode_transaksi : "-",
                    'no_ref_wo'       => !empty($r->no_ref_wo) ? $r->no_ref_wo : "-",
                    'nama_barang'     => $ket,
                    'lot_no'          => !empty($r->lot_no) ? $r->lot_no : "-",
                    'qty'             => $qty,
                    'harga'           => $price,
                    'nominal'         => $nom
                ];
            }
        }

        usort($listBiayaProduksi, function ($a, $b) {
            return strtotime($a['raw_tgl']) <=> strtotime($b['raw_tgl']);
        });

        // 3. Data Beban Operasional (Biaya)
        $listOperasional = [];
        $rekapBeban = [];
        $totalOperasional = 0;

        foreach ($resultsBiaya as $r) {
            $nom = !empty($r->jumlah) ? (float)$r->jumlah : 0;
            $totalOperasional += $nom;
            $tglStr = !empty($r->trans_akun_date) ? str_replace('/', '-', $r->trans_akun_date) : "";

            $listOperasional[] = [
                'tipe_bayar'      => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                'tgl_transaksi'   => !empty($tglStr) ? date('d-m-Y', strtotime($tglStr)) : "-",
                'raw_tgl'         => $tglStr,
                'kode_transaksi'  => !empty($r->trans_akun_kode) ? $r->trans_akun_kode : "-",
                'kode'            => !empty($r->kode) ? $r->kode : "-",
                'nama'            => !empty($r->nama) ? $r->nama : "-",
                'keterangan'      => !empty($r->keterangan) ? $r->keterangan : "-",
                'nominal'         => $nom
            ];

            $k = !empty($r->kode) ? $r->kode : "OTHER";
            if (!isset($rekapBeban[$k])) {
                $rekapBeban[$k] = [
                    'kode'  => !empty($r->kode) ? $r->kode : "-",
                    'nama'  => !empty($r->nama) ? $r->nama : "Lainnya",
                    'total' => 0,
                    'count' => 0
                ];
            }
            $rekapBeban[$k]['total'] += $nom;
            $rekapBeban[$k]['count']++;
        }

        usort($listOperasional, function ($a, $b) {
            return strtotime($a['raw_tgl']) <=> strtotime($b['raw_tgl']);
        });

        ksort($rekapBeban);
        $rekapBebanList = array_values($rekapBeban);

        $labaKotor = $totalPenjualan - $totalBiayaProduksi;
        $labaBersih = $labaKotor - $totalOperasional;

        // Dynamic Title / Labels based on period
        $labelBiayaProduksi = "Beban Pokok Produksi";
        if ($endDate < $cutoff) {
            $labelBiayaProduksi = "Beban Pembelian (PO)";
        } else if ($startDate >= $cutoff) {
            $labelBiayaProduksi = "Pemakaian Bahan Baku (BTK)";
        } else {
            $labelBiayaProduksi = "Pembelian (PO) & Pemakaian Bahan (BTK)";
        }

        return $this->response->setJSON([
            'status'               => true,
            'total_so'             => $totalSO,
            'total_cr'             => $totalCR,
            'total_penjualan'      => $totalPenjualan,
            'total_pembelian'      => $totalBiayaProduksi,
            'label_biaya_produksi' => $labelBiayaProduksi,
            'total_pemakaian_qty'  => $totalPemakaianQty,
            'laba_kotor'           => $labaKotor,
            'rekap_beban'          => $rekapBebanList,
            'total_operasional'    => $totalOperasional,
            'laba_bersih'          => $labaBersih,
            'list_penjualan'       => $listPenjualan,
            'list_pembelian'       => $listBiayaProduksi,
            'list_operasional'     => $listOperasional
        ]);
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
    public function exp_laba($bulan, $tahun, $bulanTo, $tahunTo){
        $ref_rekening = "all";

        $bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);
        $bulanTo = str_pad($bulanTo, 2, "0", STR_PAD_LEFT);

        $dateFrom = DateTime::createFromFormat('!m', $bulan);
        $nama_bulan_from = $dateFrom->format('F');

        $dateTo = DateTime::createFromFormat('!m', $bulanTo);
        $nama_bulan_to = $dateTo->format('F');

        if ($tahun == $tahunTo) {
            $periode_text = "$nama_bulan_from - $nama_bulan_to $tahun";
        } else {
            $periode_text = "$nama_bulan_from $tahun - $nama_bulan_to $tahunTo";
        }

        $resultsSO    = $this->mcoa->get_mutasi_export_so_laba($bulan, $tahun, $bulanTo, $tahunTo);
        $resultsCR    = $this->mcoa->get_mutasi_export_cr_laba($bulan, $tahun, $bulanTo, $tahunTo);
        $resultsBiaya = $this->mcoa->get_mutasi_export_biaya_laba($bulan, $tahun, $bulanTo, $tahunTo);

        // Hybrid Biaya Produksi (PO sebelum Agustus 2026, BTK mulai Agustus 2026)
        $startDate = "$tahun-$bulan-01";
        $lastDay = date("t", strtotime("$tahunTo-$bulanTo-01"));
        $endDate = "$tahunTo-$bulanTo-$lastDay";
        $cutoff = "2026-08-01";

        $resultsBiayaProduksi = [];

        // A. Periode sebelum Agustus 2026 (PO)
        if ($startDate < $cutoff) {
            $poEnd = min($endDate, '2026-07-31');
            $poBuilder = $this->db->table("trans_po_pembayaran tpp");
            $poBuilder->select("
                'Pembayaran' AS type, 
                tpp.id_rek, 
                CONCAT('Pembayaran Pembelian', ' - ', rv.nama) AS keterangan, 
                CONCAT(rr.rekening_no, ' - ', rr.rekening_bank) AS tipe_bayar, 
                tpp.pay_date AS tgl_transaksi, 
                tpp.pay_no AS kode_transaksi, 
                tpp.total_bayar AS nominal
            ");
            $poBuilder->join("ref_rekening rr", "rr.id = tpp.id_rek", "left");
            $poBuilder->join("ref_vendor rv", "rv.id = tpp.id_vendor", "left");
            $poBuilder->where("tpp.active", 1);
            $poBuilder->where("tpp.pay_date >=", $startDate);
            $poBuilder->where("tpp.pay_date <=", $poEnd);
            $poBuilder->orderBy("tpp.pay_date", "ASC");
            $poRes = $poBuilder->get()->getResult();

            foreach ($poRes as $r) {
                $resultsBiayaProduksi[] = (object)[
                    'tipe_bayar'     => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                    'tgl_transaksi'  => $r->tgl_transaksi,
                    'type'           => 'Pembayaran PO',
                    'kode_transaksi' => !empty($r->kode_transaksi) ? $r->kode_transaksi : "-",
                    'kode_akun'      => "-",
                    'nama_akun'      => "-",
                    'keterangan'     => !empty($r->keterangan) ? $r->keterangan : "-",
                    'nominal'        => (float)$r->nominal
                ];
            }
        }

        // B. Periode mulai Agustus 2026 (BTK Approved)
        if ($endDate >= $cutoff) {
            $btkStart = max($startDate, $cutoff);
            $sqlBtk = "
                SELECT 
                    tbh.id as id_header,
                    tbh.kode_transaksi,
                    tbh.tanggal as tgl_transaksi,
                    tbh.no_ref_wo,
                    COALESCE(rg.nama_gudang, 'Gudang') as tipe_bayar,
                    tbd.id_barang,
                    rb.kode_barang,
                    rb.nama_barang,
                    COALESCE(NULLIF(rb.keterangan, ''), rb.nama_barang) as ket_barang,
                    tbd.qty,
                    COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0) as price,
                    (tbd.qty * COALESCE(NULLIF(tbd.price, 0), rb.harga_satuan, 0)) as subtotal,
                    tbd.lot_no
                FROM trans_barang_detail tbd
                JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                LEFT JOIN ref_barang rb ON rb.id = tbd.id_barang
                LEFT JOIN ref_gudang rg ON rg.id = tbh.id_gudang
                WHERE tbh.jenis_transaksi = 2
                AND tbh.id_kategori IN (5, 16)
                AND tbh.status = 1
                AND tbh.active = 1
                AND tbh.tanggal >= '$btkStart'
                AND tbh.tanggal <= '$endDate'
                ORDER BY tbh.tanggal ASC, tbd.id ASC
            ";
            $btkRes = $this->db->query($sqlBtk)->getResult();

            foreach ($btkRes as $r) {
                $ket = !empty($r->nama_barang) ? $r->nama_barang : "-";
                if (!empty($r->ket_barang) && $r->ket_barang != $r->nama_barang) {
                    $ket .= " (" . $r->ket_barang . ")";
                }
                if (!empty($r->no_ref_wo)) {
                    $ket .= " [WO: " . $r->no_ref_wo . "]";
                }
                $resultsBiayaProduksi[] = (object)[
                    'tipe_bayar'     => !empty($r->tipe_bayar) ? $r->tipe_bayar : "-",
                    'tgl_transaksi'  => $r->tgl_transaksi,
                    'type'           => 'Pemakaian Bahan',
                    'kode_transaksi' => !empty($r->kode_transaksi) ? $r->kode_transaksi : "-",
                    'kode_akun'      => !empty($r->kode_barang) ? $r->kode_barang : "-",
                    'nama_akun'      => !empty($r->nama_barang) ? $r->nama_barang : "-",
                    'keterangan'     => $ket,
                    'nominal'        => (float)$r->subtotal
                ];
            }
        }

        usort($resultsBiayaProduksi, function ($a, $b) {
            return strtotime($a->tgl_transaksi) <=> strtotime($b->tgl_transaksi);
        });

        $results_all = array_merge($resultsSO, $resultsCR);
        usort($results_all, function ($a, $b) {
            return strtotime($a->tgl_transaksi) <=> strtotime($b->tgl_transaksi);
        });

        $fileName = "Laporan Detail Laba Rugi $periode_text.xlsx";

        $sheets = new Spreadsheet;
        $gets = $sheets->getActiveSheet();
        
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('16')->setBold(true);
        $sheets->getActiveSheet()->freezePane('E5');

        $sheets->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Laporan Detail Laba Rugi Periode: ' . $periode_text)
            ->setCellValue('A4', 'Tipe Bayar / Gudang')
            ->setCellValue('B4', 'Tanggal')
            ->setCellValue('C4', 'Transaksi')
            ->setCellValue('D4', 'No. Ref / Transaksi')
            ->setCellValue('E4', 'Kode')
            ->setCellValue('F4', 'Nama Akun / Barang')
            ->setCellValue('G4', 'Keterangan')
            ->setCellValue('H4', 'Masuk')
            ->setCellValue('I4', 'Keluar')
            ->setCellValue('J4', 'Saldo');

        $stylexArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '1f1f1f'],
                ],
            ],
            'alignment' => [
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
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
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'C5D9F1',
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
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'C5D9F1',
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
            
        $gets->getStyle('A4:J4')->applyFromArray($styleArray_header);
        $sheets->getActiveSheet()->mergeCells('A2:J2');

        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
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

        $gets->getStyle('A4:J4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);

        $gets->setTitle('Detail');
        $indexs = array('A','B','C','D', 'E','F','G', 'H', 'I', 'J');

        for ($i=0; $i < 10 ; $i++) { 
            $sheets->getActiveSheet()->getStyle($indexs[$i] .'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C5D9F1');
            $sheets->getActiveSheet()->getStyle($indexs[$i].'4')
                    ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
            $gets->getStyle($indexs[$i].'4')->applyFromArray($styleArray_header);
        }

        $ix = 5;
        $startRow = $ix;
        $length = $ix + count($results_all);

        foreach ($results_all as $r) { 
            $sheets->setActiveSheetIndex(0)
                ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                ->setCellValue('B'.$ix, formatTanggalIndonesia(date('Y-m-d', strtotime($r->tgl_transaksi))))
                ->setCellValue('C'.$ix, $r->type) 
                ->setCellValue('D'.$ix, ($r->type == "Sales Order") ? $r->kode_sales_order : $r->kode_cr)
                ->setCellValue('E'.$ix, "-")
                ->setCellValue('F'.$ix, "-")
                ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-");

            $nominal = !empty($r->nominal) ? $r->nominal : 0;
            $sheets->getActiveSheet()->setCellValue('H'.$ix, $nominal);
            $sheets->getActiveSheet()->getStyle("H" . $ix)->getNumberFormat()->setFormatCode('#,##0.00');
            $gets->getStyle('A'.$ix.':J'.$ix)->applyFromArray($stylexArray);
            $ix++; 
        }

        $sheets->setActiveSheetIndex(0)->setCellValue('A'.$length, "Total Penjualan");
        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);
        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);
        
        $rowPenjualan = $length;
        $sheets->setActiveSheetIndex(0)->setCellValue('J' . $length, '=SUM(H' . $startRow . ':H' . ($length-1) . ')');
        $gets->getStyle("J" . $length )->getNumberFormat()->setFormatCode('#,##0.00');
        
        // Section 2: Biaya Produksi / Pembelian
        $ix = $length + 1;
        $startRow = $ix;
        for ($xx=0; $xx < count($resultsBiayaProduksi); $xx++) {
            $r = $resultsBiayaProduksi[$xx]; 
            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tipe_bayar) ? $r->tipe_bayar : "-")
                    ->setCellValue('B'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tgl_transaksi)))) : "-")
                    ->setCellValue('C'.$ix, $r->type)
                    ->setCellValue('D'.$ix, !empty($r->kode_transaksi) ? $r->kode_transaksi : "-")
                    ->setCellValue('E'.$ix, !empty($r->kode_akun) ? $r->kode_akun : "-")
                    ->setCellValue('F'.$ix, !empty($r->nama_akun) ? $r->nama_akun : "-")
                    ->setCellValue('G'.$ix, !empty($r->keterangan) ? $r->keterangan : "-")
                    ->setCellValue('I'.$ix, !empty($r->nominal) ? $r->nominal : 0);
            $sheets->getActiveSheet()->getStyle("I" . $ix )->getNumberFormat()->setFormatCode('#,##0.00');
            $gets->getStyle('A'.$ix.':J'.$ix)->applyFromArray($stylexArray);
            $ix++;
        }
        $length = $ix;

        $labelBiayaProdExcel = "Total Biaya Produksi / Pembelian";
        if ($endDate < $cutoff) {
            $labelBiayaProdExcel = "Total Beban Pembelian";
        } else if ($startDate >= $cutoff) {
            $labelBiayaProdExcel = "Total Pemakaian Bahan Baku";
        }

        $sheets->setActiveSheetIndex(0)->setCellValue('A'.$length, $labelBiayaProdExcel);
        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);
        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $rowPembelian = $length;
        $sheets->setActiveSheetIndex(0)->setCellValue('J' . $length, '=SUM(I' . $startRow . ':I' . ($length-1) . ')');
        $gets->getStyle("J" . $length )->getNumberFormat()->setFormatCode('#,##0.00');

        $length++;
        $sheets->setActiveSheetIndex(0)->setCellValue('A'.$length, "Laba Kotor");
        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);
        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $rowLabaKotor = $length;
        $sheets->setActiveSheetIndex(0)->setCellValue('J' . $length, '=J' . $rowPenjualan . '- J' . $rowPembelian);
        $gets->getStyle("J" . $length )->getNumberFormat()->setFormatCode('#,##0.00');

        // Section 3: Beban Operasional
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
            $sheets->getActiveSheet()->getStyle("I" . $ix )->getNumberFormat()->setFormatCode('#,##0.00');
            $gets->getStyle('A'.$ix.':J'.$ix)->applyFromArray($stylexArray);
            $ix++;
        }
        $length = $ix;

        $sheets->setActiveSheetIndex(0)->setCellValue('A'.$length, "Total Beban Operasional");
        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);
        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $rowBiaya = $length;
        $sheets->setActiveSheetIndex(0)->setCellValue('J' . $length, '=SUM(I' . $startRow . ':I' . ($length-1) . ')');
        $gets->getStyle("J" . $length )->getNumberFormat()->setFormatCode('#,##0.00');

        $length++;
        $sheets->setActiveSheetIndex(0)->setCellValue('A'.$length, "Laba Bersih");
        $sheets->getActiveSheet()->mergeCells('A'. $length .':I'. $length);
        $gets->getStyle('A'.$length.':I'.$length)->applyFromArray($stylexArrayFootertext);
        $gets->getStyle('J'.$length)->applyFromArray($stylexArrayFooter);

        $sheets->setActiveSheetIndex(0)->setCellValue('J' . $length, '=J' . $rowLabaKotor . '- J' . $rowBiaya);
        $gets->getStyle("J" . $length )->getNumberFormat()->setFormatCode('#,##0.00');

        $sheets->setActiveSheetIndex(0);
        $writer = new Xlsx($sheets);
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0');
        ob_end_clean();
        $writer->save('php://output');
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
