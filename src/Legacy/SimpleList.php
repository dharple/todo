<?php

namespace App\Legacy;

class SimpleList
{

    public $obj;
    public $objName;

    public function __construct($db, $objName)
    {
        $this->db = $db;
        $this->objName = $objName;
        $this->obj = new $objName($db);
    }

    public function load($criteria)
    {
        $ret = [];

        $query = 'SELECT ' . $this->obj->tableName . '.* FROM ' . $this->obj->tableName . ' ' . $criteria;
        $result = $this->db->query($query);
        if (!$result) {
            return $ret;
        }

        while ($row = $this->db->fetchAssoc($result)) {
            $work = new $this->objName($this->db);
            $work->setData($row);
            array_push($ret, $work);
        }
        
        return $ret;
    }

    public function count($criteria)
    {
        $query = 'SELECT COUNT(*) FROM ' . $this->obj->tableName . ' ' . $criteria;
        $result = $this->db->query($query);
        if (!$result) {
            return $ret;
        }

        $row = $this->db->fetchRow($result);

        return intval($row[0]);
    }
}
