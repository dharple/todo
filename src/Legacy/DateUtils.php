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
        $monthend = strtotime(date('Y-m-t 00:00:00', $then));
        return date($format, $monthend);
    }

    public function getMonthStart($date = 'now', $format = 'Y-m-d 00:00:00')
    {
        $then = strtotime($date);
        $monthstart = strtotime(date('Y-m-1 00:00:00', $then));
        return date($format, $monthstart);
    }

    public function getNow()
    {
        return $this->getDate();
    }

    public function getWeekEnd($date = 'now', $format = 'Y-m-d 23:59:59')
    {
        $then = strtotime($date);
        $dow = date('w', $then);
        if ($dow < 6) {
            $weekend = strtotime('saturday', $then);
        } else {
            $weekend = $then;
        }
        return date($format, $weekend);
    }

    public function getWeekStart($date = 'now', $format = 'Y-m-d 00:00:00')
    {
        $then = strtotime($date);
        $dow = date('w', $then);
        if ($dow > 0) {
            $weekstart = strtotime('last sunday', $then);
        } else {
            $weekstart = $then;
        }
        return date($format, $weekstart);
    }
}
