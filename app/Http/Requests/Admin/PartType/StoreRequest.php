<?php

namespace App\Http\Requests\Admin\PartType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255|unique:part_types,title',
            'slug' => 'required|string|min:3|max:255|unique:part_types,slug',
        ];
    }
}
