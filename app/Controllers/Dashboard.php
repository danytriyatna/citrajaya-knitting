<?php

namespace App\Controllers;

use App\Models\Mdashboard;
use Modules\Keuangan\Models\Mcoa;
use Modules\Transaction\Models\DeliveryModel;
use Modules\Transaction\Models\ProductionModel;
use Modules\Laporan\Models\LaporanPersediaanModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\JenisBarangModel;

class Dashboard extends BaseController
{
    protected $mdashboard;
    protected $mProduksi;
    protected $mdelivery;
    protected $mcoa;
    protected $mLaporanPersediaan;
    protected $mGudang;
    protected $mJenisBarang;

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_DASHBOARD";
        $this->mdashboard = new Mdashboard();
        $this->mProduksi = new ProductionModel();
        $this->mdelivery = new DeliveryModel();
        $this->mcoa = new Mcoa();
        $this->mLaporanPersediaan = new LaporanPersediaanModel();
        $this->mGudang = new GudangModel();
        $this->mJenisBarang = new JenisBarangModel();
    }

    public function index()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } else {
            $this->data['titlehead'] = "Dashboard";

            // Data untuk card "Ringkasan Persediaan"
            $sortGudang = [['field' => 'nama_gudang', 'dir' => 'ASC']];
            $sortJenisBarang = [['field' => 'nama_jenis_barang', 'dir' => 'ASC']];
            $this->data['gudangPersediaan'] = $this->mGudang->getData(null, 0, 99999, $sortGudang);
            $this->data['jenisBarangPersediaan'] = $this->mJenisBarang->getData(null, 0, 99999, $sortJenisBarang);
            $this->data['bulanPersediaanSaatIni'] = (int) date('n');
            $this->data['tahunPersediaanSaatIni'] = (int) date('Y');

            return view('dashboard/index', $this->data);
        }
    }

    /**
     * Ringkasan Persediaan (card dashboard) - list per item/warna, diagregasi dari
     * data snapshot bulanan (trans_barang_history) via LaporanPersediaanModel::getDataGudang().
     * Baris hasil getDataGudang() itu per item+lot+pack; di sini kita jumlahkan per id_barang
     * supaya tampil 1 baris per warna/item (KODE WARNA, NAMA BARANG, UNIT, QTY KELUAR, QTY AKHIR).
     */
    public function persediaan_ringkasan()
    {
        $idJenisBarang = $this->request->getGet('id_jenis_barang');
        $idGudang      = $this->request->getGet('id_gudang');
        $bulan         = $this->request->getGet('bulan');
        $tahun         = $this->request->getGet('tahun');
        $search        = trim((string) $this->request->getGet('search'));

        return $this->response->setJSON(
            $this->buildPersediaanRingkasan($idJenisBarang, $idGudang, $bulan, $tahun, $search)
        );
    }

    /**
     * Sama seperti tombol "Update Data" di Laporan Persediaan: recompute snapshot
     * trans_barang_history (via updateDataHistory()) dulu, baru ambil ringkasannya.
     * Dipakai oleh tombol "Update Data" di card dashboard - supaya angka yang tampil
     * benar-benar terkini, bukan sekadar baca cache lama.
     */
    public function persediaan_update()
    {
        $idJenisBarang = $this->request->getGet('id_jenis_barang');
        $idGudang      = $this->request->getGet('id_gudang');
        $bulan         = $this->request->getGet('bulan');
        $tahun         = $this->request->getGet('tahun');
        $search        = trim((string) $this->request->getGet('search'));

        $this->mLaporanPersediaan->updateDataHistory($idJenisBarang, $idGudang, $tahun, $bulan);

        return $this->response->setJSON(
            $this->buildPersediaanRingkasan($idJenisBarang, $idGudang, $bulan, $tahun, $search)
        );
    }

    private function buildPersediaanRingkasan($idJenisBarang, $idGudang, $bulan, $tahun, $search = '')
    {
        $rows = $this->mLaporanPersediaan->getDataGudang($idJenisBarang, $idGudang, $tahun, $bulan);

        // Ambil Booked Qty (QTY WO) per id_barang:
        // Rumus: Kebutuhan WO dikurang transfer yang gudang kirimnya CJK/P1 (id_gudang_asal = 1)
        $startCutoff = '2026-06-01';
        $nextMonthStart = sprintf('%04d-%02d-01', ($bulan == 12 ? $tahun + 1 : $tahun), ($bulan == 12 ? 1 : $bulan + 1));

        $db = \Config\Database::connect();
        $sqlBooked = "
            WITH wo_kebutuhan AS (
                SELECT 
                    wo.id as id_walkorder,
                    wo.kode_walkorder,
                    w.id_barang,
                    SUM(w.total) as total_kebutuhan
                FROM trans_walkorder wo
                JOIN trans_walkorder_detail d ON d.id_walkorder = wo.id
                JOIN trans_walkorder_warna w ON w.id_walkorder_detail = d.id
                LEFT JOIN trans_sales_order so ON so.id = wo.ref_id AND wo.tipe_id = 2
                WHERE wo.active = 1 
                  AND d.active = 1 
                  AND w.active = 1 
                  AND w.id_barang IS NOT NULL
                  AND wo.tipe_id = 2
                  AND wo.status = 2
                  AND (so.id IS NULL OR (so.active = 1 AND so.status != 3))
                  AND wo.tgl_transaksi >= '$startCutoff'
                  AND wo.tgl_transaksi < '$nextMonthStart'
                GROUP BY wo.id, wo.kode_walkorder, w.id_barang
            ),
            wo_transfer AS (
                SELECT 
                    d.kode_walkorder,
                    d.id_barang,
                    SUM(d.qty) as total_trf
                FROM trans_barang_trf_detail d
                JOIN trans_barang_trf_header h ON h.id = CAST(d.id_header AS INTEGER)
                WHERE h.status = 1 AND h.active = 1 
                  AND h.id_gudang_asal = 1
                  AND d.kode_walkorder IS NOT NULL AND d.kode_walkorder != ''
                  AND h.tanggal >= '$startCutoff'
                  AND h.tanggal < '$nextMonthStart'
                GROUP BY d.kode_walkorder, d.id_barang
            ),
            booked_per_wo AS (
                SELECT 
                    wk.id_walkorder,
                    wk.kode_walkorder,
                    wk.id_barang,
                    wk.total_kebutuhan,
                    COALESCE(wt.total_trf, 0) as total_trf,
                    GREATEST(0, wk.total_kebutuhan - COALESCE(wt.total_trf, 0)) as booked_qty
                FROM wo_kebutuhan wk
                LEFT JOIN wo_transfer wt ON wt.kode_walkorder = wk.kode_walkorder AND wt.id_barang = wk.id_barang
            )
            SELECT 
                id_barang,
                ROUND(SUM(booked_qty)::numeric, 2) as total_booked
            FROM booked_per_wo
            GROUP BY id_barang
        ";
        $bookedRows = $db->query($sqlBooked)->getResultArray();
        $bookedMap = [];
        foreach ($bookedRows as $bRow) {
            $bookedMap[$bRow['id_barang']] = (float)$bRow['total_booked'];
        }

        $grouped = [];
        $hargaTanggal = []; // tracker terpisah (bukan bagian output) - lot dgn tanggal transaksi terakhir yg dipakai sbg harga representatif per barang
        foreach ($rows as $r) {
            $key = $r->id_barang;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'id_barang'    => $r->id_barang,
                    'kode_warna'   => !empty($r->color_code) ? $r->color_code : '-',
                    'nama_barang'  => $r->barang,
                    'unit'         => $r->nama_satuan,
                    'qty_awal'     => 0,
                    'qty_masuk'    => 0,
                    'qty_keluar'   => 0,
                    'qty_akhir'    => 0,
                    'qty_wo'       => 0,
                    'qty_tersedia' => 0,
                    'harga'        => 0,
                ];
                $hargaTanggal[$key] = null;
            }
            // TAMBAHAN (2026-08-08): QTY AWAL & QTY MASUK - aman dijumlahkan
            // langsung sama seperti qty_keluar/qty_akhir, karena getDataGudang()
            // sudah baca dari trans_barang_history (snapshot bulanan PER lot+pack,
            // bukan per transaksi), jadi tiap baris di $rows sudah final utk
            // lot+pack+bulan itu - bukan baris transaksi individual yang perlu
            // dikelompokkan dulu (beda dengan persediaan_detail() yg baca dari
            // getLaporanPersediaan() per-transaksi).
            $grouped[$key]['qty_awal']   += (float) $r->saldo_awal;
            $grouped[$key]['qty_masuk']  += (float) $r->masuk;
            $grouped[$key]['qty_keluar'] += (float) $r->keluar;
            $grouped[$key]['qty_akhir']  += (float) $r->saldo_akhir;

            // Harga representatif per barang - satu barang bisa punya banyak Lot dgn
            // harga beda-beda (beda batch beli), dipilih harga dari Lot dgn
            // tanggal transaksi PALING BARU (bukan dijumlahkan, harga bukan qty).
            if (!empty($r->price) && (empty($hargaTanggal[$key]) || strtotime($r->tanggal) >= strtotime($hargaTanggal[$key]))) {
                $hargaTanggal[$key] = $r->tanggal;
                $grouped[$key]['harga'] = (float) $r->price;
            }
        }

        foreach ($grouped as $key => $row) {
            $qtyWo = isset($bookedMap[$key]) ? $bookedMap[$key] : 0;
            $grouped[$key]['qty_wo'] = $qtyWo;

            // Qty Akhir tetap saldo fisik akhir (saldo_awal + masuk - keluar)
            // Qty Tersedia = Qty Akhir (fisik) - Qty WO (Booked)
            $qtyTersedia = $row['qty_akhir'] - $qtyWo;
            $grouped[$key]['qty_tersedia'] = $qtyTersedia;

            // Nilai = Qty Tersedia x Harga representatif - dinolkan kalau Qty Tersedia minus
            $grouped[$key]['nilai'] = ($qtyTersedia > 0) ? ($qtyTersedia * $row['harga']) : 0;
        }

        $data = array_values($grouped);

        if (!empty($search)) {
            $needle = strtolower($search);
            $data = array_values(array_filter($data, function ($row) use ($needle) {
                return strpos(strtolower($row['kode_warna']), $needle) !== false
                    || strpos(strtolower($row['nama_barang']), $needle) !== false;
            }));
        }

        usort($data, function ($a, $b) {
            return $b['qty_keluar'] <=> $a['qty_keluar'];
        });

        return [
            'status'         => true,
            'data'           => $data,
            'total_awal'     => array_sum(array_column($data, 'qty_awal')),
            'total_masuk'    => array_sum(array_column($data, 'qty_masuk')),
            'total_keluar'   => array_sum(array_column($data, 'qty_keluar')),
            'total_akhir'    => array_sum(array_column($data, 'qty_akhir')),
            'total_wo'       => array_sum(array_column($data, 'qty_wo')),
            'total_tersedia' => array_sum(array_column($data, 'qty_tersedia')),
            'total_nilai'    => array_sum(array_column($data, 'nilai')),
        ];
    }

    /**
     * Detail stock card untuk 1 item (dipanggil dari tombol Detail di card Ringkasan Persediaan).
     * Ambil dari LaporanPersediaanModel::getLaporanPersediaan() (baris per transaksi utk seluruh
     * gudang+jenis+bulan+tahun tsb), lalu difilter di sini utk id_barang yang dipilih saja.
     */
    public function persediaan_detail()
    {
        $idJenisBarang = $this->request->getGet('id_jenis_barang');
        $idGudang      = $this->request->getGet('id_gudang');
        $bulan         = $this->request->getGet('bulan');
        $tahun         = $this->request->getGet('tahun');
        $idBarang      = $this->request->getGet('id_barang');

        $rows = $this->mLaporanPersediaan->getLaporanPersediaan($idJenisBarang, $idGudang, $tahun, $bulan);

        $filtered = array_values(array_filter($rows, function ($r) use ($idBarang) {
            return (string) $r->id_barang === (string) $idBarang;
        }));

        // Total Awal dihitung dari getDataGudang() (baca trans_barang_history) persis
        // seperti yang dipakai buildPersediaanRingkasan() - supaya konsisten dgn QTY AWAL
        // di card Ringkasan Persediaan (titik berangkatnya tetap sama).
        $gudangRows = $this->mLaporanPersediaan->getDataGudang($idJenisBarang, $idGudang, $tahun, $bulan, ['id_barang' => $idBarang]);
        $totalAwal = 0;
        foreach ($gudangRows as $g) {
            $totalAwal += (float) $g->saldo_awal;
        }

        // TAMBAHAN (2026-08-08): qty_awal/qty_akhir per baris TIDAK lagi dipakai dari
        // r->saldo_awal/r->saldo_akhir bawaan getLaporanPersediaan() - kolom itu adalah
        // saldo berjalan PER LOT+PACK (window function di-partition per lot), jadi baris
        // pertama sebuah lot baru akan menunjukkan saldo lot itu sendiri (mis. 0), BUKAN
        // Total Awal keseluruhan barang. Di sini kita hitung ulang jadi SATU saldo
        // berjalan yang menyambung lintas semua lot (diurutkan tanggal, sama seperti
        // urutan baris $filtered apa adanya), di-seed dari $totalAwal di atas - supaya
        // baris pertama selalu = Total Awal, konsisten dgn Laporan Stock Card (menu
        // Laporan) yang pakai logika yg sama.
        // Total Akhir SENGAJA diambil dari titik akhir saldo berjalan ini (bukan dari
        // getDataGudang()/cache trans_barang_history lagi) - supaya baris terakhir tabel
        // detail selalu match dgn "Total Akhir" yang ditampilkan (konsisten secara
        // internal), walau itu berarti bisa berbeda sementara dgn QTY AKHIR di card
        // Ringkasan kalau cache-nya belum di-"Update Data".
        $running = $totalAwal;
        $detail = [];
        foreach ($filtered as $r) {
            $masuk = (float) $r->masuk;
            $keluar = (float) $r->keluar;
            $price = (float) $r->price;

            $qtyAwal = $running;
            $running += ($masuk - $keluar);
            $qtyAkhir = $running;

            $detail[] = [
                'tanggal'      => !empty($r->tanggal) ? fdate_eng_to_ind($r->tanggal) : '-',
                'transaksi'    => !empty($r->transaksi) ? $r->transaksi : '-',
                'no_transaksi' => $r->kode_transaksi,
                'unit'         => $r->nama_satuan,
                'lot'          => !empty($r->lot_no) ? $r->lot_no : '-',
                'pack'         => !empty($r->pack_name) ? $r->pack_name : '-',
                'qty_awal'     => $qtyAwal,
                'qty_masuk'    => $masuk,
                'qty_keluar'   => $keluar,
                'qty_akhir'    => $qtyAkhir,
                'harga'        => $price,
                // Nilai = Qty Akhir x Harga (bukan cuma harga satuan) - kalau Qty Akhir
                // minus (barang belum sempat dicatat masuk tapi sudah keluar), Nilai
                // ditampilkan 0, bukan negatif.
                'nilai'        => ($qtyAkhir > 0) ? ($qtyAkhir * $price) : 0,
                'jumlah'       => ($masuk > 0) ? ($masuk * $price) : null,
            ];
        }
        $totalAkhir = $running;

        $build_array = [
            'status'      => true,
            'total_awal'  => $totalAwal,
            'total_akhir' => $totalAkhir,
            'detail'      => $detail,
        ];

        return $this->response->setJSON($build_array);
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
            $params['tabel'] = "v_traking_order_sample";
        }

        else {
            $results = $this->mdashboard->getData(null, $start, $limit, $order, $filters, $params);
            $totalfiltered = $this->mdashboard->getDataCnt($filters, $params);
            $totaldata = $this->mdashboard->getDataCnt(null, $params);
            $params['tabel'] = "v_traking_order_so";
        }
        $getSum = $this->mdashboard->getSumData(null, $start, $limit, $order, $filters, $params);
        $total_qty            = $getSum ? (float)$getSum->total_qty : 0;
        $total_qty_prod       = $getSum ? (float)$getSum->total_qty_prod : 0;
        $total_qty_hasil      = $getSum ? (float)$getSum->total_qty_prod : 0;
        $total_qty_sisa_prod  = $getSum ? (float)$getSum->total_qty_sisa_prod : 0;
        $total_qty_kirim      = $getSum ? (float)$getSum->total_qty_kirim : 0;
        $total_qty_sisa_kirim = $getSum ? (float)$getSum->total_qty_sisa_kirim : 0;
        $maxpage = ceil($totalfiltered / $limit);
        $build_array = array(
            "total_qty" => $total_qty,    
            "total_qty_prod" => $total_qty_prod,
            "total_qty_hasil" => $total_qty_hasil,
            "total_qty_sisa_prod" => $total_qty_sisa_prod,
            "total_qty_kirim" => $total_qty_kirim,
            "total_qty_sisa_kirim" => $total_qty_sisa_kirim,
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "data" => array()
        );
        // $total = 0;
        foreach ($results as $key => $row) {
            $id = encrypt($row->trans_id);
            // if ($row->trans_id == "873") {
            //     dd($row->trans_kode, $row->id_prod, $row->id_walkorder);
            // }
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

            $tgl_deadline_dua = "";
            if(!empty($row->tgl_deadline_dua)){
                $tgl_deadline_dua = fdate_eng_to_ind($row->tgl_deadline_dua);
            }

            $tgl_transaksi = "";
            if(!empty($row->tgl_transaksi)){
                $tgl_transaksi = fdate_eng_to_ind($row->tgl_transaksi);
            }

            $parms['last_proses'] = 1;
            $parms['id_walkorder'] = $row->id_walkorder;
            
            if (!empty($row->id_walkorder)) {
                $dataLast = $this->mProduksi->getDataProsesProdNew($row->id_prod, true);
                $last_data = !empty($dataLast) ? $dataLast : [];
    
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
                    // $qty_hasil = $last_data->qty_prod - $qty_kirim;
                    $qty_hasil = $last_data->qty_prod;
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
            if (!empty($row->id_prod)) {
                $dataRajut = $this->mProduksi->getDataProsesProdNew($row->id_prod, false, true);
                $rajut_data = !empty($dataRajut) ? $dataRajut : [];
                if (!empty($rajut_data)) {
                    // $qty_hasil = $last_data->qty_prod - $qty_kirim;
                    $qty_prod = $rajut_data->qty_prod;
                }
                else {
                    $qty_prod = 0;
                }
            }
            else {
                $qty_prod = 0;
            }

            $qty_so = !empty($row->qty) ? (float)$row->qty : 0;
            $qty_hasil_val = !empty($qty_hasil) ? (float)$qty_hasil : 0;
            $qty_sisa = max(0, $qty_so - $qty_hasil_val);

            $pembayaran = !empty($row->nilai_pembayaran) ? (float)$row->nilai_pembayaran : 0;
            $dp = !empty($row->uang_dp) ? (float)$row->uang_dp : 0;
            $dp += !empty($row->uang_dp_2) ? (float)$row->uang_dp_2 : 0;

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
                'tgl_deadline_dua' => $tgl_deadline_dua,
                'qty' => !empty($row->qty) ? (float)$row->qty : 0,
                // 'qty_prod' => $row->qty_prod,
                'qty_prod' => (float)$qty_prod,
                'qty_kirim' => !empty($row->qty_kirim) ? (float)$row->qty_kirim : 0,
                'uang_dp' => $dp,
                'pembayaran' => (float)($pembayaran + $dp),
                'harga_total' => !empty($row->harga_total) ? (float)$row->harga_total : 0,
                'nilai_invoice' => !empty($row->nilai_invoice) ? (float)$row->nilai_invoice : 0,
                'qty_sisa' => !empty($qty_sisa) ? (float)$qty_sisa : 0,
                'qty_hasil' => !empty($qty_hasil) ? (float)$qty_hasil : 0,
                'qty_sisa_kirim' => !empty($qty_sisa_kirim) ? (float)$qty_sisa_kirim : 0,
            ));

        }
        $build_array['total_qty_hasil'] = array_sum(array_column($build_array['data'], 'qty_hasil'));
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
            
            # code...
            array_push($build_array['data'], array(
                // 'btnPo' => $btnPo,
                // 'po_date' => $po_date,
                'nama' => $row->nama_vendor,
                "po_date_exp" => !empty($row->po_date_exp) ? fdate_eng_to_ind($row->po_date_exp) : null,
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

        $penjualan = $this->mdashboard->getDataPCR($month, $year);
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

    public function lists_saldo()
    {
        $start   = $this->request->getPost('start');
        $limit   = $this->request->getPost('length');
        $filters = $this->request->getPost('filter');
        $order   = $this->request->getPost('order');
        $month   = $this->request->getPost('month');
        $year   = $this->request->getPost('year');
        // $tahun   = $this->request->getPost('tahun');

        // $params['tahun'] = $tahun;
        $params['month'] = $month;
        $params ['year'] = $year;
        $results = $this->mdashboard->getDataSaldo(null, $start, $limit, $order, $filters, $params);
        $totalSaldoGet = $this->mdashboard->getDataSaldoAll(null, $start, $limit, $order, $filters, $params);

        $totalfiltered = $this->mdashboard->getDataSaldoCnt($filters, $params);
        $totaldata = $this->mdashboard->getDataSaldoCnt(null, $params);
        $maxpage = ceil($totalfiltered / $limit);
        // TAMBAHAN (2026-08-08): beri tahu frontend apakah periode (bulan/tahun)
        // yang sedang dipilih ini SUDAH PERNAH dihitung lewat tombol "Update"
        // atau belum - supaya kalau grid kosong, user tahu itu karena belum
        // pernah di-update (bukan dikira data memang kosong/bug).
        $sudahDiupdate = $this->mdashboard->hasDataSaldo($month, $year);
        $build_array = array(
            "total_saldo" => !empty($totalSaldoGet) ?  number_format($totalSaldoGet, 2, ',', '.') : 0,
            "last_page" => $maxpage,
            "recordsTotal" => $totaldata,
            "recordsFiltered" => $totalfiltered,
            "sudah_diupdate" => $sudahDiupdate,
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
            
            array_push($build_array['data'], array(
                'kode' => $row->kode,
                'nama' => $row->nama,
                'saldo' => $row->saldo,
            ));
        }
        
        return $this->response->setJSON($build_array);
    }

    public function update_saldo($tahun, $bulan){

        $refCoa = $this->mcoa->getData(null, 0, 9999, null, null, null, null, null, 8);

        try {
            if (!empty($refCoa)) {
            foreach ($refCoa as $key => $value) {
                $results = $this->mcoa->get_mutasi_export_all($bulan, $tahun, $value->coa_id);
                $resultsSO = $this->mcoa->get_mutasi_export_so($bulan, $tahun, $value->coa_id);
                $resultsCR = $this->mcoa->get_mutasi_export_cr($bulan, $tahun, $value->coa_id);
                $resultsPB = $this->mcoa->get_mutasi_export_pb($bulan, $tahun, $value->coa_id);

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

                $saldo = $this->mcoa->get_mutasi_history($getBulan, $getTahun, $value->coa_id);

                $grand_total = 0;
                $grand_total_masuk = 0;
                $grand_total_sub = 0;
                $grand_total_sub_masuk = 0;

                for ($xx = 0; $xx < count($results_all) ; $xx++) { 
            
                    $r = $results_all[$xx];

                    if ($r->type == "Beban Biaya" || $r->type == "Kas Masuk") {
                        $isKasMasuk = ($r->type == "Kas Masuk" || (isset($r->tipe_transaksi) && $r->tipe_transaksi == 2) || substr($r->trans_akun_kode, 0, 3) == 'KM.');
                        $jml = !empty($r->jumlah) ? (float)$r->jumlah : 0;

                        if (!$isKasMasuk) {
                            $isMasuk = ($r->coa_id == $value->coa_id);
                        } else {
                            $isMasuk = ($r->coa_id != $value->coa_id);
                        }

                        if ($isMasuk) {
                            $grand_total_masuk += $jml;
                            $grand_total_sub_masuk += $jml;
                        } else {
                            $grand_total += $jml;
                            $grand_total_sub += $jml;
                        }
                    }

                    else if ($r->type == "Sales Order") {
                        $grand_total_masuk += !empty($r->uang_dp) ? $r->uang_dp : 0;
                        $grand_total_sub_masuk += !empty($r->uang_dp) ? $r->uang_dp : 0;
                    }

                    else if ($r->type == "Customer Receipt") {
                        $grand_total_masuk += !empty($r->total_bayar) ? $r->total_bayar : 0;
                        $grand_total_sub_masuk += !empty($r->total_bayar) ? $r->total_bayar : 0;
                    }

                    else if ($r->type == "Pembayaran") {
                        $grand_total += !empty($r->total_bayar) ? $r->total_bayar : 0;
                        $grand_total_sub += !empty($r->total_bayar) ? $r->total_bayar : 0;
                    }
                }

                $saldo = !empty($saldo->saldo) ? $saldo->saldo : 0;

                $isiSaldo = [
                    'coa_id' => $value->coa_id,
                    'month' => $bulan,
                    'year' => $tahun,
                    'saldo' => $saldo + $grand_total_masuk - $grand_total
                ];
                $saldo_new = $this->mcoa->get_mutasi_history($bulan, $tahun, $value->coa_id);
                if (!empty($saldo_new)) {
                    $saldo_id = $this->mcoa->updateRecord($this->mcoa->table2, $isiSaldo, "id", $saldo_new->id);
                }
                else {
                    $saldo_id = $this->mcoa->insertRecordGetid($this->mcoa->table2, $isiSaldo);
                }
                }
            }
            $build_array["status"] = true;

            return $this->response->setJSON($build_array);
        } catch (\Throwable $th) {
            $build_array["status"] = false;

            return $this->response->setJSON($build_array);
        }


    }
}
