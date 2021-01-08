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

use App\Renderer\DisplayConfig;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Error\Error as TwigError;

abstract class BaseDisplay
{
    /**
     * The DisplayConfig to use.
     *
     * @var DisplayConfig
     */
    protected DisplayConfig $config;

    /**
     * The Entity Manager to use.
     *
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * The rendered item count.
     *
     * @var int
     */
    protected int $itemCount = 0;

    /**
     * The logger to use.
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $log;

    /**
     * The actual output.
     *
     * @var string
     */
    protected string $output = '';

    /**
     * Whether or not the output has been built.
     *
     * @var bool
     */
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
                $this->log->error(sprintf('caught exception while building output: %s', $e->getMessage()));
            }
        }

        return $this->output;
    }

    /**
     * The output count.
     *
     * @return int
     */
    public function getOutputCount(): int
    {
        return $this->itemCount;
    }

    /**
     * Renders a twig template and returns the result.
     *
     * @param string $template  The template to render.
     * @param array  $variables The variables to pass to the template.
     *
     * @return string
     *
     * @throws TwigError
     */
    protected function render(string $template, array $variables = []): string
    {
        return $this->twig->render($template, $variables);
    }
}
