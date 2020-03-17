<?php


namespace App\Facade\Repositories;


use Illuminate\Support\Facades\Facade;

class User extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'userRepo';
    }
}
