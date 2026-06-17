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

    function getLaporanPersediaan($idJenisBarang = null, $idGudang = null, $year = null, $month = null)
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

        $sql = "WITH normalized_trans as ( SELECT
                    abx.tanggal,
                    abx.created_at,
                    abx.kode_transaksi,
                    abx.id_barang,
                    abx.id_gudang_asal AS id_gudang,
                    'keluar' AS jenis_transaksi,
                    abx.jumlah * -1 AS jumlah,
                    abx.id,
                    bbx.name,
                    cbx.lot_no,
                    abx.lot_id,
                    abx.pack_id,
                    rp.pack_name,
                    dbx.nama_barang,
                    dbx.kode_barang,
                    ebx.nama_jenis_barang,
                    fbx.nama_satuan,
                    abx.price,
                    dbx.id_jenis_barang
                FROM
                    trans_barang abx
                    LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
                    LEFT JOIN ref_pack rp ON abx.pack_id = rp.id
                    INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
                    INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
                    INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
                    INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
                WHERE
                    abx.id_gudang_asal IS NOT null
                    AND EXTRACT(MONTH FROM  abx.tanggal) = $month
                    AND EXTRACT(YEAR FROM  abx.tanggal) = $year
                    AND abx.id_gudang_asal = $idGudang
                    $idJenisBarangSql
                    --AND abx.id_barang = 226 
                    
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
                    cbx.lot_no,
                    abx.lot_id,
                    abx.pack_id,
                    rp.pack_name,
                    dbx.nama_barang,
                    dbx.kode_barang,
                    ebx.nama_jenis_barang,
                    fbx.nama_satuan,
                    abx.price,
                    dbx.id_jenis_barang
                FROM
                    trans_barang abx 
                    LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
                    LEFT JOIN ref_pack rp ON abx.pack_id = rp.id
                    INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
                    INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
                    INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
                    INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
                WHERE
                    abx.id_gudang_tujuan IS NOT null
                    AND EXTRACT(MONTH FROM  abx.tanggal) = $month
                    AND EXTRACT(YEAR FROM  abx.tanggal) = $year
                    AND abx.id_gudang_tujuan = $idGudang
                    $idJenisBarangSql
                    --AND abx.id_barang = 161
                    
                    ),

                history_per_lot AS (
                    SELECT
                    lot_id,
                    lot_no,
                    pack_id,
                    id_barang,
                    month,
                    year,
                    stok_awal AS saldo_awal
                FROM trans_barang_history
                ),

                normalized_trans_with_history AS (
                    SELECT
                        nt.*,
                        COALESCE(h.saldo_awal, 0) AS saldo_awal_history
                    FROM normalized_trans nt
                    LEFT JOIN history_per_lot h
                        ON nt.lot_no = h.lot_no
                    AND nt.id_barang = h.id_barang
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
                        nt.id_jenis_barang,
                        nt.id_barang,
                        nt.id_gudang,
                        nt.lot_id,
                        nt.lot_no,
                        nt.pack_id,
                        nt.pack_name,
                        nt.nama_barang,
                        nt.kode_barang,
                        nt.nama_jenis_barang,
                        nt.nama_satuan,
                        nt.price,
                        CONCAT(nt.kode_barang, ' ', nt.nama_barang) AS barang,
                        CAST(CASE WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah ELSE 0 END AS DECIMAL(18,2)) AS masuk,
                        CAST(CASE WHEN nt.jenis_transaksi = 'keluar' THEN -nt.jumlah ELSE 0 END AS DECIMAL(18,2)) AS keluar,
                        
                        CAST(
                            CASE 
                                WHEN ROW_NUMBER() OVER(PARTITION BY nt.id_barang, nt.lot_no ORDER BY nt.id ASC) = 1 
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
                                PARTITION BY nt.id_barang, nt.lot_no ORDER BY nt.id ASC
                                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                            ) + 
                            FIRST_VALUE(nt.saldo_awal_history) OVER (PARTITION BY nt.id_barang, nt.lot_no ORDER BY nt.id ASC)
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
                                LAG(sb.saldo_akhir) OVER (PARTITION BY sb.id_barang, sb.lot_no ORDER BY sb.id)
                            ) AS DECIMAL(18,2)
                        ) AS saldo_awal
                    FROM stock_base sb
                )

            SELECT *
            FROM stock_card
            --where id_barang = 226
            ORDER BY
                nama_jenis_barang ASC,
                nama_barang ASC,
                lot_no ASC,
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

    function getDataGudang($idJenisBarang = null, $idGudang = null, $year = null, $month = null, $params = null)
    {
        // dd($idJenisBarang);
        $builder = $this->db->table("trans_barang_history a");
        $builder->select("a.*, b.nama_barang, c.nama_jenis_barang, a.pack_id, rp.pack_name, b.kode_barang || ' ' || b.nama_barang as barang, CAST(a.jumlah AS DECIMAL(18,2)) as saldo_akhir, CAST(a.stok_awal AS DECIMAL(18,2)) as saldo_awal");
        $builder->join("ref_barang b", "a.id_barang = b.id", "inner");
        $builder->join("ref_jenis_barang c", "a.id_jenis_barang = c.id", "inner");
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
            $builder->where("a.lot_no", $params['lot_no']);
        }
        if (!empty($params['pack_id'])) {
            $builder->where("a.pack_id", $params['pack_id']);
        }
        if (!empty($params['lot_id'])) {
            $builder->where("a.lot_id", $params['lot_id']);
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
                    a.jumlah,
                    a.stok_awal,
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

    function updateDataHistory($idJenisBarang = null, $idGudang = null, $year = null, $month = null)
    {
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
        
        

        $lot_id = [];
        $pack_id = [];
        $lot_no = [];
        $stok_awal = [];
        $data_barang = [];
        foreach ($dataOld as $key => $value) {
            // Buat key gabungan unik
            $pack_id_part = isset($value->pack_id) ? $value->pack_id : 'null';
            $combinedKey = $value->id_barang . '_' . $value->lot_no . '_' . $pack_id_part;

            // Cek apakah kombinasi ini sudah pernah dimasukkan
            if (isset($existing_keys[$combinedKey])) {
                continue; // skip jika sudah ada
            }

            // Simpan data jika belum ada
            $data_barang[] = [
                "id_barang" => $value->id_barang,
                "lot_no" => $value->lot_no,
                "pack_id" => $value->pack_id,
                "id_jenis_barang" => $value->id_jenis_barang,
            ];

            $lot_id[] = $value->lot_id;
            $pack_id[] = $value->pack_id;
            $stok_awal[] = isset($value->saldo_awal_awal) ? $value->saldo_awal_awal : 0;
            $lot_no[] = $value->lot_no;

            // Tandai kombinasi ini sudah diproses
            $existing_keys[$combinedKey] = true;
        }

        foreach ($data_barang as $key => $lot) {
            $cutoffDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $lot_no = $lot['lot_no'];
            $pack_id_cur = $lot['pack_id'];
            $id_jenis_barang = $lot['id_jenis_barang'];
            $builder = $this->db->table("trans_barang abx");
            $builder->join("trans_lots tl", "abx.lot_id = tl.id", "inner");
            $builder->select("
                abx.id_barang,
                tl.lot_no,
                abx.pack_id,

                -- Subquery untuk ambil price terakhir yg tidak null
                (
                    SELECT abx2.price
                    FROM trans_barang abx2
                    INNER JOIN trans_lots cbx ON abx2.lot_id = cbx.id
                    WHERE cbx.lot_no = '$lot_no'
                    and abx2.pack_id = $pack_id_cur
                    AND EXTRACT(MONTH FROM abx2.tanggal) = $month
                    AND EXTRACT(YEAR FROM abx2.tanggal) = $year
                    AND abx2.price IS NOT NULL
                    ORDER BY abx2.tanggal DESC
                    LIMIT 1
                ) AS price,

                -- Subquery untuk ambil tanggal terbaru
                (
                    SELECT abx3.tanggal
                    FROM trans_barang abx3
                    INNER JOIN trans_lots cbx2 ON abx3.lot_id = cbx2.id
                    WHERE cbx2.lot_no = '$lot_no'
                    and abx3.pack_id = $pack_id_cur
                    AND EXTRACT(MONTH FROM abx3.tanggal) = $month
                    AND EXTRACT(YEAR FROM abx3.tanggal) = $year
                    ORDER BY abx3.tanggal DESC
                    LIMIT 1
                ) AS tanggal,

                COALESCE(SUM(
                    CASE 
                        WHEN abx.id_gudang_tujuan = $idGudang THEN abx.jumlah
                        ELSE 0
                    END
                ), 0) as total_masuk,

                COALESCE(SUM(
                    CASE 
                        WHEN abx.id_gudang_asal = $idGudang THEN abx.jumlah
                        ELSE 0
                    END
                ), 0) as total_keluar,

                COALESCE(SUM(
                    CASE 
                        WHEN abx.id_gudang_tujuan = $idGudang THEN abx.jumlah
                        WHEN abx.id_gudang_asal = $idGudang THEN -abx.jumlah
                        ELSE 0
                    END
                ), 0) as total_jumlah
            ");
            $builder->where("EXTRACT(MONTH FROM abx.tanggal)", $month);
            $builder->where("EXTRACT(YEAR FROM abx.tanggal)", $year);
            $builder->where("abx.id_barang", $lot['id_barang']);
            $builder->where("tl.lot_no", $lot['lot_no']);
            $builder->where("abx.pack_id", $lot['pack_id']);
            $builder->groupBy("abx.id_barang, tl.lot_no, abx.pack_id");
            
            
            $data = $builder->get()->getRow();
            // if ($lot == 'K38T34') {
            //     dd($data, $stok_awal[$key]);
            // }
            
            $jumlah = 0;
            
            
            if (isset($data)) {
                for ($i=0; $i < 2; $i++) { 
                    $saldo_awal= $stok_awal[$key];
                    $jumlah = $data->total_jumlah + $saldo_awal;
                    $masuk = $data->total_masuk;
                    $keluar = $data->total_keluar;
                    $bulan = $month;
                    $tahun = $year;
                    if (($i == 1)) {
                        $masuk = 0;
                        $keluar = 0;
                        $saldo_awal = $jumlah;
                        $bulan = $nextMonth;
                        $tahun = $nextYear;
                    }
                    $isi['id_barang'] = $data->id_barang;
                    $isi['id_jenis_barang'] = !empty($idJenisBarang) ? $idJenisBarang : $id_jenis_barang;
                    $isi['lot_id'] = $lot_id[$key];
                    $isi['lot_no'] = $lot_no;
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
                    $builder_detail->where('abx.lot_no', $lot_no);
                    $builder_detail->where('abx.pack_id', $pack_id[$key]);
                    $builder_detail->where('abx.id_barang', $data->id_barang);
                    // $builder_detail->where('abx.id_trans_barang', $value->id);
                    $builder_detail->select("*");

                    $detail = $builder_detail->get()->getRow();
                    if (empty($detail)) {
                        $builder_detail_insert = $this->db->table("trans_barang_history");
                        if ($builder_detail_insert->insert($isi) === false) {
                            $error = $this->db->error();
                            var_dump($error);
                            die;
                        }
                    }
                    else {
                        $this->db->table("trans_barang_history")->update($isi, array("month" => $bulan, "year" => $tahun, "lot_no" => $lot_no, "id_barang" => $data->id_barang, "pack_id" => $pack_id[$key]));
                    }
                }
                
            }
        }

        $resData = $this->getDataGudang($idJenisBarang, $idGudang, $year, $month);
        foreach ($resData as $key => $value) {
            
            $paramsNext = array(
                "id_barang" => $value->id_barang,
                "lot_no" => $value->lot_no,
                "lot_id" => $value->lot_id,
                "pack_id" => $value->pack_id,
            );
            $getNextData = $this->getDataGudang($idJenisBarang, $idGudang, $nextYear, $nextMonth, $paramsNext);
            if (empty($getNextData)) {
                $isi['id_barang'] = $value->id_barang;
                $isi['id_jenis_barang'] = $value->id_jenis_barang;
                $isi['lot_id'] = $value->lot_id;
                $isi['lot_no'] = $value->lot_no;
                $isi['pack_id'] = $value->pack_id;
                $isi['stok_awal'] = $value->stok_awal;
                $isi['masuk'] = $value->masuk;
                $isi['keluar'] = $value->keluar;
                $isi['month'] = $nextMonth;
                $isi['year'] = $nextYear;
                $isi['jumlah'] = $value->jumlah;
                $isi['id_gudang'] = $idGudang;
                $isi['tanggal'] = $value->tanggal;
                $isi['price'] = $value->price;

                $builder_detail_insert = $this->db->table("trans_barang_history");
                if ($builder_detail_insert->insert($isi) === false) {
                    $error = $this->db->error();
                    var_dump($error);
                    die;
                }
            }
        }
        return true;
    }

    function getDataPersediaanBarang($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $where = "WHERE 1=1";

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

        $innerSql = "
            SELECT DISTINCT ON (a.id_barang, a.pack_id, a.lot_no)
                a.id_barang,
                a.month,
                a.year,
                a.lot_id,
                a.pack_id,
                e.pack_name,
                a.lot_no,
                a.jumlah AS qty,
                a.price,
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
            ORDER BY a.id_barang, a.pack_id, a.lot_no, (a.year * 100 + a.month) DESC
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
        // $builder->where('uk.active = 1');
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

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
}
