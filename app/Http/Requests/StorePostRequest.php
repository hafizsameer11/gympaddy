<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|mimes:jpeg,jpg,png,gif,mp4,mov,avi,wmv,flv|max:102400', // 100MB max
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure at least content or media is provided
            if (empty($this->content) && empty($this->media)) {
                $validator->errors()->add('content', 'Either content or media must be provided.');
            }

            // Ensure only one video per post
            if ($this->hasFile('media')) {
                $videoCount = 0;
                foreach ($this->file('media') as $file) {
                    if (str_starts_with($file->getMimeType(), 'video/')) {
                        $videoCount++;
                    }
                }
                if ($videoCount > 1) {
                    $validator->errors()->add('media', 'Only one video file is allowed per post.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('Post creation validation failed', ['errors' => $validator->errors()]);
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
