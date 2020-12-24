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

use App\Entity\Item;
use App\Entity\Section;
use App\Helper;

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

    protected function buildOutput()
    {
        $entityManager = Helper::getEntityManager();
        $itemRepository = $entityManager->getRepository(Item::class);

        $internalPriorityLevels = $this->config->getInternalPriorityLevels();

        $qb = $itemRepository->createQueryBuilder('i')
            ->orderBy('i.priority')
            ->addOrderBy('i.task')
            ->where('i.section = :section')
            ->setParameter('section', $this->getId());

        $dateUtils = new DateUtils();

        if (!empty($this->config->getIds())) {
            $qb->andWhere('i.id IN (:ids)')
                ->setParameter('ids', $this->config->getIds());
        }

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

        if ($this->config->getFilterPriority() == 'high') {
            $qb->andWhere('i.priority = :priority')
                ->setParameter('priority', intval($internalPriorityLevels['high']));
        } elseif ($this->config->getFilterPriority() == 'normal') {
            $qb->andWhere('i.priority >= :priority')
                ->setParameter('priority', intval($internalPriorityLevels['normal']));
        } elseif ($this->config->getFilterPriority() == 'low') {
            $qb->andWhere('i.priority >= :priority')
                ->setParameter('priority', intval($internalPriorityLevels['low']));
        }

        if ($this->config->getFilterAging() != 'all') {
            $qb->andWhere('i.created <= :created')
                ->setParameter('created', $dateUtils->getDate(sprintf('-%d days', $this->config->getFilterAging()), 'Y-m-d 00:00:00'));
        }

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

        if ($this->config->getShowPriorityEditor() == 'y') {
            $template = 'priority_editor';
        } else {
            $template = 'main';
        }

        $this->output = $this->render(sprintf('partials/section/%s.html.twig', $template), [
            'items'              => $items,
            'priorityHigh'       => 2,
            'priorityNormal'     => $internalPriorityLevels['normal'],
            'section'            => $this->section,
            'sectionUrl'         => str_replace('{SECTION_ID}', ($this->config->getShowSection() ? 0 : $this->getId()), $this->config->getSectionLink()),
            'showPriority'       => $this->config->getShowPriority(),
            'showSectionLink'    => !empty($this->config->getSectionLink()) ? 'y' : 'n',
        ]);

        $this->outputBuilt = true;
    }

    public function getId()
    {
        return $this->section->getId();
    }

    public function getOutputCount()
    {
        return $this->itemCount;
    }
}
