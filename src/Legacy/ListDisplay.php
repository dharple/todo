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
use Exception;

class ListDisplay extends BaseDisplay
{
    public $displayIds;
    public $displayFilterClosed = 'none';
    public $displayFilterPriority = 'all';
    public $displayFilterAging = 'all';
    public $displayShowInactive = 'n';
    public $displayShowSection = 0;
    public $displaySectionLink = '';
    public $displayShowPriority = 'n';
    public $displayShowPriorityEditor = 'n';
    public $footer;
    public $internalPriorityLevels = [];

    public $userId;
    public $itemCount;

    public function __construct($userId)
    {
        $this->userId = $userId;
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

    public function setShowInactive($displayShowInactive)
    {
        $this->displayShowInactive = $displayShowInactive;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
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
        $sectionRepository = $entityManager->getRepository(Section::class);
        $qb = $sectionRepository->createQueryBuilder('s')
            ->where('s.user = :user')
            ->orderBy('s.name')
            ->setParameter('user', $this->userId);

        if ($this->displayShowInactive != 'y') {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', 'Active');
        }

        if ($this->displayShowSection != 0) {
            $qb->andWhere('s.id = :id')
                ->setParameter('id', $this->displayShowSection);
        }

        $sections = $qb->getQuery()->getResult();

        $itemCount = 0;

        $sectionOutput = '';
        $sectionsDrawn = 0;

        foreach ($sections as $section) {
            $sectionDisplay = new SectionDisplay($section);

            $sectionDisplay->setIds($this->displayIds);
            $sectionDisplay->setFilterClosed($this->displayFilterClosed);
            $sectionDisplay->setFilterPriority($this->displayFilterPriority);
            $sectionDisplay->setFilterAging($this->displayFilterAging);
            $sectionDisplay->setShowSection($this->displayShowSection);
            $sectionDisplay->setSectionLink($this->displaySectionLink);
            $sectionDisplay->setShowPriority($this->displayShowPriority);
            $sectionDisplay->setShowPriorityEditor($this->displayShowPriorityEditor);
            $sectionDisplay->setInternalPriorityLevels($this->internalPriorityLevels);

            $build = $sectionDisplay->getOutput();

            if (empty($build)) {
                continue;
            }

            $sectionOutput .= $build;
            $sectionsDrawn++;

            $itemCount += $sectionDisplay->getOutputCount();
        }

        if (empty($sectionOutput)) {
            $this->output = '<b>No Items</b><br>';
            $this->outputBuilt = true;
            $this->itemCount = 0;
            return;
        }

        $class = ($sectionsDrawn > 1) ? 'wrapper-large' : 'wrapper-small';

        $ret = '<div class="wrapper ' . htmlspecialchars($class) . '">';

        $ret .= $sectionOutput;

        $ret .= '<div class="section">';
        $ret .= $this->replaceTotals($this->footer ?? '', $itemCount);
        $ret .= '</div>';

        $ret .= '</div>';

        $this->itemCount = $itemCount;
        $this->output = $ret;
        $this->outputBuilt = true;
    }

    public function getOutputCount()
    {
        return $this->itemCount;
    }

    /**
     * Replaces {GRAND_TOTAL} and {NOT_SHOWN} with the appropriate values.
     *
     * @param string $string
     * @param int    $grand_total
     *
     * @return string
     */
    public function replaceTotals(string $string, int $grand_total)
    {
        $string = str_replace('{GRAND_TOTAL}', $grand_total, $string);

        $entityManager = Helper::getEntityManager();
        $qb = $entityManager->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Item::class, 'i')
            ->where('i.user = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', $this->userId)
            ->setParameter('status', 'Open');

        $total = $qb->getQuery()->getSingleScalarResult();

        $string = str_replace('{NOT_SHOWN}', sprintf('%d', $total - $grand_total), $string);

        return $string;
    }
}
