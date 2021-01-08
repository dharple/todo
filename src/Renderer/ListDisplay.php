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

use App\Auth\Guard;
use App\Entity\Item;
use App\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * Displays a list of sections.
 */
class ListDisplay extends BaseDisplay
{
    /**
     * The footer template to display.
     *
     * @var string
     */
    protected string $footer;

    /**
     * ListDisplay constructor.
     *
     * @param DisplayConfig          $config The display config to use.
     * @param EntityManagerInterface $em     The entity manager to use.
     * @param LoggerInterface        $log    The logger to use.
     * @param Environment            $twig   The renderer to use.
     */
    public function __construct(
        DisplayConfig $config,
        EntityManagerInterface $em,
        LoggerInterface $log,
        Environment $twig
    ) {
        $this->config = $config;
        $this->em     = $em;
        $this->log    = $log;
        $this->twig   = $twig;
    }

    /**
     * Applies any section filter to the query.
     *
     * @param QueryBuilder $qb The querybuilder to use.
     *
     * @return void
     */
    protected function applySectionFilter(QueryBuilder $qb): void
    {
        if ($this->config->getFilterSection() != 0) {
            $qb->andWhere('s.id = :id')
                ->setParameter('id', $this->config->getFilterSection());
        }
    }

    /**
     * Applies any active or inactive filter to the query.
     *
     * @param QueryBuilder $qb The querybuilder to use.
     *
     * @return void
     */
    protected function applyStatusFilter(QueryBuilder $qb): void
    {
        if ($this->config->getShowInactive() === false) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', 'Active');
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
        $user = Guard::getUser();

        $qb = $this->em
            ->createQueryBuilder()
            ->select('s')
            ->from(Section::class, 's')
            ->where('s.user = :user')
            ->orderBy('s.name')
            ->setParameter('user', $user);

        $this->applySectionFilter($qb);
        $this->applyStatusFilter($qb);

        $sections = $qb->getQuery()->getResult();

        $itemCount = 0;

        $sectionOutput = '';
        $sectionsDrawn = 0;

        foreach ($sections as $section) {
            $sectionDisplay = new SectionDisplay($section, $this->config, $this->em, $this->log, $this->twig);

            $build = $sectionDisplay->getOutput();

            if (empty($build)) {
                continue;
            }

            $sectionOutput .= $build;
            $sectionsDrawn++;

            $itemCount += $sectionDisplay->getOutputCount();
        }

        $this->output = $this->render('partials/list/wrapper.html.twig', [
            'footer'         => $this->replaceTotals($this->footer ?? '', $itemCount),
            'sectionsDrawn'  => $sectionsDrawn,
            'sectionOutput'  => $sectionOutput,
        ]);

        $this->itemCount = $itemCount;
        $this->outputBuilt = true;
    }

    /**
     * Replaces {GRAND_TOTAL} and {NOT_SHOWN} with the appropriate values.
     *
     * @param string $str        The string to adjust.
     * @param int    $grandTotal The grand total to use.
     *
     * @return string
     *
     * @throws Exception
     */
    protected function replaceTotals(string $str, int $grandTotal): string
    {
        $user = Guard::getUser();

        $str = str_replace('{GRAND_TOTAL}', (string) $grandTotal, $str);

        $qb = $this->em
            ->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Item::class, 'i')
            ->where('i.user = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'Open');

        $total = $qb->getQuery()->getSingleScalarResult();

        return str_replace('{NOT_SHOWN}', sprintf('%d', $total - $grandTotal), $str);
    }

    /**
     * Sets the footer for the list.
     *
     * @param mixed $footer The footer string to use.
     *
     * @return ListDisplay
     */
    public function setFooter($footer): ListDisplay
    {
        $this->footer = $footer;
        return $this;
    }
}
