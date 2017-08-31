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
        $this->adminUser = factory(\App\User::class)->states('user')->create();
        $this->regularUser = factory(\App\User::class)->states('admin')->create();

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

        $this->sendJsonForUser($this->adminUser, 'POST', 'users', $formData);

        array_pop($formData);
        $this->assertDatabaseHas('users', $formData);

        Mail::assertSent(Welcome::class, function ($mailable) use ($formData) {
            return $mailable->hasTo($formData['email']);
        });
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

        $this->sendJsonForUser($this->adminUser, 'POST', 'users', $formData)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['first_name', 'last_name']]);

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

        $this->json('PUT', 'password/create/' . $user->token, $formData);

        $this->assertFalse($user->fresh()->isPending());
    }

    /** @test */
    function it_returns_400_if_invalid_token()
    {
        $formData = [
            'password'              => 'foobarbaz',
            'password_confirmation' => 'foobarbaz'
        ];

        $this->json('PUT', 'password/create/adsasdsadad', $formData)
            ->assertStatus(400);
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

        $this->sendJsonForUser($this->regularUser, 'POST', 'me', $formData)
            ->assertStatus(200);

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

        $this->sendJsonForUser($this->adminUser, 'POST', 'users/' . $this->regularUser->id, $formData)
            ->assertStatus(200);

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
        $this->sendJsonForUser($this->adminUser, 'PUT', 'users/' . $user->id . '/invite')
            ->assertStatus(200);

        // Then the receives an invite email
        Mail::assertSent(Welcome::class, function($mailable) use ($user) {
            return $mailable->hasTo($user->email);
        });

        // And the user is "findable" by the token again
        $this->assertEquals(get_class($user), get_class(User::findByToken($user->fresh()->token)));
    }
}
