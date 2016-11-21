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

Route::post('login', ['uses' => 'AuthenticateController@authenticate']);


Route::group(['middleware' => ['jwt.auth', 'jwt.refresh']], function () {
    Route::get('me', ['uses' => 'UserController@showMe']);
    Route::post('me', ['uses' => 'UserController@updateMe']);
});

Route::put('users/password/{token}', 'UserController@password');

/**
 * Admin restricted endpoints
 */
Route::group(['middleware' => ['jwt.auth', 'jwt.refresh', 'role:admin']], function () {
    Route::get('users', 'UserController@index');
    Route::get('users/{user}', 'UserController@show');
    Route::post('users/{user}', 'UserController@update');
    Route::post('users', 'UserController@store');
    Route::put('users/{user}/invite', 'UserController@resendInvite');
});