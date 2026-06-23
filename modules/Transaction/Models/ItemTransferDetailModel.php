<?php

namespace Modules\Transaction\Models;

class ItemTransferDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_barang_trf_detail";
    protected $tblDetSO = "trans_barang_trf_so";
    protected $tblSO = "trans_sales_order";
    protected $tblBarang = "ref_barang";
    protected $tblSatuan = "ref_satuan";
    protected $tblGudang = "ref_gudang";
    protected $tblTrxLots = "trans_lots";
    protected $tblPack = "ref_pack";
    protected $tblDetailSO = "trans_barang_trf_so_det";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblBarang . " ebx", "uk.id_barang = ebx.id", "inner");
        $builder->join($this->tblSatuan . " fbx", "ebx.id_satuan = fbx.id", "inner");
        $builder->join("trans_walkorder wo", "uk.kode_walkorder = wo.kode_walkorder", "left");
        $builder->join($this->tblTrxLots . " gbx", "uk.lot_id = gbx.id AND gbx.id_gudang = $params[id_gudang]", "left");
        $builder->join($this->tblPack . " hbx", "uk.pack_id = hbx.id", "left");
        $builder->select("uk.id,gbx.id as lot_id,uk.id_header,uk.qty,uk.lot_no, uk.id_barang,
                          fbx.nama_satuan as nama_unit, ebx.kode_barang, ebx.nama_barang, 
                          uk.price, uk.keterangan, hbx.pack_name, uk.pack_id,wo.kode_walkorder,wo.id as id_wo");

        if (!empty($params['id_header'])) {
            $builder->where('uk.id_header', $params['id_header']);
        }
        // if (!empty($params['id_gudang'])) {
        //     $builder->where('gbx.id_gudang', $params['id_gudang']);
        // }
        if ($params['isReceive']) {
            $builder->groupStart();
            $builder->where("qty_receive < qty");
            $builder->orWhere("qty_receive IS NULL");
            $builder->groupEnd();
        }
        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.rec_no) LIKE', strtolower("%{$filters[0]['value']}%"));
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
        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(dbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.po_no) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetailSO($idHeader = null)
    {
        $builder = $this->db->table($this->tblDetSO . " abx");

        $builder->select("bbx.id,abx.id_so, bbx.kode_sales_order, cbx.nama, bbx.deskripsi");
        $builder->join($this->tblSO . " bbx", "abx.id_so = bbx.id", "left");
        $builder->join("ref_konsumen cbx", "bbx.id_konsumen = cbx.id", "inner");

        $builder->where("abx.id_header", $idHeader);
        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }

    // function getDataDetSO($idHeader = null)
    // {
    //     $builder = $this->db->table($this->tblDetailSO . " abx");

    //     $builder->select("abx.qty, abx.tipe, abx.ref_detail_id, abx.qty as qty_kirim, abx.kode_sales_order, abx.id_konsumen, abx.style, abx.kode_sales_order, abx.deskripsi, 
    //                       abx.color,abx.amount,cbx.nama as buyer, abx.kode_ukuran, abx.keterangan, rk.key_ukuran, trfhead.id_proses,
    //                       (
    //                         CASE 
    //                             WHEN abx.tipe = 1 THEN 
    //                                 TRIM(BOTH ' ~ ' FROM COALESCE(rwtsd1.keterangan, '') ||
    //                                     CASE WHEN rwtsd2.keterangan IS NOT NULL THEN ' ~ ' || rwtsd2.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsd3.keterangan IS NOT NULL THEN ' ~ ' || rwtsd3.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsd4.keterangan IS NOT NULL THEN ' ~ ' || rwtsd4.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsd5.keterangan IS NOT NULL THEN ' ~ ' || rwtsd5.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsd6.keterangan IS NOT NULL THEN ' ~ ' || rwtsd6.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsd7.keterangan IS NOT NULL THEN ' ~ ' || rwtsd7.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsd8.keterangan IS NOT NULL THEN ' ~ ' || rwtsd8.keterangan ELSE '' END 
    //                                 )

    //                             ELSE 
    //                                 TRIM(BOTH ' ~ ' FROM COALESCE(rwtsod1.keterangan, '') ||
    //                                     CASE WHEN rwtsod2.keterangan IS NOT NULL THEN ' ~ ' || rwtsod2.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsod3.keterangan IS NOT NULL THEN ' ~ ' || rwtsod3.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsod4.keterangan IS NOT NULL THEN ' ~ ' || rwtsod4.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsod5.keterangan IS NOT NULL THEN ' ~ ' || rwtsod5.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsod6.keterangan IS NOT NULL THEN ' ~ ' || rwtsod6.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsod7.keterangan IS NOT NULL THEN ' ~ ' || rwtsod7.keterangan ELSE '' END ||
    //                                     CASE WHEN rwtsod8.keterangan IS NOT NULL THEN ' ~ ' || rwtsod8.keterangan ELSE '' END 
    //                                 )
    //                         END
    //                     ) AS deskripsi");
    //     $builder->join("ref_konsumen cbx", "abx.id_konsumen = cbx.id", "inner");
    //     $builder->join("ref_ukuran rk", "abx.kode_ukuran = rk.kode_ukuran", "left");
    //     $builder->join("trans_barang_trf_header trfhead", "abx.id_header = trfhead.id", "left");
    //     $builder->join("trans_sample_det tsd", "tsd.id = abx.ref_detail_id and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd1", "rwtsd1.id = tsd.id_warna_1 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd2", "rwtsd2.id = tsd.id_warna_2 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd3", "rwtsd3.id = tsd.id_warna_3 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd4", "rwtsd4.id = tsd.id_warna_4 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd5", "rwtsd5.id = tsd.id_warna_5 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd6", "rwtsd6.id = tsd.id_warna_6 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd7", "rwtsd7.id = tsd.id_warna_7 and abx.tipe = 1", "left");
    //     $builder->join("ref_warna rwtsd8", "rwtsd8.id = tsd.id_warna_8 and abx.tipe = 1", "left");
    //     $builder->join("trans_sales_order_det tsod", "tsod.id = abx.ref_detail_id and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod1", "rwtsod1.id = tsod.id_warna_1 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod2", "rwtsod2.id = tsod.id_warna_2 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod3", "rwtsod3.id = tsod.id_warna_3 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod4", "rwtsod4.id = tsod.id_warna_4 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod5", "rwtsod5.id = tsod.id_warna_5 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod6", "rwtsod6.id = tsod.id_warna_6 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod7", "rwtsod7.id = tsod.id_warna_7 and abx.tipe = 2", "left");
    //     $builder->join("ref_warna rwtsod8", "rwtsod8.id = tsod.id_warna_8 and abx.tipe = 2", "left");

    //     $subQuery = '(SELECT wop.harga FROM trans_walkorder_proses wop 
    //                     left join trans_walkorder wo on wop.id_walkorder = wo.id 
    //                     WHERE wop.id_proses = trfhead.id_proses 
    //                     AND wo.ref_kode = abx.kode_sales_order) limit 1';

    //     $builder->select("($subQuery) AS harga");

    //     $builder->where("abx.id_header", $idHeader);
    //     $this->_data = $builder->get()->getResult();


    //     return $this->_data;
    // }

    function getDataDetSO($idHeader = null)
    {
        $builder = $this->db->table($this->tblDetailSO . " abx");

        $builder->select("abx.id, abx.print_type, trfhead.id as id_header, abx.qty, abx.tipe, abx.ref_detail_id, abx.qty as qty_kirim, abx.kode_sales_order, abx.id_konsumen, abx.style, abx.kode_sales_order, abx.deskripsi, 
                          abx.color,abx.amount,cbx.nama as buyer, abx.kode_ukuran, abx.total_scanned, abx.keterangan, rk.key_ukuran, trfhead.id_proses,
                          (
                            CASE 
                                WHEN abx.tipe = 1 THEN 
                                    TRIM(BOTH ' ~ ' FROM COALESCE(rwtsd1.keterangan, '') ||
                                        CASE WHEN rwtsd2.keterangan IS NOT NULL THEN ' ~ ' || rwtsd2.keterangan ELSE '' END ||
                                        CASE WHEN rwtsd3.keterangan IS NOT NULL THEN ' ~ ' || rwtsd3.keterangan ELSE '' END ||
                                        CASE WHEN rwtsd4.keterangan IS NOT NULL THEN ' ~ ' || rwtsd4.keterangan ELSE '' END ||
                                        CASE WHEN rwtsd5.keterangan IS NOT NULL THEN ' ~ ' || rwtsd5.keterangan ELSE '' END ||
                                        CASE WHEN rwtsd6.keterangan IS NOT NULL THEN ' ~ ' || rwtsd6.keterangan ELSE '' END ||
                                        CASE WHEN rwtsd7.keterangan IS NOT NULL THEN ' ~ ' || rwtsd7.keterangan ELSE '' END ||
                                        CASE WHEN rwtsd8.keterangan IS NOT NULL THEN ' ~ ' || rwtsd8.keterangan ELSE '' END 
                                    )

                                ELSE 
                                    TRIM(BOTH ' ~ ' FROM COALESCE(rwtsod1.keterangan, '') ||
                                        CASE WHEN rwtsod2.keterangan IS NOT NULL THEN ' ~ ' || rwtsod2.keterangan ELSE '' END ||
                                        CASE WHEN rwtsod3.keterangan IS NOT NULL THEN ' ~ ' || rwtsod3.keterangan ELSE '' END ||
                                        CASE WHEN rwtsod4.keterangan IS NOT NULL THEN ' ~ ' || rwtsod4.keterangan ELSE '' END ||
                                        CASE WHEN rwtsod5.keterangan IS NOT NULL THEN ' ~ ' || rwtsod5.keterangan ELSE '' END ||
                                        CASE WHEN rwtsod6.keterangan IS NOT NULL THEN ' ~ ' || rwtsod6.keterangan ELSE '' END ||
                                        CASE WHEN rwtsod7.keterangan IS NOT NULL THEN ' ~ ' || rwtsod7.keterangan ELSE '' END ||
                                        CASE WHEN rwtsod8.keterangan IS NOT NULL THEN ' ~ ' || rwtsod8.keterangan ELSE '' END 
                                    )
                            END
                        ) AS deskripsi,
                         COALESCE((
                            SELECT SUM(tbmp.qty)
                            FROM trans_barang_masuk_produksi tbmp
                            INNER JOIN trans_barang_header tbh
                                ON tbh.id = tbmp.id_header
                            WHERE tbmp.kode_sales_order = abx.kode_sales_order
                            AND tbmp.kode_ukuran = abx.kode_ukuran
                            AND tbmp.color = abx.color
                            AND tbh.id_proses = trfhead.id_proses
                            AND tbh.id_cmt = trfhead.id_cmt
                            AND LEFT(tbh.kode_transaksi, 3) = 'BTM'
                            AND tbh.active = 1
                        ), 0) AS qty_terima,
                        (abx.qty - COALESCE((
                            SELECT SUM(tbmp.qty)
                            FROM trans_barang_masuk_produksi tbmp
                            INNER JOIN trans_barang_header tbh
                                ON tbh.id = tbmp.id_header
                            WHERE tbmp.kode_sales_order = abx.kode_sales_order
                            AND tbmp.kode_ukuran = abx.kode_ukuran
                            AND tbmp.color = abx.color
                            AND tbh.id_proses = trfhead.id_proses
                            AND tbh.id_cmt = trfhead.id_cmt
                            AND LEFT(tbh.kode_transaksi, 3) = 'BTM'
                            AND tbh.active = 1
                        ),0)
                    ) AS qty_sisa");
        $builder->join("ref_konsumen cbx", "abx.id_konsumen = cbx.id", "inner");
        $builder->join("ref_ukuran rk", "abx.kode_ukuran = rk.kode_ukuran", "left");
        $builder->join("trans_barang_trf_header trfhead", "abx.id_header = trfhead.id", "left");
        $builder->join("trans_sample_det tsd", "tsd.id = abx.ref_detail_id and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd1", "rwtsd1.id = tsd.id_warna_1 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd2", "rwtsd2.id = tsd.id_warna_2 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd3", "rwtsd3.id = tsd.id_warna_3 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd4", "rwtsd4.id = tsd.id_warna_4 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd5", "rwtsd5.id = tsd.id_warna_5 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd6", "rwtsd6.id = tsd.id_warna_6 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd7", "rwtsd7.id = tsd.id_warna_7 and abx.tipe = 1", "left");
        $builder->join("ref_warna rwtsd8", "rwtsd8.id = tsd.id_warna_8 and abx.tipe = 1", "left");
        $builder->join("trans_sales_order_det tsod", "tsod.id = abx.ref_detail_id and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod1", "rwtsod1.id = tsod.id_warna_1 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod2", "rwtsod2.id = tsod.id_warna_2 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod3", "rwtsod3.id = tsod.id_warna_3 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod4", "rwtsod4.id = tsod.id_warna_4 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod5", "rwtsod5.id = tsod.id_warna_5 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod6", "rwtsod6.id = tsod.id_warna_6 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod7", "rwtsod7.id = tsod.id_warna_7 and abx.tipe = 2", "left");
        $builder->join("ref_warna rwtsod8", "rwtsod8.id = tsod.id_warna_8 and abx.tipe = 2", "left");

        $subQuery = '(SELECT wop.harga FROM trans_walkorder_proses wop 
                        left join trans_walkorder wo on wop.id_walkorder = wo.id 
                        WHERE wop.id_proses = trfhead.id_proses 
                        AND wo.ref_kode = abx.kode_sales_order) limit 1';

        $builder->select("($subQuery) AS harga");

        $builder->where("abx.id_header", $idHeader);
        // $builder->where("trfhead.status", 1);
        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }

    function getDataDetSORef($idHeader = null, $array = false)
    {
        $inClause = $array 
            ? "IN (" . implode(',', array_map('intval', $idHeader)) . ")" 
            : "= " . intval($idHeader);

        $sql = "
            WITH btm_qty AS (
                SELECT 
                    tbmp.kode_sales_order,
                    tbmp.kode_ukuran,
                    tbmp.color,
                    tbh.id_proses,
                    tbh.id_cmt,
                    SUM(tbmp.qty) AS total_qty
                FROM trans_barang_masuk_produksi tbmp
                INNER JOIN trans_barang_header tbh ON tbh.id = tbmp.id_header
                WHERE LEFT(tbh.kode_transaksi, 3) = 'BTM'
                AND tbh.active = 1
                GROUP BY tbmp.kode_sales_order, tbmp.kode_ukuran, tbmp.color, tbh.id_proses, tbh.id_cmt
            ),

            walkorder_harga AS (
                SELECT 
                    wop.id_proses,
                    wo.ref_kode,
                    wop.harga,
                    ROW_NUMBER() OVER (PARTITION BY wop.id_proses, wo.ref_kode ORDER BY wop.id) AS rn
                FROM trans_walkorder_proses wop
                LEFT JOIN trans_walkorder wo ON wop.id_walkorder = wo.id
            ),

            warna_tsd AS (
                SELECT 
                    tsd.id,
                    TRIM(BOTH ' ~ ' FROM 
                        COALESCE(w1.keterangan, '') ||
                        CASE WHEN w2.keterangan IS NOT NULL THEN ' ~ ' || w2.keterangan ELSE '' END ||
                        CASE WHEN w3.keterangan IS NOT NULL THEN ' ~ ' || w3.keterangan ELSE '' END ||
                        CASE WHEN w4.keterangan IS NOT NULL THEN ' ~ ' || w4.keterangan ELSE '' END ||
                        CASE WHEN w5.keterangan IS NOT NULL THEN ' ~ ' || w5.keterangan ELSE '' END ||
                        CASE WHEN w6.keterangan IS NOT NULL THEN ' ~ ' || w6.keterangan ELSE '' END ||
                        CASE WHEN w7.keterangan IS NOT NULL THEN ' ~ ' || w7.keterangan ELSE '' END ||
                        CASE WHEN w8.keterangan IS NOT NULL THEN ' ~ ' || w8.keterangan ELSE '' END
                    ) AS deskripsi
                FROM trans_sample_det tsd
                LEFT JOIN ref_warna w1 ON w1.id = tsd.id_warna_1
                LEFT JOIN ref_warna w2 ON w2.id = tsd.id_warna_2
                LEFT JOIN ref_warna w3 ON w3.id = tsd.id_warna_3
                LEFT JOIN ref_warna w4 ON w4.id = tsd.id_warna_4
                LEFT JOIN ref_warna w5 ON w5.id = tsd.id_warna_5
                LEFT JOIN ref_warna w6 ON w6.id = tsd.id_warna_6
                LEFT JOIN ref_warna w7 ON w7.id = tsd.id_warna_7
                LEFT JOIN ref_warna w8 ON w8.id = tsd.id_warna_8
            ),

            warna_tsod AS (
                SELECT 
                    tsod.id,
                    TRIM(BOTH ' ~ ' FROM 
                        COALESCE(w1.keterangan, '') ||
                        CASE WHEN w2.keterangan IS NOT NULL THEN ' ~ ' || w2.keterangan ELSE '' END ||
                        CASE WHEN w3.keterangan IS NOT NULL THEN ' ~ ' || w3.keterangan ELSE '' END ||
                        CASE WHEN w4.keterangan IS NOT NULL THEN ' ~ ' || w4.keterangan ELSE '' END ||
                        CASE WHEN w5.keterangan IS NOT NULL THEN ' ~ ' || w5.keterangan ELSE '' END ||
                        CASE WHEN w6.keterangan IS NOT NULL THEN ' ~ ' || w6.keterangan ELSE '' END ||
                        CASE WHEN w7.keterangan IS NOT NULL THEN ' ~ ' || w7.keterangan ELSE '' END ||
                        CASE WHEN w8.keterangan IS NOT NULL THEN ' ~ ' || w8.keterangan ELSE '' END
                    ) AS deskripsi
                FROM trans_sales_order_det tsod
                LEFT JOIN ref_warna w1 ON w1.id = tsod.id_warna_1
                LEFT JOIN ref_warna w2 ON w2.id = tsod.id_warna_2
                LEFT JOIN ref_warna w3 ON w3.id = tsod.id_warna_3
                LEFT JOIN ref_warna w4 ON w4.id = tsod.id_warna_4
                LEFT JOIN ref_warna w5 ON w5.id = tsod.id_warna_5
                LEFT JOIN ref_warna w6 ON w6.id = tsod.id_warna_6
                LEFT JOIN ref_warna w7 ON w7.id = tsod.id_warna_7
                LEFT JOIN ref_warna w8 ON w8.id = tsod.id_warna_8
            )

            SELECT 
                abx.id,
                abx.print_type,
                trfhead.id AS id_header,
                abx.qty,
                abx.tipe,
                abx.ref_detail_id,
                abx.qty AS qty_kirim,
                abx.kode_sales_order,
                abx.id_konsumen,
                abx.style,
                abx.color,
                abx.amount,
                cbx.nama AS buyer,
                abx.kode_ukuran,
                abx.keterangan,
                rk.key_ukuran,
                trfhead.id_proses,
                CASE 
                    WHEN abx.tipe = 1 THEN wtsd.deskripsi
                    ELSE wtsod.deskripsi
                END AS deskripsi,
                COALESCE(bq.total_qty, 0) AS qty_terima,
                (abx.qty - COALESCE(bq.total_qty, 0)) AS qty_sisa,
                wh.harga

            FROM trans_barang_trf_so_det abx
            INNER JOIN ref_konsumen cbx                ON abx.id_konsumen = cbx.id
            LEFT JOIN  ref_ukuran rk                   ON abx.kode_ukuran = rk.kode_ukuran
            LEFT JOIN  trans_barang_trf_header trfhead ON abx.id_header = trfhead.id
            LEFT JOIN  warna_tsd wtsd                  ON wtsd.id = abx.ref_detail_id AND abx.tipe = 1
            LEFT JOIN  warna_tsod wtsod                ON wtsod.id = abx.ref_detail_id AND abx.tipe = 2
            LEFT JOIN  btm_qty bq                      ON bq.kode_sales_order = abx.kode_sales_order
                                                    AND bq.kode_ukuran = abx.kode_ukuran
                                                    AND bq.color = abx.color
                                                    AND bq.id_proses = trfhead.id_proses
                                                    AND bq.id_cmt = trfhead.id_cmt
            LEFT JOIN  walkorder_harga wh              ON wh.id_proses = trfhead.id_proses
                                                    AND wh.ref_kode = abx.kode_sales_order
                                                    AND wh.rn = 1

            WHERE abx.id_header {$inClause}
            AND trfhead.status = 1
        ";

        $this->_data = $this->db->query($sql)->getResult();

        return $this->_data;
    }

    function getDataDetail($idHeader = null, $array = false)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id as id_detail, abx.id_barang, abx.qty, abx.qty as qty_transfer, abx.qty as qty_exist, abx.lot_id, abx.lot_no, cbx.kode_barang, cbx.nama_barang, sbx.nama_satuan as nama_unit, abx.price");
        $builder->join("ref_barang cbx", "abx.id_barang = cbx.id", "inner");
        $builder->join("ref_satuan sbx", "cbx.id_satuan = sbx.id", "inner");
        $builder->join(
            "trans_barang_trf_header trfhead", 
            "CAST(abx.id_header AS INTEGER) = trfhead.id", 
            "left"
        );
        if ($array == true) {
            $builder->whereIn("abx.id_header", $idHeader);
        }
        else {
            $builder->where("abx.id_header", $idHeader);
        }
        $builder->where("trfhead.status", 1);
        $builder->orderBy('abx.id', "DESC");
        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }

    function getDataPrint($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $id_header = $params['id_header'];
        $id_gudang = $params['id_gudang'];

        $whereReceive = "";
        if (!empty($params['isReceive'])) {
            $whereReceive = "AND (uk.qty_receive < uk.qty OR uk.qty_receive IS NULL)";
        }

        $sql = "
            SELECT
                sub.id_barang,
                sub.id_header,
                sub.lot_no,
                sub.kode_barang,
                sub.nama_barang,
                STRING_AGG(DISTINCT sub.keterangan, ' ~ ' ORDER BY sub.keterangan) AS keterangan,
                STRING_AGG(sub.pack_qty, '|' ORDER BY sub.urutan) AS pack_data,
                SUM(sub.qty) AS qty
            FROM (
                SELECT
                    uk.id AS urutan,
                    uk.id_barang,
                    uk.id_header,
                    uk.lot_no,
                    uk.qty,
                    uk.keterangan,
                    ebx.kode_barang,
                    ebx.nama_barang,
                    CONCAT(COALESCE(hbx.pack_name, '-'), ':', uk.qty::text) AS pack_qty
                FROM trans_barang_trf_detail uk
                INNER JOIN ref_barang ebx ON uk.id_barang = ebx.id
                INNER JOIN ref_satuan fbx ON ebx.id_satuan = fbx.id
                LEFT  JOIN trans_lots gbx ON uk.lot_id = gbx.id AND gbx.id_gudang = ?
                LEFT  JOIN ref_pack hbx ON uk.pack_id = hbx.id
                WHERE uk.id_header = ?
                AND uk.active = 1
                {$whereReceive}
            ) sub
            GROUP BY
                sub.id_barang, sub.id_header, sub.lot_no,
                sub.kode_barang, sub.nama_barang
            ORDER BY sub.id_barang
        ";
        $this->_data = $this->db->query($sql, [$id_gudang, $id_header])->getResult();

        return $this->_data;
    }
}
