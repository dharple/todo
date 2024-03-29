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

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Describes an item or task that needs to be done.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @ORM\Table(name="item")
 */
class Item
{
    /**
     * Date completed
     *
     * @ORM\Column(type="datetime",nullable=true)
     * @var ?DateTime
     */
    protected ?DateTime $completed = null;

    /**
     * Date created
     *
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    protected DateTime $created;

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
     * Priority
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $priority;

    /**
     * The section that this item belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Section", inversedBy="items")
     * @var ?Section
     */
    protected ?Section $section = null;

    /**
     * Status
     *
     * @ORM\Column(type="string",length=20)
     * @var string
     */
    protected string $status;

    /**
     * Task
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected string $task;

    /**
     * The user that this item belongs to.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="items")
     * @var ?User
     */
    protected ?User $user = null;

    /**
     * Returns the completion stamp.
     */
    public function getCompleted(): ?DateTime
    {
        return $this->completed;
    }

    /**
     * Returns the creation stamp.
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * Returns the primary key.
     */
    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    /**
     * Returns the priority of the task.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Gets the section for this item.
     */
    public function getSection(): ?Section
    {
        return $this->section;
    }

    /**
     * Returns the status of the task.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Returns the task itself.
     */
    public function getTask(): string
    {
        return $this->task ?? '';
    }

    /**
     * Returns the user for this item.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets the completion stamp of the task.
     *
     * @param ?DateTime $completed The completion stamp.
     */
    public function setCompleted(?DateTime $completed): Item
    {
        $this->completed = $completed;
        return $this;
    }

    /**
     * Sets the creation stamp of the task.
     *
     * @param DateTime $created The creation stamp.
     */
    public function setCreated(DateTime $created): Item
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Sets the priority of the task.
     *
     * @param int $priority The priority of the task.
     */
    public function setPriority(int $priority): Item
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Sets the section for this item.
     *
     * @param Section|null $section The section to set.
     */
    public function setSection(?Section $section): Item
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Sets the status of the task.
     *
     * @param string $status The status of the task.
     */
    public function setStatus(string $status): Item
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Sets the task itself.
     *
     * @param string $task The task itself.
     */
    public function setTask(string $task): Item
    {
        $this->task = $task;
        return $this;
    }

    /**
     * Sets the user for this item.
     *
     * @param User|null $user The user for this item.
     */
    public function setUser(?User $user): Item
    {
        $this->user = $user;
        return $this;
    }
}
