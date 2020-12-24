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
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="user")
     *
     * @var ArrayCollection
     */
    protected $items;

    /**
     * Password
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Section", mappedBy="user")
     *
     * @var ArrayCollection
     */
    protected $sections;

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

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        return $this->items;
    }

    /**
     * @return ArrayCollection
     */
    public function getSections(): ArrayCollection
    {
        return $this->sections;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $fullname
     * @return User
     */
    public function setFullname(string $fullname): User
    {
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $timezone
     * @return User
     */
    public function setTimezone(string $timezone): User
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }
}
