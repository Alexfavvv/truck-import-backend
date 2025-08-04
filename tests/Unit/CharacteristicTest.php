<?php

namespace Tests\Unit;

use App\Models\Characteristic;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacteristicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Проверяет, что характеристика принадлежит продукту.
     */
    public function testCharacteristicBelongsToProduct()
    {
        // Создаем продукт
        $product = Product::factory()->create();

        // Создаем характеристику, привязанную к этому продукту
        $characteristic = Characteristic::factory()->create([
            'product_id' => $product->id,
        ]);

        // Проверяем, что отношение 'product' возвращает экземпляр Product
        $this->assertInstanceOf(Product::class, $characteristic->product);
        // Проверяем, что ID продукта соответствует ожидаемому
        $this->assertEquals($product->id, $characteristic->product->id);
    }
}
