<?php

class BaseDisplay
{
    var $output = '';
    var $outputBuilt = false;

    function buildOutput()
    {
        $this->output = '';
        $this->outputBuilt = true;
    }

    function getOutput()
    {
        if (!$this->outputBuilt) {
            $this->buildOutput();
        }

        return $this->output;
    }

    function drawEstimate($estimate, $text = 'Total')
    {
        $output = '';
        $output .= '<tr>';
        $output .= '<td colspan=3>';
        $output .= '&nbsp;';
        $output .= '</td>';
        $output .= '<td align=right class="estimate_total">';
        $output .= number_format($estimate, 1);
        $output .= '</td>';
        $output .= '<td>';
        $output .= '&nbsp;';
        $output .= '</td>';
        $output .= '<td class="estimate_total">';
        $output .= $text;
        $output .= '</td>';
        $output .= '</tr>';
        $output .= "\n";

        return $output;
    }
}
