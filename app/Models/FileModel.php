<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
	protected $table                = '_files';
	protected $primaryKey           = 'id';
	protected $useAutoIncrement     = true;
	protected $returnType           = 'object';
	protected $allowedFields        = ['file_name','file_size','file_type','file_name_origin','active'];

	// Dates
	protected $useTimestamps        = true;
	protected $dateFormat           = 'datetime';
	protected $createdField         = 'created_at';
	protected $updatedField         = 'updated_at';


    function getFiles($id)
    {
        if (empty($id)) return null;
        $cache = \Config\Services::cache();
        $cacheKey = 'file_info_' . $id;
        $file = $cache->get($cacheKey);
        if ($file !== null) {
            return $file;
        }
        $file = $this->db->table($this->table)->where('id', $id)->get()->getRow();
        $cache->save($cacheKey, $file, 3600);
        return $file;
    }
}
