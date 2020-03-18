<?php


namespace App\Services;


use App\Event\Broadcast;
use App\Models\ChatRecord;
use App\Models\Friend;
use App\Models\Group;
use App\Models\OfflineMessage;
use App\Models\User;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketService implements WebSocketHandlerInterface
{
    private $wsTable;

    // 声明没有参数的构造函数
    public function __construct()
    {
        $this->wsTable = app('swoole')->wsTable;
    }

    public function onOpen(Server $server, Request $request)
    {
        $user = auth('api')->user();

        if (!$user instanceof User) {
            $server->push($request->fd, json_encode(["type" => "token expire"]));

            $server->close($request->fd);

            return;
        }

        $this->wsTable->set('uid:' . $user->id, ["value" => $request->fd]);// 绑定uid到fd的映射

        $this->wsTable->set('fd:' . $request->fd, ["value" => $user->id]);// 绑定fd到uid的映射

        $user->status = 'online';

        $user->save();

        $users = $user->friends()->value('friend_id');

        $onlineNotice = new Broadcast(collect([
            'user' => $users,
            'data' => [
                "type"   => "friendStatus",
                "uid"    => $user->id,
                "status" => 'online',
            ],
        ]));

        $onlineNotice->setTries(3);
        // 这里可以做个try/catch
        Event::fire($onlineNotice);

        $user->offlineMessages()->where('status', 0)->get()->each(function ($msg) use ($request, $server) {
            $res = $server->push($request->fd, $msg->data);
            if ($res) {
                $msg->status = 1;
                $msg->save();
            }
        });

        $server->push($request->fd, json_encode([
            "type"  => "msgBox",
            "count" => $user->systemMessages()->where('read', 0)->count(),
        ]));
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $info = json_decode($frame->data);

        if (!isset($info->type)) return;

        $uid = app('swoole')->wsTable->get('fd:' . $frame->fd);

        if (!$uid) return;

        $user = User::find($uid['value']);

        if (!$user instanceof User) return;

        switch ($info->type) {
            case "chatMessage":
                $record = [
                    'user_id' => $info->data->mine->id,
                    'content' => $info->data->mine->content,
                ];

                if ($info->data->to->type == "friend") {
                    $data = [
                        'username'  => $info->data->mine->username,
                        'avatar'    => $info->data->mine->avatar,
                        'id'        => $info->data->mine->id,
                        'type'      => $info->data->to->type,
                        'content'   => $info->data->mine->content,
                        'cid'       => 0,
                        'mine'      => $user->id == $info->data->to->id ? true : false,//要通过判断是否是我自己发的
                        'fromid'    => $info->data->mine->id,
                        'timestamp' => time() * 1000,
                    ];

                    if ($info->data->to->id == $user->id) {
                        return;
                    }

                    $record['friend_id'] = $info->data->to->id;

                    $this->send($server, $info->data->to->id, $data, true);
                }
                if ($info->data->to->type == "group") {
                    $data = [
                        'username'  => $info->data->mine->username,
                        'avatar'    => $info->data->mine->avatar,
                        'id'        => $info->data->to->id,
                        'type'      => $info->data->to->type,
                        'content'   => $info->data->mine->content,
                        'cid'       => 0,
                        'mine'      => false,//要通过判断是否是我自己发的
                        'fromid'    => $info->data->mine->id,
                        'timestamp' => time() * 1000,
                    ];

                    $group = Group::find($info->data->to->id);

                    if ($group instanceof Group) {
                        $group->members->each(function ($u) use ($data, $user, $server) {
                            if ($u->user_id != $user->id)
                                $this->send($server, $u->user_id, $data, true);
                        });
                        $record['group_id'] = $info->data->to->id;
                    }
                }

                ChatRecord::create($record);
                break;
            default:
                break;
        }

    }

    public function onClose(Server $server, $fd, $reactorId)
    {
        $uid = app('swoole')->wsTable->get('fd:' . $fd);

        if ($uid) {
            $user = User::find($uid['value']);

            if ($user instanceof User) {
                $users = $user->friends()->value('friend_id');

                $OfflineNotice = new Broadcast(collect([
                    'user' => $users,
                    'data' => [
                        "type"   => "friendStatus",
                        "uid"    => $user->id,
                        "status" => 'offline',
                    ],
                ]));

                $OfflineNotice->setTries(3);
                // 这里可以做个try/catch
                Event::fire($OfflineNotice);

                app('swoole')->wsTable->del('uid:' . $uid['value']);// 解绑uid映射

                app('swoole')->wsTable->del('fd:' . $fd);// 解绑fd映射

                $user->status = 'offline';

                $user->save();
            }
        }
    }

    public function send(Server $server, int $uid, array $data, bool $offline_msg = false)
    {
        $fd = app('swoole')->wsTable->get('uid:' . $uid);//获取接受者fd
        if ($fd == false) {
            //这里说明该用户已下线，日后做离线消息用
            if ($offline_msg) {
                $data = [
                    'user_id' => $uid,
                    'data'    => json_encode($data),
                ];
                //插入离线消息
                OfflineMessage::create($data);
            }
            return false;
        }
        return $server->push($fd['value'], json_encode($data));//发送消息
    }
}
