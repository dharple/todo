<?php

require_once('ItemDisplay.inc');

class ItemDisplayPriorityEditor extends ItemDisplay
{

    function drawItem($item)
    {
        $output = '';
        $output .= '<tr>';
        $output .= '<td>';
        $output .= '&nbsp;';
        $output .= '</td>';
        $output .= '<td>';
        $output .= '<input type=text size=3 align=right name="itemPriority[' . $item->getId() . ']" value=' . $item->getPriority() . '>';
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
