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
use DateTimeImmutable;
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
    protected const CACHE_TIMEOUT = 3;

    /**
     * Counts already looked up during this request, keyed by cache key.
     *
     * @var array<string, int>
     */
    protected array $results = [];

    /**
     * Returns analytics for a given year.
     *
     * @param int $year The year to return analytics for.
     */
    public function doneDuringYear(int $year): int
    {
        return $this->executeDoneDuringYear($year);
    }

    /**
     * Returns analytics for the previous month.
     */
    public function doneLastMonth(): int
    {
        return $this->executeDoneLastMonth();
    }

    /**
     * Returns analytics for the previous week.
     */
    public function doneLastWeek(): int
    {
        return $this->executeDoneLastWeek();
    }

    /**
     * Returns analytics for a number of previous months.
     *
     * @param int $distance How many months to go back.
     */
    public function donePreviousMonths(int $distance): int
    {
        return $this->executeDonePreviousMonths($distance);
    }

    /**
     * Returns analytics for this month.
     */
    public function doneThisMonth(): int
    {
        return $this->executeDoneThisMonth();
    }

    /**
     * Returns analytics for this week.
     */
    public function doneThisWeek(): int
    {
        return $this->executeDoneThisWeek();
    }

    /**
     * Returns analytics for today.
     */
    public function doneToday(): int
    {
        return $this->executeDoneToday();
    }

    /**
     * Returns analytics for all time.
     */
    public function doneTotal(): int
    {
        return $this->executeDoneTotal();
    }

    /**
     * Returns analytics for yesterday.
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
     */
    protected function execute(?DateTime $start = null, ?DateTime $end = null): int
    {
        $key = hash('md5', serialize([__METHOD__, $this->user->id, $start, $end]));

        if (isset($this->results[$key])) {
            return $this->results[$key];
        }

        Log::debug(sprintf(
            '%s, %s, %s, %s, %s, %s',
            __METHOD__,
            $key,
            Cache::has($key) ? 'hit' : 'miss',
            $this->user->id,
            $start ?? 'null',
            $end ?? 'null'
        ));

        return $this->results[$key] = (int) Cache::remember($key, static::CACHE_TIMEOUT, fn () =>
            $this->createQueryBuilder($start, $end)->count());
    }

    /**
     * Gets the current average days per item.
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

            // Raw rows and native date math: hydrating models and using Carbon
            // here is slow for users with many closed items.
            $rows = Item::where('user_id', $this->user->id)
                ->where(function ($q) use ($activeSectionIds) {
                    $q->where('status', 'Closed')
                        ->orWhere(function ($q2) use ($activeSectionIds) {
                            $q2->where('status', 'Open')->whereIn('section_id', $activeSectionIds);
                        });
                })
                ->toBase()
                ->get(['created_at', 'completed_at']);

            if ($rows->isEmpty()) {
                $result = 0.0;
            } else {
                $now   = Carbon::now()->toDateTimeImmutable();
                $total = 0;
                foreach ($rows as $row) {
                    $created   = new DateTimeImmutable((string) $row->created_at);
                    $completed = ($row->completed_at === null) ? $now : new DateTimeImmutable((string) $row->completed_at);
                    $diff      = $created->diff($completed);
                    $total    += ($diff->invert ? 0 : (int) $diff->days) + 1;
                }
                $result = $total / $rows->count();
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
        $year    = Carbon::now()->year;
        $minYear = $year - 10;
        $total   = $this->doneTotal();
        $seen    = 0;
        $ret     = [];

        while ($seen < $total && $year > $minYear) {
            $current = $this->doneDuringYear($year);

            $ret[$year] = $current;

            $seen += $current;
            $year--;
        }

        return $ret;
    }
}
