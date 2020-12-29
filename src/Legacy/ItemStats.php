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

    /**
     * Gets the current average
     *
     * @return mixed
     */
    public function getAverage()
    {
        // Ugly
        $query = "UPDATE item SET created = completed WHERE user_id = '" . addslashes($this->user_id) . "' AND status = 'Closed' AND (TO_DAYS(completed) - TO_DAYS(created)) < 0";
        $this->db->query($query);

        $query = "SELECT AVG(IFNULL(TO_DAYS(item.completed) - TO_DAYS(item.created) + 1, TO_DAYS(NOW()) - TO_DAYS(item.created) + 1)) FROM item LEFT JOIN section ON item.section_id = section.id WHERE item.user_id = '" . addslashes($this->user_id) . "' AND (item.status = 'closed' OR (item.status = 'open' AND section.status = 'active'))";
        $result = $this->db->query($query);
        $row = $this->db->fetchRow($result);
        return $row[0];
    }
}
