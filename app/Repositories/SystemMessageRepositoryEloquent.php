<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\SystemMessageRepository;
use App\Models\SystemMessage;
use App\Validators\SystemMessageValidator;

/**
 * Class SystemMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class SystemMessageRepositoryEloquent extends BaseRepository implements SystemMessageRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return SystemMessage::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
