<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Renderer;

use App\Entity\Item;
use App\Entity\Section;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * Displays a section.
 */
class SectionDisplay extends BaseDisplay
{
    /**
     * The section to render.
     *
     * @var Section
     */
    protected Section $section;

    /**
     * SectionDisplay constructor.
     *
     * @param Section                $section The section to render.
     * @param DisplayConfig          $config  The display config to use.
     * @param EntityManagerInterface $em      The entity manager to use.
     * @param LoggerInterface        $log     The logger to use.
     * @param Environment            $twig    The renderer to use.
     */
    public function __construct(
        Section $section,
        DisplayConfig $config,
        EntityManagerInterface $em,
        LoggerInterface $log,
        Environment $twig
    ) {
        $this->config  = $config;
        $this->em      = $em;
        $this->log     = $log;
        $this->section = $section;
        $this->twig    = $twig;
    }

    /**
     * Applies any aging filter to the main QueryBuilder.
     *
     * @param QueryBuilder $qb The query builder to use.
     */
    protected function applyAgingFilter(QueryBuilder $qb): void
    {
        if ($this->config->getFilterAging() != 'all') {
            $start = Carbon::now()
                ->subDays((int) $this->config->getFilterAging())
                ->startOfDay();

            $qb->andWhere('i.created <= :created')
                ->setParameter('created', $start->format('Y-m-d H:i:s'));
        }
    }

    /**
     * Applies a freshness filter to the main QueryBuilder.
     *
     * @param QueryBuilder $qb The query builder to use.
     *
     * @throws Exception
     */
    protected function applyFreshnessFilter(QueryBuilder $qb): void
    {
        if ($this->config->getFilterFreshness() != 'all') {
            $start = match ($this->config->getFilterFreshness()) {
                'today' => Carbon::now(),
                'recently' => Carbon::now()->subDays(3),
                'week' => Carbon::now()->startOfWeek(),
                'month' => Carbon::now()->startOfMonth(),
                default => throw new Exception('Unsupported level of freshness.  Too fresh.'),
            };

            $qb->andWhere('i.created >= :created')
                ->setParameter('created', $start->startOfDay()->format('Y-m-d H:i:s'));
        }
    }

    /**
     * Applies any ID filter to the main QueryBuilder.
     *
     * @param QueryBuilder $qb The query builder to use.
     */
    protected function applyIdFilter(QueryBuilder $qb): void
    {
        if (!empty($this->config->getFilterIds())) {
            $qb->andWhere('i.id IN (:ids)')
                ->setParameter('ids', $this->config->getFilterIds());
        }
    }

    /**
     * Apply the priority filter to the main query.
     *
     * @param QueryBuilder $qb The query builder to use.
     */
    protected function applyPriorityFilter(QueryBuilder $qb): void
    {
        $priorityLevels = DisplayHelper::getPriorityLevels();

        if ($this->config->getFilterPriority() == 'high') {
            $qb->andWhere('i.priority = :priority')
                ->setParameter('priority', intval($priorityLevels['high']));
        } elseif ($this->config->getFilterPriority() == 'normal') {
            $qb->andWhere('i.priority <= :priority')
                ->setParameter('priority', intval($priorityLevels['normal']));
        } elseif ($this->config->getFilterPriority() == 'low') {
            $qb->andWhere('i.priority <= :priority')
                ->setParameter('priority', intval($priorityLevels['low']));
        }
    }

    /**
     * Applies a status filter to the main QueryBuilder.
     *
     * @param QueryBuilder $qb The query builder to use.
     */
    protected function applyStatusFilter(QueryBuilder $qb): void
    {
        $closedExpr = null;
        $deletedExpr = null;

        if ($this->config->getFilterClosed() == 'all') {
            $closedExpr = $qb->expr()->eq('i.status', ':statusClosed');
            $qb->setParameter('statusClosed', 'Closed');
        } elseif ($this->config->getFilterClosed() == 'today' || $this->config->getFilterClosed() == 'recently') {
            $closedExpr = $qb->expr()->andX(
                $qb->expr()->gte('i.completed', ':closedStart'),
                $qb->expr()->lt('i.completed', ':closedEnd'),
                $qb->expr()->eq('i.status', ':statusClosed')
            );
            $start = ($this->config->getFilterClosed() == 'today') ? Carbon::now() : Carbon::now()->subDays(3);
            $qb->setParameter('closedStart', $start->startOfDay()->format('Y-m-d H:i:s'));
            $qb->setParameter('closedEnd', Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));
            $qb->setParameter('statusClosed', 'Closed');
        }

        if ($this->config->getFilterDeleted() == 'all') {
            $deletedExpr = $qb->expr()->eq('i.status', ':statusDeleted');
            $qb->setParameter('statusDeleted', 'Deleted');
        } elseif ($this->config->getFilterDeleted() == 'today' || $this->config->getFilterDeleted() == 'recently') {
            $deletedExpr = $qb->expr()->andX(
                $qb->expr()->gte('i.completed', ':deletedStart'),
                $qb->expr()->lt('i.completed', ':deletedEnd'),
                $qb->expr()->eq('i.status', ':statusDeleted')
            );
            $start = ($this->config->getFilterDeleted() == 'today') ? Carbon::now() : Carbon::now()->subDays(3);
            $qb->setParameter('deletedStart', $start->startOfDay()->format('Y-m-d H:i:s'));
            $qb->setParameter('deletedEnd', Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));
            $qb->setParameter('statusDeleted', 'Deleted');
        }

        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('i.status', ':statusOpen'),
            $closedExpr,
            $deletedExpr
        ));

        $qb->setParameter('statusOpen', 'Open');
    }

    /**
     * Builds the output for this display.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function buildOutput(): void
    {
        $priorityLevels = DisplayHelper::getPriorityLevels();

        $qb = $this->em
            ->createQueryBuilder()
            ->select('i')
            ->from(Item::class, 'i')
            ->orderBy('i.priority')
            ->addOrderBy('i.task')
            ->where('i.section = :section')
            ->setParameter('section', $this->section->getId());

        $this->applyAgingFilter($qb);
        $this->applyFreshnessFilter($qb);
        $this->applyIdFilter($qb);
        $this->applyPriorityFilter($qb);
        $this->applyStatusFilter($qb);

        $items = $qb->getQuery()->getResult();

        if ((is_countable($items) ? count($items) : 0) == 0) {
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

        $template = sprintf(
            'partials/section/%s.html.twig',
            $this->config->getShowPriorityEditor() ? 'priority_editor' : 'main'
        );

        $this->output = $this->render($template, [
            'filterSection'      => $this->config->getFilterSection(),
            'items'              => $items,
            'priorityHigh'       => 2,
            'priorityNormal'     => $priorityLevels['normal'],
            'priorityLow'        => $priorityLevels['low'],
            'section'            => $this->section,
            'showPriority'       => $this->config->getShowPriority(),
        ]);

        $this->outputBuilt = true;
    }
}
