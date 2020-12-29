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

interface Database
{
    public function connect($host, $user, $pass, $db);

    public function fetchAssoc($resultSet);

    public function fetchRow($resultSet);

    public function getErrorMessage();

    public function getErrorNumber();

    public function lastInsertId();

    public function query($query);
}
