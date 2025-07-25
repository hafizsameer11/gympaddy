<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMarketplaceListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
             'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:marketplace_categories,id',
            'price' => 'nullable|numeric|min:0.01',
            'location' => 'nullable|string|max:255',
            'media_files' => 'nullable|array|max:4',
            'media_files.*' => 'file|image|mimes:jpeg,png,jpg,webp|max:5120',
            // ...add other fields and constraints as needed...
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('MarketplaceListing update validation failed', ['errors' => $validator->errors()]);
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
