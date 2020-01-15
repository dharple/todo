<?php

require_once('Database.php');

class BaseObject
{

    public $db;
    public $data;
    public $tableName;
    public $idField;

    public function load($id)
    {
        $query = 'SELECT * FROM ' . $this->tableName . ' WHERE ' . $this->idField . " = '" . $id . "'";
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

            if ($value == 'NOW()') {
                //
                // XXX - Expand this
                //
                array_push($queryElements, $field . '=' . $value);
            } else {
                array_push($queryElements, $field . "='" . addslashes($value) . "'");
            }
        }

        if ($this->data[$this->idField] == 0) {
            $query = 'INSERT INTO ' . $this->tableName . ' SET ' . implode(',', $queryElements);
        } else {
            $query = 'UPDATE ' . $this->tableName . ' SET ' . implode(',', $queryElements) . ' WHERE ' . $this->idField . " = '" . $this->data[$this->idField] . "'";
        }

        $result = $this->db->query($query);

        if (!$result) {
            return false;
        }

        if ($this->data[$this->idField] == 0) {
            $this->data[$this->idField] = $this->db->lastInsertId();
        }

        return true;
    }

    public function delete()
    {
        if ($this->data[$this->idField] > 0) {
            $query = 'DELETE FROM ' . $this->tableName . ' WHERE ' . $this->idField . " = '" . $this->data[$this->idField] . "'";
            $result = $this->db->query($query);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->data[$this->idField];
    }

    public function clearId()
    {
        $this->data[$this->idField] = 0;
    }

    public function getErrorMessage()
    {
        return $this->db->getErrorMessage();
    }

    public function getErrorNumber()
    {
        return $this->db->getErrorNumber();
    }
}
