<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPasswordRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Mail\Welcome;
use App\Role;
use App\Http\Transformer\UserTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index()
    {
        return $this->paginatedCollection(User::paginate(15), new UserTransformer);
    }

    public function store(UserRequest $request, User $user)
    {
        $user->fill($request->only('first_name', 'last_name', 'username', 'email'))
            ->withRole($request->get('role_id'))
            ->createToken()
            ->save();

        $user->addOrganisations($request->get('organisations'));

        Mail::to($user->email)->send(new Welcome($user));

        return $this->item($user, new UserTransformer);
    }

    public function password($token, UserPasswordRequest $request)
    {
        if (! $token) return $this->badRequest('no token provided');
        if (! $user = User::findByToken($token)) return $this->badRequest('could not find user by token');

        $user->token = null;
        $user->token_generated_at = null;
        $user->password = bcrypt($request->get('password'));
        $user->active = true;
        $user->save();

        return $this->item($user, new UserTransformer);
    }

    public function show(User $user)
    {
        return $this->item($user, new UserTransformer);
    }

    public function showMe()
    {
        return $this->item(user(), new UserTransformer);
    }

    public function update(User $user, UserUpdateRequest $request)
    {
        $user->fill($request->only('first_name', 'last_name', 'email', 'active'))
            ->withRole($request->get('role_id'))
            ->save();

        $user->addOrganisations($request->get('organisations'));

        return $this->item($user, new UserTransformer);
    }

    public function updateMe(UserUpdateRequest $request)
    {
        $user = user();
        $user->fill($request->only('first_name', 'last_name', 'email', 'username', 'email'))
            ->withPassword($request->get('password'))
            ->save();

        return $this->item($user, new UserTransformer);
    }

    public function resendInvite(User $user)
    {
        if (! $user->hasRole('user')) return $this->badRequest('only regular users can be invited');

        $user->createToken()->save();

        Mail::to($user->email)->send(new Welcome($user));

        return $this->item($user, new UserTransformer);
    }
}