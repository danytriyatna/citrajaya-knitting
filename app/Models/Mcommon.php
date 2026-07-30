<?php 
namespace App\Models;

use CodeIgniter\Model;

class Mcommon extends Model
{
    protected $_data = null;

    public function __construct()
    {
        parent::__construct();
    }

    function checkMenuAccess($role_id, $menu_alias)
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'chk_menu_' . $role_id . '_' . md5($menu_alias);
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $isAllow = false;
        $builder = $this->db->table('sec_role_priv a');
        $data = $builder->select('a.allow_view')
                ->join('sec_modul b', 'a.module_id = b.id')
                ->where('a.role_id', $role_id)
                ->where('b.alias', $menu_alias)
                ->get()->getRow();
        if($data){
            $isAllow = (bool)$data->allow_view;
        }
        $cache->save($cacheKey, $isAllow, 3600);
        return $isAllow;
    }

    function getMenuAccessCRUD($role_id, $menu_alias)
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'crud_menu_' . $role_id . '_' . md5($menu_alias);
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $builder = $this->db->table('sec_role_priv a');
        $res = $builder->select('a.allow_view, a.allow_new, a.allow_edit, a.allow_delete, a.allow_print, a.allow_approve')
                ->join('sec_modul b', 'a.module_id = b.id')
                ->where('a.role_id', $role_id)
                ->where('b.alias', $menu_alias)
                ->get()->getRow();

        $cache->save($cacheKey, $res, 3600);
        return $res;
    }

    function getMenuByRoleID($roleid)
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'role_menu_' . $roleid;
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $builder = $this->db->table('sec_role_priv a');
        $builder->select('a.module_id, b.name as module_name, b.alias as module_alias, COALESCE(b.pid,0) AS module_pid, b.url as module_url, b.icon_cls as mod_icon_cls, b.seq as mod_seq, b.group as mod_group');
        $builder->join('sec_modul b', 'a.module_id = b.id');
        $builder->where(['b.publish' => 1, 'a.allow_view' => 1, 'a.role_id' => $roleid]);
        $builder->orderBy('b.pid, b.seq, a.module_id');
        $result = $builder->get()->getResultArray();

        $cache->save($cacheKey, $result, 3600);
        return $result;
    }

    function setLog($user_id, $module_alias, $trans_id, $activity, $description = "")
    {
		$server = \Config\Services::request()->getServer();
        $ip_address = $server['REMOTE_ADDR'];
        $http_agent = $server['HTTP_USER_AGENT'];
        $http_host = $server['HTTP_HOST'];
        $comp_name = gethostbyaddr($ip_address);

        $mac = "";

        $sql = "INSERT INTO log_activity(log_date,ip_address,comp_name,user_id,module_alias,
                trans_id,activity,description,http_agent,http_host,mac_address) 
                VALUES(NOW(),?,?,?,?,?,?,?,?,?,?);";

        $this->db->transStart();
        $this->db->query($sql, array($ip_address, $comp_name, $user_id,
            $module_alias, $trans_id, $activity, $description, $http_agent, $http_host, $mac));
        $this->db->transComplete();
    }

    


}