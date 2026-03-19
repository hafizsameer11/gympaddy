<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|string|unique:users,username',
            'fullname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'age' => 'required|integer|min:18|max:120',
            'gender' => 'nullable|in:male,female,other',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
        ];
    }
}
