<?php

use App\Models\Role;
use App\Models\User;
use App\Support\DocsSync;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('docs:sync {source? : Path to a dpanel repository checkout (default: download from GitHub)} {--branch= : GitHub branch to download from}', function (?string $source = null) {
    if ($source !== null && ! is_dir(rtrim($source, '/').'/docs')) {
        $this->error("No docs/ directory found in [{$source}].");

        return 1;
    }

    $sync = $source !== null
        ? DocsSync::fromDirectory($source)
        : DocsSync::fromGitHub(branch: $this->option('branch') ?: null);

    foreach ($sync->changed as $slug) {
        $this->line("Updated <info>{$slug}</info>");
    }

    foreach ($sync->skipped as $slug) {
        $this->warn("Skipped {$slug}: ".DocsSync::files()[$slug].' not found.');
    }

    if ($sync->status === 'failed') {
        $this->error($sync->message ?? 'Sync failed.');

        return 1;
    }

    $this->info(count($sync->changed).' page(s) updated from '.$sync->source.'. Rendered pages refresh automatically.');
})->purpose('Copy the documentation Markdown from GitHub or a dpanel checkout into resources/docs');

Artisan::command('helpdesk:admin {email} {--name=Admin}', function (string $email) {
    $password = Str::password(16);

    $user = User::updateOrCreate(['email' => $email], [
        'name' => $this->option('name'),
        'password' => $password,
        'email_verified_at' => now(),
        'role_id' => Role::where('slug', 'admin')->value('id'),
    ]);

    $this->info("Admin ready: {$user->email}");
    $this->line("Password: <comment>{$password}</comment> (change it from the Profile page)");
})->purpose('Create or reset an administrator account');
