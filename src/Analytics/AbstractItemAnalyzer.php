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
use App\Entity\User;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
     * The Entity Manager to use.
     *
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * The ordering for this analyzer.
     *
     * @var string
     */
    protected string $ordering;

    /**
     * The user to collect stats on.
     *
     * @var User
     */
    protected User $user;

    /**
     * AbstractItemAnalyzer constructor.
     *
     * @param EntityManagerInterface $em   The EntityManager to use.
     * @param User                   $user The user to user.
     */
    public function __construct(EntityManagerInterface $em, User $user)
    {
        $this->em       = $em;
        $this->ordering = static::ORDER_BY_TASK;
        $this->user     = $user;
    }

    /**
     * Creates a QueryBuilder based on start, end, and ordering.
     *
     * @param DateTime|null $start Starting DateTime.
     * @param DateTime|null $end   Ending DateTime.
     *
     * @return QueryBuilder
     *
     * @throws Exception
     */
    protected function createQueryBuilder(?DateTime $start = null, ?DateTime $end = null): QueryBuilder
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('i')
            ->from(Item::class, 'i')
            ->where('i.user = :user')
            ->andWhere('i.status = :status')
            ->setParameter('user', $this->user)
            ->setParameter('status', 'Closed');

        if (!empty($start)) {
            $qb->andWhere('i.completed BETWEEN :start and :end')
                ->setParameter('start', $start->format('Y-m-d H:i:s'))
                ->setParameter('end', $end->format('Y-m-d H:i:s'));
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
