<?php

namespace App\Support;

/**
 * Every permission the admin panel checks. Roles store a list of these keys
 * (or "*" for every permission), and each key is registered as a Gate
 * ability, so routes use `can:tickets.manage` and Vue reads `can['tickets.manage']`.
 */
class Permissions
{
    public const GROUPS = [
        'Help desk' => [
            'tickets.manage' => 'Answer and manage support tickets',
            'reviews.manage' => 'Moderate and reply to reviews',
            'comments.manage' => 'Moderate and reply to docs comments',
            'feedback.view' => 'Read "was this page helpful?" feedback',
            'mail.send' => 'Send email from the panel and read the mail log',
            'ai.use' => 'Draft replies with the AI assistant',
        ],
        'Content' => [
            'docs.sync' => 'Sync the documentation from GitHub',
        ],
        'Donations' => [
            'donations.manage' => 'Verify donations, edit bank details and the goal',
        ],
        'Administration' => [
            'users.manage' => 'Create and edit staff accounts',
            'roles.manage' => 'Create and edit roles and permissions',
            'settings.manage' => 'Change SMTP and AI settings',
        ],
    ];

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_merge(...array_values(array_map('array_keys', self::GROUPS)));
    }
}
