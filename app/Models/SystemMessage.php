<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class SystemMessage.
 *
 * @package namespace App\Models;
 */
class SystemMessage extends Model implements Transformable
{
    use TransformableTrait;

    const FRIEND_ASSENT = 1;

    const FRIEND_REFUSAL = 2;

    const FRIEND_REQUEST = 0;

    const FRIEND_NOTICE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'from_id', 'friend_group_id', 'remark', 'type', 'status', 'read',
    ];


    public function fromUser()
    {
        return $this->hasOne(User::class, 'id', 'from_id');
    }

}
