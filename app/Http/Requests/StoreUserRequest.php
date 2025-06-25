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
            'age' => 'nullable|integer',
            'gender' => 'nullable|in:male,female,other',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
        ];
    }
}
