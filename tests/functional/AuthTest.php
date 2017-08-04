<?php

use App\Notifications\ResetPassword;
use App\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(\App\User::class)->states('user')->create();
    }

    /** @test */
    function it_returns_a_jwt_token_for_valid_credentials()
    {
        $this->json('POST', '/auth/login', [
            'username' => $this->user->username,
            'password' => 'sedasad'
        ])->assertStatus(401);

        $this->json('POST', '/auth/login', [
            'username' => $this->user->username,
            'password' => 'password'
        ])->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    /** @test */
    function it_restricts_access_based_on_valid_jwt_token()
    {
        $this->json('GET', 'me')->assertStatus(400);

        $this->sendJsonForUser($this->user, 'GET', 'me')
            ->assertStatus(200);
    }

    /** @test */
    function it_blocks_suspended_users()
    {
        $this->user->active = false;
        $this->user->save();
        $this->sendJsonForUser($this->user, 'GET','me')
            ->assertStatus(403);
    }

    /** @test */
    function non_admins_cannot_manage_user_accounts()
    {
        $this->sendJsonForUser($this->user, 'GET', 'users')
            ->assertStatus(403);
    }

    /** @test */
    function admins_can_manage_user_accounts()
    {
        $this->user->withRole(factory(Role::class)->create((['name' => 'admin']))->id)->save();

        $this->sendJsonForUser($this->user, 'GET', 'users')
            ->assertStatus(200);
    }

    /** @test */
    function it_sends_password_reset_email()
    {
        Notification::fake();
        $this->json('PUT', 'password/email', ['email' => $this->user->email])
            ->assertStatus(200);
        Notification::assertSentTo($this->user, ResetPassword::class);
    }
    
    /** @test */
    function it_resets_password_if_given_a_valid_token()
    {
        Notification::fake();

        $this->json('PUT', 'password/email', ['email' => $this->user->email])
            ->assertStatus(200);

        $this->json('PUT', 'password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'adasds'
        ])->assertStatus(400);

        // Get token
        $token = '';
        Notification::assertSentTo([$this->user], ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });

        $response = $this->json('PUT', 'password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => $token
        ]);

        $response->assertStatus(200);
    }
    
    /** @test */
    function it_throttles_user_after_five_failed_login_attempts()
    {
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertStatus(401);
        $this->post('/auth/login', [
            'username'     => $this->user->username,
            'password'     => 'wrong-password'
        ])->assertStatus(429);
    }
    
    /** @test */
    function it_throttles_user_after_five_failed_reset_attempts()
    {
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertStatus(400);
        $this->put('password/reset', [
            'email' => $this->user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => 'wrong-token'
        ])->assertStatus(429);
    }
}
