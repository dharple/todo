<?php

namespace App\Legacy;

class ItemStats extends ItemHistory
{

    public function execute($start = '', $end = '')
    {
        if ($start != '') {
            $whereClause = " AND completed >= '$start' AND completed <= '$end'";
        } else {
            $whereClause = '';
        }

        return $this->itemList->count("WHERE user_id = '$this->user_id' AND status = 'Closed'" . $whereClause);
    }
}