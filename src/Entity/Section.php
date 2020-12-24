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
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="section")
     *
     * @var ArrayCollection
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
     * @ORM\Column(type="string")
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
    protected $user;

    public function __construct()
    {
        $this->items = new ArrayCollection();
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param string $name
     * @return Section
     */
    public function setName(string $name): Section
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $status
     * @return Section
     */
    public function setStatus(string $status): Section
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param User|null $user
     * @return Section
     */
    public function setUser(?User $user): Section
    {
        $this->user = $user;
        return $this;
    }
}
