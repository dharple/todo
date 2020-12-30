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

class DateUtils
{

    public function getDate($date = 'now', $format = 'Y-m-d H:i:s')
    {
        $then = strtotime($date);
        return date($format, $then);
    }

    public function getMonthEnd($date = 'now', $format = 'Y-m-d 23:59:59')
    {
        $then = strtotime($date);
        $monthEnd = strtotime(date('Y-m-t 00:00:00', $then));
        return date($format, $monthEnd);
    }

    public function getMonthStart($date = 'now', $format = 'Y-m-d 00:00:00')
    {
        $then = strtotime($date);
        $monthStart = strtotime(date('Y-m-1 00:00:00', $then));
        return date($format, $monthStart);
    }

    public function getWeekEnd($date = 'now', $format = 'Y-m-d 23:59:59')
    {
        $then = strtotime($date);
        $dow = date('w', $then);
        if ($dow < 6) {
            $weekEnd = strtotime('saturday', $then);
        } else {
            $weekEnd = $then;
        }
        return date($format, $weekEnd);
    }

    public function getWeekStart($date = 'now', $format = 'Y-m-d 00:00:00')
    {
        $then = strtotime($date);
        $dow = date('w', $then);
        if ($dow > 0) {
            $weekStart = strtotime('last sunday', $then);
        } else {
            $weekStart = $then;
        }
        return date($format, $weekStart);
    }
}
