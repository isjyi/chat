<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\OfflineMessageRepository;
use App\Models\OfflineMessage;
use App\Validators\OfflineMessageValidator;

/**
 * Class OfflineMessageRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class OfflineMessageRepositoryEloquent extends BaseRepository implements OfflineMessageRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OfflineMessage::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
