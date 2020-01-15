<?php

require_once('include/BaseExport.inc');
require_once('include/SimpleList.inc');
require_once('include/Section.inc');
require_once('include/Item.inc');
require_once('include/DateUtils.inc');


class ICSExport extends BaseExport
{
    var $db;

    var $userId;

    var $method = 'REQUEST';
    var $lineTerminators = "\r\n";

    function ICSExport($db, $userId)
    {
        $this->db = $db;
        $this->userId = $userId;
    }

    function buildHeaders()
    {
        $this->headers = [];

        array_push($this->headers, 'Content-Type: text/calendar; name=calendar.ics; charset=utf-8; METHOD=' . $this->method);
        array_push($this->headers, 'Content-Disposition: attachment; filename="calendar-' . date('Ymd') . '.ics"');
        array_push($this->headers, 'Content-Description: Task information');
        array_push($this->headers, 'Content-Transfer-Encoding: 7bit');
    }

    function buildOutput()
    {

        $ret = 'BEGIN:VCALENDAR' . $this->lineTerminators;
        $ret .= 'VERSION:2.0' . $this->lineTerminators;
        $ret .= 'PRODID:-//Outsanity//NONSGML Simple Todo//EN' . $this->lineTerminators;
        $ret .= 'METHOD:' . $this->method . $this->lineTerminators;

        $user_id = $_SESSION['user_id'];

        $sectionList = new SimpleList($this->db, 'Section');

        $query = "WHERE user_id = '$user_id'";

        if ($display_show_inactive != 'y') {
            $query .= " AND status = 'Active'";
        }

        $query .= ' ORDER BY name';

        $sections = $sectionList->load($query);

        $itemList = new SimpleList($this->db, 'Item');

        $itemCount = 0;

        $dateUtils = new DateUtils();

        foreach ($sections as $section) {
            $query = 'WHERE section_id = ' . $section->getId();

            if ($this->displayFilterClosed == 'all') {
                $query .= " AND status <> 'Deleted'";
            } elseif ($this->displayFilterClosed == 'today') {
                $query .= " AND (status = 'Open' OR (completed >= '" . $dateUtils->getDate('now', 'Y-m-d 00:00:00') . "' AND completed <= '" . $dateUtils->getDate('now', 'Y-m-d 23:59:59') . "' AND status = 'Closed'))";
            } elseif ($this->displayFilterClosed == 'recently') {
                $query .= " AND (status = 'Open' OR (completed >= '" . $dateUtils->getDate('-3 days', 'Y-m-d 00:00:00') . "' AND completed <= '" . $dateUtils->getDate('now', 'Y-m-d 23:59:59') . "' AND status = 'Closed'))";
            } else {
                $query .= " AND status = 'Open'";
            }
            
            if ($this->displayFilterPriority == 'high') {
                $query .= ' AND priority <= 2';
            } elseif ($this->displayFilterPriority == 'normal') {
                $query .= ' AND priority BETWEEN 2 AND 4';
            } elseif ($this->displayFilterPriority == 'low') {
                $query .= ' AND priority >= 4';
            }

            if ($this->displayFilterAging != 'all') {
                $query .= " AND (TO_DAYS('" . $dateUtils->getNow() . "') - TO_DAYS(created)) >= '" . $this->displayFilterAging . "'";
            }

            $query .= ' ORDER BY priority, task';

            $items = $itemList->load($query);

            if (count($items) == 0) {
                continue;
            }

            foreach ($items as $item) {
                $created = strtotime($item->getCreated());
                $createdStr = gmdate('Ymd', $created) . 'T' . gmdate('His', $created) . 'Z';

                $ret .= 'BEGIN:VTODO' . $this->lineTerminators;
                $ret .= 'UID:' . $createdStr . '-' . $item->getId() . '@' . $_SERVER['SERVER_NAME'] . $this->lineTerminators;
                $ret .= 'DTSTAMP:' . $createdStr . $this->lineTerminators;
                $ret .= 'SUMMARY:' . $item->getTask() . $this->lineTerminators;
                $ret .= 'CATEGORIES:' . $section->getName() . $this->lineTerminators;
                if ($item->getStatus() == 'Closed') {
                    $ret .= 'STATUS:COMPLETED' . $this->lineTerminators;
                }
                $ret .= 'END:VTODO' . $this->lineTerminators;
            }
        }

        $ret .= 'END:VCALENDAR' . $this->lineTerminators;

        $this->output = $ret;
        $this->outputBuilt = true;
    }
}
