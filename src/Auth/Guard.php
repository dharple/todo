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
     */
    public static function checkPassword(User $user, string $password): bool
    {
        return password_verify($password, $user->getPassword());
    }

    /**
     * Gets the current logged in user.
     *
     * @throws Exception
     */
    public static function getUser(): User
    {
        static $user = null;

        if ($user === null) {
            if (empty($_SESSION['userId'])) {
                throw new Exception('Unable to find user.');
            }

            $user = Helper::getEntityManager()->find(User::class, $_SESSION['userId']);
            if ($user === null) {
                throw new Exception('Unable to find user.');
            }
        }

        return $user;
    }

    /**
     * Attempts to log in.
     *
     * @param string $username The user who is logging in.
     * @param string $password The password they are using.
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

        $_SESSION['userId'] = $user->getId();

        return $user;
    }

    /**
     * Sets a user's password.
     *
     * @param User   $user     The user to change.
     * @param string $password The password to set.
     */
    public static function setPassword(User $user, string $password): void
    {
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
    }
}
