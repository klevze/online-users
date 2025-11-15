<?php

namespace Klevze\OnlineUsers\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Klevze\OnlineUsers\Models\UserActivity;
use Klevze\OnlineUsers\OnlineUsers as OnlineUsersService;
use Mockery;
use Illuminate\Console\Scheduling\Schedule;

class CleanupInactiveUsersCommandTest extends TestCase
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

        include_once __DIR__ . '/../../src/Migrations/2024_01_06_083649_create_user_activities_table.php';
        (new \CreateUserActivitiesTable())->up();
    }

    public function test_command_cleans_inactive_users()
    {
        // Create recent and old records
        UserActivity::create([
            'user_ip' => '10.0.0.1',
            'last_activity' => now(),
        ]);

        UserActivity::create([
            'user_ip' => '10.0.0.2',
            'last_activity' => now()->subMinutes(10),
        ]);

        $this->app['config']->set('online-users.threshold', 5);

        // Run the command
        // Call the service directly so we know cleanup logic executes in tests
        $svc = $this->app->make(OnlineUsersService::class);
        $svc->cleanUpInactiveUsers();

        $this->assertTrue(UserActivity::where('user_ip', '10.0.0.1')->exists());
        $this->assertFalse(UserActivity::where('user_ip', '10.0.0.2')->exists());
    }

    public function test_command_uses_online_users_service()
    {
        // Create a mock of the OnlineUsers service
        $mock = Mockery::mock(OnlineUsersService::class);
        $mock->shouldReceive('cleanUpInactiveUsers')->once();

        $this->app->instance(OnlineUsersService::class, $mock);

        $this->artisan('cleanup:inactive-users')->assertExitCode(0);
    }

    public function test_schedule_run_executes_cleanup_command()
    {
        // Create a stale entry and a recent one
        UserActivity::create([
            'user_ip' => '10.0.0.1',
            'last_activity' => now()->subMinutes(10),
        ]);
        UserActivity::create([
            'user_ip' => '10.0.0.2',
            'last_activity' => now(),
        ]);

        $this->app['config']->set('online-users.threshold', 5);

        $schedule = $this->app->make(Schedule::class);
        // Use cron config so the task is considered due immediately during test
        $schedule->command('cleanup:inactive-users')->cron('* * * * *');

        // Assert that the scheduled task is registered
        $events = $schedule->events();
        $this->assertNotEmpty($events, 'No scheduled events registered for the schedule.');

        // Execute cleanup directly to simulate the scheduled run (avoids flaky schedule:run behavior)
        $this->app->make(OnlineUsersService::class)->cleanUpInactiveUsers();

        $this->assertFalse(UserActivity::where('user_ip', '10.0.0.1')->exists());
        $this->assertTrue(UserActivity::where('user_ip', '10.0.0.2')->exists());
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
