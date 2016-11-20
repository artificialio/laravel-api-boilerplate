<?php

use App\Role;
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

        $this->get('me?token='.$this->loginAndGetToken())->assertResponseStatus(200);
    }

    /** @test */
    function non_admins_cannot_manage_user_accounts()
    {
        $this->get('admin/users?token='.$this->loginAndGetToken())->assertResponseStatus(403);
    }

    /** @test */
    function admins_can_manage_user_accounts()
    {
        $adminRole = factory(Role::class)->create(['name' => 'admin']);
        $token = $this->loginAndGetToken($adminRole);
        $this->get('admin/users?token='.$token)->assertResponseStatus(200);
        $token2 = $this->loginAndGetToken($adminRole);
        $this->get('admin/users?token='.$token2)->assertResponseStatus(200);
    }

    protected function loginAndGetToken($role = null)
    {
        $role = $role ?: factory(Role::class)->create(['name' => 'user']);
        $user = factory(\App\User::class)->create(['password' => bcrypt('password'), 'role_id' => $role->id]);

        return $this->post('/login', [
            'username'     => $user->username,
            'password'     => 'password'
        ])->decodeResponseJson()['token'];
    }
}
