<?php

require_once('include/BaseDisplay.inc');
require_once('include/SimpleList.inc');
require_once('include/Item.inc');
require_once('include/Section.inc');
require_once('include/ItemDisplay.inc');
require_once('include/ItemDisplayEstimateEditor.inc');
require_once('include/ItemDisplayPriorityEditor.inc');
require_once('include/DateUtils.inc');

class SectionDisplay extends BaseDisplay
{
    var $db;
    var $displayIds;
    var $displayFilterClosed = 'none';
    var $displayFilterPriority = 'all';
    var $displayFilterAging = 'all';
    var $displayShowEstimate = 'n';
    var $displayShowEstimateEditor = 'n';
    var $displayShowSplit = 'all';
    var $displayCheckClosed = 'n';
    var $displayShowSection = 0;
    var $displaySectionLink = '';
    var $displayShowPriority = 'n';
    var $displayShowPriorityEditor = 'n';
    var $internalPriorityLevels = [];
    var $splitPoint = 0;

    var $id;
    var $section;

    var $itemCount = 0;
    var $padding = 0;
    var $estimate = 0;

    function SectionDisplay($db, $section)
    {
        $this->db = $db;
        $this->user_id = $user_id;

        if (is_object($section)) {
            $this->section = $section;
            $this->id = $section->getId();
        } else {
            $this->id = $section;
            unset($this->section);
        }
    }

    function setFilterClosed($displayFilterClosed)
    {
        $this->displayFilterClosed = $displayFilterClosed;
    }

    function setFilterPriority($displayFilterPriority)
    {
        $this->displayFilterPriority = $displayFilterPriority;
    }

    function setFilterAging($displayFilterAging)
    {
        $this->displayFilterAging = $displayFilterAging;
    }

    function getDisplayWidth()
    {
        $width = 4;
        if ($this->displayShowEstimate) {
            $width += 2;
        }
        return $width;
    }

    function setIds($ids)
    {
        $this->displayIds = $ids;
    }

    function setShowEstimate($displayShowEstimate)
    {
        $this->displayShowEstimate = $displayShowEstimate;
    }

    function setShowEstimateEditor($displayShowEstimateEditor)
    {
        $this->displayShowEstimateEditor = $displayShowEstimateEditor;
    }

    function setShowSplit($displayShowSplit)
    {
        $this->displayShowSplit = $displayShowSplit;
    }

    function setSplitPoint($splitPoint)
    {
        $this->splitPoint = $splitPoint;
    }

    function setCheckClosed($displayCheckClosed)
    {
        $this->displayCheckClosed = $displayCheckClosed;
    }

    function setShowSection($displayShowSection)
    {
        $this->displayShowSection = $displayShowSection;
    }

    function setSectionLink($displaySectionLink)
    {
        $this->displaySectionLink = $displaySectionLink;
    }

    function setShowPriority($displayShowPriority)
    {
        $this->displayShowPriority = $displayShowPriority;
    }

    function setShowPriorityEditor($displayShowPriorityEditor)
    {
        $this->displayShowPriorityEditor = $displayShowPriorityEditor;
    }

    function setInternalPriorityLevels($internalPriorityLevels)
    {
        $this->internalPriorityLevels = $internalPriorityLevels;
    }

    function buildOutput()
    {
        if ($this->section) {
            $section = $this->section;
        } else {
            $section = new Section($this->db, $this->id);
        }

        $itemList = new SimpleList($this->db, 'Item');

        $this->itemCount = 0;
        $this->padding = 0;
        $this->outputBuilt = true;

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

        if (count($items) == 0) {
            return;
        }

        if ($this->displayShowSplit == 'first' || $this->displayShowSplit == 'last') {
            if ($this->splitPoint > 0 && $this->splitPoint < count($items)) {
                $half = $this->splitPoint;
            } else {
                $half = (int) (count($items) * 2.0 / 3.0);
            }
        }

        $output = '';

        $colspan = $this->getDisplayWidth();
        $output .= "<tr><td colspan=$colspan class=\"section\">";
        if ($this->displaySectionLink) {
            $output .= '<a class="section_link" href="' . str_replace('{SECTION_ID}', ($this->displayShowSection ? 0 : $section->getId()), $this->displaySectionLink) . '">';
        }
        $output .= $section->getName();
        if ($this->displaySectionLink) {
            $output .= '</a>';
        }
        if ($this->displayShowSplit == 'last') {
            $output .= " (con't)";
        } elseif ($section->getStatus() == 'Inactive') {
            $output .= ' (Inactive)';
        }
        $output .= '</td></tr>';

        $this->itemCount = 0;
        $this->padding = 2;
        if ($this->displayShowSplit != 'last') {
            $this->estimate = 0;
        }

        $count = 0;
        foreach ($items as $item) {
            $count++;

            if ($this->displayShowSplit == 'first') {
                if ($count > $half) {
                    continue;
                }
            } elseif ($this->displayShowSplit == 'last') {
                if ($count <= $half) {
                    continue;
                }
            }

            if ($this->displayShowEstimateEditor == 'y') {
                $itemDisplay = new ItemDisplayEstimateEditor($db, &$item);
            } elseif ($this->displayShowPriorityEditor == 'y') {
                $itemDisplay = new ItemDisplayPriorityEditor($db, &$item);
            } else {
                $itemDisplay = new ItemDisplay($db, &$item);
            }

            $itemDisplay->setShowEstimate($this->displayShowEstimate);
            $itemDisplay->setCheckClosed($this->displayCheckClosed);
            $itemDisplay->setShowPriority($this->displayShowPriority);
            $itemDisplay->setInternalPriorityLevels($this->internalPriorityLevels);

            $output .= $itemDisplay->getOutput();
            $this->itemCount += $itemDisplay->getOutputCount();

            $this->estimate += $item->getEstimate();
        }

        if ($this->displayShowEstimate == 'y' && $this->displayShowSplit != 'first') {
            $output .= $this->drawEstimate($this->estimate);

            $this->padding++;
        }

        $output .= "<tr><td colspan=$colspan>&nbsp;</td></tr>";

        $this->output = $output;
    }

    function getOutputCount()
    {
        return $this->itemCount;
    }

    function getOutputLength()
    {
        return $this->itemCount + $this->padding;
    }

    function getOutputEstimate()
    {
        return $this->estimate;
    }

    function getId()
    {
        return $this->id;
    }
}
