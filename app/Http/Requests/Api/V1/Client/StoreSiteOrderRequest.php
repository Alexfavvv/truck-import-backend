<?php

namespace App\Http\Requests\Api\V1\Client;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSiteOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', Order::class);
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'comment' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first() ?: 'Ошибка валидации.',
            'code' => 'VALIDATION_ERROR',
            'errors' => $validator->errors(),
        ], 422));
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Недостаточно прав для создания заказа.',
            'code' => 'FORBIDDEN',
        ], 403));
    }
}
