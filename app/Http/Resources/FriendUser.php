<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FriendUser extends JsonResource
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
            'username' => $this->nickname,
            'id' => $this->id,
            'status' => $this->status,
            'sign' => $this->sign,
            'avatar' => $this->avatar,
            'type' => $this->type,
            "groupid" => $this->groupid,
        ];
    }
}
