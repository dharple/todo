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

class ItemDisplayPriorityEditor extends ItemDisplay
{

    public function drawItem($item)
    {
        $id = 'item-' . $item->getId();

        $output = '<li>';
        $output .= '<input class="list-item" id="' . htmlspecialchars($id) . '" type="text" size="3" align="right" name="itemPriority[' . $item->getId() . ']" value="' . $item->getPriority() . '">';
        $output .= '<label class="list-item-label" label-for="' . htmlspecialchars($id) . '">';
        $output .= '<span';
        if ($item->getStatus() != 'Open') {
            $output .= ' class="closed"';
        } elseif ($item->getPriority() <= 2) {
            $output .= ' class="high_priority"';
        }
        $output .= '>';
        $output .= htmlspecialchars($item->getTask());
        $output .= '</span>';
        $output .= '</li>';
        $output .= "\n";

        return $output;
    }
}
