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

class ItemDisplay extends BaseDisplay
{
    public $db;
    public $displayShowEstimate = 'n';
    public $displayCheckClosed = 'n';
    public $displayShowPriority = 'n';
    public $internalPriorityLevels = [];
    public $item;
    public $id;

    public function __construct($db, $item)
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

    public function setShowEstimate($displayShowEstimate)
    {
        $this->displayShowEstimate = $displayShowEstimate;
    }

    public function setCheckClosed($displayCheckClosed)
    {
        $this->displayCheckClosed = $displayCheckClosed;
    }

    public function setShowPriority($displayShowPriority)
    {
        $this->displayShowPriority = $displayShowPriority;
    }

    public function setInternalPriorityLevels($internalPriorityLevels)
    {
        $this->internalPriorityLevels = $internalPriorityLevels;
    }

    public function buildOutput()
    {
        if ($this->item) {
            $item = $this->item;
        } else {
            $item = new Item($this->db, $this->id);
        }

        $this->output = $this->drawItem($item);
        $this->outputBuilt = true;
    }

    public function drawItem($item)
    {
        $output = '<li>';

        if ($this->displayShowPriority == 'y') {
            $output .= $item->getPriority();
        } elseif ($this->displayShowPriority == 'above_normal' && $item->getPriority() < $this->internalPriorityLevels['normal']) {
            $output .= $item->getPriority();
        } else {
            $output .= '&nbsp;';
        }

        $id = 'item-' . $item->getId();

        $output .= '<input class="list-item" id="' . htmlspecialchars($id) . '" type="checkbox" name="itemIds[]" value="' . $item->getId() . '"';
        if ($this->displayCheckClosed == 'y' && $item->getStatus() != 'Open') {
            $output .= ' checked=true';
        }
        $output .= '>';

        $output .= '<label class="list-item-label" label-for="' . htmlspecialchars($id) . '">';

        if ($this->displayShowEstimate == 'y') {
            $output .= '<span class="estimate">';
            $output .= $item->getEstimate();
            $output .= '</span>';
        }
        $output .= '<span';
        if ($item->getStatus() != 'Open') {
            $output .= ' class="closed"';
        } elseif ($item->getPriority() <= 2) {
            $output .= ' class="high_priority"';
        }
        $output .= '>';
        $output .= htmlspecialchars($item->getTask());
        $output .= '</span>';

        $output .= '</label>';

        $output .= '</li>';
        $output .= "\n";

        return $output;
    }

    public function getOutputCount()
    {
        return 1;
    }
}
