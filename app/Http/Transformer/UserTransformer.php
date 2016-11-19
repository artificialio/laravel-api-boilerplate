<?php
namespace App\Http\Transformer;

use App\User;
use League\Fractal;

class UserTransformer extends Fractal\TransformerAbstract
{
    public function transform(User $user)
    {
        return $user->toArray();
    }
}