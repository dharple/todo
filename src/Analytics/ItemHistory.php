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
use Doctrine\ORM\ORMException;

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
     * @param string|null $start Starting datetime string.
     * @param string|null $end   Ending datetime string.
     *
     * @return mixed
     * @throws ORMException
     */
    protected function execute(?string $start = null, ?string $end = null)
    {
        return $this->createQueryBuilder($start, $end)
            ->getQuery()
            ->getResult();
    }
}
