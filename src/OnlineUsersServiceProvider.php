<?php

namespace Klevze\OnlineUsers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class OnlineUsersServiceProvider extends ServiceProvider
{

    public function register()
    {
        // Merge package config
        $this->mergeConfigFrom(__DIR__ . '/../config/online-users.php', 'online-users');
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Bind the service into the container so the facade resolves
        $this->app->singleton(OnlineUsers::class, function ($app) {
            return new OnlineUsers();
        });

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
        // Allow publishing package config and migrations to the Laravel app
        $this->publishes([
            __DIR__ . '/../config/online-users.php' => config_path('online-users.php'),
        ], 'online-users-config');

        // Publish all migrations in the package so users get any new migrations
        $this->publishes([
            __DIR__ . '/Migrations/' => database_path('migrations'),
        ], 'online-users-migrations');
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CleanupInactiveUsers::class,
                Console\PopulateUserIpHash::class,
            ]);
        }

    }
}
