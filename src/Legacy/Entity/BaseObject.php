<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Entity;

use App\Legacy\Database;
use Exception;

class BaseObject
{

    protected $data;

    protected Database $db;

    protected string $idField = 'id';

    public string $tableName;

    /**
     * BaseObject constructor.
     *
     * @param Database $db The database to load from.
     * @param int      $id The ID to load.
     *
     * @throws Exception
     */
    public function __construct(Database $db, int $id = 0)
    {
        $this->db = $db;

        if ($id) {
            $this->load($id);
        }
    }

    public function clearId()
    {
        $this->data[$this->idField] = 0;
    }

    public function delete()
    {
        if ($this->data[$this->idField] > 0) {
            $query = 'DELETE FROM ' . $this->tableName . ' WHERE ' . $this->idField . " = '" . addslashes($this->data[$this->idField]) . "'";
            $result = $this->db->query($query);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    public function getErrorMessage()
    {
        return $this->db->getErrorMessage();
    }

    public function getErrorNumber()
    {
        return $this->db->getErrorNumber();
    }

    public function getId()
    {
        return $this->data[$this->idField];
    }

    public function load($id)
    {
        if (!is_numeric($id)) {
            throw new Exception('could not find ' . get_class($this) . ' with ID ' . $id);
        }

        $query = 'SELECT * FROM ' . $this->tableName . ' WHERE ' . $this->idField . " = '" . addslashes($id) . "'";
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        $this->data = $row;
    }

    public function save()
    {
        $queryElements = [];

        foreach ($this->data as $field => $value) {
            if ($field == $this->idField) {
                continue;
            }

            if ($field == 'completed' && empty($value)) {
                continue;
            }

            if ($value == 'NOW()') {
                //
                // XXX - Expand this
                //
                array_push($queryElements, $field . '=' . $value);
            } else {
                array_push($queryElements, $field . "='" . addslashes($value) . "'");
            }
        }

        if (empty($this->data[$this->idField])) {
            $query = 'INSERT INTO ' . $this->tableName . ' SET ' . implode(',', $queryElements);
        } else {
            $query = 'UPDATE ' . $this->tableName . ' SET ' . implode(',', $queryElements) . ' WHERE ' . $this->idField . " = '" . addslashes($this->data[$this->idField]) . "'";
        }

        $result = $this->db->query($query);

        if (!$result) {
            return false;
        }

        if (empty($this->data[$this->idField])) {
            $this->data[$this->idField] = $this->db->lastInsertId();
        }

        return true;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
