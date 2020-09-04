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
        $output = '<span class="estimate-total">';
        $output .= number_format($estimate, 1);
        $output .= '&nbsp;';
        $output .= $text;
        $output .= '</span>';
        $output .= "\n";

        return $output;
    }
}
