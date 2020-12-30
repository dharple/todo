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
    public static function getAgingFilterValues(): array
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
    public static function getClosedFilterValues(): array
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
    public static function getPriorityFilterValues(): array
    {
        $todoPriority = static::getTodoPriority();
        return [
            'all' => 'All',
            'high' => '' . $todoPriority['high'],
            'normal' => $todoPriority['high'] . '-' . $todoPriority['normal'],
            'low' => $todoPriority['high'] . '-' . $todoPriority['low']
        ];
    }

    /**
     * Returns fields for the show priority display.
     *
     * @return string[]
     */
    public static function getShowPriorityDisplay(): array
    {
        return [
            'y' => 'All',
            'above_normal' => 'Above Normal',
            'n' => 'None'
        ];
    }

    /**
     * Returns the set of available priorities.
     *
     * @return int[]
     */
    public static function getTodoPriority(): array
    {
        $todoPriority = [
            'high' => 1,
            'low' => 10,
        ];

        $todoPriority['normal'] = intval((($todoPriority['low'] - $todoPriority['high']) / 2) + $todoPriority['high']);

        return $todoPriority;
    }
}
