<?php

namespace Klevze\OnlineUsers\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Schema;

class DropUserIpMigrationTest extends TestCase
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

    public function test_drop_user_ip_removes_column()
    {
        // ensure initial column is present
        $this->assertTrue(Schema::hasColumn('user_activities', 'user_ip'));

        // run migration that drops column
        include_once __DIR__ . '/../../src/Migrations/2025_11_15_000001_drop_raw_user_ip_from_user_activities_table.php';
        (new \DropRawUserIpFromUserActivitiesTable())->up();

        $this->assertFalse(Schema::hasColumn('user_activities', 'user_ip'));
    }
}
