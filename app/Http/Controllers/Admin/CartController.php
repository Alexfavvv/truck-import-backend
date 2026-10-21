<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = Cart::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['user_id' => $request->user()->id]
        );
        $cart->load(['items.product.brand']);

        return view('admin.cart.index', compact('cart'));
    }

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
        $capped = [];

        foreach ($validated['items'] as $item) {
            try {
                $this->addItemToCart($cart, (int) $item['product_id'], (int) $item['quantity'], $capped);
            } catch (ValidationException $e) {
                $errors[] = Arr::first(Arr::flatten($e->errors())) ?? 'Ошибка валидации.';
            }
        }

        if (count($errors) > 0) {
            return response()->json([
                'message' => 'Ошибки при добавлении в корзину.',
                'errors' => $errors,
            ], 422);
        }

        $payload = [
            'message' => count($capped) > 0
                ? 'Товары в корзине; часть количества ограничена остатком на складе.'
                : 'Товары добавлены в корзину.',
            'success' => true,
        ];

        if (count($capped) > 0) {
            $payload['capped'] = $capped;
        }

        return response()->json($payload);
    }

    /**
     * @param  array<int, array{product_id: int, requested: int, applied: int, available: int|null}>  $cappedOut
     *
     * @throws ValidationException
     */
    private function addItemToCart(Cart $cart, int $productId, int $quantity, array &$cappedOut = []): void
    {
        $product = Product::findOrFail($productId);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $desired = $cartItem->quantity + $quantity;
            $applied = $desired;

            if ($product->quantity !== null) {
                $available = (int) $product->quantity;
                if ($available < 1) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Товар отсутствует на складе.',
                    ]);
                }
                if ($desired > $available) {
                    $applied = $available;
                    if ($applied !== $desired) {
                        $cappedOut[] = [
                            'product_id' => $product->id,
                            'requested' => $desired,
                            'applied' => $applied,
                            'available' => $available,
                        ];
                    }
                }
            }

            $cartItem->update(['quantity' => $applied]);
        } else {
            $applied = $quantity;

            if ($product->quantity !== null) {
                $available = (int) $product->quantity;
                if ($available < 1) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Товар отсутствует на складе.',
                    ]);
                }
                if ($quantity > $available) {
                    $applied = $available;
                    $cappedOut[] = [
                        'product_id' => $product->id,
                        'requested' => $quantity,
                        'applied' => $applied,
                        'available' => $available,
                    ];
                }
            }

            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $applied,
            ]);
        }
    }
}
