<?php

namespace Modules\Laporan\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Referensi\Models\SatuanModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Laporan\Models\LaporanPersediaanModel;

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
}
