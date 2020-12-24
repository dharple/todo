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

class ListDisplay extends BaseDisplay
{
    protected DisplayConfig $config;

    protected string $footer;

    protected int $itemCount = 0;

    protected int $userId;

    public function __construct($userId, DisplayConfig $config)
    {
        $this->userId = $userId;
        $this->config = $config;
    }

    protected function buildOutput()
    {
        $entityManager = Helper::getEntityManager();
        $sectionRepository = $entityManager->getRepository(Section::class);
        $qb = $sectionRepository->createQueryBuilder('s')
            ->where('s.user = :user')
            ->orderBy('s.name')
            ->setParameter('user', $this->userId);

        if ($this->config->getShowInactive() != 'y') {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', 'Active');
        }

        if ($this->config->getShowSection() != 0) {
            $qb->andWhere('s.id = :id')
                ->setParameter('id', $this->config->getShowSection());
        }

        $sections = $qb->getQuery()->getResult();

        $itemCount = 0;

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

    /**
     * @param mixed $footer
     * @return ListDisplay
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
        return $this;
    }
}
