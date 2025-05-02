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
            abx.lot_id,
            dbx.nama_barang,
            dbx.kode_barang,
            ebx.nama_jenis_barang,
            fbx.nama_satuan,
            abx.price
        FROM
            trans_barang abx
            LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
            INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
            INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
            INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
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
        if (!empty($idJenisBarang)) {
            $sql .= " AND dbx.id_jenis_barang = :id_jenis_barang:";
            $params['id_jenis_barang'] = $idJenisBarang;
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
            abx.lot_id,
            dbx.nama_barang,
            dbx.kode_barang,
            ebx.nama_jenis_barang,
            fbx.nama_satuan,
            abx.price
        FROM
            trans_barang abx 
            LEFT JOIN ref_trans bbx ON LEFT(abx.kode_transaksi, 3) = bbx.alias
            INNER JOIN trans_lots cbx ON abx.lot_id = cbx.id
              INNER JOIN ref_barang dbx ON abx.id_barang = dbx.id
            INNER JOIN ref_jenis_barang ebx ON dbx.id_jenis_barang = ebx.id
            INNER JOIN ref_satuan fbx ON dbx.id_satuan = fbx.id
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
        if (!empty($idJenisBarang)) {
            $sql .= " AND dbx.id_jenis_barang = :id_jenis_barang:";
            $params['id_jenis_barang'] = $idJenisBarang;
        }
       $sql .= "
    ),
    saldo_sebelumnya_history AS (
        SELECT
            th.lot_id,
            th.id_barang,
            th.id_gudang,
            SUM(CASE
                WHEN th.id_gudang IS NOT NULL THEN th.jumlah
                ELSE 0
            END) AS saldo_sebelumnya
        FROM trans_barang_history th
        WHERE th.month = :prev_month:
          AND th.year = :prev_year:";

        if (!empty($idGudang)) {
            $sql .= " AND th.id_gudang = :id_gudang:";
            $params['id_gudang'] = $idGudang;
        }

        if (!empty($idJenisBarang)) {
            $sql .= " AND th.id_barang IN (
                SELECT id FROM ref_barang WHERE id_jenis_barang = :id_jenis_barang:
            )";
            $params['id_jenis_barang'] = $idJenisBarang;
        }

$sql .= "
        GROUP BY th.id_barang, th.id_gudang, th.lot_id
    ),

    history_per_lot AS (
    SELECT
        lot_id,
        month,
        year,
        jumlah AS saldo_awal
    FROM trans_barang_history
),
normalized_trans_with_history AS (
    SELECT
        nt.*,
        COALESCE(h.saldo_awal, 0) AS saldo_awal_history
    FROM normalized_trans nt
    LEFT JOIN history_per_lot h
        ON nt.lot_id = h.lot_id
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
                WHEN ROW_NUMBER() OVER(PARTITION BY nt.lot_id ORDER BY nt.id ASC) = 1 
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
                PARTITION BY nt.lot_id 
                ORDER BY nt.id ASC 
                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
            ) + 
            FIRST_VALUE(nt.saldo_awal_history) OVER (PARTITION BY nt.lot_id ORDER BY nt.id ASC)
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
                LAG(sb.saldo_akhir) OVER (PARTITION BY sb.lot_id ORDER BY sb.id)
            ) AS DECIMAL(18,2)
        ) AS saldo_awal
    FROM stock_base sb
)

    SELECT *
    FROM stock_card
    ORDER BY
        nama_jenis_barang ASC,
        nama_barang ASC,
        lot_id DESC,
        created_at ASC,
        id_gudang,
        tanggal,
        id ASC,
        kode_transaksi
";

$query = $this->db->query($sql, $params);


        $this->_data  = $query->getResult();
        // dd($this->_data);
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
        
        

        $lot_id = [];
        $lot_no = [];
        $stok_awal = [];

        foreach ($dataOld as $key => $value) {
            if (!in_array($value->lot_id, $lot_id)) {
                $lot_id[] = $value->lot_id;
                $stok_awal[] = $value->saldo_awal;
                $lot_no[] = $value->lot_no;
            }
        }

        foreach ($lot_id as $key => $lot) {
            $cutoffDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $builder = $this->db->table("trans_barang abx");
            $builder->select("
                abx.id_barang,
                abx.lot_id,
                COALESCE(SUM(
                    CASE 
                        WHEN abx.id_gudang_tujuan IS NOT NULL THEN abx.jumlah
                        ELSE 0
                    END
                ), 0) as total_masuk,
                COALESCE(SUM(
                    CASE 
                        WHEN abx.id_gudang_asal IS NOT NULL THEN -abx.jumlah
                        ELSE 0
                    END
                ), 0) as total_keluar,
                COALESCE(SUM(
                    CASE 
                        WHEN abx.id_gudang_tujuan IS NOT NULL THEN abx.jumlah
                        WHEN abx.id_gudang_asal IS NOT NULL THEN -abx.jumlah
                        ELSE 0
                    END
                ), 0) as total_jumlah
            ");
            $builder->where("EXTRACT(MONTH FROM abx.tanggal)", $month);
            $builder->where("EXTRACT(YEAR FROM abx.tanggal)", $year);
            $builder->where("abx.lot_id", $lot);
            $builder->groupBy("abx.id_barang, abx.lot_id");

            
            $data = $builder->get()->getRow();
            
            $jumlah = 0;
            
            if (isset($data)) {
                $jumlah = $data->total_jumlah;
                $isi['id_barang'] = $data->id_barang;
                $isi['lot_id'] = $data->lot_id;
                $isi['lot_no'] = $lot_no[$key];
                $isi['stok_awal'] = $stok_awal[$key];
                $isi['masuk'] = round($data->total_masuk, 2);
                $isi['keluar'] = round($data->total_keluar, 2);
                $isi['month'] = $nextMonth;
                $isi['year'] = $nextYear;
                $isi['jumlah'] = round($jumlah, 2);
                $isi['tanggal'] = date('Y-m-d H:i:s');
                
                $builder_detail = $this->db->table("trans_barang_history abx");
                $builder_detail->where('abx.month', $nextMonth);
                $builder_detail->where('abx.year', $nextYear);
                $builder_detail->where('abx.lot_id', $data->lot_id);
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
                    $this->db->table("trans_barang_history")->update($isi, array("month" => $nextMonth, "year" => $nextYear, "lot_id" => $data->lot_id));
                }
            }
        }
        return true;
    }
}
