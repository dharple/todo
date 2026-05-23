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

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Describes a user of the application.
 */
#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Full name
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected string $fullname;

    /**
     * Primary key
     *
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected int $id;

    /**
     * The items associated with this user.
     *
     * This is an instance of a Collection that also implements Selectable.
     *
     * @var AbstractLazyCollection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Item', mappedBy: 'user')]
    protected $items;

    /**
     * Password
     *
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected string $password;

    /**
     * The sections associated with this user.
     *
     * This is an instance of a Collection that also implements Selectable.
     *
     * @var AbstractLazyCollection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Section', mappedBy: 'user')]
    protected $sections;

    /**
     * Timezone
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 128)]
    protected string $timezone;

    /**
     * Username
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 32, unique: true)]
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
     * Removes sensitive data from the user.
     *
     * @return void
     */
    public function eraseCredentials(): void
    {
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
     * @return AbstractLazyCollection|ArrayCollection
     */
    public function getItems(): AbstractLazyCollection|ArrayCollection
    {
        return $this->items;
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
     * Returns the roles granted to this user.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * Returns the sections associated with this user.
     *
     * @return AbstractLazyCollection|ArrayCollection
     */
    public function getSections(): AbstractLazyCollection|ArrayCollection
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
     * Returns the identifier for this user (the username).
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->getUsername();
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
