<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\GroupMember;

/**
 * Class GroupMemberRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class GroupMemberRepositoryEloquent extends BaseRepository implements GroupMemberRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return GroupMember::class;
    }



    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
