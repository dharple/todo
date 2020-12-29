<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Describes an item or task that needs to be done.
 *
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * Full name
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $fullname;

    /**
     * Primary key
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var int
     */
    protected int $id;

    /**
     * The items associated with this user.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="user")
     *
     * @var Collection
     */
    protected Collection $items;

    /**
     * Password
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $password;

    /**
     * The sections associated with this user.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Section", mappedBy="user")
     *
     * @var Collection
     */
    protected Collection $sections;

    /**
     * Timezone
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $timezone;

    /**
     * Username
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $username;

    /**
     * Constructs a new user.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->sections = new ArrayCollection();
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
     * Returns the ID of this user.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the items associated with this user.
     *
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Returns the sections associated with this user.
     *
     * @return Collection
     */
    public function getSections(): Collection
    {
        return $this->sections;
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
     * Sets the password for this user.
     *
     * @param string $password The password.
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
