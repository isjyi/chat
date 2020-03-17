<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory("App\Models\User")->create([
            'id'       => 10000,
            'nickname' => 'admin',
            'username' => 'admin',
            'password' => bcrypt('123456'),
            'avatar'   => 'uploads/avatar/20200314/5e6cf4ebb2963.jpeg',
        ])->each(function ($u) {
            if ($u instanceof User) {
                $u->groups()->create([
                    'name'   => 'PHP交流群',
                    'id'     => '10001',
                    'avatar' => 'uploads/avatar/20190109/5c358bcaa77e3.jpeg',
                ]);
                $u->friend_group()->create([
                    'name' => '默认分组',
                ]);
            }
        });
    }
}
