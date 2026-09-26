<?php

namespace Modules\Laporan\Models;

class LaporanStockCardModel extends \App\Models\PrModel
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

    function getTahun()
    {
        $builder = $this->db->table("ref_tahun");
        $builder->select("*");
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }
    function getBulan()
    {
        $builder = $this->db->table("ref_bulan");
        $builder->select("*");
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getLaporanPersediaan($idBarang = null, $idGudang = null, $year = null, $month = null)
    {
        // Semua filter di-bind sebagai parameter (bukan interpolasi string) untuk
        // menghindari SQL injection. Gudang/Bulan/Tahun bersifat opsional (mendukung
        // "Semua Gudang/Bulan/Tahun" di UI); Barang tetap wajib karena Stock Card
        // pada dasarnya laporan per-item.
        $params = [];
        $params['id_barang'] = $idBarang;

        // Filter tanggal: pakai range (sargable, bisa pakai index btree biasa)
        // kalau tahun/bulan diisi, bukan EXTRACT(...) yang tidak sargable.
        $dateFilter = "";
        if (!empty($year) && !empty($month)) {
            $start = sprintf('%04d-%02d-01', $year, $month);
            $endMonth = ((int) $month) == 12 ? 1 : ((int) $month) + 1;
            $endYear = ((int) $month) == 12 ? ((int) $year) + 1 : (int) $year;
            $params['tgl_start'] = $start;
            $params['tgl_end'] = sprintf('%04d-%02d-01', $endYear, $endMonth);
            $dateFilter = " AND abx.tanggal >= :tgl_start: AND abx.tanggal < :tgl_end: ";
        } elseif (!empty($year)) {
            $params['tgl_start'] = sprintf('%04d-01-01', $year);
            $params['tgl_end'] = sprintf('%04d-01-01', ((int) $year) + 1);
            $dateFilter = " AND abx.tanggal >= :tgl_start: AND abx.tanggal < :tgl_end: ";
        } elseif (!empty($month)) {
            $params['bulan'] = $month;
            $dateFilter = " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan: ";
        }

        $gudangFilterAsal = "";
        $gudangFilterTujuan = "";
        if (!empty($idGudang)) {
            $params['id_gudang'] = $idGudang;
            $gudangFilterAsal = " AND abx.id_gudang_asal = :id_gudang: ";
            $gudangFilterTujuan = " AND abx.id_gudang_tujuan = :id_gudang: ";
        }

        // TAMBAHAN (2026-08-08): kolom Saldo Harian (running balance per baris)
        // diaktifkan lagi di UI (stock_card.js) - pakai persis angka "Total Awal"
        // yang sudah dihitung getDataGudang() sebagai titik awal kumulatifnya,
        // supaya Saldo Harian di baris terakhir selalu cocok dgn "Total akhir"
        // yang tampil di atas laporan ini (satu sumber kebenaran, tidak dihitung
        // dobel dengan rumus yang bisa berbeda).
        $saldoAwalPeriode = (float) ($this->getDataGudang($idBarang, $idGudang, $year, $month)->saldo_awal ?? 0);
        $params['saldo_awal_periode'] = $saldoAwalPeriode;

        $sql = "WITH raw_trans AS (
            SELECT abx.*,
                   ROW_NUMBER() OVER (
                       PARTITION BY abx.kode_transaksi, abx.id_barang, COALESCE(abx.lot_no, ''), COALESCE(abx.pack_id, 0), abx.id_gudang_asal, abx.id_gudang_tujuan, abx.jumlah
                       ORDER BY abx.id DESC
                   ) AS rn
            FROM trans_barang abx
            WHERE COALESCE(abx.active, 1) = 1
        ),
        normalized_trans AS ( SELECT
            abx.tanggal,
            abx.created_at,
            abx.kode_transaksi,
            abx.id_barang,
            abx.id_gudang_asal AS id_gudang,
            'keluar' AS jenis_transaksi,
            abx.jumlah * -1 AS jumlah,
            abx.id,
            bbx.name,
            COALESCE(NULLIF(abx.lot_no, ''), '-') as lot_no,
            abx.pack_id,
            COALESCE(rp.pack_name, '-') as pack_name,
            dbx.nama_barang,
            dbx.kode_barang,
            ebx.nama_jenis_barang,
            fbx.nama_satuan,
            COALESCE(
                NULLIF(abx.price, 0),
                (SELECT trd.price FROM trans_receive_detail trd JOIN trans_receive_header trh ON trh.id = trd.id_header WHERE trd.id_barang = abx.id_barang AND trd.lot_no = abx.lot_no AND trd.price > 0 AND trd.active = 1 ORDER BY trh.id DESC LIMIT 1),
                dbx.harga_satuan,
                0
            ) as price
        FROM
            raw_trans abx
            LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            LEFT JOIN ref_pack rp ON abx.pack_id = rp.id
            INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
            INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
            INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
        WHERE
            abx.rn = 1
            AND abx.id_gudang_asal IS NOT null
            AND abx.id_barang = :id_barang:
            $dateFilter
            $gudangFilterAsal

        UNION ALL
        SELECT
            abx.tanggal,
            abx.created_at,
            abx.kode_transaksi,
            abx.id_barang,
            abx.id_gudang_tujuan AS id_gudang,
            'masuk' AS jenis_transaksi,
            abx.jumlah AS jumlah,
            abx.id,
            bbx.name,
            COALESCE(NULLIF(abx.lot_no, ''), '-') as lot_no,
            abx.pack_id,
            COALESCE(rp.pack_name, '-') as pack_name,
            dbx.nama_barang,
            dbx.kode_barang,
            ebx.nama_jenis_barang,
            fbx.nama_satuan,
            COALESCE(
                NULLIF(abx.price, 0),
                (SELECT trd.price FROM trans_receive_detail trd JOIN trans_receive_header trh ON trh.id = trd.id_header WHERE trd.id_barang = abx.id_barang AND trd.lot_no = abx.lot_no AND trd.price > 0 AND trd.active = 1 ORDER BY trh.id DESC LIMIT 1),
                dbx.harga_satuan,
                0
            ) as price
        FROM
            raw_trans abx
            LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            LEFT JOIN ref_pack rp ON abx.pack_id = rp.id
            INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
            INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
            INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
        WHERE
            abx.rn = 1
            AND abx.id_gudang_tujuan IS NOT null
            AND abx.id_barang = :id_barang:
            $dateFilter
            $gudangFilterTujuan
            )
        SELECT
            id,
            tanggal,
            created_at,
            name AS transaksi,
            kode_transaksi,
            id_barang,
            id_gudang,
            pack_name,
            lot_no,
            nama_barang,
            kode_barang,
            nama_jenis_barang,
            nama_satuan,
            price,
            CONCAT(kode_barang, ' ', nama_barang) AS barang,
            CAST(CASE WHEN jenis_transaksi = 'masuk' THEN jumlah ELSE 0 END AS DECIMAL(18,2)) AS masuk,
            CAST(CASE WHEN jenis_transaksi = 'keluar' THEN -jumlah ELSE 0 END AS DECIMAL(18,2)) AS keluar,
            -- Qty Awal = saldo SEBELUM baris ini (tidak termasuk mutasi baris ini
            -- sendiri) - saldo sebelum periode (dari getDataGudang()) + kumulatif
            -- net mutasi (jumlah sudah bertanda: + masuk, - keluar) sampai baris
            -- SEBELUM ini, diurutkan sama persis dengan ORDER BY di bawah.
            CAST(
                :saldo_awal_periode: + COALESCE(SUM(jumlah) OVER (
                    ORDER BY tanggal, created_at ASC, id_gudang, id ASC, kode_transaksi
                    ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING
                ), 0)
            AS DECIMAL(18,2)) AS qty_awal,
            -- Qty Akhir = saldo SETELAH baris ini (termasuk mutasi baris ini).
            CAST(
                :saldo_awal_periode: + SUM(jumlah) OVER (
                    ORDER BY tanggal, created_at ASC, id_gudang, id ASC, kode_transaksi
                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                )
            AS DECIMAL(18,2)) AS qty_akhir
        FROM normalized_trans
        ORDER BY
            nama_jenis_barang ASC,
            nama_barang ASC,
            tanggal,
            created_at ASC,
            id_gudang,
            id ASC,
            kode_transaksi";

        $query = $this->db->query($sql, $params);


        $this->_data  = $query->getResult();
        // dd($this->_data);
        return $this->_data;
    }

    function getDataGudang($idBarang = null, $idGudang = null, $year = null, $month = null)
    {
        // Saldo Awal diambil dari trans_barang_history (titik cutoff/stok opname)
        // agar konsisten dengan Laporan Persediaan dan Dashboard.
        // Saldo Akhir = saldo_awal + net mutasi bulan dari trans_barang.

        $params = [];
        $params['id_barang'] = $idBarang;

        // --- Saldo Awal: dari trans_barang_history bulan yang dipilih ---
        $gudangHistoryFilter = !empty($idGudang) ? "AND h.id_gudang = :id_gudang:" : "";
        $monthHistoryFilter  = !empty($month)    ? "AND h.month = :h_month:"      : "";
        $yearHistoryFilter   = !empty($year)     ? "AND h.year  = :h_year:"       : "";

        if (!empty($idGudang)) $params['id_gudang'] = $idGudang;
        if (!empty($month))    $params['h_month']   = $month;
        if (!empty($year))     $params['h_year']    = $year;

        $sqlSaldoAwal = "
            SELECT COALESCE(SUM(h.stok_awal), 0) AS saldo_awal
            FROM trans_barang_history h
            WHERE h.id_barang = :id_barang:
            $gudangHistoryFilter
            $monthHistoryFilter
            $yearHistoryFilter
        ";
        $rowHistory = $this->db->query($sqlSaldoAwal, $params)->getRow();
        $saldoAwal  = $rowHistory ? (float) $rowHistory->saldo_awal : 0;

        // --- Saldo Akhir: saldo_awal + net mutasi bulan dari trans_barang ---
        $dateFilterAsal   = "";
        $dateFilterTujuan = "";

        if (!empty($year) && !empty($month)) {
            $start    = sprintf('%04d-%02d-01', $year, $month);
            $endMonth = ((int) $month) == 12 ? 1 : ((int) $month) + 1;
            $endYear  = ((int) $month) == 12 ? ((int) $year) + 1 : (int) $year;
            $end      = sprintf('%04d-%02d-01', $endYear, $endMonth);
            $params['tgl_start'] = $start;
            $params['tgl_end']   = $end;
            $dateFilterAsal   = "AND abx.tanggal >= :tgl_start: AND abx.tanggal < :tgl_end:";
            $dateFilterTujuan = "AND abx.tanggal >= :tgl_start: AND abx.tanggal < :tgl_end:";
        } elseif (!empty($year)) {
            $params['tgl_start'] = sprintf('%04d-01-01', $year);
            $params['tgl_end']   = sprintf('%04d-01-01', ((int) $year) + 1);
            $dateFilterAsal   = "AND abx.tanggal >= :tgl_start: AND abx.tanggal < :tgl_end:";
            $dateFilterTujuan = "AND abx.tanggal >= :tgl_start: AND abx.tanggal < :tgl_end:";
        }

        $gudangFilterAsal   = !empty($idGudang) ? "AND abx.id_gudang_asal    = :id_gudang:" : "AND abx.id_gudang_asal    IS NOT NULL";
        $gudangFilterTujuan = !empty($idGudang) ? "AND abx.id_gudang_tujuan  = :id_gudang:" : "AND abx.id_gudang_tujuan  IS NOT NULL";

        $sqlNet = "
            WITH raw_trans AS (
                SELECT abx.*,
                       ROW_NUMBER() OVER (
                           PARTITION BY abx.kode_transaksi, abx.id_barang,
                               COALESCE(abx.lot_no, ''), COALESCE(abx.pack_id, 0),
                               abx.id_gudang_asal, abx.id_gudang_tujuan, abx.jumlah
                           ORDER BY abx.id DESC
                       ) AS rn
                FROM trans_barang abx
                WHERE COALESCE(abx.active, 1) = 1
            )
            SELECT
                COALESCE(
                    (SELECT SUM(abx.jumlah) FROM raw_trans abx
                     WHERE abx.rn = 1 AND abx.id_barang = :id_barang: $dateFilterTujuan $gudangFilterTujuan), 0
                ) -
                COALESCE(
                    (SELECT SUM(abx.jumlah) FROM raw_trans abx
                     WHERE abx.rn = 1 AND abx.id_barang = :id_barang: $dateFilterAsal $gudangFilterAsal), 0
                ) AS net_mutasi
        ";
        $rowNet     = $this->db->query($sqlNet, $params)->getRow();
        $netMutasi  = $rowNet ? (float) $rowNet->net_mutasi : 0;
        $saldoAkhir = $saldoAwal + $netMutasi;

        return (object) [
            'saldo_awal'  => $saldoAwal,
            'saldo_akhir' => $saldoAkhir,
        ];
    }
}

