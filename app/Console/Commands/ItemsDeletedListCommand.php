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
use Illuminate\Console\Command;

/**
 * Command to list deleted items.
 */
class ItemsDeletedListCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List deleted items';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'items:deleted:list';

    /**
     * Executes the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $items = Item::where('status', 'Deleted')
            ->orderBy('completed', 'desc')
            ->with(['section', 'user'])
            ->get();

        $rows = $items->map(fn (Item $item) => [
            $item->getId(),
            $item->getTask(),
            $item->getCompleted() ? $item->getCompleted()->format('Y-m-d') : 'unknown',
            $item->getSection()?->getName() ?? '',
            $item->getUser()?->getUsername() ?? '',
        ]);

        $this->table(['id', 'task', 'deleted at', 'section', 'user'], $rows);

        return self::SUCCESS;
    }
}
