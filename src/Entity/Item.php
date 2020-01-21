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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Item extends AbstractEntity
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $completed_at;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Section", inversedBy="items")
     */
    protected $section;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $task;

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completed_at;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setCompletedAt(\DateTimeInterface $completed_at): self
    {
        $this->completed_at = $completed_at;

        return $this;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function setTask(string $task): self
    {
        $this->task = $task;

        return $this;
    }
}
