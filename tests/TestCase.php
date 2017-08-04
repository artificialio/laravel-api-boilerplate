<?php

use App\Role;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Helper function which authenticates and retrieves the token for a specified user
     *
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function sendJsonForUser(User $user, $method, $uri, array $data = [], array $headers = [])
    {
        $token = JWTAuth::fromUser($user);
        return $this->json($method, $uri, $data, array_merge($headers, [
            'Authorization' => 'Bearer ' . $token
        ]));
    }

    public function loginAndGetToken(User $user)
    {
        return $this->post('/auth/login', [
            'username' => $user->username,
            'password' => 'password'
        ])->decodeResponseJson()['token'];
    }
}
