<?php

namespace Klevze\OnlineUsers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class OnlineUsersServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        $this->app->booting(function() {
            $loader = AliasLoader::getInstance();
            $loader->alias('OnlineUsers', 'Klevze\OnlineUsers\Facades\OnlineUsers');

        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CleanupInactiveUsers::class,
            ]);
        }

    }
}
