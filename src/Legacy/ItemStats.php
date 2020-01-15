<?php

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
