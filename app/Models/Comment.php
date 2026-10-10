<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A comment on a documentation page. Visitors' comments wait for moderation;
 * staff replies are children of the comment they answer and publish at once.
 */
#[Fillable(['page', 'parent_id', 'user_id', 'name', 'email', 'body', 'status', 'is_staff', 'ip_address'])]
#[Hidden(['email', 'ip_address'])]
class Comment extends Model
{
    public const STATUSES = ['pending', 'approved', 'spam'];

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
        ];
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function url(): string
    {
        return route('docs.show', $this->page).'#comment-'.($this->parent_id ?? $this->id);
    }
}
