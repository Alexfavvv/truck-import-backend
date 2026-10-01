<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientCartQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_quantity_can_be_changed_from_one_to_two_to_five_and_persists_on_reload(): void
    {
        $product = Product::factory()->withoutImage()->create(['quantity' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/client/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertOk();

        $item = CartItem::query()->where('product_id', $product->id)->firstOrFail();
        $this->putJson("/api/v1/client/cart/items/{$item->id}", ['quantity' => 2])->assertOk();
        $this->putJson("/api/v1/client/cart/items/{$item->id}", ['quantity' => 5])->assertOk();

        $this->getJson('/api/v1/client/cart')->assertOk()->assertJsonPath('items.0.quantity', 5);
        $this->assertSame(5, $item->fresh()->quantity);
    }

    public function test_product_quantity_one_does_not_limit_cart_quantity_five(): void
    {
        $product = Product::factory()->withoutImage()->create(['quantity' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/client/cart/items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ])->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_quantity_999_is_allowed(): void
    {
        $product = Product::factory()->withoutImage()->create(['quantity' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/client/cart/items', [
            'product_id' => $product->id,
            'quantity' => 999,
        ])->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 999,
        ]);
    }

    public function test_quantity_above_999_is_rejected(): void
    {
        $product = Product::factory()->withoutImage()->create(['quantity' => 1]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/client/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1000,
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);

        $this->postJson('/api/v1/client/cart/items', [
            'product_id' => $product->id,
            'quantity' => 999,
        ])->assertOk();
        $item = CartItem::query()->where('product_id', $product->id)->firstOrFail();
        $this->putJson("/api/v1/client/cart/items/{$item->id}", ['quantity' => 1000])->assertUnprocessable();
        $this->assertSame(999, $item->fresh()->quantity);
    }
}
