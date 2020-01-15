<?php

namespace App\Legacy;

class BaseDisplay
{
    public $output = '';
    public $outputBuilt = false;

    public function buildOutput()
    {
        $this->output = '';
        $this->outputBuilt = true;
    }

    public function getOutput()
    {
        if (!$this->outputBuilt) {
            $this->buildOutput();
        }

        return $this->output;
    }

    public function drawEstimate($estimate, $text = 'Total')
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
