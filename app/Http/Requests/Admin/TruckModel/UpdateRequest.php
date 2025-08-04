<?php

namespace App\Http\Requests\Admin\TruckModel;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $truckModelId = $this->route('truck_model')->id;
        return [
            'truckModel.brand_id' => 'required|exists:brands,id',
            'truckModel.title' => 'required|string|min:3|max:255|unique:truck_models,title,' . $truckModelId,
            'truckModel.slug' => 'required|string|min:3|max:255|unique:truck_models,slug,' . $truckModelId,
            'truckModel.description' => 'nullable|string',
            'truckModel.content' => 'nullable|string',
            'truckModel.image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp',

            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',
        ];
    }

    protected function passedValidation(): void
    {
        $validatedData = $this->validated();

        // Получаем старый путь к изображению, если он есть
        $oldImagePath = $this->route('truck_model')->image_path ?? null;

        // Загружаем новое изображение, если оно есть
        $newImagePath = $this->hasFile('truckModel.image') ?
            Storage::disk('public')->put('/images', $this->file('truckModel.image')) :
            null;

        // Удаляем старое изображение, если новое изображение загружено
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->merge([
            'truckModel' => [
                ...$this->validated()['truckModel'],
                'image_path' => $newImagePath ?? $oldImagePath,
            ],
            'meta' => $validatedData['meta'] ?? [],
        ]);
    }
}
