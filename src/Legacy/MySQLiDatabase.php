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

use Exception;

class MySQLiDatabase implements Database
{

    public $conn;
    public $error;
    public $errno;

    public function connect($host, $user, $pass, $db)
    {
        $this->conn = mysqli_connect($host, $user, $pass);
        if ($this->conn === false) {
            throw new Exception('Cannot connect to database');
        }
        mysqli_select_db($this->conn, $db);
    }

    public function query($query)
    {
        if (!preg_match('/^SELECT /i', $query)) {
            file_put_contents(
                '/tmp/todo.log',
                sprintf(
                    "%s %s %s running query %s\n",
                    date('c'),
                    get_class($this),
                    'INFO',
                    $query
                ),
                FILE_APPEND
            );
        }
        $resultSet = mysqli_query($this->conn, $query);
        if (mysqli_error($this->conn)) {
            $this->error = mysqli_error($this->conn);
            $this->errno = mysqli_errno($this->conn);

            file_put_contents(
                '/tmp/todo.log',
                sprintf(
                    "%s %s %s error running query %s: %s [%d]\n",
                    date('c'),
                    get_class($this),
                    'ERROR',
                    $query,
                    $this->error,
                    $this->errno
                ),
                FILE_APPEND
            );

            return false;
        } else {
            return $resultSet;
        }
    }

    public function fetchRow($resultSet)
    {
        return mysqli_fetch_row($resultSet);
    }

    public function fetchAssoc($resultSet)
    {
        return mysqli_fetch_assoc($resultSet);
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
