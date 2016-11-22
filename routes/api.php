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

Route::post('auth/login', ['uses' => 'Auth\AuthenticateController@authenticate']);

Route::group(['prefix' => 'password'], function (){
    Route::put('create/{token}', 'UserController@password');
    Route::put('email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::put('reset', 'Auth\ResetPasswordController@reset');
});


Route::group(['middleware' => ['jwt.auth', 'jwt.refresh']], function () {
    Route::get('me', ['uses' => 'UserController@showMe']);
    Route::post('me', ['uses' => 'UserController@updateMe']);
});




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