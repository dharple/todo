<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Logger;

use App\Helper;
use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Logs SQL queries to files.
 */
class FileSQLLogger implements SQLLogger
{

    /**
     * Logs a SQL statement to a file.
     *
     * @param string     $sql    SQL statement.
     * @param array|null $params Statement parameters.
     * @param array|null $types  Parameter types.
     *
     * @return void
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $level = preg_match('/^SELECT /i', $sql) ? 'debug' : 'info';

        Helper::getLogger()->log($level, $sql);
        if (isset($params)) {
            Helper::getLogger()->log($level, json_encode($params));
        }
    }

    /**
     * Doesn't do anything; needed for interface.
     *
     * @return void
     */
    public function stopQuery()
    {
    }
}
