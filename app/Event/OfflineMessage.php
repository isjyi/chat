<?php


namespace App\Event;


use Hhxsv5\LaravelS\Swoole\Task\Event;
use Illuminate\Support\Collection;

class OfflineMessage extends Event
{
    public $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
