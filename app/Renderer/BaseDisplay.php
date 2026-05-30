<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Renderer;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Common display methods.
 */
abstract class BaseDisplay
{
    /**
     * The DisplayConfig to use.
     */
    protected DisplayConfig $config;

    /**
     * The rendered item count.
     */
    protected ?ItemCount $itemCount = null;

    /**
     * The actual output.
     */
    protected string $output = '';

    /**
     * Whether or not the output has been built.
     */
    protected bool $outputBuilt = false;

    /**
     * Builds the output for this display.
     *
     *
     * @throws Exception
     */
    protected function buildOutput(): void
    {
        $this->output      = '';
        $this->outputBuilt = true;
    }

    /**
     * Returns the generated output for this display.
     */
    public function getOutput(): string
    {
        if (!$this->outputBuilt) {
            try {
                $this->buildOutput();
            } catch (Exception $e) {
                Log::error(sprintf('caught exception while building output: %s', $e->getMessage()));
            }
        }

        return $this->output;
    }

    /**
     * Returns the output count.
     */
    public function getOutputCount(): ItemCount
    {
        if (!isset($this->itemCount)) {
            $this->itemCount = new ItemCount();
        }

        return $this->itemCount;
    }

    /**
     * Renders a Blade template and returns the result.
     *
     * @param string               $template  The template name in dot notation.
     * @param array<string, mixed> $variables The variables to pass to the template.
     */
    protected function render(string $template, array $variables = []): string
    {
        return view($template, $variables)->render();
    }

    /**
     * Resets the output for this display.
     */
    protected function resetOutput(): void
    {
        $this->output      = '';
        $this->outputBuilt = false;
        $this->itemCount   = null;
    }
}
