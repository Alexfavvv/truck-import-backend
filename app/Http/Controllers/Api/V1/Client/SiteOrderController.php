<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Client\StoreSiteOrderRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SiteOrderController extends Controller
{
    #[OA\Post(
        path: '/v1/client/orders',
        summary: 'Создать заказ с витрины (checkout)',
        description: 'Заказчик определяется по **Bearer-токену**. Контакты в примечание к заказу берутся из профиля пользователя. Позиции: **`sku`** (как в `products.sku`) + `quantity`. Цены строк — из каталога.',
        security: [['sanctum' => []]],
        tags: ['Orders'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(property: 'comment', type: 'string', nullable: true, description: 'Доп. комментарий к заказу'),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            required: ['sku', 'quantity'],
                            properties: [
                                new OA\Property(property: 'sku', type: 'string', description: 'Должен совпадать с products.sku'),
                                new OA\Property(property: 'quantity', type: 'integer', minimum: 1),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Заказ создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'order_id', type: 'integer'),
                        new OA\Property(property: 'order_number', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 403, description: 'Нет права создавать заказы'),
            new OA\Response(response: 404, description: 'Товар по sku не найден в каталоге'),
            new OA\Response(response: 409, description: 'Недостаточно товара на складе'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreSiteOrderRequest $request, OrderService $orderService): JsonResponse
    {
        $validated = $request->validated();

        $quantitiesBySku = [];
        foreach ($validated['items'] as $row) {
            $sku = $row['sku'];
            $quantitiesBySku[$sku] = ($quantitiesBySku[$sku] ?? 0) + $row['quantity'];
        }

        $skus = array_keys($quantitiesBySku);
        $products = Product::query()->whereIn('sku', $skus)->get()->keyBy('sku');

        $missing = array_values(array_diff($skus, $products->keys()->all()));
        if ($missing !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Товары не найдены в каталоге: ' . implode(', ', $missing),
                'code' => 'PRODUCT_NOT_FOUND',
            ], 404);
        }

        $items = [];
        foreach ($quantitiesBySku as $sku => $qty) {
            $product = $products->get($sku);
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => (float) $product->price,
            ];
        }

        $notes = $this->formatProfileNotes($request->user(), $validated['comment'] ?? null);

        $order = $orderService->createOrder($request->user(), [
            'notes' => $notes,
            'items' => $items,
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ], 200);
    }

    private function formatProfileNotes(User $user, ?string $comment): string
    {
        $lines = [
            'Заказ с витрины (API)',
            'Пользователь id: ' . $user->id,
            'Логин: ' . ($user->login ?? ''),
            'ФИО: ' . ($user->full_name ?: $user->name ?: ''),
            'Телефон: ' . ($user->phone ?? ''),
            'Email: ' . ($user->email ?? ''),
        ];

        if (! empty($user->company)) {
            $lines[] = 'Компания: ' . $user->company;
        }

        if ($comment !== null && $comment !== '') {
            $lines[] = 'Комментарий: ' . $comment;
        }

        return implode("\n", $lines);
    }
}
