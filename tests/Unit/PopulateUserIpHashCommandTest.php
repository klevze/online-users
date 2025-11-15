<?php

namespace Klevze\OnlineUsers\Tests\Unit;

use Klevze\OnlineUsers\Models\UserActivity;
use Orchestra\Testbench\TestCase;

class PopulateUserIpHashCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Klevze\OnlineUsers\OnlineUsersServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();
        include_once __DIR__ . '/../../src/Migrations/2024_01_06_083649_create_user_activities_table.php';
        (new \CreateUserActivitiesTable())->up();

        // ensure hash column is available (migration added in 2025)
        include_once __DIR__ . '/../../src/Migrations/2025_11_15_000000_add_user_ip_hash_to_user_activities_table.php';
        (new \AddUserIpHashToUserActivitiesTable())->up();
    }

    public function test_populate_command_hashes_ips()
    {
        // insert two records without ip_hash
        UserActivity::create(['user_ip' => '1.2.3.4', 'last_activity' => now()]);
        UserActivity::create(['user_ip' => '5.6.7.8', 'last_activity' => now()]);

        $this->app['config']->set('online-users.ip_salt', 'testsalt');
        $this->app['config']->set('online-users.hash_algorithm', 'sha256');

        $this->artisan('online-users:populate-ip-hash')->assertExitCode(0);

        $hash1 = hash('sha256', '1.2.3.4' . 'testsalt');
        $hash2 = hash('sha256', '5.6.7.8' . 'testsalt');

        $this->assertTrue(UserActivity::where('user_ip_hash', $hash1)->exists());
        $this->assertTrue(UserActivity::where('user_ip_hash', $hash2)->exists());
    }

    public function test_dry_run_does_not_modify_database()
    {
        UserActivity::create(['user_ip' => '9.9.9.9', 'last_activity' => now()]);

        $this->app['config']->set('online-users.ip_salt', 'testsalt');
        $this->app['config']->set('online-users.hash_algorithm', 'sha256');

        $this->artisan('online-users:populate-ip-hash --dry-run')
            ->expectsOutput('Dry run: 1 records would be processed.')
            ->assertExitCode(0);

        // The record should still have null user_ip_hash (no changes in dry-run)
        $this->assertTrue(UserActivity::whereNull('user_ip_hash')->exists());
    }
}
