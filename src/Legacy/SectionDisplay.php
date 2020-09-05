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

use App\Legacy\Entity\Item;
use App\Legacy\Entity\Section;

class SectionDisplay extends BaseDisplay
{
    public $db;
    public $displayIds;
    public $displayFilterClosed = 'none';
    public $displayFilterPriority = 'all';
    public $displayFilterAging = 'all';
    public $displayPrintable = false;
    public $displayShowSection = 0;
    public $displaySectionLink = '';
    public $displayShowPriority = 'n';
    public $displayShowPriorityEditor = 'n';
    public $internalPriorityLevels = [];

    public $id;
    public $section;

    public $itemCount = 0;

    public function __construct($db, $section)
    {
        $this->db = $db;

        if (is_object($section)) {
            $this->section = $section;
            $this->id = $section->getId();
        } else {
            $this->id = $section;
            unset($this->section);
        }
    }

    public function setFilterClosed($displayFilterClosed)
    {
        $this->displayFilterClosed = $displayFilterClosed;
    }

    public function setFilterPriority($displayFilterPriority)
    {
        $this->displayFilterPriority = $displayFilterPriority;
    }

    public function setFilterAging($displayFilterAging)
    {
        $this->displayFilterAging = $displayFilterAging;
    }

    public function getDisplayWidth()
    {
        $width = 4;
        return $width;
    }

    public function setIds($ids)
    {
        $this->displayIds = $ids;
    }

    public function setPrintable($displayPrintable)
    {
        $this->displayPrintable = $displayPrintable;
    }

    public function setShowSection($displayShowSection)
    {
        $this->displayShowSection = $displayShowSection;
    }

    public function setSectionLink($displaySectionLink)
    {
        $this->displaySectionLink = $displaySectionLink;
    }

    public function setShowPriority($displayShowPriority)
    {
        $this->displayShowPriority = $displayShowPriority;
    }

    public function setShowPriorityEditor($displayShowPriorityEditor)
    {
        $this->displayShowPriorityEditor = $displayShowPriorityEditor;
    }

    public function setInternalPriorityLevels($internalPriorityLevels)
    {
        $this->internalPriorityLevels = $internalPriorityLevels;
    }

    protected function buildOutput()
    {
        if ($this->section) {
            $section = $this->section;
        } else {
            $section = new Section($this->db, $this->id);
        }

        $itemList = new SimpleList($this->db, Item::class);

        $dateUtils = new DateUtils();

        $query = 'WHERE section_id = ' . $section->getId();

        if (is_array($this->displayIds)) {
            $query .= " AND id IN ('" . implode("','", $this->displayIds) . "')";
        }

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
            $query .= " AND priority = '" . intval($this->internalPriorityLevels['high']) . "'";
        } elseif ($this->displayFilterPriority == 'normal') {
            $query .= " AND priority BETWEEN '" . intval($this->internalPriorityLevels['high']) . "' AND '" . intval($this->internalPriorityLevels['normal']) . "'";
        } elseif ($this->displayFilterPriority == 'low') {
            $query .= " AND priority BETWEEN '" . intval($this->internalPriorityLevels['high']) . "' AND '" . intval($this->internalPriorityLevels['low']) . "'";
        }

        if ($this->displayFilterAging != 'all') {
            $query .= " AND (TO_DAYS('" . $dateUtils->getNow() . "') - TO_DAYS(created)) >= '" . $this->displayFilterAging . "'";
        }

        $query .= ' ORDER BY priority, task';

        $items = $itemList->load($query);

        $this->itemCount = count($items);

        if ($this->itemCount == 0) {
            $this->outputBuilt = true;
            return;
        }

        if ($this->displayPrintable) {
            $template = 'printable';
        } elseif ($this->displayShowPriorityEditor == 'y') {
            $template = 'priority_editor';
        } else {
            $template = 'main';
        }

        $this->output = $this->render(sprintf('partials/section/%s.html.twig', $template), [
            'items'              => $items,
            'priorityHigh'       => 2,
            'priorityNormal'     => $this->internalPriorityLevels['normal'],
            'section'            => $section,
            'sectionUrl'         => str_replace('{SECTION_ID}', ($this->displayShowSection ? 0 : $section->getId()), $this->displaySectionLink),
            'showPriority'       => $this->displayShowPriority,
            'showSectionLink'    => isset($this->displaySectionLink) ? 'y' : 'n',
        ]);

        $this->outputBuilt = true;
    }

    public function getOutputCount()
    {
        return $this->itemCount;
    }

    public function getId()
    {
        return $this->id;
    }
}
