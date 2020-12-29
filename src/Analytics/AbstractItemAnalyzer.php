<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Analytics;

use App\Entity\Item;
use App\Helper;
use App\Legacy\DateUtils;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class AbstractItemAnalyzer
 */
abstract class AbstractItemAnalyzer
{
    /**
     * Constant for ordering by section
     *
     * @var string
     */
    public const ORDER_BY_SECTION = 'section';

    /**
     * Constant for ordering by task (item)
     *
     * @var string
     */
    public const ORDER_BY_TASK = 'task';

    /**
     * Helper utility.
     *
     * @var DateUtils
     */
    protected DateUtils $dateUtils;

    /**
     * The ordering for this analyzer.
     *
     * @var string
     */
    protected string $ordering;

    /**
     * AbstractItemAnalyzer constructor.
     */
    public function __construct()
    {
        $this->dateUtils = new DateUtils();
        $this->ordering = static::ORDER_BY_TASK;
    }

    /**
     * Creates a QueryBuilder based on start, end, and ordering.
     *
     * @param string|null $start Starting datetime string.
     * @param string|null $end   Ending datetime string.
     *
     * @return QueryBuilder
     *
     * @throws ORMException
     * @throws Exception
     */
    protected function createQueryBuilder(?string $start = null, ?string $end = null): QueryBuilder
    {
        $qb = Helper::getEntityManager()
            ->getRepository(Item::class)
            ->createQueryBuilder('i');

        $qb->where('i.user = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', Helper::getUser())
            ->setParameter('status', 'Closed');

        if (!empty($start)) {
            $qb->andWhere('i.completed BETWEEN :start and :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        if ($this->ordering == static::ORDER_BY_SECTION) {
            $qb->leftJoin('i.section', 's')
                ->orderBy('DATE(i.completed)', 'DESC')
                ->addOrderBy('s.name', 'ASC')
                ->addOrderBy('i.task', 'ASC');
        } else {
            $qb->orderBy('DATE(i.completed)', 'DESC')
                ->addOrderBy('i.task', 'ASC');
        }

        return $qb;
    }

    /**
     * Executes the query.
     *
     * @param string|null $start Starting datetime string.
     * @param string|null $end   Ending datetime string.
     *
     * @return mixed
     */
    abstract protected function execute(?string $start = null, ?string $end = null);

    /**
     * Returns analytics for the previous month.
     *
     * @return mixed
     */
    protected function executeDoneLastMonth()
    {
        $work = date('Y-m-15') . ' -1 month';
        $start = $this->dateUtils->getMonthStart($work, 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getMonthEnd($work, 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Returns analytics for the previous week.
     *
     * @return mixed
     */
    protected function executeDoneLastWeek()
    {
        $start = $this->dateUtils->getWeekStart('-1 week', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getWeekEnd('-1 week', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Returns analytics for a number of previous months.
     *
     * @param int $distance How many months to go back.
     *
     * @return mixed
     */
    protected function executeDonePreviousMonths(int $distance)
    {
        $work = ' -' . intval($distance) . ' month';
        $start = $this->dateUtils->getDate($work, 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getDate('now', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Returns analytics for this month.
     *
     * @return mixed
     */
    protected function executeDoneThisMonth()
    {
        $work = date('Y-m-15');
        $start = $this->dateUtils->getMonthStart($work, 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getMonthEnd($work, 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Returns analytics for this week.
     *
     * @return mixed
     */
    protected function executeDoneThisWeek()
    {
        $start = $this->dateUtils->getWeekStart('now', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getWeekEnd('now', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Returns analytics for today.
     *
     * @return mixed
     */
    protected function executeDoneToday()
    {
        $start = $this->dateUtils->getDate('now', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getDate('now', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Returns analytics for all time.
     *
     * @return mixed
     */
    protected function executeDoneTotal()
    {
        return $this->execute();
    }

    /**
     * Returns analytics for yesterday.
     *
     * @return mixed
     */
    protected function executeDoneYesterday()
    {
        $start = $this->dateUtils->getDate('yesterday', 'Y-m-d 00:00:00');
        $end = $this->dateUtils->getDate('yesterday', 'Y-m-d 23:59:59');
        return $this->execute($start, $end);
    }

    /**
     * Sets the ordering to use for the analytics.
     *
     * @param string $ordering The ordering to use.
     *
     * @return AbstractItemAnalyzer
     */
    public function setOrdering(string $ordering): AbstractItemAnalyzer
    {
        $this->ordering = $ordering;
        return $this;
    }
}
