<?php

namespace Modules\Utility\Models;

class UserModel extends \App\Models\PrModel
{

    protected $table = "sec_user";
    protected $_data = null;
    protected $primaryKey = 'id';
    protected $mauth = null;

    protected $allowedFields = [
        'username',
        'password'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->mauth = new \IonAuth\Models\IonAuthModel();
    }

    function getData($id = null, $offset = null, $limit = null, $order = null, $filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " u");

        $builder->select("u.id, u.username, u.prefix, u.nip, u.full_name, u.email, u.created_on, u.last_login, u.active, u.nip,u.file_id_photo, 
            ur.role_id, r.name as role_name, r.description as role_alias, u.phone ");
        $builder->join("sec_user_role ur", "ur.user_id=u.id", "inner");
        $builder->join("sec_role r", "r.id=ur.role_id", "inner");

        if ($params["filter_role"]) {
            $builder->where("r.id", $params["filter_role"]);
        }

        if ($params["filter_status"] != "") {
            $builder->where("u.active", $params["filter_status"]);
        }

        if ($id == null or $id == "") {
            if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
                $builder->groupStart();
                $builder->where('LOWER(u.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(u.username) LIKE', strtolower("%{$filters[0]['value']}%"));
                $builder->orWhere('LOWER(u.email) LIKE', strtolower("%{$filters[0]['value']}%"));
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
            $builder->where("u.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }
    function getDataByRoleId($id = null, $aktif = null)
    {
        $builder = $this->db->table($this->table . " u");

        $builder->select("u.id, u.username, u.prefix,u.full_name, u.email, u.created_on, u.last_login, u.active, u.nip,u.file_id_photo, 
            ur.role_id, r.name as role_name, r.description as role_alias, u.phone ");
        $builder->join("sec_user_role ur", "ur.user_id=u.id", "inner");
        $builder->join("sec_role r", "r.id=ur.role_id", "inner");


        $builder->orderBy('id', 'DESC');
        $builder->where("r.id", $id);

        if ($aktif != null) {
            $builder->where("u.active", $aktif);
        }

        $this->_data = $builder->get()->getResult();


        return $this->_data;
    }

    function getDataCnt($filters = null, $params = null)
    {
        $builder = $this->db->table($this->table . " u");

        $builder->select("count(u.id) as _cnt");
        $builder->join("sec_user_role ur", "ur.user_id=u.id", "inner");
        $builder->join("sec_role r", "r.id=ur.role_id", "inner");

        if (!empty($filters) && is_array($filters) && count($filters) >= 1) {
            $builder->groupStart();
            $builder->where('LOWER(u.full_name) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(u.username) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->orWhere('LOWER(u.email) LIKE', strtolower("%{$filters[0]['value']}%"));
            $builder->groupEnd();
        }

        $this->_data = $builder->get()->getRow()->_cnt;

        return $this->_data;
    }

    function getRoles()
    {
        $builder = $this->db->table("sec_role");
        $builder->where("active", 1);

        if ($this->session->role_name == "superadmin") {
            $builder->where("id >=", 1);
        } elseif ($this->session->role_name == "admin") {
            $builder->where("id >", 1);
        }

        $builder->orderBy("id", "asc");
        $this->_data = $builder->get()->getResult();
        return $this->_data;
    }

    function getUsers($id = null, $search_string = null, $positionId = null)
    {
        $builder = $this->db->table($this->table . " u");

        $builder->select("u.id, u.username, u.prefix,u.full_name, u.email, u.created_on, u.last_login, u.active, u.nip,u.file_id_photo, 
            ur.role_id, r.name as role_name, u.phone, u.id_perusahaan ");
        $builder->join("sec_user_role ur", "ur.user_id=u.id", "inner");
        $builder->join("sec_role r", "r.id=ur.role_id", "inner");

        if ($id == null or $id == "") {

            if ($this->session->role_name == "superadmin") {
                $builder->where("u.id >=", 1);
            } elseif ($this->session->role_name == "admin") {
                $builder->where("u.id >", 1);
            }

            if (!empty($search_string)) {
                $builder->groupStart();
                $builder->like("u.full_name", $search_string);
                $builder->orLike("u.email", $search_string);
                $builder->groupEnd();
            }

            $builder->orderBy("u.username", "asc");

            $this->_data = $builder->get()->getResult();
        } else {
            $builder->where("u.id", $id);

            $this->_data = $builder->get()->getRow();
        }

        return $this->_data;
    }

    public function updateRole($id, $data)
    {
        $return = false;

        $this->db->transBegin();
        $this->db->table('sec_user_role')->update($data, ['user_id' => $id]);

        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            $return = false;
        } else {
            $this->db->transCommit();
            $return = false;
        }

        return $return;
    }

    function getDataByNip($nip)
    {
        $builder = $this->db->table($this->table . " u");

        $builder->select("u.id, u.username, u.nip, u.full_name")
            ->where("u.nip", $nip);

        $this->_data = $builder->get()->getRow();

        return $this->_data;
    }
}
