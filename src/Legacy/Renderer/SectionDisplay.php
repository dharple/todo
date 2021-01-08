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

use App\Entity\Item;
use App\Entity\Section;
use App\Renderer\DisplayConfig;
use App\Renderer\DisplayHelper;
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
     * @param Section                $section
     * @param DisplayConfig          $config
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $log
     * @param Environment            $twig
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
     * @param QueryBuilder $qb
     *
     * @return void
     */
    protected function applyAgingFilter(QueryBuilder $qb): void
    {
        if ($this->config->getFilterAging() != 'all') {
            $start = Carbon::now()
                ->subDays((int) $this->config->getFilterAging())
                ->startOfDay();

            $qb->andWhere('i.created <= :created')
                ->setParameter('created', $start->format('Y-m-d h:i:s'));
        }
    }

    /**
     * Applies any closed filter to the main QueryBuilder.
     *
     * @param QueryBuilder $qb
     *
     * @return void
     */
    protected function applyClosedFilter(QueryBuilder $qb): void
    {
        if ($this->config->getFilterClosed() == 'all') {
            $qb->andWhere('i.status != :status')
                ->setParameter('status', 'Deleted');
        } elseif ($this->config->getFilterClosed() == 'today' or $this->config->getFilterClosed() == 'recently') {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('i.status', ':statusOpen'),
                $qb->expr()->andX(
                    $qb->expr()->gte('i.completed', ':dayStart'),
                    $qb->expr()->lt('i.completed', ':dayEnd'),
                    $qb->expr()->eq('i.status', ':statusClosed')
                )
            ));

            if ($this->config->getFilterClosed() == 'today') {
                $start = Carbon::now();
            } else {
                $start = Carbon::now()->subDays(3);
            }

            $qb
                ->setParameter('statusOpen', 'Open')
                ->setParameter('dayStart', $start->startOfDay()->format('Y-m-d H:i:s'))
                ->setParameter('dayEnd', Carbon::now()->endOfDay()->format('Y-m-d H:i:s'))
                ->setParameter('statusClosed', 'Closed');
        } else {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', 'Open');
        }
    }

    /**
     * Applies any ID filter to the main QueryBuilder.
     *
     * @param QueryBuilder $qb
     *
     * @return void
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
     * @param QueryBuilder $qb
     *
     * @return void
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
            ->from(Item::class, 'i')
            ->orderBy('i.priority')
            ->addOrderBy('i.task')
            ->where('i.section = :section')
            ->setParameter('section', $this->section->getId());

        $this->applyAgingFilter($qb);
        $this->applyClosedFilter($qb);
        $this->applyIdFilter($qb);
        $this->applyPriorityFilter($qb);

        $items = $qb->getQuery()->getResult();

        if (count($items) == 0) {
            $this->outputBuilt = true;
            return;
        }

        $this->itemCount = 0;
        foreach ($items as $item) {
            if ($item->getStatus() == 'Open') {
                $this->itemCount++;
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
            'section'            => $this->section,
            'showPriority'       => $this->config->getShowPriority(),
        ]);

        $this->outputBuilt = true;
    }
}
