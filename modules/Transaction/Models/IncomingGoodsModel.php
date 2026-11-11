<?php

namespace Modules\Transaction\Models;

class IncomingGoodsModel extends \App\Models\PrModel
{

    protected $table = "trans_barang";
    protected $kd = "1";
    protected $tblGudang = "ref_gudang";
    protected $tblBarang = "ref_barang";
    protected $tblKategori = "ref_kategori_persediaan";
    protected $tblSatuan = "ref_satuan";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblGudang . " abx", "uk.id_gudang_tujuan = abx.id", "left");
        $builder->join($this->tblGudang . " bbx", "uk.id_gudang_asal = bbx.id", "left");
        $builder->join($this->tblBarang . " cbx", "uk.id_barang = cbx.id", "inner");
        $builder->join($this->tblKategori . " dbx", "uk.id_kategori = dbx.id", "inner");
        $builder->join($this->tblSatuan . " ebx", "cbx.id_satuan = ebx.id", "inner");
        $builder->select("uk.id, uk.id_kategori, cbx.kode_barang, cbx.nama_barang,ebx.nama_satuan, uk.keterangan, bbx.nama_gudang as gudang_asal, abx.nama_gudang as gudang_tujuan, uk.jumlah, uk.tanggal, uk.nama, uk.kode_transaksi, dbx.kategori");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            $builder->where('uk.jenis_transaksi', $this->kd);
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(cbx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('id');
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

        $builder->select("count(1) as _cnt");
        $builder->where('uk.jenis_transaksi', $this->kd);
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(cbx.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.kode_transaksi) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_gudang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateKodePersediaan()
    {
        $kd = "BTM";
        $builder = $this->db->table($this->table . ' a');
        $builder->select("LEFT(kode_transaksi, 7) AS tgl, RIGHT( kode_transaksi, 4 ) AS kode ");

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

        // hasilnya SOD24100001 dst.
        return $kodejadi;
    }

    function getRefKategoriPersedian()
    {
        $builder = $this->db->table("ref_kategori_persediaan");
        $builder->select("*");
        $builder->where('jenis', $this->kd);
        $builder->where('aktif', 1);
        $this->_data = $builder->get()->getResultArray();
        return $this->_data;
    }

    function getLastStokBarang($idBarang, $idGudang)
    {
        $builder = $this->db->table("trans_persediaan");
        $builder->select("stok");
        $builder->where('id_barang', $idBarang);
        $builder->where('id_gudang', $idGudang);
        $this->_data = $builder->get()->getRow();
        return $this->_data;
        // if ($builder->get()) {
        //     $this->_data = $builder->get()->getRow();
        //     return $this->_data;
        // } else {
        //     return null;
        // }
    }

    function getLastStokBarangBalances($idBarang, $idGudang, $lotId, $pack_id = null)
    {
        $builder = $this->db->table("trans_barang_balances");
        $builder->select("*");
        $builder->where('id_barang', $idBarang);
        $builder->where('id_gudang', $idGudang);
        if ($lotId === null || $lotId === '') {
            $builder->where('lot_id IS NULL', null, false);
        } else {
            $builder->where('lot_id', $lotId);
        }
        if (!empty($pack_id)) {
            $builder->where('pack_id', $pack_id);
        }
        $builder->where('tanggal', date("Y-m-d"));
        $builder->orderBy('id', 'DESC');
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    /**
     * Hitung saldo akhir live untuk barang non-lot ('-' / kosong) berdasarkan
     * snapshot trans_barang_history bulan sebelumnya ditambah mutasi trans_barang
     * terkini (termasuk baris yang baru saja di-insert dalam transaksi aktif).
     */
    public function getLiveNonLotBalance($idBarang, $idGudang, $packId = null, $trxDate = null)
    {
        if (empty($idBarang) || empty($idGudang)) {
            return 0.0;
        }

        $curYM = (int) date('Ym');
        $trxYM = !empty($trxDate) ? (int) date('Ym', strtotime($trxDate)) : $curYM;
        $minYM = min($curYM, $trxYM);
        $maxYM = max($curYM, $trxYM);

        $calcLive = function ($packFilter, $allLots = false) use ($idBarang, $idGudang, $minYM, $maxYM) {
            $lotHistCond = $allLots ? '' : "AND COALESCE(NULLIF(TRIM(lot_no), ''), '-') = '-'";
            $lotTrxCond  = $allLots ? '' : "AND COALESCE(NULLIF(TRIM(lot_no), ''), '-') = '-'";

            $baseYM = 202605;
            $baseSaldo = 0.0;

            if ($packFilter === 'all') {
                // Hitung per-pack secara akurat jika semua pack digabung agar batas ym per-pack tidak miring
                $packRows = $this->db->query("
                    SELECT DISTINCT ON (COALESCE(tbh.pack_id, 0), COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-'))
                        tbh.pack_id,
                        COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-') AS lot_key,
                        (tbh.year * 100 + tbh.month) AS ym,
                        tbh.jumlah
                    FROM trans_barang_history tbh
                    WHERE tbh.id_barang = ?
                      AND tbh.id_gudang = ?
                      {$lotHistCond}
                      AND (tbh.year * 100 + tbh.month) >= 202606
                      AND (tbh.year * 100 + tbh.month) < ?
                    ORDER BY COALESCE(tbh.pack_id, 0), COALESCE(NULLIF(TRIM(tbh.lot_no), ''), '-'), tbh.year DESC, tbh.month DESC, tbh.id DESC
                ", [(int) $idBarang, (int) $idGudang, $minYM])->getResult();

                if (empty($packRows)) {
                    $mutRow = $this->db->query("
                        SELECT
                            COALESCE(SUM(CASE WHEN id_gudang_tujuan = ? THEN jumlah ELSE 0 END), 0) AS masuk,
                            COALESCE(SUM(CASE WHEN id_gudang_asal = ? THEN jumlah ELSE 0 END), 0) AS keluar
                        FROM trans_barang
                        WHERE id_barang = ?
                          {$lotTrxCond}
                          AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) >= 202606
                          AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) <= ?
                          AND COALESCE(active, 1) = 1
                    ", [(int) $idGudang, (int) $idGudang, (int) $idBarang, $maxYM])->getRow();
                    return (float) ($mutRow->masuk ?? 0) - (float) ($mutRow->keluar ?? 0);
                }

                $sumTotal = 0.0;
                $coveredKeys = [];
                foreach ($packRows as $pr) {
                    $pClause = ($pr->pack_id !== null) ? "= " . (int) $pr->pack_id : "IS NULL";
                    $lotEq = $this->db->escape($pr->lot_key);
                    $coveredKeys[] = ($pr->pack_id ?? 'NULL') . '|' . $pr->lot_key;
                    $mutPack = $this->db->query("
                        SELECT
                            COALESCE(SUM(CASE WHEN id_gudang_tujuan = ? THEN jumlah ELSE 0 END), 0) AS masuk,
                            COALESCE(SUM(CASE WHEN id_gudang_asal = ? THEN jumlah ELSE 0 END), 0) AS keluar
                        FROM trans_barang
                        WHERE id_barang = ?
                          AND pack_id {$pClause}
                          AND COALESCE(NULLIF(TRIM(lot_no), ''), '-') = {$lotEq}
                          AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) > ?
                          AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) <= ?
                          AND COALESCE(active, 1) = 1
                    ", [(int) $idGudang, (int) $idGudang, (int) $idBarang, (int) $pr->ym, $maxYM])->getRow();
                    $sumTotal += (float) $pr->jumlah + (float) ($mutPack->masuk ?? 0) - (float) ($mutPack->keluar ?? 0);
                }

                // Tambahkan mutasi pack/lot baru yang belum punya snapshot history < minYM
                $extraRows = $this->db->query("
                    SELECT
                        pack_id,
                        COALESCE(NULLIF(TRIM(lot_no), ''), '-') AS lot_key,
                        COALESCE(SUM(CASE WHEN id_gudang_tujuan = ? THEN jumlah ELSE 0 END), 0) -
                        COALESCE(SUM(CASE WHEN id_gudang_asal = ? THEN jumlah ELSE 0 END), 0) AS net_qty
                    FROM trans_barang
                    WHERE id_barang = ?
                      {$lotTrxCond}
                      AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) >= 202606
                      AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) <= ?
                      AND COALESCE(active, 1) = 1
                    GROUP BY pack_id, COALESCE(NULLIF(TRIM(lot_no), ''), '-')
                ", [(int) $idGudang, (int) $idGudang, (int) $idBarang, $maxYM])->getResult();

                $coveredSet = array_flip($coveredKeys);
                foreach ($extraRows as $er) {
                    $k = ($er->pack_id ?? 'NULL') . '|' . $er->lot_key;
                    if (!isset($coveredSet[$k])) {
                        $sumTotal += (float) $er->net_qty;
                    }
                }
                return $sumTotal;
            }

            $packClause = !empty($packFilter) ? "= " . (int) $packFilter : "IS NULL";
            $prevRow = $this->db->query("
                SELECT (tbh.year * 100 + tbh.month) AS ym, tbh.jumlah
                FROM trans_barang_history tbh
                WHERE tbh.id_barang = ?
                  AND tbh.id_gudang = ?
                  AND tbh.pack_id {$packClause}
                  {$lotHistCond}
                  AND (tbh.year * 100 + tbh.month) >= 202606
                  AND (tbh.year * 100 + tbh.month) < ?
                ORDER BY tbh.year DESC, tbh.month DESC, tbh.id DESC
                LIMIT 1
            ", [(int) $idBarang, (int) $idGudang, $minYM])->getRow();

            if (!empty($prevRow)) {
                $baseYM = (int) $prevRow->ym;
                $baseSaldo = (float) $prevRow->jumlah;
            }

            $mutRow = $this->db->query("
                SELECT
                    COALESCE(SUM(CASE WHEN id_gudang_tujuan = ? THEN jumlah ELSE 0 END), 0) AS masuk,
                    COALESCE(SUM(CASE WHEN id_gudang_asal = ? THEN jumlah ELSE 0 END), 0) AS keluar
                FROM trans_barang
                WHERE id_barang = ?
                  AND pack_id {$packClause}
                  {$lotTrxCond}
                  AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) > ?
                  AND (EXTRACT(YEAR FROM tanggal)::int * 100 + EXTRACT(MONTH FROM tanggal)::int) <= ?
                  AND COALESCE(active, 1) = 1
            ", [(int) $idGudang, (int) $idGudang, (int) $idBarang, $baseYM, $maxYM])->getRow();

            return $baseSaldo + (float) ($mutRow->masuk ?? 0) - (float) ($mutRow->keluar ?? 0);
        };

        $saldo = $calcLive($packId, false);
        if ($saldo < 0) {
            $allNonLot = $calcLive('all', false);
            if ($allNonLot > $saldo) {
                $saldo = $allNonLot;
            }
        }
        if ($saldo < 0) {
            $allItem = $calcLive('all', true);
            if ($allItem > $saldo) {
                $saldo = $allItem;
            }
        }

        return round($saldo, 2);
    }
    function getLotNo($lotNo = null, $idBarang = null, $idLotNo = null, $idGudang = null, $packId = null)
    {
        if (empty($lotNo) && empty($idBarang) && empty($idLotNo) && empty($idGudang)) {
            return null;
        }
        $builder = $this->db->table("trans_lots");
        $builder->select("*");
        if (!empty($lotNo)) {
            $trimmedLot = trim((string)$lotNo);
            $builder->where('TRIM(lot_no)', $trimmedLot);
        }
        if (!empty($idBarang)) {
            $builder->where('id_barang', $idBarang);
        }
        if (!empty($idLotNo)) {
            $builder->where('id', $idLotNo);
        }
        if (!empty($idGudang)) {
            $builder->where('id_gudang', $idGudang);
        }
        // Ditambahkan supaya pengecekan "lot sudah dipakai barang lain" (lihat
        // ReceiveItem::checkLotsNo()) bisa disaring per pack juga - lot_no yang
        // sama boleh dipakai lagi kalau pack-nya beda, jadi tidak salah tangkap
        // baris pack lain yang kebetulan lot_no-nya sama.
        if (!empty($packId)) {
            $builder->where('pack_id', $packId);
        }
        $builder->where('active', 1);
        $builder->orderBy('qty', 'DESC');
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }

    function trxInsertUpdateRecord($data)
    {
        $this->db->transStart();
        try {


            $this->insertRecordGetid("trans_barang", $data);
            $arrPersediaan = [
                "id_barang" => $data['id_barang'],
                "id_gudang" => $data['id_gudang_tujuan'],
                "stok" => $data['stok'],
                "created_at" =>  $data['created_at'],
                "created_by" =>  $data['created_by'],
                "active" => 1,
            ];
            $arrParam =  [
                "id_barang" => $data['id_barang'],
                "id_gudang" => $data['id_gudang_tujuan'],
            ];
            $resData = $this->getLastStokBarang($data['id_barang'], $data['id_gudang_tujuan']);
            if (!empty($resData)) {
                $this->updateRecords("trans_persediaan", $arrPersediaan, $arrParam);
            } else {
                $this->insertRecordGetid("trans_persediaan", $arrPersediaan);
            }

            if ($data['id_kategori'] == 1) {
                $data['tipe'] = 2;
                $data['id_kategori'] = 4;
                $data['jenis_transaksi'] = 2;
                $this->insertRecordGetid("trans_barang", $data);
                $resDataKeluar = $this->getLastStokBarang($data['id_barang'], $data['id_gudang_asal']);
                $arrPersediaanKeluar = [
                    "id_barang" => $data['id_barang'],
                    "id_gudang" => $data['id_gudang_asal'],
                    "stok" => $data['stok'],
                    "created_at" =>  $data['created_at'],
                    "created_by" =>  $data['created_by'],
                    "active" => 1,
                ];
                $arrParamKeluar =  [
                    "id_barang" => $data['id_barang'],
                    "id_gudang" => $data['id_gudang_asal'],
                ];
                $resDataKeluar = $this->getLastStokBarang($data['id_barang'], $data['id_gudang_asal']);
                if (!empty($resDataKeluar)) {
                    $this->updateRecords("trans_persediaan", $arrPersediaanKeluar, $arrParamKeluar);
                } else {
                    $this->insertRecordGetid("trans_persediaan", $arrPersediaanKeluar);
                }
            }

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
}
