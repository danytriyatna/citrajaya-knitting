<?php 
namespace Modules\Utility\Models;

class PrivilegeModel extends \App\Models\PrModel
{
    protected $table = "sec_role_priv";
    protected $_data = null;
	protected $mauth = null;

    public function __construct()
    {
        parent::__construct();
        $this->mauth = new \IonAuth\Models\IonAuthModel();

    }

    public function getPrivilege($role_id)
    {
        $sql = "SELECT b.id, b.name, b.alias, COALESCE(b.pid,0) as pid, LTRIM(RTRIM(b.url)) as url, b.seq, a.allow_view, 
                    a.allow_new, a.allow_edit, a.allow_delete, a.allow_print, a.allow_approve 
                FROM sec_role_priv a
                    LEFT JOIN sec_modul b ON a.module_id = b.id
                WHERE b.publish=1 AND a.allow_view=1 AND a.role_id=?
                UNION 
                SELECT m.id, m.name, m.alias, COALESCE(m.pid,0) as pid, LTRIM(RTRIM(m.url)) as url, m.seq, 0 as allow_view,
                    0 as allow_new, 0 as allow_edit, 0 as allow_delete, 0 as allow_print,0 as allow_approve
                FROM sec_modul m WHERE m.publish=1 AND NOT EXISTS(
                    select 1 from sec_role_priv p where p.module_id = m.id AND p.allow_view = 1 AND p.role_id=?
                ) ORDER BY pid, seq, id";

        $Q = $this->db->query($sql, array($role_id, $role_id));
        $this->_data = $Q->getResult();
        return $this->_data;
    }

    public function updatePrivilege($role_id)
    {
        $this->db->transBegin();

        $this->db->table($this->table)->update(['allow_view' => 0,'allow_new' => 0,'allow_edit' => 0,'allow_delete' => 0,'allow_print' => 0,'allow_approve' => 0], ['role_id' => $role_id]);

        if (isset($_POST["moduleid"]) && is_array($_POST["moduleid"])) {
            foreach ($_POST["moduleid"] as $val) :
                if ($val > 0) {
                    $exists = $this->db->table($this->table)->select("count(module_id) as _cnt")->where("role_id", $role_id)->where("module_id", $val)->get()->getRow()->_cnt;
                    if ($exists > 0) {
                        $this->db->table($this->table)->update(['allow_view' => 1], ['role_id' => $role_id, 'module_id' => $val]);
                    } else {
                        $this->db->table($this->table)->insert(['role_id' => $role_id, 'module_id' => $val, 'allow_view' => 1 , 'allow_new' => 0, 'allow_edit' => 0, 'allow_delete' => 0, 'allow_print' => 0, 'allow_approve' => 0]);
                    }
                }
            endforeach;
        }

        if(isset($_POST["auth_new"])){
        foreach ($_POST["auth_new"] as $val) :
            if ($val > 0) {
		        $exists = $this->db->table($this->table)->select("count(module_id) as _cnt")->where("role_id", $role_id)->where("module_id", $val)->get()->getRow()->_cnt;
                if ($exists > 0) {
            		$this->db->table($this->table)->update(['allow_new' => 1], ['role_id' => $role_id, 'module_id' => $val]);
                } 
            }
        endforeach;
        }

        if(isset($_POST["auth_edit"])){
        foreach ($_POST["auth_edit"] as $val) :
            if ($val > 0) {
		        $exists = $this->db->table($this->table)->select("count(module_id) as _cnt")->where("role_id", $role_id)->where("module_id", $val)->get()->getRow()->_cnt;
                if ($exists > 0) {
            		$this->db->table($this->table)->update(['allow_edit' => 1], ['role_id' => $role_id, 'module_id' => $val]);
                } 
            }
        endforeach;
        }

        if(isset($_POST["auth_del"])){
        foreach ($_POST["auth_del"] as $val) :
            if ($val > 0) {
		        $exists = $this->db->table($this->table)->select("count(module_id) as _cnt")->where("role_id", $role_id)->where("module_id", $val)->get()->getRow()->_cnt;
                if ($exists > 0) {
            		$this->db->table($this->table)->update(['allow_delete' => 1], ['role_id' => $role_id, 'module_id' => $val]);
                } 
            }
        endforeach;
        }

        if(isset($_POST["auth_print"])){
        foreach ($_POST["auth_print"] as $val) :
            if ($val > 0) {
		        $exists = $this->db->table($this->table)->select("count(module_id) as _cnt")->where("role_id", $role_id)->where("module_id", $val)->get()->getRow()->_cnt;
                if ($exists > 0) {
            		$this->db->table($this->table)->update(['allow_print' => 1], ['role_id' => $role_id, 'module_id' => $val]);
                } 
            }
        endforeach;
        }

        if(isset($_POST["auth_approve"])){
        foreach ($_POST["auth_approve"] as $val) :
            if ($val > 0) {
		        $exists = $this->db->table($this->table)->select("count(module_id) as _cnt")->where("role_id", $role_id)->where("module_id", $val)->get()->getRow()->_cnt;
                if ($exists > 0) {
            		$this->db->table($this->table)->update(['allow_approve' => 1], ['role_id' => $role_id, 'module_id' => $val]);
                } 
            }
        endforeach;
        }

        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            $return["success"] = false;
            //throw new Exception('Update otorisasi gagal !!');
        } else {
            $this->db->transCommit();
            $return["success"] = true;
        }

        return $return["success"];
    }
}