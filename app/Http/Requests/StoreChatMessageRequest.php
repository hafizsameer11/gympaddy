<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'receiver_id' => 'required_without:listing_id|integer|exists:users,id',
            'message' => 'required|string',
            'listing_id' => 'nullable|integer|exists:marketplace_listings,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('ChatMessage creation validation failed', ['errors' => $validator->errors()]);
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'code' => 422,
            'message' => 'Validation Failed',
            'errors' => collect($validator->errors())->map(function($messages, $field) {
                return [
                    'field' => $field,
                    'reason' => $messages[0],
                    'suggestion' => 'Please provide a valid value'
                ];
            })->values(),
        ], 422));
    }
}
