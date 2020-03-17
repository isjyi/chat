<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FriendGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasManyThrough(User::class,Friend::class,'friend_group_id','id','id','friend_id');
    }
}
