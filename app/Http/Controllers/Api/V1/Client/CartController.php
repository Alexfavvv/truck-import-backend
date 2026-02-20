<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Client\Cart\AddToCartRequest;
use App\Http\Requests\Api\V1\Client\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    #[OA\Get(
        path: '/v1/client/cart',
        summary: 'Получить корзину текущего пользователя',
        security: [['sanctum' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Корзина пользователя',
                content: new OA\JsonContent(ref: '#/components/schemas/Cart')
            ),
            new OA\Response(
                response: 401,
                description: 'Не авторизован',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
        ]
    )]
    public function show(Request $request): array
    {
        $user = $request->user();
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );

        $cart->load(['items.product.brand']);

        return CartResource::make($cart)->resolve();
    }

    #[OA\Post(
        path: '/v1/client/cart/items',
        summary: 'Добавить товар в корзину',
        security: [['sanctum' => []]],
        tags: ['Cart'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'quantity'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer', example: 42, description: 'ID товара'),
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1, maximum: 999, example: 2, description: 'Количество'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Обновлённая корзина',
                content: new OA\JsonContent(ref: '#/components/schemas/Cart')
            ),
            new OA\Response(
                response: 401,
                description: 'Не авторизован',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации (напр. недостаточно товара)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function add(AddToCartRequest $request): array
    {
        $user = $request->user();
        $data = $request->validated();

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );

        $product = Product::findOrFail($data['product_id']);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $data['quantity'];

            if ($product->quantity !== null && $newQuantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity . ', в корзине уже: ' . $cartItem->quantity,
                ]);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            if ($product->quantity !== null && $data['quantity'] > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity,
                ]);
            }

            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
            ]);
        }

        $cart->load(['items.product.brand']);

        return CartResource::make($cart)->resolve();
    }

    #[OA\Put(
        path: '/v1/client/cart/items/{cartItem}',
        summary: 'Обновить количество позиции в корзине',
        security: [['sanctum' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'cartItem',
                in: 'path',
                required: true,
                description: 'ID позиции корзины',
                schema: new OA\Schema(type: 'integer', example: 7)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1, maximum: 999, example: 3),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Обновлённая корзина',
                content: new OA\JsonContent(ref: '#/components/schemas/Cart')
            ),
            new OA\Response(response: 401, description: 'Не авторизован', content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')),
            new OA\Response(response: 403, description: 'Нет доступа к этой позиции'),
            new OA\Response(response: 404, description: 'Позиция не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): array
    {
        $user = $request->user();
        $data = $request->validated();

        $cartItem->load('cart');
        if ($cartItem->cart->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        $cartItem->load('product.brand');
        $product = $cartItem->product;

        if ($product->quantity !== null && $data['quantity'] > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity,
            ]);
        }

        $cartItem->update(['quantity' => $data['quantity']]);

        $cart = $cartItem->cart;
        $cart->load(['items.product.brand']);

        return CartResource::make($cart)->resolve();
    }

    #[OA\Delete(
        path: '/v1/client/cart/items/{cartItem}',
        summary: 'Удалить позицию из корзины',
        security: [['sanctum' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(
                name: 'cartItem',
                in: 'path',
                required: true,
                description: 'ID позиции корзины',
                schema: new OA\Schema(type: 'integer', example: 7)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Корзина без удалённой позиции',
                content: new OA\JsonContent(ref: '#/components/schemas/Cart')
            ),
            new OA\Response(response: 401, description: 'Не авторизован', content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')),
            new OA\Response(response: 403, description: 'Нет доступа к этой позиции'),
            new OA\Response(response: 404, description: 'Позиция не найдена'),
        ]
    )]
    public function remove(Request $request, CartItem $cartItem): array
    {
        $user = $request->user();

        $cartItem->load('cart');
        if ($cartItem->cart->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        $cart->load(['items.product.brand']);

        return CartResource::make($cart)->resolve();
    }

    #[OA\Delete(
        path: '/v1/client/cart',
        summary: 'Очистить корзину полностью',
        security: [['sanctum' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Пустая корзина',
                content: new OA\JsonContent(ref: '#/components/schemas/Cart')
            ),
            new OA\Response(response: 401, description: 'Не авторизован', content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')),
        ]
    )]
    public function clear(Request $request): array
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->load(['items.product.brand']);
        } else {
            $cart = Cart::create(['user_id' => $user->id]);
            $cart->load(['items.product.brand']);
        }

        return CartResource::make($cart)->resolve();
    }
}
