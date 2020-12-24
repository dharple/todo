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
    public $displayIds;
    public $displayFilterClosed = 'none';
    public $displayFilterPriority = 'all';
    public $displayFilterAging = 'all';
    public $displayShowSection = 0;
    public $displaySectionLink = '';
    public $displayShowPriority = 'n';
    public $displayShowPriorityEditor = 'n';
    public $internalPriorityLevels = [];

    public Section $section;

    public $itemCount = 0;

    public function __construct(Section $section)
    {
        $this->section = $section;
    }

    public function setFilterClosed($displayFilterClosed)
    {
        $this->displayFilterClosed = $displayFilterClosed;
    }

    public function setFilterPriority($displayFilterPriority)
    {
        $this->displayFilterPriority = $displayFilterPriority;
    }

    public function setFilterAging($displayFilterAging)
    {
        $this->displayFilterAging = $displayFilterAging;
    }

    public function setIds($ids)
    {
        $this->displayIds = $ids;
    }

    public function setShowSection($displayShowSection)
    {
        $this->displayShowSection = $displayShowSection;
    }

    public function setSectionLink($displaySectionLink)
    {
        $this->displaySectionLink = $displaySectionLink;
    }

    public function setShowPriority($displayShowPriority)
    {
        $this->displayShowPriority = $displayShowPriority;
    }

    public function setShowPriorityEditor($displayShowPriorityEditor)
    {
        $this->displayShowPriorityEditor = $displayShowPriorityEditor;
    }

    public function setInternalPriorityLevels($internalPriorityLevels)
    {
        $this->internalPriorityLevels = $internalPriorityLevels;
    }

    protected function buildOutput()
    {
        $entityManager = Helper::getEntityManager();
        $itemRepository = $entityManager->getRepository(Item::class);

        $qb = $itemRepository->createQueryBuilder('i')
            ->orderBy('i.priority')
            ->addOrderBy('i.task')
            ->where('i.section = :section')
            ->setParameter('section', $this->getId());

        $dateUtils = new DateUtils();

        if (is_array($this->displayIds)) {
            $qb->andWhere('i.id IN (:ids)')
                ->setParameter('ids', $this->displayIds);
        }

        if ($this->displayFilterClosed == 'all') {
            $qb->andWhere('i.status != :status')
                ->setParameter('status', 'Deleted');
        } elseif ($this->displayFilterClosed == 'today' or $this->displayFilterClosed == 'recently') {
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

            if ($this->displayFilterClosed == 'today') {
                $qb->setParameter('dayStart', $dateUtils->getDate('now', 'Y-m-d 00:00:00'));
            } else {
                $qb->setParameter('dayStart', $dateUtils->getDate('-3 days', 'Y-m-d 00:00:00'));
            }
        } else {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', 'Open');
        }

        if ($this->displayFilterPriority == 'high') {
            $qb->andWhere('i.priority = :priority')
                ->setParameter('priority', intval($this->internalPriorityLevels['high']));
        } elseif ($this->displayFilterPriority == 'normal') {
            $qb->andWhere('i.priority >= :priority')
                ->setParameter('priority', intval($this->internalPriorityLevels['normal']));
        } elseif ($this->displayFilterPriority == 'low') {
            $qb->andWhere('i.priority >= :priority')
                ->setParameter('priority', intval($this->internalPriorityLevels['low']));
        }

        if ($this->displayFilterAging != 'all') {
            $qb->andWhere('i.created <= :created')
                ->setParameter('created', $dateUtils->getDate(sprintf('-%d days', $this->displayFilterAging), 'Y-m-d 00:00:00'));
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

        if ($this->displayShowPriorityEditor == 'y') {
            $template = 'priority_editor';
        } else {
            $template = 'main';
        }

        $this->output = $this->render(sprintf('partials/section/%s.html.twig', $template), [
            'items'              => $items,
            'priorityHigh'       => 2,
            'priorityNormal'     => $this->internalPriorityLevels['normal'],
            'section'            => $this->section,
            'sectionUrl'         => str_replace('{SECTION_ID}', ($this->displayShowSection ? 0 : $this->getId()), $this->displaySectionLink),
            'showPriority'       => $this->displayShowPriority,
            'showSectionLink'    => isset($this->displaySectionLink) ? 'y' : 'n',
        ]);

        $this->outputBuilt = true;
    }

    public function getOutputCount()
    {
        return $this->itemCount;
    }

    public function getId()
    {
        return $this->section->getId();
    }
}
