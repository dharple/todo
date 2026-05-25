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
use App\Models\Section;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Historical analytics. Returns counts of closed items.
 */
class ItemStats extends AbstractItemAnalyzer
{
    /**
     * How long to cache stats for, in seconds.
     *
     * @var integer
     */
    protected const CACHE_TIMEOUT = 30;

    /**
     * Returns analytics for a given year.
     *
     * @param int $year The year to return analytics for.
     *
     * @return int
     */
    public function doneDuringYear(int $year): int
    {
        return $this->executeDoneDuringYear($year);
    }

    /**
     * Returns analytics for the previous month.
     *
     * @return int
     */
    public function doneLastMonth(): int
    {
        return $this->executeDoneLastMonth();
    }

    /**
     * Returns analytics for the previous week.
     *
     * @return int
     */
    public function doneLastWeek(): int
    {
        return $this->executeDoneLastWeek();
    }

    /**
     * Returns analytics for a number of previous months.
     *
     * @param int $distance How many months to go back.
     *
     * @return int
     */
    public function donePreviousMonths(int $distance): int
    {
        return $this->executeDonePreviousMonths($distance);
    }

    /**
     * Returns analytics for this month.
     *
     * @return int
     */
    public function doneThisMonth(): int
    {
        return $this->executeDoneThisMonth();
    }

    /**
     * Returns analytics for this week.
     *
     * @return int
     */
    public function doneThisWeek(): int
    {
        return $this->executeDoneThisWeek();
    }

    /**
     * Returns analytics for today.
     *
     * @return int
     */
    public function doneToday(): int
    {
        return $this->executeDoneToday();
    }

    /**
     * Returns analytics for all time.
     *
     * @return int
     */
    public function doneTotal(): int
    {
        return $this->executeDoneTotal();
    }

    /**
     * Returns analytics for yesterday.
     *
     * @return int
     */
    public function doneYesterday(): int
    {
        return $this->executeDoneYesterday();
    }

    /**
     * Executes the query.
     *
     * @param DateTime|null $start Starting DateTime.
     * @param DateTime|null $end   Ending DateTime.
     *
     * @return int
     */
    protected function execute(?DateTime $start = null, ?DateTime $end = null): int
    {
        $key = hash('md5', serialize([__METHOD__, $this->user->id, $start, $end]));

        Log::debug(sprintf(
            '%s, %s, %s, %s, %s, %s',
            __METHOD__,
            $key,
            Cache::has($key) ? 'hit' : 'miss',
            $this->user->id,
            $start ?? 'null',
            $end ?? 'null'
        ));

        return (int) Cache::remember($key, static::CACHE_TIMEOUT, fn () =>
            $this->createQueryBuilder($start, $end)->count());
    }

    /**
     * Gets the current average days per item.
     *
     * @return float
     */
    public function getAverage(): float
    {
        $key = hash('md5', serialize([__METHOD__, $this->user->id]));

        Log::debug(sprintf(
            '%s, %s, %s, %s',
            __METHOD__,
            $key,
            Cache::has($key) ? 'hit' : 'miss',
            $this->user->id
        ));

        return (float) Cache::remember($key, static::CACHE_TIMEOUT, function () {
            $activeSectionIds = Section::where('user_id', $this->user->id)
                ->where('status', 'Active')
                ->pluck('id');

            $items = Item::where('user_id', $this->user->id)
                ->where(function ($q) use ($activeSectionIds) {
                    $q->where('status', 'Closed')
                        ->orWhere(function ($q2) use ($activeSectionIds) {
                            $q2->where('status', 'Open')->whereIn('section_id', $activeSectionIds);
                        });
                })
                ->get();

            if ($items->isEmpty()) {
                $result = 0.0;
            } else {
                $total = $items->sum(function (Item $item) {
                    $completed = $item->completed_at ?? Carbon::now();
                    return (int) max($item->created_at->diffInDays($completed), 0) + 1;
                });
                $result = $total / $items->count();
            }

            return $result;
        });
    }

    /**
     * Loads a summary of number of items done by week.
     *
     * Returns an array sorted in ascending order by start date. Each element contains:
     * - string weekOf  Start of week (formatted)
     * - int    done    Number of items done
     *
     * @param int $weeks How many weeks to load, starting with the current week.
     *
     * @return array<int, array{weekOf: string, done: int}>
     */
    public function getWeeklySummary(int $weeks): array
    {
        $current = Carbon::now();
        $ret     = [];

        while ($weeks-- > 0) {
            $start = (clone $current)->startOfWeek();
            $end   = (clone $current)->endOfWeek();

            $ret[] = [
                'weekOf' => $start->format('M j'),
                'done'   => $this->execute($start, $end),
            ];

            $current = $current->subWeek();
        }

        return array_reverse($ret);
    }

    /**
     * Loads a summary of number of items done by year.
     *
     * @return array<int, int>
     */
    public function getYearlySummary(): array
    {
        $year    = (int) date('Y');
        $minYear = $year - 10;
        $total   = $this->doneTotal();
        $seen    = 0;
        $ret     = [];

        while ($seen < $total && $year > $minYear) {
            $current = $this->doneDuringYear($year);

            $ret[$year] = $current;

            $total += $current;
            $year--;
        }

        return $ret;
    }
}
