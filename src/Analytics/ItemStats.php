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

use App\Helper;
use Carbon\Carbon;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * Historical analytics.  This returns counts of closed items.
 */
class ItemStats extends AbstractItemAnalyzer
{
    /**
     * Cache to use.
     *
     * @var ?Cache
     */
    protected ?Cache $cache = null;

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
     * @param string|null $start Starting datetime string.
     * @param string|null $end   Ending datetime string.
     *
     * @return mixed
     * @throws ORMException
     */
    protected function execute(?string $start = null, ?string $end = null)
    {
        $cache = $this->getCache();
        $cacheKey = md5(serialize([
            __METHOD__,
            $_SESSION['user_id'],
            $start,
            $end,
        ]));

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $qb = $this->createQueryBuilder($start, $end)
            ->select('COUNT(i.id)');
        $result = $qb->getQuery()->getSingleScalarResult();

        $cache->save($cacheKey, $result);

        return $result;
    }

    /**
     * Gets the current average
     *
     * @return float
     *
     * @throws Exception
     */
    public function getAverage(): float
    {
        $user = Helper::getUser();

        $cache = $this->getCache();
        $cacheKey = md5(serialize([
            __METHOD__,
            $user->getId(),
        ]));

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }

        $sections = $user->getSections()->matching(
            new Criteria(
                new Comparison('status', '=', 'Active')
            )
        );

        $criteria = new Criteria();
        $expr = Criteria::expr();
        $criteria->where(
            $expr->orX(
                $expr->eq('status', 'Closed'),
                $expr->andX(
                    $expr->eq('status', 'Open'),
                    $expr->in('section', $sections->toArray())
                )
            )
        );

        $items = $user->getItems()->matching($criteria);
        $values = $items->map(function ($item) {
            $completed = new Carbon($item->getCompleted());
            $created = new Carbon($item->getCreated());
            return max($completed->diffInDays($created), 0) + 1;
        });

        $total = array_reduce($values->toArray(), function ($carry, $value) {
            return $carry + $value;
        });

        $result = $total / count($values);

        $cache->save($cacheKey, $result);

        return $result;
    }

    /**
     * Retrieves a cache instance for caching.
     *
     * @return Cache
     */
    protected function getCache(): Cache
    {
        if (!isset($this->cache)) {
            $this->cache = new ArrayCache();
        }

        return $this->cache;
    }
}
