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
use DateTime;
use Exception;

/**
 * Historical analytics.  This returns sets of closed items.
 */
class ItemHistory extends AbstractItemAnalyzer
{

    /**
     * Returns analytics for the previous month.
     *
     * @return Item[]
     */
    public function doneLastMonth(): array
    {
        return $this->executeDoneLastMonth();
    }

    /**
     * Returns analytics for the previous week.
     *
     * @return Item[]
     */
    public function doneLastWeek(): array
    {
        return $this->executeDoneLastWeek();
    }

    /**
     * Returns analytics for a number of previous months.
     *
     * @param int $distance How many months to go back.
     *
     * @return Item[]
     */
    public function donePreviousMonths(int $distance): array
    {
        return $this->executeDonePreviousMonths($distance);
    }

    /**
     * Returns analytics for this month.
     *
     * @return Item[]
     */
    public function doneThisMonth(): array
    {
        return $this->executeDoneThisMonth();
    }

    /**
     * Returns analytics for this week.
     *
     * @return Item[]
     */
    public function doneThisWeek(): array
    {
        return $this->executeDoneThisWeek();
    }

    /**
     * Returns analytics for today.
     *
     * @return Item[]
     */
    public function doneToday(): array
    {
        return $this->executeDoneToday();
    }

    /**
     * Returns analytics for all time.
     *
     * @return Item[]
     */
    public function doneTotal(): array
    {
        return $this->executeDoneTotal();
    }

    /**
     * Returns analytics for yesterday.
     *
     * @return Item[]
     */
    public function doneYesterday(): array
    {
        return $this->executeDoneYesterday();
    }

    /**
     * Executes the query.
     *
     * @param DateTime|null $start Starting DateTime.
     * @param DateTime|null $end   Ending DateTime.
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function execute(?DateTime $start = null, ?DateTime $end = null)
    {
        $qb = $this->createQueryBuilder($start, $end);

        if ($this->ordering == static::ORDER_BY_SECTION) {
            $qb->leftJoin('i.section', 's')
                ->orderBy('DATE(i.completed)', 'DESC')
                ->addOrderBy('s.name', 'ASC')
                ->addOrderBy('i.task', 'ASC');
        } else {
            $qb->orderBy('DATE(i.completed)', 'DESC')
                ->addOrderBy('i.task', 'ASC');
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
