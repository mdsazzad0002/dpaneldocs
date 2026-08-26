<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'slug'];

    public function documentation(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }

    public static function findOrCreateByName(string $name): self
    {
        $existing = self::query()->where('name', $name)->first();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'id' => (string) Str::uuid(),
            'name' => $name,
            'slug' => self::uniqueSlug($name),
        ]);
    }

    public static function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            self::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
