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

    function getLaporanStockCard($idBarang = null, $idGudang = null, $year = null, $month = null)
    {
        $params = [];
        $sql = "
        WITH normalized_trans AS (
            SELECT
                abx.tanggal,
                abx.created_at,
                abx.kode_transaksi,
                abx.id_barang,
                abx.id_gudang_asal AS id_gudang,
                'keluar' AS jenis_transaksi,
                abx.jumlah * -1 AS jumlah,
                abx.id,
                bbx.name
            FROM
                trans_barang abx
                LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            WHERE
                abx.id_gudang_asal IS NOT NULL ";
            if (!empty($month)) {
                $sql .= " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan:";
                $params['bulan'] = $month;
            }
            if (!empty($year)) {
                $sql .= " AND EXTRACT(YEAR FROM  abx.tanggal) = :tahun:";
                $params['tahun'] = $year;
            }
            if (!empty($idGudang)) {
                $sql .= " AND abx.id_gudang_asal = :id_gudang:";
                $params['id_gudang'] = $idGudang;
            }
            if (!empty($idBarang)) {
                $sql .= "AND abx.id_barang = :id_barang: ";
                $params['id_barang'] = $idBarang;
            }
            $sql .= " UNION ALL
            SELECT
                abx.tanggal,
                abx.created_at,
                abx.kode_transaksi,
                abx.id_barang,
                abx.id_gudang_tujuan AS id_gudang,
                'masuk' AS jenis_transaksi,
                abx.jumlah AS jumlah,
                abx.id,
                bbx.name
            FROM
                trans_barang abx 
                LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            WHERE
                abx.id_gudang_tujuan IS NOT NULL ";
            if (!empty($month)) {
                $sql .= " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan:";
                $params['bulan'] = $month;
            }
            if (!empty($year)) {
                $sql .= " AND EXTRACT(YEAR FROM  abx.tanggal) = :tahun:";
                $params['tahun'] = $year;
            }
            if (!empty($idGudang)) {
                $sql .= " AND abx.id_gudang_tujuan = :id_gudang:";
                $params['id_gudang'] = $idGudang;
            }
            if (!empty($idBarang)) {
                $sql .= "AND abx.id_barang = :id_barang: ";
                $params['id_barang'] = $idBarang;
            }
            $sql .= " ),
        saldo_awal AS (
            SELECT
                nt.id_barang,
                nt.id_gudang,
                SUM(nt.jumlah) AS saldo_awal
            FROM
                normalized_trans nt ";
            if (!empty($idGudang)) {
                $sql .= "WHERE nt.id_gudang = :id_gudang: ";
                $params['id_gudang'] = $idGudang;
            }

            if (!empty($idBarang)) {
                $sql .= "AND nt.id_barang = :id_barang: ";
                $params['id_barang'] = $idBarang;
            }



        $sql .= " GROUP BY
                nt.id_barang,nt.id_gudang
        ),
        
        stock_card AS (
            SELECT
                nt.tanggal,
                nt.created_at,
                nt.name as transaksi,
                nt.kode_transaksi,
                nt.id_barang,
                nt.id_gudang,
                CASE
                    WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah 
                    ELSE 0 
                END AS masuk,
                CASE
                    WHEN nt.jenis_transaksi = 'keluar' THEN -nt.jumlah 
                    ELSE 0 
                END AS keluar,
                SUM(nt.jumlah) OVER (
                    PARTITION BY nt.id_barang,  nt.id_gudang
                    ORDER BY nt.id ASC, nt.kode_transaksi
                ) AS saldo_akhir
            FROM
                normalized_trans nt
                LEFT JOIN saldo_awal sa 
                    ON nt.id_barang = sa.id_barang 
                    AND nt.id_gudang = sa.id_gudang
        )
        SELECT *
        FROM stock_card ";


        $sql .=  " 
            ORDER BY
                created_at ASC,
                id_barang,
                id_gudang,
                tanggal,
                kode_transaksi";
        $query = $this->db->query($sql, $params);

        $this->_data  = $query->getResult();
        return $this->_data;
    }

    function getLaporanStockCardOldNew($idBarang = null, $idGudang = null, $year = null, $month = null)
    {
        $params = [];
        $sql = "
        WITH normalized_trans AS (
            SELECT
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
                abx.lot_id
            FROM
                trans_barang abx
                LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
                INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
            WHERE
                abx.id_gudang_asal IS NOT NULL ";
            if (!empty($month)) {
                $sql .= " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan:";
                $params['bulan'] = $month;
            }
            if (!empty($year)) {
                $sql .= " AND EXTRACT(YEAR FROM  abx.tanggal) = :tahun:";
                $params['tahun'] = $year;
            }
            if (!empty($idGudang)) {
                $sql .= " AND abx.id_gudang_asal = :id_gudang:";
                $params['id_gudang'] = $idGudang;
            }
            if (!empty($idBarang)) {
                $sql .= "AND abx.id_barang = :id_barang: ";
                $params['id_barang'] = $idBarang;
            }
            $sql .= " UNION ALL
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
                abx.lot_id
            FROM
                trans_barang abx 
                LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
                INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
            WHERE
                abx.id_gudang_tujuan IS NOT NULL ";
            if (!empty($month)) {
                $sql .= " AND EXTRACT(MONTH FROM abx.tanggal) = :bulan:";
                $params['bulan'] = $month;
            }
            if (!empty($year)) {
                $sql .= " AND EXTRACT(YEAR FROM  abx.tanggal) = :tahun:";
                $params['tahun'] = $year;
            }
            if (!empty($idGudang)) {
                $sql .= " AND abx.id_gudang_tujuan = :id_gudang:";
                $params['id_gudang'] = $idGudang;
            }
            if (!empty($idBarang)) {
                $sql .= "AND abx.id_barang = :id_barang: ";
                $params['id_barang'] = $idBarang;
            }
            $sql .= " ),
        saldo_awal AS (
            SELECT
                nt.id_barang,
                nt.id_gudang,
                nt.lot_id,
                SUM(nt.jumlah) AS saldo_awal
            FROM
                normalized_trans nt ";
            if (!empty($idGudang)) {
                $sql .= "WHERE nt.id_gudang = :id_gudang: ";
                $params['id_gudang'] = $idGudang;
            }

            if (!empty($idBarang)) {
                $sql .= "AND nt.id_barang = :id_barang: ";
                $params['id_barang'] = $idBarang;
            }



        $sql .= " GROUP BY
                nt.id_barang,nt.id_gudang,nt.lot_id
        ),
        
        stock_card AS (
            SELECT
                nt.tanggal,
                nt.created_at,
                nt.name as transaksi,
                nt.kode_transaksi,
                nt.id_barang,
                nt.id_gudang,
                nt.lot_id,
                nt.lot_no,
                CASE
                    WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah 
                    ELSE 0 
                END AS masuk,
                CASE
                    WHEN nt.jenis_transaksi = 'keluar' THEN -nt.jumlah 
                    ELSE 0 
                END AS keluar,
                SUM(nt.jumlah) OVER (
                    PARTITION BY nt.id_barang,  nt.id_gudang,nt.lot_id
                    ORDER BY nt.id ASC, nt.kode_transaksi
                ) AS saldo_akhir
            FROM
                normalized_trans nt
                LEFT JOIN saldo_awal sa 
                    ON nt.id_barang = sa.id_barang 
                    AND nt.id_gudang = sa.id_gudang
                    AND nt.lot_id = sa.lot_id
        )
        SELECT *
        FROM stock_card ";


        $sql .=  " 
            ORDER BY
                created_at ASC,
                id_barang,
                id_gudang,
                tanggal,
                kode_transaksi";
        $query = $this->db->query($sql, $params);

        $this->_data  = $query->getResult();
        return $this->_data;
    }

    function getLaporanStockCardOld($idBarang = null, $tglMulai = null, $tglAkhir = null)
    {
        $subQuery = $this->db->table('trans_barang t2')
            ->select("
        SUM(CASE WHEN t2.jenis_transaksi = '1' THEN t2.jumlah ELSE 0 END) -
        SUM(CASE WHEN t2.jenis_transaksi = '2' THEN t2.jumlah ELSE 0 END)
    ", false)
            ->where('t2.id_barang = t.id_barang')
            ->where('t2.id_gudang_tujuan = t.id_gudang_tujuan')
            ->where('t2.tanggal <= t.tanggal');

        // Main query
        $builder = $this->db->table('trans_barang t');
        $builder->select("
    t.tanggal,
    kt.kategori,
    t.keterangan,
    t.stok as saldo,
    lt.nama_barang,
    CASE 
        WHEN t.jenis_transaksi = '1' THEN ht.nama_gudang
        WHEN t.jenis_transaksi = '2' THEN gt.nama_gudang
        ELSE NULL 
    END AS nama_gudang,
    CASE 
        WHEN t.jenis_transaksi = '1' THEN t.jumlah 
        ELSE 0 
    END AS masuk,
    CASE 
        WHEN t.jenis_transaksi = '2' THEN t.jumlah 
        ELSE 0 
    END AS keluar,
    ({$subQuery->getCompiledSelect()}) AS saldo
", false)
            ->join('ref_kategori_persediaan kt', 't.id_kategori = kt.id', 'inner')
            ->join('ref_gudang gt', 't.id_gudang_asal = gt.id', 'left')
            ->join('ref_gudang ht', 't.id_gudang_tujuan = ht.id', 'left')
            ->join('ref_barang lt', 't.id_barang = lt.id', 'left');
        $builder->where('t.active', 1);
        $builder->orderBy('t.tanggal', 'DESC');
        if (!empty($idBarang)) {
            $builder->where('t.id_barang', $idBarang);
        }
        if (!empty($tglMulai)) {
            $builder->where('t.tanggal>=', $tglMulai);
        }
        if (!empty($tglAkhir)) {
            $builder->where('t.tanggal<=', $tglAkhir);
        }

        $query = $builder->get();
        $this->_data  = $query->getResult();
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

    function getLaporanPersediaan($idBarang = null, $idGudang = null, $year = null, $month = null)
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
            abx.price
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
            AND abx.id_barang = $idBarang
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
            abx.price
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
            AND abx.id_barang = $idBarang
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
                AND nt.pack_id = h.pack_id
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
                nt.id_barang,
                nt.id_gudang,
                nt.lot_id,
                nt.pack_name,
                nt.lot_no,
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
            SELECT DISTINCT ON (sb.id_barang, sb.lot_no, sb.transaksi, sb.kode_transaksi, sb.pack_name, sb.nama_satuan)
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

    function getLaporanPersediaanNew($idBarang = null, $idGudang = null, $year = null, $month = null)
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

        $sql = "
        WITH normalized_trans as ( 
            SELECT
                abx.tanggal,
                abx.created_at,
                abx.kode_transaksi,
                abx.id_barang,
                abx.id_gudang_asal AS id_gudang,
                'keluar' AS jenis_transaksi,
                abx.jumlah * -1 AS jumlah,
                abx.id,
                bbx.name,
                dbx.nama_barang,
                dbx.kode_barang,
                ebx.nama_jenis_barang,
                fbx.nama_satuan,
                abx.price
            FROM trans_barang abx
            LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
            INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
            INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
            WHERE abx.id_gudang_asal IS NOT null
            AND EXTRACT(MONTH FROM  abx.tanggal) = $month
            AND EXTRACT(YEAR FROM  abx.tanggal) = $year
            AND abx.id_gudang_asal = $idGudang
            AND abx.id_barang = $idBarang
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
                dbx.nama_barang,
                dbx.kode_barang,
                ebx.nama_jenis_barang,
                fbx.nama_satuan,
                abx.price
            FROM trans_barang abx 
            LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
            INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
            INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
            WHERE abx.id_gudang_tujuan IS NOT null
            AND EXTRACT(MONTH FROM  abx.tanggal) = $month
            AND EXTRACT(YEAR FROM  abx.tanggal) = $year
            AND abx.id_gudang_tujuan = $idGudang
            AND abx.id_barang = $idBarang
            --AND abx.id_barang = 161
        ),

        history_per_lot AS (
            SELECT
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
            LEFT JOIN history_per_lot h ON nt.id_barang = h.id_barang
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
                        WHEN ROW_NUMBER() OVER(PARTITION BY nt.id_barang ORDER BY nt.id ASC) = 1 
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
                        PARTITION BY nt.id_barang ORDER BY nt.id ASC
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                    ) + 
                    FIRST_VALUE(nt.saldo_awal_history) OVER (PARTITION BY nt.id_barang ORDER BY nt.id ASC)
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
                        LAG(sb.saldo_akhir) OVER (PARTITION BY sb.id_barang ORDER BY sb.id)
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

    function getDataGudang($idBarang = null, $idGudang = null, $year = null, $month = null)
    {
        // dd($idJenisBarang);
        $builder = $this->db->table("trans_barang_history a");
        $builder->select("a.id_barang, b.nama_barang, CAST(SUM(a.jumlah) AS DECIMAL(18,2)) as saldo_akhir, CAST(SUM(a.stok_awal) AS DECIMAL(18,2)) as saldo_awal");
        $builder->join("ref_barang b", "a.id_barang = b.id", "inner");
        $builder->join("ref_jenis_barang c", "a.id_jenis_barang = c.id", "inner");
        $builder->groupBy("a.id_barang, b.nama_barang");
        if (!empty($idBarang)) {
            $builder->where("a.id_barang", $idBarang);
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
        // $builder->orderBy("c.nama_jenis_barang", "ASC");
        $builder->orderBy("b.nama_barang", "ASC");    
        // $builder->orderBy("a.lot_id", "DESC");    
        // $builder->orderBy("a.tanggal", "ASC");    
        // $builder->orderBy("a.id", "ASC");    
        
        $this->_data = $builder->get()->getRow();
        return $this->_data;
    }
}

