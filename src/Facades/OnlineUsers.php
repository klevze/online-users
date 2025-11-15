<?php

namespace Klevze\OnlineUsers\Facades;

use Illuminate\Support\Facades\Facade;

class OnlineUsers extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Klevze\OnlineUsers\OnlineUsers::class;
    }
}
