<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['page', 'helpful', 'comment', 'ip_address'])]
#[Hidden(['ip_address'])]
class PageFeedback extends Model
{
    protected $table = 'page_feedback';

    protected function casts(): array
    {
        return [
            'helpful' => 'boolean',
        ];
    }
}
