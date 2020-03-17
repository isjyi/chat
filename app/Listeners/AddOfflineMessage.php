<?php


namespace App\Listeners;


use App\Models\OfflineMessage;
use App\Repositories\OfflineMessageRepositoryEloquent;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;

class AddOfflineMessage extends Listener
{
    // 声明没有参数的构造函数

    /**
     * AddOfflineMessage constructor.
     *
     * @param OfflineMessageRepositoryEloquent $offlineMessage
     */
    public function __construct()
    {
    }

    public function handle(Event $event)
    {
        \Log::info(__CLASS__ . ':handle start', [$event->getData()]);

        $data = $event->getData();

        $data->put('data', json_encode($data->get('data')));

        OfflineMessage::create($data->all());

        \Log::info(__CLASS__ . ':handle end');
    }
}
