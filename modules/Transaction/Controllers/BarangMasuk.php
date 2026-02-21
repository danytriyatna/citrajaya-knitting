<?php

namespace Modules\Transaction\Controllers;

use App\Models\FileModel;
use App\Libraries\DompdfGenerator;
use App\Controllers\BaseController;
use Modules\Referensi\Models\BarangModel;
use Modules\Referensi\Models\GudangModel;
use Modules\Referensi\Models\SatuanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Modules\Referensi\Models\KonsumenModel;
use Modules\Referensi\Models\OperatorModel;
use Modules\Referensi\Models\JenisBarangModel;
use Modules\Transaction\Models\BarangMasukModel;
use Modules\Referensi\Models\ProsesProduksiModel;
use Modules\Transaction\Models\ItemTransferModel;
use Modules\Transaction\Models\IncomingGoodsModel;
use Modules\Transaction\Models\BarangMasukDetailModel;
use Modules\Transaction\Models\ItemTransferDetailModel;


class BarangMasuk extends BaseController
{
    protected $mBarang;
    protected $mRef;
    protected $mRefDet;
    protected $mJenisBarang;
    protected $mSatuan;
    protected $mBarangMasuk;
    protected $mTrf;
    protected $mTrfDet;
    protected $mGudang;
    protected $mOperator;
    protected $mkonsumen;
    protected $mProses;

    protected $views = '\Modules\Transaction\Views';
    protected $urlv  = 'trans/incoming-goods';

    function __construct()
    {
        $this->MOD_ALIAS = "MOD_TRANSAKSI_BARANG_MASUK";
        $this->mBarang = new BarangModel();
        $this->mJenisBarang = new JenisBarangModel();
        $this->mSatuan = new SatuanModel();
        $this->mBarangMasuk = new IncomingGoodsModel();
        $this->mRef = new BarangMasukModel();
        $this->mRefDet = new BarangMasukDetailModel();
        $this->mGudang = new GudangModel();
        $this->mTrf = new ItemTransferModel();
        $this->mTrfDet = new ItemTransferDetailModel();
        $this->files  = new FileModel();
        $this->mOperator = new OperatorModel();
        $this->mkonsumen = new KonsumenModel();
        $this->mProses = new ProsesProduksiModel();
    }

    public function index()
    {

        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['titlehead'] = "Transaksi Data Barang Masuk ";

        $sortSatuan = [
            [
                'field' => 'nama_satuan',
                'dir' => 'ASC'
            ]
        ];
        $sortJenisBarang = [
            [
                'field' => 'nama_jenis_barang',
                'dir' => 'ASC'
            ]
        ];

        $dataSatuan = $this->mSatuan->getData(null, 0, 99999, $sortSatuan);
        $dataJenisBarang = $this->mJenisBarang->getData(null, 0, 99999, $sortJenisBarang);
        $this->data['satuan'] = $dataSatuan;
        $this->data['jenisBarang'] = $dataJenisBarang;
        return view($this->views . '\barang_masuk_list', $this->data);
    }


    public function lists()
    {
        $start      = $this->request->getPost('start');
        $limit      = $this->request->getPost('length');
        $filters    = $this->request->getPost('filter');
        $order      = $this->request->getPost('sort');

        $params = [];

        $results = $this->mRef->getData(null, $start, $limit, $order, $filters, $params);
        $totalfiltered = $this->mRef->getDataCnt($filters, $params);
        $totaldata = $this->mRef->getDataCnt(null, $params);
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
            if ($this->_edit) {
                $atr_edit['title'] = 'Edit';
                $atr_edit['url'] = $this->urlv . '/edit/';
                $atr_edit['class'] = '';
            }
            // if ($this->_delete) {
            //     $atr_del['title'] = 'Hapus';
            //     $atr_del['url'] = $this->urlv . '/delete/';
            //     $atr_del['class'] = '';
            //     $atr_del['onclick'] = "return confirm('Hapus Data ?')";
            // }
            // if ($row->status != 0 && $row->id_kategori == 3) {
                $atr_other['title'] = 'Print';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            // }

            // if ($row->status != 0 && $row->id_kategori == 12) {
                $atr_other['title'] = 'Print Faktur';
                $atr_other['target'] = "blank";
                $atr_other['url'] = $this->urlv . '/print-faktur/';
                $atr_other['class'] = '';
                $atr_other['icon_class'] = 'fa-print';
            // }

            if ($atr_edit || $atr_other)
                $btnAction = btn_action_group($id, $atr_edit, $atr_del, $atr_other);

            // $aktif =  ($row->active) ? "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/deactivate/".$id."' data-confirm-message='Anda yakin ingin menonaktifkan user ini?'><i class='fa fa-check text-success'>&nbsp;</i></a>" :
            //                            "<a href='javascript:void(0)' class='atr_active' data-item-active='utilitas/users/activate/".$id."' data-confirm-message='Anda yakin ingin mengaktifkan user ini?'><i class='fa fa-times text-danger'>&nbsp;</i></a>";
            $status = "";
            if ($row->status == 0) {
                $status = "<span class='badge bg-secondary'>Draft</span>";
            } else if ($row->status == 1) {
                $status = "<span class='badge bg-success'>Approved</span>";
            }
            array_push(
                $build_array["data"],
                array(
                    "aksi" => $btnAction ? $btnAction : '',
                    "id"   => ($id),
                    "no_ref_trf" => $row->no_ref_trf,
                    "kode_transaksi" => $row->kode_transaksi,
                    "tanggal" => fdate_eng_to_ind($row->tanggal),
                    "kategori" => $row->kategori,
                    "nama_gudang" => $row->nama_gudang,
                    "keterangan" => $row->keterangan,
                    "proses" => $row->proses,
                    "nama_operator" => $row->nama_operator,
                    "status" => $status
                )
            );
        }
        return $this->response->setJSON($build_array);
    }

    public function form($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        $this->data['id'] = $id;


        if ($id != "") {
            $id = decrypt($id);
            $resData = $this->mRef->getData($id);
            
            $tanggal = date("d F Y", strtotime($resData->tanggal));
            $resData->tanggal = $tanggal;
            $resData->id_buyer = !empty($resData->id_buyer) ? encrypt($resData->id_buyer) : null;
            $sort = [
                [
                    'field' => 'uk.id',
                    'dir' => 'ASC'
                ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            foreach ($resDataDetail as &$rowData) {
                $rowData->id_barang = encrypt($rowData->id_barang);
            }
            if (!empty($resData->no_ref_trf)) {
                # code...
                $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            }
            else if (empty($resData->no_ref_trf) && isset($resData->id_proses) && isset($resData->id_cmt)) {
                $results = $this->mTrf->getDataByProsesAndOperator($resData->id_proses, $resData->id_cmt);
            }

            $this->data['resData'] = $resData;
            $this->data['detail'] = json_encode($resDataDetail);

            if($resData->id_kategori == 12 || $resData->id_kategori == 1){
                if (isset($resData->no_ref_trf)) {
                # code...
                    $resDataDetSO = !empty($results) ? $this->mRef->getDataDetSO($id) : null;
                }
                else if (empty($resData->no_ref_trf) && isset($resData->id_proses) && isset($resData->id_cmt)) {
                    $resDataDetSO = count($results) > 0 ? $this->mRef->getDataDetSO($id) : null;
                }
                $this->data['dataSO'] = json_encode($resDataDetSO);
            }else{
                $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
                $this->data['dataSO'] = json_encode($resDataDetSO);
            }
        }
        $reDataKategori = $this->mBarangMasuk->getRefKategoriPersedian();
        $sortGudang = [
            [
                'field' => 'nama_gudang',
                'dir' => 'ASC'
            ]
        ];
        $resDataGudang = $this->mGudang->getData(null, 0, 99999, $sortGudang);
        
        $sortProses = [
            [
                'field' => 'nama',
                'dir' => 'ASC'
                ]
            ];
        $resDataProses = $this->mProses->getData(null, 0, 99999, $sortProses);

        $sortOperator = [
            [
                'field' => 'nama_operator',
                'dir' => 'ASC'
                ]
            ];
        $resDataOperator = $this->mOperator->getData(null, 0, 99999, $sortOperator);
        $this->data['kategori']    = $reDataKategori;
        $this->data['gudang']    = $resDataGudang;
        $this->data['proses']    = $resDataProses;
        $this->data['data_cmt']    = $resDataOperator;
        // dd($this->data['resData']);
        $this->data['titlehead'] = "Form Barang Masuk";

        $sortOperator = [
        [
            'field' => 'nama_operator',
            'dir' => 'ASC'
        ]
        ];
        $dataOperator = $this->mOperator->getData(null, 0, 99999, $sortOperator);
        $this->data['operator']    = $dataOperator;
        // dd($this->data);
        return view($this->views . '\barang_masuk_form', $this->data);
    }

    function save()
    {

        $msg    = "Data gagal disimpan !";
        $status = false;
        $id = $this->request->getPost('id');
        $tanggal = $this->request->getPost('tanggal');
        $statusData = $this->request->getPost('status');
        $id_gudang = $this->request->getPost('id_gudang');
        $id_buyer = $this->request->getPost('id_buyer');
        $id_kategori = $this->request->getPost('id_kategori');
        $nama = $this->request->getPost('nama');
        $keterangan = $this->request->getPost('keterangan');
        $dataDetail = $this->request->getPost('data');
        $no_ref_trf = $this->request->getPost('no_ref_trf');

        $id_proses = $this->request->getPost("id_proses");
        $id_cmt = $this->request->getPost("id_cmt");
        $nomor_mesin = $this->request->getPost("nomor_mesin");
        $jam_mesin = $this->request->getPost("jam_mesin");
        $nilai_mesin = $this->request->getPost("nilai_mesin");

        $dataProduksi = $this->request->getPost("dataProduksi");

        if ($id != "") {
            $id = decrypt($id);
        }
        if ($id_buyer != "") {
            $id_buyer = decrypt($id_buyer);
        }

        $dataHeader = [
            // "id_buyer" => !empty($id_buyer) ? $id_buyer : null,
            "tanggal" => $tanggal,
            "status" => $statusData,
            "jenis_transaksi" => 1,
            "id_gudang" => $id_gudang,
            "id_kategori" => $id_kategori,
            "no_ref_trf" => $no_ref_trf,
            "keterangan" => $keterangan,
            "nama" => $nama,
            // "id_proses" => $id_proses,
            // "id_cmt" => $id_cmt,
            // "jml_qc" => $jml_qc,
            // "jml_mesin" => $jml_mesin,
            // "jml_lain" => $jml_lain,
        ];

        if(!empty($id_buyer)){
            $dataHeader['id_buyer'] = $id_buyer;
        }

        if($id_kategori == 12 || $id_kategori == 1){
            $dataHeader['id_proses'] = $id_proses;
            $dataHeader['id_cmt'] = !empty($id_cmt) ? $id_cmt : -11 ;
            if (!empty($nomor_mesin)) {
                $dataHeader['nomor_mesin'] = $nomor_mesin;
            }
            if (!empty($jam_mesin)) {
                $dataHeader['jam_mesin'] = $jam_mesin;
            }
            if (!empty($nilai_mesin)) {
                $dataHeader['nilai_mesin'] = $nilai_mesin;
            }
        }

        if ($id) {
            $dataHeader['updated_at'] = date("Y-m-d H:i:s");
            $dataHeader['updated_by'] = $this->get_userid();
        } else {
            $dataHeader['created_at'] = date("Y-m-d H:i:s");
            $dataHeader['created_by'] = $this->get_userid();
        }
        // print_r(json_encode($dataHeader));exit;
        $res = $this->mRef->trxInsertUpdateRecord($dataHeader, $id, $dataDetail, $dataProduksi);
        if ($res['status']) {
            $status = true;
            $msg = "Data berhasil disimpan!";
        }else{
            $msg = $res['message'];
        }

        $build_array['message'] = $msg;
        $build_array['status']  = $status;
        return $this->response->setJSON($build_array);
    }

    function getLastStock()
    {
        $idGudangTujuan = $this->request->getPost("idGudangTujuan");
        $idGudangAsal = $this->request->getPost("idGudangAsal");
        $idBarang = $this->request->getPost("idBarang");
        $data = [];
        $idBarang = decrypt($idBarang);
        $results = $this->mBarangMasuk->getLastStokBarang($idBarang, $idGudangTujuan);
        if (!empty($idGudangAsal)) {
            $resGudangAsal = $this->mBarangMasuk->getLastStokBarang($idBarang, $idGudangAsal);
        }
        $data['status'] = true;
        $data['stok'] = !empty($results) ? $results->stok : 0;
        $data['stokAsal'] = !empty($resGudangAsal) ? $resGudangAsal->stok : 0;

        return $this->response->setJSON($data);
    }

    public function activate($id)
    {
        if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
            throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        $activation = $this->mBarang->activate($id);
        if ($activation) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "User Diaktifkan");
            $this->session->setFlashdata('message', "User berhasil di aktifkan");
        } else {
            $this->session->setFlashdata('err', "User gagal di aktifkan !");
        }
        return redirect()->to($this->urlv);
    }

    public function deactivate($id = NULL)
    {
        if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
            throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        // if ($id == 1) {
        //     return redirect()->to($this->urlv);
        // }
        $data = ['active' => 0];

        $deactivate = $this->mBarang->updateRecord($this->mBarang->table, $data, 'id', $id);
        if ($deactivate) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Data Barang Dinonaktifkan");
            $this->session->setFlashdata('message', "Data Barang berhasil di Hapus ");
        } else {
            $this->session->setFlashdata('err', "Data Barang gagal di Hapus !");
        }
        return redirect()->to($this->urlv);
    }

    public function delete($id = NULL)
    {
        if (!$this->auth->loggedIn() or (!$this->auth->isAdmin() && !$this->auth->isSuperadmin())) {
            throw new \Exception('You must be an administrator to view this page.');
        }

        if ($id != null && $id != "") {
            $id = decrypt($id);
        }

        $id = (int)$id;
        if ($id == 1) {
            return redirect()->to($this->urlv);
        }

        $res = $this->mBarang->deleteUser($id);
        if ($res) {
            $this->mcommon->setLog($this->currentUser->user_id, $this->MOD_ALIAS, $id, "Master Ukuran Dihapus");
            $this->session->setFlashdata('message', "Master Ukuran berhasil dihapus");
        } else {
            $this->session->setFlashdata('err', "Master Ukuran gagal dihapus");
        }
        return redirect()->to($this->urlv);
    }

    public function print($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        $dompdf = new \Dompdf\Dompdf();
        // Set Dompdf options for portrait orientation
        $dompdf->setPaper('A4', 'portrait');

        $this->data['data'] = [];
        if ($id != "") {
            $id = decrypt($id);
            $resData = $this->mRef->getData($id);

            $sort = [
            [
                'field' => 'uk.id',
                'dir' => 'ASC'
            ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            $results = $this->mTrf->getDataByNoTrf($resData->no_ref_trf);
            $resDataDetSO = !empty($results) ? $this->mTrfDet->getDataDetSO($results->id) : null;
            $this->data['data'] = !empty($resData) ? $resData : [];
            $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
        }
        $html = view($this->views . '\barang_masuk_print', $this->data);

        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
        exit;
    }


    public function print_faktur($id = null)
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        }
        $dompdf = new \Dompdf\Dompdf();
        // Set Dompdf options for portrait orientation
        $dompdf->setPaper('A4', 'portrait');

        $this->data['data'] = [];
        if ($id != "") {
            $id = decrypt($id);
            $resData = $this->mRef->getData($id);

            $sort = [
            [
                'field' => 'uk.id',
                'dir' => 'ASC'
            ]
            ];

            $resDataDetail = $this->mRefDet->getData(null, 0, 99999, $sort, params: array("id_header" => $id, "isReceive" => false));
            $results = $this->mTrf->getDataByProsesAndOperator($resData->id_proses, $resData->id_cmt);
            
            $dtKonsumen = [];
            $resDataDetSO = !empty($results) ? $this->mRef->getDataDetSO($id) : null;
            if(!empty($resDataDetSO)){
                $id_konsumen = $resDataDetSO[0]->id_konsumen;
                $dtKonsumen = $this->mkonsumen->getData($id_konsumen);
            }
            $this->data['dataSO'] = ($resDataDetSO);
            
            $this->data['dtKonsumen'] = !empty($dtKonsumen) ? $dtKonsumen : [];
            $this->data['data'] = !empty($resData) ? $resData : [];
            // $this->data['dataSO'] = !empty($resDataDetSO) ? $resDataDetSO : [];
            $this->data['detail'] = !empty($resDataDetail) ? $resDataDetail : [];
            // dd($this->data);
        }
        $html = view($this->views . '\barang_masuk_faktur_print', $this->data);

        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('rec_item.pdf', ['Attachment' => true]);
        exit;
    }

    public function print_excel_lists($from_date, $to_date){

        $fileName = "BTM-List.xlsx";

        $id = $this->request->getGet('data_id');

        $tanggal_sql_from = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
        $tanggal_sql_to = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

        $results = $this->mRef->get_export($tanggal_sql_from, $tanggal_sql_to);
        

        //start phpspreadsheet
        $sheets    = new Spreadsheet;

        $gets = $sheets->getActiveSheet();
        $title = formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_from))))." - ".formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $tanggal_sql_to))));
        $gets->getStyle('A2')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          

        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A2', 'Barang Masuk (BTM) '. $title)

               ->setCellValue('A4', 'TANGGAL TRANSAKSI')
               ->setCellValue('B4', 'KODE TRANSAKSI')
               ->setCellValue('C4', 'JENIS TRANSAKSI')
               ->setCellValue('D4', 'NO REF TRANSFER')
               ->setCellValue('E4', 'KODE SALES ORDER')
               ->setCellValue('F4', 'NAMA BUYER')
               ->setCellValue('G4', 'STYLE')
               ->setCellValue('H4', 'DESKRIPSI')
               ->setCellValue('I4', 'GUDANG PENGIRIM')
               ->setCellValue('J4', 'NAMA PROSES')
               ->setCellValue('K4', 'NAMA CMT')
               ->setCellValue('L4', 'COLOR')
               ->setCellValue('M4', 'KODE UKURAN')
               ->setCellValue('N4', 'KETERANGAN')
               ->setCellValue('O4', 'NOMOR MESIN')
               ->setCellValue('P4', 'JAM MESIN')
               ->setCellValue('Q4', 'NILAI MESIN')
               ->setCellValue('R4', 'QTY KIRIM')
               ->setCellValue('S4', 'QTY')
               ->setCellValue('T4', 'HARGA')
               ->setCellValue('U4', 'AMOUNT')
               ->setCellValue('V4', 'STATUS')
               ->setCellValue('W4', 'TGL SCAN');

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
        $gets->getStyle('A4:W4')->applyFromArray($styleArray_header);
        // $gets->getStyle('A3:I3')->applyFromArray($styleArray_header);
        
        // set mergecell
        // $sheets->getActiveSheet()->mergeCells('A2:I2');
        $sheets->getActiveSheet()->mergeCells('A2:W2');
        // $sheets->getActiveSheet()->mergeCells('A4:I4');
        // $sheets->getActiveSheet()->mergeCells('A5:C5');

        // set Center title
        $sheets->getActiveSheet()->getStyle('A2')
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        
        // set width
          $gets->getColumnDimension('A')->setWidth(20);
          $gets->getColumnDimension('B')->setWidth(17);
          $gets->getColumnDimension('C')->setWidth(35);
          $gets->getColumnDimension('D')->setWidth(17);
          $gets->getColumnDimension('E')->setWidth(17);
          $gets->getColumnDimension('F')->setWidth(30);
          $gets->getColumnDimension('G')->setWidth(25);
          $gets->getColumnDimension('H')->setWidth(35);
          $gets->getColumnDimension('I')->setWidth(25);
          $gets->getColumnDimension('J')->setWidth(35);
          $gets->getColumnDimension('K')->setWidth(35);
          $gets->getColumnDimension('L')->setWidth(45);
          $gets->getColumnDimension('M')->setWidth(10);
          $gets->getColumnDimension('N')->setWidth(20);
          $gets->getColumnDimension('O')->setWidth(10);
          $gets->getColumnDimension('P')->setWidth(10);
          $gets->getColumnDimension('Q')->setWidth(10);
          $gets->getColumnDimension('R')->setWidth(15);
          $gets->getColumnDimension('S')->setWidth(15);
          $gets->getColumnDimension('T')->setWidth(30);
          $gets->getColumnDimension('U')->setWidth(30);
          $gets->getColumnDimension('V')->setWidth(10);
          $gets->getColumnDimension('W')->setWidth(25);

        // end set width
        //   $gets->getStyle('A3:I3')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:W4')->getFont()->setName('Arial Narrow')->setSize('12')->setBold(true);
          $gets->getStyle('A4:W4')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

        
        $gets->setTitle('Detail');
        $indexs = array(
            'A','B','C','D', 'E','F','G', 'H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W'
        );

        for ($i=0; $i < 23 ; $i++) { 

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
        }

        $qty = 0;
        $amount = 0;
        
        for ($xx = 0; $xx < count($results) ; $xx++) { 
            
            $r = $results[$xx];
            

            $qty += $r->qty;
            $amount += $r->amount;

            $status_data = "Draft";
            if (!empty($r->status) && $r->status == 1) {
                $status_data = "Approved";
            }

            $sheets->setActiveSheetIndex(0)
                    ->setCellValue('A'.$ix, !empty($r->tgl_transaksi) ? formatTanggalIndonesia(date('Y-m-d', strtotime(str_replace('/', '-', $r->tanggal)))) : "-")
                    ->setCellValue('B'.$ix, !empty($r->kode_transaksi) ? $r->kode_transaksi : "-")
                    ->setCellValue('C'.$ix, !empty($r->jenis_transaksi) ? $r->jenis_transaksi : "-")
                    ->setCellValue('D'.$ix, !empty($r->no_ref_trf) ? $r->no_ref_trf : '-')
                    ->setCellValue('E'.$ix, !empty($r->kode_sales_order) ? $r->kode_sales_order : '-')
                    ->setCellValue('F'.$ix, !empty($r->nama_buyer) ? $r->nama_buyer : '-')
                    ->setCellValue('G'.$ix, !empty($r->style) ? $r->style : '-')
                    ->setCellValue('H'.$ix, !empty($r->deskripsi) ? $r->deskripsi : '-')
                    ->setCellValue('I'.$ix, !empty($r->gudang_pengirim) ? $r->gudang_pengirim : '-')
                    ->setCellValue('J'.$ix, !empty($r->nama_proses) ? $r->nama_proses : '-')
                    ->setCellValue('K'.$ix, !empty($r->nama_cmt) ? $r->nama_cmt : '-')
                    ->setCellValue('L'.$ix, !empty($r->color) ? $r->color : '-')
                    ->setCellValue('M'.$ix, !empty($r->kode_ukuran) ? $r->kode_ukuran : '-')
                    ->setCellValue('N'.$ix, !empty($r->keterangan) ? $r->keterangan : '-')
                    ->setCellValue('O'.$ix, !empty($r->nomor_mesin) ? $r->nomor_mesin : '-')
                    ->setCellValue('P'.$ix, !empty($r->jam_mesin) ? $r->jam_mesin : '-')
                    ->setCellValue('Q'.$ix, !empty($r->nilai_mesin) ? $r->nilai_mesin : '-')
                    ->setCellValue('R'.$ix, !empty($r->qty_kirim) ? $r->qty_kirim : 0)
                    ->setCellValue('S'.$ix, !empty($r->qty) ? $r->qty : 0)
                    ->setCellValue('T'.$ix, !empty($r->harga) ? $r->harga : 0)
                    ->setCellValue('U'.$ix, !empty($r->amount) ? $r->amount : 0)
                    ->setCellValue('V'.$ix, $status_data)
                    ->setCellValue('W'.$ix, !empty($r->tgl_scan) ? $r->tgl_scan : '-');
                    // ->setCellValue('O'.$ix, !empty($row->bln1) ? $row->bln1 : 0);

            // if($length > 0 && $ix === $length - 1){
            //     $gets->getStyle('A'.$ix.':N'.$ix)->applyFromArray($style_bodyBottom);
            // }else{
                $gets->getStyle('A'.$ix.':W'.$ix)->applyFromArray($stylexArray);
            // }

            $sheets->getActiveSheet()->getStyle("T" . $ix .":U" . $ix )->getNumberFormat()
                    ->setFormatCode('#,##0.00');

            $ix++;
        }
        $sheets->setActiveSheetIndex(0)
               ->setCellValue('A'.$length, "Total");

        $sheets->getActiveSheet()->mergeCells('A'. $length .':R'. $length);
        
        $gets->getStyle('A'.$length.':W'.$length)->applyFromArray($stylexArrayFooter);
        
       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('S'.$length, $qty);

       $sheets->setActiveSheetIndex(0)
                    ->setCellValue('U'.$length, $amount);

       $gets->getStyle("U" . $length)->getNumberFormat()
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
