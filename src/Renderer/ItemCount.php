<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Renderer;

/**
 * Tracks item counts.
 */
class ItemCount
{
    /**
     * The number of closed items.
     *
     * @var int
     */
    protected $closedCount = 0;

    /**
     * The number of open items.
     *
     * @var int
     */
    protected $openCount = 0;

    /**
     * Adds another count to this count.
     *
     * @param ItemCount $count The count to add.
     *
     * @return ItemCount
     */
    public function add(ItemCount $count)
    {
        $this->addClosed($count->getClosedCount());
        $this->addOpen($count->getOpenCount());
        return $this;
    }

    /**
     * Adds to the closed count.
     *
     * @param int $count The number of items to add.
     */
    public function addClosed(int $count = 1): ItemCount
    {
        $this->closedCount += $count;
        return $this;
    }

    /**
     * Adds to the open count.
     *
     * @param int $count The number of items to add.
     */
    public function addOpen(int $count = 1): ItemCount
    {
        $this->openCount += $count;
        return $this;
    }

    /**
     * Returns the total number of closed items.
     */
    public function getClosedCount(): int
    {
        return $this->closedCount;
    }

    /**
     * Returns the total number of open items.
     */
    public function getOpenCount(): int
    {
        return $this->openCount;
    }

    /**
     * Returns the total number of open AND closed items.
     */
    public function getTotalCount(): int
    {
        return $this->openCount + $this->closedCount;
    }

    /**
     * Sets the closed count.
     *
     * @param int $count Value to set.
     */
    public function setClosedCount(int $count): ItemCount
    {
        $this->closedCount = $count;
        return $this;
    }

    /**
     * Sets the open count.
     *
     * @param int $count Value to set.
     */
    public function setOpenCount(int $count): ItemCount
    {
        $this->openCount = $count;
        return $this;
    }
}
