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
 * Helper methods for renderer classes.
 */
class DisplayHelper
{

    /**
     * Returns fields for the aging filter.
     *
     * @return string[]
     */
    public static function getFilterAgingValues(): array
    {
        return [
            'all' => 'All',
            '30' => '30',
            '60' => '60',
            '90' => '90',
            '365' => '365'
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
            'all' => 'All',
            'recently' => 'Recently',
            'today' => 'Today',
            'none' => 'None'
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
            'all' => 'All',
            'high' => '' . $priorityLevels['high'],
            'normal' => $priorityLevels['high'] . '-' . $priorityLevels['normal'],
            'low' => $priorityLevels['high'] . '-' . $priorityLevels['low']
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
            'low' => 10,
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
            'y' => 'All',
            'above_normal' => 'Above Normal',
            'n' => 'None'
        ];
    }
}
