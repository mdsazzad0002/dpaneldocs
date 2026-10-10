<?php

namespace App\Providers;

use App\Models\User;
use App\Support\MailSettings;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        foreach (Permissions::all() as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        // SMTP saved in the admin panel overrides the .env mailer.
        $this->app->afterResolving('mail.manager', fn () => MailSettings::apply());
    }
}
