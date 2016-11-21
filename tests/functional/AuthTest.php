<?php

use App\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(\App\User::class)->create();
    }

    /** @test */
    function it_returns_a_jwt_token_for_valid_credentials()
    {
        $this->post('/login', [
            'username'     => $this->user->username,
            'password'     => 'sedasad'
        ])->assertResponseStatus(401);

        $this->post('/login', [
            'username'     => $this->user->username,
            'password'     => 'password'
        ])->assertResponseStatus(200)
            ->seeJsonStructure(['token']);
    }

    /** @test */
    function it_restricts_access_based_on_valid_jwt_token()
    {
        $this->get('me')->assertResponseStatus(400);

        $this->get('me?token='.$this->loginAndGetToken($this->user))->assertResponseStatus(200);
    }

    /** @test */
    function it_blocks_suspended_users()
    {
        $this->user->active = false;
        $this->user->save();
        $this->get('me?token='.$this->loginAndGetToken($this->user))->assertResponseStatus(403);
    }

    /** @test */
    function non_admins_cannot_manage_user_accounts()
    {
        $this->get('users?token='.$this->loginAndGetToken($this->user))->assertResponseStatus(403);
    }

    /** @test */
    function admins_can_manage_user_accounts()
    {
        $this->user->withRole(factory(Role::class)->create((['name' => 'admin']))->id)->save();
        $token = $this->loginAndGetToken($this->user);

        $this->get('users?token='.$token)->assertResponseStatus(200);
    }
}
