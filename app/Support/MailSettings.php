<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Points Laravel's mailer at the SMTP server saved in the admin panel. Runs
 * the first time the mail manager is resolved, so .env stays the fallback
 * until SMTP is switched on in Settings.
 */
class MailSettings
{
    public static function apply(): void
    {
        $settings = rescue(fn () => Setting::allValues(), [], false);

        if (($settings['mail.enabled'] ?? '0') !== '1' || blank($settings['mail.host'] ?? null)) {
            return;
        }

        $encryption = Setting::get('mail.encryption', 'tls');

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => Setting::get('mail.host'),
            'mail.mailers.smtp.port' => (int) Setting::get('mail.port', $encryption === 'ssl' ? 465 : 587),
            'mail.mailers.smtp.username' => Setting::get('mail.username'),
            'mail.mailers.smtp.password' => Setting::get('mail.password'),
            'mail.from.address' => Setting::get('mail.from_address', config('mail.from.address')),
            'mail.from.name' => Setting::get('mail.from_name', config('mail.from.name')),
        ]);
    }
}
