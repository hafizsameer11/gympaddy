<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBusinessRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'business_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'address' => 'required|string',
            'business_email' => 'required|email|max:255',
            'business_phone' => 'required|string|max:30',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('Business creation validation failed', ['errors' => $validator->errors()]);
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'code' => 422,
            'message' => 'Validation Failed',
            'errors' => collect($validator->errors())->map(function ($messages, $field) {
                return [
                    'field' => $field,
                    'reason' => $messages[0],
                    'suggestion' => 'Please provide a valid value'
                ];
            })->values(),
        ], 422));
    }
}
