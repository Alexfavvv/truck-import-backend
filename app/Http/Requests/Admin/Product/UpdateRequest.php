<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'product.sku' =>'required|string|unique:products,sku,' . $this->route('product')->id,
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

    protected function passedValidation(): void
    {
        // Получаем старый путь к изображению, если он есть
        $oldImagePath = $this->route('product')->image_path ?? null;

        // Загружаем новое изображение, если оно есть
        $newImagePath = $this->hasFile('product.image') ?
            Storage::disk('public')->put('/images', $this->file('product.image')) :
            null;

        // Удаляем старое изображение, если новое изображение загружено
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->merge([
            'product' => [
                ...$this->validated()['product'],
                'image_path' => $newImagePath ?? $oldImagePath,
            ]
        ]);
    }
}
