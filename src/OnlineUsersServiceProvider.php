<?php

namespace Klevze\OnlineUsers;

use Illuminate\Support\ServiceProvider;

class OnlineUsersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CleanupInactiveUsers::class,
            ]);
        }

    }
}
