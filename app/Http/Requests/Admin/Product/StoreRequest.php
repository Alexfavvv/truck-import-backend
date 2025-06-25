<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;


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
            'product.brand_id' =>'required|integer|exists:brands,id',
            'product.sku' =>'required|string|unique:products,sku',
            'product.title' =>'required|string|max:255',
            'product.description' =>'nullable|string',
            'product.price' =>'required|string|max:255',
            'product.quantity' =>'required|integer',
            'product.delivery' =>'nullable|string|max:255',
            'product.category_id' =>'required|integer|exists:categories,id',
            'product.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',

            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',

            'characteristics' => 'nullable|array',
            'characteristics.*.title' => 'nullable|string|max:255',
            'characteristics.*.description' => 'nullable|string|max:255',
        ];
    }

    public function passedValidation(): void
    {
        $this->merge([
            'product' => [
                ...$this->validated()['product'],
                'image_path' => $this->hasFile('product.image') ?
                    Storage::disk('public')->put('/images', $this->file('product.image')) :
                    null,
            ],

        ]);
    }
}
