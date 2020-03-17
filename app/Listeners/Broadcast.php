<?php


namespace App\Listeners;


use App\Event\OfflineMessage;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use Illuminate\Support\Collection;

class Broadcast extends Listener
{
    private $swoole;
    private $wsTable;

    // 声明没有参数的构造函数
    public function __construct()
    {
        $this->swoole  = app('swoole');
        $this->wsTable = $this->swoole->wsTable;
    }

    public function handle(Event $event)
    {
        \Log::info(__CLASS__ . ':handle start', [$event->getData()]);

        $data = $event->getData();

        $user = collect($data->get('user'));

        $user->each(function ($user) use ($data) {
            $fd = $this->wsTable->get('uid:' . $user);//获取接受者fd

            if ($fd) {
                $this->swoole->push($fd['value'], json_encode($data->get('data')));
                \Log::info(__CLASS__ . ':debug', [$fd, $data->get('data')]);
            } else {
                $event = new OfflineMessage(collect([
                    'user_id' => $user,
                    'data'    => $data->get('data'),
                ]));

                $event->setTries(3);

                Event::fire($event);
            }


        });

        \Log::info(__CLASS__ . ':handle end');
    }
}
