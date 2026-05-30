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

namespace App\Http\Controllers;

use App\Analytics\ItemStats;
use App\Models\Item;
use App\Models\User;
use App\Renderer\DisplayConfig;
use App\Renderer\DisplayHelper;
use App\Renderer\ListDisplay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles the main to-do list, item editing, bulk adding, and prioritization.
 */
class ItemController extends Controller
{
    /**
     * Session index for display config.
     *
     * @var string
     */
    protected const DISPLAY_CONFIG = 'display-config';

    /**
     * Displays the main to-do list.
     *
     * @param Request $request The current HTTP request.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        $errors = session('controller_errors', []);

        $config = $this->loadDisplayConfig($request);

        try {
            $config->processRequest($request);
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        $this->saveDisplayConfig($request, $config);

        $listDisplay = new ListDisplay($config, $user);

        $itemStats = new ItemStats($user);

        $listDisplay->setFooter(view('partials.index.summary', [
            'itemStats' => $itemStats,
        ])->render());

        $listOutput     = $listDisplay->getOutput();
        $itemCount      = $listDisplay->getOutputCount()->getTotalCount();
        $shownOpenCount = $listDisplay->getOutputCount()->getOpenCount();
        $totalOpenCount = Item::forUser($user)->open()->count();

        $hideFooter = (($shownOpenCount === $totalOpenCount) && ($itemStats->doneTotal() === 0));
        if ($hideFooter) {
            $listDisplay->setFooter('');
            $listOutput = $listDisplay->getOutput();
        }

        return view('index', [
            'chartData'             => $itemStats->getWeeklySummary(4),
            'config'                => $config,
            'countOpen'             => $totalOpenCount,
            'countShown'            => $shownOpenCount,
            'errors'                => $errors,
            'filterAgingValues'     => DisplayHelper::getFilterAgingValues(),
            'filterClosedValues'    => DisplayHelper::getFilterClosedValues(),
            'filterDeletedValues'   => DisplayHelper::getFilterDeletedValues(),
            'filterFreshnessValues' => DisplayHelper::getFilterFreshnessValues(),
            'filterPriorityValues'  => DisplayHelper::getFilterPriorityValues(),
            'hasItems'              => ($itemCount > 0),
            'hasSections'           => ($user->sections()->active()->count() > 0),
            'itemStats'             => $itemStats,
            'list'                  => $listOutput,
            'showAdvanced'          => ($config->getFilterClosed() !== 'none' || $config->getFilterDeleted() !== 'none'),
            'showPriorityValues'    => DisplayHelper::getShowPriorityValues(),
            'timezones'             => timezone_identifiers_list(\DateTimeZone::PER_COUNTRY, 'US'),
            'user'                  => $user,
        ]);
    }

    /**
     * Handles bulk item actions submitted from the main to-do list.
     *
     * @param Request $request The current HTTP request.
     */
    public function indexPost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $errors = [];

        $markDoneButton   = $request->input('markDoneButton');
        $editButton       = $request->input('editButton');
        $prioritizeButton = $request->input('prioritizeButton');
        $duplicateButton  = $request->input('duplicateButton');
        $itemIds          = (array) $request->input('itemIds', []);

        if (!empty($markDoneButton)) {
            try {
                foreach ($itemIds as $itemId) {
                    $item = Item::find($itemId);

                    if ($item === null) {
                        $errors[] = sprintf('Unable to load item #%s', $itemId);
                        continue;
                    }

                    $item
                        ->setStatus(($markDoneButton !== 'Delete') ? 'Closed' : 'Deleted')
                        ->setCompletedAt($markDoneButton === 'Mark Done Yesterday' ? 'yesterday 23:45' : 'now')
                        ->save();
                }
            } catch (\Exception $e) {
                $errors[] = sprintf('Failed to mark items done: %s', $e->getMessage());
            }
        } elseif (!empty($editButton)) {
            if (!empty($itemIds)) {
                return redirect()->route('item_edit', [
                    'op'  => 'edit',
                    'ids' => $itemIds,
                ]);
            }
            $errors[] = 'Please select one or more items to edit';
        } elseif (!empty($prioritizeButton)) {
            $params = !empty($itemIds) ? ['ids' => $itemIds] : [];
            return redirect()->route('item_prioritize', $params);
        } elseif (!empty($duplicateButton)) {
            try {
                foreach ($itemIds as $itemId) {
                    $item = Item::find($itemId);

                    if ($item === null) {
                        $errors[] = sprintf('Unable to load item #%s', $itemId);
                        continue;
                    }

                    $item->replicate()
                        ->setCompletedAt(null)
                        ->setStatus('Open')
                        ->save();
                }
            } catch (\Exception $e) {
                $errors[] = sprintf('Failed to duplicate items: %s', $e->getMessage());
            }
        }

        return redirect()->route('index')->with('controller_errors', $errors);
    }

    /**
     * Displays the bulk item add form.
     *
     * @param Request $request The current HTTP request.
     */
    public function itemBulkAdd(Request $request): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        $config   = $this->loadDisplayConfig($request);
        $selected = DisplayHelper::getDefaultSectionId($user, $config);

        $priorityLevels = DisplayHelper::getPriorityLevels();

        return view('item_bulk_add', [
            'errors'           => session('controller_errors', []),
            'priorityLevels'   => $priorityLevels,
            'sections'         => $user->sections()->active()->orderBy('name')->get(),
            'selectedPriority' => $priorityLevels['normal'],
            'selectedSection'  => $selected,
        ]);
    }

    /**
     * Handles the bulk item add form submission.
     *
     * @param Request $request The current HTTP request.
     */
    public function itemBulkAddPost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();
        $errors         = [];

        try {
            $tasks     = preg_split('/[\r\n]/', (string) $request->input('tasks', ''));
            $sectionId = (int) $request->input('section', 0);
            foreach ($tasks as $task) {
                $task = trim((string) $task);
                if ($task === '') {
                    continue;
                }

                $item = new Item();
                $item->section_id = $sectionId;
                $item->setPriority((int) $request->input('priority', $priorityLevels['normal']))
                    ->setStatus('Open')
                    ->setTask($task)
                    ->setUser($user)
                    ->save();
            }
        } catch (\Exception $e) {
            $errors[] = sprintf('Failed to add items: %s', $e->getMessage());
        }

        if (empty($errors) && $request->input('submitButton') === 'Do It') {
            return redirect()->route('index');
        }

        return redirect()->route('item_bulk_add')->with('controller_errors', $errors);
    }

    /**
     * Displays the item editor (create or edit one or more items).
     *
     * @param Request $request The current HTTP request.
     */
    public function itemEdit(Request $request): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();

        $sections        = [];
        $items           = [];
        $op              = $request->query('op', 'add');
        $sectionOverride = null;

        if ($op === 'edit') {
            $sections = $user->sections()->orderBy('name')->get();
            $ids      = (array) $request->query('ids', []);
            $items    = Item::whereIn('id', $ids)
                ->where('user_id', $user->id)
                ->get()
                ->all();
        } elseif ($op === 'add') {
            $sections = $user->sections()->active()->orderBy('name')->get();
            $items    = [
                (new Item())
                    ->setPriority($priorityLevels['normal'])
                    ->setStatus('Open'),
            ];
            $config          = $this->loadDisplayConfig($request);
            $sectionOverride = DisplayHelper::getDefaultSectionId($user, $config);
        }

        return view('item_edit', [
            'errors'          => session('controller_errors', []),
            'items'           => $items,
            'op'              => $op,
            'priorityLevels'  => $priorityLevels,
            'sections'        => $sections,
            'sectionOverride' => $sectionOverride,
            'statuses'        => ['Open', 'Closed', 'Deleted'],
        ]);
    }

    /**
     * Handles the item editor form submission (create or edit one or more items).
     *
     * @param Request $request The current HTTP request.
     *
     * @throws \Exception If an item cannot be found in the POST data.
     */
    public function itemEditPost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();
        $errors         = [];

        $taskData = $request->input('task');
        if (!is_array($taskData)) {
            $taskData = [];
        }

        try {
            $itemIds = array_keys($taskData);
            $items   = [];

            if (count($itemIds) === 1 && $itemIds[0] === 'new') {
                $items[] = (new Item())
                    ->setUser($user)
                    ->setStatus('Open');
            } else {
                $items = Item::whereIn('id', $itemIds)
                    ->where('user_id', $user->id)
                    ->get()
                    ->all();
            }

            $statusData    = (array) $request->input('status', []);
            $completedData = (array) $request->input('completed_at', []);
            $priorityData  = (array) $request->input('priority', []);
            $sectionData   = (array) $request->input('section', []);

            foreach ($items as $item) {
                $itemId = $item->getId() ?? 'new';

                if (!array_key_exists($itemId, $taskData)) {
                    throw new \Exception(sprintf('Could not find item #%s', $itemId));
                }

                if ($itemId !== 'new') {
                    $status = (string) ($statusData[$itemId] ?? 'Open');
                    $item->setStatus($status);
                    switch ($status) {
                        case 'Open':
                            $item->setCompletedAt(null);
                            break;

                        case 'Closed':
                        case 'Deleted':
                            $item->setCompletedAt($completedData[$itemId] ?? 'now');
                            break;
                    }
                }

                $item->section_id = (int) ($sectionData[$itemId] ?? 0);
                $item
                    ->setPriority((int) ($priorityData[$itemId] ?? $priorityLevels['normal']))
                    ->setTask((string) $taskData[$itemId])
                    ->save();
            }
        } catch (\Exception $e) {
            $errors[] = sprintf('Failed to edit items: %s', $e->getMessage());
        }

        if (empty($errors) && $request->input('submitButton') === 'Do It') {
            return redirect()->route('index');
        }

        $itemIds = array_keys($taskData);
        if (count($itemIds) === 1 && $itemIds[0] === 'new') {
            return redirect()->route('item_edit', ['op' => 'add'])->with('controller_errors', $errors);
        }

        return redirect()->route('item_edit', ['op' => 'edit', 'ids' => $itemIds])->with('controller_errors', $errors);
    }

    /**
     * Displays the item prioritization form.
     *
     * @param Request $request The current HTTP request.
     */
    public function itemPrioritize(Request $request): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        $errors = session('controller_errors', []);

        $config = clone $this->loadDisplayConfig($request);

        try {
            $config->setFilterClosed('none');
        } catch (\Exception) {
            $errors[] = 'Could not disable closed filter for this view.';
        }

        try {
            $config->setFilterDeleted('none');
        } catch (\Exception) {
            $errors[] = 'Could not disable deleted filter for this view.';
        }

        $config->setShowPriorityEditor(true);

        $ids = (array) $request->query('ids', []);
        if (count($ids) > 0) {
            $config->setFilterIds($ids);
        }

        $listDisplay = new ListDisplay($config, $user);
        $listOutput  = $listDisplay->getOutput();
        $itemCount   = $listDisplay->getOutputCount()->getOpenCount();

        return view('item_prioritize', [
            'errors'   => $errors,
            'hasItems' => ($itemCount > 0),
            'list'     => $listOutput,
        ]);
    }

    /**
     * Handles the item prioritization form submission.
     *
     * @param Request $request The current HTTP request.
     */
    public function itemPriorizePost(Request $request): RedirectResponse
    {
        $user = Auth::user();
        assert($user instanceof User);

        $priorityLevels = DisplayHelper::getPriorityLevels();
        $errors         = [];

        if ($request->input('submitButton') === 'Update') {
            try {
                foreach ((array) $request->input('itemPriority', []) as $itemId => $priority) {
                    $item = Item::find($itemId);

                    if ($item === null) {
                        $errors[] = sprintf('Unable to load item #%s', $itemId);
                        continue;
                    }

                    $priority = (int) $priority;
                    if ($priority < $priorityLevels['high']) {
                        $priority = $priorityLevels['high'];
                    } elseif ($priority > $priorityLevels['low']) {
                        $priority = $priorityLevels['low'];
                    }

                    $item->setPriority($priority)->save();
                }
            } catch (\Exception $e) {
                $errors[] = sprintf('Failed to change priorities: %s', $e->getMessage());
            }
        }

        if (empty($errors)) {
            return redirect()->route('index');
        }

        return redirect()->route('item_prioritize')->with('controller_errors', $errors);
    }

    /**
     * Loads the display config from the session.
     *
     * NOTE: this is dependent upon Laravel using JSON as the serialization
     * method for sessions.
     *
     * @param Request $request The current HTTP request.
     */
    protected function loadDisplayConfig(Request $request): DisplayConfig
    {
        if ($request->query->has('reset_display_settings')) {
            $request->session()->forget(static::DISPLAY_CONFIG);
        }

        return new DisplayConfig($request->session()->get(static::DISPLAY_CONFIG, []));
    }

    /**
     * Saves the display config to the session.
     *
     * @param Request       $request The current HTTP request.
     * @param DisplayConfig $config  The config to save.
     */
    protected function saveDisplayConfig(Request $request, DisplayConfig $config): void
    {
        $request->session()->put(static::DISPLAY_CONFIG, $config);
    }
}
