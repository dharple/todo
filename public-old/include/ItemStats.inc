<?php

require_once('ItemHistory.inc');
require_once('DateUtils.inc');

class ItemStats extends ItemHistory
{

    function execute($start = '', $end = '')
    {
        if ($start != '') {
            $whereClause = " AND completed >= '$start' AND completed <= '$end'";
        } else {
            $whereClause = '';
        }

        return $this->itemList->count("WHERE user_id = '$this->user_id' AND status = 'Closed'" . $whereClause);
    }
}
