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
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Displays a section.
 */
class SectionDisplay extends BaseDisplay
{
    /**
     * Constructs a new SectionDisplay.
     *
     * @param Section       $section The section to render.
     * @param DisplayConfig $config  The display config to use.
     */
    public function __construct(protected Section $section, DisplayConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Applies any aging filter to the main query builder.
     *
     * @param Builder $qb The query builder to use.
     */
    protected function applyAgingFilter(Builder $qb): void
    {
        if ($this->config->getFilterAging() != 'all') {
            $start = Carbon::now()
                ->subDays((int) $this->config->getFilterAging())
                ->startOfDay();

            $qb->where('created_at', '<=', $start->format('Y-m-d H:i:s'));
        }
    }

    /**
     * Applies a freshness filter to the main query builder.
     *
     * @param Builder $qb The query builder to use.
     *
     * @throws Exception
     */
    protected function applyFreshnessFilter(Builder $qb): void
    {
        if ($this->config->getFilterFreshness() != 'all') {
            $start = match ($this->config->getFilterFreshness()) {
                'today'    => Carbon::now(),
                'recently' => Carbon::now()->subDays(3),
                'week'     => Carbon::now()->startOfWeek(),
                'month'    => Carbon::now()->startOfMonth(),
                default    => throw new Exception('Unsupported level of freshness.  Too fresh.'),
            };

            $qb->where('created_at', '>=', $start->startOfDay()->format('Y-m-d H:i:s'));
        }
    }

    /**
     * Applies any ID filter to the main query builder.
     *
     * @param Builder $qb The query builder to use.
     */
    protected function applyIdFilter(Builder $qb): void
    {
        if (!empty($this->config->getFilterIds())) {
            $qb->whereIn('id', $this->config->getFilterIds());
        }
    }

    /**
     * Applies the priority filter to the main query builder.
     *
     * @param Builder $qb The query builder to use.
     */
    protected function applyPriorityFilter(Builder $qb): void
    {
        $priorityLevels = DisplayHelper::getPriorityLevels();

        if ($this->config->getFilterPriority() == 'high') {
            $qb->where('priority', $priorityLevels['high']);
        } elseif ($this->config->getFilterPriority() == 'normal') {
            $qb->where('priority', '<=', $priorityLevels['normal']);
        } elseif ($this->config->getFilterPriority() == 'low') {
            $qb->where('priority', '<=', $priorityLevels['low']);
        }
    }

    /**
     * Applies a status filter to the main query builder.
     *
     * @param Builder $qb The query builder to use.
     */
    protected function applyStatusFilter(Builder $qb): void
    {
        $qb->where(function (Builder $q) {
            $q->where('status', 'Open');

            if ($this->config->getFilterClosed() == 'all') {
                $q->orWhere('status', 'Closed');
            } elseif ($this->config->getFilterClosed() == 'today' || $this->config->getFilterClosed() == 'recently') {
                $start = ($this->config->getFilterClosed() == 'today') ? Carbon::now() : Carbon::now()->subDays(3);
                $q->orWhere(function (Builder $q2) use ($start) {
                    $q2->where('status', 'Closed')
                        ->where('completed_at', '>=', $start->startOfDay()->format('Y-m-d H:i:s'))
                        ->where('completed_at', '<', Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));
                });
            }

            if ($this->config->getFilterDeleted() == 'all') {
                $q->orWhere('status', 'Deleted');
            } elseif ($this->config->getFilterDeleted() == 'today' || $this->config->getFilterDeleted() == 'recently') {
                $start = ($this->config->getFilterDeleted() == 'today') ? Carbon::now() : Carbon::now()->subDays(3);
                $q->orWhere(function (Builder $q2) use ($start) {
                    $q2->where('status', 'Deleted')
                        ->where('completed_at', '>=', $start->startOfDay()->format('Y-m-d H:i:s'))
                        ->where('completed_at', '<', Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));
                });
            }
        });
    }

    /**
     * Builds the output for this display.
     *
     * @throws Exception
     */
    protected function buildOutput(): void
    {
        $priorityLevels = DisplayHelper::getPriorityLevels();

        $qb = Item::where('section_id', $this->section->getId())
            ->orderBy('priority')
            ->orderBy('task');

        $this->applyAgingFilter($qb);
        $this->applyFreshnessFilter($qb);
        $this->applyIdFilter($qb);
        $this->applyPriorityFilter($qb);
        $this->applyStatusFilter($qb);

        $items = $qb->get();

        if ($items->isEmpty()) {
            $this->outputBuilt = true;
            return;
        }

        $itemCount = $this->getOutputCount();
        foreach ($items as $item) {
            switch ($item->getStatus()) {
                case 'Open':
                    $itemCount->addOpen();
                    break;

                case 'Closed':
                    $itemCount->addClosed();
                    break;
            }
        }

        $template = $this->config->getShowPriorityEditor()
        ? 'partials.section.priority_editor'
        : 'partials.section.main';

        $this->output = $this->render($template, [
            'filterSection'  => $this->config->getFilterSection(),
            'items'          => $items,
            'priorityHigh'   => 2,
            'priorityNormal' => $priorityLevels['normal'],
            'priorityLow'    => $priorityLevels['low'],
            'section'        => $this->section,
            'showPriority'   => $this->config->getShowPriority(),
        ]);

        $this->outputBuilt = true;
    }
}
