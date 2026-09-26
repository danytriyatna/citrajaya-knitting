<?php

namespace Modules\Laporan\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Laporan\Models\LaporanStockCardModel;

class LaporanStockCard extends BaseController
{
    protected $mBarang;
    protected $mJenisBarang;
    protected $mSatuan;
    protected $mBarangMasuk;
    protected $mGudang;
    protected $mLaporan;

    protected $views = '\Modules\Laporan\Views';
    protected $urlv  = 'laporan/stock-card';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_LAPORAN_STOCK_CARD";
        $this->mBarang = new BarangModel();
        $this->mJenisBarang = new JenisBarangModel();
        $this->mSatuan = new SatuanModel();
        $this->mBarangMasuk = new IncomingGoodsModel();
        $this->mGudang = new GudangModel();
        $this->mLaporan = new LaporanStockCardModel();
        $this->files  = new FileModel();
    }

    public function index()
    {

        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Laporan Stock Card ";
        $sortGudang = [
            [
                'field' => 'nama_gudang',
                'dir' => 'ASC'
            ]
        ];
        $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
        $resTahun = $this->mLaporan->getTahun();
        $resBulan = $this->mLaporan->getBulan();

        $this->data['gudang']    = $resDataGudang;
        $this->data['tahun']    = $resTahun;
        $this->data['bulan']    = $resBulan;
        return view($this->views . '\laporan_stock_card', $this->data);
    }


    public function getDataLaporanStockCard()
    {

        $build_array = [];
        $build_array["code"] = 200;
        $build_array["status"] = false;

        $idBarang = $this->request->getGet('filter_barang_id');
        if ($idBarang != null) {
            $idBarang = decrypt($idBarang);
        }
        $idBarang = !empty($idBarang) ? (int) $idBarang : null;

        // Query model sekarang pakai parameter binding (bukan interpolasi string),
        // tapi cast ke int di sini tetap dipertahankan sebagai lapisan pertahanan
        // tambahan supaya nilai yang masuk ke query benar2 cuma angka.
        $filter_gudang = $this->request->getGet('filter_gudang_id');
        $filter_gudang = !empty($filter_gudang) ? (int) $filter_gudang : null;

        $tahun = $this->request->getGet('tahun');
        $tahun = !empty($tahun) ? (int) $tahun : null;

        $bulan = $this->request->getGet('bulan');
        $bulan = !empty($bulan) ? (int) $bulan : null;

        $resData = $this->mLaporan->getLaporanPersediaan($idBarang, $filter_gudang, $tahun, $bulan);
        // dd($resData);
        $total = $this->mLaporan->getDataGudang($idBarang, $filter_gudang, $tahun, $bulan);
        // dd($total);
        $total_awal = 0;
        $total_akhir = 0;
        if (isset($total)) {
            $total_awal = $total->saldo_awal;
            $total_akhir = $total->saldo_akhir;
        }

        if(!empty($resData)){
            foreach ($resData as $key => $value) {
                // Kolom "masuk" SELALU ada di setiap baris (hasil CASE...ELSE 0, bukan
                // null), jadi isset() selalu true dan cabang "keluar" di bawahnya tidak
                // akan pernah kejalan - akibatnya Jumlah untuk transaksi Keluar selalu 0.
                // Perbaikan: cek nilai numeriknya, bukan keberadaan propertinya.
                $qtyMasuk = isset($value->masuk) ? (float) $value->masuk : 0;
                $qtyKeluar = isset($value->keluar) ? (float) $value->keluar : 0;
                $price = isset($value->price) ? (float) $value->price : 0;

                if ($qtyMasuk != 0) {
                    $value->jumlah = $qtyMasuk * $price;
                }
                elseif ($qtyKeluar != 0) {
                    $value->jumlah = $qtyKeluar * $price;
                }
                else {
                    $value->jumlah = 0;
                }
            }
        }

        $build_array["message"] = "Data ditemukan";
        $build_array["data"] =  !empty($resData) ? $resData : [];
        $build_array["status"] = true;
        $build_array["total_awal"] = $total_awal;
        $build_array["total_akhir"] = $total_akhir;

        return $this->response->setJSON($build_array);
    }
}
