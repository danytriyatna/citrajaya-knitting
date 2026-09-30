<?php

namespace Modules\Transaction\Models;

class BarangKeluarModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_header";
    protected $tblDet = "trans_barang_detail";
    protected $kd = "2";
    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblVendor = "ref_vendor";
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
        $builder->join($this->tblVendor . " ebx", "uk.id_vendor = ebx.id", "left");
        $builder->join("trans_walkorder tw", "uk.no_ref_wo = tw.kode_walkorder", "left");
        $builder->select("uk.no_ref_trf, uk.no_ref_wo, uk.id, uk.id_buyer,uk.status, uk.id_kategori, uk.keterangan, 
                        abx.nama_gudang,  uk.tanggal, ebx.nama , uk.kode_transaksi, dbx.kategori, uk.id_gudang, tw.ref_kode as kode_so");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            $builder->where('uk.jenis_transaksi', $this->kd);
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
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
        $builder->join($this->tblVendor . " ebx", "uk.id_vendor = ebx.id", "left");
        $builder->select("count(1) as _cnt");
        $builder->where('uk.jenis_transaksi', $this->kd);
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(ebx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataBarang($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->tblTrxLots . " uk");
        $builder->join($this->tblBarang . " abx", "uk.id_barang = abx.id", "left");
        $builder->join($this->tblSatuan . " dbx", "abx.id_satuan = dbx.id", "inner");
        $builder->select("uk.id_barang, dbx.nama_satuan, abx.nama_barang, abx.kode_barang, uk.id as lot_id, uk.lot_no,uk.qty");
        if (!empty($params['id_gudang'])) {
            $builder->where('uk.id_gudang', $params['id_gudang']);
        }
        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->Where('LOWER(abx.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.lot_no) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('uk.id');
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

    function getDataBarangCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->tblTrxLots . " uk");
        $builder->join($this->tblBarang . " abx", "uk.id_barang = abx.id", "left");
        $builder->join($this->tblSatuan . " dbx", "abx.id_satuan = dbx.id", "inner");
        $builder->select("count(1) as _cnt");
        $builder->where('uk.active = 1');
        if (!empty($params['id_gudang'])) {
            $builder->where('uk.id_gudang', $params['id_gudang']);
        }
        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(abx.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.lot_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }


    function generateKodePersediaan($type = null)
    {
        $kd = "BTK";
        $builder = $this->db->table($this->table . ' a');
        if (!empty($type)) {
            $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 5 ) AS kode ");
        }
        else {
            $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");
        }
        $builder->where("LEFT(kode_transaksi, 3) = '$kd'");
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

        // Anti-collision verification
        while ($this->db->table($this->table)->where('kode_transaksi', $kodejadi)->countAllResults() > 0) {
            $kode++;
            $kodemax = str_pad($kode, 5, "0", STR_PAD_LEFT);
            $kodejadi = $kd . date('y') . date('m') . $kodemax;
        }

        // hasilnya SOD24100001 dst.
        return $kodejadi;
    }

    function trxInsertUpdateRecord($data, $id, $detail)
    {
        $this->db->transStart();
        try {
            $nama = $data['nama'];
            unset($data['nama']);

            if (!empty($id)) {
                $arrDelete =  [
                    "id_header" => $id,
                ];
                $this->deleteRecordMultipleColumn($this->tblDet, $arrDelete);
                $getCurrent = $this->getData($id);
                $data['kode_transaksi'] = $getCurrent->kode_transaksi;
                $arrParam =  [
                    "id" => $id,
                ];
                $this->updateRecords($this->table, $data, $arrParam);
            } else {
                $data['kode_transaksi'] = $this->generateKodePersediaan("BTK");
                $id = $this->insertRecordGetid($this->table,  $data);
            }

            foreach ($detail as $key => $rowData) {
                if ($rowData['id_barang'] != "") {
                    // $idBarang = decrypt($rowData['id_barang']);
                    $idBarang = $rowData['id_barang'];
                }

                // Kunci harga: pastikan harga setiap barang keluar selalu mengikuti harga resmi
                // perolehan lot (trans_receive_detail) atau ref_barang, tidak boleh dimanipulasi user.
                $authenticPrice = null;
                $lotNoVal = trim((string)($rowData['lot_no'] ?? ''));
                if (!empty($lotNoVal) && $lotNoVal !== '-') {
                    $rowRec = $this->db->table('trans_receive_detail trd')
                        ->select('trd.price')
                        ->join('trans_receive_header trh', 'trh.id = trd.id_header')
                        ->where('trd.id_barang', $idBarang)
                        ->where('trd.lot_no', $lotNoVal)
                        ->where('trd.price >', 0)
                        ->where('trd.active', 1)
                        ->orderBy('trh.id', 'DESC')
                        ->get()->getRow();
                    if (!empty($rowRec) && !empty($rowRec->price) && (float)$rowRec->price > 0) {
                        $authenticPrice = (float)$rowRec->price;
                    }
                }
                if (empty($authenticPrice) && !empty($idBarang)) {
                    $refB = $this->db->table('ref_barang')
                        ->select('harga_satuan')
                        ->where('id', $idBarang)
                        ->get()->getRow();
                    if (!empty($refB) && !empty($refB->harga_satuan) && (float)$refB->harga_satuan > 0) {
                        $authenticPrice = (float)$refB->harga_satuan;
                    }
                }
                if (!empty($authenticPrice) && $authenticPrice > 0) {
                    $rowData['price'] = $authenticPrice;
                }

                $dataDetail = [
                    "id_barang" => $idBarang,
                    "lot_no" => !empty($rowData['lot_no']) ? $rowData['lot_no'] : null,
                    "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                    "lot_id" => !empty($rowData['lot_id']) ? $rowData['lot_id'] : 0,
                    "id_header" => $id,
                    "qty" => $rowData['qty'],
                    "price" => !empty($rowData['price']) ? $rowData['price'] : null,
                    // Snapshot saldo No. WO (hanya terisi utk Tipe "Pemakaian Bahan Baku atau
                    // Barang"), dibekukan di sini supaya cetakan transaksi ini konsisten walau
                    // ada transaksi Barang Keluar lain utk WO yang sama dibuat sesudahnya.
                    // Pakai pengecekan '' eksplisit (bukan isset() saja) - Tabulator selalu
                    // mengirim key ini walau kosong, jadi isset() tetap true untuk string
                    // kosong dan itu bikin error "invalid input syntax for type numeric"
                    // saat insert ke Postgres pada Tipe selain "Pemakaian Bahan Baku atau Barang".
                    "qty_terima" => (isset($rowData['qty_terima']) && $rowData['qty_terima'] !== '') ? $rowData['qty_terima'] : null,
                    "qty_retur" => (isset($rowData['qty_retur']) && $rowData['qty_retur'] !== '') ? $rowData['qty_retur'] : null,
                    "qty_pemakaian_sebelumnya" => (isset($rowData['qty_pemakaian_sebelumnya']) && $rowData['qty_pemakaian_sebelumnya'] !== '') ? $rowData['qty_pemakaian_sebelumnya'] : null,
                    "qty_sisa" => (isset($rowData['qty_sisa']) && $rowData['qty_sisa'] !== '') ? $rowData['qty_sisa'] : null,
                ];
                $this->insertRecordGetid($this->tblDet, $dataDetail);

                // Hapus dulu baris trans_barang lama utk dokumen ini (sekali saja, di
                // iterasi pertama) sebelum insert ulang - supaya edit berulang pada
                // dokumen yang sudah Approved tidak menumpuk baris ledger duplikat.
                if ($data['status'] == 1 && $key === 0 && !empty($data['kode_transaksi'])) {
                    // CATATAN (2026-08-08): sebelum baris trans_barang lama dihapus, qty
                    // yang PERNAH dikurangi dari trans_lots akibat dokumen ini (kalau
                    // dokumen ini sudah pernah disimpan/di-approve sebelumnya lalu diedit
                    // ulang) harus dikembalikan dulu - baris trans_barang lama adalah
                    // catatan persis berapa & dari lot mana. Tanpa ini, trans_lots akan
                    // terus berkurang dobel setiap kali dokumen Barang Keluar yang sama
                    // diedit/disimpan ulang - pola bug sama seperti yang sudah diperbaiki
                    // di Item Transfer, Purchasing Terima Barang & Delivery Order.
                    $oldTransBarangBk = $this->db->table($this->tblTrxBarang)
                        ->select('lot_id, SUM(jumlah) as jumlah')
                        ->where('kode_transaksi', $data['kode_transaksi'])
                        ->where('COALESCE(active, 1)', 1)
                        ->groupBy('lot_id')
                        ->get()->getResult();

                    foreach ($oldTransBarangBk as $otb) {
                        if (empty($otb->lot_id)) continue;
                        $this->db->table($this->tblTrxLots)
                            ->where('id', $otb->lot_id)
                            ->set('qty', 'qty + ' . (float) $otb->jumlah, false)
                            ->update();
                    }

                    // Soft-delete baris trans_barang lama (menjaga audit trail ledger)
                    $this->db->table($this->tblTrxBarang)
                        ->where('kode_transaksi', $data['kode_transaksi'])
                        ->where('COALESCE(active, 1)', 1)
                        ->set('active', 0)
                        ->set('deleted_at', date('Y-m-d H:i:s'))
                        ->set('deleted_by', $data['updated_by'] ?? ($data['created_by'] ?? null))
                        ->update();
                }

                if ($data['status'] == 1) {

                    $mBarangMasuk = new IncomingGoodsModel();
                    $arrParam =  [
                        "id_barang" => $idBarang,
                        "id_gudang" => $data['id_gudang'],
                    ];

                    // "-" dipakai di berbagai tempat sbg penanda "tidak ada Lot No."
                    // (bukan lot_no sungguhan), jadi harus diperlakukan sama seperti kosong.
                    $lotNoValue = trim((string) ($rowData['lot_no'] ?? ''));
                    $hasLotNo = $lotNoValue !== '' && $lotNoValue !== '-';

                    $qtyKeluar = (float)($rowData['qty'] ?? 0);

                    $idLots = null;
                    $resLotNo = $hasLotNo ? $mBarangMasuk->getLotNo($lotNoValue, $idBarang, null, $data['id_gudang']) : null;
                    // $resLotNo = $mBarangMasuk->getLotNo($rowData['lot_no'], $idBarang, $data['id_gudang']);
                    if (!empty($resLotNo)) {
                        $idLots = $resLotNo->id;
                        $sisaLot = (float)$resLotNo->qty - $qtyKeluar;
                        // Pengecekan ketersediaan stok dilepas sesuai instruksi operasional pabrik,
                        // saldo lot tetap dipotong agar merefleksikan pemakaian riil tanpa memblokir approve.
                        $this->updateRecords($this->tblTrxLots, array("qty" => $sisaLot), array("id" => $idLots));
                    } elseif ($hasLotNo) {
                        // Jika qty = 0 (baris catatan/kosong), lewati tanpa melempar error
                        if ($qtyKeluar > 0) {
                            // Jika Lot No diisi tapi belum tercatat di gudang ini, buatkan lot baru
                            // dengan saldo negatif agar id_lots tetap valid dan ledger kartu stok utuh
                            $newLotData = [
                                "id_barang"   => $idBarang,
                                "lot_no"      => $lotNoValue,
                                "id_gudang"   => $data['id_gudang'],
                                "qty"         => -$qtyKeluar,
                                "tanggal"     => date("Y-m-d"),
                                "active"      => 1,
                                "created_at"  => date("Y-m-d H:i:s"),
                                "pack_id"     => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null
                            ];
                            $idLots = $this->insertRecordGetid($this->tblTrxLots, $newLotData);
                        }
                    }

                    $resData = $mBarangMasuk->getLastStokBarangBalances($idBarang, $data['id_gudang'], $idLots, !empty($rowData['pack_id']) ? $rowData['pack_id'] : null);

                    // $stokAwal = !empty($resData) ? $resData->stok : 0;
                    $dataBarang = [
                        "id_barang" => $idBarang,
                        "jenis_transaksi" => 2,
                        "jumlah" =>  $rowData['qty'],
                        "tanggal" => date("Y-m-d H:i:s"),
                        "id_gudang_asal" =>  !empty($data['id_gudang']) ? $data['id_gudang'] : null,
                        "nama" => $nama,
                        "id_kategori" => $data['id_kategori'],
                        "keterangan" => "Barang Keluar Dari Outgoing Goods",
                        "active" => 1,
                        "tipe" => 1,
                        "created_at" =>  date("Y-m-d H:i:s"),
                        "lot_id" => $idLots,
                        "lot_no" => $rowData['lot_no'],
                        "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
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
                        "pack_id" => !empty($rowData['pack_id']) ? $rowData['pack_id'] : null,
                        "saldo_awal" => 0,
                        "saldo_akhir" => $rowData['qty'],
                        "active" => 1,
                        "created_at" =>  date("Y-m-d H:i:s"),
                    ];
                    if (!empty($resData)) {
                        $stock = !empty($rowData['qty_exist']) ? $rowData['qty_exist'] - $rowData['qty'] : 0;
                        $this->updateRecords($this->tblTrxBalances, array("saldo_akhir" => $stock), array("id" => $resData->id));
                    } else {
                        $this->insertRecordGetid($this->tblTrxBalances, $arrStockBalances);
                    }
                }
            }
            $this->db->transComplete();

            if ($this->db->transStatus() === TRUE) {
                return true;
            } else {

                throw new \Exception("Transaction failed");
            }
        } catch (\Throwable $e) {
            // \Throwable (bukan cuma \Exception) supaya fatal error/TypeError PHP
            // (mis. operasi angka pada data yang ternyata bukan angka) juga ke-rollback
            // dan pesannya bisa diteruskan ke user, bukan jadi 500 kosong.
            $this->db->transRollback();
            throw $e;
        }
    }

    function get_export($from_date = null, $to_date = null){
        $builder = $this->db->table("trans_barang_detail tbd");
        $builder->join("trans_barang_header tbh", "tbh.id = CAST(tbd.id_header AS INTEGER)", "inner");
        $builder->join("ref_kategori_persediaan rkp", "rkp.id = tbh.id_kategori", "inner");
        $builder->join("ref_gudang rg", "rg.id = tbh.id_gudang", "inner");
        $builder->join("ref_barang rb", "rb.id = tbd.id_barang", "inner");
        $builder->join("ref_satuan rs", "rs.id = rb.id_satuan", "inner");

        $builder->select("tbh.kode_transaksi, tbh.tanggal, rkp.kategori, rg.nama_gudang, 
                            tbh.no_ref_trf, tbh.no_ref_wo, rb.kode_barang, rb.nama_barang, 
                            tbd.lot_no, tbd.qty, rs.nama_satuan, tbd.price");

        $builder->where('tbh.active', 1);
        $builder->where('tbh.jenis_transaksi', 2);
        // Sebelumnya ada filter id_kategori = 5 ("Pemakaian Bahan Baku atau
        // Barang") di sini - jadi export Excel selalu cuma nampilin tipe itu
        // saja, padahal list on-screen (BarangKeluarModel::getData()) tidak
        // membatasi kategori sama sekali. Dihapus supaya export ikut semua
        // tipe Barang Keluar sesuai rentang tanggal yang dipilih, konsisten
        // dgn list-nya.

        $builder->where("tbh.tanggal BETWEEN'$from_date' AND '$to_date'");
        $builder->orderBy("tbh.tanggal", "asc");
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    /**
     * Membatalkan Approve dokumen Barang Keluar (BTK).
     * Membalikkan semua efek transaksi:
     *  1. Mengembalikan saldo qty ke trans_lots (qty = qty + jumlah)
     *  2. Menyinkronkan saldo akhir di trans_barang_balances hari ini
     *  3. Menghapus baris mutasi trans_barang milik dokumen ini
     *  4. Mengembalikan status dokumen ke Draft (status = 0)
     */
    function cancelApprove($id, $updatedBy = null)
    {
        $this->db->transStart();
        try {
            $header = $this->getData($id);
            if (empty($header)) {
                throw new \Exception("Data barang keluar tidak ditemukan.");
            }
            if ((int) $header->status !== 1) {
                throw new \Exception("Dokumen ini belum di-Approve, tidak bisa dibatalkan.");
            }

            // 1) & 2) Balikkan trans_lots + samakan snapshot trans_barang_balances hari ini,
            // berdasarkan baris trans_barang yang tercatat utk kode_transaksi ini.
            // Barang Keluar hanya memiliki sisi "keluar" (jenis_transaksi = 2, dari id_gudang_asal).
            $trxRows = $this->db->table($this->tblTrxBarang)
                ->select('id_barang, lot_id, pack_id, id_gudang_asal, SUM(jumlah) as jumlah')
                ->where('kode_transaksi', $header->kode_transaksi)
                ->groupBy('id_barang, lot_id, pack_id, id_gudang_asal')
                ->get()->getResult();

            foreach ($trxRows as $row) {
                if (empty($row->lot_id)) continue;

                $this->db->table($this->tblTrxLots)
                    ->where('id', $row->lot_id)
                    ->set('qty', 'qty + ' . (float) $row->jumlah, false)
                    ->update();

                $lotBaru = $this->db->table($this->tblTrxLots)->where('id', $row->lot_id)->get()->getRow();
                if (!empty($lotBaru)) {
                    $this->db->table($this->tblTrxBalances)
                        ->where('id_barang', $row->id_barang)
                        ->where('id_gudang', $row->id_gudang_asal)
                        ->where('lot_id', $row->lot_id)
                        ->where('pack_id', $row->pack_id)
                        ->where('tanggal', date('Y-m-d'))
                        ->set('saldo_akhir', $lotBaru->qty)
                        ->update();
                }
            }

            // 3) Soft-delete baris trans_barang milik dokumen ini (menjaga audit trail ledger)
            $this->db->table($this->tblTrxBarang)
                ->where('kode_transaksi', $header->kode_transaksi)
                ->where('COALESCE(active, 1)', 1)
                ->set('active', 0)
                ->set('deleted_at', date('Y-m-d H:i:s'))
                ->set('deleted_by', $updatedBy)
                ->update();

            // 4) Kembalikan status dokumen ke Draft
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
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
