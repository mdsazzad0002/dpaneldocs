<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'company', 'rating', 'title', 'body', 'status', 'ip_address', 'approved_at'])]
#[Hidden(['email', 'ip_address'])]
class Review extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    /**
     * Average rating, count, and a 5..1 star breakdown of approved reviews.
     *
     * @return array{count: int, average: float, breakdown: array<int, int>}
     */
    public static function summary(): array
    {
        $counts = static::approved()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $count = (int) $counts->sum();
        $breakdown = collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($star) => [$star => (int) ($counts[$star] ?? 0)])->all();
        $average = $count ? round(collect($breakdown)->map(fn ($n, $star) => $n * $star)->sum() / $count, 1) : 0.0;

        return ['count' => $count, 'average' => $average, 'breakdown' => $breakdown];
    }
}
