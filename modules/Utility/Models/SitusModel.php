<?php 
namespace Modules\Utility\Models;

class SitusModel extends \App\Models\PrModel
{
    protected $table = "setting_situs";
    protected $_data = null;

    protected $allowedFields = [
        'name_app',
        'title',
        'footer'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    function getData()
    {
        $cache = \Config\Services::cache();
        $data = $cache->get('setting_situs_row');
        if ($data !== null) {
            return $data;
        }

        $builder = $this->db->table($this->table);
        $this->_data = $builder->get()->getRow();
        $cache->save('setting_situs_row', $this->_data, 3600);
        return $this->_data;
    }
    
}