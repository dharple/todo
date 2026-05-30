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

use App\Models\Item;
use App\Models\Section;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Displays a list of sections.
 */
class ListDisplay extends BaseDisplay
{
    /**
     * The footer template to display.
     */
    protected string $footer;

    /**
     * Constructs a new ListDisplay.
     *
     * @param DisplayConfig $config The display config to use.
     * @param User          $user   The user to use.
     */
    public function __construct(DisplayConfig $config, protected User $user)
    {
        $this->config = $config;
    }

    /**
     * Applies any section filter to the query.
     *
     * @param Builder $qb The query builder to use.
     */
    protected function applySectionFilter(Builder $qb): void
    {
        if ($this->config->getFilterSection() != 0) {
            $qb->where('id', $this->config->getFilterSection());
        }
    }

    /**
     * Applies any active or inactive filter to the query.
     *
     * @param Builder $qb The query builder to use.
     */
    protected function applyStatusFilter(Builder $qb): void
    {
        if ($this->config->getShowInactive() === false) {
            $qb->where('status', 'Active');
        }
    }

    /**
     * Builds the output for this display.
     *
     *
     * @throws Exception
     */
    protected function buildOutput(): void
    {
        $qb = Section::where('user_id', $this->user->id)->orderBy('name');

        $this->applySectionFilter($qb);
        $this->applyStatusFilter($qb);

        $sections = $qb->get();

        $sectionOutput = '';
        $sectionsDrawn = 0;

        foreach ($sections as $section) {
            $sectionDisplay = new SectionDisplay($section, $this->config);

            $build = $sectionDisplay->getOutput();

            if (empty($build)) {
                continue;
            }

            $sectionOutput .= $build;
            $sectionsDrawn++;

            $this->getOutputCount()->add($sectionDisplay->getOutputCount());
        }

        $this->output = $this->render('partials.list.wrapper', [
            'footer'        => $this->replaceTotals($this->footer ?? ''),
            'sectionsDrawn' => $sectionsDrawn,
            'sectionOutput' => $sectionOutput,
        ]);

        $this->outputBuilt = true;
    }

    /**
     * Replaces {GRAND_TOTAL} and {NOT_SHOWN} with the appropriate values.
     *
     * @param string $str The string to adjust.
     */
    protected function replaceTotals(string $str): string
    {
        $grandTotal = $this->getOutputCount()->getOpenCount();
        $str        = str_replace('{GRAND_TOTAL}', (string) $grandTotal, $str);
        $total      = Item::forUser($this->user)->open()->count();

        return str_replace('{NOT_SHOWN}', sprintf('%d', $total - $grandTotal), $str);
    }

    /**
     * Sets the footer for the list.
     *
     * @param string $footer The footer string to use.
     */
    public function setFooter(string $footer): ListDisplay
    {
        $this->resetOutput();
        $this->footer = $footer;
        return $this;
    }
}
