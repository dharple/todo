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

use App\Legacy\Entity\Section;

class ListDisplay extends BaseDisplay
{
    public $db;
    public $displayIds;
    public $displayFilterClosed = 'none';
    public $displayFilterPriority = 'all';
    public $displayFilterAging = 'all';
    public $displayShowEstimate = 'n';
    public $displayShowEstimateEditor = 'n';
    public $displayShowInactive = 'n';
    public $displayCheckClosed = 'n';
    public $displayShowSection = 0;
    public $displaySectionLink = '';
    public $displayShowPriority = 'n';
    public $displayShowPriorityEditor = 'n';
    public $footer;
    public $internalPriorityLevels = [];

    public $userId;
    public $itemCount;

    public function __construct($db, $userId)
    {
        $this->db = $db;
        $this->userId = $userId;
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

    public function setShowInactive($displayShowInactive)
    {
        $this->displayShowInactive = $displayShowInactive;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    public function getDisplayWidth()
    {
        $width = 4;
        if ($this->displayShowEstimate) {
            $width += 2;
        }
        return $width;
    }

    public function setIds($ids)
    {
        $this->displayIds = $ids;
    }

    public function setShowEstimate($displayShowEstimate)
    {
        $this->displayShowEstimate = $displayShowEstimate;
    }

    public function setShowEstimateEditor($displayShowEstimateEditor)
    {
        $this->displayShowEstimateEditor = $displayShowEstimateEditor;
    }

    public function setCheckClosed($displayCheckClosed)
    {
        $this->displayCheckClosed = $displayCheckClosed;
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

    public function buildOutput()
    {

        $sectionList = new SimpleList($this->db, Section::class);

        $query = "WHERE user_id = '" . addslashes($this->userId) . "'";

        if ($this->displayShowInactive != 'y') {
            $query .= " AND status = 'Active'";
        }

        if ($this->displayShowSection != 0) {
            $query .= " AND id = '" . $this->displayShowSection . "'";
        }

        $query .= ' ORDER BY name';

        $sections = $sectionList->load($query);

        $itemCount = 0;
        $grandEstimate = 0;

        $sectionRenderers = [];

        foreach ($sections as $section) {
            $sectionDisplay = new SectionDisplay($this->db, $section);

            $sectionDisplay->setIds($this->displayIds);
            $sectionDisplay->setFilterClosed($this->displayFilterClosed);
            $sectionDisplay->setFilterPriority($this->displayFilterPriority);
            $sectionDisplay->setFilterAging($this->displayFilterAging);
            $sectionDisplay->setShowEstimate($this->displayShowEstimate);
            $sectionDisplay->setShowEstimateEditor($this->displayShowEstimateEditor);
            $sectionDisplay->setCheckClosed($this->displayCheckClosed);
            $sectionDisplay->setShowSection($this->displayShowSection);
            $sectionDisplay->setSectionLink($this->displaySectionLink);
            $sectionDisplay->setShowPriority($this->displayShowPriority);
            $sectionDisplay->setShowPriorityEditor($this->displayShowPriorityEditor);
            $sectionDisplay->setInternalPriorityLevels($this->internalPriorityLevels);

            $sectionDisplay->buildOutput();

            if ($sectionDisplay->getOutputCount() == 0) {
                continue;
            }

            $itemCount += $sectionDisplay->getOutputCount();
            $grandEstimate += $sectionDisplay->getOutputEstimate();

            array_push($sectionRenderers, $sectionDisplay);
        }

        if ($itemCount == 0) {
            $this->output = '<b>No Items</b><br>';
            $this->outputBuilt = true;
            $this->itemCount = 0;
            return;
        }

        $class = (count($sectionRenderers) > 1) ? 'wrapper-large' : 'wrapper-small';

        $ret = '<div class="wrapper ' . htmlspecialchars($class) . '">';

        foreach ($sectionRenderers as $sectionDisplay) {
            $ret .= $sectionDisplay->getOutput();
        }

        $ret .= '<div class="section">';
        if ($this->displayShowEstimate == 'y') {
            $ret .= $this->drawEstimate($grandEstimate, 'Grand Total');
        }

        $ret .= $this->replaceTotals($this->footer ?? '', $itemCount);
        $ret .= '</div>';

        $ret .= '</div>';

        $this->itemCount = $itemCount;
        $this->output = $ret;
        $this->outputBuilt = true;
    }

    public function getOutputCount()
    {
        return $this->itemCount;
    }

    /**
     * Replaces {GRAND_TOTAL} and {NOT_SHOWN} with the appropriate values.
     */
    public function replaceTotals($string, $grand_total)
    {
        $string = str_replace('{GRAND_TOTAL}', $grand_total, $string);

        $total = 0;

        $query = "SELECT COUNT(*) FROM item WHERE user_id = '" . addslashes($this->userId) . "' AND status = 'Open'";
        $result = $this->db->query($query);
        if ($result) {
            $row = $this->db->fetchRow($result);
            $total = $row[0];
        } else {
            $total = $grand_total;
        }

        $string = str_replace('{NOT_SHOWN}', $total - $grand_total, $string);

        return $string;
    }
}
