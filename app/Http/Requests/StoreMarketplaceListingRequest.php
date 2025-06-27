<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMarketplaceListingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => '``nullable``|integer|exists:marketplace_categories,id',
            'price' => 'required|numeric|min:0.01',
            'location' => 'required|string|max:255',
            'media_files' => 'required|array|max:4',
            'media_files.*' => 'file|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB per file
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        \Log::warning('MarketplaceListing creation validation failed', ['errors' => $validator->errors()]);
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
