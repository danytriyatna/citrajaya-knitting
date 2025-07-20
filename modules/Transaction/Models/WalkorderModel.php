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
                          abx.tipe_id, cbx.file_name, abx.id_gudang, abx.keterangan
                        ");

        $builder->join("ref_konsumen bbx", "abx.id_konsumen = bbx.id", "inner");
        // $builder->join("trans_sample ts", "ts.id = abx.ref_id and abx.tipe_id = 1", "left");
        // $builder->join("trans_sales_order tso", "tso.id = abx.ref_id and abx.tipe_id = 2", "left");
        $builder->join("_files cbx", "abx.file_id = cbx.id", "left");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(abx.kode_walkorder) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.ref_kode) LIKE', strtolower("%{$filters[0]['value']}%"));
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

        $builder->select("abx.id, abx.id_walkorder, abx.ref_detail_id, abx.qty, abx.gram, abx.gram_nd, abx.kg, abx.loss,
                          abx.kg_loss, abx.total, abx.tipe_id, abx.kuota, abx.kuota_tambah,
                          (case when abx.tipe_id = 2 then tso.id_warna_1 else ts.id_warna_1 end) as id_wdasar,
		                  (case when abx.tipe_id = 2 then rw2.kode_warna else rw1.kode_warna end) as wdasar
                        ");

        $builder->join("trans_sales_order_det tso", "tso.id = abx.ref_detail_id and abx.tipe_id = 2", "left");
        $builder->join("ref_warna rw2", "rw2.id = tso.id_warna_1", "left");
        $builder->join("trans_sample_det ts", "ts.id = abx.ref_detail_id and abx.tipe_id = 1", "left");
        $builder->join("ref_warna rw1", "rw1.id = ts.id_warna_1", "left");

        if ($id == null or $id == "") {
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

            if (!empty($params['id_walkorder'])) {
                $builder->where('abx.id_walkorder', $params['id_walkorder']);
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

            $this->_data = $builder->get()->getResult();
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
        $builder =  $this->db->table($this->table4 . ' twpu');
        $builder->select("
                            twpu.id, twpu.id_walkorder_proses, twpu.id_ukuran, twpu.qty, twpu.qty_prod, twpu.ref_detail_id,
                            rk.kode_ukuran, rk.keterangan, rk.key_ukuran,
                            twp.id_walkorder, tw.tipe_id, tw.id_konsumen, tw.keterangan_style,
                            rw.kode_warna, rw.id as id_warna,
                            (
                                case when tw.tipe_id = 1 then 
                                                    (select x.harga_satuan from trans_sample_ukuran x where x.id_sample_det = twpu.ref_detail_id and x.id_ukuran = twpu.id_ukuran)
                                        when tw.tipe_id = 2 then 
                                                    (select x.harga_satuan from trans_sales_order_ukuran x where x.id_sales_order_det = twpu.ref_detail_id and x.id_ukuran = twpu.id_ukuran)
                                        else 0 end
                            ) as harga,
                            p.seq as proses,
                            max(p.seq) OVER  (partition by twp.id_walkorder) as proses_akhir
                        ");
        $builder->join("ref_ukuran rk", "twpu.id_ukuran = rk.id", "inner");
        $builder->join("trans_walkorder_proses twp", "twpu.id_walkorder_proses = twp.id", "inner");
        $builder->join("_jenis_proses_produksi p ", "p.id = twp.id_proses", "inner");
        $builder->join("trans_walkorder tw", "twp.id_walkorder = tw.id", "inner");
        $builder->join("trans_sample_det tsd", "twpu.ref_detail_id = tsd.id and tw.tipe_id = 1", "left");
        $builder->join("trans_sales_order_det tsod", "twpu.ref_detail_id = tsod.id and tw.tipe_id = 2", "left");
        $builder->join("ref_warna rw", "rw.id = (case when tw.tipe_id = 1 then tsd.id_warna_1 when tw.tipe_id = 2 then tsod.id_warna_1 else -1 end)", "left");

        if(!empty($params['id_walkorder'])){
            $builder->where('tw.id', $params['id_walkorder']);
        }

        if(!empty($params['id_proses'])){
            $builder->where('twp.id_proses', $params['id_proses']);
        }

        if(!empty($params['id_ukuran'])){
            $builder->where('twp.id_ukuran', $params['id_ukuran']);
        }

        if(!empty($params['key_ukuran'])){
            $builder->where('rk.key_ukuran', $params['key_ukuran']);
        }

        if(!empty($params['kode_warna'])){
            // $builder->where('rw.kode_warna', $params['kode_warna']);
            $builder->where('LOWER(rw.kode_warna) LIKE', strtolower("%{$params['kode_warna']}%"));
        }

        if(!empty($params['last_proses']) && !empty($params['id_walkorder'])){
            $builder->where('twp.id_proses = (select max(tx.id_proses) from trans_walkorder_proses tx where tx.id_walkorder = '.$params['id_walkorder'].')');
        }

        if(!empty($params['kata_kunci'])){
            $builder->groupStart();
            $builder->where('LOWER(rw.kode_warna) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->orWhere('LOWER(rk.kode_ukuran) LIKE', strtolower("%{$params['kata_kunci']}%"));
            $builder->groupEnd();
        }

        // if (empty($params['offset'])) $params['offset'] = 0;
        // if (empty($params['limit']))  $params['limit'] = 10;

        // $builder->limit($params['limit'], $params['offset']);

        $builder->orderBy("twp.id_walkorder desc, twpu.ref_detail_id, p.seq asc, twpu.id_ukuran");

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    
    // END PROSES UKURAN 

    // WARNA    
    function getData_warna($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table5 . " abx");

        $builder->select(" abx.id, abx.id_walkorder_detail, abx.id_warna, abx.persen,
                           abx.gram, abx.gram_nd, abx.kg, abx.loss, abx.kg_loss, abx.total,
                           abx.kuota, abx.kuota_tambah, rw.kode_warna, rw.keterangan as warna_keterangan
                        ");

        $builder->join("ref_warna rw", "rw.id = abx.id_warna", "inner");

        if ($id == null or $id == "") {
            $builder->where('abx.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(rw.kode_warna) LIKE', strtolower("%{$filters[0]['value']}%"));
                // $builder->orWhere('LOWER(rk.keterangan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if (!empty($params['id_walkorder_detail'])) {
                $builder->where('abx.id_walkorder_detail', $params['id_walkorder_detail']);
            }

            if (!empty($params['id_warna'])) {
                $builder->where('abx.id_warna', $params['id_warna']);
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

    function getDataCnt_warna($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table5 . " abx");

        $builder->select("count(1) as _cnt");
        $builder->join("ref_warna rw", "rw.id = abx.id_warna", "inner");

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

        $builder->select(" abx.id, abx.id_walkorder_detail, abx.id_warna, abx.persen,
                           abx.gram, abx.gram_nd, abx.kg, abx.loss, abx.kg_loss, abx.total,
                           abx.kuota, abx.kuota_tambah, rw.kode_warna, rw.keterangan as warna_keterangan
                        ");

        $builder->join("ref_warna rw", "rw.id = abx.id_warna", "inner");
        
        $builder->join("trans_walkorder_detail wodet", "wodet.id = abx.id_walkorder_detail", "inner");
        $builder->join("trans_walkorder wo", "wo.id = wodet.id_walkorder", "inner");

        $builder->where('wo.id', $id);

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }
    // END WARNA    
}
