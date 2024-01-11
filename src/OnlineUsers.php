<?php

namespace Klevze\OnlineUsers;

use Klevze\OnlineUsers\Models\UserActivity;
use Illuminate\Support\Facades\DB;

class OnlineUsers
{
    protected $threshold = 5; // Adjust the threshold as needed

    public function __construct()
    {

    }

    public function cleanUpInactiveUsers()
    {
        $inactiveThreshold = now()->subMinutes($this->threshold);
        UserActivity::where('last_activity', '<=', $inactiveThreshold)->delete();
    }

    public function getActiveUsers()
    {
        return UserActivity::where('last_activity', '>=', now()
                   ->subMinutes($this->threshold))
                   ->count();
    }
}
