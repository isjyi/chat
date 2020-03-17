<?php

namespace App\Listeners;

use App\Event\Register;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateDefaultFriendGroup
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
            $user->group_members()->create([
                'group_id' => 10001
            ]);
        }
    }
}
