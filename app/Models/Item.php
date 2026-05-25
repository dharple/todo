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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Describes an item or task that needs to be done.
 *
 * @property int      $id
 * @property string   $task
 * @property string   $status
 * @property int      $priority
 * @property Carbon   $created_at
 * @property ?Carbon  $completed_at
 * @property ?Carbon  $updated_at
 * @property ?int     $section_id
 * @property ?int     $user_id
 */
class Item extends Model
{
    /**
     * Attribute casting map.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Mass-assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'completed_at',
        'created_at',
        'priority',
        'section_id',
        'status',
        'task',
        'user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item';

    /**
     * Whether the model uses created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Returns the completion stamp.
     *
     * @return ?Carbon
     */
    public function getCompletedAt(): ?Carbon
    {
        return $this->completed_at;
    }

    /**
     * Returns the primary key.
     *
     * @return ?int
     */
    public function getId(): ?int
    {
        return isset($this->id) ? (int) $this->id : null;
    }

    /**
     * Returns the priority of the task.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return (int) $this->priority;
    }

    /**
     * Returns the section for this item.
     *
     * @return ?Section
     */
    public function getSection(): ?Section
    {
        return $this->section;
    }

    /**
     * Returns the status of the task.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Returns the task itself.
     *
     * @return string
     */
    public function getTask(): string
    {
        return $this->task ?? '';
    }

    /**
     * Returns the user for this item.
     *
     * @return ?User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Scope: items for a given user.
     *
     * @param Builder $query The query builder.
     * @param User    $user  The user to filter by.
     *
     * @return void
     */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Scope: open items only.
     *
     * @param Builder $query The query builder.
     *
     * @return void
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'Open');
    }

    /**
     * Returns the section this item belongs to.
     *
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Sets the completion stamp of the task.
     *
     * @param mixed $completedAt The completion stamp.  Pass `null` to wipe it
     *                           out, anything else gets fed into Carbon's
     *                           constructor.
     *
     * @return Item
     */
    public function setCompletedAt(mixed $completedAt): Item
    {
        $this->completed_at = $completedAt ? new Carbon($completedAt) : null;
        return $this;
    }

    /**
     * Sets the priority of the task.
     *
     * @param int $priority The priority of the task.
     *
     * @return Item
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
     *
     * @return Item
     */
    public function setSection(?Section $section): Item
    {
        $this->section()->associate($section);
        return $this;
    }

    /**
     * Sets the status of the task.
     *
     * @param string $status The status of the task.
     *
     * @return Item
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
     *
     * @return Item
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
     *
     * @return Item
     */
    public function setUser(?User $user): Item
    {
        $this->user()->associate($user);
        return $this;
    }

    /**
     * Returns the user this item belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
