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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Describes a section that tasks get grouped into.
 *
 * @property int     $id
 * @property string  $name
 * @property string  $status
 * @property ?int    $user_id
 */
class Section extends Model
{
    /**
     * Mass-assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'status',
        'user_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'section';

    /**
     * Whether the model uses created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = true;

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
     * @return ?User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Returns items belonging to this section.
     *
     * @return HasMany<Item, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Scope: active sections only.
     *
     * @param Builder $query The query builder.
     *
     * @return void
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'Active');
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
        $this->user()->associate($user);
        return $this;
    }

    /**
     * Returns the user this section belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
