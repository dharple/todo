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

class ItemStats extends ItemHistory
{

    public function execute($start = '', $end = '')
    {
        if ($start != '') {
            $whereClause = " AND completed >= '" . addslashes($start) . "' AND completed <= '" . addslashes($end) . "'";
        } else {
            $whereClause = '';
        }

        return $this->itemList->count("WHERE user_id = '" . addslashes($this->user_id) . "' AND status = 'Closed'" . $whereClause);
    }
}
