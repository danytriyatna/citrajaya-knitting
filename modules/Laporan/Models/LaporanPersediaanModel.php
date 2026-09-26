<?php

namespace Modules\Laporan\Models;

class LaporanPersediaanModel extends \App\Models\PrModel
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

    // function getLaporanPersediaan($idJenisBarang = null, $idGudang = null, $year = null, $month = null)
    // {
    //     $params = [];
    //     $sql = "
    // WITH normalized_trans AS (
    //     SELECT
    //         abx.tanggal,
    //         abx.created_at,
    //         abx.kode_transaksi,
    //         abx.id_barang,
    //         abx.id_gudang_asal AS id_gudang,
    //         'keluar' AS jenis_transaksi,
    //         abx.jumlah * -1 AS jumlah,
    //         abx.id,
    //         bbx.name,
    //         cbx.lot_no,
    //         abx.lot_id,
    //         dbx.nama_barang,
    //         dbx.kode_barang,
    //         ebx.nama_jenis_barang,
    //         fbx.nama_satuan,
    //         abx.price
    //     FROM
    //         trans_barang abx
    //         LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
    //         INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
    //         INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
    //         INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
    //         INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
    //     WHERE
    //         abx.id_gudang_asal IS NOT NULL ";
    //     if (!empty($month)) {
    //         $sql .= " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan:";
    //         $params['bulan'] = $month;
    //     }
    //     if (!empty($year)) {
    //         $sql .= " AND EXTRACT(YEAR FROM  abx.tanggal) = :tahun:";
    //         $params['tahun'] = $year;
    //     }
    //     if (!empty($idGudang)) {
    //         $sql .= " AND abx.id_gudang_asal = :id_gudang:";
    //         $params['id_gudang'] = $idGudang;
    //     }
    //     if (!empty($idJenisBarang)) {
    //         $sql .= " AND dbx.id_jenis_barang = :id_jenis_barang:";
    //         $params['id_jenis_barang'] = $idJenisBarang;
    //     }
    //     $sql .= " UNION ALL
    //     SELECT
    //         abx.tanggal,
    //         abx.created_at,
    //         abx.kode_transaksi,
    //         abx.id_barang,
    //         abx.id_gudang_tujuan AS id_gudang,
    //         'masuk' AS jenis_transaksi,
    //         abx.jumlah AS jumlah,
    //         abx.id,
    //         bbx.name,
    //         cbx.lot_no,
    //         abx.lot_id,
    //         dbx.nama_barang,
    //         dbx.kode_barang,
    //         ebx.nama_jenis_barang,
    //         fbx.nama_satuan,
    //         abx.price
    //     FROM
    //         trans_barang abx 
    //         LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
    //         INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
    //           INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
    //         INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
    //         INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
    //     WHERE
    //         abx.id_gudang_tujuan IS NOT NULL ";
    //     if (!empty($month)) {
    //         $sql .= " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan:";
    //         $params['bulan'] = $month;
    //     }
    //     if (!empty($year)) {
    //         $sql .= " AND EXTRACT(YEAR FROM  abx.tanggal) = :tahun:";
    //         $params['tahun'] = $year;
    //     }
    //     if (!empty($idGudang)) {
    //         $sql .= " AND abx.id_gudang_tujuan = :id_gudang:";
    //         $params['id_gudang'] = $idGudang;
    //     }
    //     if (!empty($idJenisBarang)) {
    //         $sql .= " AND dbx.id_jenis_barang = :id_jenis_barang:";
    //         $params['id_jenis_barang'] = $idJenisBarang;
    //     }
    //     $sql .= " ),
    // saldo_awal AS (
    //     SELECT
    //         nt.lot_id,
    //         nt.id_barang,
    //         nt.id_gudang,
    //         COALESCE(SUM(nt.jumlah), 0) AS saldo_awal
    //     FROM
    //         normalized_trans nt ";
    //     if (!empty($idGudang)) {
    //         $sql .= "WHERE nt.id_gudang = :id_gudang: ";
    //         $params['id_gudang'] = $idGudang;
    //     }



    //     $sql .= " GROUP BY
    //         nt.id_barang,nt.id_gudang,  nt.lot_id
    // ),
    // stock_card AS (
    //     SELECT
    //         nt.tanggal,
    //         nt.created_at,
    //         nt.name as transaksi,
    //         nt.kode_transaksi,
    //         nt.id_barang,
    //         nt.id_gudang,
    //         nt.lot_id,
    //         nt.lot_no,
    //         nt.nama_barang,
    //         nt.kode_barang,
    //         nt.nama_jenis_barang,
    //         nt.nama_satuan,
    //         nt.price,
    //         CONCAT(nt.kode_barang,' ', nt.nama_barang) AS barang,
    //         CASE
    //             WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah 
    //             ELSE 0 
    //         END AS masuk,
    //         CASE
    //             WHEN nt.jenis_transaksi = 'keluar' THEN -nt.jumlah 
    //             ELSE 0 
    //         END AS keluar,
    //         SUM(nt.jumlah) OVER (
    //             PARTITION BY nt.id_barang,  nt.id_gudang,  nt.lot_id
    //             ORDER BY nt.id ASC, nt.kode_transaksi
    //         ) AS saldo_akhir
    //     FROM
    //         normalized_trans nt
    //         LEFT JOIN saldo_awal sa 
    //             ON nt.id_barang = sa.id_barang 
    //             AND nt.lot_id = sa.lot_id
    //             AND nt.id_gudang = sa.id_gudang
            
    // )
    // SELECT *
    // FROM stock_card ";


    //     $sql .=  " ORDER BY
    //     nama_jenis_barang ASC,
    //     nama_barang ASC,
    //     created_at ASC,
    //     id_gudang,
    //     tanggal,
    //     kode_transaksi";
    //     $query = $this->db->query($sql, $params);

    //     $this->_data  = $query->getResult();
    //     return $this->_data;
    // }

    function getLaporanPersediaan($idJenisBarang = null, $idGudang = null, $year = null, $month = null, $idBarang = null)
    {
        $params = [];

        if ($month == 1) {
            $params['prev_month'] = 12;
            $params['prev_year'] = $year - 1;
        }
        else {
            $params['prev_month'] = $month - 1;
            $params['prev_year'] = $year;
        }
        $idJenisBarangSql = "";
        if (!empty($idJenisBarang)) {
            $idJenisBarangSql = "AND dbx.id_jenis_barang = $idJenisBarang";
        }

        $idBarangSql = "";
        if (!empty($idBarang)) {
            $idBarangSql = "AND abx.id_barang = $idBarang";
        }

        $idGudangAsalSql = "";
        $idGudangTujuanSql = "";
        if (!empty($idGudang)) {
            $idGudangAsalSql = "AND abx.id_gudang_asal = $idGudang";
            $idGudangTujuanSql = "AND abx.id_gudang_tujuan = $idGudang";
        }

        $sql = "WITH raw_trans AS (
            SELECT abx.*,
                   ROW_NUMBER() OVER (
                       PARTITION BY abx.kode_transaksi, abx.id_barang, COALESCE(abx.lot_no, ''), COALESCE(abx.pack_id, 0), abx.id_gudang_asal, abx.id_gudang_tujuan, abx.jumlah
                       ORDER BY abx.id DESC
                   ) AS rn
            FROM trans_barang abx
            WHERE EXTRACT(MONTH FROM abx.tanggal) = $month
              AND EXTRACT(YEAR FROM abx.tanggal) = $year
              AND COALESCE(abx.active, 1) = 1
        ),
        normalized_trans as ( SELECT
            abx.tanggal,
            abx.created_at,
            abx.kode_transaksi,
            abx.id_barang,
            abx.id_gudang_asal AS id_gudang,
            'keluar' AS jenis_transaksi,
            abx.jumlah * -1 AS jumlah,
            abx.id,
            bbx.name,
            -- dash dan blank (kosong) sama-sama berarti tidak ada lot, tapi tersimpan tidak
            -- konsisten di trans_barang - disamakan di sini supaya tidak dianggap 2 lot
            -- berbeda di partisi/agregasi berikutnya (akar bug Stok Awal beda dgn Akhir
            -- bulan sebelumnya).
            COALESCE(NULLIF(abx.lot_no, ''), '-') AS lot_no,
            abx.pack_id,
            rp.pack_name,
            dbx.nama_barang,
            dbx.kode_barang,
            ebx.nama_jenis_barang,
            fbx.nama_satuan,
            COALESCE(
                NULLIF(abx.price, 0),
                (SELECT trd.price FROM trans_receive_detail trd JOIN trans_receive_header trh ON trh.id = trd.id_header WHERE trd.id_barang = abx.id_barang AND trd.lot_no = abx.lot_no AND trd.price > 0 AND trd.active = 1 ORDER BY trh.id DESC LIMIT 1),
                dbx.harga_satuan,
                0
            ) AS price,
            dbx.id_jenis_barang
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
            AND EXTRACT(MONTH FROM  abx.tanggal) = $month
            AND EXTRACT(YEAR FROM  abx.tanggal) = $year
            $idGudangAsalSql
            $idJenisBarangSql
            $idBarangSql
            
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
            COALESCE(NULLIF(abx.lot_no, ''), '-') AS lot_no,
            abx.pack_id,
            rp.pack_name,
            dbx.nama_barang,
            dbx.kode_barang,
            ebx.nama_jenis_barang,
            fbx.nama_satuan,
            COALESCE(
                NULLIF(abx.price, 0),
                (SELECT trd.price FROM trans_receive_detail trd JOIN trans_receive_header trh ON trh.id = trd.id_header WHERE trd.id_barang = abx.id_barang AND trd.lot_no = abx.lot_no AND trd.price > 0 AND trd.active = 1 ORDER BY trh.id DESC LIMIT 1),
                dbx.harga_satuan,
                0
            ) AS price,
            dbx.id_jenis_barang
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
            AND EXTRACT(MONTH FROM  abx.tanggal) = $month
            AND EXTRACT(YEAR FROM  abx.tanggal) = $year
            $idGudangTujuanSql
            $idJenisBarangSql
            $idBarangSql
            
            ),

            normalized_trans_dedup AS (
                SELECT
                    tanggal, created_at, kode_transaksi, id_barang, id_gudang,
                    jenis_transaksi, jumlah, id, name, lot_no, pack_id,
                    pack_name, nama_barang, kode_barang, nama_jenis_barang, nama_satuan, price, id_jenis_barang
                FROM normalized_trans
            ),
            
        history_per_lot AS (
            SELECT DISTINCT ON (id_barang, COALESCE(id_gudang, 0), year, month, COALESCE(pack_id, 0), COALESCE(NULLIF(lot_no, ''), '-'))
                COALESCE(NULLIF(lot_no, ''), '-') AS lot_no,
                pack_id,
                id_barang,
                id_gudang,
                month,
                year,
                stok_awal AS saldo_awal
            FROM trans_barang_history
            ORDER BY id_barang, COALESCE(id_gudang, 0), year, month, COALESCE(pack_id, 0), COALESCE(NULLIF(lot_no, ''), '-'), id DESC
        ),
        normalized_trans_with_history AS (
            SELECT
                nt.*,
                COALESCE(h.saldo_awal, 0) AS saldo_awal_history
            FROM normalized_trans_dedup nt
            LEFT JOIN history_per_lot h
                ON nt.lot_no = h.lot_no
                AND nt.pack_id = h.pack_id
                AND nt.id_barang = h.id_barang
                -- id_gudang wajib ikut di JOIN ini - tanpanya, baris history gudang LAIN
                -- dgn item/pack/lot/bulan/tahun yg sama ikut ke-JOIN (fan-out), bikin
                -- baris transaksi kelipatan & merusak SUM/ROW_NUMBER window di bawah.
                AND nt.id_gudang = h.id_gudang
                AND EXTRACT(MONTH FROM nt.tanggal) = h.month
                AND EXTRACT(YEAR FROM nt.tanggal) = h.year
        ),
        stock_base AS (
            SELECT
                nt.id,
                nt.tanggal,
                nt.created_at,
                nt.name AS transaksi,
                nt.kode_transaksi,
                nt.id_barang,
                nt.id_gudang,
                nt.pack_name,
                nt.pack_id,
                nt.lot_no,
                nt.nama_barang,
                nt.kode_barang,
                nt.nama_jenis_barang,
                nt.nama_satuan,
                nt.price,
                nt.id_jenis_barang,
                CONCAT(nt.kode_barang, ' ', nt.nama_barang) AS barang,
                CAST(CASE WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah ELSE 0 END AS DECIMAL(18,2)) AS masuk,
                CAST(CASE WHEN nt.jenis_transaksi = 'keluar' THEN -nt.jumlah ELSE 0 END AS DECIMAL(18,2)) AS keluar,
                
                -- NOTE: partition ditambah pack_id (sebelumnya cuma id_barang+lot_no) -
                -- kalau satu lot punya lebih dari 1 pack, running balance sebelumnya
                -- ikut mencampur qty antar pack & saldo_awal_history cuma ke-assign ke
                -- baris yang kebetulan tercatat paling awal di lot itu (pack lain jadi
                -- NULL/salah). Ini akar salah satu penyebab Total Akhir di Dashboard
                -- beda dengan Ringkasan Persediaan.
                CAST(
                    CASE
                        WHEN ROW_NUMBER() OVER(PARTITION BY nt.id_barang, nt.lot_no, nt.pack_id ORDER BY nt.id ASC) = 1
                        THEN nt.saldo_awal_history
                        ELSE NULL
                    END AS DECIMAL(18,2)
                ) AS saldo_awal_awal,

                CAST(
                    SUM(
                        CASE
                            WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah
                            WHEN nt.jenis_transaksi = 'keluar' THEN nt.jumlah
                            ELSE 0
                        END
                    ) OVER (
                        PARTITION BY nt.id_barang, nt.lot_no, nt.pack_id ORDER BY nt.id ASC
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                    ) +
                    FIRST_VALUE(nt.saldo_awal_history) OVER (PARTITION BY nt.id_barang, nt.lot_no, nt.pack_id ORDER BY nt.id ASC)
                    AS DECIMAL(18,2)
                ) AS saldo_akhir
            FROM normalized_trans_with_history nt
        ),

        stock_card AS (
            SELECT
                sb.*,
                CAST(
                    COALESCE(
                        sb.saldo_awal_awal,
                        LAG(sb.saldo_akhir) OVER (PARTITION BY sb.id_barang, sb.lot_no, sb.pack_id ORDER BY sb.id)
                    ) AS DECIMAL(18,2)
                ) AS saldo_awal
            FROM stock_base sb
        )

            SELECT *
            FROM stock_card
            ORDER BY
                nama_jenis_barang ASC,
                nama_barang ASC,
                tanggal,
                -- lot_no DESC,
                created_at ASC,
                id_gudang,
                id ASC,
                kode_transaksi";

        $query = $this->db->query($sql, $params);


        $this->_data  = $query->getResult();
        // dd($this->_data);
        return $this->_data;
    }

    function getDataGudang($idJenisBarang = null, $idGudang = null, $year = null, $month = null, $params = null)
    {
        // dd($idJenisBarang);
        $builder = $this->db->table("trans_barang_history a");
        $builder->select("a.*, b.nama_barang, c.nama_jenis_barang, a.pack_id, COALESCE(rp.pack_name, '-') as pack_name, COALESCE(NULLIF(a.lot_no, ''), '-') as lot_no, b.kode_barang || ' ' || b.nama_barang as barang, 
                            CAST(CASE WHEN a.year = 2026 AND (a.month = 6 OR a.month = 7) THEN (a.masuk - a.keluar) ELSE a.jumlah END AS DECIMAL(18,2)) as saldo_akhir,
                            CAST(CASE WHEN a.year = 2026 AND (a.month = 6 OR a.month = 7) THEN 0 ELSE a.stok_awal END AS DECIMAL(18,2)) as saldo_awal,
                            w.keterangan as color_code, rs.nama_satuan");
        $builder->join("ref_barang b", "a.id_barang = b.id", "inner");
        $builder->join("ref_jenis_barang c", "a.id_jenis_barang = c.id", "inner");
        $builder->join("ref_warna w", "b.id_warna = w.id", "left");
        $builder->join("ref_satuan rs", "b.id_satuan = rs.id", "left");
        $builder->join("ref_pack rp", "a.pack_id = rp.id", "left");
        if (!empty($idJenisBarang)) {
            $builder->where("a.id_jenis_barang", $idJenisBarang);
        }
        if (!empty($idGudang)) {
            $builder->where("a.id_gudang", $idGudang);
        }
        if (!empty($month)) {
            $builder->where("a.month", $month);
        }
        if (!empty($year)) {
            $builder->where("a.year", $year);
        }
        if (!empty($params['id_barang'])) {
            $builder->where("a.id_barang", $params['id_barang']);
        }
        if (!empty($params['lot_no'])) {
            $builder->where("COALESCE(NULLIF(a.lot_no, ''), '-')", $params['lot_no']);
        }
        if (!empty($params['pack_id'])) {
            $builder->where("a.pack_id", $params['pack_id']);
        }
        if (!empty($params['lot_id'])) {
            $builder->where("a.lot_id", $params['lot_id']);
        }
        if (!empty($params['search'])) {
            $builder->groupStart();
            $builder->Where('LOWER(b.nama_barang) LIKE', strtolower("%{$params['search']}%"));
            $builder->orWhere('LOWER(b.kode_barang) LIKE', strtolower("%{$params['search']}%"));
            $builder->orWhere('LOWER(a.lot_no) LIKE', strtolower("%{$params['search']}%"));
            $builder->groupEnd();
        }
        $builder->orderBy("c.nama_jenis_barang", "ASC");
        $builder->orderBy("b.nama_barang", "ASC");    
        $builder->orderBy("a.lot_id", "DESC");    
        $builder->orderBy("a.tanggal", "ASC");    
        $builder->orderBy("a.id", "ASC");    
        
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getDataGudangPrint($idJenisBarang = null, $idGudang = null, $year = null, $month = null, $params = null)
    {
        $whereJenis  = !empty($idJenisBarang)        ? "AND a.id_jenis_barang = ?"  : "";
        $whereGudang = !empty($idGudang)             ? "AND a.id_gudang = ?"        : "";
        $whereMonth  = !empty($month)                ? "AND a.month = ?"            : "";
        $whereYear   = !empty($year)                 ? "AND a.year = ?"             : "";
        $whereBarang = !empty($params['id_barang'])  ? "AND a.id_barang = ?"        : "";
        $whereLotNo  = !empty($params['lot_no'])     ? "AND a.lot_no = ?"           : "";
        $wherePack   = !empty($params['pack_id'])    ? "AND a.pack_id = ?"          : "";
        $whereLotId  = !empty($params['lot_id'])     ? "AND a.lot_id = ?"           : "";

        $sql = "
        SELECT
            sub.id_barang,
            sub.id_jenis_barang,
            sub.lot_no,
            sub.kode_barang,
            sub.nama_jenis_barang,
            sub.kode_warna,
            sub.barang,
            sub.tanggal,
            sub.nama_unit,
            sub.pack_name,
            sub.price,
            STRING_AGG(
                CONCAT(COALESCE(sub.pack_name, '-'), ':', sub.qty::text),
                '|'
                ORDER BY sub.pack_name
            ) AS pack_data,
            CAST(SUM(sub.masuk) AS DECIMAL(18,2))     AS masuk,
            CAST(SUM(sub.keluar) AS DECIMAL(18,2))    AS keluar,
            CAST(SUM(sub.jumlah) AS DECIMAL(18,2))    AS saldo_akhir,
            CAST(SUM(sub.stok_awal) AS DECIMAL(18,2)) AS saldo_awal
        FROM (
            SELECT
                a.*,
                (CASE WHEN a.year = 2026 AND (a.month = 6 OR a.month = 7) THEN 0 ELSE a.stok_awal END) AS stok_awal,
                (CASE WHEN a.year = 2026 AND (a.month = 6 OR a.month = 7) THEN (a.masuk - a.keluar) ELSE a.jumlah END) AS jumlah,
                b.nama_barang,
                b.kode_barang,
                b.nama_barang AS barang,
                c.nama_jenis_barang,
                rw.keterangan as kode_warna,
                rp.pack_name,
                fbx.nama_satuan AS nama_unit,
                a.jumlah  AS qty
            FROM trans_barang_history a
            INNER JOIN ref_barang b        ON a.id_barang       = b.id
            INNER JOIN ref_satuan fbx      ON b.id_satuan       = fbx.id
            LEFT  JOIN ref_warna rw        ON b.id_warna        = rw.id
            INNER JOIN ref_jenis_barang c  ON a.id_jenis_barang = c.id
            LEFT  JOIN ref_pack rp         ON a.pack_id         = rp.id
            WHERE 1=1
            {$whereJenis}
            {$whereGudang}
            {$whereMonth}
            {$whereYear}
            {$whereBarang}
            {$whereLotNo}
            {$wherePack}
            {$whereLotId}
        ) sub
        GROUP BY
            sub.id_barang, sub.id_jenis_barang,
            sub.lot_no, sub.kode_barang,
            sub.nama_jenis_barang, sub.kode_warna, sub.barang,
            sub.tanggal, sub.nama_unit, sub.pack_name, sub.price
        ORDER BY
            sub.nama_jenis_barang ASC,
            sub.barang            ASC,
            sub.pack_name         ASC,
            sub.tanggal           DESC
         ";

        $binds = [];
        if (!empty($idJenisBarang))       $binds[] = $idJenisBarang;
        if (!empty($idGudang))            $binds[] = $idGudang;
        if (!empty($month))               $binds[] = $month;
        if (!empty($year))                $binds[] = $year;
        if (!empty($params['id_barang'])) $binds[] = $params['id_barang'];
        if (!empty($params['lot_no']))    $binds[] = $params['lot_no'];
        if (!empty($params['pack_id']))   $binds[] = $params['pack_id'];
        if (!empty($params['lot_id']))    $binds[] = $params['lot_id'];

        $this->_data = $this->db->query($sql, $binds)->getResult();
        return $this->_data;
    }

    function getDataGudangPrintPack($idJenisBarang = null, $idGudang = null, $year = null, $month = null, $params = null)
    {
        $whereJenis  = !empty($idJenisBarang)        ? "AND a.id_jenis_barang = ?"  : "";
        $whereGudang = !empty($idGudang)             ? "AND a.id_gudang = ?"        : "";
        $whereMonth  = !empty($month)                ? "AND a.month = ?"            : "";
        $whereYear   = !empty($year)                 ? "AND a.year = ?"             : "";
        $whereBarang = !empty($params['id_barang'])  ? "AND a.id_barang = ?"        : "";
        $whereLotNo  = !empty($params['lot_no'])     ? "AND a.lot_no = ?"           : "";
        $wherePack   = !empty($params['pack_id'])    ? "AND a.pack_id = ?"          : "";
        $whereLotId  = !empty($params['lot_id'])     ? "AND a.lot_id = ?"           : "";

       $sql = "
            SELECT
                sub.id_barang,
                sub.id_jenis_barang,
                sub.lot_no,
                sub.kode_barang,
                sub.nama_jenis_barang,
                sub.kode_warna,
                sub.barang,
                sub.tanggal,
                sub.nama_unit,
                sub.price,
                STRING_AGG(
                    CONCAT(COALESCE(sub.pack_name, '-'), ':', sub.qty::text),
                    '|'
                    ORDER BY sub.pack_name
                ) AS pack_data,
                CAST(SUM(sub.masuk) AS DECIMAL(18,2))     AS masuk,
                CAST(SUM(sub.keluar) AS DECIMAL(18,2))    AS keluar,
                CAST(SUM(sub.jumlah) AS DECIMAL(18,2))    AS saldo_akhir,
                CAST(SUM(sub.stok_awal) AS DECIMAL(18,2)) AS saldo_awal
            FROM (
                SELECT
                    a.id_barang,
                    a.id_jenis_barang,
                    a.lot_no,
                    a.tanggal,
                    a.price,
                    a.masuk,
                    a.keluar,
                    (CASE WHEN a.year = 2026 AND (a.month = 6 OR a.month = 7) THEN (a.masuk - a.keluar) ELSE a.jumlah END) AS jumlah,
                    (CASE WHEN a.year = 2026 AND (a.month = 6 OR a.month = 7) THEN 0 ELSE a.stok_awal END) AS stok_awal,
                    a.id AS urutan,
                    b.nama_barang,
                    b.kode_barang,
                    b.nama_barang AS barang,
                    c.nama_jenis_barang,
                    rw.keterangan AS kode_warna,
                    rp.pack_name,
                    fbx.nama_satuan AS nama_unit,
                    a.jumlah AS qty
                FROM trans_barang_history a
                INNER JOIN ref_barang b        ON a.id_barang       = b.id
                INNER JOIN ref_satuan fbx      ON b.id_satuan       = fbx.id
                LEFT  JOIN ref_warna rw        ON b.id_warna        = rw.id
                INNER JOIN ref_jenis_barang c  ON a.id_jenis_barang = c.id
                LEFT  JOIN ref_pack rp         ON a.pack_id         = rp.id
                WHERE 1=1
                {$whereJenis}
                {$whereGudang}
                {$whereMonth}
                {$whereYear}
                {$whereBarang}
                {$whereLotNo}
                {$wherePack}
                {$whereLotId}
            ) sub
            GROUP BY
                sub.id_barang, sub.id_jenis_barang,
                sub.lot_no, sub.kode_barang,
                sub.nama_jenis_barang, sub.kode_warna, sub.barang,
                sub.tanggal, sub.nama_unit, sub.price
            ORDER BY
                sub.nama_jenis_barang ASC,
                sub.barang            ASC,
                sub.tanggal           DESC
        ";

        $binds = [];
        if (!empty($idJenisBarang))       $binds[] = $idJenisBarang;
        if (!empty($idGudang))            $binds[] = $idGudang;
        if (!empty($month))               $binds[] = $month;
        if (!empty($year))                $binds[] = $year;
        if (!empty($params['id_barang'])) $binds[] = $params['id_barang'];
        if (!empty($params['lot_no']))    $binds[] = $params['lot_no'];
        if (!empty($params['pack_id']))   $binds[] = $params['pack_id'];
        if (!empty($params['lot_id']))    $binds[] = $params['lot_id'];

        $this->_data = $this->db->query($sql, $binds)->getResult();
        return $this->_data;
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

    /**
     * Perbaiki trans_barang.tanggal yang "melenceng" dari bulan yang seharusnya,
     * berdasarkan YY+MM yang tertanam di kode_transaksi (bukan dengan menyamakan
     * ke tabel header, karena header pun bisa ikut salah - lihat kasus TRF260600171).
     *
     * Setiap modul yang menulis ke trans_barang membuat kode_transaksi dengan pola
     * PREFIX + YY + MM + urutan, sekali saja saat dokumen pertama dibuat, dan kode
     * ini tidak pernah diubah lagi walau tanggalnya diedit ulang. Jadi YY/MM di kode
     * adalah sumber kebenaran yang paling bisa dipercaya untuk bulan pembuatan asli.
     *
     * Prefix yang dikenal (lihat generateKodePersediaan() di masing-masing model):
     *   TRF  = Item Transfer (ItemTransferModel)
     *   BTM  = Barang Masuk (BarangMasukModel / IncomingGoodsModel)
     *   BTMC = Barang Masuk lintas perusahaan (BarangMasukModel, id_perusahaan != 1)
     *   BTK  = Barang Keluar (BarangKeluarModel / OutgoingGoodsModel)
     *   REC  = Receive Item (ReceiveItemModel)
     */
    /**
     * Sinkronisasi tanggal trans_barang agar SELALU 100% mengikuti tanggal resmi
     * dari tabel Header Dokumen aslinya (trans_barang_header, trans_barang_trf_header, trans_receive_header),
     * BUKAN menebak-nebak dari awalan kode transaksi.
     */
    private function syncTanggalDariKodeTransaksi($idGudang = null, $year = null, $month = null)
    {
        // 1. Sinkronisasi dari Barang Masuk (BTM) & Barang Keluar (BTK)
        $this->db->query("
            UPDATE trans_barang 
            SET tanggal = tbh.tanggal
            FROM trans_barang_header tbh
            WHERE trans_barang.kode_transaksi = tbh.kode_transaksi
              AND trans_barang.tanggal::date <> tbh.tanggal::date
        ");

        // 2. Sinkronisasi dari Transfer Barang (TRF)
        $this->db->query("
            UPDATE trans_barang 
            SET tanggal = tth.tanggal
            FROM trans_barang_trf_header tth
            WHERE trans_barang.kode_transaksi = tth.kode_transaksi
              AND trans_barang.tanggal::date <> tth.tanggal::date
        ");

        // 3. Sinkronisasi dari Receive Item PO (REC)
        $this->db->query("
            UPDATE trans_barang 
            SET tanggal = trh.rec_date
            FROM trans_receive_header trh
            WHERE trans_barang.kode_transaksi = trh.rec_no
              AND trans_barang.tanggal::date <> trh.rec_date::date
        ");
    }

    /**
     * Menyelaraskan harga-harga transaksi yang anomali (harga 0, null, atau menyimpang
     * pada Barang Keluar / Transfer) sesuai aturan kronologis mundur:
     * - Harga SELALU mengacu pada transaksi masuk (Receive Item / Barang Masuk) yang
     *   tanggalnya <= tanggal transaksi yang sedang diupdate (tidak boleh mengintip tanggal ke depan).
     * - Mengambil transaksi masuk terdekat sebelum/pada tanggal tersebut (ORDER BY tanggal DESC).
     * - Fallback ke harga master ref_barang.harga_satuan jika belum ada transaksi masuk s.d. tanggal tersebut.
     */
    public function syncHargaTransaksiAnomali($idGudang = null, $year = null, $month = null)
    {
        $wherePeriodTb = "1=1";
        $bindsTb = [];

        if (!empty($year) && !empty($month)) {
            $wherePeriodTb .= " AND EXTRACT(YEAR FROM tb.tanggal) = ? AND EXTRACT(MONTH FROM tb.tanggal) = ?";
            $bindsTb[] = (int)$year;
            $bindsTb[] = (int)$month;
        } elseif (!empty($year)) {
            $wherePeriodTb .= " AND EXTRACT(YEAR FROM tb.tanggal) = ?";
            $bindsTb[] = (int)$year;
        }

        if (!empty($idGudang)) {
            $wherePeriodTb .= " AND (tb.id_gudang_asal = ? OR tb.id_gudang_tujuan = ?)";
            $bindsTb[] = (int)$idGudang;
            $bindsTb[] = (int)$idGudang;
        }

        // 1. Update trans_barang: cari harga masuk resmi <= tb.tanggal
        $sqlTb = "
            UPDATE trans_barang
            SET price = sub.authentic_price
            FROM (
                SELECT
                    tb.id,
                    COALESCE(
                        -- Prioritas 1: Receive PO (trans_receive_detail) pada tanggal <= tb.tanggal
                        (
                            SELECT trd.price
                            FROM trans_receive_detail trd
                            JOIN trans_receive_header trh ON trh.id = trd.id_header
                            WHERE trd.id_barang = tb.id_barang
                              AND COALESCE(NULLIF(TRIM(trd.lot_no), ''), '-') = COALESCE(NULLIF(TRIM(tb.lot_no), ''), '-')
                              AND trd.price > 0
                              AND trd.active = 1
                              AND trh.rec_date::date <= tb.tanggal::date
                            ORDER BY trh.rec_date DESC, trh.id DESC
                            LIMIT 1
                        ),
                        -- Prioritas 2: Transaksi Masuk BTM pada tanggal <= tb.tanggal
                        (
                            SELECT tbd.price
                            FROM trans_barang_detail tbd
                            JOIN trans_barang_header tbh ON tbh.id = tbd.id_header
                            WHERE tbd.id_barang = tb.id_barang
                              AND COALESCE(NULLIF(TRIM(tbd.lot_no), ''), '-') = COALESCE(NULLIF(TRIM(tb.lot_no), ''), '-')
                              AND tbd.price > 0
                              AND tbh.status = 1
                              AND tbh.jenis_transaksi = 1
                              AND tbh.tanggal::date <= tb.tanggal::date
                            ORDER BY tbh.tanggal DESC, tbh.id DESC
                            LIMIT 1
                        ),
                        -- Prioritas 3: Fallback ke harga master ref_barang
                        (
                            SELECT rb.harga_satuan
                            FROM ref_barang rb
                            WHERE rb.id = tb.id_barang AND rb.harga_satuan > 0
                        )
                    ) AS authentic_price
                FROM trans_barang tb
                WHERE {$wherePeriodTb}
                  AND COALESCE(tb.active, 1) = 1
            ) sub
            WHERE trans_barang.id = sub.id
              AND COALESCE(trans_barang.active, 1) = 1
              AND sub.authentic_price IS NOT NULL
              AND sub.authentic_price > 0
              AND (
                  trans_barang.price IS NULL 
                  OR trans_barang.price <= 0 
                  OR (LEFT(trans_barang.kode_transaksi, 3) IN ('BTK', 'TRF') AND trans_barang.price <> sub.authentic_price)
              )
        ";
        $this->db->query($sqlTb, $bindsTb);

        // 2. Sinkronisasi balik ke trans_barang_detail (BTK / BTM) yang ada di periode ini
        $wherePeriodHdr = "1=1";
        $bindsHdr = [];
        if (!empty($year) && !empty($month)) {
            $wherePeriodHdr .= " AND EXTRACT(YEAR FROM tbh.tanggal) = ? AND EXTRACT(MONTH FROM tbh.tanggal) = ?";
            $bindsHdr[] = (int)$year;
            $bindsHdr[] = (int)$month;
        } elseif (!empty($year)) {
            $wherePeriodHdr .= " AND EXTRACT(YEAR FROM tbh.tanggal) = ?";
            $bindsHdr[] = (int)$year;
        }
        if (!empty($idGudang)) {
            $wherePeriodHdr .= " AND tbh.id_gudang = ?";
            $bindsHdr[] = (int)$idGudang;
        }

        $sqlTbd = "
            UPDATE trans_barang_detail tbd
            SET price = tb.price
            FROM trans_barang tb
            JOIN trans_barang_header tbh ON tbh.kode_transaksi = tb.kode_transaksi
            WHERE tbd.id_header = tbh.id
              AND tbd.id_barang = tb.id_barang
              AND COALESCE(NULLIF(TRIM(tbd.lot_no), ''), '-') = COALESCE(NULLIF(TRIM(tb.lot_no), ''), '-')
              AND tb.price > 0
              AND COALESCE(tb.active, 1) = 1
              AND (tbd.price IS NULL OR tbd.price <> tb.price)
              AND {$wherePeriodHdr}
        ";
        $this->db->query($sqlTbd, $bindsHdr);

        // 3. Sinkronisasi balik ke trans_transfer_detail (TRF) yang ada di periode ini
        $wherePeriodTrf = "1=1";
        $bindsTrf = [];
        if (!empty($year) && !empty($month)) {
            $wherePeriodTrf .= " AND EXTRACT(YEAR FROM tth.tanggal) = ? AND EXTRACT(MONTH FROM tth.tanggal) = ?";
            $bindsTrf[] = (int)$year;
            $bindsTrf[] = (int)$month;
        } elseif (!empty($year)) {
            $wherePeriodTrf .= " AND EXTRACT(YEAR FROM tth.tanggal) = ?";
            $bindsTrf[] = (int)$year;
        }
        if (!empty($idGudang)) {
            $wherePeriodTrf .= " AND (tth.id_gudang_asal = ? OR tth.id_gudang_tujuan = ?)";
            $bindsTrf[] = (int)$idGudang;
            $bindsTrf[] = (int)$idGudang;
        }

        $sqlTtd = "
            UPDATE trans_transfer_detail ttd
            SET price = tb.price
            FROM trans_barang tb
            JOIN trans_barang_trf_header tth ON tth.kode_transaksi = tb.kode_transaksi
            WHERE ttd.id_header = tth.id
              AND ttd.id_barang = tb.id_barang
              AND COALESCE(NULLIF(TRIM(ttd.lot_no), ''), '-') = COALESCE(NULLIF(TRIM(tb.lot_no), ''), '-')
              AND tb.price > 0
              AND COALESCE(tb.active, 1) = 1
              AND (ttd.price IS NULL OR ttd.price <> tb.price)
              AND {$wherePeriodTrf}
        ";
        $this->db->query($sqlTtd, $bindsTrf);
    }

    public function resyncMutasiDariDokumen($idGudang = null, $year = null, $month = null)
    {
        $builder = $this->db->table('trans_barang_header');
        $builder->select('id, kode_transaksi, jenis_transaksi, id_gudang, id_kategori, tanggal');
        $builder->where('status', 1);
        $builder->where('deleted_at IS NULL');
        if (!empty($idGudang)) {
            $builder->where('id_gudang', $idGudang);
        }
        if (!empty($year)) {
            $builder->where('EXTRACT(YEAR FROM tanggal) =', (int)$year);
        }
        if (!empty($month)) {
            $builder->where('EXTRACT(MONTH FROM tanggal) =', (int)$month);
        }
        $headers = $builder->get()->getResult();

        foreach ($headers as $h) {
            $hdrId = $h->id;
            $kodeTrx = $h->kode_transaksi;
            $jenisTrx = $h->jenis_transaksi;
            $idGudangHdr = $h->id_gudang;
            $idKategori = $h->id_kategori;
            $tanggal = $h->tanggal;

            // Ambil rincian resmi dari trans_barang_detail
            $details = $this->db->table('trans_barang_detail')
                ->select('id_barang, qty, price, lot_no, pack_id')
                ->where('id_header', $hdrId)
                ->get()->getResult();

            // Ambil data mutasi yang ada saat ini di trans_barang
            $existingTB = $this->db->table('trans_barang')
                ->select('id, id_barang, jumlah, lot_no')
                ->where('kode_transaksi', $kodeTrx)
                ->where('deleted_at IS NULL')
                ->where('active', 1)
                ->get()->getResult();

            $totalDetailQty = array_sum(array_map(fn($d) => (float)$d->qty, $details));
            $totalTBQty = array_sum(array_map(fn($d) => (float)$d->jumlah, $existingTB));

            // Jika ada diskrepansi Qty total atau beda jumlah baris
            if (abs($totalDetailQty - $totalTBQty) > 0.001 || count($details) !== count($existingTB)) {
                // Soft-delete mutasi lama yang bermasalah untuk transaksi ini
                $this->db->table('trans_barang')
                    ->where('kode_transaksi', $kodeTrx)
                    ->where('deleted_at IS NULL')
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'active' => 0]);

                // Insert ulang mutasi yang 100% presisi sesuai dokumen resmi
                foreach ($details as $d) {
                    $gudangAsal = ($jenisTrx == 2) ? $idGudangHdr : null;
                    $gudangTujuan = ($jenisTrx == 1) ? $idGudangHdr : null;

                    $this->db->table('trans_barang')->insert([
                        'id_barang' => $d->id_barang,
                        'jenis_transaksi' => $jenisTrx,
                        'jumlah' => $d->qty,
                        'id_gudang_asal' => $gudangAsal,
                        'id_gudang_tujuan' => $gudangTujuan,
                        'created_at' => $tanggal,
                        'active' => 1,
                        'id_kategori' => $idKategori,
                        'kode_transaksi' => $kodeTrx,
                        'stok' => 0,
                        'tipe' => 0,
                        'tanggal' => $tanggal,
                        'price' => !empty($d->price) ? $d->price : 0,
                        'lot_no' => !empty($d->lot_no) ? $d->lot_no : null,
                        'pack_id' => !empty($d->pack_id) ? $d->pack_id : null,
                    ]);
                }
            }
        }
    }

    function updateDataHistory($idJenisBarang = null, $idGudang = null, $year = null, $month = null)
    {
        // 1. perbaiki dulu tanggal trans_barang yang melenceng (berdasarkan
        // nomor transaksinya) SEBELUM histori bulanan dihitung ulang, supaya
        // snapshot trans_barang_history tidak ikut mewarisi tanggal yang salah.
        $this->syncTanggalDariKodeTransaksi($idGudang, $year, $month);

        // 2. perbaiki harga transaksi anomali (harga 0, null, atau typo) pada periode ini
        // dengan selalu mengacu ke transaksi masuk pada atau sebelum tanggal transaksi (tanggal <= T)
        $this->syncHargaTransaksiAnomali($idGudang, $year, $month);

        $dataOld =  $this->getLaporanPersediaan($idJenisBarang, $idGudang, $year, $month);
        $nextMonth = $month;
        $nextYear = $year;
        if ($month == 12) {
            $nextMonth = 1;
            $nextYear = $year + 1;
        }
        else {
            $nextMonth = $month + 1;
        }

        $prevMonth = $month;
        $prevYear = $year;
        if ($month == 12) {
            $prevMonth = 1;
            $prevYear = $year + 1;
        }
        else {
            $prevMonth = $month + 1;
        }
        
        
        $data_barang_map = [];
        $stok_awal_map = [];

        foreach ($dataOld as $value) {
            $pack_id_part = isset($value->pack_id) ? $value->pack_id : 'null';
            $combinedKey = $value->id_barang . '_' . $value->lot_no . '_' . $pack_id_part;

            if (!isset($data_barang_map[$combinedKey])) {
                $data_barang_map[$combinedKey] = [
                    "id_barang" => $value->id_barang,
                    "lot_no" => $value->lot_no,
                    "pack_id" => $value->pack_id,
                    "id_jenis_barang" => $value->id_jenis_barang,
                ];
                $stok_awal_map[$combinedKey] = 0;
            }

            if (isset($value->saldo_awal_awal) && $value->saldo_awal_awal !== '' && $value->saldo_awal_awal !== null) {
                $stok_awal_map[$combinedKey] = (float)$value->saldo_awal_awal;
            }
        }

        $data_barang = array_values($data_barang_map);
        $stok_awal = array_values($stok_awal_map);
        $pack_id = array_column($data_barang, 'pack_id');
        $lot_no = array_column($data_barang, 'lot_no');

        // Hapus snapshot lama untuk bulan dan gudang yang sedang di-update agar tidak meninggalkan baris yatim (orphan rows)
        $cleanBuilder = $this->db->table("trans_barang_history");
        $cleanBuilder->where('month', $month);
        $cleanBuilder->where('year', $year);
        if (!empty($idGudang)) {
            $cleanBuilder->where('id_gudang', $idGudang);
        }
        if (!empty($idJenisBarang)) {
            $cleanBuilder->where('id_jenis_barang', $idJenisBarang);
        }
        $cleanBuilder->delete();

        foreach ($data_barang as $key => $lot) {
            $cutoffDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            // Awal bulan SETELAH bulan yg direkap - dipakai sbg batas atas (exclusive)
            // utk subquery price/tanggal carry-forward di bawah, supaya mencakup semua
            // transaksi s.d. akhir bulan yg direkap, bukan cuma yg persis di bulan itu.
            $nextCutoffDate = sprintf('%04d-%02d-01', $nextYear, $nextMonth);
            $lot_no = $lot['lot_no'];
            $pack_id_val = $lot['pack_id'];
            $hasPackId = !empty($pack_id_val);
            // Klausa pack_id dibangun sebagai fragmen SQL tetap (bukan nilai user),
            // nilainya sendiri tetap lewat bound parameter ":pack_id:" di bawah.
            $pack_id_clause = $hasPackId ? '= :pack_id:' : 'IS NULL';
            $id_jenis_barang = $lot['id_jenis_barang'];
            $id_barang_cur = $lot['id_barang'];

            // Semua nilai dari luar (lot_no, month, year, id_gudang, id_barang, pack_id)
            // memakai bound parameter ":nama:" - sebelumnya diselipkan langsung ke
            // string SQL (rawan SQL injection), pola yang sama dengan yang sudah
            // diperbaiki di LaporanStockCardModel::getLaporanPersediaan().
            $sql = "
                SELECT
                    abx.id_barang,
                    COALESCE(NULLIF(abx.lot_no, ''), '-') AS lot_no,
                    abx.pack_id,

                    -- Subquery ambil price terakhir yg tidak null s.d. akhir bulan yang
                    -- direkap (bukan cuma di dalam bulan itu saja) - supaya kalau bulan ini
                    -- tidak ada transaksi baru utk lot ini, harga tetap dibawa dari harga
                    -- terakhir yang diketahui (mis. dari Receive Item bulan sebelumnya),
                    -- bukan jadi NULL/kosong. Difilter juga dgn id_barang supaya tidak
                    -- salah ambil punya barang lain yang lot_no-nya kebetulan sama (lot_no
                    -- diketik manual di form Receive, tidak dijamin unik antar barang).
                    --
                    -- TAMBAHAN (2026-08-08): DIPRIORITASKAN transaksi 'BTM'/'REC' (Barang
                    -- Masuk / Receive Item - sumber harga BELI asli), baru fallback ke
                    -- jenis transaksi lain (Transfer/Keluar) kalau lot itu tidak pernah
                    -- punya transaksi BTM/REC sama sekali. Ini menutup celah bug: harga
                    -- yang salah ke-input di form Transfer/Keluar (misalnya 'ketuker' dgn
                    -- barang lain) sebelumnya bisa ikut kebawa jadi 'harga terakhir' Lot
                    -- itu di sini - lalu snapshot ini dipakai lagi utk prefill form
                    -- Transfer berikutnya, jadi salahnya menyebar terus-menerus. Dengan
                    -- prioritas ini, harga selalu mengacu ke transaksi pembelian aslinya.
                    COALESCE(
                        -- Prioritas 1: Receive PO (trans_receive_detail) pada tanggal < next_cutoff
                        (
                            SELECT trd.price
                            FROM trans_receive_detail trd
                            JOIN trans_receive_header trh ON trh.id = trd.id_header
                            WHERE trd.id_barang = :id_barang:
                              AND COALESCE(NULLIF(TRIM(trd.lot_no), ''), '-') = :lot_no:
                              AND trd.price > 0
                              AND trd.active = 1
                              AND trh.rec_date < :next_cutoff:
                            ORDER BY trh.rec_date DESC, trh.id DESC
                            LIMIT 1
                        ),
                        -- Prioritas 2: Transaksi pembelian / masuk BTM/REC pada tanggal < next_cutoff
                        (
                            SELECT abx2.price
                            FROM trans_barang abx2
                            WHERE abx2.id_barang = :id_barang:
                            AND COALESCE(NULLIF(abx2.lot_no, ''), '-') = :lot_no:
                            AND abx2.pack_id $pack_id_clause
                            AND abx2.tanggal < :next_cutoff:
                            AND abx2.price IS NOT NULL
                            AND abx2.price > 0
                            AND COALESCE(abx2.active, 1) = 1
                            ORDER BY
                                CASE WHEN LEFT(abx2.kode_transaksi, 3) IN ('BTM', 'REC') THEN 0 ELSE 1 END,
                                abx2.tanggal DESC
                            LIMIT 1
                        ),
                        -- Prioritas 3: Fallback ke master ref_barang
                        (
                            SELECT rb.harga_satuan
                            FROM ref_barang rb
                            WHERE rb.id = :id_barang:
                        ),
                        0
                    ) AS price,

                    -- Subquery ambil tanggal transaksi terakhir s.d. akhir bulan yang
                    -- direkap (carry-forward sama seperti price di atas).
                    (
                        SELECT abx3.tanggal
                        FROM trans_barang abx3
                        WHERE abx3.id_barang = :id_barang:
                        AND COALESCE(NULLIF(abx3.lot_no, ''), '-') = :lot_no:
                        AND abx3.pack_id $pack_id_clause
                        AND abx3.tanggal < :next_cutoff:
                        AND COALESCE(abx3.active, 1) = 1
                        ORDER BY abx3.tanggal DESC
                        LIMIT 1
                    ) AS tanggal,

                    COALESCE(SUM(
                        CASE
                            WHEN abx.id_gudang_tujuan = :id_gudang: THEN abx.jumlah
                            ELSE 0
                        END
                    ), 0) AS total_masuk,

                    COALESCE(SUM(
                        CASE
                            WHEN abx.id_gudang_asal = :id_gudang: THEN abx.jumlah
                            ELSE 0
                        END
                    ), 0) AS total_keluar,

                    COALESCE(SUM(
                        CASE
                            WHEN abx.id_gudang_tujuan = :id_gudang: THEN abx.jumlah
                            WHEN abx.id_gudang_asal = :id_gudang: THEN -abx.jumlah
                            ELSE 0
                        END
                    ), 0) AS total_jumlah

                FROM (
                    SELECT * FROM (
                        SELECT
                            abx_inner.*,
                            ROW_NUMBER() OVER (
                                PARTITION BY
                                    abx_inner.id_barang,
                                    abx_inner.kode_transaksi,
                                    abx_inner.pack_id,
                                    abx_inner.jumlah,
                                    COALESCE(abx_inner.id_gudang_tujuan, 0),
                                    COALESCE(abx_inner.id_gudang_asal, 0)
                                ORDER BY abx_inner.created_at DESC
                            ) AS rn
                        FROM trans_barang abx_inner
                        WHERE abx_inner.id_barang = :id_barang:
                          AND COALESCE(abx_inner.active, 1) = 1
                    ) t
                    WHERE rn = 1
                ) abx

                WHERE
                    EXTRACT(MONTH FROM abx.tanggal) = :month:
                    AND EXTRACT(YEAR FROM abx.tanggal) = :year:
                    AND abx.id_barang = :id_barang:
                    AND COALESCE(NULLIF(abx.lot_no, ''), '-') = :lot_no:
                    AND abx.pack_id $pack_id_clause

                GROUP BY
                    abx.id_barang,
                    COALESCE(NULLIF(abx.lot_no, ''), '-'),
                    abx.pack_id
            ";

            $params = [
                'lot_no'      => $lot_no,
                'month'       => $month,
                'year'        => $year,
                'id_gudang'   => $idGudang,
                'id_barang'   => $id_barang_cur,
                'next_cutoff' => $nextCutoffDate,
            ];
            if ($hasPackId) {
                $params['pack_id'] = $pack_id_val;
            }

            $data = $this->db->query($sql, $params)->getRow();
            
            $jumlah = 0;
            
            
            if (isset($data)) {
                for ($i=0; $i < 2; $i++) { 
                    $saldo_awal = $stok_awal[$key];
                    // CUTOFF JULI 2026: Titik cutoff stok awal = 0 (Juni & Juli 2026)
                    if (($month == 6 || $month == 7) && $year == 2026 && $i == 0) {
                        $saldo_awal = 0;
                    }
                    $jumlah = $data->total_jumlah + $saldo_awal;
                    $masuk = $data->total_masuk;
                    $keluar = $data->total_keluar;
                    $bulan = $month;
                    $tahun = $year;
                    if (($i == 1)) {
                        // CUTOFF JULI 2026: Jika bulan berikutnya adalah Juli 2026 (cutoff) atau sebelumnya, jangan bawa saldo
                        if ($nextYear < 2026 || ($nextYear == 2026 && $nextMonth <= 7)) {
                            continue;
                        }
                        $masuk = 0;
                        $keluar = 0;
                        $saldo_awal = $jumlah;
                        $bulan = $nextMonth;
                        $tahun = $nextYear;
                    }
                    $isi['id_barang'] = $data->id_barang;
                    $isi['id_jenis_barang'] = !empty($idJenisBarang) ? $idJenisBarang : $id_jenis_barang;
                    $isi['lot_no'] = !empty($lot_no) ? $lot_no : '-';
                    $isi['pack_id'] = $pack_id[$key];
                    $isi['stok_awal'] = $saldo_awal;
                    $isi['masuk'] = round($masuk, 2);
                    $isi['keluar'] = round($keluar, 2);
                    $isi['month'] = $bulan;
                    $isi['year'] = $tahun;
                    $isi['jumlah'] = round($jumlah, 2);
                    $isi['id_gudang'] = $idGudang;
                    $isi['tanggal'] = $data->tanggal;
                    $isi['price'] = $data->price;
                    $builder_detail = $this->db->table("trans_barang_history abx");
                    $builder_detail->where('abx.month', $bulan);
                    $builder_detail->where('abx.year', $tahun);
                    $builder_detail->where("COALESCE(NULLIF(abx.lot_no, ''), '-')", $lot_no);
                    $builder_detail->where('abx.id_barang', $data->id_barang);
                    // id_gudang WAJIB ikut di filter ini - tanpanya, baris histori gudang
                    // LAIN dgn barang/lot/pack/bulan/tahun yg sama ikut kebaca "sudah ada"
                    // lalu ke-UPDATE dgn data gudang ini (menimpa data gudang lain).
                    $builder_detail->where('abx.id_gudang', $idGudang);
                    if (!empty($pack_id[$key])) {
                        $builder_detail->where('abx.pack_id', $pack_id[$key]);
                    } else {
                        $builder_detail->where('abx.pack_id IS NULL');
                    }
                    $builder_detail->select("*");

                    $detail = $builder_detail->get()->getRow();
                    if (empty($detail)) {
                        $builder_detail_insert = $this->db->table("trans_barang_history");
                        if ($builder_detail_insert->insert($isi) === false) {
                            $error = $this->db->error();
                            log_message('error', 'Insert trans_barang_history failed: ' . json_encode($error));
                            return false;
                        }
                    }
                    else {
                        $builder_update = $this->db->table("trans_barang_history");
                        $builder_update->where("month", $bulan);
                        $builder_update->where("year", $tahun);
                        $builder_update->where("COALESCE(NULLIF(lot_no, ''), '-')", $lot_no);
                        $builder_update->where("id_barang", $data->id_barang);
                        $builder_update->where("id_gudang", $idGudang);
                        if (!empty($pack_id[$key])) {
                            $builder_update->where("pack_id", $pack_id[$key]);
                        } else {
                            $builder_update->where("pack_id IS NULL");
                        }
                        $builder_update->update($isi);
                    }
                }
                
            }
        }

        // CUTOFF JULI 2026: Jika merekap bulan <= Juni 2026, hentikan carry forward ke Juli 2026
        if ($nextYear < 2026 || ($nextYear == 2026 && $nextMonth <= 7)) {
            return true;
        }

        $resData = $this->getDataGudang($idJenisBarang, $idGudang, $year, $month);
        foreach ($resData as $key => $value) {

            $paramsNext = array(
                "id_barang" => $value->id_barang,
                "lot_no" => $value->lot_no,
                "pack_id" => $value->pack_id,
            );
            $getNextData = $this->getDataGudang($idJenisBarang, $idGudang, $nextYear, $nextMonth, $paramsNext);
            // Stok Awal bulan depan HARUS sama dengan Stok Akhir (jumlah) bulan ini - carry
            // forward. Sebelumnya kode ini keliru menyalin stok_awal/masuk/keluar/jumlah bulan
            // INI apa adanya ke baris bulan depan, jadi Awal bulan depan bisa beda dgn Akhir
            // bulan ini (bug yg dilaporkan user).
            if (empty($getNextData)) {
                $isi['id_barang'] = $value->id_barang;
                $isi['id_jenis_barang'] = $value->id_jenis_barang;
                $isi['lot_no'] = $value->lot_no;
                $isi['pack_id'] = $value->pack_id;
                $isi['stok_awal'] = $value->jumlah;
                $isi['masuk'] = 0;
                $isi['keluar'] = 0;
                $isi['month'] = $nextMonth;
                $isi['year'] = $nextYear;
                $isi['jumlah'] = $value->jumlah;
                $isi['id_gudang'] = $idGudang;
                $isi['tanggal'] = $value->tanggal;
                $isi['price'] = $value->price;

                $builder_detail_insert = $this->db->table("trans_barang_history");
                if ($builder_detail_insert->insert($isi) === false) {
                    $error = $this->db->error();
                    log_message('error', 'Insert next month trans_barang_history failed: ' . json_encode($error));
                    return false;
                }
            } else {
                // Baris bulan depan sudah ada (mis. sudah pernah di-generate terpisah) -
                // pastikan stok_awal-nya tetap sinkron dgn Akhir bulan ini, dan hitung ulang
                // jumlah (akhir) bulan depan supaya konsisten - TANPA mengubah masuk/keluar
                // yang sudah tercatat (itu transaksi riil bulan tsb, tidak boleh disentuh).
                foreach ($getNextData as $nextRow) {
                    $newStokAwal = $value->jumlah;
                    $newJumlah = $newStokAwal + (float) $nextRow->masuk - (float) $nextRow->keluar;
                    if ((float) $nextRow->stok_awal !== (float) $newStokAwal) {
                        $builder_update_next = $this->db->table("trans_barang_history");
                        $builder_update_next->where("id", $nextRow->id);
                        $builder_update_next->update([
                            "stok_awal" => $newStokAwal,
                            "jumlah" => $newJumlah,
                        ]);
                    }
                }
            }
        }
        return true;
    }

    function getDataPersediaanBarang($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $where = "WHERE 1=1 AND b.active = 1";
        // CUTOFF JULI 2026: semua riwayat sebelum Juli 2026 tidak boleh masuk
        $where .= " AND (a.year * 100 + a.month) >= 202607";

        if (!empty($params['id_gudang'])) {
            $where .= " AND a.id_gudang = " . intval($params['id_gudang']);
        }

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $keyword = strtolower($filters[0]['value']);
            $keyword = $this->db->escapeLikeString($keyword);
            $where .= " AND (
                LOWER(b.kode_barang) LIKE '%{$keyword}%'
                OR LOWER(b.nama_barang) LIKE '%{$keyword}%'
                OR LOWER(a.lot_no) LIKE '%{$keyword}%'
                OR LOWER(e.pack_name) LIKE '%{$keyword}%'
            )";
        }
        if (!empty($params['kode_walkorder'])) {
            $kode_walkorder = $params['kode_walkorder'];
            $where .= " AND b.id IN (
                SELECT tww.id_barang
                FROM trans_walkorder_warna tww
                INNER JOIN trans_walkorder_detail twd ON tww.id_walkorder_detail = twd.id
                INNER JOIN trans_walkorder tw on twd.id_walkorder = tw.id
                WHERE tw.kode_walkorder = '{$kode_walkorder}'
            )";
        }

        $innerSql = "
            SELECT DISTINCT ON (a.id_barang, a.pack_id, COALESCE(NULLIF(a.lot_no, ''), '-'))
                a.id_barang,
                a.month,
                a.year,
                a.lot_id,
                a.pack_id,
                e.pack_name,
                COALESCE(NULLIF(a.lot_no, ''), '-') AS lot_no,
                a.jumlah AS qty,
                COALESCE(
                    (SELECT trd.price FROM trans_receive_detail trd JOIN trans_receive_header trh ON trh.id = trd.id_header WHERE trd.id_barang = a.id_barang AND trd.lot_no = a.lot_no AND trd.price > 0 AND trd.active = 1 ORDER BY trh.id DESC LIMIT 1),
                    NULLIF(a.price, 0),
                    b.harga_satuan,
                    0
                ) AS price,
                c.nama_satuan,
                b.nama_barang,
                b.kode_barang,
                b.harga_satuan
            FROM trans_barang_history a
            INNER JOIN ref_barang b         ON a.id_barang = b.id
            INNER JOIN ref_satuan c         ON b.id_satuan = c.id
            INNER JOIN ref_jenis_barang d   ON b.id_jenis_barang = d.id
            left JOIN ref_pack e            ON a.pack_id = e.id
            {$where}
            ORDER BY a.id_barang, a.pack_id, COALESCE(NULLIF(a.lot_no, ''), '-'), (a.year * 100 + a.month) DESC
        ";

        if ($id == null || $id == "") {

            $orderBy = "sub.nama_barang ASC";
            if (!empty($order)) {
                $field = $order[0]['field'];
                $dir   = strtoupper($order[0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
                $orderBy = "sub.{$field} {$dir}";
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit))  $limit  = 10;

            $sql = "
                SELECT * FROM ({$innerSql}) sub
                WHERE sub.qty > 0
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}
            ";

            $this->_data = $this->db->query($sql)->getResult();

        } else {
            $sql = "
                SELECT * FROM ({$innerSql}) sub
                WHERE sub.id_barang = " . intval($id) . "
            ";

            $this->_data = $this->db->query($sql)->getRow();
        }

        return $this->_data;
    }

    function getDataPersediaanBarangCnt($filters = null, $params = null)
    {
        $subQuery = $this->db->table('trans_barang_history')
        ->select("id_barang, MAX(year * 100 + month) as max_period")
        ->where("(year * 100 + month) >= 202607")
        ->groupBy("id_barang");
        $builder = $this->db->table("trans_barang_history a");
        $builder->join("ref_barang b", "a.id_barang = b.id", "inner");
        $builder->join("ref_satuan c", "b.id_satuan = c.id", "inner");
        $builder->join("ref_jenis_barang d", "b.id_jenis_barang = d.id", "inner");
        $builder->join("ref_pack f", "a.pack_id = f.id", "left");
        $builder->join("({$subQuery->getCompiledSelect()}) e", 
            'a.id_barang = e.id_barang AND (a.year * 100 + a.month) = e.max_period', 
            'inner');
        $builder->select("count(1) as _cnt");
        $builder->where('b.active = 1');
        $builder->where('a.jumlah > 0');
        // CUTOFF JULI 2026: semua riwayat sebelum Juli 2026 tidak boleh masuk
        $builder->where('(a.year * 100 + a.month) >= 202607');

        if (!empty($params['id_gudang'])) {
            $builder->where('a.id_gudang', $params['id_gudang']);
        }

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->Where('LOWER(b.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(b.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(a.lot_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(f.pack_name) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        if (!empty($params['kode_walkorder'])) {
            $kode_walkorder = $params['kode_walkorder'];
            $builder->whereIn('b.id', function($subBuilder) use ($kode_walkorder) {
                $subBuilder->select('tww.id_barang')
                    ->from('trans_walkorder_warna tww')
                    ->join('trans_walkorder_detail twd', 'tww.id_walkorder_detail = twd.id', 'inner')
                    ->join('trans_walkorder tw', 'twd.id_walkorder = tw.id', 'inner')
                    ->where('tw.kode_walkorder', $kode_walkorder);
                return $subBuilder;
            });
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
