<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'mine' => [
                'username' => $this->nickname,
                'id' => $this->id,
                'status' => $this->status,
                'sign' => $this->sign,
                'avatar' => $this->avatar,
                'friend' => FriendGroup::collection($this->friend_group),
            ],
            'friend' => FriendGroup::collection($this->friend_group),
            'group' => Group::collection($this->joinGroup)
        ];
    }
}
