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

/**
 * Describes a section that tasks get grouped into.
 *
 * @ORM\Entity()
 * @ORM\Table(name="section")
 */
class Section
{
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
     * The items associated with this section.
     *
     * This is an instance of a Collection that also implements Selectable.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="section")
     *
     * @var AbstractLazyCollection|ArrayCollection
     */
    protected $items;

    /**
     * Name
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $name;

    /**
     * Status
     *
     * @ORM\Column(type="string",length=20)
     * @var string
     */
    protected string $status;

    /**
     * The user that this item belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sections")
     *
     * @var ?User
     */
    protected ?User $user = null;

    /**
     * Constructs a new section.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * Returns the ID of this section.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the items that belong to this section.
     *
     * @return AbstractLazyCollection|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns the name of this section.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the status of this section.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Returns the user for this section.
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets the name of this section.
     *
     * @param string $name The name of this section.
     *
     * @return Section
     */
    public function setName(string $name): Section
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the status for this section.
     *
     * @param string $status The status for this section.
     *
     * @return Section
     */
    public function setStatus(string $status): Section
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Sets the user for this section.
     *
     * @param User|null $user The user to set.
     *
     * @return Section
     */
    public function setUser(?User $user): Section
    {
        $this->user = $user;
        return $this;
    }
}
