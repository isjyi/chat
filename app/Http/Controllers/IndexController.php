<?php

namespace App\Http\Controllers;

use App\Models\ChatRecord;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Type;

class IndexController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function login(Request $request)
    {
        return view('login');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('index');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function register(Request $request)
    {
        $code_hash = uniqid() . uniqid();
        return view('register', ['code_hash' => $code_hash]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function imageCode(Request $request)
    {
        $key = $request->input('key');
        return getValidate(210, 70, $key);
    }

    public function createGroup(Request $request)
    {
        return view('create_group');
    }

    public function chatLog(Request $request)
    {
        $id   = $request->get('id');
        $type = $request->get('type');
        return view('chat_log', ['id' => $id, 'type' => $type]);
    }

    public function chatRecordData(Request $request, int $id, string $type)
    {
        if ($type == 'group') {
            $list = ChatRecord::select('id', 'user_id', 'content', 'created_at')->with(['user' => function ($q) {
                $q->select('id', 'nickname', 'avatar');
            }])->where('group_id', $id)->orderBy('created_at', 'DESC')->paginate(10);
        } else {

            $list = ChatRecord::select('id', 'user_id', 'content', 'created_at')->with(['user' => function ($q) {
                $q->select('id', 'nickname', 'avatar');
            }])->where([
                'user_id'   => auth()->user()->id,
                'friend_id' => $id,
            ])->orWhere([
                'user_id'   => $id,
                'friend_id' => auth()->user()->id,
            ])->orderBy('created_at', 'DESC')->paginate(10);
        }
        $list->each(function ($item) {
            $item->time = $item->created_at->unix();
        });
        return $this->json(0, '', $list);

    }

}
