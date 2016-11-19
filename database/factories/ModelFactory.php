<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Carbon\Carbon;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'first_name'         => 'Johannes',
        'last_name'          => 'Lanstrom',
        'username'           => 'jlanstrom',
        'email'              => 'johannes@artificial.io',
        'role_id'            => 3,
        'token'              => str_random(30),
        'token_generated_at' => Carbon::now()
    ];
});