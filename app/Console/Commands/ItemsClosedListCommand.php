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

namespace App\Console\Commands;

use App\Models\Item;
use DateTime;
use Illuminate\Console\Command;

/**
 * Command to list closed items in a given timeframe.
 */
class ItemsClosedListCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List closed items';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'items:closed:list
        {--year= : Show items closed in this year (YYYY); cannot be combined with --start-date or --end-date}
        {--start-date= : Show items closed on or after this date (YYYY-MM-DD)}
        {--end-date= : Show items closed on or before this date (YYYY-MM-DD)}';

    /**
     * Executes the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $year      = $this->option('year');
        $startDate = $this->option('start-date');
        $endDate   = $this->option('end-date');

        if ($year !== null && ($startDate !== null || $endDate !== null)) {
            $this->error('--year cannot be combined with --start-date or --end-date');
            return self::FAILURE;
        }

        if ($year !== null) {
            $startDate = $year . '-01-01';
            $endDate   = $year . '-12-31';
        }

        $qb = Item::where('status', 'Closed')->orderBy('completed_at', 'desc');

        if ($startDate !== null) {
            $start = DateTime::createFromFormat('Y-m-d', $startDate);
            if ($start === false) {
                $this->error('Invalid --start-date format; expected YYYY-MM-DD');
                return self::FAILURE;
            }
            $qb->where('completed_at', '>=', $start->format('Y-m-d 00:00:00'));
        }

        if ($endDate !== null) {
            $end = DateTime::createFromFormat('Y-m-d', $endDate);
            if ($end === false) {
                $this->error('Invalid --end-date format; expected YYYY-MM-DD');
                return self::FAILURE;
            }
            $qb->where('completed_at', '<=', $end->format('Y-m-d 23:59:59'));
        }

        $items = $qb->with(['section', 'user'])->get();

        $rows = $items->map(fn (Item $item) => [
            $item->getId(),
            $item->getTask(),
            $item->getCompletedAt() ? $item->getCompletedAt()->format('Y-m-d') : 'unknown',
            $item->getSection()?->getName() ?? '',
            $item->getUser()?->getUsername() ?? '',
        ]);

        $this->table(['id', 'task', 'closed at', 'section', 'user'], $rows);

        return self::SUCCESS;
    }
}
