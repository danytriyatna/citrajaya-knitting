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

    transaksi_dengan_rownum AS (
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
        COALESCE(sh.jumlah, 0) AS saldo_awal_raw,
        CAST(COALESCE(sh.jumlah, 0) AS DECIMAL(18,2)) AS saldo_awal,
        CAST(
            CASE WHEN nt.jenis_transaksi = 'masuk' THEN nt.jumlah ELSE 0 END
            AS DECIMAL(18,2)
        ) AS masuk,
        CAST(
            CASE WHEN nt.jenis_transaksi = 'keluar' THEN -nt.jumlah ELSE 0 END
            AS DECIMAL(18,2)
        ) AS keluar,
        ROW_NUMBER() OVER (PARTITION BY nt.id_barang, nt.id_gudang, nt.lot_id ORDER BY nt.id ASC, nt.kode_transaksi) AS rn
    FROM
        normalized_trans nt
        LEFT JOIN trans_barang_history sh ON nt.id = sh.id_trans_barang
    ),

    stock_card AS (
        SELECT *,
            CAST(
                SUM(
                    CASE WHEN rn = 1 THEN saldo_awal_raw ELSE 0 END + masuk - keluar
                ) OVER (
                    PARTITION BY id_barang, id_gudang, lot_id
                    ORDER BY id ASC, kode_transaksi
                ) AS DECIMAL(18,2)
            ) AS saldo_akhir
        FROM transaksi_dengan_rownum
    )

    SELECT *
    FROM stock_card
    ORDER BY
        nama_jenis_barang ASC,
        nama_barang ASC,
        created_at ASC,
        id_gudang,
        tanggal,
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
        

        $prevMonth = 12;
        $prevYear = $year;
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year - 1;
        }
        else {
            $prevMonth = $month - 1;
        }
        
        

        $lot_id = [];

        foreach ($dataOld as $key => $value) {

            if (!in_array($value->lot_no, $lot_id)) {
                $lot_id[] = $value->lot_no;
            }
        }
        foreach ($lot_id as $key => $lot) {
            $cutoffDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $builder = $this->db->table("trans_barang abx");
            $builder->select("abx.id, abx.id_barang, abx.lot_id, abx.kode_transaksi, abx.id_kategori, abx.tanggal, abx.price, COALESCE(SUM(abx.jumlah), 0) as jumlah");
            // $builder->where("abx.tanggal <", $cutoffDate);
            $builder->where("EXTRACT(MONTH FROM abx.tanggal)", $prevMonth);
            $builder->where("EXTRACT(YEAR FROM  abx.tanggal)", $prevYear);
            $builder->where("abx.id_gudang_tujuan is not null");
            $builder->where("abx.lot_id", $lot);
            $builder->groupBy("abx.id, abx.id_barang, abx.lot_id, abx.kode_transaksi, abx.id_kategori, abx.tanggal, abx.price");
            $data = $builder->get()->getRow();

            $builder_keluar = $this->db->table("trans_barang abx");
            $builder_keluar->select("abx.id, abx.id_barang, abx.lot_id, abx.kode_transaksi, abx.id_kategori, abx.tanggal, abx.price, COALESCE(SUM(abx.jumlah), 0) as jumlah");
            // $builder_keluar->where("abx.tanggal <", $cutoffDate);
            $builder_keluar->where("EXTRACT(MONTH FROM abx.tanggal)", $prevMonth);
            $builder_keluar->where("EXTRACT(YEAR FROM  abx.tanggal)", $prevYear);
            $builder_keluar->where("abx.id_gudang_asal is not null");
            $builder_keluar->where("abx.lot_id", $lot);
            $builder_keluar->groupBy("abx.id, abx.id_barang, abx.lot_id, abx.kode_transaksi, abx.id_kategori, abx.tanggal, abx.price");
            $data_keluar = $builder_keluar->get()->getRow();
            $jumlah = 0;
            
            if (isset($data)) {
                $jumlah += $data->jumlah;
            }
            if (isset($data_keluar)) {
                $jumlah -= $data_keluar->jumlah;
            }
            foreach ($dataOld as $key => $value) {

                if ($value->lot_id == $lot) {
                    $isi['id_barang'] = $value->id_barang;
                    $isi['id_trans_barang'] = $value->id;
                    $isi['id_gudang'] = $value->id_gudang;
                    $isi['lot_id'] = $value->lot_id;
                    $isi['month'] = $month;
                    $isi['year'] = $year;
                    $isi['jumlah'] = $jumlah;
                    
                    if (isset($value->masuk) && $value->masuk > 0) {
                        $jumlah += $value->masuk;
                    }
                    if (isset($value->keluar) && $value->keluar > 0) {
                        $jumlah -= $value->keluar;
                    }
                    $builder_detail = $this->db->table("trans_barang_history abx");
                    $builder_detail->where('abx.month', $month);
                    $builder_detail->where('abx.year', $year);
                    $builder_detail->where('abx.id_trans_barang', $value->id);
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
                        $this->db->table("trans_barang_history")->update($isi, array("id_trans_barang" => $value->id, "month" => $month, "year" => $year));
                    }
                }
            }
        }

        return true;
    }
}
