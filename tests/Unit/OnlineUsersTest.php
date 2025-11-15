<?php

namespace Klevze\OnlineUsers\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Klevze\OnlineUsers\Models\UserActivity;
use Klevze\OnlineUsers\OnlineUsers as OnlineUsersService;

class OnlineUsersTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Klevze\OnlineUsers\OnlineUsersServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();

        // Run the package migration
        include_once __DIR__ . '/../../src/Migrations/2024_01_06_083649_create_user_activities_table.php';
        (new \CreateUserActivitiesTable())->up();
    }

    public function test_get_active_users_counts_successfully()
    {
        UserActivity::create([
            'user_ip' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        $service = new OnlineUsersService();

        $this->assertEquals(1, $service->getActiveUsers());
    }
}
