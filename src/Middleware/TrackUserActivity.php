<?php
namespace Klevze\OnlineUsers\Middleware;

use Klevze\OnlineUsers\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tracking = function_exists('config') ? config('online-users.tracking', 'ip') : 'ip';

        // If we configure anonymization for IP tracking, apply hash via salt.
        $anonymize = function_exists('config') ? config('online-users.anonymize_ip', false) : false;
        $ipSalt = function_exists('config') ? config('online-users.ip_salt', null) : null;
        $hashAlgorithm = function_exists('config') ? config('online-users.hash_algorithm', 'sha256') : 'sha256';
        $storeRaw = function_exists('config') ? config('online-users.store_raw_ip', false) : false;

        switch ($tracking) {
            case 'user_id':
                if (auth()->check()) {
                    UserActivity::updateOrCreate(
                        ['user_id' => auth()->id()],
                        ['last_activity' => now()]
                    );
                }
                break;
            case 'session':
                UserActivity::updateOrCreate(
                    ['session_id' => session()->getId()],
                    ['last_activity' => now()]
                );
                break;
            case 'ip':
            default:
                $ipAddr = request()->ip();
                $ipHash = null;
                if ($anonymize && $ipSalt) {
                    $ipHash = hash($hashAlgorithm, $ipAddr . $ipSalt);
                }

                $attributes = [];
                if ($storeRaw || !$anonymize) {
                    $attributes['user_ip'] = $ipAddr;
                }

                if ($ipHash) {
                    $attributes['user_ip_hash'] = $ipHash;
                }

                // Ensure user_ip key exists in attributes before calling updateOrCreate
                $attributes['user_ip'] = $attributes['user_ip'] ?? null;

                $attributes['last_activity'] = now();

                UserActivity::updateOrCreate(
                    ['user_ip' => $attributes['user_ip'], 'user_ip_hash' => $attributes['user_ip_hash'] ?? null],
                    $attributes
                );
                break;
        }

        return $next($request);
    }
}
