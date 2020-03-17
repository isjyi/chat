<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function json($code = 200,$msg = '',$data = [])
    {
        $res = [
            'code'  =>$code,
            'msg'   =>$msg,
        ];

        !empty($data) ? $res['data'] = $data : null;

        return response()->json($res)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
