<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Добавить выбранные товары в корзину текущего пользователя (админ).
     * Тело запроса: { "items": [ { "product_id": 1, "quantity": 2 }, ... ] }
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );

        $errors = [];

        foreach ($validated['items'] as $item) {
            try {
                $this->addItemToCart($cart, (int) $item['product_id'], (int) $item['quantity']);
            } catch (ValidationException $e) {
                $errors[] = $e->errors()->first();
            }
        }

        if (count($errors) > 0) {
            return response()->json([
                'message' => 'Ошибки при добавлении в корзину.',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'message' => 'Товары добавлены в корзину.',
            'success' => true,
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function addItemToCart(Cart $cart, int $productId, int $quantity): void
    {
        $product = Product::findOrFail($productId);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;

            if ($product->quantity !== null && $newQuantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity . ', в корзине уже: ' . $cartItem->quantity,
                ]);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            if ($product->quantity !== null && $quantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Недостаточно товара на складе. Доступно: ' . $product->quantity,
                ]);
            }

            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }
    }
}
