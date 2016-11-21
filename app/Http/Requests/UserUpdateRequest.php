<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $user = $this->route('user') ?: user();

        return [
            'username'          => 'alpha|unique:users,username,'.$user->id,
            'email'             => 'email|unique:users,email,'.$user->id,
            'role_id'           => 'integer',
            'organisations.*'   => 'integer',
            'password'          => 'min:8'
        ];
    }
}
