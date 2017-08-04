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
        'first_name'         => $faker->unique()->firstName,
        'last_name'          => $faker->unique()->lastName,
        'username'           => $faker->unique()->name,
        'email'              => $faker->unique()->email,
        'role_id'            => factory(App\Role::class)->create()->id,
        'token'              => str_random(30),
        'token_generated_at' => Carbon::now(),
        'active'             => true,
        'password'           => bcrypt('password')
    ];
});

$factory->define(App\Role::class, function (Faker\Generator $faker) {
    return [
        'name'         => 'user '.$faker->unique()->name,
        'display_name' => 'User',
    ];
});
