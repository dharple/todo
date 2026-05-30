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
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Command to list deleted items.
 */
#[Description('List deleted items')]
#[Signature('items:deleted:list')]
class ItemsDeletedListCommand extends Command
{
    /**
     * Executes the command.
     *
     * @return int
     */
    public function handle(): int
    {
        $items = Item::where('status', 'Deleted')
            ->orderBy('completed_at', 'desc')
            ->with(['section', 'user'])
            ->get();

        $rows = $items->map(fn (Item $item) => [
            $item->getId(),
            $item->getTask(),
            $item->getCompletedAt() ? $item->getCompletedAt()->format('Y-m-d') : 'unknown',
            $item->getSection()?->getName() ?? '',
            $item->getUser()?->getUsername() ?? '',
        ]);

        $this->table(['id', 'task', 'deleted at', 'section', 'user'], $rows);

        return self::SUCCESS;
    }
}
