<?php

namespace Klevze\OnlineUsers;

use Klevze\OnlineUsers\Models\UserActivity;

class OnlineUsers
{
    protected int $threshold = 5; // Adjust the threshold as needed

    public function __construct()
    {
        // Allow overriding default threshold from config
        if (function_exists('config')) {
            $this->threshold = config('online-users.threshold', $this->threshold);
        }

    }

    public function cleanUpInactiveUsers(): void
    {
        $inactiveThreshold = now()->subMinutes($this->threshold);
        UserActivity::where('last_activity', '<=', $inactiveThreshold)->delete();
    }

    public function getActiveUsers(): int
    {
        return UserActivity::where('last_activity', '>=', now()
                   ->subMinutes($this->threshold))
                   ->count();
    }

    /**
     * Set the active threshold in minutes.
     */
    public function setThreshold(int $minutes): self
    {
        $this->threshold = $minutes;
        return $this;
    }

    /**
     * Get the active threshold in minutes.
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }
}
