<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdCampaignRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'budget' => 'required|numeric|min:0.01',
            'status' => 'nullable|string|in:pending,active,paused,completed,rejected',
            // ...add other fields and constraints as needed...
        ];
    }
}
