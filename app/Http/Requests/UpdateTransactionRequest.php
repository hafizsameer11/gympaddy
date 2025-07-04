<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'type' => 'sometimes|required|in:topup,withdraw,gift,purchase,ad,other',
            'reference' => 'nullable|string|max:255',
            'related_user_id' => 'nullable|integer|exists:users,id',
            'meta' => 'nullable',
            'status' => 'nullable|string|max:50',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('Transaction update validation failed', ['errors' => $validator->errors()]);
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
