<?php

use App\Models\User;
use App\Support\Docs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

Artisan::command('docs:sync {source : Path to a dpanel repository checkout}', function (string $source) {
    $source = rtrim($source, '/');

    if (! is_dir($source.'/docs')) {
        $this->error("No docs/ directory found in [{$source}].");

        return 1;
    }

    // Site page slug => file in the dpanel repository.
    $map = collect(config('site.docs', []))
        ->flatMap(fn ($pages) => array_keys($pages))
        ->mapWithKeys(fn ($slug) => [$slug => Docs::sourceFile($slug)]);

    foreach ($map as $slug => $file) {
        if (! is_file($source.'/'.$file)) {
            $this->warn("Skipped {$slug}: {$file} not found.");

            continue;
        }

        File::copy($source.'/'.$file, Docs::path($slug));
        $this->line("Synced <info>{$slug}</info> from {$file}");
    }

    $this->info('Documentation synced. Rendered pages refresh automatically.');
})->purpose('Copy the documentation Markdown from a dpanel checkout into resources/docs');

Artisan::command('helpdesk:admin {email} {--name=Admin}', function (string $email) {
    $password = Str::password(16);

    $user = User::updateOrCreate(['email' => $email], [
        'name' => $this->option('name'),
        'password' => $password,
        'email_verified_at' => now(),
    ]);

    $user->assignRole(Role::findOrCreate('admin', 'web'));

    $this->info("Admin ready: {$email}");
    $this->line("Password: <comment>{$password}</comment> (change it from the Profile page)");
})->purpose('Create or reset a help desk administrator account');
