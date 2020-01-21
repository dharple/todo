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

class ItemDisplayEstimateEditor extends ItemDisplay
{

    public function drawItem($item)
    {
        $output = '';
        $output .= '<tr>';
        $output .= '<td>';
        $output .= '&nbsp;';
        $output .= '</td>';
        $output .= '<td>';
        $output .= '<input type=text size=5 align=right name="itemEstimate[' . $item->getId() . ']" value=' . $item->getEstimate() . '>';
        $output .= '</td>';
        $output .= '<td>';
        $output .= '&nbsp;';
        $output .= '</td>';
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
}
