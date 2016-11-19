<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function it_returns_a_jwt_token_for_valid_credentials()
    {
        $user = factory(\App\User::class)->create(['password' => bcrypt('password')]);

        $this->post('/login', [
            'username'     => $user->username,
            'password'     => 'sedasad'
        ])->assertResponseStatus(401);

        $this->post('/login', [
            'username'     => $user->username,
            'password'     => 'password'
        ])->assertResponseStatus(200)
            ->seeJsonStructure(['token']);
    }

    /** @test */
    function it_restricts_access_based_on_valid_jwt_token()
    {
        $this->get('me')->assertResponseStatus(400);

        $this->get('me?token='.$this->loginAndGetToken())->dump()->assertResponseStatus(200);
    }

    protected function loginAndGetToken()
    {
        $user = factory(\App\User::class)->create(['password' => bcrypt('password')]);

        return $this->post('/login', [
            'username'     => $user->username,
            'password'     => 'password'
        ])->decodeResponseJson()['token'];
    }
}
