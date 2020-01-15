<?php

namespace App\Legacy;

interface Database
{
    public function connect($host, $user, $pass, $db);
    public function query($query);
    public function fetchRow($resultSet);
    public function fetchAssoc($resultSet);
    public function getErrorMessage();
    public function getErrorNumber();
    public function lastInsertId();
}
