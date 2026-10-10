<?php

namespace App\Support;

use App\Models\Donation;
use App\Models\Setting;

/**
 * The donation goal shown on /donate ("Buy me a laptop / PC") and its progress.
 */
class Donations
{
    public const DEFAULTS = [
        'donation.enabled' => '1',
        'donation.title' => 'Buy me a laptop / PC',
        'donation.description' => 'dPanel is free and built in my spare time on an old machine. A faster laptop or PC means quicker builds, more testing on real servers, and more features for everyone. Every contribution, big or small, helps.',
        'donation.goal_amount' => '150000',
        'donation.currency' => 'BDT',
        'donation.raised_offset' => '0',
        'donation.thank_you' => 'Thank you so much for supporting dPanel! Your donation was received and counts towards the new development machine. It really makes a difference.',
    ];

    public static function setting(string $key): string
    {
        return (string) Setting::get($key, self::DEFAULTS[$key] ?? '');
    }

    /**
     * @return array{enabled: bool, title: string, description: string, amount: float, currency: string, raised: float, offset: float, percent: int, supporters: int}
     */
    public static function goal(): array
    {
        $currency = self::setting('donation.currency');
        $offset = (float) self::setting('donation.raised_offset');
        $verified = Donation::verified()->where('currency', $currency);
        $raised = (float) $verified->sum('amount') + $offset;
        $amount = (float) self::setting('donation.goal_amount');

        return [
            'enabled' => self::setting('donation.enabled') === '1',
            'title' => self::setting('donation.title'),
            'description' => self::setting('donation.description'),
            'amount' => $amount,
            'currency' => $currency,
            'raised' => $raised,
            'offset' => $offset,
            'percent' => $amount > 0 ? (int) min(100, floor($raised / $amount * 100)) : 0,
            'supporters' => Donation::verified()->count(),
        ];
    }

    public static function format(float $amount, string $currency): string
    {
        return $currency.' '.number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);
    }
}
