<?php

namespace App\Http\Controllers;

use App\Http\Resources\User;
use App\Models\FriendGroup;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return response()->json([
            'code' => 0,
            'msg'  => '',
            'data' => new User(auth()->user()),
        ]);
    }

    public function updateSign(Request $request)
    {
        $user = auth('api')->user();

        if ($user instanceof \App\Models\User) {
            $user->sign = $request->input('sign');

            $user->save();

            return response()->json([
                'code' => 200,
                'msg'  => '签名修改成功',
            ]);
        }

        return response()->json([
            'code' => 500,
            'msg'  => '签名修改失败',
        ]);
    }
}
