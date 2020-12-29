<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Renderer;

class DisplayConfig
{

    protected string $filterAging = 'all';

    protected string $filterClosed = 'none';

    protected string $filterPriority = 'all';

    protected array $ids = [];

    protected array $internalPriorityLevels = [];

    protected string $sectionLink = '';

    protected string $showInactive = 'n';

    protected string $showPriority = 'n';

    protected string $showPriorityEditor = 'n';

    protected int $showSection = 0;

    /**
     * @return string
     */
    public function getFilterAging(): string
    {
        return $this->filterAging;
    }

    /**
     * @return string
     */
    public function getFilterClosed(): string
    {
        return $this->filterClosed;
    }

    /**
     * @return string
     */
    public function getFilterPriority(): string
    {
        return $this->filterPriority;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @return array
     */
    public function getInternalPriorityLevels(): array
    {
        return $this->internalPriorityLevels;
    }

    /**
     * @return string
     */
    public function getSectionLink(): string
    {
        return $this->sectionLink;
    }

    /**
     * @return string
     */
    public function getShowInactive(): string
    {
        return $this->showInactive;
    }

    /**
     * @return string
     */
    public function getShowPriority(): string
    {
        return $this->showPriority;
    }

    /**
     * @return string
     */
    public function getShowPriorityEditor(): string
    {
        return $this->showPriorityEditor;
    }

    /**
     * @return int
     */
    public function getShowSection(): int
    {
        return $this->showSection;
    }

    /**
     * @param string $filterAging
     *
     * @return DisplayConfig
     */
    public function setFilterAging(string $filterAging): DisplayConfig
    {
        $this->filterAging = $filterAging;
        return $this;
    }

    /**
     * @param string $filterClosed
     *
     * @return DisplayConfig
     */
    public function setFilterClosed(string $filterClosed): DisplayConfig
    {
        $this->filterClosed = $filterClosed;
        return $this;
    }

    /**
     * @param string $filterPriority
     *
     * @return DisplayConfig
     */
    public function setFilterPriority(string $filterPriority): DisplayConfig
    {
        $this->filterPriority = $filterPriority;
        return $this;
    }

    /**
     * @param array $ids
     *
     * @return DisplayConfig
     */
    public function setIds(array $ids): DisplayConfig
    {
        $this->ids = $ids;
        return $this;
    }

    /**
     * @param array $internalPriorityLevels
     *
     * @return DisplayConfig
     */
    public function setInternalPriorityLevels(array $internalPriorityLevels): DisplayConfig
    {
        $this->internalPriorityLevels = $internalPriorityLevels;
        return $this;
    }

    /**
     * @param string $sectionLink
     *
     * @return DisplayConfig
     */
    public function setSectionLink(string $sectionLink): DisplayConfig
    {
        $this->sectionLink = $sectionLink;
        return $this;
    }

    /**
     * @param string $showInactive
     *
     * @return DisplayConfig
     */
    public function setShowInactive(string $showInactive): DisplayConfig
    {
        $this->showInactive = $showInactive;
        return $this;
    }

    /**
     * @param string $showPriority
     *
     * @return DisplayConfig
     */
    public function setShowPriority(string $showPriority): DisplayConfig
    {
        $this->showPriority = $showPriority;
        return $this;
    }

    /**
     * @param string $showPriorityEditor
     *
     * @return DisplayConfig
     */
    public function setShowPriorityEditor(string $showPriorityEditor): DisplayConfig
    {
        $this->showPriorityEditor = $showPriorityEditor;
        return $this;
    }

    /**
     * @param int $showSection
     *
     * @return DisplayConfig
     */
    public function setShowSection(int $showSection): DisplayConfig
    {
        $this->showSection = $showSection;
        return $this;
    }
}
