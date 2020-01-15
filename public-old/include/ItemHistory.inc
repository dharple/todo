<?php

require_once('DateUtils.inc');

class ItemHistory
{

    public $userId;
    public $itemList;
    public $dateUtils;
    public $ordering;

    public function __construct($db, $user_id)
    {
        $this->user_id = $user_id;
        $this->itemList = new SimpleList($db, 'Item');
        $this->dateUtils = new DateUtils();

        $this->ordering = 'task';
    }

    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    public function execute($start = '', $end = '')
    {
        if ($start != '') {
            $whereClause = " AND item.completed >= '$start' AND item.completed <= '$end'";
        } else {
            $whereClause = '';
        }

        $baseQuery = "WHERE item.user_id = '" . $this->user_id . "' AND item.status = 'Closed'" . $whereClause;
        if ($this->ordering == 'section') {
            $query = "LEFT JOIN section ON item.section_id = section.id ${baseQuery} ORDER BY TO_DAYS(item.completed) DESC, section.name, item.task";
        } else {
            $query = "${baseQuery} ORDER BY TO_DAYS(completed) DESC, task";
        }

        return $this->itemList->load($query);
    }

    public function doneToday()
    {
        $start = $this->dateUtils->getDate('now', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getDate('now', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function doneYesterday()
    {
        $start = $this->dateUtils->getDate('yesterday', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getDate('yesterday', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function doneThisWeek()
    {
        $start = $this->dateUtils->getWeekStart('now', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getWeekEnd('now', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function doneLastWeek()
    {
        $start = $this->dateUtils->getWeekStart('-1 week', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getWeekEnd('-1 week', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function doneThisMonth()
    {
        $work = date('Y-m-15');
        $start = $this->dateUtils->getMonthStart($work, 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getMonthEnd($work, 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function doneLastMonth()
    {
        $work = date('Y-m-15') . ' -1 month';
        $start = $this->dateUtils->getMonthStart($work, 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getMonthEnd($work, 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function donePreviousMonths($distance)
    {
        $work = ' -' . intval($distance) . ' month';
        $start = $this->dateUtils->getDate($work, 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getDate('now', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    public function doneTotal()
    {
        return $this->execute();
    }
}
