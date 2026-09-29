<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'category', 'priority', 'subject', 'message', 'dpanel_version', 'server_os', 'status', 'ip_address', 'last_activity_at'])]
#[Hidden(['access_token', 'ip_address'])]
class SupportTicket extends Model
{
    public const CATEGORIES = [
        'installation' => 'Installation & setup',
        'bug' => 'Bug report',
        'migration' => 'Migration (cPanel, CyberPanel)',
        'email-dns-ssl' => 'Email, DNS & SSL',
        'feature' => 'Feature request',
        'paid-support' => 'Paid expert assistance',
        'other' => 'Something else',
    ];

    public const PRIORITIES = [
        'low' => 'Low — general question',
        'normal' => 'Normal — something is not working',
        'high' => 'High — a production site is down',
    ];

    public const STATUSES = [
        'open' => 'Open',
        'answered' => 'Answered',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            $ticket->reference ??= static::newReference();
            $ticket->access_token ??= Str::random(48);
            $ticket->last_activity_at ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
        ];
    }

    public static function newReference(): string
    {
        do {
            $reference = 'DP-'.Str::upper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * The private link that lets the customer follow the ticket without an account.
     */
    public function publicUrl(): string
    {
        return route('support.tickets.show', ['reference' => $this->reference, 'token' => $this->access_token]);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? Str::headline($this->category);
    }
}
