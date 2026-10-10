<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'to_email', 'to_name', 'subject', 'body', 'status', 'error', 'context'])]
class OutboundMail extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
