<?php

namespace Klevze\OnlineUsers\Tests\Unit;

use Carbon\Carbon;
use Klevze\OnlineUsers\Models\UserActivity;
use Klevze\OnlineUsers\OnlineUsers as OnlineUsersService;
use Orchestra\Testbench\TestCase;

class CleanupInactiveUsersTest extends TestCase
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

        // Run the package migration
        include_once __DIR__ . '/../../src/Migrations/2024_01_06_083649_create_user_activities_table.php';
        (new \CreateUserActivitiesTable())->up();
    }

    public function test_cleanup_removes_old_records_but_not_recent_ones()
    {
        // Set threshold to 5 minutes
        $this->app['config']->set('online-users.threshold', 5);

        // freeze current time
        Carbon::setTestNow($now = Carbon::now());

        // Recent activity - should stay
        UserActivity::create([
            'user_ip'       => '10.0.0.1',
            'last_activity' => $now,
        ]);

        // Old activity - should be cleaned up
        UserActivity::create([
            'user_ip'       => '10.0.0.2',
            'last_activity' => $now->copy()->subMinutes(10),
        ]);

        // sanity check
        $this->assertEquals(2, UserActivity::count());

        $service = new OnlineUsersService();
        $service->cleanUpInactiveUsers();

        // One old record removed
        $this->assertEquals(1, UserActivity::count());
        $this->assertTrue(UserActivity::where('user_ip', '10.0.0.1')->exists());
        $this->assertFalse(UserActivity::where('user_ip', '10.0.0.2')->exists());

        Carbon::setTestNow();
    }
}
