<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Replace with your authorization logic, e.g., checking if the user is an admin.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            // Основные поля продукта
            'product.brand_id' => 'required|integer|exists:brands,id',
            'product.part_type_id' => 'required|integer|exists:part_types,id',
            'product.sku' => 'required|string|unique:products,sku,' . $productId . '|max:255',
            'product.title' => 'required|string|max:255',
            'product.description' => 'nullable|string',
            'product.price' => 'required|numeric|min:0',
            'product.quantity' => 'required|integer|min:0',
            'product.delivery' => 'nullable|string|max:255',
            'product.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',

            // SEO-поля
            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',

            // Характеристики (повторяющиеся поля)
            'characteristics' => 'nullable|array',
            'characteristics.*.id' => 'nullable|integer|exists:characteristics,id',
            'characteristics.*.title' => 'nullable|string|max:255',
            'characteristics.*.description' => 'nullable|string|max:255',

            // Совместимые производители (пивотная таблица)
            'truckManufacturers' => 'nullable|array',
            'truckManufacturers.*' => 'integer|exists:truck_manufacturers,id',
        ];
    }

    /**
     * Handle cleanup and data formatting before validation passes.
     */
    public function passedValidation(): void
    {
        $validatedData = $this->validated();
        $product = $this->route('product');

        // Получаем старый путь к изображению, если он есть
        $oldImagePath = $product->image_path ?? null;

        // Загружаем новое изображение, если оно есть
        $newImagePath = $this->hasFile('product.image') ?
            Storage::disk('public')->put('/images', $this->file('product.image')) :
            null;

        // Удаляем старое изображение, если новое изображение загружено
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        // Merge updated data for the product model
        $this->merge([
            'product' => [
                ...$validatedData['product'],
                'image_path' => $newImagePath ?? $oldImagePath,
            ],
            'meta' => $validatedData['meta'] ?? [],
            'characteristics' => $validatedData['characteristics'] ?? [],
            'truckManufacturers' => $validatedData['truckManufacturers'] ?? [],
        ]);
    }
}
