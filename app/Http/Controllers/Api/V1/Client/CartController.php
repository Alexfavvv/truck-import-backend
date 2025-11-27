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

class CartController extends Controller
{
    /**
     * Получить корзину пользователя
     */
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

    /**
     * Добавить товар в корзину
     */
    public function add(AddToCartRequest $request): array
    {
        $user = $request->user();
        $data = $request->validated();

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );

        $product = Product::findOrFail($data['product_id']);

        // Проверяем, есть ли уже этот товар в корзине
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Если товар уже есть, увеличиваем количество
            $newQuantity = $cartItem->quantity + $data['quantity'];
            
            // Проверка доступности при увеличении количества
            if ($product->quantity !== null && $newQuantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity . ', в корзине уже: ' . $cartItem->quantity,
                ]);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Проверка доступности товара перед созданием нового элемента
            if ($product->quantity !== null && $data['quantity'] > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity,
                ]);
            }

            // Создаем новый элемент корзины
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
            ]);
        }

        $cart->load(['items.product.brand']);

        return CartResource::make($cart)->resolve();
    }

    /**
     * Обновить количество товара в корзине
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): array
    {
        $user = $request->user();
        $data = $request->validated();

        // Загружаем корзину и проверяем, что элемент корзины принадлежит пользователю
        $cartItem->load('cart');
        if ($cartItem->cart->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        $cartItem->load('product.brand');
        $product = $cartItem->product;

        // Проверка доступности товара
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

    /**
     * Удалить товар из корзины
     */
    public function remove(Request $request, CartItem $cartItem): array
    {
        $user = $request->user();

        // Загружаем корзину и проверяем, что элемент корзины принадлежит пользователю
        $cartItem->load('cart');
        if ($cartItem->cart->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        $cart->load(['items.product.brand']);

        return CartResource::make($cart)->resolve();
    }

    /**
     * Очистить корзину
     */
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
