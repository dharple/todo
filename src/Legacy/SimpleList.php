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

use App\Legacy\Entity\BaseObject;
use Exception;

class SimpleList
{

    protected Database $db;

    protected BaseObject $obj;

    protected string $objName;

    /**
     * SimpleList constructor.
     *
     * @param Database $db      The database to load from.
     * @param string   $objName The class to load for.
     *
     * @throws Exception
     */
    public function __construct(Database $db, string $objName)
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
}
