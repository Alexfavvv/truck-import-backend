<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'brand.title' => 'required|string|min:3|max:255|unique:brands,title',
            'brand.slug' => 'required|string|min:3|max:255|unique:brands,slug',
            'brand.description' => 'nullable|string',
            'brand.content' => 'nullable|string',
            'brand.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',

            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',
        ];
    }

    public function passedValidation(): void
    {
        $validatedData = $this->validated();

        $this->merge([
            'brand' => [
                ...$validatedData['brand'],
                'image_path' => $this->hasFile('brand.image') ?
                    Storage::disk('public')->put('/images', $this->file('brand.image')) :
                    null,
            ],
            'meta' => $validatedData['meta'] ?? [],

        ]);
    }
}
