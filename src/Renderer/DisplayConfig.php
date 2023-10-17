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

use Exception;

/**
 * Holds the current display configuration.
 */
class DisplayConfig
{
    /**
     * These members persist between page loads.
     *
     * @var string[]
     */
    protected const SAVED_VALUES =
    [
        'filterAging',
        'filterClosed',
        'filterDeleted',
        'filterFreshness',
        'filterPriority',
        'filterSection',
        'showInactive',
        'showPriority',
    ];

    /**
     * Filters based on aging.
     *
     * Valid values:
     *  - 'all'
     *  - a string containing an integer greater than zero
     *
     * @var string
     */
    protected string $filterAging = 'all';

    /**
     * Filters based on whether or not the item is closed.
     *
     * Valid values:
     *  - 'none'
     *  - 'recently'
     *  - 'today'
     *  - 'all'
     *
     * @var string
     */
    protected string $filterClosed = 'none';

    /**
     * Filters based on whether or not the item is deleted.
     *
     * Valid values:
     *  - 'none'
     *  - 'recently'
     *  - 'today'
     *  - 'all'
     *
     * @var string
     */
    protected string $filterDeleted = 'none';

    /**
     * Filters based on freshness.
     *
     * Valid values:
     *  - 'all'
     *  - 'today'
     *  - 'recently'
     *  - 'week'
     *  - 'month'
     *
     * @var string
     */
    protected string $filterFreshness = 'all';

    /**
     * Filters based on ID.
     *
     * Does not persist between page loads.
     *
     * @var int[]
     */
    protected array $filterIds = [];

    /**
     * Filters based on priority.
     *
     * Valid values:
     *  - 'all'
     *  - 'high'
     *  - 'normal'
     *  - 'low'
     *
     * @var string
     */
    protected string $filterPriority = 'all';

    /**
     * Filters based on section ID.
     *
     * @var int
     */
    protected int $filterSection = 0;

    /**
     * Whether or not to show inactive sections.
     *
     * @var bool
     */
    protected bool $showInactive = false;

    /**
     * Whether or not to show priority values in the list.
     *
     * Valid values:
     *  - 'y'
     *  - 'n'
     *  - 'above_normal'
     *
     * @var string
     */
    protected string $showPriority = 'n';

    /**
     * Whether or not to show the priority editor.
     *
     * Does not persist between page loads.
     *
     * @var bool
     */
    protected bool $showPriorityEditor = false;

    /**
     * Only save persistent values on sleep.
     *
     * @return array
     */
    public function __sleep(): array
    {
        return static::SAVED_VALUES;
    }

    /**
     * Returns the current aging filter.
     */
    public function getFilterAging(): string
    {
        return $this->filterAging;
    }

    /**
     * Returns the current closed filter.
     */
    public function getFilterClosed(): string
    {
        return $this->filterClosed;
    }

    /**
     * Returns the current deleted filter.
     */
    public function getFilterDeleted(): string
    {
        return $this->filterDeleted;
    }

    /**
     * Returns the current freshness filter.
     */
    public function getFilterFreshness(): string
    {
        return $this->filterFreshness;
    }
    /**
     * Returns the current filter for IDs.
     */
    public function getFilterIds(): array
    {
        return $this->filterIds;
    }

    /**
     * Returns the current priority filter.
     */
    public function getFilterPriority(): string
    {
        return $this->filterPriority;
    }

    /**
     * Returns the current filter for section.
     */
    public function getFilterSection(): int
    {
        return $this->filterSection;
    }

    /**
     * Returns whether or not to show inactive sections.
     */
    public function getShowInactive(): bool
    {
        return $this->showInactive;
    }

    /**
     * Returns whether to show priority values.
     */
    public function getShowPriority(): string
    {
        return $this->showPriority;
    }

    /**
     * Returns whether or not to show the priority editor.
     */
    public function getShowPriorityEditor(): bool
    {
        return $this->showPriorityEditor;
    }

    /**
     * Turns a string into a bool; leaves booleans alone.
     *
     * @param bool|string $in The value to review.
     */
    protected function processBoolean(bool|string $in): bool
    {
        return is_bool($in) ? $in : ($in === 'y');
    }

    /**
     * Processes any request variables.
     *
     * @throws Exception
     */
    public function processRequest(): DisplayConfig
    {
        foreach (static::SAVED_VALUES as $field) {
            $requestVar = strtolower(preg_replace('/[A-Z]/', '_\0', $field));

            if (isset($_REQUEST[$requestVar])) {
                $setMethod = 'set' . ucfirst($field);
                $this->$setMethod($_REQUEST[$requestVar]);
            }
        }

        return $this;
    }

    /**
     * Sets the aging filter.
     *
     * @param string $filterAging Aging filter.
     *
     * @throws Exception
     */
    public function setFilterAging(string $filterAging): DisplayConfig
    {
        $valid = DisplayHelper::getFilterAgingValues();
        if (!array_key_exists($filterAging, $valid)) {
            throw new Exception('Invalid value for aging filter');
        }
        $this->filterAging = $filterAging;
        return $this;
    }

    /**
     * Sets the closed filter.
     *
     * @param string $filterClosed Closed filter.
     *
     * @throws Exception
     */
    public function setFilterClosed(string $filterClosed): DisplayConfig
    {
        $valid = DisplayHelper::getFilterClosedValues();
        if (!array_key_exists($filterClosed, $valid)) {
            throw new Exception('Invalid value for closed filter');
        }
        $this->filterClosed = $filterClosed;
        return $this;
    }

    /**
     * Sets the deleted filter.
     *
     * @param string $filterDeleted Deleted filter.
     *
     * @throws Exception
     */
    public function setFilterDeleted(string $filterDeleted): DisplayConfig
    {
        $valid = DisplayHelper::getFilterDeletedValues();
        if (!array_key_exists($filterDeleted, $valid)) {
            throw new Exception('Invalid value for deleted filter');
        }
        $this->filterDeleted = $filterDeleted;
        return $this;
    }

    /**
     * Sets the freshness filter.
     *
     * @param string $filterFreshness Freshness filter.
     *
     * @throws Exception
     */
    public function setFilterFreshness(string $filterFreshness): DisplayConfig
    {
        $valid = DisplayHelper::getFilterFreshnessValues();
        if (!array_key_exists($filterFreshness, $valid)) {
            throw new Exception('Invalid value for freshness filter');
        }
        $this->filterFreshness = $filterFreshness;
        return $this;
    }

    /**
     * Sets the ID filter.
     *
     * @param int[] $filterIds IDs to filter on.
     */
    public function setFilterIds(array $filterIds): DisplayConfig
    {
        $this->filterIds = $filterIds;
        return $this;
    }

    /**
     * Sets the priority filter.
     *
     * @param string $filterPriority Priority filter.
     *
     * @throws Exception
     */
    public function setFilterPriority(string $filterPriority): DisplayConfig
    {
        $valid = DisplayHelper::getFilterPriorityValues();
        if (!array_key_exists($filterPriority, $valid)) {
            throw new Exception('Invalid value for priority filter');
        }
        $this->filterPriority = $filterPriority;
        return $this;
    }

    /**
     * If set, only this section is shown.
     *
     * @param int $filterSection A section ID, or 0.
     */
    public function setFilterSection(int $filterSection): DisplayConfig
    {
        $this->filterSection = $filterSection;
        return $this;
    }

    /**
     * Whether or not to show inactive sections.
     *
     * @param bool|string $showInactive True or False.
     */
    public function setShowInactive(bool|string $showInactive): DisplayConfig
    {
        $this->showInactive = $this->processBoolean($showInactive);
        return $this;
    }

    /**
     * Whether or not to show the priority in the list display.
     *
     * @param string $showPriority Multiple values.
     *
     * @throws Exception
     */
    public function setShowPriority(string $showPriority): DisplayConfig
    {
        $valid = DisplayHelper::getShowPriorityValues();
        if (!array_key_exists($showPriority, $valid)) {
            throw new Exception('Invalid value for priority display');
        }
        $this->showPriority = $showPriority;
        return $this;
    }

    /**
     * Whether or not to show the priority editor.
     *
     * @param bool|string $showPriorityEditor True or False.
     */
    public function setShowPriorityEditor(bool|string $showPriorityEditor): DisplayConfig
    {
        $this->showPriorityEditor = $this->processBoolean($showPriorityEditor);
        return $this;
    }
}
