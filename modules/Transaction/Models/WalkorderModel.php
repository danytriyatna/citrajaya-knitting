<?php

namespace Modules\Transaction\Models;

class WalkorderModel extends \App\Models\PrModel
{

    protected $table  = "trans_walkorder";
    protected $table2 = "trans_walkorder_detail";
    protected $table3 = "trans_walkorder_proses";
    protected $table4 = "trans_walkorder_proses_ukuran";
    protected $table5 = "trans_walkorder_warna";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    // walkorder head 
    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->select("abx.id, abx.ref_id, abx.ref_kode, abx.kode_walkorder, abx.id_konsumen, abx.id_style, abx.qty, abx.file_id,
                          abx.ref_kode, abx.status, bbx.nama as konsumen_nama, abx.tgl_deadline, abx.tgl_transaksi, abx.keterangan_style,
                          abx.tipe_id, cbx.file_name, abx.id_gudang, abx.keterangan, 
                          (case when abx.tipe_id = 1 then ts.deskripsi else tso.deskripsi end) as deskripsi,
                          (case when abx.tipe_id = 1 then ts.style else tso.style end) as style,
                          (case when abx.tipe_id = 1 then null else tso.tgl_deadline_dua end) as tgl_deadline_dua,
                        ");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->join("trans_sample ts", "ts.id = abx.ref_id and abx.tipe_id = 1", "left");
        $builder->join("trans_sales_order tso", "tso.id = abx.ref_id and abx.tipe_id = 2", "left");
        $builder->join("_files cbx", "abx.file_id = cbx.id", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $value = strtolower("%{$filters[0]['value']}%");
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_walkorder) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.ref_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.keterangan_style) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere("LOWER(CONCAT(TRIM(abx.keterangan_style), ' - ', TRIM(bbx.nama))) LIKE", $value);
                $builder->groupEnd();
            }

            if (!empty($params['id_konsumen'])) {
                $builder->where('abx.id_konsumen', $params['id_konsumen']);
            }

            if (!empty($params['ref_kode'])) {
                $builder->where('abx.ref_kode', $params['ref_kode']);
            }

            if (!empty($params['ref_id'])) {
                $builder->where('abx.ref_id', $params['ref_id']);
            }

            if (!empty($params['tipe_id'])) {
                $builder->where('abx.tipe_id', $params['tipe_id']);
            }

            // if (!empty($params['id'])) {
            //     $builder->where('abx.id <>', $params['id']);
            // }

            // if (!empty($params['keterangan_style'])) {
            //     $builder->where('abx.keterangan_style', $params['keterangan_style']);
            // }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id desc');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " abx");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        $builder->select("count(1) as _cnt");
        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(abx.kode_walkorder) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.ref_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getDataDetailWOApproveSO($style = null, $id_proses = null, $current_id_wo = null, $type = null)
    {
        $builder = $this->db->table($this->table3 . " abx");

        $builder->select("abx.harga, abx.id_walkorder, bbx.keterangan_style, bbx.kode_walkorder");
        $builder->join("trans_walkorder bbx", "abx.id_walkorder = bbx.id", "left");
        $builder->where('abx.active = 1');

        if (!empty($type)) {
            $builder->where('bbx.tipe_id', $type);
        }

        if (!empty($style)) {
            $builder->where('bbx.keterangan_style', $style);
        }

        if (!empty($id_proses)) {
            $builder->where('abx.id_proses', $id_proses);
        }

        if (!empty($current_id_wo)) {
            $builder->where('abx.id_walkorder <>', $current_id_wo);
        }

        $builder->orderBy('bbx.id desc');

        $builder->limit(1);

        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }

    function generete_kode()
    {
        $kd = "WRD";
        $builder = $this->db->table($this->table . ' a');
        $builder->select('LEFT(kode_walkorder, 7) AS tgl, RIGHT( kode_walkorder, 4 ) AS kode ');

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

    // end walkorder head 

    // WALKORDER DETAIL 
    function getData_detail($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " abx");

        // --- LOGIKA STOK HISTORY 8 BARANG ---
        $id_gudang = !empty($params['id_gudang']) ? $params['id_gudang'] : null;
        $gudangFilter = !empty($id_gudang) ? "AND tbh.id_gudang = $id_gudang" : "";

        $sq = [];
        for ($i = 1; $i <= 8; $i++) {
            $sq[] = "COALESCE((
                SELECT tbh.jumlah FROM trans_barang_history tbh 
                WHERE tbh.id_barang = (CASE WHEN abx.tipe_id = 2 THEN tso.id_barang_$i ELSE ts.id_barang_$i END)
                $gudangFilter 
                ORDER BY tbh.year DESC, tbh.month DESC LIMIT 1
            ), 0)";
        }
        $totalHistoryQuery = "(" . implode(" + ", $sq) . ") AS total_kuota_history";
        $builder = $this->db->table($this->table2 . " abx");

        $builder->select("abx.id, abx.id_walkorder, abx.ref_detail_id, abx.gram, abx.gram_nd, abx.kg, abx.loss,
                        abx.kg_loss, abx.total, abx.tipe_id, abx.kuota, abx.kuota_tambah,

                        (case when abx.tipe_id = 2 then tso.id_warna_1 else ts.id_warna_1 end) as id_wdasar,
                        {$totalHistoryQuery},
                        -- Flag sumber warna
                        CASE
                            WHEN abx.tipe_id = 2 AND tso.id_barang_1 IS NOT NULL THEN 'via_barang'
                            WHEN abx.tipe_id = 1 AND ts.id_barang_1  IS NOT NULL THEN 'via_barang'
                            ELSE 'via_warna'
                        END AS sumber_warna,

                        (CASE 
                            WHEN abx.tipe_id = 2 THEN
                                TRIM(BOTH ' - ' FROM
                                    COALESCE(rws1.kode_warna, '') ||
                                    CASE WHEN rws2.kode_warna IS NOT NULL THEN ' - ' || rws2.kode_warna ELSE '' END ||
                                    CASE WHEN rws3.kode_warna IS NOT NULL THEN ' - ' || rws3.kode_warna ELSE '' END ||
                                    CASE WHEN rws4.kode_warna IS NOT NULL THEN ' - ' || rws4.kode_warna ELSE '' END ||
                                    CASE WHEN rws5.kode_warna IS NOT NULL THEN ' - ' || rws5.kode_warna ELSE '' END ||
                                    CASE WHEN rws6.kode_warna IS NOT NULL THEN ' - ' || rws6.kode_warna ELSE '' END ||
                                    CASE WHEN rws7.kode_warna IS NOT NULL THEN ' - ' || rws7.kode_warna ELSE '' END ||
                                    CASE WHEN rws8.kode_warna IS NOT NULL THEN ' - ' || rws8.kode_warna ELSE '' END
                                )
                            ELSE
                                TRIM(BOTH ' - ' FROM
                                    COALESCE(rw1.kode_warna, '') ||
                                    CASE WHEN rw2.kode_warna IS NOT NULL THEN ' - ' || rw2.kode_warna ELSE '' END ||
                                    CASE WHEN rw3.kode_warna IS NOT NULL THEN ' - ' || rw3.kode_warna ELSE '' END ||
                                    CASE WHEN rw4.kode_warna IS NOT NULL THEN ' - ' || rw4.kode_warna ELSE '' END ||
                                    CASE WHEN rw5.kode_warna IS NOT NULL THEN ' - ' || rw5.kode_warna ELSE '' END ||
                                    CASE WHEN rw6.kode_warna IS NOT NULL THEN ' - ' || rw6.kode_warna ELSE '' END ||
                                    CASE WHEN rw7.kode_warna IS NOT NULL THEN ' - ' || rw7.kode_warna ELSE '' END ||
                                    CASE WHEN rw8.kode_warna IS NOT NULL THEN ' - ' || rw8.kode_warna ELSE '' END
                                )
                        END) AS wdasar,

                        (CASE 
                            WHEN abx.tipe_id = 2 THEN (
                                SELECT SUM(tsou.qty) 
                                FROM trans_sales_order_ukuran tsou 
                                WHERE tsou.id_sales_order_det = tso.id
                            )
                            WHEN abx.tipe_id = 1 THEN (
                                SELECT SUM(tsu.qty) 
                                FROM trans_sample_ukuran tsu 
                                WHERE tsu.id_sample_det = ts.id
                            )
                        END) as qty,

                        (CASE 
                            WHEN abx.tipe_id = 2 THEN (
                                SELECT SUM(tdd.qty) 
                                FROM trans_delivery_detail tdd  
                                INNER JOIN trans_sales_order_det tsod ON tsod.id = tdd.ref_detail_id
                                WHERE tdd.ref_detail_id = tso.id
                            )
                            WHEN abx.tipe_id = 1 THEN (
                                SELECT SUM(tdd.qty) 
                                FROM trans_delivery_detail tdd  
                                INNER JOIN trans_sample_det tsd ON tsd.id = tdd.ref_detail_id
                                WHERE tdd.ref_detail_id = ts.id
                            )
                        END) as qty_do
        ");

        // ========================
        // JOIN trans_sales_order_det (tipe_id = 2)
        // ========================
        $builder->join("trans_sales_order_det tso", "tso.id = abx.ref_detail_id AND abx.tipe_id = 2", "left");

        // JOIN ref_barang untuk SO
        $builder->join("ref_barang bso1", "bso1.id = tso.id_barang_1", "left");
        $builder->join("ref_barang bso2", "bso2.id = tso.id_barang_2", "left");
        $builder->join("ref_barang bso3", "bso3.id = tso.id_barang_3", "left");
        $builder->join("ref_barang bso4", "bso4.id = tso.id_barang_4", "left");
        $builder->join("ref_barang bso5", "bso5.id = tso.id_barang_5", "left");
        $builder->join("ref_barang bso6", "bso6.id = tso.id_barang_6", "left");
        $builder->join("ref_barang bso7", "bso7.id = tso.id_barang_7", "left");
        $builder->join("ref_barang bso8", "bso8.id = tso.id_barang_8", "left");

        // JOIN ref_warna untuk SO: COALESCE dari ref_barang, fallback ke trans_sales_order_det
        $builder->join("ref_warna rws1", "rws1.id = COALESCE(bso1.id_warna, tso.id_warna_1)", "left");
        $builder->join("ref_warna rws2", "rws2.id = COALESCE(bso2.id_warna, tso.id_warna_2)", "left");
        $builder->join("ref_warna rws3", "rws3.id = COALESCE(bso3.id_warna, tso.id_warna_3)", "left");
        $builder->join("ref_warna rws4", "rws4.id = COALESCE(bso4.id_warna, tso.id_warna_4)", "left");
        $builder->join("ref_warna rws5", "rws5.id = COALESCE(bso5.id_warna, tso.id_warna_5)", "left");
        $builder->join("ref_warna rws6", "rws6.id = COALESCE(bso6.id_warna, tso.id_warna_6)", "left");
        $builder->join("ref_warna rws7", "rws7.id = COALESCE(bso7.id_warna, tso.id_warna_7)", "left");
        $builder->join("ref_warna rws8", "rws8.id = COALESCE(bso8.id_warna, tso.id_warna_8)", "left");

        // ========================
        // JOIN trans_sample_det (tipe_id = 1)
        // ========================
        $builder->join("trans_sample_det ts", "ts.id = abx.ref_detail_id AND abx.tipe_id = 1", "left");

        // JOIN ref_barang untuk sample
        $builder->join("ref_barang bs1", "bs1.id = ts.id_barang_1", "left");
        $builder->join("ref_barang bs2", "bs2.id = ts.id_barang_2", "left");
        $builder->join("ref_barang bs3", "bs3.id = ts.id_barang_3", "left");
        $builder->join("ref_barang bs4", "bs4.id = ts.id_barang_4", "left");
        $builder->join("ref_barang bs5", "bs5.id = ts.id_barang_5", "left");
        $builder->join("ref_barang bs6", "bs6.id = ts.id_barang_6", "left");
        $builder->join("ref_barang bs7", "bs7.id = ts.id_barang_7", "left");
        $builder->join("ref_barang bs8", "bs8.id = ts.id_barang_8", "left");

        // JOIN ref_warna untuk sample: COALESCE dari ref_barang, fallback ke trans_sample_det
        $builder->join("ref_warna rw1", "rw1.id = COALESCE(bs1.id_warna, ts.id_warna_1)", "left");
        $builder->join("ref_warna rw2", "rw2.id = COALESCE(bs2.id_warna, ts.id_warna_2)", "left");
        $builder->join("ref_warna rw3", "rw3.id = COALESCE(bs3.id_warna, ts.id_warna_3)", "left");
        $builder->join("ref_warna rw4", "rw4.id = COALESCE(bs4.id_warna, ts.id_warna_4)", "left");
        $builder->join("ref_warna rw5", "rw5.id = COALESCE(bs5.id_warna, ts.id_warna_5)", "left");
        $builder->join("ref_warna rw6", "rw6.id = COALESCE(bs6.id_warna, ts.id_warna_6)", "left");
        $builder->join("ref_warna rw7", "rw7.id = COALESCE(bs7.id_warna, ts.id_warna_7)", "left");
        $builder->join("ref_warna rw8", "rw8.id = COALESCE(bs8.id_warna, ts.id_warna_8)", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');

            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(abx.qty) LIKE',     strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.gram) LIKE',    strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.gram_nd) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.kg) LIKE',      strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.loss) LIKE',    strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_walkorder'])) {
                $builder->where('abx.id_walkorder', $params['id_walkorder']);
            }

            if (!empty($params['ref_detail_id'])) {
                $builder->where('abx.ref_detail_id', $params['ref_detail_id']);
            }

            if (!empty($params['tipe_id'])) {
                $builder->where('abx.tipe_id', $params['tipe_id']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id ASC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            if (!empty($params['single'])) {
                $this->_data = $builder->get()->getRow();
            } else {
                $this->_data = $builder->get()->getResult();
            }
        } else {
            $builder->where("abx.id", $id);
            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt_detail($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table2 . " abx");

        $builder->select("count(1) as _cnt");

        $builder->join("trans_sales_order_det tso", "tso.id = abx.ref_detail_id and abx.tipe_id = 2", "left");
        $builder->join("ref_warna rw2", "rw2.id = tso.id_warna_1", "left");
        $builder->join("trans_sample_det ts", "ts.id = abx.ref_detail_id and abx.tipe_id = 1", "left");
        $builder->join("ref_warna rw1", "rw1.id = ts.id_warna_1", "left");

        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(abx.qty) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.gram) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.gram_nd) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.kg) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.loss) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
    // END WALKORDER DETAIL 

    // PROSES
    function getData_proses($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table3 . " abx");

        $builder->select(" abx.id, abx.id_walkorder, abx.id_proses, abx.keterangan,
                           pp.seq, pp.nama as proses, abx.approved_int, abx.harga
                        ");

        $builder->join("_jenis_proses_produksi pp", "pp.id = abx.id_proses", "inner");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(pp.proses) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_walkorder'])) {
                $builder->where('abx.id_walkorder', $params['id_walkorder']);
            }

            if (!empty($params['id_proses'])) {
                $builder->where('abx.id_proses', $params['id_proses']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id ASC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            if (!empty($params['single'])) {
               $this->_data = $builder->get()->getRow();
            }

            else {
                $this->_data = $builder->get()->getResult();
            }
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt_proses($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table3 . " abx");

        $builder->select("count(1) as _cnt");
        $builder->join("_jenis_proses_produksi pp", "pp.id = abx.id_proses", "inner");

        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(pp.proses) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }
    // END PROSES

    // PROSES UKURAN 
    function getData_proses_ukuran($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table4 . " abx");

        $builder->select(" abx.id, abx.id_walkorder_proses, abx.id_ukuran, abx.qty,
                           rk.kode_ukuran, rk.keterangan
                        ");

        $builder->join("ref_ukuran rk", "rk.id = abx.id_ukuran", "inner");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rk.kode_ukuran) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(rk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_walkorder_proses'])) {
                $builder->where('abx.id_walkorder_proses', $params['id_walkorder_proses']);
            }

            if (!empty($params['id_ukuran'])) {
                $builder->where('abx.id_ukuran', $params['id_ukuran']);
            }

            if (!empty($params['ref_detail_id'])) {
                $builder->where('abx.ref_detail_id', $params['ref_detail_id']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id ASC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("abx.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt_proses_ukuran($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table4 . " abx");

        $builder->select("count(1) as _cnt");
        $builder->join("ref_ukuran rk", "rk.id = abx.id_ukuran", "inner");

        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(rk.kode_ukuran) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(rk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

         if (!empty($params['id_walkorder_proses'])) {
            $builder->where('abx.id_walkorder_proses', $params['id_walkorder_proses']);
        }

        if (!empty($params['id_ukuran'])) {
            $builder->where('abx.id_ukuran', $params['id_ukuran']);
        }

        if (!empty($params['ref_detail_id'])) {
            $builder->where('abx.ref_detail_id', $params['ref_detail_id']);
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getCnt_produksi($id){
        $builder = $this->db->table($this->table4 . " abx");
        $builder->select("(SUM(abx.qty_prod)) as _cnt");
        $builder->where("ab.id_proses = 1");
        $builder->join($this->table3 . " ab", "ab.id = abx.id_walkorder_proses");
        $builder->where('ab.id_walkorder', $id);
        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getListProduksiUkuran($params){
        $builder = $this->db->table($this->table4 . ' twpu');
        $builder->select("
            twpu.id, twpu.id_walkorder_proses, twpu.id_ukuran, twpu.qty, twpu.qty_prod, twpu.ref_detail_id,
            rk.kode_ukuran, rk.keterangan, rk.key_ukuran,
            twp.id_walkorder, tw.tipe_id, tw.id_konsumen, tw.keterangan_style,

            -- Flag sumber warna
            CASE
                WHEN tw.tipe_id = 1 AND tsd.id_barang_1  IS NOT NULL THEN 'via_barang'
                WHEN tw.tipe_id = 2 AND tsod.id_barang_1 IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna,

            TRIM(BOTH ' - ' FROM
                COALESCE(rw1.kode_warna, '') ||
                CASE WHEN rw2.kode_warna IS NOT NULL THEN ' - ' || rw2.kode_warna ELSE '' END ||
                CASE WHEN rw3.kode_warna IS NOT NULL THEN ' - ' || rw3.kode_warna ELSE '' END ||
                CASE WHEN rw4.kode_warna IS NOT NULL THEN ' - ' || rw4.kode_warna ELSE '' END ||
                CASE WHEN rw5.kode_warna IS NOT NULL THEN ' - ' || rw5.kode_warna ELSE '' END ||
                CASE WHEN rw6.kode_warna IS NOT NULL THEN ' - ' || rw6.kode_warna ELSE '' END ||
                CASE WHEN rw7.kode_warna IS NOT NULL THEN ' - ' || rw7.kode_warna ELSE '' END ||
                CASE WHEN rw8.kode_warna IS NOT NULL THEN ' - ' || rw8.kode_warna ELSE '' END
            ) AS kode_warna,
            rw1.id as id_warna_1, rw2.id as id_warna_2, rw3.id as id_warna_3, rw4.id as id_warna_4,
            rw5.id as id_warna_5, rw6.id as id_warna_6, rw7.id as id_warna_7, rw8.id as id_warna_8,

            (
                CASE WHEN tw.tipe_id = 1 THEN
                    (SELECT x.harga_satuan FROM trans_sample_ukuran x 
                    WHERE x.id_sample_det = twpu.ref_detail_id AND x.id_ukuran = twpu.id_ukuran)
                WHEN tw.tipe_id = 2 THEN
                    (SELECT x.harga_satuan FROM trans_sales_order_ukuran x 
                    WHERE x.id_sales_order_det = twpu.ref_detail_id AND x.id_ukuran = twpu.id_ukuran)
                ELSE 0 END
            ) as harga,

            p.seq as proses,
            max(p.seq) OVER (PARTITION BY twp.id_walkorder) as proses_akhir
        ");

        $builder->join("ref_ukuran rk",              "twpu.id_ukuran = rk.id",              "inner");
        $builder->join("trans_walkorder_proses twp",  "twpu.id_walkorder_proses = twp.id",   "inner");
        $builder->join("_jenis_proses_produksi p",    "p.id = twp.id_proses",                "inner");
        $builder->join("trans_walkorder tw",          "twp.id_walkorder = tw.id",            "inner");

        // JOIN trans_sample_det (tipe_id = 1)
        $builder->join("trans_sample_det tsd",        "twpu.ref_detail_id = tsd.id AND tw.tipe_id = 1",  "left");

        // JOIN ref_barang untuk sample
        $builder->join("ref_barang bs1", "bs1.id = tsd.id_barang_1", "left");
        $builder->join("ref_barang bs2", "bs2.id = tsd.id_barang_2", "left");
        $builder->join("ref_barang bs3", "bs3.id = tsd.id_barang_3", "left");
        $builder->join("ref_barang bs4", "bs4.id = tsd.id_barang_4", "left");
        $builder->join("ref_barang bs5", "bs5.id = tsd.id_barang_5", "left");
        $builder->join("ref_barang bs6", "bs6.id = tsd.id_barang_6", "left");
        $builder->join("ref_barang bs7", "bs7.id = tsd.id_barang_7", "left");
        $builder->join("ref_barang bs8", "bs8.id = tsd.id_barang_8", "left");

        // JOIN trans_sales_order_det (tipe_id = 2)
        $builder->join("trans_sales_order_det tsod",  "twpu.ref_detail_id = tsod.id AND tw.tipe_id = 2", "left");

        // JOIN ref_barang untuk SO
        $builder->join("ref_barang bso1", "bso1.id = tsod.id_barang_1", "left");
        $builder->join("ref_barang bso2", "bso2.id = tsod.id_barang_2", "left");
        $builder->join("ref_barang bso3", "bso3.id = tsod.id_barang_3", "left");
        $builder->join("ref_barang bso4", "bso4.id = tsod.id_barang_4", "left");
        $builder->join("ref_barang bso5", "bso5.id = tsod.id_barang_5", "left");
        $builder->join("ref_barang bso6", "bso6.id = tsod.id_barang_6", "left");
        $builder->join("ref_barang bso7", "bso7.id = tsod.id_barang_7", "left");
        $builder->join("ref_barang bso8", "bso8.id = tsod.id_barang_8", "left");

        // JOIN ref_warna: COALESCE dari ref_barang masing-masing tipe, fallback ke id_warna di det
        $builder->join("ref_warna rw1", "rw1.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs1.id_warna  WHEN tw.tipe_id = 2 THEN bso1.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_1  WHEN tw.tipe_id = 2 THEN tsod.id_warna_1  ELSE NULL END)", "left");
        $builder->join("ref_warna rw2", "rw2.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs2.id_warna  WHEN tw.tipe_id = 2 THEN bso2.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_2  WHEN tw.tipe_id = 2 THEN tsod.id_warna_2  ELSE NULL END)", "left");
        $builder->join("ref_warna rw3", "rw3.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs3.id_warna  WHEN tw.tipe_id = 2 THEN bso3.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_3  WHEN tw.tipe_id = 2 THEN tsod.id_warna_3  ELSE NULL END)", "left");
        $builder->join("ref_warna rw4", "rw4.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs4.id_warna  WHEN tw.tipe_id = 2 THEN bso4.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_4  WHEN tw.tipe_id = 2 THEN tsod.id_warna_4  ELSE NULL END)", "left");
        $builder->join("ref_warna rw5", "rw5.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs5.id_warna  WHEN tw.tipe_id = 2 THEN bso5.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_5  WHEN tw.tipe_id = 2 THEN tsod.id_warna_5  ELSE NULL END)", "left");
        $builder->join("ref_warna rw6", "rw6.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs6.id_warna  WHEN tw.tipe_id = 2 THEN bso6.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_6  WHEN tw.tipe_id = 2 THEN tsod.id_warna_6  ELSE NULL END)", "left");
        $builder->join("ref_warna rw7", "rw7.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs7.id_warna  WHEN tw.tipe_id = 2 THEN bso7.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_7  WHEN tw.tipe_id = 2 THEN tsod.id_warna_7  ELSE NULL END)", "left");
        $builder->join("ref_warna rw8", "rw8.id = COALESCE(CASE WHEN tw.tipe_id = 1 THEN bs8.id_warna  WHEN tw.tipe_id = 2 THEN bso8.id_warna  ELSE NULL END, CASE WHEN tw.tipe_id = 1 THEN tsd.id_warna_8  WHEN tw.tipe_id = 2 THEN tsod.id_warna_8  ELSE NULL END)", "left");

        if (!empty($params['id_walkorder'])) {
            $builder->where('tw.id', $params['id_walkorder']);
        }

        if (!empty($params['id_proses'])) {
            $builder->where('twp.id_proses', $params['id_proses']);
        }

        if (!empty($params['id_ukuran'])) {
            $builder->where('twp.id_ukuran', $params['id_ukuran']);
        }

        if (!empty($params['key_ukuran'])) {
            $builder->where('rk.key_ukuran', $params['key_ukuran']);
        }

        if (!empty($params['kode_warna'])) {
            $builder->where('LOWER(rw1.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw2.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw3.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw4.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw5.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw6.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw7.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
            $builder->orWhere('LOWER(rw8.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
        }

        if (!empty($params['last_proses']) && !empty($params['id_walkorder'])) {
            $builder->where('twp.id_proses = (SELECT MAX(tx.id_proses) FROM trans_walkorder_proses tx WHERE tx.id_walkorder = ' . $params['id_walkorder'] . ')');
        }

        if (!empty($params['kata_kunci'])) {
            $builder->groupStart();
            $builder->where('LOWER(rw1.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw2.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw3.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw4.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw5.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw6.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw7.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rw8.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rk.kode_ukuran) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->groupEnd();
        }

        $builder->orderBy("twp.id_walkorder DESC, twpu.ref_detail_id, p.seq ASC, twpu.id_ukuran");

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    
    // END PROSES UKURAN 

    // WARNA    
    function getData_warna($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table5 . " abx");

        $id_gudang = !empty($params['id_gudang']) ? $params['id_gudang'] : null;

        $subQtyOnHand = "COALESCE((
            SELECT tbh.jumlah
            FROM trans_barang_history tbh
            WHERE tbh.id_barang = abx.id_barang
            " . (!empty($id_gudang) ? "AND tbh.id_gudang = $id_gudang" : "") . "
            ORDER BY tbh.year DESC, tbh.month DESC
            LIMIT 1
        ), 0) AS kuota_history";

        $builder->select(" 
            abx.id, 
            abx.id_walkorder_detail, 
            abx.id_warna, 
            abx.id_barang,
            abx.persen,
            abx.gram, abx.gram_nd, abx.kg, abx.loss, abx.kg_loss, abx.total,
            abx.kuota, abx.kuota_tambah, 
            COALESCE(rb.nama_barang, rw.kode_warna) AS kode_warna,
            rw.keterangan as warna_keterangan,

            -- Flag sumber warna
            CASE 
                WHEN abx.id_barang IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna,

            -- Qty on hand dari trans_barang_history
            {$subQtyOnHand}
        ");

        // JOIN ref_barang (nullable)
        $builder->join("ref_barang rb", "rb.id = abx.id_barang", "left");

        // JOIN ref_warna: prioritaskan warna dari ref_barang, fallback ke id_warna di abx
        $builder->join("ref_warna rw", "rw.id = COALESCE(rb.id_warna, abx.id_warna)", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');

            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rw.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_walkorder_detail'])) {
                $builder->where('abx.id_walkorder_detail', $params['id_walkorder_detail']);
            }

            if (!empty($params['id_warna'])) {
                $builder->where('abx.id_warna', $params['id_warna']);
            }

            if (!empty($params['id_barang'])) {
                $builder->where('abx.id_barang', $params['id_barang']);
            }

            if (!empty($order)) {
                $builder->orderBy($order[0]['field'], $order[0]['dir'], TRUE);
            } else {
                $builder->orderBy('abx.id ASC');
            }

            if (empty($offset)) $offset = 0;
            if (empty($limit)) $limit = 10;

            $builder->limit($limit, $offset);

            if (!empty($params['single'])) {
                $this->_data = $builder->get()->getRow();
            } else {
                $this->_data = $builder->get()->getResult();
            }

        } else {
            $builder->where("abx.id", $id);
            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    function getDataCnt_warna($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table5 . " abx");

        $builder->select("count(1) as _cnt");
        // JOIN ref_barang (nullable)
        $builder->join("ref_barang rb", "rb.id = abx.id_barang", "left");

        // JOIN ref_warna: prioritaskan warna dari ref_barang, fallback ke id_warna di abx
        $builder->join("ref_warna rw", "rw.id = COALESCE(rb.id_warna, abx.id_warna)", "left");

        $builder->where('abx.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(rw.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    // WARNA    
    function getData_warna_print($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table5 . " abx");

        $builder->select(" 
            abx.id_warna,
            abx.id_barang,

            CASE 
                WHEN abx.id_barang IS NOT NULL THEN 'via_barang'
                ELSE 'via_warna'
            END AS sumber_warna,

            CASE
                WHEN abx.id_barang IS NOT NULL THEN COALESCE(NULLIF(rb.keterangan, ''), rw.keterangan)
                ELSE rw.keterangan
            END AS kode_warna,

            SUM(abx.gram)        as gram,
            SUM(abx.kg)          as kg,
            SUM(abx.loss)        as loss,
            SUM(abx.kg_loss)     as kg_loss,
            SUM(abx.total)       as total,
            SUM(abx.kuota)       as kuota,
            SUM(abx.kuota_tambah) as kuota_tambah,
            '-' as total_sementara,
            (
                SELECT COALESCE(SUM(tbtd.qty), 0)
                FROM trans_barang_trf_detail tbtd
                INNER JOIN trans_barang_trf_header tbth ON tbth.id = CAST(tbtd.id_header AS INTEGER)
                WHERE tbtd.kode_walkorder = (
                        SELECT wo2.kode_walkorder 
                        FROM trans_walkorder wo2 
                        WHERE wo2.id = " . (int)$id . "
                    )
                AND tbtd.id_barang      = abx.id_barang
                AND tbth.id_gudang_asal = 1
            ) AS total_qty_trf,
             (
                SELECT COALESCE(SUM(tbd.qty), 0)
                FROM trans_barang_detail tbd
                INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                 WHERE tbh.no_ref_wo = (
                        SELECT wo3.kode_walkorder 
                        FROM trans_walkorder wo3 
                        WHERE wo3.id = " . (int)$id . "
                    )
                AND tbd.id_barang      = abx.id_barang
            ) AS total_qty_pakai,
             (
                (
                    SELECT COALESCE(SUM(tbtd.qty), 0)
                    FROM trans_barang_trf_detail tbtd
                    INNER JOIN trans_barang_trf_header tbth ON tbth.id = CAST(tbtd.id_header AS INTEGER)
                    WHERE tbtd.kode_walkorder = (
                            SELECT wo2.kode_walkorder 
                            FROM trans_walkorder wo2 
                            WHERE wo2.id = " . (int)$id . "
                        )
                    AND tbtd.id_barang      = abx.id_barang
                    AND tbth.id_gudang_asal = 1
                )
                -
                (
                    SELECT COALESCE(SUM(tbd.qty), 0)
                    FROM trans_barang_detail tbd
                    INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                    WHERE tbh.no_ref_wo = (
                            SELECT wo3.kode_walkorder 
                            FROM trans_walkorder wo3 
                            WHERE wo3.id = " . (int)$id . "
                        )
                    AND tbd.id_barang      = abx.id_barang
                )
            ) AS sisa
        ");

        // JOIN ref_barang (nullable)
        $builder->join("ref_barang rb", "rb.id = abx.id_barang", "left");

        // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di abx
        $builder->join("ref_warna rw", "rw.id = COALESCE(rb.id_warna, abx.id_warna)", "left");

        $builder->join("trans_walkorder_detail wodet", "wodet.id = abx.id_walkorder_detail", "inner");
        $builder->join("trans_walkorder wo",           "wo.id = wodet.id_walkorder",          "inner");

        $builder->where('wo.id', $id);
        $builder->groupBy('abx.id_warna, abx.id_barang, rb.nama_barang, rb.keterangan, rw.kode_warna, rw.keterangan');

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function getData_warna_print_prod($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
{
    $id = (int)$id;

    $builder = $this->db->table($this->table5 . " abx");

    // 🌟 Subquery gabungan lot_no dari kedua sumber, per id_barang
    $lotSubquery = "
        (
            SELECT tbtd.id_barang, tbtd.lot_no
            FROM trans_barang_trf_detail tbtd
            INNER JOIN trans_barang_trf_header tbth ON tbth.id = CAST(tbtd.id_header AS INTEGER)
            WHERE tbtd.kode_walkorder = (
                    SELECT wo2.kode_walkorder 
                    FROM trans_walkorder wo2 
                    WHERE wo2.id = {$id}
                )
            AND tbth.id_gudang_asal = 1

            UNION

            SELECT tbd.id_barang, tbd.lot_no
            FROM trans_barang_detail tbd
            INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
            WHERE tbh.no_ref_wo = (
                    SELECT wo3.kode_walkorder 
                    FROM trans_walkorder wo3 
                    WHERE wo3.id = {$id}
                )
        ) lot
    ";

    $builder->select(" 
        abx.id_warna,
        abx.id_barang,
        lot.lot_no,

        CASE 
            WHEN abx.id_barang IS NOT NULL THEN 'via_barang'
            ELSE 'via_warna'
        END AS sumber_warna,

        CASE
            WHEN abx.id_barang IS NOT NULL THEN COALESCE(NULLIF(rb.keterangan, ''), rw.keterangan)
            ELSE rw.keterangan
        END AS kode_warna,

        SUM(abx.gram)        as gram,
        SUM(abx.kg)          as kg,
        SUM(abx.loss)        as loss,
        SUM(abx.kg_loss)     as kg_loss,
        SUM(abx.total)       as total,
        SUM(abx.kuota)       as kuota,
        SUM(abx.kuota_tambah) as kuota_tambah,
        '-' as total_sementara,

        (
            SELECT COALESCE(SUM(tbtd.qty), 0)
            FROM trans_barang_trf_detail tbtd
            INNER JOIN trans_barang_trf_header tbth ON tbth.id = CAST(tbtd.id_header AS INTEGER)
            WHERE tbtd.kode_walkorder = (
                    SELECT wo2.kode_walkorder 
                    FROM trans_walkorder wo2 
                    WHERE wo2.id = {$id}
                )
            AND tbtd.id_barang      = abx.id_barang
            AND tbtd.lot_no         = lot.lot_no
            AND tbth.id_gudang_asal = 1
        ) AS total_qty_trf,

        (
            SELECT COALESCE(SUM(tbd.qty), 0)
            FROM trans_barang_detail tbd
            INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
            WHERE tbh.no_ref_wo = (
                    SELECT wo3.kode_walkorder 
                    FROM trans_walkorder wo3 
                    WHERE wo3.id = {$id}
                )
            AND tbd.id_barang = abx.id_barang
            AND tbd.lot_no    = lot.lot_no
        ) AS total_qty_pakai,

        (
            (
                SELECT COALESCE(SUM(tbtd.qty), 0)
                FROM trans_barang_trf_detail tbtd
                INNER JOIN trans_barang_trf_header tbth ON tbth.id = CAST(tbtd.id_header AS INTEGER)
                WHERE tbtd.kode_walkorder = (
                        SELECT wo2.kode_walkorder 
                        FROM trans_walkorder wo2 
                        WHERE wo2.id = {$id}
                    )
                AND tbtd.id_barang      = abx.id_barang
                AND tbtd.lot_no         = lot.lot_no
                AND tbth.id_gudang_asal = 1
            )
            -
            (
                SELECT COALESCE(SUM(tbd.qty), 0)
                FROM trans_barang_detail tbd
                INNER JOIN trans_barang_header tbh ON tbh.id = CAST(tbd.id_header AS INTEGER)
                WHERE tbh.no_ref_wo = (
                        SELECT wo3.kode_walkorder 
                        FROM trans_walkorder wo3 
                        WHERE wo3.id = {$id}
                    )
                AND tbd.id_barang = abx.id_barang
                AND tbd.lot_no    = lot.lot_no
            )
        ) AS sisa
    ");

    // JOIN ref_barang (nullable)
    $builder->join("ref_barang rb", "rb.id = abx.id_barang", "left");

    // JOIN ref_warna: COALESCE dari ref_barang, fallback ke id_warna di abx
    $builder->join("ref_warna rw", "rw.id = COALESCE(rb.id_warna, abx.id_warna)", "left");

    $builder->join("trans_walkorder_detail wodet", "wodet.id = abx.id_walkorder_detail", "inner");
    $builder->join("trans_walkorder wo",           "wo.id = wodet.id_walkorder",          "inner");

    // 🌟 JOIN ke daftar lot_no gabungan
    $builder->join($lotSubquery, "lot.id_barang = abx.id_barang", "left", false);

    $builder->where('wo.id', $id);

    // 🌟 lot.lot_no ikut masuk GROUP BY supaya row pecah per lot_no
    $builder->groupBy('abx.id_warna, abx.id_barang, lot.lot_no, rb.nama_barang, rb.keterangan, rw.kode_warna, rw.keterangan');

    $this->_data = $builder->get()->getResult();

    return $this->_data;
}
    // END WARNA    
}
