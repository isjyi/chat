<?php

namespace App\Http\Controllers;

use App\Models\SystemMessage;
use App\Repositories\SystemMessageRepositoryEloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MessageBoxController extends Controller
{
    private $systemMessageRepo;

    public function __construct(SystemMessageRepositoryEloquent $systemMessageRepo)
    {
        $this->systemMessageRepo = $systemMessageRepo;
    }

    public function messageBox(Request $request)
    {
        $this->systemMessageRepo->where('user_id', auth()->user()->id)->update([
            'read' => 1,
        ]);

        $list = $this->systemMessageRepo
            ->select('id', 'from_id', 'friend_group_id as group_id', 'remark', 'type', 'status', 'created_at')
            ->with(['fromUser' => function ($q) {
                $q->select('id', 'avatar', 'nickname');
            }])
            ->where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')->paginate(10);

        $list->each(function ($i) {
            $i->time = time_tranx($i->created_at->unix());
        });

        return view('message_box', ['list' => $list]);

    }
}
