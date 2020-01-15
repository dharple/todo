<?php

namespace App\Legacy;

class SectionDisplay extends BaseDisplay
{
    public $db;
    public $displayIds;
    public $displayFilterClosed = 'none';
    public $displayFilterPriority = 'all';
    public $displayFilterAging = 'all';
    public $displayShowEstimate = 'n';
    public $displayShowEstimateEditor = 'n';
    public $displayShowSplit = 'all';
    public $displayCheckClosed = 'n';
    public $displayShowSection = 0;
    public $displaySectionLink = '';
    public $displayShowPriority = 'n';
    public $displayShowPriorityEditor = 'n';
    public $internalPriorityLevels = [];
    public $splitPoint = 0;

    public $id;
    public $section;

    public $itemCount = 0;
    public $padding = 0;
    public $estimate = 0;

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

    public function setShowSplit($displayShowSplit)
    {
        $this->displayShowSplit = $displayShowSplit;
    }

    public function setSplitPoint($splitPoint)
    {
        $this->splitPoint = $splitPoint;
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
        if ($this->section) {
            $section = $this->section;
        } else {
            $section = new Section($this->db, $this->id);
        }

        $itemList = new SimpleList($this->db, Item::class);

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
                $itemDisplay = new ItemDisplayEstimateEditor($this->db, $item);
            } elseif ($this->displayShowPriorityEditor == 'y') {
                $itemDisplay = new ItemDisplayPriorityEditor($this->db, $item);
            } else {
                $itemDisplay = new ItemDisplay($this->db, $item);
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

    public function getOutputCount()
    {
        return $this->itemCount;
    }

    public function getOutputLength()
    {
        return $this->itemCount + $this->padding;
    }

    public function getOutputEstimate()
    {
        return $this->estimate;
    }

    public function getId()
    {
        return $this->id;
    }
}