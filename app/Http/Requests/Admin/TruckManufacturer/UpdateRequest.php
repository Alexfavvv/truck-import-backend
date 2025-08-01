<?php

namespace App\Http\Requests\Admin\TruckManufacturer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        // Получаем ID производителя из маршрута для корректной проверки уникальности.
        $manufacturerId = $this->route('truck_manufacturer')->id;

        return [
            'title' => 'required|string|min:3|max:255|unique:truck_manufacturers,title,' . $manufacturerId,
            'slug' => 'required|string|min:3|max:255|unique:truck_manufacturers,slug,' . $manufacturerId,
        ];
    }
}
