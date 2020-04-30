<?php

namespace App\Listeners;

use App\Events\sendNotification;
use App\Models\User;
use App\Notifications\systemNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
