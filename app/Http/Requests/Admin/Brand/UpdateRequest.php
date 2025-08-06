<?php

namespace App\Http\Requests\Admin\Brand;

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
        $brandId = $this->route('brand')->id;
        return [
            'brand.title' => 'required|string|min:3|max:255|unique:brands,title,' . $brandId,
            'brand.slug' => 'required|string|min:3|max:255|unique:brands,slug,' . $brandId,
            'brand.description' => 'nullable|string',
            'brand.image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp',

            // Правила для вложенных полей JSON-структуры
            'brand.content' => 'nullable|array',
            'brand.content.subcat__title' => 'nullable|string|max:255',
            'brand.content.first__place' => 'nullable|array',
            'brand.content.first__place.*.0' => 'nullable|string|max:255', // Заголовок
            'brand.content.first__place.*.1' => 'nullable|string',        // Описание
            'brand.content.second__place' => 'nullable|array',
            'brand.content.second__place.*.0' => 'nullable|string|max:255',
            'brand.content.second__place.*.1' => 'nullable|string',
            'brand.content.faq__title' => 'nullable|string|max:255',
            'brand.content.faq__data' => 'nullable|array',
            'brand.content.faq__data.*.0' => 'nullable|string|max:255', // Вопрос
            'brand.content.faq__data.*.1' => 'nullable|string',        // Ответ

            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',

            'partTypes' => 'nullable|array',
            'partTypes.*' => 'integer|exists:part_types,id',
        ];
    }

    protected function passedValidation(): void
    {
        $validatedData = $this->validated();

        // Получаем старый путь к изображению, если он есть
        $oldImagePath = $this->route('brand')->image_path ?? null;

        // Загружаем новое изображение, если оно есть
        $newImagePath = $this->hasFile('brand.image') ?
            Storage::disk('public')->put('/images', $this->file('brand.image')) :
            null;

        // Удаляем старое изображение, если новое изображение загружено
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->merge([
            'brand' => [
                ...$validatedData['brand'],
                'image_path' => $newImagePath ?? $oldImagePath,
            ],
            'meta' => $validatedData['meta'] ?? [],
            'partTypes' => $validatedData['partTypes'] ?? [],
        ]);
    }
}
