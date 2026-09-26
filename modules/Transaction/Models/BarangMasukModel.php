<?php

namespace Modules\Transaction\Models;

class BarangMasukModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_header";
    protected $tblDet = "trans_barang_detail";
    protected $kd = "1";
    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblBuyer = "ref_konsumen";
    protected $tblKategori = "ref_kategori_persediaan";
    protected $tblSatuan = "ref_satuan";
    protected $tblTrxBarang = "trans_barang";
    protected $tblTrxLots = "trans_lots";
    protected $tblTrxBalances = "trans_barang_balances";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang = abx.id", "left");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblBuyer . " ebx", "uk.id_buyer = ebx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = uk.id_cmt', 'left');

        $builder->select("uk.no_ref_trf,uk.id, uk.id_buyer,uk.status, uk.id_kategori, uk.keterangan, abx.nama_gudang,  uk.tanggal, ebx.nama,
                          uk.kode_transaksi, dbx.kategori, uk.id_gudang, uk.nilai_mesin, uk.nomor_mesin, uk.jam_mesin, uk.id_cmt, uk.id_proses,
                          jp.nama as proses, rp.nama_operator, rp.alamat as alamat_cmt, uk.id_perusahaan");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            $builder->where('uk.jenis_transaksi', $this->kd);
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.no_ref_trf) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rp.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(jp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(dbx.kategori) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_perusahaan'])) {
                if ($params['id_perusahaan'] == 1) {
                    $builder->groupStart();
                        $builder->where('uk.id_perusahaan', 1);
                        $builder->orWhere('uk.id_perusahaan IS NULL');
                    $builder->groupEnd();
                } else {
                    $builder->where('uk.id_perusahaan', $params['id_perusahaan']);
                }
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('uk.id', 'DESC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("uk.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang = abx.id", "left");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblBuyer . " ebx", "uk.id_buyer = ebx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = uk.id_cmt', 'left');
        $builder->select("count(1) as _cnt");
        $builder->where('uk.jenis_transaksi', $this->kd);
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.no_ref_trf) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rp.nama_operator) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(jp.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(dbx.kategori) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if (!empty($params['id_perusahaan'])) {
            if ($params['id_perusahaan'] == 1) {
                $builder->groupStart();
                    $builder->where('uk.id_perusahaan', 1);
                    $builder->orWhere('uk.id_perusahaan IS NULL');
                $builder->groupEnd();
            } else {
                $builder->where('uk.id_perusahaan', $params['id_perusahaan']);
            }
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function get_export($from_date = null, $to_date = null, $id_perusahaan = null){
        $builder = $this->db->table("trans_barang_masuk_produksi tbmp");
        $builder->join($this->table . " uk", "tbmp.id_header = uk.id", "inner");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang = abx.id", "left");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblBuyer . " ebx", "tbmp.id_konsumen = ebx.id", "left");
        $builder->join('_jenis_proses_produksi jp', 'jp.id = uk.id_proses', 'left');
        $builder->join('ref_operator rp', 'rp.id = uk.id_cmt', 'left');

        $builder->select("uk.tanggal, tbmp.tgl_transaksi, uk.kode_transaksi, dbx.kategori as jenis_transaksi, uk.no_ref_trf, tbmp.kode_sales_order, ebx.nama as nama_buyer, tbmp.style, tbmp.deskripsi, abx.nama_gudang as gudang_pengirim, 
                        jp.nama as nama_proses, rp.nama_operator as nama_cmt, tbmp.color, tbmp.kode_ukuran, tbmp.keterangan, 
                        tbmp.nomor_mesin, tbmp.jam_mesin, tbmp.nilai_mesin, tbmp.qty_kirim, tbmp.qty, tbmp.harga, tbmp.amount, tbmp.tgl_scan, tbmp.print_type, uk.status");
        $builder->where('uk.active = 1');
        $builder->where("tbmp.tgl_transaksi BETWEEN '$from_date' AND '$to_date'");
        $builder->orderBy("tbmp.tgl_transaksi", "desc");
        
        if (!empty($id_perusahaan)) {
            if ($id_perusahaan == 1) {
                $builder->groupStart();
                    $builder->where('uk.id_perusahaan', 1);
                    $builder->orWhere('uk.id_perusahaan IS NULL');
                $builder->groupEnd();
            } else {
                $builder->where('uk.id_perusahaan', $id_perusahaan);
            }
        }

        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function generateKodePersediaan($type = null, $id_perusahaan = null)
    {
        $kd = "BTM";
        $builder = $this->db->table($this->table . ' a');
        if (!empty($id_perusahaan) && $id_perusahaan != 1) {
            $kd = "BTMC";
            if (!empty($type)) {
                $builder->select("LEFT(kode_transaksi, 8) AS tgl, RIGHT( kode_transaksi, 5 ) AS kode ");
            }
            else {
                $builder->select("LEFT(kode_transaksi, 8) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");
            }
            $builder->where("LEFT(kode_transaksi, 4) = '$kd'");
    
            $builder->orderBy('a.id', "DESC");
            $builder->limit(1);
            $query = $builder->get()->getRow();
    
            if ($query != NULL) {
                if ($query->tgl == $kd . date('y') . date('m')) {     //cek dulu apakah ada sudah ada tahun dan bulan di tabel.   
                    //jika tahun dan bulan ternyata sudah ada.      
                    // $data = $query->row();
                    $kode = intval($query->kode) + 1;
                } else {
                    //jika tahun dan belum ada      
                    $kode = 1;
                }
            } else {
                $kode = 1;
            }
    
            $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT); // angka 3 menunjukkan jumlah digit angka 0
            $kodejadi = $kd . date('y') . date('m') . $kodemax;
        }
        else {
            if (!empty($type)) {
                $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 5 ) AS kode ");
            }
            else {
                $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");
            }
            $builder->where("LEFT(kode_transaksi, 3) = '$kd'");
            $builder->where("LEFT(kode_transaksi, 4) != 'BTMC'");
    
            $builder->orderBy('a.id', "DESC");
            $builder->limit(1);
            $query = $builder->get()->getRow();
    
            if ($query != NULL) {
                if ($query->tgl == $kd . date('y') . date('m')) {     //cek dulu apakah ada sudah ada tahun dan bulan di tabel.   
                    //jika tahun dan bulan ternyata sudah ada.      
                    // $data = $query->row();
                    $kode = intval($query->kode) + 1;
                } else {
                    //jika tahun dan belum ada      
                    $kode = 1;
                }
            } else {
                $kode = 1;
            }
    
            $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $kodejadi = $kd . date('y') . date('m') . $kodemax;
        }

        // Anti-collision verification
        while ($this->db->table($this->table)->where('kode_transaksi', $kodejadi)->countAllResults() > 0) {
            $kode++;
            $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $kodejadi = $kd . date('y') . date('m') . $kodemax;
        }

        // hasilnya SOD24100001 dst.
        return $kodejadi;
    }

    function trxInsertUpdateRecord($data, $id, $detail, $dataProduksi)
    {
        $this->db->transStart();
        try {
            $nama = $data['nama'];
            $tgl_trans = $data['tanggal'];
            unset($data['nama']);

            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                // $this->deleteRecordMultipleColumn('trans_barang_masuk_produksi', $arrDelete);
                $getCurrent = $this->getData($id);
                $data['kode_transaksi'] = $getCurrent->kode_transaksi;
                if ($getCurrent->status == 1) {
                    // Dokumen yang sudah Approved tidak boleh lagi mengubah tanggal transaksi -
                    // kalau tidak dikunci, setiap edit berikutnya akan menimpa tanggal di
                    // trans_barang (sumber Laporan Stock Card) dengan tanggal baru dari form,
                    // padahal nomor dokumennya sudah dikunci sejak awal dibuat. Ini persis
                    // penyebab kasus TRF260600171 di modul Item Transfer sebelumnya.
                    $tgl_trans = $getCurrent->tanggal;
                    $data['tanggal'] = $getCurrent->tanggal;
                }
                $this->updateRecords($this->table, $data, array("id" => $id));
                if (!empty($tgl_trans) && !empty($data['kode_transaksi'])) {
                    $this->db->table($this->tblTrxBarang)
                        ->where('kode_transaksi', $data['kode_transaksi'])
                        ->update(['tanggal' => date("Y-m-d H:i:s", strtotime($tgl_trans))]);
                }
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan("BTM", $data['id_perusahaan']);
                $id = $this->insertRecordGetid($this->table,  $data);
            }

            if(!empty($detail)){
                foreach ($detail as $key => $rowData) {
                    $idBarang = null;
                    if (!empty($rowData['id_barang'])) {
                        $idBarang = is_numeric($rowData['id_barang']) ? (int)$rowData['id_barang'] : decrypt($rowData['id_barang']);
                    }

                    $dataDetail = [
                        "id_barang" => $idBarang,
                        "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                        "lot_id" => !empty($rowData['lot_id']) ? $rowData['lot_id'] : 0,
                        "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                        "id_header" => $id,
                        "qty" => $rowData['qty'],
                        "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                    ];

                    $this->insertRecordGetid($this->tblDet, $dataDetail);

                    // Hapus dulu baris trans_barang lama utk dokumen ini (sekali saja,
                    // di iterasi pertama) sebelum insert ulang - supaya edit berulang
                    // pada dokumen yang sudah Approved tidak menumpuk baris ledger
                    // duplikat (sebelumnya di sini langsung insert tanpa hapus dulu).
                    if ($data['status'] == 1 && $key === 0 && !empty($data['kode_transaksi'])) {
                        // CATATAN (2026-08-08): sebelum baris trans_barang lama dihapus,
                        // qty yang PERNAH ditambahkan ke trans_lots akibat dokumen ini
                        // (kalau dokumen ini sudah pernah disimpan/di-approve sebelumnya
                        // lalu diedit ulang) harus dibalik (dikurangi) dulu - baris
                        // trans_barang lama adalah catatan persis berapa & dari lot mana.
                        // Tanpa ini, trans_lots akan terus bertambah dobel setiap kali
                        // dokumen Barang Masuk yang sama diedit/disimpan ulang - pola bug
                        // sama seperti yang sudah diperbaiki di Item Transfer, Purchasing
                        // Terima Barang, Barang Keluar & Delivery Order.
                        $oldTransBarangBm = $this->db->table($this->tblTrxBarang)
                            ->select('lot_id, SUM(jumlah) as jumlah')
                            ->where('kode_transaksi', $data['kode_transaksi'])
                            ->groupBy('lot_id')
                            ->get()->getResult();

                        foreach ($oldTransBarangBm as $otb) {
                            if (empty($otb->lot_id)) continue;
                            $this->db->table($this->tblTrxLots)
                                ->where('id', $otb->lot_id)
                                ->set('qty', 'qty - ' . (float) $otb->jumlah, false)
                                ->update();
                        }

                        $this->db->table($this->tblTrxBarang)->where('kode_transaksi', $data['kode_transaksi'])->delete();
                    }

                    if ($data['status'] == 1) {

                        if (empty( $rowData['lot_no']) || empty( $rowData['pack_id'])) {
                            throw new \Exception($rowData['kode_barang']." Lot No dan Pack tidak boleh kosong!");
                        }
    
                        $mBarangMasuk = new IncomingGoodsModel();
                        $arrParam =  [
                            "id_barang" => $idBarang,
                            "id_gudang" => $data['id_gudang'],
                        ];
                        // FIX (2026-08-08): sebelumnya id_gudang masuk ke parameter ke-3
                        // getLotNo() (idLotNo - filter berdasarkan ID baris trans_lots
                        // spesifik), bukan parameter ke-4 (idGudang) yang seharusnya.
                        $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, null, $data['id_gudang']);
                        $dataLots = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_no" => $rowData['lot_no'],
                            "qty" => $rowData['qty'],
                            "active" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                        ];
                        if (!empty($resLotNo)) {
                            $idLots = $resLotNo->id;
                            $this->updateRecords($this->tblTrxLots, array("qty" => $resLotNo->qty + $rowData['qty']), array("id" => $idLots));
                        } else {
                            $idLots = $this->insertRecordGetid($this->tblTrxLots, $dataLots);
                        }
                        
                        $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang'], $idLots, !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);
                        
                        // $stokAwal = !empty($resData) ? $resData->stok : 0;
                        $dataBarang = [
                            "id_barang" => $idBarang,
                            "jenis_transaksi" => 1,
                            "jumlah" =>  $rowData['qty'],
                            "tanggal" => !empty($tgl_trans) ? date("Y-m-d H:i:s", strtotime($tgl_trans)) : date("Y-m-d H:i:s"),
                            "id_gudang_tujuan" =>  !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "nama" => $nama,
                            "id_kategori" => $data['id_kategori'],
                            "keterangan" => "Barang Masuk Dari Incoming Goods",
                            "active" => 1,
                            "tipe" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => $rowData['pack_id'],
                            "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                            "kode_transaksi" => $data['kode_transaksi'],
                        ];
                        $this->insertRecordGetid($this->tblTrxBarang, $dataBarang);
                        $arrStockBalances = [
                            "id_barang" => $idBarang,
                            "id_gudang" => !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                            "tanggal" => date("Y-m-d H:i:s"),
                            "lot_id" => $idLots,
                            "lot_no" => $rowData['lot_no'],
                            "pack_id" => $rowData['pack_id'],
                            "saldo_awal" => 0,
                            "saldo_akhir" => $rowData['qty'],
                            "active" => 1,
                            "created_at" =>  date("Y-m-d H:i:s"),
                        ];
                        if (!empty($resData)) {
                            $this->updateRecords($this->tblTrxBalances, array("saldo_akhir" => $resLotNo->qty + $rowData['qty']), array("id" => $resData->id));
                        } else {
                            $this->insertRecordGetid($this->tblTrxBalances, $arrStockBalances);
                        }
                    }
                }
            }

            if($data['id_kategori'] == 12 || $data['id_kategori'] == 1 || $data['id_kategori'] == 15){
                if(!empty($dataProduksi)){

                    $hedr_data = $this->getData($id);

                    $id_proses = $data['id_proses'];
                    $id_cmt = $data['id_cmt'];
                    $kode_transaksi = $hedr_data->kode_transaksi;
                    $i = 0;

                    // CATATAN (2026-08-08): SEBELUMNYA di sini ada penghapusan SEMUA
                    // baris trans_produksi_operator utk kode_transaksi ini, sekali saja
                    // sebelum loop di bawah (delete-by-kode_transaksi). Itu memperbaiki
                    // masalah dobel KALAU seluruh riwayat dikirim ulang tiap simpan -
                    // tapi ternyata alur "Kartu Produksi" di sini simpan BERTAHAP (per
                    // sesi scan, lihat addItem() di form.js yang menggabungkan qty per
                    // kombinasi HANYA dalam satu sesi, bukan lintas sesi). Akibatnya
                    // delete-by-kode_transaksi tadi ikut menghapus riwayat dari sesi
                    // simpan SEBELUMNYA yang tidak ikut terkirim di sesi sekarang -
                    // data hilang (kasus BTMC260800021: 26 baris trans_barang_masuk_
                    // produksi tapi cuma 7 baris trans_produksi_operator, semuanya
                    // dengan created_at yang sama persis - hanya sisa sesi terakhir).
                    //
                    // Diganti jadi hapus PER ITEM (persis sebelum insert baris
                    // penggantinya, di dalam loop foreach $dataProduksi di bawah),
                    // dicocokkan berdasarkan identitas lengkap baris itu saja - supaya
                    // resave/re-approve item yang SAMA tidak menumpuk duplikat, TANPA
                    // menyentuh baris item LAIN dari sesi simpan sebelumnya.
                    $idMP = [];
                    foreach ($dataProduksi as $xrow) {
                        $xpr = [];
                        $xpr['kode_sales_order'] = $xrow['kode_sales_order'];
                        $xpr['kode_ukuran'] = $xrow['kode_ukuran'];
                        $clr = explode('~', $xrow['color']);
                        $xpr['kode_warna1'] = $clr[0];
                        if(!empty($clr[1])){
                            $xpr['kode_warna2'] = $clr[1];
                        }
                        if(!empty($clr[2])){
                            $xpr['kode_warna3'] = $clr[2];
                        }
                        if(!empty($clr[3])){
                            $xpr['kode_warna4'] = $clr[3];
                        }
                        if(!empty($clr[4])){
                            $xpr['kode_warna5'] = $clr[4];
                        }
                        if(!empty($clr[5])){
                            $xpr['kode_warna6'] = $clr[5];
                        }
                        if(!empty($clr[6])){
                            $xpr['kode_warna7'] = $clr[6];
                        }
                        if(!empty($clr[7])){
                            $xpr['kode_warna8'] = $clr[7];
                        }

                        $tipe = substr($xpr['kode_sales_order'], 0, 3) === 'SPL' ? 1 : (substr($xpr['kode_sales_order'], 0, 3) === 'SOD' ? 2 : null);

                        $dtSo = $this->getDataSO($xpr, $tipe);
                        
                        
                        $idSo = !empty($dtSo) ? $dtSo->id_sales_order : 0;

                        $rtgl = !empty($xrow['tgl_transaksi']) ? $xrow['tgl_transaksi'] : date('Y-m-d');

                        $tgl_scan = !empty($xrow['tgl_scan']) ? $xrow['tgl_scan'] : null;

                        $hanya_tanggal = null;
                        $isi_kurung = null;

                        // Kartu Produksi ASLI (cetakan pertama) tidak punya angka dalam
                        // kurung di belakang tanggal scan - itu cuma ada di kartu CETAK
                        // ULANG. Sebelumnya satu regex mensyaratkan tanggal DAN kurung
                        // sekaligus, jadi kartu asli (tanpa kurung) gagal total dan
                        // tanggal scan-nya ikut hilang (null) - padahal cuma print_type
                        // yang memang seharusnya kosong utk kartu asli. Sekarang diambil
                        // terpisah: tanggal tetap tersimpan walau kurungnya tidak ada.
                        if (!empty($tgl_scan)) {
                            $tglScanTrim = trim($tgl_scan);
                            if (preg_match('/^(\d{4}\s\d{2}\s\d{2}\s\d{2}-\d{2}-\d{2}-\d{6})/', $tglScanTrim, $matchesTgl)) {
                                $hanya_tanggal = $matchesTgl[1];
                            }
                            if (preg_match('/\((\d+)\)\s*$/', $tglScanTrim, $matchesKurung)) {
                                $isi_kurung = $matchesKurung[1];
                            }
                        }

                        $finalPrintType = !empty($xrow['print_type']) ? (int)$xrow['print_type'] : (!empty($isi_kurung) ? (int)$isi_kurung : 1);

                        if ($xrow['qty_kirim'] < $xrow['qty'] && $data['id_kategori'] != 1 && $finalPrintType != 2 && $data['id_perusahaan'] != 2) {
                            throw new \Exception("QTY Terima Melebihi QTY yang Tersedia!");
                        }

                        $qty_kirim_perusahaan = $xrow['qty_kirim'];
                        if ($data['id_perusahaan'] == 2) {
                            $qty_kirim_perusahaan = null;
                        }


                        $qty_val = !empty($xrow['qty']) ? (float)$xrow['qty'] : 0;
                        $berat_val = !empty($xrow['berat']) ? (float)$xrow['berat'] : 0;
                        $harga_item = !empty($xrow['harga']) ? (float)$xrow['harga'] : 0;
                        $amount_item = (!empty($xrow['amount']) && (float)$xrow['amount'] > 0) ? (float)$xrow['amount'] : ($qty_val * $harga_item);

                        $dataDetail = [
                            "id_ref" => !empty($idSo) ? $idSo : null,
                            "id_header" => $id,
                            "color" => $xrow['color'],
                            "tgl_scan" => !empty($hanya_tanggal) ? $hanya_tanggal : null,
                            "print_type" => $finalPrintType,
                            "deskripsi" => $xrow['deskripsi'],
                            "style" => !empty($xrow['style']) ? $xrow['style'] : null,
                            "keterangan" => !empty($xrow['keterangan']) ? $xrow['keterangan'] : null,
                            "total_scanned" => !empty($xrow['total_scanned']) ? $xrow['total_scanned'] : null,
                            "qty" => $qty_val,
                            "berat" => $berat_val,
                            "qty_qc" => $qty_val,
                            "berat_qc" => $berat_val,
                            "qty_kirim" => $qty_kirim_perusahaan,
                            "id_konsumen" => $xrow['id_konsumen'],
                            "kode_sales_order" => $xrow['kode_sales_order'],
                            "kode_ukuran" => $xrow['kode_ukuran'],
                            "amount" => $amount_item,
                            "harga" => $harga_item,
                             "tgl_transaksi" => $rtgl,

                            "nomor_mesin" => !empty($data['nomor_mesin']) ? $data['nomor_mesin'] : '-',
                           
                            // "tgl_transaksi" => !empty($data['tanggal']) ? $data['tanggal'] : null,
                            "jam_mesin" => !empty($data['jam_mesin']) ? $data['jam_mesin'] : 0,
                            "nilai_mesin" => !empty($data['nilai_mesin']) ? $data['nilai_mesin'] : 0,
                        ];

                        if (!empty($xrow['id_mp'])) {
                            if ($data['id_perusahaan'] != 2) {
                                $arrParamMP =  [
                                    "id_mp" => $xrow['id_mp'],
                                ];
    
                                $qtyNew = !empty($xrow['qty']) ? $xrow['qty'] : 0;
    
                                $getDataDetSo = $this->getDataDetSO(null, $arrParamMP);
    
                                $arrParamMPOther =  [
                                    'id_proses' => $id_proses, 
                                    'id_cmt' => $id_cmt, 
                                    'id_ref' => $idSo, 
                                    'color' => $xrow['color'], 
                                    'kode_ukuran' => $xrow['kode_ukuran'],
                                    'id_konsumen' => $xrow['id_konsumen'],
                                    'current_id_mp' => $xrow['id_mp'],
                                    'id_perusahaan' => $data['id_perusahaan'],
                                ];
                                $getDataDetSoOther = $this->getDataDetSO(null, $arrParamMPOther);
                                if ($getDataDetSo->qty != $qtyNew) {
                                    
                                    foreach ($getDataDetSoOther as $key_so => $value_so) {
                                        if ($getDataDetSo->qty < $qtyNew) {
                                            $newQTYUpdate = $value_so->qty_kirim - ($qtyNew - $getDataDetSo->qty);
                                        }
                                        else {
                                            $newQTYUpdate = $value_so->qty_kirim + ($getDataDetSo->qty - $qtyNew);
                                            // if ($value_so->id_mp == 71258) {
                                            //     # code...
                                            //     dd($value_so->qty_kirim, $getDataDetSo->qty, $qtyNew, $value_so->id_mp, $newQTYUpdate);
                                            // }
                                        }
                                        $this->updateRecord('trans_barang_masuk_produksi', ['qty_kirim' => $newQTYUpdate], 'id', $value_so->id_mp);
                                    }
                                }
                            }


                            $this->updateRecord('trans_barang_masuk_produksi', $dataDetail, 'id', $xrow['id_mp']);
                            // $getDataDetSo = $this->getDataDetSO(null, ['id_proses' => $id_proses, 'id_cmt' => $id_cmt, 'id_ref' => $idSo, 'color' => $xrow['color'], 'kode_ukuran' => $xrow['kode_ukuran']]);
                            $idMP[] = $xrow['id_mp'];
                        }
                        else {
                            $idMP[] = $this->insertRecordGetid('trans_barang_masuk_produksi', $dataDetail);
                        }
        

                        // $getDataDetSo = $this->getDataDetSO(null, ['id_proses' => $id_proses, 'id_cmt' => $id_cmt, 'id_ref' => $idSo, 'color' => $xrow['color'], 'kode_ukuran' => $xrow['kode_ukuran']]);

                        if($data['status'] == 1){
                            $xp['id_proses'] = $id_proses;
                            $xp['kode_ukuran'] = $xrow['kode_ukuran'];
                            $xp['ref_id'] = $idSo;
                            $dtProses = $this->getDataWP($xp);
                            if(!empty($dtProses)){
                                $idProduksi = $this->getDataProduksiByIdWalkorder($dtProses->id_walkorder)->id;

                                $qty = !empty($xrow['qty']) ? (float)$xrow['qty'] : 0;
                                $berat = !empty($xrow['berat']) ? $xrow['berat'] : 0;
                                $harga = !empty($xrow['harga']) ? (float)$xrow['harga'] : 0;
                                $amount = (!empty($xrow['amount']) && (float)$xrow['amount'] > 0) ? (float)$xrow['amount'] : ($qty * $harga);
                              
                                // $harga = 0;
                                // if($qty > 0){
                                //     $harga = $amount / $qty;
                                //     $harga = round($harga, 0);
                                // }
                                // insert data produksi
                                if (empty($dtSo->id_warna_1)) {

                                    print_r($dtSo);
                                     throw new \Exception("Data SO " . $xrow['kode_sales_order'] . " dengan warna " . $xrow['color'] . " tidak ditemukan, pastikan data SO sudah benar");
                                }
                                $arrDataUkuran = [
                                    "kode_transaksi" => $kode_transaksi,
                                    "id_produksi" => $idProduksi,
                                    "id_walkorder_proses_ukuran" => $dtProses->key_kedua,
                                    "id_proses" => $id_proses,
                                    "id_ukuran" => $dtProses->id_ukuran,
                                    "id_warna" => $dtSo->id_warna_1,
                                    "id_operator" => $id_cmt,
                                    "tgl_transaksi" => $rtgl,//date('Y-m-d'),
                                    "qty" =>  $qty_val,
                                    "berat" =>  $berat_val,
                                    "harga" => $harga_item,
                                    "harga_total" => $amount_item,
                                    "ref_detail_id" => $dtSo->id,
                                    "nomor_mesin" => !empty($data['nomor_mesin']) ? $data['nomor_mesin'] : '-',
                                    "active" => 1,
                                    "flag" => 1,
                                    "id_category" => $data['id_kategori'],
                                    "created_at" =>  date("Y-m-d H:i:s"),
                                    "print_type" => $finalPrintType,

                                ];

                                // Hapus dulu baris trans_produksi_operator LAMA yang identitasnya
                                // PERSIS sama dengan baris yang akan diinsert ini saja (bukan
                                // seluruh dokumen) - supaya resave/re-approve item yang sama tidak
                                // menumpuk duplikat, tapi item LAIN milik dokumen yang sama dari
                                // sesi simpan sebelumnya tetap aman tidak ikut terhapus.
                                $qtyLamaUtkItemIni = 0;
                                if ($data['status'] == 1 && !empty($kode_transaksi)) {
                                    $oldRowsItemIni = $this->db->table("trans_produksi_operator")
                                        ->where('kode_transaksi', $kode_transaksi)
                                        ->where('id_walkorder_proses_ukuran', $arrDataUkuran['id_walkorder_proses_ukuran'])
                                        ->where('id_ukuran', $arrDataUkuran['id_ukuran'])
                                        ->where('id_proses', $arrDataUkuran['id_proses'])
                                        ->where('id_operator', $arrDataUkuran['id_operator'])
                                        ->where('id_warna', $arrDataUkuran['id_warna'])
                                        ->where('ref_detail_id', $arrDataUkuran['ref_detail_id'])
                                        ->get()->getResult();

                                    foreach ($oldRowsItemIni as $orow) {
                                        $qtyLamaUtkItemIni += (float) $orow->qty;
                                    }

                                    if (!empty($oldRowsItemIni)) {
                                        $this->db->table("trans_produksi_operator")
                                            ->where('kode_transaksi', $kode_transaksi)
                                            ->where('id_walkorder_proses_ukuran', $arrDataUkuran['id_walkorder_proses_ukuran'])
                                            ->where('id_ukuran', $arrDataUkuran['id_ukuran'])
                                            ->where('id_proses', $arrDataUkuran['id_proses'])
                                            ->where('id_operator', $arrDataUkuran['id_operator'])
                                            ->where('id_warna', $arrDataUkuran['id_warna'])
                                            ->where('ref_detail_id', $arrDataUkuran['ref_detail_id'])
                                            ->delete();
                                    }
                                }

                                $this->insertRecordGetid("trans_produksi_operator", $arrDataUkuran);

                                // update qty - dikurangi dulu kontribusi baris lama utk item
                                // ini (kalau ada, lihat $qtyLamaUtkItemIni di atas) supaya tidak
                                // dihitung dobel saat baris penggantinya ditambahkan lagi.

                                $id_wop = $dtProses->key_kebenearan;
                                $qty_prod = max(0, (int) $dtProses->qty_prod - (int) $qtyLamaUtkItemIni);

                                $qty_now = (int) $qty_val + (int) $qty_prod;

                                $upd['qty_prod'] = $qty_now;

                                if ($data['id_kategori'] == 12) {
                                    # code...
                                    $this->updateRecord('trans_walkorder_proses_ukuran', $upd, 'id', $id_wop);
                                }

                            }else{
                                throw new \Exception("Ada salah satu data SO Belum sampai proses Produksi " . $xrow['kode_sales_order'] . ', pastikan data sudah sampai proses produksi');
                            }

                            $i++;
                        }
                    }

                    $this->deleteRecordCondition('trans_barang_masuk_produksi', 'id', $idMP, 'id_header', $id);
                    // exit;
                } else if (empty($detail)) {
                    throw new \Exception("Data Produksi tidak ada");
                }
            }

            if ($this->db->transStatus() === TRUE) {
                $this->db->transComplete();
                return [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                ];
            } else {
                throw new \Exception("Transaction failed");
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            // throw $e;
            return [
                'status' => false,
                'message' => $e->getMessage(),
                // 'message' => $e->getMessage() . " (di baris " . $e->getLine() . " file " . $e->getFile() . ")",
            ];
        }
    }

    /**
     * TAMBAHAN (2026-08-09): "Batalkan Approve" - membalik SEMUA efek yang
     * terjadi saat dokumen ini di-approve (lihat blok status==1 di
     * trxInsertUpdateRecord() di atas), lalu kembalikan status ke Draft (0).
     * Baris detail barang & Data Produksi TIDAK dihapus dari sisi "line item
     * dokumen" (yang dihapus hanya efek sampingnya ke tabel lain), supaya
     * dokumen bisa diedit & di-approve ulang.
     *
     * Yang dibalik:
     * 1) trans_lots - qty di lot tujuan dikurangi lagi.
     * 2) trans_barang_balances - baris snapshot HARI INI (kalau ada) utk lot
     *    terkait disamakan lagi ke qty trans_lots yg sudah dibalik.
     * 3) trans_barang - baris "masuk" milik dokumen ini dihapus.
     * 4) KHUSUS Tipe "Penerimaan Hasil Produksi" (id_kategori 12) atau
     *    "Sample/Perbaikan" (id_kategori 1):
     *    a) trans_produksi_operator - baris milik dokumen ini dihapus, dan
     *       trans_walkorder_proses_ukuran.qty_prod dikurangi lagi sebesar
     *       kontribusi dokumen ini per kombinasi item (id_walkorder_proses_
     *       ukuran + id_ukuran + id_proses + id_operator + id_warna +
     *       ref_detail_id) - HANYA utk id_kategori 12.
     *    b) trans_barang_masuk_produksi - TETAP DIPERTAHANKAN (tidak dihapus),
     *       karena ini adalah detail barang/data produksi dokumen agar bisa
     *       diedit & di-approve ulang tanpa kehilangan data inputan.
     */
    function cancelApprove($id, $updatedBy = null)
    {
        $this->db->transStart();
        try {
            $header = $this->getData($id);
            if (empty($header)) {
                throw new \Exception("Data barang masuk tidak ditemukan.");
            }
            if ((int) $header->status !== 1) {
                throw new \Exception("Dokumen ini belum di-Approve, tidak bisa dibatalkan.");
            }

            // 1) & 2) Balikkan trans_lots + samakan snapshot trans_barang_balances
            // hari ini, berdasarkan baris trans_barang yang tercatat utk
            // kode_transaksi ini. Barang Masuk hanya py sisi "masuk" (jenis_
            // transaksi=1, ke id_gudang_tujuan) - tidak ada sisi "keluar".
            $trxRows = $this->db->table($this->tblTrxBarang)
                ->select('id_barang, lot_id, pack_id, id_gudang_tujuan, SUM(jumlah) as jumlah')
                ->where('kode_transaksi', $header->kode_transaksi)
                ->groupBy('id_barang, lot_id, pack_id, id_gudang_tujuan')
                ->get()->getResult();

            foreach ($trxRows as $row) {
                if (empty($row->lot_id)) continue;

                $this->db->table($this->tblTrxLots)
                    ->where('id', $row->lot_id)
                    ->set('qty', 'qty - ' . (float) $row->jumlah, false)
                    ->update();

                $lotBaru = $this->db->table($this->tblTrxLots)->where('id', $row->lot_id)->get()->getRow();
                if (!empty($lotBaru)) {
                    $this->db->table($this->tblTrxBalances)
                        ->where('id_barang', $row->id_barang)
                        ->where('id_gudang', $row->id_gudang_tujuan)
                        ->where('lot_id', $row->lot_id)
                        ->where('pack_id', $row->pack_id)
                        ->where('tanggal', date('Y-m-d'))
                        ->set('saldo_akhir', $lotBaru->qty)
                        ->update();
                }
            }

            // 3) Hapus baris trans_barang milik dokumen ini.
            $this->db->table($this->tblTrxBarang)->where('kode_transaksi', $header->kode_transaksi)->delete();

            // 4) Kalau dokumen ini Tipe "Penerimaan Hasil Produksi"/"Sample-Perbaikan"/"Penerimaan Barang Produksi".
            if ($header->id_kategori == 12 || $header->id_kategori == 1 || $header->id_kategori == 15) {
                // Catatan: Baris trans_barang_masuk_produksi TIDAK dihapus, karena ini adalah
                // detail barang/data produksi dokumen. Dokumen dikembalikan ke status Draft (0)
                // agar pengguna tetap memiliki rincian barang dan dapat mengedit atau melakukan approve ulang.

                // trans_produksi_operator - kurangi lagi trans_walkorder_proses_
                // ukuran.qty_prod sebesar kontribusi dokumen ini per kombinasi item,
                // HANYA kalau id_kategori == 12 (persis syarat yg sama dipakai saat
                // approve), lalu hapus baris trans_produksi_operator milik dokumen ini.
                $opRows = $this->db->table('trans_produksi_operator')
                    ->select('id_walkorder_proses_ukuran, id_ukuran, id_proses, id_operator, id_warna, ref_detail_id, SUM(qty) as qty')
                    ->where('kode_transaksi', $header->kode_transaksi)
                    ->groupBy('id_walkorder_proses_ukuran, id_ukuran, id_proses, id_operator, id_warna, ref_detail_id')
                    ->get()->getResult();

                if ($header->id_kategori == 12) {
                    foreach ($opRows as $op) {
                        if (empty($op->id_walkorder_proses_ukuran)) continue;
                        $wopRow = $this->db->table('trans_walkorder_proses_ukuran')
                            ->where('id', $op->id_walkorder_proses_ukuran)
                            ->get()->getRow();
                        if (!empty($wopRow)) {
                            $newQtyProd = max(0, (int) $wopRow->qty_prod - (int) $op->qty);
                            $this->updateRecord('trans_walkorder_proses_ukuran', ['qty_prod' => $newQtyProd], 'id', $op->id_walkorder_proses_ukuran);
                        }
                    }
                }

                $this->db->table('trans_produksi_operator')->where('kode_transaksi', $header->kode_transaksi)->delete();
            }

            // 5) Kembalikan status dokumen ke Draft.
            $updateHeader = ['status' => 0, 'updated_at' => date('Y-m-d H:i:s')];
            if (!empty($updatedBy)) {
                $updateHeader['updated_by'] = $updatedBy;
            }
            $this->updateRecords($this->table, $updateHeader, ['id' => $id]);

            $this->db->transComplete();

            if ($this->db->transStatus() === TRUE) {
                return true;
            } else {
                throw new \Exception("Transaction failed");
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * MAINTENANCE ONLY (2026-08-08) - bangun ulang trans_produksi_operator
     * utk SATU dokumen (id_header) dari data trans_barang_masuk_produksi
     * (ground truth, sumber yang dipakai grid "Data Produksi" saat form
     * dibuka utk edit - lihat getDataDetSO()).
     *
     * Dipakai utk perbaiki data historis yang rusak akibat 2 bug terpisah:
     * 1. Bug lama: delete-by-kode_transaksi versi awal tidak diterapkan
     *    sama sekali -> resave berulang menumpuk baris duplikat di
     *    trans_produksi_operator (tpo lebih banyak dari yang seharusnya).
     * 2. Bug baru (baru diperbaiki): delete-by-kode_transaksi versi
     *    sebelumnya menghapus SELURUH baris utk satu dokumen sebelum
     *    insert ulang, padahal alur "Kartu Produksi" simpan bertahap
     *    per sesi -> riwayat sesi lama ikut hilang (tpo lebih sedikit).
     *
     * Baris trans_barang_masuk_produksi yang identitasnya sama (proses +
     * ukuran + operator + warna + ref_detail_id yang sama, setelah
     * di-resolve lewat getDataSO()/getDataWP() persis seperti alur simpan
     * normal) DIGABUNG (SUM qty & nilai) jadi SATU baris trans_produksi_
     * operator - supaya total qty & nilai per dokumen COCOK dengan total
     * trans_barang_masuk_produksi, walaupun baris fisiknya banyak (mis.
     * beberapa kali scan kartu produksi dgn kombinasi yang sama).
     *
     * @param int  $idHeader
     * @param bool $dryRun kalau true, TIDAK menulis apa pun ke DB - cuma
     *                      menghitung & melaporkan apa yang AKAN terjadi.
     */
    function rebuildProduksiOperatorForHeader($idHeader, $dryRun = false)
    {
        $hedr_data = $this->getData($idHeader);

        if (empty($hedr_data)) {
            return ['status' => false, 'message' => 'Header tidak ditemukan', 'id_header' => $idHeader];
        }
        if ($hedr_data->status != 1) {
            return ['status' => false, 'message' => 'Dokumen belum Approved (status != 1), dilewati', 'id_header' => $idHeader];
        }
        if (!in_array($hedr_data->id_kategori, [12, 1, 15])) {
            return ['status' => false, 'message' => 'Kategori bukan Produksi/CMT, dilewati', 'id_header' => $idHeader];
        }

        $id_proses = $hedr_data->id_proses;
        $id_cmt = $hedr_data->id_cmt;
        $kode_transaksi = $hedr_data->kode_transaksi;
        $id_kategori = $hedr_data->id_kategori;
        $nomor_mesin = $hedr_data->nomor_mesin;

        $bmRows = $this->getDataDetSO($idHeader);

        if (empty($bmRows)) {
            return ['status' => false, 'message' => 'Tidak ada baris trans_barang_masuk_produksi', 'id_header' => $idHeader];
        }

        // LANGKAH 1: resolve identitas tiap baris bm (persis seperti alur
        // simpan normal di trxInsertUpdateRecord), lalu kelompokkan &
        // jumlahkan per identitas.
        $groups = [];
        $errors = [];

        foreach ($bmRows as $bmRow) {
            try {
                $xpr = [];
                $xpr['kode_sales_order'] = $bmRow->kode_sales_order;
                $xpr['kode_ukuran'] = $bmRow->kode_ukuran;
                $clr = explode('~', (string) $bmRow->color);
                $xpr['kode_warna1'] = $clr[0];
                if (!empty($clr[1])) $xpr['kode_warna2'] = $clr[1];
                if (!empty($clr[2])) $xpr['kode_warna3'] = $clr[2];
                if (!empty($clr[3])) $xpr['kode_warna4'] = $clr[3];
                if (!empty($clr[4])) $xpr['kode_warna5'] = $clr[4];
                if (!empty($clr[5])) $xpr['kode_warna6'] = $clr[5];
                if (!empty($clr[6])) $xpr['kode_warna7'] = $clr[6];
                if (!empty($clr[7])) $xpr['kode_warna8'] = $clr[7];

                $tipe = substr($xpr['kode_sales_order'], 0, 3) === 'SPL' ? 1 : (substr($xpr['kode_sales_order'], 0, 3) === 'SOD' ? 2 : null);

                $dtSo = $this->getDataSO($xpr, $tipe);
                $idSo = !empty($dtSo) ? $dtSo->id_sales_order : 0;

                if (empty($dtSo) || empty($dtSo->id_warna_1)) {
                    $errors[] = "id_mp={$bmRow->id_mp}: data SO '{$bmRow->kode_sales_order}' warna '{$bmRow->color}' tidak ditemukan, dilewati";
                    continue;
                }

                $xp = [];
                $xp['id_proses'] = $id_proses;
                $xp['kode_ukuran'] = $bmRow->kode_ukuran;
                $xp['ref_id'] = $idSo;
                $dtProses = $this->getDataWP($xp);

                if (empty($dtProses)) {
                    $errors[] = "id_mp={$bmRow->id_mp}: proses produksi tidak ditemukan (SO {$bmRow->kode_sales_order}, ukuran {$bmRow->kode_ukuran}), dilewati";
                    continue;
                }

                $key = implode('|', [
                    $dtProses->key_kedua,
                    $dtProses->id_ukuran,
                    $id_proses,
                    $id_cmt,
                    $dtSo->id_warna_1,
                    $dtSo->id,
                ]);

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        "kode_transaksi" => $kode_transaksi,
                        "id_produksi" => null, // diisi di bawah, sekali saja
                        "id_walkorder_proses_ukuran" => $dtProses->key_kedua,
                        "id_walkorder" => $dtProses->id_walkorder,
                        "id_wop" => $dtProses->key_kebenearan,
                        "id_proses" => $id_proses,
                        "id_ukuran" => $dtProses->id_ukuran,
                        "id_warna" => $dtSo->id_warna_1,
                        "id_operator" => $id_cmt,
                        "ref_detail_id" => $dtSo->id,
                        "qty" => 0,
                        "berat" => 0,
                        "amount" => 0,
                        "tgl_transaksi" => $bmRow->tgl_transaksi,
                        "print_type" => $bmRow->print_type,
                        "jumlah_baris_bm" => 0,
                    ];
                }

                $qtyRow = (float) $bmRow->qty;
                $beratRow = (float) $bmRow->berat;

                $groups[$key]['qty'] += $qtyRow;
                $groups[$key]['berat'] += $beratRow;
                $groups[$key]['amount'] += (float) $bmRow->amount;
                $groups[$key]['jumlah_baris_bm']++;
                // pakai tanggal transaksi PALING BARU dari kelompok ini
                if (!empty($bmRow->tgl_transaksi) && $bmRow->tgl_transaksi > $groups[$key]['tgl_transaksi']) {
                    $groups[$key]['tgl_transaksi'] = $bmRow->tgl_transaksi;
                }
            } catch (\Exception $e) {
                $errors[] = "id_mp={$bmRow->id_mp}: " . $e->getMessage();
            }
        }

        if (empty($groups)) {
            return [
                'status' => false,
                'message' => 'Tidak ada baris yang berhasil di-resolve',
                'id_header' => $idHeader,
                'errors' => $errors,
            ];
        }

        $ringkasan = [
            'status' => true,
            'id_header' => $idHeader,
            'kode_transaksi' => $kode_transaksi,
            'jumlah_identitas' => count($groups),
            'total_qty_baru' => array_sum(array_column($groups, 'qty')),
            'total_nilai_baru' => array_sum(array_column($groups, 'amount')),
            'errors' => $errors,
            'dry_run' => $dryRun,
        ];

        if ($dryRun) {
            return $ringkasan;
        }

        // LANGKAH 2: tulis ke DB - per identitas, hapus baris lama (kalau
        // ada) lalu insert SATU baris baru dgn total yang sudah dijumlah,
        // sekaligus koreksi qty_prod.
        $this->db->transStart();
        try {
            foreach ($groups as $g) {
                $idProduksi = $this->getDataProduksiByIdWalkorder($g['id_walkorder'])->id;

                $arrDataUkuran = [
                    "kode_transaksi" => $g['kode_transaksi'],
                    "id_produksi" => $idProduksi,
                    "id_walkorder_proses_ukuran" => $g['id_walkorder_proses_ukuran'],
                    "id_proses" => $g['id_proses'],
                    "id_ukuran" => $g['id_ukuran'],
                    "id_warna" => $g['id_warna'],
                    "id_operator" => $g['id_operator'],
                    "tgl_transaksi" => $g['tgl_transaksi'],
                    "qty" => $g['qty'],
                    "berat" => $g['berat'],
                    "harga" => $g['qty'] > 0 ? round($g['amount'] / $g['qty']) : 0,
                    "harga_total" => $g['amount'],
                    "ref_detail_id" => $g['ref_detail_id'],
                    "nomor_mesin" => !empty($nomor_mesin) ? $nomor_mesin : '-',
                    "active" => 1,
                    "flag" => 1,
                    "id_category" => $id_kategori,
                    "created_at" => date("Y-m-d H:i:s"),
                    "print_type" => $g['print_type'],
                ];

                $oldRowsQuery = $this->db->table("trans_produksi_operator")
                    ->where('kode_transaksi', $g['kode_transaksi'])
                    ->where('id_walkorder_proses_ukuran', $g['id_walkorder_proses_ukuran'])
                    ->where('id_ukuran', $g['id_ukuran'])
                    ->where('id_proses', $g['id_proses'])
                    ->where('id_operator', $g['id_operator'])
                    ->where('id_warna', $g['id_warna'])
                    ->where('ref_detail_id', $g['ref_detail_id']);

                $qtyLama = 0;
                foreach ($oldRowsQuery->get()->getResult() as $orow) {
                    $qtyLama += (float) $orow->qty;
                }

                $this->db->table("trans_produksi_operator")
                    ->where('kode_transaksi', $g['kode_transaksi'])
                    ->where('id_walkorder_proses_ukuran', $g['id_walkorder_proses_ukuran'])
                    ->where('id_ukuran', $g['id_ukuran'])
                    ->where('id_proses', $g['id_proses'])
                    ->where('id_operator', $g['id_operator'])
                    ->where('id_warna', $g['id_warna'])
                    ->where('ref_detail_id', $g['ref_detail_id'])
                    ->delete();

                $this->insertRecordGetid("trans_produksi_operator", $arrDataUkuran);

                if ($id_kategori == 12 && !empty($g['id_wop'])) {
                    $qtyBaseline = $this->db->table('trans_walkorder_proses_ukuran')
                        ->select('qty_prod')->where('id', $g['id_wop'])->get()->getRow();
                    $qty_prod_sekarang = !empty($qtyBaseline) ? (float) $qtyBaseline->qty_prod : 0;
                    $qty_prod_baru = max(0, $qty_prod_sekarang - $qtyLama) + $g['qty'];

                    $this->db->table('trans_walkorder_proses_ukuran')
                        ->where('id', $g['id_wop'])
                        ->update(['qty_prod' => $qty_prod_baru]);
                }
            }

            if ($this->db->transStatus() === TRUE) {
                $this->db->transComplete();
                $ringkasan['message'] = 'Berhasil dibangun ulang';
            } else {
                throw new \Exception('Transaction failed');
            }
        } catch (\Exception $e) {
            $this->db->transRollback();
            $ringkasan['status'] = false;
            $ringkasan['message'] = 'Gagal: ' . $e->getMessage();
        }

        return $ringkasan;
    }

    function getDataSO($params, $tipe)
    {
        $prms = '';

        if(!empty($params['kode_warna1'])){
            $prms .= " AND rw1.kode_warna = '" . $params['kode_warna1'] . "'";
        }
        if(!empty($params['kode_warna2'])){
            $prms .= " AND rw2.kode_warna = '" . $params['kode_warna2'] . "'";
        }
        if(!empty($params['kode_warna3'])){
            $prms .= " AND rw3.kode_warna = '" . $params['kode_warna3'] . "'";
        }
        if(!empty($params['kode_warna4'])){
            $prms .= " AND rw4.kode_warna = '" . $params['kode_warna4'] . "'";
        }
        if(!empty($params['kode_warna5'])){
            $prms .= " AND rw5.kode_warna = '" . $params['kode_warna5'] . "'";
        }
        if(!empty($params['kode_warna6'])){
            $prms .= " AND rw6.kode_warna = '" . $params['kode_warna6'] . "'";
        }
        if(!empty($params['kode_warna7'])){
            $prms .= " AND rw7.kode_warna = '" . $params['kode_warna7'] . "'";
        }
        if(!empty($params['kode_warna8'])){
            $prms .= " AND rw8.kode_warna = '" . $params['kode_warna8'] . "'";
        }

        if(!empty($params['kode_ukuran'])){
            $prms .= " AND rk.kode_ukuran = '" . $params['kode_ukuran'] . "'";
        }

        if ($tipe == 1) {

            if(!empty($params['kode_sales_order'])){
                $prms .= " AND so.kode_sample = '" . $params['kode_sales_order'] . "'";
            }

            $builder = $this->db->table('trans_sample_ukuran ou');
            $builder->select("
                ou.id,
                ou.id_sample as id_sales_order,
                ou.id_sample_det as id_sales_order_det,
                COALESCE(b1.id_warna, sod.id_warna_1) AS id_warna_1,
                COALESCE(b2.id_warna, sod.id_warna_2) AS id_warna_2,
                COALESCE(b3.id_warna, sod.id_warna_3) AS id_warna_3,
                COALESCE(b4.id_warna, sod.id_warna_4) AS id_warna_4,
                COALESCE(b5.id_warna, sod.id_warna_5) AS id_warna_5,
                COALESCE(b6.id_warna, sod.id_warna_6) AS id_warna_6,
                COALESCE(b7.id_warna, sod.id_warna_7) AS id_warna_7,
                COALESCE(b8.id_warna, sod.id_warna_8) AS id_warna_8,

                -- Flag sumber warna
                CASE 
                    WHEN sod.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                rw1.keterangan as color1,
                rw2.keterangan as color2,
                rw3.keterangan as color3,
                rw4.keterangan as color4,
                rw5.keterangan as color5,
                rw6.keterangan as color6,
                rw7.keterangan as color7,
                rw8.keterangan as color8
            ");

            $builder->join('trans_sample_det sod', 'sod.id = ou.id_sample_det', 'inner');
            $builder->join('trans_sample so', 'so.id = sod.id_sample', 'inner');
            $builder->join('ref_ukuran rk', 'rk.id = ou.id_ukuran', 'inner');

            // JOIN ref_barang
            $builder->join('ref_barang b1', 'b1.id = sod.id_barang_1', 'left');
            $builder->join('ref_barang b2', 'b2.id = sod.id_barang_2', 'left');
            $builder->join('ref_barang b3', 'b3.id = sod.id_barang_3', 'left');
            $builder->join('ref_barang b4', 'b4.id = sod.id_barang_4', 'left');
            $builder->join('ref_barang b5', 'b5.id = sod.id_barang_5', 'left');
            $builder->join('ref_barang b6', 'b6.id = sod.id_barang_6', 'left');
            $builder->join('ref_barang b7', 'b7.id = sod.id_barang_7', 'left');
            $builder->join('ref_barang b8', 'b8.id = sod.id_barang_8', 'left');

            // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di sod
            $builder->join('ref_warna rw1', 'rw1.id = COALESCE(b1.id_warna, sod.id_warna_1)', 'left');
            $builder->join('ref_warna rw2', 'rw2.id = COALESCE(b2.id_warna, sod.id_warna_2)', 'left');
            $builder->join('ref_warna rw3', 'rw3.id = COALESCE(b3.id_warna, sod.id_warna_3)', 'left');
            $builder->join('ref_warna rw4', 'rw4.id = COALESCE(b4.id_warna, sod.id_warna_4)', 'left');
            $builder->join('ref_warna rw5', 'rw5.id = COALESCE(b5.id_warna, sod.id_warna_5)', 'left');
            $builder->join('ref_warna rw6', 'rw6.id = COALESCE(b6.id_warna, sod.id_warna_6)', 'left');
            $builder->join('ref_warna rw7', 'rw7.id = COALESCE(b7.id_warna, sod.id_warna_7)', 'left');
            $builder->join('ref_warna rw8', 'rw8.id = COALESCE(b8.id_warna, sod.id_warna_8)', 'left');

            $builder->where('1 = 1' . $prms);

        } elseif ($tipe == 2) {

            if(!empty($params['kode_sales_order'])){
                $prms .= " AND so.kode_sales_order = '" . $params['kode_sales_order'] . "'";
            }

            $builder = $this->db->table('trans_sales_order_ukuran ou');
            $builder->select("
                ou.id,
                ou.id_sales_order,
                ou.id_sales_order_det,
                COALESCE(b1.id_warna, sod.id_warna_1) AS id_warna_1,
                COALESCE(b2.id_warna, sod.id_warna_2) AS id_warna_2,
                COALESCE(b3.id_warna, sod.id_warna_3) AS id_warna_3,
                COALESCE(b4.id_warna, sod.id_warna_4) AS id_warna_4,
                COALESCE(b5.id_warna, sod.id_warna_5) AS id_warna_5,
                COALESCE(b6.id_warna, sod.id_warna_6) AS id_warna_6,
                COALESCE(b7.id_warna, sod.id_warna_7) AS id_warna_7,
                COALESCE(b8.id_warna, sod.id_warna_8) AS id_warna_8,

                -- Flag sumber warna
                CASE 
                    WHEN sod.id_barang_1 IS NOT NULL THEN 'via_barang'
                    ELSE 'via_warna'
                END AS sumber_warna,

                rw1.keterangan as color1,
                rw2.keterangan as color2,
                rw3.keterangan as color3,
                rw4.keterangan as color4,
                rw5.keterangan as color5,
                rw6.keterangan as color6,
                rw7.keterangan as color7,
                rw8.keterangan as color8
            ");

            $builder->join('trans_sales_order_det sod', 'sod.id = ou.id_sales_order_det', 'inner');
            $builder->join('trans_sales_order so',      'so.id = sod.id_sales_order',     'inner');
            $builder->join('ref_ukuran rk',             'rk.id = ou.id_ukuran',           'inner');

            // JOIN ref_barang
            $builder->join('ref_barang b1', 'b1.id = sod.id_barang_1', 'left');
            $builder->join('ref_barang b2', 'b2.id = sod.id_barang_2', 'left');
            $builder->join('ref_barang b3', 'b3.id = sod.id_barang_3', 'left');
            $builder->join('ref_barang b4', 'b4.id = sod.id_barang_4', 'left');
            $builder->join('ref_barang b5', 'b5.id = sod.id_barang_5', 'left');
            $builder->join('ref_barang b6', 'b6.id = sod.id_barang_6', 'left');
            $builder->join('ref_barang b7', 'b7.id = sod.id_barang_7', 'left');
            $builder->join('ref_barang b8', 'b8.id = sod.id_barang_8', 'left');

            // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di sod
            $builder->join('ref_warna rw1', 'rw1.id = COALESCE(b1.id_warna, sod.id_warna_1)', 'left');
            $builder->join('ref_warna rw2', 'rw2.id = COALESCE(b2.id_warna, sod.id_warna_2)', 'left');
            $builder->join('ref_warna rw3', 'rw3.id = COALESCE(b3.id_warna, sod.id_warna_3)', 'left');
            $builder->join('ref_warna rw4', 'rw4.id = COALESCE(b4.id_warna, sod.id_warna_4)', 'left');
            $builder->join('ref_warna rw5', 'rw5.id = COALESCE(b5.id_warna, sod.id_warna_5)', 'left');
            $builder->join('ref_warna rw6', 'rw6.id = COALESCE(b6.id_warna, sod.id_warna_6)', 'left');
            $builder->join('ref_warna rw7', 'rw7.id = COALESCE(b7.id_warna, sod.id_warna_7)', 'left');
            $builder->join('ref_warna rw8', 'rw8.id = COALESCE(b8.id_warna, sod.id_warna_8)', 'left');

            $builder->where('1 = 1' . $prms);
        }

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataWP($params)
    {
        $prms = '';
        if(!empty($params['id_proses'])){
            $prms .= " AND tp.id_proses = " . $params['id_proses'];
        }

        if(!empty($params['kode_ukuran'])){
            $prms .= " AND rk.kode_ukuran = '" . $params['kode_ukuran'] . "'";
        }

        if(!empty($params['ref_id'])){
            $prms .= " AND tw.ref_id = " . $params['ref_id'];
        }

        if(!empty($params['kode_sales_order'])){
            $prms .= " AND tw.ref_kode = " . $params['kode_sales_order'];
        }

        $builder = $this->db->table('trans_walkorder_proses_ukuran tpx');
        $builder->select("
            tpx.id as key_kebenearan,
            tp.id as key_kedua,
            tw.id as id_walkorder,
            tp.id_proses,
            tpx.id_walkorder_proses,
            tpx.id_ukuran,
            rk.kode_ukuran,
            tpx.qty,
            tpx.qty_prod
        ");
        $builder->join('trans_walkorder_proses tp', 'tp.id = tpx.id_walkorder_proses', 'inner');
        $builder->join('trans_walkorder tw', 'tw.id = tp.id_walkorder', 'inner');
        $builder->join('ref_ukuran rk', 'rk.id = tpx.id_ukuran', 'inner');
        $builder->where('1 = 1 '. $prms);
        
        $builder->orderBy('tpx.id', 'desc');

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataProduksiByIdWalkorder($idWalkorder)
    {
        $builder = $this->db->table('trans_produksi tp');
        $builder->select("
            tp.id,
            tp.id_walkorder
        ");

        $builder->where('tp.id_walkorder', $idWalkorder);
        $builder->orderBy('tp.id', 'desc');

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function getDataDetSO($idHeader = null, $params = null, $condition = null, $idMP = null, $id_perusahaan = null)
    {
        $builder = $this->db->table("trans_barang_masuk_produksi abx");

        // btm_kg: total Berat (Kg) yang SUDAH tercatat di SEMUA transaksi Barang Masuk
        // (bukan cuma transaksi ini) utk WO yang sama dgn SO baris ini - direlasikan
        // lewat trans_walkorder (ref_id + tipe_id, sama seperti disambiguasi SPL/SOD
        // yang sudah dipakai di trxInsertUpdateRecord). Murni informasi, read-only.
        $builder->select("abx.id as id_mp,abx.qty, abx.kode_sales_order, abx.id_konsumen, abx.style, abx.deskripsi,
                          abx.color,abx.amount, abx.tgl_scan, abx.print_type, cbx.nama as buyer, abx.kode_ukuran, abx.keterangan, abx.id_ref, abx.qty_kirim,
                          abx.nomor_mesin, abx.tgl_transaksi, abx.jam_mesin, abx.nilai_mesin, abx.harga,
                          (abx.qty - abx.qty_kirim) as qty_sisa, abx.berat, abx.total_scanned,
                          COALESCE(abx.qty_qc, abx.qty) AS qty_qc,
                          COALESCE(abx.berat_qc, abx.berat) AS berat_qc,
                          tw.kode_walkorder,
                          (
                              SELECT SUM(tbmp2.berat)
                              FROM trans_barang_masuk_produksi tbmp2
                              INNER JOIN trans_walkorder tw2
                                  ON tw2.ref_id = tbmp2.id_ref
                                 AND tw2.tipe_id = CASE WHEN LEFT(tbmp2.kode_sales_order, 3) = 'SPL' THEN 1 ELSE 2 END
                              WHERE tw2.kode_walkorder = tw.kode_walkorder
                          ) AS btm_kg", false);

        $builder->join("ref_konsumen cbx", "abx.id_konsumen = cbx.id", "inner");
        $builder->join("trans_barang_header head", "abx.id_header = head.id", "inner");
        $builder->join(
            "trans_walkorder tw",
            "tw.ref_id = abx.id_ref AND tw.tipe_id = CASE WHEN LEFT(abx.kode_sales_order, 3) = 'SPL' THEN 1 ELSE 2 END",
            "left"
        );

        if (!empty($idHeader)) {
            $builder->where("abx.id_header", $idHeader);
            if ($condition) {
                $builder->whereNotIn("abx.id", $idMP);
            }
        }

        if (!empty($params['id_mp'])) {
            $builder->where("abx.id", $params['id_mp']);
            $this->_data = $builder->get()->getRow();
            return $this->_data;
        }

        else {
            if (!empty($params['id_proses'])) {
                $builder->where("head.id_proses", $params['id_proses']);
            }
            if (!empty($params['id_perusahaan'])) {
                $builder->where("head.id_perusahaan", $params['id_perusahaan']);
            }
            if (!empty($params['id_cmt'])) {
                $builder->where("head.id_cmt", $params['id_cmt']);
            }
            if (!empty($params['id_ref'])) {
                $builder->where("abx.id_ref", $params['id_ref']);
            }
            if (!empty($params['color'])) {
                $builder->where("abx.color", $params['color']);
            }
            if (!empty($params['kode_ukuran'])) {
                $builder->where("abx.kode_ukuran", $params['kode_ukuran']);
            }
            if (!empty($params['id_konsumen'])) {
                $builder->where("abx.id_konsumen", $params['id_konsumen']);
            }
            if (!empty($params['current_id_mp'])) {
            
                if (is_array($params['current_id_mp'])) {
                    $builder->whereNotIn("abx.id", $params['current_id_mp']);
                }
                else {
                    $builder->where("abx.id <>", $params['current_id_mp']);
                }
            }
        }

        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }

    /**
     * Ambil harga dari transaksi Barang Masuk terakhir dengan kombinasi
     * Proses + CMT (header) dan Style (detail) yang sama.
     * Khusus dipakai untuk validasi auto-harga perusahaan "Citra Knitt".
     */
    function getLastHargaByProsesCmtStyle($id_proses, $id_cmt, $style, $id_perusahaan = null)
    {
        if (empty($id_proses) || empty($id_cmt) || empty($style)) {
            return null;
        }

        $builder = $this->db->table("trans_barang_masuk_produksi abx");
        $builder->select("abx.id as id_mp, abx.harga, abx.style, abx.tgl_transaksi, abx.created_at, head.id_proses, head.id_cmt, head.id_perusahaan");
        $builder->join("trans_barang_header head", "abx.id_header = head.id", "inner");
        $builder->where("head.id_proses", $id_proses);
        $builder->where("head.id_cmt", $id_cmt);
        $builder->where("abx.style", $style);
        $builder->where("head.active", 1);
        $builder->where("abx.harga >", 0);

        if (!empty($id_perusahaan)) {
            $builder->where("head.id_perusahaan", $id_perusahaan);
        }

        $builder->orderBy("abx.tgl_transaksi", "DESC");
        $builder->orderBy("abx.id", "DESC");
        $builder->limit(1);

        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }
}
