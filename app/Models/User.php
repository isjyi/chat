<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, Transformable
{
    use Notifiable, TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'nickname', 'avatar', 'email', 'password', 'sign',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function friend_group()
    {
        return $this->hasMany(FriendGroup::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function group_members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function friends()
    {
        return $this->hasMany(Friend::class);
    }

    public function joinGroup()
    {
        return $this->hasManyThrough(Group::class, GroupMember::class, 'user_id', 'id', 'id', 'group_id');
    }

    public function offlineMessages()
    {
        return $this->hasMany(OfflineMessage::class);
    }

    public function systemMessages()
    {
        return $this->hasMany(SystemMessage::class);
    }
}
