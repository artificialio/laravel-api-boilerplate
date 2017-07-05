<?php

use App\Role;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(\App\User::class)->create(['role_id' => factory(Role::class)->create(['name' => 'user'])->id]);
    }

    /** @test */
    function it_returns_a_jwt_token_for_valid_credentials()
    {
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'sedasad'
        ])->assertResponseStatus(401);

        $this->post('/auth/login', [
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

    /** @test */
    function it_sends_password_reset_email()
    {
        Notification::fake();
        $this->put('password/email', ['email' => $this->user->email])->assertResponseStatus(200);
        Notification::assertSentTo($this->user, ResetPassword::class);
    }
    
    /** @test */
    function it_resets_password_if_given_a_valid_token()
    {
        $this->put('password/email', ['email' => $this->user->email]);

        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'adasds'
        ])->assertResponseStatus(400);

        $token = DB::table('password_resets')->where('email', $this->user->email)->first()->token;
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => $token
        ])->assertResponseStatus(200);
    }
    
    /** @test */
    function it_throttles_user_after_five_failed_login_attempts()
    {
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertResponseStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertResponseStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertResponseStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertResponseStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertResponseStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertResponseStatus(429);
    }
    
    /** @test */
    function it_throttles_user_after_five_failed_reset_attempts()
    {
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertResponseStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertResponseStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertResponseStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertResponseStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertResponseStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertResponseStatus(429);
    }
}
