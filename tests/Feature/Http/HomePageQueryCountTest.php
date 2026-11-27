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

namespace Tests\Feature\Http;

use App\Models\Item;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The home page should not issue more queries as sections and items grow.
 */
class HomePageQueryCountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The authenticated user for requests.
     */
    private User $user;

    /**
     * Creates a user.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'fullname' => 'Test User',
            'password' => 'hashed',
            'timezone' => 'UTC',
            'username' => 'testuser',
        ]);
    }

    /**
     * Adds sections, each with open and closed items.
     *
     * @param int $sections  How many sections to add.
     * @param int $itemsEach How many open and closed items to add per section.
     */
    private function addSections(int $sections, int $itemsEach): void
    {
        for ($i = 0; $i < $sections; $i++) {
            $section = Section::create([
                'name'    => 'Section ' . uniqid(),
                'status'  => 'Active',
                'user_id' => $this->user->id,
            ]);

            for ($j = 0; $j < $itemsEach; $j++) {
                foreach (['Open', 'Closed'] as $status) {
                    Item::create([
                        'completed_at' => $status === 'Closed' ? Carbon::now()->subDays($j) : null,
                        'created_at'   => Carbon::now()->subDays($j + 5),
                        'priority'     => 5,
                        'section_id'   => $section->id,
                        'status'       => $status,
                        'task'         => 'Task ' . uniqid(),
                        'user_id'      => $this->user->id,
                    ]);
                }
            }
        }
    }

    /**
     * Counts the queries issued by a GET request.
     *
     * @param string $url The URL to request.
     */
    private function countQueries(string $url): int
    {
        Cache::flush();
        $this->flushSession();
        $this->actingAs($this->user);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queries;
    }

    /**
     * The query count stays flat as sections and items grow.
     */
    public function testQueryCountIsConstant(): void
    {
        $this->addSections(2, 1);
        $baseline = $this->countQueries('/');

        $this->addSections(5, 3);
        $this->assertSame($baseline, $this->countQueries('/'));
    }

    /**
     * Every open item is shown under its own section.
     */
    public function testItemsAppearUnderTheirSections(): void
    {
        $this->addSections(3, 2);

        $this->actingAs($this->user);
        $response = $this->get('/')->assertOk();

        foreach (Section::with('items')->get() as $section) {
            foreach ($section->items->where('status', 'Open') as $item) {
                $response->assertSeeInOrder([$section->name, $item->task]);
            }
        }
    }
}
