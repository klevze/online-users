<?php

namespace Klevze\OnlineUsers\Tests\Feature;

use Klevze\OnlineUsers\Models\UserActivity;
use Orchestra\Testbench\TestCase;

class AnonymizationIntegrationTest extends TestCase
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
        include_once __DIR__ . '/../../src/Migrations/2025_11_15_000000_add_user_ip_hash_to_user_activities_table.php';
        (new \AddUserIpHashToUserActivitiesTable())->up();
    }

    public function test_authenticated_user_with_anonymization_stores_hash_and_no_raw_ip_by_default()
    {
        $this->app['config']->set('online-users.tracking', 'ip');
        $this->app['config']->set('online-users.anonymize_ip', true);
        $this->app['config']->set('online-users.ip_salt', 'testsalt');
        $this->app['config']->set('online-users.store_raw_ip', false);

        // Simulate a request, use API from tests
        $request = \Illuminate\Http\Request::create('/', 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.0.1');
        $this->app->instance('request', $request);

        // call middleware
        (new \Klevze\OnlineUsers\Middleware\TrackUserActivity())->handle($request, function ($r) {
            return 'ok';
        });

        $hash = hash('sha256', '192.168.0.1'.'testsalt');

        $this->assertTrue(UserActivity::where('user_ip_hash', $hash)->exists());
        $this->assertFalse(UserActivity::whereNotNull('user_ip')->exists());
    }
}
