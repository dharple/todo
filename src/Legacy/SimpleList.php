<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

class SimpleList
{

    public $db;
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
            return 0;
        }

        $row = $this->db->fetchRow($result);

        return intval($row[0]);
    }
}
