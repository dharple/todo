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
     *
     * @return string
     */
    public function getFilterAging(): string
    {
        return $this->filterAging;
    }

    /**
     * Returns the current closed filter.
     *
     * @return string
     */
    public function getFilterClosed(): string
    {
        return $this->filterClosed;
    }

    /**
     * Returns the current filter for IDs.
     *
     * @return array
     */
    public function getFilterIds(): array
    {
        return $this->filterIds;
    }

    /**
     * Returns the current priority filter.
     *
     * @return string
     */
    public function getFilterPriority(): string
    {
        return $this->filterPriority;
    }

    /**
     * Returns the current filter for section.
     *
     * @return int
     */
    public function getFilterSection(): int
    {
        return $this->filterSection;
    }

    /**
     * Returns whether or not to show inactive sections.
     *
     * @return bool
     */
    public function getShowInactive(): bool
    {
        return $this->showInactive;
    }

    /**
     * Returns whether to show priority values.
     *
     * @return string
     */
    public function getShowPriority(): string
    {
        return $this->showPriority;
    }

    /**
     * Returns whether or not to show the priority editor.
     *
     * @return bool
     */
    public function getShowPriorityEditor(): bool
    {
        return $this->showPriorityEditor;
    }

    /**
     * Turns a string into a bool; leaves booleans alone.
     *
     * @param bool|string $in The value to review.
     *
     * @return bool
     */
    protected function processBoolean($in): bool
    {
        return is_bool($in) ? $in : ($in === 'y');
    }

    /**
     * Processes any request variables.
     *
     * @throws Exception
     *
     * @return DisplayConfig
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
     * @return DisplayConfig
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
     * @return DisplayConfig
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
     * Sets the ID filter.
     *
     * @param int[] $filterIds IDs to filter on.
     *
     * @return DisplayConfig
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
     * @return DisplayConfig
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
     *
     * @return DisplayConfig
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
     *
     * @return DisplayConfig
     */
    public function setShowInactive($showInactive): DisplayConfig
    {
        $this->showInactive = $this->processBoolean($showInactive);
        return $this;
    }

    /**
     * Whether or not to show the priority in the list display.
     *
     * @param string $showPriority Multiple values.
     *
     * @return DisplayConfig
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
     *
     * @return DisplayConfig
     */
    public function setShowPriorityEditor($showPriorityEditor): DisplayConfig
    {
        $this->showPriorityEditor = $this->processBoolean($showPriorityEditor);
        return $this;
    }
}
