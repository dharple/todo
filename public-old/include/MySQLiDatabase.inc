<?php

require_once('Database.inc');

class MySQLiDatabase implements Database
{

    public $conn;
    public $error;
    public $errno;

    public function connect($host, $user, $pass, $db)
    {
        $this->conn = mysqli_connect($host, $user, $pass);
        mysqli_select_db($this->conn, $db);
    }

    public function query($query)
    {
        $resultSet = mysqli_query($this->conn, $query);
        if (mysqli_error($this->conn)) {
            $this->error = mysqli_error($this->conn);
            $this->errno = mysqli_errno($this->conn);
            return false;
        } else {
            return $resultSet;
        }
    }

    public function fetchRow($resultSet)
    {
        $row = mysqli_fetch_row($resultSet);
        return $row;
    }

    public function fetchAssoc($resultSet)
    {
        $row = mysqli_fetch_assoc($resultSet);
        return $row;
    }

    public function lastInsertId()
    {
        return mysqli_insert_id($this->conn);
    }

    public function getErrorMessage()
    {
        return $this->error;
    }

    public function getErrorNumber()
    {
        return $this->errno;
    }
}
