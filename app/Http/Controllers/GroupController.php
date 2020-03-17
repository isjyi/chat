<?php

namespace App\Http\Controllers;

use App\Event\Broadcast;
use App\Http\Resources\JoinGroup;
use App\Http\Resources\Users;
use App\Models\Group;
use App\Models\GroupMember;
use App\Repositories\GroupMemberRepositoryEloquent;
use App\Repositories\GroupRepositoryEloquent;
use DB;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $groupRepo;

    protected $groupMemberRepo;

    public function __construct(GroupRepositoryEloquent $group, GroupMemberRepositoryEloquent $groupMember)
    {
        $this->groupRepo       = $group;
        $this->groupMemberRepo = $groupMember;
    }

    public function joinGroup(Request $request, int $id)
    {
        $group = $this->groupRepo->find($id);

        if (!$group instanceof Group) {
            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '该群不存在',
                ]
            );
        }

        $check = $this->groupMemberRepo->where([
            'group_id' => $id,
            'user_id'  => $request->user()->id,
        ])->first();

        if ($check instanceof GroupMember) {
            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '您已经是该群成员',
                ]
            );
        }
        try {
            DB::beginTransaction();

            $query = $group->members()->create([
                'user_id' => $request->user()->id,
            ]);

            if ($query instanceof GroupMember) {
                $event = new Broadcast(collect([
                    'user' => $group->members()->value('user_id'),
                    'data' => [
                        "type" => "joinNotify",
                        "data" => [
                            "system"  => true,
                            "id"      => $group->id,
                            "type"    => "group",
                            "content" => auth()->user()->id . "加入了群聊，欢迎下新人吧～",
                        ],
                    ],
                ]));

                $event->setTries(3);

                $success = Event::fire($event);

                if (!$success) throw new \Exception('error');

                DB::commit();

                $group->setAttribute('type', 'group');
                return response()->json(
                    [
                        'code' => 200,
                        'msg'  => '加入成功',
                        'data' => new JoinGroup($group),
                    ]
                );
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '请稍后再试',
                ]
            );
        }
    }

    public function createGroup(Request $request)
    {
        try {
            $request->offsetSet('user_id', $request->user()->id);

            DB::beginTransaction();

            $group = $this->groupRepo->create($request->all());

            $group->members()->create(['user_id' => $request->user()->id]);

            DB::commit();

            $group->setAttribute('type', 'group');

            return response()->json(
                [
                    'code' => 200,
                    'msg'  => '创建成功！',
                    'data' => new JoinGroup($group),
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '创建失败！',
                ]
            );
        }
    }

    public function groupMember(Request $request)
    {
        $id = $request->input('id');

        $group = $this->groupRepo->find($id);

        if ($group instanceof Group) {
            return response()->json([
                'code' => 0,
                'msg'  => '',
                'data' => [
                    'list' => Users::collection($group->users),
                ],
            ]);
        }
    }
}
