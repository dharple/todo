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
     * Attempts to log in.
     *
     * @param string $username The user who is logging in.
     * @param string $password The password they are using.
     *
     * @return User
     *
     * @throws Exception
     */
    public static function login(string $username, string $password): User
    {
        $user = Helper::getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if ($user === null) {
            throw new Exception('Invalid username or password');
        }

        if (!static::checkPassword($user, $password)) {
            throw new Exception('Invalid username or password');
        }

        return $user;
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
