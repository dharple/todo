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

class MySQLiDatabase implements Database
{

    public $conn;
    public $error;
    public $errno;

    public function connect($host, $user, $pass, $db)
    {
        $this->conn = mysqli_connect($host, $user, $pass);
        if ($this->conn === false) {
            throw new \Exception('Cannot connect to database');
        }
        mysqli_select_db($this->conn, $db);
    }

    public function query($query)
    {
        file_put_contents(
            '/tmp/todo.log',
            sprintf("INFO [%s]: running query %s\n", get_class($this), $query),
            FILE_APPEND
        );
        $resultSet = mysqli_query($this->conn, $query);
        if (mysqli_error($this->conn)) {
            $this->error = mysqli_error($this->conn);
            $this->errno = mysqli_errno($this->conn);

            file_put_contents(
                '/tmp/todo.log',
                sprintf("ERROR [%s]: error running query %s: %s [%d]\n", get_class($this), $query, $this->error, $this->errno),
                FILE_APPEND
            );

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
