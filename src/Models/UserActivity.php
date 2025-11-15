<?php

namespace Klevze\OnlineUsers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method static \Illuminate\Database\Eloquent\Builder updateOrCreate(array $attributes, array $values)
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, $operator = null, $value = null)
 */
class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_ip',
        'last_activity',
        'user_ip_hash',
        'user_id',
        'session_id',
    ];

    /**
     * Set table name from config, if configured.
     */
    protected static function booted()
    {
        parent::booted();

        if (function_exists('config')) {
            $table = config('online-users.table');
            if (!empty($table)) {
                (new self())->setTable($table);
            }
        }
    }
}
