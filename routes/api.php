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

Route::get('me', ['middleware' => ['jwt.auth', 'jwt.refresh'], 'uses' => 'UserController@me']);

/**
 * Routes for the admin operations
 */
Route::group(['prefix' => 'admin', 'middleware' => ['jwt.auth', 'jwt.refresh', 'role:admin']], function () {
    Route::get('users', 'UserController@index');
    Route::post('users', 'UserController@store');
    Route::put('users/password/{userByToken}', 'UserController@password');
});