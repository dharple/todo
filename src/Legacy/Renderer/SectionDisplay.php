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
use App\Helper;
use App\Legacy\DateUtils;
use App\Renderer\DisplayConfig;
use App\Renderer\DisplayHelper;
use Doctrine\ORM\QueryBuilder;
use Exception;

class SectionDisplay extends BaseDisplay
{

    protected DisplayConfig $config;

    protected int $itemCount = 0;

    protected Section $section;

    public function __construct(Section $section, DisplayConfig $config)
    {
        $this->section = $section;
        $this->config = $config;
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
        $dateUtils = new DateUtils();

        if ($this->config->getFilterAging() != 'all') {
            $qb->andWhere('i.created <= :created')
                ->setParameter('created', $dateUtils->getDate(sprintf('-%d days', $this->config->getFilterAging()), 'Y-m-d 00:00:00'));
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
        $dateUtils = new DateUtils();

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
            ))
                ->setParameter('statusOpen', 'Open')
                ->setParameter('dayEnd', $dateUtils->getDate('now', 'Y-m-d 23:59:59'))
                ->setParameter('statusClosed', 'Closed');

            if ($this->config->getFilterClosed() == 'today') {
                $qb->setParameter('dayStart', $dateUtils->getDate('now', 'Y-m-d 00:00:00'));
            } else {
                $qb->setParameter('dayStart', $dateUtils->getDate('-3 days', 'Y-m-d 00:00:00'));
            }
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

        $qb = Helper::getEntityManager()
            ->getRepository(Item::class)
            ->createQueryBuilder('i')
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

    public function getOutputCount(): int
    {
        return $this->itemCount;
    }
}
