<?php

namespace App\Models;

use CodeIgniter\Model;

class PrModel extends Model
{
    protected $table = "";
    protected $primaryKey = "id";
    protected $session;
    public $id;

    public function __construct()
    {
        parent::__construct();
        $this->session = session();
    }

    public function delete($id = null, bool $purge = false)
    {
        $builder = $this->db->table($this->table);

        if (is_array($id)) {
            $builder->whereIn($this->primaryKey, $id);
        } else {
            $builder->where($this->primaryKey, $id);
        }

        $result = $builder->delete();

        return $result;
    }

    public function deleteRecord($table, $column, $id)
    {
        $builder = $this->db->table($table);

        if (is_array($id)) {
            $builder->whereIn($column, $id);
        } else {
            $builder->where($column, $id);
        }

        $result = $builder->delete();

        return $result;
    }

    public function deleteRecordCondition($table, $column, $id, $parent_id, $parent_value)
    {
        $builder = $this->db->table($table);

        $builder->whereNotIn($column, $id);
        $builder->where($parent_id, $parent_value);

        $result = $builder->delete();

        return $result;
    }

    public function insertRecordGetid($table, $data)
    {

        $builder = $this->db->table($table);
        if ($builder->insert($data) === false) {
            $error = $this->db->error();
            var_dump($error);
            die;
        }
        return $this->db->insertID();
    }

    public function updateRecord($table, $data, $column, $id)
    {
        $exec = $this->db->table($table)->update($data, array($column => $id));
        return $exec;
    }
    public function updateRecords($table, $data, $arr)
    {
        $exec = $this->db->table($table)->update($data, $arr);
        return $exec;
    }

    public function sortParentchild($_modules)
    {
        return $this->buildTree($_modules, 0);
    }

    public function deleteRecordMultipleColumn($table, $arr)
    {
        $builder = $this->db->table($table);

        if (is_array($arr)) {
            $builder->where($arr);
        }

        $result = $builder->delete();

        return $result;
    }

    private function buildTree($elements, $parentId = 0)
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element->pid == $parentId) {
                $element->treename = $element->name;
                $branch[] = $element;
            } else {
                $element->treename = $element->name;
                if (strlen($element->treename) >= 14 && substr($element->treename, 0, 14) == "&nbsp;|_&nbsp;") {
                    $element->treename = "&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;" . $element->name;
                } elseif (strlen($element->treename) >= 32 && substr($element->treename, 0, 32) == "&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;") {
                    $element->treename = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;" . $element->name;
                } elseif (strlen($element->treename) >= 32 && substr($element->treename, 0, 50) == "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;") {
                    $element->treename = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|_&nbsp;" . $element->name;
                } elseif (strlen($element->treename) >= 68 && substr($element->treename, 0, 60) == "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;") {
                    $element->treename = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.|_&nbsp;" . $element->name;
                } else {
                    $element->treename = "&nbsp;|_&nbsp;" . $element->name;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}
