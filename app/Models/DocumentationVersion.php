<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentationVersion extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'documentation_id', 'version', 'changelog', 'install_guide',
        'file_path', 'file_name', 'file_size', 'downloads',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'downloads' => 'integer',
    ];

    public function documentation(): BelongsTo
    {
        return $this->belongsTo(Documentation::class);
    }
}
