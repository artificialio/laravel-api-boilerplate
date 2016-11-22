<?php

use App\Mail\Welcome;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;

class UserControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected $faker;
    protected $adminUser;
    protected $regularUser;

    public function setUp()
    {
        parent::setUp();
        $this->seed(OrganisationSeeder::class);
        $this->faker = Faker\Factory::create();
        $this->adminUser = factory(\App\User::class)->create(['role_id' => factory(Role::class)->create(['name' => 'admin'])->id]);
        $this->regularUser = factory(\App\User::class)->create(['role_id' => factory(Role::class)->create(['name' => 'user'])->id]);
        Mail::fake();
    }

    /** @test */
    function admins_can_create_and_assign_users_to_organisations()
    {
        $formData = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'username'   => $this->faker->unique()->firstName,
            'email'      => $this->faker->email,
            'role_id'    => 2,
            'organisations'  => [1,2]
        ];

        $this->json('post', 'users?token='.$this->loginAndGetToken($this->adminUser), $formData);
        array_pop($formData);
        $this->seeInDatabase('users', $formData);

        Mail::assertSentTo($formData['email'], Welcome::class);
    }
    
    /** @test */
    function user_creation_returns_422_on_invalid_data()
    {
        $formData = [
            'username'   => $this->faker->unique()->name,
            'email'      => $this->faker->email,
            'role_id'    => 2,
            'organisations'  => [1,2]
        ];

        $this->json('post', 'users?token='.$this->loginAndGetToken($this->adminUser), $formData)
            ->assertResponseStatus(422)
            ->seeJsonStructure(['first_name', 'last_name']);
    }

    /** @test */
    function it_sets_password_if_given_valid_token()
    {
        $user = factory(User::class)->create(['password' => null]);
        $this->assertTrue($user->isPending());

        $formData = [
            'password'              => 'foobarbaz',
            'password_confirmation' => 'foobarbaz'
        ];

        $this->put('users/password/'.$user->token, $formData);

        $this->assertFalse($user->fresh()->isPending());
    }

    /** @test */
    function it_returns_400_if_invalid_token()
    {
        $formData = [
            'password'              => 'foobarbaz',
            'password_confirmation' => 'foobarbaz'
        ];

        $this->json('put', 'users/password/adsasdsadad', $formData)->assertResponseStatus(400);
    }

    /** @test */
    function an_authenticated_user_can_read_and_update_their_account()
    {
        $formData = [
            'first_name' => $this->faker->unique()->firstName,
            'last_name'  => $this->faker->unique()->lastName,
            'username'   => $this->faker->unique()->firstName,
            'email'      => $this->faker->unique()->email,
        ];

        $token = $this->loginAndGetToken($this->regularUser);
        $this->call('POST','me?token='.$token, $formData);

        $this->assertResponseStatus(200);

        $this->assertEquals($formData['first_name'], $this->regularUser->fresh()->first_name);
        $this->assertEquals($formData['email'], $this->regularUser->fresh()->email);
    }

    /** @test */
    function admins_can_read_and_update_user_accounts()
    {
        $formData = [
            'first_name' => $this->faker->unique()->firstName,
            'last_name'  => $this->faker->unique()->lastName,
            'username'   => $this->faker->unique()->firstName,
            'email'      => $this->faker->unique()->email,
        ];

        $token = $this->loginAndGetToken($this->adminUser);
        $this->json('post', 'users/'.$this->regularUser->id.'?token='.$token, $formData)->assertResponseStatus(200);
        $this->assertEquals($formData['first_name'], $this->regularUser->fresh()->first_name);
        $this->assertEquals($formData['email'], $this->regularUser->fresh()->email);
    }

    /** @test */
    function an_admin_can_resend_expired_invites()
    {
        // Given that I have a user who's token has expired
        $user = factory(User::class)->create(['token_generated_at' => Carbon::now()->subDays(2)]);
        $this->assertEquals(null, User::findByToken($user->token));

        // When I resend my invitation for that user
        $this->put('users/'.$user->id.'/invite?token='.$this->loginAndGetToken($this->adminUser));

        // Then the receives an invite email
        Mail::assertSentTo($user->email, Welcome::class);

        // And the user is "findable" by the token again
        $this->assertEquals(get_class($user), get_class(User::findByToken($user->fresh()->token)));
    }
}