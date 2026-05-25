<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Analytics;

use App\Models\Item;
use DateTime;
use Illuminate\Database\Eloquent\Collection;

/**
 * Historical analytics. Returns sets of closed items.
 */
class ItemHistory extends AbstractItemAnalyzer
{
    /**
     * Returns analytics for a given year.
     *
     * @param int $year The year to return analytics for.
     *
     * @return Collection<int, Item>
     */
    public function doneDuringYear(int $year): Collection
    {
        return $this->executeDoneDuringYear($year);
    }

    /**
     * Returns analytics for the previous month.
     *
     * @return Collection<int, Item>
     */
    public function doneLastMonth(): Collection
    {
        return $this->executeDoneLastMonth();
    }

    /**
     * Returns analytics for the previous week.
     *
     * @return Collection<int, Item>
     */
    public function doneLastWeek(): Collection
    {
        return $this->executeDoneLastWeek();
    }

    /**
     * Returns analytics for a number of previous months.
     *
     * @param int $distance How many months to go back.
     *
     * @return Collection<int, Item>
     */
    public function donePreviousMonths(int $distance): Collection
    {
        return $this->executeDonePreviousMonths($distance);
    }

    /**
     * Returns analytics for this month.
     *
     * @return Collection<int, Item>
     */
    public function doneThisMonth(): Collection
    {
        return $this->executeDoneThisMonth();
    }

    /**
     * Returns analytics for this week.
     *
     * @return Collection<int, Item>
     */
    public function doneThisWeek(): Collection
    {
        return $this->executeDoneThisWeek();
    }

    /**
     * Returns analytics for today.
     *
     * @return Collection<int, Item>
     */
    public function doneToday(): Collection
    {
        return $this->executeDoneToday();
    }

    /**
     * Returns analytics for all time.
     *
     * @return Collection<int, Item>
     */
    public function doneTotal(): Collection
    {
        return $this->executeDoneTotal();
    }

    /**
     * Returns analytics for yesterday.
     *
     * @return Collection<int, Item>
     */
    public function doneYesterday(): Collection
    {
        return $this->executeDoneYesterday();
    }

    /**
     * Executes the query.
     *
     * @param DateTime|null $start Starting DateTime.
     * @param DateTime|null $end   Ending DateTime.
     *
     * @return Collection<int, Item>
     */
    protected function execute(?DateTime $start = null, ?DateTime $end = null): Collection
    {
        $qb = $this->createQueryBuilder($start, $end);

        if ($this->ordering === static::ORDER_BY_SECTION) {
            $qb->leftJoin('section', 'section.id', '=', 'item.section_id')
                ->orderByRaw('DATE(item.completed_at) DESC')
                ->orderBy('section.name')
                ->orderBy('item.task');
        } else {
            $qb->orderByRaw('DATE(completed_at) DESC')
                ->orderBy('task');
        }

        return $qb->get();
    }
}
