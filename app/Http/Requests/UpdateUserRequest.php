<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('id');
        return [
            'username' => 'sometimes|string|unique:users,username,' . $userId,
            'fullname' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone' => 'sometimes|string|unique:users,phone,' . $userId,
            'age' => 'nullable|integer',
            'gender' => 'nullable|in:male,female,other',
            'password' => 'sometimes|string|min:6',
            'role' => 'nullable|string',
        ];
    }
}
