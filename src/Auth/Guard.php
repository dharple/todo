<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Auth;

use App\Entity\User;
use App\Helper;
use Exception;

/**
 * Authentication guard.
 */
class Guard
{
    /**
     * Confirms a password.
     *
     * @param User   $user     The user to confirm.
     * @param string $password The password to confirm.
     *
     * @return bool
     */
    public static function checkPassword(User $user, string $password): bool
    {
        return password_verify($password, $user->getPassword());
    }

    /**
     * Sets a user's password.
     *
     * @param User   $user     The user to change.
     * @param string $password The password to set.
     *
     * @return void
     */
    public static function setPassword(User $user, string $password): void
    {
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
    }
}
