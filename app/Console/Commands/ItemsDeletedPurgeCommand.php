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
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Command to purge deleted items older than 30 days.
 */
#[Description('Purges deleted items older than 30 days')]
#[Signature('items:deleted:purge')]
class ItemsDeletedPurgeCommand extends Command
{
    /**
     * How long deleted items are retained, in days.
     *
     * @var int
     */
    protected const RETAIN_DAYS = 30;

    /**
     * How long deleted items are retained if their completed stamp is NULL, in days.
     *
     * @var int
     */
    protected const RETAIN_DAYS_CORRECTION = 120;

    /**
     * Executes the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $stamp    = (new Carbon())->subDays(static::RETAIN_DAYS)->format('Y-m-d');
        $altStamp = (new Carbon())->subDays(static::RETAIN_DAYS_CORRECTION)->format('Y-m-d');

        Item::where('status', 'Deleted')
            ->where(function ($q) use ($stamp, $altStamp) {
                $q->where('completed_at', '<=', $stamp)
                    ->orWhere(function ($q2) use ($altStamp) {
                        $q2->whereNull('completed_at')->where('created_at', '<=', $altStamp);
                    });
            })
            ->with(['user'])
            ->get()
            ->each(function (Item $item) {
                $this->line(sprintf('item purged: %d, %s, %s', $item->getId(), $item->getUser()?->getUsername() ?? '', $item->getTask()));
                $item->delete();
            });

        return self::SUCCESS;
    }
}
