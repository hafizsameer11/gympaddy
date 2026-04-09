<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|nullable|string|max:255',
            'content' => 'sometimes|nullable|string',
            'media_url' => 'nullable|url',
            'media' => 'sometimes|array|max:10',
            'media.*' => 'file|mimes:jpeg,jpg,png,gif,mp4,mov,avi,wmv,flv|max:102400',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('Post update validation failed', ['errors' => $validator->errors()]);
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
