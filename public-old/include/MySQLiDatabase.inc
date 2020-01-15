<?php

require_once('Database.inc');

class MySQLiDatabase implements Database
{

    var $conn;
    var $error;
    var $errno;

    function connect($host, $user, $pass, $db)
    {
        $this->conn = mysqli_connect($host, $user, $pass);
        mysqli_select_db($this->conn, $db);
    }

    function query($query)
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

    function fetchRow($resultSet)
    {
        $row = mysqli_fetch_row($resultSet);
        return $row;
    }

    function fetchAssoc($resultSet)
    {
        $row = mysqli_fetch_assoc($resultSet);
        return $row;
    }

    function lastInsertId()
    {
        return mysqli_insert_id($this->conn);
    }

    function getErrorMessage()
    {
        return $this->error;
    }

    function getErrorNumber()
    {
        return $this->errno;
    }
}
