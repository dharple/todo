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

namespace App\Renderer;

use App\Models\Item;
use App\Models\Section;
use App\Models\User;

/**
 * Helper methods for renderer classes.
 */
class DisplayHelper
{
    /**
     * Returns the default section to use for editors.
     *
     * @param User          $user   The user to use.
     * @param DisplayConfig $config Display config; falls back to the most recently edited item.
     */
    public static function getDefaultSectionId(User $user, DisplayConfig $config): int
    {
        if ($config->getFilterSection()) {
            return $config->getFilterSection();
        }

        $item = Item::where('user_id', $user->id)
            ->whereIn('status', ['Open', 'Closed'])
            ->orderBy('id', 'desc')
            ->first();

        if ($item !== null) {
            return (int) $item->section_id;
        }

        $section = Section::where('user_id', $user->id)->first();

        return $section ? (int) $section->id : 0;
    }

    /**
     * Returns fields for the aging filter.
     *
     * @return string[]
     */
    public static function getFilterAgingValues(): array
    {
        return [
            'all' => 'All',
            '30'  => '30',
            '60'  => '60',
            '90'  => '90',
            '365' => '365',
        ];
    }

    /**
     * Returns fields for the closed filter.
     *
     * @return string[]
     */
    public static function getFilterClosedValues(): array
    {
        return [
            'all'      => 'All',
            'recently' => 'Recently',
            'today'    => 'Today',
            'none'     => 'None',
        ];
    }

    /**
     * Returns fields for the deleted filter.
     *
     * @return string[]
     */
    public static function getFilterDeletedValues(): array
    {
        return [
            'all'      => 'All',
            'recently' => 'Recently',
            'today'    => 'Today',
            'none'     => 'None',
        ];
    }

    /**
     * Returns fields for the freshness filter.
     *
     * @return string[]
     */
    public static function getFilterFreshnessValues(): array
    {
        return [
            'all'      => 'All',
            'today'    => 'Today',
            'recently' => 'Recently',
            'week'     => 'This Week',
            'month'    => 'This Month',
        ];
    }

    /**
     * Returns fields for the priority filter.
     *
     * @return string[]
     */
    public static function getFilterPriorityValues(): array
    {
        $priorityLevels = static::getPriorityLevels();
        return [
            'all'    => 'All',
            'high'   => '' . $priorityLevels['high'],
            'normal' => $priorityLevels['high'] . '-' . $priorityLevels['normal'],
            'low'    => $priorityLevels['high'] . '-' . $priorityLevels['low'],
        ];
    }

    /**
     * Returns the set of available priorities.
     *
     * @return int[]
     */
    public static function getPriorityLevels(): array
    {
        $priorityLevels = [
            'high' => 1,
            'low'  => 10,
        ];

        $priorityLevels['normal'] = intval((($priorityLevels['low'] - $priorityLevels['high']) / 2) + $priorityLevels['high']);

        return $priorityLevels;
    }

    /**
     * Returns fields for the show priority display.
     *
     * @return string[]
     */
    public static function getShowPriorityValues(): array
    {
        return [
            'y'            => 'All',
            'above_normal' => 'Above Normal',
            'n'            => 'None',
        ];
    }
}
