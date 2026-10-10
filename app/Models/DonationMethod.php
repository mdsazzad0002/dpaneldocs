<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A way to send money: a bank account, a mobile wallet (bKash, Nagad…), or
 * anything else with instructions. Shown on the public donate page.
 */
#[Fillable(['type', 'label', 'bank_name', 'account_name', 'account_number', 'branch', 'routing_number', 'swift_code', 'instructions', 'is_active', 'sort_order'])]
class DonationMethod extends Model
{
    public const TYPES = [
        'bank' => 'Bank transfer',
        'mobile' => 'Mobile banking',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
