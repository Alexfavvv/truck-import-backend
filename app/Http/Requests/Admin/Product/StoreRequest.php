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
            // Основные поля продукта
            'product.brand_id' => 'required|integer|exists:brands,id',
            'product.part_type_id' => 'required|integer|exists:part_types,id',
            'product.sku' => 'required|string|unique:products,sku|max:255',
            'product.title' => 'required|string|max:255',
            'product.description' => 'nullable|string',
            'product.price' => 'required|numeric|min:0|max:9999999999999.99',
            'product.quantity' => 'required|integer|min:0',
            'product.delivery' => 'nullable|string|max:255',
            'product.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',

            // Поля для SEO-блока
            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',

            // Поля для характеристик (повторяющиеся)
            'characteristics' => 'nullable|array',
            'characteristics.*.title' => 'nullable|string|max:255',
            'characteristics.*.description' => 'nullable|string|max:255',

            // Поле для совместимых производителей (пивотная таблица)
            'truckManufacturers' => 'nullable|array',
            'truckManufacturers.*' => 'integer|exists:truck_manufacturers,id',
        ];
    }

    public function passedValidation(): void
    {
        $validatedData = $this->validated();

        $this->merge([
            'product' => [
                ...$validatedData['product'],
                'image_path' => $this->hasFile('product.image') ?
                    Storage::disk('public')->put('/images', $this->file('product.image')) :
                    null,
            ],
            'meta' => $validatedData['meta'] ?? [],
            'characteristics' => $validatedData['characteristics'] ?? [],
            'truckManufacturers' => $validatedData['truckManufacturers'] ?? [],

        ]);
    }
}
