<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateController extends Controller
{
    use ThrottlesLogins {
        sendLockoutResponse as traitSendLockoutResponse;
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'username' => 'required', 'password' => 'required',
        ]);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        try {
            if (! $token = JWTAuth::attempt($request->only('username', 'password'))) {

                $this->incrementLoginAttempts($request);

                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {

            $this->incrementLoginAttempts($request);

            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );
        $message = Lang::get('auth.throttle', ['seconds' => $seconds]);

        return $this->tooManyRequests($message);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }
}