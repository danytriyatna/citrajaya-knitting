<?php

namespace Modules\Purchasing\Models;

class PaymentDetailModel extends \App\Models\PrModel
{

    protected $table = "trans_po_pembayaran_detail";
    protected $tblTerm = "ref_term";
    protected $tblPoHeader = "trans_po_header";

    protected $_data = null;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    function getDataDetail($idHeader = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->select("uk.id,uk.id_header, uk.id_po ,concat(uk.qty_receive, '/',uk.qty) as qty_status, uk.po_no,uk.po_date, uk.do_date, uk.qty, uk.qty_receive, uk.hutang, uk.total_bayar,sisa_bayar,uk.diskon, uk.grand_total");
        $builder->where("uk.id_header", $idHeader);

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }

    function getDataDetailRemaining($idHeader = null)
    {
        $builder = $this->db->table($this->table . " uk");
        $builder->select("sum(uk.total_bayar)+sum(uk.diskon) as grand_total, (sum(uk.total_bayar)+sum(uk.diskon)) - sum(uk.sisa_bayar) as sisa_bayar");
        $builder->where("uk.id_po", $idHeader);

        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }

    function getDataPO($idVendor = null)
    {
        $builder = $this->db->table($this->tblPoHeader . " uk");
        $builder->select("uk.id as id_header, uk.id as id_po, uk.po_no, uk.po_date, uk.po_date + concat(ebx.name)::INTERVAL AS do_date, uk.qty, uk.qty_payment as qty_receive, concat(uk.qty_payment, '/',uk.qty) as qty_status, uk.total as hutang, uk.total_payment as grand_total,(uk.total - uk.total_payment) as sisa_bayar");
        $builder->join($this->tblTerm . " ebx", "uk.id_term = ebx.id", "inner");
        $builder->where("uk.status!=", 2);
        $builder->where("uk.approve_status", 1);
        $builder->where('COALESCE(uk.total_payment, 0) < uk.total');
        $builder->where("uk.id_vendor", $idVendor);

        $this->_data = $builder->get()->getResult();

        return $this->_data;
    }
}
