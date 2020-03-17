<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => 'jwt-refresh'], function ($route) {
    $route->get('/ws', function () {
    });
    $route->get('/find', 'FriendController@find');
    $route->get('/message_box', 'MessageBoxController@messageBox');
    $route->get('/chat_log', 'IndexController@chatLog');
});
Route::get('/', 'IndexController@index');
Route::get('/login', ['uses' => 'IndexController@login']);
Route::get('/register', ['uses' => 'IndexController@register']);
Route::get('/image_code', ['uses' => 'IndexController@imageCode']);
Route::get('/create/group', ['uses' => 'IndexController@createGroup']);
Route::post('/upload', ['uses' => 'FileController@upload']);



