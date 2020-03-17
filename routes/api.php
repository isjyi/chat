<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'middleware' => 'api',
    'prefix'     => 'auth',
], function ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');
    $router->post('register', 'AuthController@register');
});

Route::group(['middleware' => 'jwt-refresh'], function ($route) {
    $route->get("me", 'UserController@me');
    $route->post("update/sign", 'UserController@updateSign');
    $route->post("join/group/{id}", 'GroupController@joinGroup');
    $route->post('/create/group', 'GroupController@createGroup');
    $route->post('/friend', 'FriendController@add');
    $route->post('/friend/{id}', 'FriendController@operate');
    $route->get('/chat_record_data/{id}/{type}', 'IndexController@chatRecordData');
    $route->get('/group_members', 'GroupController@groupMember');
});
