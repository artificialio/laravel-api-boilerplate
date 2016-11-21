<?php
namespace App\Http\Transformer;

use App\User;
use League\Fractal;

class UserTransformer extends Fractal\TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'email' => $user->email,
            'active' => $user->active_formatted,
            'role' => $user->role->display_name
        ];
    }
}