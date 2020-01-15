<?php

namespace App\Legacy;

class ListDisplay extends BaseDisplay
{
    public $db;
    public $columns = 2;
    public $columnFooters;
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
    public $internalPriorityLevels = [];

    public $userId;
    public $itemCount;

    public $splitThreshold = 10;
    public $splitMinimumChunk = 3;

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

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function setColumnFooter($column, $content)
    {
        $colspan = $this->getDisplayWidth();
        $fixed = "<tr><td colspan=$colspan>";
        $fixed .= $content;
        $fixed .= '</td></tr>';

        $this->columnFooters[$column] = $fixed;
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

        // Sanity Checking
        $this->columns = intval($this->columns);

        if ($this->columns < 1) {
            $this->columns = 1;
        }

        if ($this->columns > 10) {
            $this->columns = 10;
        }

        //

        $sectionList = new SimpleList($this->db, Section::class);

        $query = "WHERE user_id = '$this->userId'";

        if ($this->displayShowInactive != 'y') {
            $query .= " AND status = 'Active'";
        }

        if ($this->displayShowSection != 0) {
            $query .= " AND id = '" . $this->displayShowSection . "'";
        }

        $query .= ' ORDER BY name';

        $sections = $sectionList->load($query);

        $itemCount = 0;
        $realItemCount = 0;
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

            $itemCount += $sectionDisplay->getOutputLength();
            $realItemCount += $sectionDisplay->getOutputCount();
            $grandEstimate += $sectionDisplay->getOutputEstimate();

            array_push($sectionRenderers, $sectionDisplay);
        }

        if ($itemCount == 0) {
            $this->output = '<b>No Items</b><br>';
            $this->outputBuilt = true;
            $this->itemCount = 0;
            return;
        }

        //
        // Split Columns
        //

        // Find Total Length

        $outputSectionRenderers = [];
        for ($i = 0; $i < $this->columns; $i++) {
            $outputSectionRenderers[$i] = [];
        }

        $totalLength = 0;
        $sectionCounts = [];
        foreach ($sectionRenderers as $id => $sectionDisplay) {
            $sectionDisplay->buildOutput();

            $sectionCounts[$id] = $sectionDisplay->getOutputLength();

            $totalLength += $sectionDisplay->getOutputLength();
        }

        $desiredLength = $totalLength / $this->columns;

        $buildLength = 0;

        $columnCount = 0;
        foreach ($sectionCounts as $id => $sectionLength) {
            $sectionDisplay = $sectionRenderers[$id];

            if ($columnCount == $this->columns - 1) {
                    array_push($outputSectionRenderers[$columnCount], $sectionDisplay);
                    continue;
            }

            $realSectionLength = $sectionDisplay->getOutputCount();

            $workBuildLength = $buildLength + $sectionLength;
            if ($workBuildLength > $desiredLength) {
                if ($realSectionLength > $this->splitThreshold && (abs($desiredLength - $buildLength) > $this->splitMinimumChunk)) {
                    // Splittable

                    $splitPoint = ($desiredLength - $buildLength);


                    if ($splitPoint < $this->splitMinimumChunk) {
                        $splitPoint = $this->splitMinimumChunk;
                    } elseif ($realSectionLength - $splitPoint < $this->splitMinimumChunk) {
                        $splitPoint = $realSectionLength - $this->splitMinimumChunk;
                    }

                    if (substr(phpversion(), 0, 1) == '5') {
                        $newSectionDisplay = clone $sectionDisplay;
                    } else {
                        $newSectionDisplay = $sectionDisplay;
                    }

                    $newSectionDisplay->setShowSplit('first');
                    $newSectionDisplay->setSplitPoint($splitPoint);

                    $newSectionDisplay->buildOutput();

                    array_push($outputSectionRenderers[$columnCount], $newSectionDisplay);

                    $columnCount++;
                    $buildLength = 0;

                    $sectionDisplay->setShowSplit('last');
                    $sectionDisplay->setSplitPoint($splitPoint);

                    $sectionDisplay->buildOutput();
                } elseif ($id > 0) {
                    // Not Splittable

                    $columnCount++;
                    $buildLength = 0;
                }
            }

            array_push($outputSectionRenderers[$columnCount], $sectionDisplay);

            // Use The Method getOutputLength Instead of $sectionLength //
            $buildLength += $sectionDisplay->getOutputLength();
        }

        $ret = '<table width=100% cellspacing=5>';
        $ret .= '<tr valign=top>';

        $td_open = '<td width="' . intval(100 / $this->columns) . "%\"><table>\n";
        $td_close = "</table></td>\n";

        $columnRealItemCount = 0;

        foreach ($outputSectionRenderers as $columnCount => $list) {
            $ret .= $td_open;
            $columnRealItemCount = 0;

            if (count($list) == 0) {
                $ret .= '&nbsp;';
            }

            foreach ($list as $sectionDisplay) {
                $ret .= $sectionDisplay->getOutput();

                $columnRealItemCount += $sectionDisplay->getOutputCount();
            }

            $ret .= $this->replaceTotals($this->columnFooters[$columnCount], $realItemCount, $columnRealItemCount);

            if ($this->displayShowEstimate == 'y') {
                if ($columnCount == $this->columns - 1 || count($outputSectionRenderers[$columnCount]) == 0) {
                    $ret .= $this->drawEstimate($grandEstimate, 'Grand Total');
                }
            }

            $ret .= $td_close;
        }

        $ret .= "</tr>\n";
        $ret .= "</table>\n";

        $this->itemCount = $realItemCount;
        $this->output = $ret;
        $this->outputBuilt = true;
    }

    public function getOutputCount()
    {
        return $this->itemCount;
    }

    /**
     * Replaces {GRAND_TOTAL} and {COLUMN_TOTAL} with the appropriate values.
     */
    public function replaceTotals($string, $grand_total, $column_total)
    {
        $string = str_replace('{GRAND_TOTAL}', $grand_total, $string);
        $string = str_replace('{COLUMN_TOTAL}', $column_total, $string);

        $total = 0;

        $query = "SELECT COUNT(*) FROM item WHERE user_id = '$this->userId' AND status = 'Open'";
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