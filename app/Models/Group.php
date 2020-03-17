<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Group.
 *
 * @package namespace App\Models;
 */
class Group extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'avatar',
    ];

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, GroupMember::class, 'group_id', 'id', 'id', 'user_id');
    }

}
