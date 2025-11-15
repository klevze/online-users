<?php

namespace Klevze\OnlineUsers\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Schema;

class UserIpHashIndexTest extends TestCase
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
        include_once __DIR__ . '/../../src/Migrations/2025_11_15_000000_add_user_ip_hash_to_user_activities_table.php';
        (new \AddUserIpHashToUserActivitiesTable())->up();
    }

    public function test_index_exists_for_user_ip_hash()
    {
        $conn = Schema::getConnection();
        $driver = $conn->getDriverName();

        // Use the database's PRAGMA/index listing for sqlite. For other drivers, fallback to Doctrine Schema Manager.
        if ($driver === 'sqlite') {
            $indexes = $conn->select("PRAGMA index_list('user_activities')");
            $found = false;
            foreach ($indexes as $idx) {
                $name = $idx->name ?? $idx->idxname ?? null;
                if ($name && str_contains($name, 'user_ip_hash')) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'user_ip_hash index not found');
            return;
        }

        // Fallback to Doctrine Schema Manager for non-SQLite drivers (ensure doctrine/dbal is present in dev deps).
        $sm = $conn->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('user_activities');

        $found = false;
        foreach ($indexes as $idx) {
            $columns = $idx->getColumns();
            if (in_array('user_ip_hash', $columns)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'user_ip_hash index not found');
    }
}
