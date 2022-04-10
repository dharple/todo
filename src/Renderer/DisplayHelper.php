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

use App\Entity\Item;
use App\Entity\Section;
use App\Entity\User;
use App\Helper;
use Doctrine\ORM\EntityManager;

/**
 * Helper methods for renderer classes.
 */
class DisplayHelper
{
    /**
     * Returns the default section to use for editors.
     *
     * @param EntityManager $em   The entity manager to use.
     * @param User          $user The user to use.
     *
     * @return int
     */
    public static function getDefaultSectionId(EntityManager $em, User $user): int
    {
        $config = Helper::getDisplayConfig();
        if ($config->getFilterSection()) {
            return $config->getFilterSection();
        }

        $item = $em->getRepository(Item::class)
            ->findOneBy([
                'status' => ['Open', 'Closed'],
                'user' => $user,
            ], [
                'id' => 'DESC'
            ]);

        if ($item !== null) {
            return $item
                ->getSection()
                ->getId();
        }

        $sections = $em->getRepository(Section::class)
            ->findAll();
        $section = array_shift($sections);

        return $section->getId();
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
     * Returns fields for the deleted filter.
     *
     * @return string[]
     */
    public static function getFilterDeletedValues(): array
    {
        return [
            'all' => 'All',
            'recently' => 'Recently',
            'today' => 'Today',
            'none' => 'None'
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
