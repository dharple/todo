<?php

require_once('include/BaseDisplay.inc');
require_once('include/Item.inc');

class ItemDisplay extends BaseDisplay
{
    var $db;
    var $displayShowEstimate = 'n';
    var $displayCheckClosed = 'n';
    var $displayShowPriority = 'n';
    var $internalPriorityLevels = [];
    var $item;
    var $id;

    function ItemDisplay($db, $item)
    {
        $this->db = $db;

        if (is_object($item)) {
            $this->item = $item;
            $this->id = $item->getId();
        } else {
            $this->id = $item;
            unset($this->item);
        }
    }

    function setShowEstimate($displayShowEstimate)
    {
        $this->displayShowEstimate = $displayShowEstimate;
    }

    function setCheckClosed($displayCheckClosed)
    {
        $this->displayCheckClosed = $displayCheckClosed;
    }

    function setShowPriority($displayShowPriority)
    {
        $this->displayShowPriority = $displayShowPriority;
    }

    function setInternalPriorityLevels($internalPriorityLevels)
    {
        $this->internalPriorityLevels = $internalPriorityLevels;
    }

    function buildOutput()
    {
        if ($this->item) {
            $item = $this->item;
        } else {
            $item = new Item($this->db, $this->id);
        }

        $this->output = $this->drawItem($item);
        $this->outputBuilt = true;
    }

    function drawItem($item)
    {
        $output = '';
        $output .= '<tr>';
        $output .= '<td>';
        if ($this->displayShowPriority == 'y') {
            $output .= $item->getPriority();
        } elseif ($this->displayShowPriority == 'above_normal' && $item->getPriority() < $this->internalPriorityLevels['normal']) {
            $output .= $item->getPriority();
        } else {
            $output .= '&nbsp;';
        }
        $output .= '</td>';
        $output .= '<td>';
        $output .= '<input type=checkbox name=itemIds[] value=' . $item->getId();
        if ($this->displayCheckClosed == 'y' && $item->getStatus() != 'Open') {
            $output .= ' checked=true';
        }
        $output .= '>';
        $output .= '</td>';
        $output .= '<td>';
        $output .= '&nbsp;';
        $output .= '</td>';
        if ($this->displayShowEstimate == 'y') {
            $output .= '<td align=right class="estimate">';
            $output .= $item->getEstimate();
            $output .= '</td>';
            $output .= '<td>';
            $output .= '&nbsp;';
            $output .= '</td>';
        }
        $output .= '<td';
        if ($item->getStatus() != 'Open') {
            $output .= ' class="closed"';
        } elseif ($item->getPriority() <= 2) {
            $output .= ' class="high_priority"';
        }
        $output .= '>';
        $output .= htmlspecialchars($item->getTask());
        $output .= '</td>';
        $output .= '</tr>';
        $output .= "\n";

        return $output;
    }

    function getOutputCount()
    {
        return 1;
    }
}
