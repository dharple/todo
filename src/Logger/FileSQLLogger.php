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

class FileSQLLogger implements SQLLogger
{

    /**
     * @inheritDoc
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $level = preg_match('/^SELECT /i', $sql) ? 'debug' : 'info';

        Helper::getLogger()->log($level, $sql);
        if (isset($params)) {
            Helper::getLogger()->log($level, json_encode($params));
        }
    }

    /**
     * @inheritDoc
     */
    public function stopQuery()
    {
    }
}
