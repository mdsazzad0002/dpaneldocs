<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A donation a supporter reports after sending money. It counts towards the
 * goal once an admin has checked the transfer and marked it verified.
 */
#[Fillable(['name', 'email', 'amount', 'currency', 'donation_method_id', 'transaction_id', 'message', 'is_public', 'status', 'verified_at', 'ip_address'])]
#[Hidden(['email', 'ip_address', 'transaction_id'])]
class Donation extends Model
{
    public const STATUSES = ['pending', 'verified', 'rejected'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_public' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function scopeVerified(Builder $query): void
    {
        $query->where('status', 'verified');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(DonationMethod::class, 'donation_method_id');
    }
}
