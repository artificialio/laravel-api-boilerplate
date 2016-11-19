<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->route('userByToken');
        // TODO(johannes) add regex for password that validates:
        //•	Passwords must not contain spaces.
        //•	Passwords must contain characters from the following categories:
        //•	Uppercase characters
        //•	Lowercase characters
        //•	Base 10 digits (0 through 9)
        //•	Non-alphanumeric characters: ~!@#$%^&*_-
        //• Passwords must not contain the whole username or email address of the user.

        return [
            'password' => 'required|confirmed|min:8'
        ];
    }
}
