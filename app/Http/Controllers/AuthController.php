<?php

namespace App\Http\Controllers;

use App\Event\Register;
use App\Models\User;
use DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use userRepo;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login', 'register']);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('api');
    }

    /**
     * Get a JWT via given credentials.AuthenticatesUsers
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = userRepo::where($this->username(), $request->input($this->username()))->first();

        if (!$user instanceof User)
            return response()->json(['msg' => '用户不存在'], 401);

        if (!$token = $this->attemptLogin($request, $user)) {
            return response()->json(['msg' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
//        $code = $request->input('key');
//
//        $code_value = Cache::get('image:'.$code);
//
//        if ($code_value != $request->input('code'))
//            return $this->json(402,'验证码错误');

        $user = userRepo::findByField($this->username(), $request->input($this->username()));

        if ($user instanceof User)
            return $this->json(402, '用户名已存在');
        try {
            DB::beginTransaction();

            $request->offsetSet('password', bcrypt($request->input('password')));

            $user = userRepo::create($request->all());

            event(new Register($user));

            DB::commit();

            return response()->json([
                'code'    => '200',
                'message' => 'success',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code'  => 402,
                'error' => $e->getMessage(),
            ]);
        }

    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json([
            'code'    => 200,
            'message' => 'success',
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'code'    => 200,
            'message' => 'success',
            'token'   => $token,
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function attemptLogin(Request $request, User $user)
    {
        $claims = collect();

        $claims->put('nickname', $user->nickname);
        $claims->put('avatar', $user->avatar);

        return $this->guard()->claims($claims->all())->attempt(
            $this->credentials($request)
        );
        //claims 添加额外参数
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }
}
