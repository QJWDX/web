<?php

namespace App\Listeners;

use App\Events\UserLogin;
use App\Models\Common\LoginLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserLogin  $event
     * @return void
     */
    public function handle(UserLogin $event)
    {
        $user_id = $event->user->id;
        $login_time = date('Y-m-d H:i:s');
        $ip = request()->getClientIp();
        $login_address = '';
        $is_success = 1;
        $event->user->increment('login_count');
        LoginLog::query()->create(compact('user_id', 'ip', 'login_address', 'login_time', 'is_success'));
    }
}
