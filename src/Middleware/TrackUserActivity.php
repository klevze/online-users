<?php
namespace Klevze\OnlineUsers\Middleware;

use Klevze\OnlineUsers\Models\UserActivity;
use Closure;

class TrackUserActivity
{
    public function handle($request, Closure $next)
    {
        UserActivity::updateOrCreate(
            ['user_ip' => request()->ip()],
            ['last_activity' => now()]
        );

        return $next($request);
    }
}
