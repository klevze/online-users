<?php

namespace Klevze\OnlineUsers\Console;

use Illuminate\Console\Command;
use Klevze\OnlineUsers\OnlineUsers;

class CleanupInactiveUsers extends Command
{
    protected $signature   = 'cleanup:inactive-users';
    protected $description = 'Remove inactive users from the user_activities table.';

    public function handle(): int
    {
        $onlineUsers = app(OnlineUsers::class);
        $onlineUsers->cleanUpInactiveUsers();

        $this->info('Inactive users cleaned up successfully.');
        return 0;
    }
}
