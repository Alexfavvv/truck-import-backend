<?php

namespace App\Http\Requests\Api\V1\Client\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'price' => 'nullable|string',
            'limit' => 'nullable|integer',
            'page' => 'nullable|integer',
            'brand' => 'nullable',
            'brand.*' => 'string',
            'model' => 'nullable|string',
            'part' => 'nullable|string',
            'sku' => 'nullable|string',
        ];
    }
}
