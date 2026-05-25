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

namespace Tests\Unit\Analytics;

use App\Analytics\ItemStats;
use App\Models\Item;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Tests for ItemStats::getAverage().
 */
class ItemStatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Active section belonging to the primary test user.
     *
     * @var Section
     */
    private Section $activeSection;

    /**
     * Primary test user.
     *
     * @var User
     */
    private User $user;

    /**
     * Sets up a fresh user and active section before each test, and clears the cache.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::create([
            'username' => 'testuser',
            'password' => 'hashed',
            'fullname' => 'Test User',
            'timezone' => 'UTC',
        ]);

        $this->activeSection = Section::create([
            'name'    => 'Active Section',
            'status'  => 'Active',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Resets Carbon's test time after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Creates a closed item for the primary test user.
     *
     * @param string $created   Creation datetime string.
     * @param string $completed Completion datetime string.
     *
     * @return Item
     */
    private function createClosedItem(string $created, string $completed): Item
    {
        return Item::create([
            'completed_at' => $completed,
            'created_at'   => $created,
            'priority'     => 1,
            'section_id'   => $this->activeSection->id,
            'status'       => 'Closed',
            'task'         => 'Test task',
            'user_id'      => $this->user->id,
        ]);
    }

    /**
     * Creates an open item for the primary test user in the given section.
     *
     * @param string  $created The creation datetime string.
     * @param Section $section The section to place the item in.
     *
     * @return Item
     */
    private function createOpenItem(string $created, Section $section): Item
    {
        return Item::create([
            'completed_at' => null,
            'created_at'   => $created,
            'priority'     => 1,
            'section_id'   => $section->id,
            'status'       => 'Open',
            'task'         => 'Test task',
            'user_id'      => $this->user->id,
        ]);
    }

    /**
     * Verifies that getAverage returns 0.0 when no items exist for the user.
     *
     * @return void
     */
    public function testGetAverageReturnsZeroWithNoItems(): void
    {
        $stats = new ItemStats($this->user);

        $this->assertSame(0.0, $stats->getAverage());
    }

    /**
     * Verifies that an item created and closed on the same day counts as 1 day.
     *
     * @return void
     */
    public function testGetAverageWithItemCreatedAndClosedOnSameDay(): void
    {
        $this->createClosedItem('2024-01-01 09:00:00', '2024-01-01 17:00:00');

        $stats = new ItemStats($this->user);

        // diffInDays = 0, +1 = 1
        $this->assertSame(1.0, $stats->getAverage());
    }

    /**
     * Verifies that an item open for exactly 10 days counts as 11 days.
     *
     * @return void
     */
    public function testGetAverageWithItemOpenForTenDays(): void
    {
        $this->createClosedItem('2024-01-01 12:00:00', '2024-01-11 12:00:00');

        $stats = new ItemStats($this->user);

        // diffInDays = 10, +1 = 11
        $this->assertSame(11.0, $stats->getAverage());
    }

    /**
     * Verifies the average across multiple closed items with differing ages.
     *
     * @return void
     */
    public function testGetAverageAcrossMultipleClosedItems(): void
    {
        // 0 days + 1 = 1
        $this->createClosedItem('2024-01-01 12:00:00', '2024-01-01 12:00:00');
        // 9 days + 1 = 10
        $this->createClosedItem('2024-01-01 12:00:00', '2024-01-10 12:00:00');

        $stats = new ItemStats($this->user);

        // (1 + 10) / 2 = 5.5
        $this->assertSame(5.5, $stats->getAverage());
    }

    /**
     * Verifies that an open item in an active section is included in the average.
     *
     * @return void
     */
    public function testGetAverageIncludesOpenItemsInActiveSections(): void
    {
        Carbon::setTestNow('2024-01-11 12:00:00');

        $this->createOpenItem('2024-01-01 12:00:00', $this->activeSection);

        $stats = new ItemStats($this->user);

        // 10 days + 1 = 11
        $this->assertSame(11.0, $stats->getAverage());
    }

    /**
     * Verifies that an open item in an inactive section is excluded from the average.
     *
     * @return void
     */
    public function testGetAverageExcludesOpenItemsInInactiveSections(): void
    {
        Carbon::setTestNow('2024-01-11 12:00:00');

        $inactiveSection = Section::create([
            'name'    => 'Inactive Section',
            'status'  => 'Inactive',
            'user_id' => $this->user->id,
        ]);

        $this->createOpenItem('2024-01-01 12:00:00', $inactiveSection);

        $stats = new ItemStats($this->user);

        $this->assertSame(0.0, $stats->getAverage());
    }

    /**
     * Verifies that deleted items are excluded from the average.
     *
     * @return void
     */
    public function testGetAverageExcludesDeletedItems(): void
    {
        Item::create([
            'completed_at' => null,
            'created_at'   => '2024-01-01 12:00:00',
            'priority'     => 1,
            'section_id'   => $this->activeSection->id,
            'status'       => 'Deleted',
            'task'         => 'Deleted task',
            'user_id'      => $this->user->id,
        ]);

        $stats = new ItemStats($this->user);

        $this->assertSame(0.0, $stats->getAverage());
    }

    /**
     * Verifies that items belonging to other users are excluded from the average.
     *
     * @return void
     */
    public function testGetAverageExcludesOtherUsersItems(): void
    {
        $otherUser = User::create([
            'username' => 'otheruser',
            'password' => 'hashed',
            'fullname' => 'Other User',
            'timezone' => 'UTC',
        ]);

        $otherSection = Section::create([
            'name'    => 'Other Section',
            'status'  => 'Active',
            'user_id' => $otherUser->id,
        ]);

        // Other user's item with a large value — should not affect the primary user's average
        Item::create([
            'completed_at' => '2024-12-31 12:00:00',
            'created_at'   => '2024-01-01 12:00:00',
            'priority'     => 1,
            'section_id'   => $otherSection->id,
            'status'       => 'Closed',
            'task'         => 'Other task',
            'user_id'      => $otherUser->id,
        ]);

        $stats = new ItemStats($this->user);

        $this->assertSame(0.0, $stats->getAverage());
    }

    /**
     * Verifies the average when a mix of closed and active open items are present.
     *
     * @return void
     */
    public function testGetAverageWithMixedClosedAndOpenItems(): void
    {
        Carbon::setTestNow('2024-01-11 12:00:00');

        // Closed: 4 days + 1 = 5
        $this->createClosedItem('2024-01-01 12:00:00', '2024-01-05 12:00:00');

        // Open in active section; now - created = 10 days + 1 = 11
        $this->createOpenItem('2024-01-01 12:00:00', $this->activeSection);

        $stats = new ItemStats($this->user);

        // (5 + 11) / 2 = 8.0
        $this->assertSame(8.0, $stats->getAverage());
    }
}
