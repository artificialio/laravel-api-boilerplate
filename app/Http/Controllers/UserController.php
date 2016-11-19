<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPasswordRequest;
use App\Http\Requests\UserRequest;
use App\Mail\Welcome;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        $user = new User($request->all());
        $user->token = str_random(30);
        $user->token_generated_at = Carbon::now();
        $user->role()->associate(Role::findOrFail($request->get('role_id')));
        $user->save();
        $user->organisations()->attach($request->get('organisations'));

        Mail::to($user->email)->send(new Welcome($user));

        // Maybe return the created user (user transformer)?
        return response('User Invited', 201);
    }

    public function password(User $user, UserPasswordRequest $request)
    {
        $user->token = null;
        $user->token_generated_at = null;
        $user->password = bcrypt($request->get('password'));
        $user->active = true;
        $user->save();

        return response('Password created', 201);
    }

    public function me()
    {
        return;
    }
}