<?php

namespace Modules\Referensi\Models;

class BarangModel extends \App\Models\PrModel
{

    protected $table = "ref_barang";
    protected $tblSatuan = "ref_satuan";
    protected $tblJenisBarang = "ref_jenis_barang";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->join($this->tblJenisBarang . " abx", "uk.id_jenis_barang = abx.id", "inner");
        $builder->join($this->tblSatuan . " bbx", "uk.id_satuan = bbx.id", "inner");
        $builder->join("ref_warna rw", "uk.id_warna = rw.id", "left");
        $builder->select("uk.id, uk.nama_barang, uk.keterangan, uk.id_satuan, uk.id_jenis_barang, uk.stok_minimum, uk.harga_satuan,
                          uk.kode_barang, abx.nama_jenis_barang, bbx.nama_satuan, uk.id_warna, rw.kode_warna, rw.keterangan as kode");

        if ($id == null or $id == "") {
            $builder->where('uk.active = 1');
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(uk.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(uk.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(abx.nama_jenis_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(bbx.nama_satuan) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->groupEnd();
            }

            if(!empty($params['nama_barang'])){
                $builder->where('uk.nama_barang', $params['nama_barang']);
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
        $builder->join($this->tblJenisBarang . " abx", "uk.id_jenis_barang = abx.id", "inner");
        $builder->join($this->tblSatuan . " bbx", "uk.id_satuan = bbx.id", "inner");
        $builder->join("ref_warna rw", "uk.id_warna = rw.id", "left");

        $builder->where('uk.active = 1');

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(uk.nama_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(uk.kode_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(abx.nama_jenis_barang) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(bbx.nama_satuan) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function generateKodeBarang()
    {
        $kd = "BRG";
        $builder = $this->db->table($this->table . ' a');
        $builder->select("LEFT(kode_barang, 7) AS tgl, RIGHT( kode_barang, 4 ) AS kode ");

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
}
