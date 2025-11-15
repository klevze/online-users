<?php

namespace Klevze\OnlineUsers\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Klevze\OnlineUsers\Middleware\TrackUserActivity;
use Klevze\OnlineUsers\Models\UserActivity;
use Orchestra\Testbench\TestCase;

class TrackUserActivityMiddlewareTest extends TestCase
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
        include_once __DIR__ . '/../../src/Migrations/2025_11_15_000000_add_user_ip_hash_to_user_activities_table.php';
        (new \AddUserIpHashToUserActivitiesTable())->up();
        (new \CreateUserActivitiesTable())->up();
    }

    public function test_ip_tracking_creates_user_activity()
    {
        $this->app['config']->set('online-users.tracking', 'ip');
        $this->app['config']->set('online-users.anonymize_ip', false);

        $request = Request::create('/', 'GET');
        $request->server->set('REMOTE_ADDR', '123.45.67.89');
        $this->app->instance('request', $request);

        (new TrackUserActivity())->handle($request, function ($r) {
            return 'ok';
        });

        $this->assertTrue(UserActivity::where('user_ip', '123.45.67.89')->exists());
    }

    public function test_ip_tracking_with_anonymization_hashes_ip()
    {
        $this->app['config']->set('online-users.tracking', 'ip');
        $this->app['config']->set('online-users.anonymize_ip', true);
        $this->app['config']->set('online-users.ip_salt', 'testsalt');

        $request = Request::create('/', 'GET');
        $request->server->set('REMOTE_ADDR', '123.45.67.89');
        $this->app->instance('request', $request);

        (new TrackUserActivity())->handle($request, function ($r) {
            return 'ok';
        });

        $expectedHash = hash('sha256', '123.45.67.89' . 'testsalt');

        $this->assertTrue(UserActivity::where('user_ip_hash', $expectedHash)->exists());
    }

    public function test_session_tracking_creates_user_activity()
    {
        $this->app['config']->set('online-users.tracking', 'session');

        $this->app['session']->start();

        $request = Request::create('/', 'GET');
        $this->app->instance('request', $request);
        // Attach Laravel session to request
        $request->setLaravelSession($this->app['session.store']);

        (new TrackUserActivity())->handle($request, function ($r) {
            return 'ok';
        });

        $sessionId = $this->app['session.store']->getId();

        $this->assertTrue(UserActivity::where('session_id', $sessionId)->exists());
    }

    public function test_user_id_tracking_creates_user_activity_when_authenticated()
    {
        $this->app['config']->set('online-users.tracking', 'user_id');

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(42);

        $request = Request::create('/', 'GET');
        $this->app->instance('request', $request);

        (new TrackUserActivity())->handle($request, function ($r) {
            return 'ok';
        });

        $this->assertTrue(UserActivity::where('user_id', 42)->exists());
    }
}
