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

use App\Helper;
use Exception;

class MySQLiDatabase implements Database
{

    protected $conn;

    protected $errno;

    protected $error;

    public function connect($host, $user, $pass, $db)
    {
        $this->conn = mysqli_connect($host, $user, $pass);
        if ($this->conn === false) {
            throw new Exception('Cannot connect to database');
        }
        mysqli_select_db($this->conn, $db);
    }

    public function fetchAssoc($resultSet)
    {
        return mysqli_fetch_assoc($resultSet);
    }

    public function fetchRow($resultSet)
    {
        return mysqli_fetch_row($resultSet);
    }

    public function getErrorMessage()
    {
        return $this->error;
    }

    public function getErrorNumber()
    {
        return $this->errno;
    }

    public function lastInsertId()
    {
        return mysqli_insert_id($this->conn);
    }

    public function query($query)
    {
        $level = preg_match('/^SELECT /i', $query) ? 'debug' : 'info';
        Helper::getLogger()->log($level, $query);

        $resultSet = mysqli_query($this->conn, $query);
        if (mysqli_error($this->conn)) {
            $this->error = mysqli_error($this->conn);
            $this->errno = mysqli_errno($this->conn);

            Helper::getLogger()->error(
                sprintf(
                    'error running query %s: %s [%d]',
                    $query,
                    $this->error,
                    $this->errno
                )
            );

            return false;
        } else {
            return $resultSet;
        }
    }
}
