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
 * Command to purge closed items in a given timeframe.
 */
class ItemsClosedPurgeCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges closed items in a given timeframe';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'items:closed:purge
        {--year= : Purge items closed in this year (YYYY); cannot be combined with --start-date or --end-date}
        {--start-date= : Purge items closed on or after this date (YYYY-MM-DD)}
        {--end-date= : Purge items closed on or before this date (YYYY-MM-DD)}';

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

        $qb = Item::where('status', 'Closed');

        if ($startDate !== null) {
            $start = DateTime::createFromFormat('Y-m-d', $startDate);
            if ($start === false) {
                $this->error('Invalid --start-date format; expected YYYY-MM-DD');
                return self::FAILURE;
            }
            $qb->where('completed', '>=', $start->format('Y-m-d 00:00:00'));
        }

        if ($endDate !== null) {
            $end = DateTime::createFromFormat('Y-m-d', $endDate);
            if ($end === false) {
                $this->error('Invalid --end-date format; expected YYYY-MM-DD');
                return self::FAILURE;
            }
            $qb->where('completed', '<=', $end->format('Y-m-d 23:59:59'));
        }

        $qb->with(['user'])->get()->each(function (Item $item) {
            $this->line(sprintf('item purged: %d, %s, %s', $item->getId(), $item->getUser()?->getUsername() ?? '', $item->getTask()));
            $item->delete();
        });

        return self::SUCCESS;
    }
}
