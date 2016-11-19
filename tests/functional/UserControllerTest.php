<?php

use App\Mail\Welcome;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(OrganisationSeeder::class);
        Mail::fake();
    }

    /** @test */
    function admins_can_create_users_and_assign_them_to_orgs()
    {
        $formData = [
            'first_name' => 'Johannes',
            'last_name'  => 'Lanstrom',
            'username'   => 'jlanstrom',
            'email'      => 'johannes@artificial.io',
            'role_id'    => 2,
            'organisations'  => [1,2]
        ];

        $this->post('admin/users', $formData);
        $organisations = array_pop($formData);

        $user = User::first();
        $this->seeInDatabase('users', $formData);
        $this->assertEquals('manager', $user->role->name);
        $this->assertEquals($organisations, $user->organisations->pluck('id')->all());
    }

    /** @test */
    function it_sends_out_welcome_email_to_invited_user()
    {
        $formData = [
            'first_name' => 'Johannes',
            'last_name'  => 'Lanstrom',
            'username'   => 'jlanstrom',
            'email'      => 'johannes@artificial.io',
            'role_id'    => 2,
            'organisations'  => [1,2]
        ];
        $this->post('admin/users', $formData);

        Mail::assertSentTo($formData['email'], Welcome::class);
    }


    /** @test */
    function it_sets_password_if_given_valid_token()
    {
        $user = factory(User::class)->create();
        $this->assertTrue($user->isPending());
        $formData = [
            'password'              => 'foobarbaz',
            'password_confirmation' => 'foobarbaz'
        ];

        $this->put('admin/users/password/'.$user->token, $formData);

        $this->assertFalse($user->fresh()->isPending());
    }

    /** @test */
    function it_returns_404_if_invalid_token()
    {
        $user = factory(User::class)->create();
        $formData = [
            'password'              => 'foobarbaz',
            'password_confirmation' => 'foobarbaz'
        ];

        $this->put('admin/users/password/adsasdsadad', $formData)->assertResponseStatus(404);
    }
}