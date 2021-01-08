<?php

namespace App\Listeners;

use App\Events\sendNotification;
use App\Notifications\systemNotification;
use Dx\Role\Models\User;

class sendNotificationListener
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
     * @param  sendNotification  $event
     * @return void
     */
    public function handle(sendNotification $event)
    {
        $user = User::all();
        \Illuminate\Support\Facades\Notification::send($user, new systemNotification($event->notification));
    }
}
