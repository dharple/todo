<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Renderer;

use App\Helper;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class BaseDisplay
{
    protected string $output = '';

    protected bool $outputBuilt = false;

    /**
     * Holds the twig renderer.
     *
     * @var Environment
     */
    protected Environment $twig;

    /**
     * Builds the output for this display.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function buildOutput(): void
    {
        $this->output = '';
        $this->outputBuilt = true;
    }

    /**
     * Returns the generated output for this display.
     *
     * @return string
     */
    public function getOutput(): string
    {
        if (!$this->outputBuilt) {
            try {
                $this->buildOutput();
            } catch (Exception $e) {
                Helper::getLogger()->error(sprintf('caught exception while building output: %s', $e->getMessage()));
            }
        }

        return $this->output;
    }

    protected function getTwig(): Environment
    {
        if (!isset($this->twig)) {
            $this->twig = Helper::getTwig();
        }

        return $this->twig;
    }

    /**
     * Renders a twig template and returns the result.
     *
     * @param string $template  The template to render.
     * @param array  $variables The variables to pass to the template.
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function render(string $template, array $variables = []): string
    {
        return $this->getTwig()->render($template, $variables);
    }
}
