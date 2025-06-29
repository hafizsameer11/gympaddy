<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreFollowRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'follower_id' => 'required|integer',
            'followable_id' => 'required|integer',
            'followable_type' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::warning('Follow creation validation failed', ['errors' => $validator->errors()]);
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
