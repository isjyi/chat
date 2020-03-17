<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\ChatRecordRepository;
use App\Models\ChatRecord;
use App\Validators\ChatRecordValidator;

/**
 * Class ChatRecordRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ChatRecordRepositoryEloquent extends BaseRepository implements ChatRecordRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ChatRecord::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
