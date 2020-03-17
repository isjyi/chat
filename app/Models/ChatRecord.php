<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class ChatRecord.
 *
 * @package namespace App\Models;
 */
class ChatRecord extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'friend_id', 'group_id', 'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
