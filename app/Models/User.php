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

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Describes a user of the application.
 *
 * @property int    $id
 * @property string $username
 * @property string $password
 * @property string $fullname
 * @property string $timezone
 */
#[Fillable([
    'username',
    'password',
    'fullname',
    'timezone',
])]
#[Hidden([
    'password',
])]
#[Table(name: 'user')]
class User extends Authenticatable
{
    /**
     * Whether the model uses created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Returns the login identifier field name.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    /**
     * Returns the full name of this user.
     *
     * @return string
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * Returns the hashed password for this user.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Returns the timezone for this user.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Returns the username for this user.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Returns items belonging to this user.
     *
     * @return HasMany<Item, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Returns sections belonging to this user.
     *
     * @return HasMany<Section, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Sets the full name of this user.
     *
     * @param string $fullname The full name.
     *
     * @return User
     */
    public function setFullname(string $fullname): User
    {
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * Sets the hashed password for this user.
     *
     * @param string $password The password, already hashed.
     *
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Sets the timezone for this user.
     *
     * @param string $timezone The timezone for this user.
     *
     * @return User
     */
    public function setTimezone(string $timezone): User
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Sets the username of this user.
     *
     * @param string $username The username.
     *
     * @return User
     */
    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }
}
