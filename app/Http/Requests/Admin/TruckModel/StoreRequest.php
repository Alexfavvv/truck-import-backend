<?php

namespace App\Http\Requests\Admin\TruckModel;

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
            'truckModel.brand_id' => 'required|exists:brands,id',
            'truckModel.title' => 'required|string|min:3|max:255|unique:truck_models,title',
            'truckModel.slug' => 'required|string|min:3|max:255|unique:truck_models,slug',
            'truckModel.description' => 'nullable|string',
            'truckModel.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',

            // Правила валидации для вложенного поля 'content'
            'truckModel.content' => 'nullable|array',
            'truckModel.content.faq__title' => 'nullable|string|max:255',
            'truckModel.content.faq__data' => 'nullable|array',

            // Правила для каждого элемента массива faq__data
            'truckModel.content.faq__data.*.0' => 'nullable|string', // Вопрос
            'truckModel.content.faq__data.*.1' => 'nullable|string', // Ответ

            'truckModel.content.first__place' => 'nullable|array|max:3',
            // Правила для каждого элемента массива first__place
            'truckModel.content.first__place.*.0' => 'nullable|string|max:255',
            'truckModel.content.first__place.*.1' => 'nullable|string',

            'truckModel.content.second__place' => 'nullable|array|max:3',
            // Правила для каждого элемента массива second__place
            'truckModel.content.second__place.*.0' => 'nullable|string|max:255',
            'truckModel.content.second__place.*.1' => 'nullable|string',

            'meta.meta_h1' => 'nullable|string|max:255',
            'meta.meta_title' => 'nullable|string|max:255',
            'meta.meta_description' => 'nullable|string|max:500',
        ];
    }

    public function passedValidation(): void
    {
        $validatedData = $this->validated();

        $this->merge([
            'truckModel' => [
                ...$validatedData['truckModel'],
                'image_path' => $this->hasFile('truckModel.image') ?
                    Storage::disk('public')->put('/images', $this->file('truckModel.image')) :
                    null,
            ],
            'meta' => $validatedData['meta'] ?? [],
        ]);
    }
}
