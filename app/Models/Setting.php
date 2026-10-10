<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value settings edited from the admin panel (SMTP, AI assistant,
 * donation goal). Secret keys are stored encrypted with the app key.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Settings stored encrypted at rest and never sent to the browser.
     */
    public const SECRETS = ['mail.password', 'ai.api_key'];

    private const CACHE_KEY = 'settings:all';

    /**
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        // Before the migrations have run there is nothing to read (or cache).
        if (! Cache::has(self::CACHE_KEY) && ! Schema::hasTable('settings')) {
            return [];
        }

        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::allValues()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        if (in_array($key, self::SECRETS, true)) {
            return rescue(fn () => Crypt::decryptString($value), $default, false);
        }

        return $value;
    }

    /**
     * Save several settings at once. A null secret keeps the stored value,
     * so forms can leave password fields blank.
     *
     * @param  array<string, mixed>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            $isSecret = in_array($key, self::SECRETS, true);

            if ($isSecret && ($value === null || $value === '')) {
                continue;
            }

            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            static::query()->updateOrCreate(['key' => $key], [
                'value' => $isSecret ? Crypt::encryptString((string) $value) : ($value === null ? null : (string) $value),
            ]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    public static function forget(string $key): void
    {
        static::query()->whereKey($key)->delete();
        Cache::forget(self::CACHE_KEY);
    }

    public static function has(string $key): bool
    {
        return filled(static::allValues()[$key] ?? null);
    }
}
