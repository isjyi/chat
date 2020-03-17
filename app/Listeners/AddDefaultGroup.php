<?php

namespace App\Listeners;

use App\Event\Register;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddDefaultGroup
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
     * @param  Register  $event
     * @return void
     */
    public function handle(Register $event)
    {
        $user = $event->user;

        if ($user instanceof User) {
            $user->friend_group()->create([
                'name' => '默认分组'
            ]);
        }
    }
}
