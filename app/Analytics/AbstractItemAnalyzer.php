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
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base class for item analytics.
 */
abstract class AbstractItemAnalyzer
{
    /**
     * Constant for ordering by section.
     *
     * @var string
     */
    public const ORDER_BY_SECTION = 'section';

    /**
     * Constant for ordering by task (item).
     *
     * @var string
     */
    public const ORDER_BY_TASK = 'task';

    /**
     * The ordering for this analyzer.
     *
     * @var string
     */
    protected string $ordering;

    /**
     * Constructs a new analyzer.
     *
     * @param User $user The user to analyze.
     */
    public function __construct(protected User $user)
    {
        $this->ordering = static::ORDER_BY_TASK;
    }

    /**
     * Creates an Eloquent query builder for closed items, optionally filtered by date range.
     *
     * @param DateTime|null $start Starting DateTime.
     * @param DateTime|null $end   Ending DateTime.
     *
     * @return Builder<Item>
     */
    protected function createQueryBuilder(?DateTime $start = null, ?DateTime $end = null): Builder
    {
        $qb = Item::where('item.user_id', $this->user->id)->where('item.status', 'Closed');

        if ($start !== null && $end !== null) {
            $qb->whereBetween('completed_at', [
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
            ]);
        }

        return $qb;
    }

    /**
     * Executes the query.
     *
     * @param DateTime|null $start Starting DateTime.
     * @param DateTime|null $end   Ending DateTime.
     *
     * @return mixed
     */
    abstract protected function execute(?DateTime $start = null, ?DateTime $end = null);

    /**
     * Returns analytics for a given year.
     *
     * @param int $year The year to return analytics for.
     *
     * @return mixed
     */
    protected function executeDoneDuringYear(int $year)
    {
        $mid = Carbon::create($year, 6, 6, 6, 6, 6);

        return $this->execute(
            (clone $mid)->startOfYear(),
            (clone $mid)->endOfYear()
        );
    }

    /**
     * Returns analytics for the previous month.
     *
     * @return mixed
     */
    protected function executeDoneLastMonth()
    {
        $start = Carbon::now()
            ->settings(['monthOverflow' => false])
            ->subMonth();
        $end = clone $start;

        return $this->execute(
            $start->startOfMonth(),
            $end->endOfMonth()
        );
    }

    /**
     * Returns analytics for the previous week.
     *
     * @return mixed
     */
    protected function executeDoneLastWeek()
    {
        return $this->execute(
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()->endOfWeek()
        );
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
        return $this->execute(
            Carbon::now()
                ->settings(['monthOverflow' => false])
                ->subMonths($distance)
                ->startOfDay(),
            Carbon::now()->endOfDay()
        );
    }

    /**
     * Returns analytics for this month.
     *
     * @return mixed
     */
    protected function executeDoneThisMonth()
    {
        return $this->execute(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );
    }

    /**
     * Returns analytics for this week.
     *
     * @return mixed
     */
    protected function executeDoneThisWeek()
    {
        return $this->execute(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );
    }

    /**
     * Returns analytics for today.
     *
     * @return mixed
     */
    protected function executeDoneToday()
    {
        return $this->execute(
            Carbon::now()->startOfDay(),
            Carbon::now()->endOfDay()
        );
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
        return $this->execute(
            Carbon::now()->subDay()->startOfDay(),
            Carbon::now()->subDay()->endOfDay()
        );
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
