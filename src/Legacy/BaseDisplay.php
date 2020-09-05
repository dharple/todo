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

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class BaseDisplay
{
    protected $output = '';
    protected $outputBuilt = false;

    /**
     * Holds the twig renderer.
     *
     * @var Environment
     */
    protected $twig;

    protected function buildOutput()
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

    protected function getTwig()
    {
        if (!isset($this->twig)) {
            $loader = new FilesystemLoader(dirname(dirname(dirname(__FILE__))) . '/templates');
            $this->twig = new Environment($loader);
        }

        return $this->twig;
    }

    protected function render($template, $variables = [])
    {
        return $this->getTwig()->render($template, $variables);
    }
}
