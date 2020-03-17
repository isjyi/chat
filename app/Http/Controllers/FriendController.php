<?php

namespace App\Http\Controllers;

use App\Event\Broadcast;
use App\Http\Resources\FriendUser;
use App\Models\Friend;
use App\Models\SystemMessage;
use App\Models\User;
use App\Repositories\FriendRepositoryEloquent;
use App\Repositories\GroupRepositoryEloquent;
use App\Repositories\SystemMessageRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;
use DB;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    protected $userRepo;
    protected $groupRepo;
    protected $friendRepo;
    protected $systemMessageRepo;

    public function __construct(UserRepositoryEloquent $user, GroupRepositoryEloquent $group, FriendRepositoryEloquent $friend, SystemMessageRepositoryEloquent $systemMessage)
    {
        $this->userRepo          = $user;
        $this->groupRepo         = $group;
        $this->friendRepo        = $friend;
        $this->systemMessageRepo = $systemMessage;
    }

    public function find(Request $request)
    {
        $type   = $request->input('type');
        $wd     = $request->input('wd');
        $users  = [];
        $groups = [];

        switch ($type) {
            case "user" :
                $users = $this->userRepo->select('id', 'nickname', 'avatar')->where(function ($q) use ($wd) {
                    $q->where('id', 'like', '%' . $wd . '%');
                    $q->orWhere('nickname', 'like', '%' . $wd . '%');
                    $q->orWhere('username', 'like', '%' . $wd . '%');
                })->get();

                break;
            case "group" :
                $groups = $this->groupRepo->select('id', 'name as groupname', 'avatar')->where(function ($q) use ($wd) {
                    $q->where('id', 'like', '%' . $wd . '%');
                    $q->orWhere('name', 'like', '%' . $wd . '%');
                })->get();

                break;
            default :
                break;
        }

        return view('find', ['user_list' => $users, 'group_list' => $groups, 'type' => $type, 'wd' => $wd]);
    }

    public function add(Request $request)
    {
        if ($request->input('user_id') == auth()->user()->id) {
            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '不能添加自己为好友',
                ]
            );
        }

        $isFriend = $this->friendRepo->where([
            'friend_id' => $request->input('user_id'),
            'user_id'   => auth()->user()->id,
        ])->first();

        if ($isFriend instanceof Friend) {
            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '对方已经是你的好友，不可重复添加',
                ]
            );
        }

        $request->offsetSet('from_id', auth()->user()->id);

//        $isApply = $this->systemMessageRepo->where([
//            'user_id' => $request->input('user_id'),
//            'from_id' => $request->input('from_id'),
//
//        ])

        try {
            DB::beginTransaction();

            $this->systemMessageRepo->create($request->all());

            $count = $this->systemMessageRepo->count(['user_id' => $request->input('user_id'), 'read' => 0]);


            $event = new Broadcast(collect([
                'user' => $request->input('user_id'),
                'data' => [
                    "type"  => "msgBox",
                    "count" => $count,
                ],
            ]));

            $event->setTries(3);

            $success = Event::fire($event);

            if (!$success) throw new \Exception('error');

            DB::commit();

            return response()->json(
                [
                    'code' => 200,
                    'msg'  => '添加成功！',
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(
                [
                    'code' => 500,
                    'msg'  => '请稍后再试!',
                ]
            );
        }
    }

    public function operate(Request $request, int $id)
    {
        $systemMsg = $this->systemMessageRepo->find($id);

        if ($systemMsg instanceof SystemMessage) {
            $isFriend = $this->friendRepo->where([
                'user_id'   => $systemMsg->user_id,
                'friend_id' => $systemMsg->from_id,
            ])->first();

            if ($isFriend instanceof Friend) {
                return response()->json(
                    [
                        'code' => 500,
                        'msg'  => '已经是好友了!',
                    ]
                );
            }

            try {
                $systemMsg->status = $request->input('status');
                DB::beginTransaction();
                $systemMsg->save();

                if ($systemMsg->status == SystemMessage::FRIEND_ASSENT) {
                    $this->friendRepo->create([
                            'user_id'         => $systemMsg->user_id,
                            'friend_id'       => $systemMsg->from_id,
                            'friend_group_id' => $request->input('groupid'),
                        ]
                    );

                    $this->friendRepo->create(
                        [
                            'user_id'         => $systemMsg->from_id,
                            'friend_id'       => $systemMsg->user_id,
                            'friend_group_id' => $systemMsg->friend_group_id,
                        ]);

                    $this->systemMessageRepo->create([
                        'user_id' => $systemMsg->from_id,
                        'from_id' => $systemMsg->user_id,
                        'type'    => SystemMessage::FRIEND_NOTICE,
                        'status'  => SystemMessage::FRIEND_ASSENT,
                    ]);

                    $user = $this->userRepo->find($systemMsg->from_id);

                    if (!$user instanceof User)
                        throw new \Exception('User does not exist');

                    $user->setAttribute('groupid', $request->input('groupid'));
                    $user->setAttribute('type', 'friend');

                    if (auth()->user() instanceof User) {
                        $addList = new Broadcast(collect([
                            'user' => $systemMsg->from_id,
                            'data' => [
                                "type" => 'addList',
                                "data" => [
                                    "type"     => 'friend',
                                    "avatar"   => auth()->user()->avatar,
                                    "username" => auth()->user()->nickname,
                                    "groupid"  => $systemMsg->friend_group_id,
                                    "id"       => auth()->user()->id,
                                    "sign"     => auth()->user()->sign,
                                ],
                            ],
                        ]));

                        $addList->setTries(3);

                        $success = Event::fire($addList);

                        if (!$success) throw new \Exception('error');
                    }
                } elseif ($systemMsg->status == SystemMessage::FRIEND_REFUSAL) {
                    $this->systemMessageRepo->create([
                        'user_id' => $systemMsg->from_id,
                        'from_id' => $systemMsg->user_id,
                        'type'    => SystemMessage::FRIEND_NOTICE,
                        'status'  => SystemMessage::FRIEND_REFUSAL,
                    ]);
                }

                $pushMsg = new Broadcast(collect([
                    'user' => $systemMsg->from_id,
                    'data' => [
                        "type"  => 'msgBox',
                        "count" => $this->systemMessageRepo->where([
                            'user_id' => $systemMsg->from_id,
                            'read'    => 0,
                        ])->count(),
                    ],
                ]));

                $pushMsg->setTries(3);

                $success = Event::fire($pushMsg);

                if (!$success) throw new \Exception('error');

                DB::commit();

                $data = [
                    'code' => 200,
                    'msg'  => $systemMsg->status == SystemMessage::FRIEND_REFUSAL ? '已拒绝' : 'success',
                ];

                if ($systemMsg->status == SystemMessage::FRIEND_ASSENT)
                    $data['data'] = new FriendUser($user);

                return response()->json($data);
            } catch (\Exception $e) {
                DB::rollback();

                return response()->json(
                    [
                        'code' => 500,
                        'msg'  => '请稍后再试!',
                    ]
                );
            }
        }
    }
}
