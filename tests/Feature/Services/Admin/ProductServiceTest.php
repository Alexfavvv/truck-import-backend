<?php

namespace Tests\Feature\Services\Admin;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Product;
use App\Models\MetaTag;
use App\Services\Admin\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreCreatesProductWithMetaTagsAndCharacteristics()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $data = [
            'product' => [
                'sku' => 'ABC123',
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'title' => 'Тестовый продукт',
                'description' => 'Описание продукта',
                'price' => '1000',
                'quantity' => 10,
                'image_path' => 'image.jpg',
            ],
            'meta' => [
                'meta_title' => 'Мета-заголовок',
                'meta_description' => 'Мета-описание',
            ],
            'characteristics' => [
                ['title' => 'Цвет', 'description' => 'Красный'],
                ['title' => 'Материал', 'description' => 'Металл'],
            ],
        ];

        $product = ProductService::store($data);

        $this->assertDatabaseHas('products', ['title' => 'Тестовый продукт']);
        $this->assertDatabaseHas('metatags', [
            'meta_title' => 'Мета-заголовок',
            'metatagable_id' => $product->id,
            'metatagable_type' => Product::class,
        ]);
        $this->assertDatabaseHas('characteristics', ['title' => 'Цвет', 'description' => 'Красный']);
        $this->assertDatabaseHas('characteristics', ['title' => 'Материал', 'description' => 'Металл']);
    }

    public function testUpdateModifiesProductMetaTagsAndCharacteristics()
    {
        $product = Product::factory()->create(['title' => 'Старый продукт']);
        MetaTag::factory()->create([
            'metatagable_id' => $product->id,
            'metatagable_type' => Product::class,
            'meta_title' => 'Старый мета-тег',
        ]);

        $data = [
            'product' => [
                'title' => 'Обновленный продукт',
                'description' => 'Новое описание',
                'price' => '2000',
                'quantity' => 5,
            ],
            'meta' => [
                'meta_title' => 'Обновленный мета-тег',
            ],
            'characteristics' => [
                ['title' => 'Вес', 'description' => '5 кг'],
            ],
        ];

        ProductService::update($data, $product);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'title' => 'Обновленный продукт']);
        $this->assertDatabaseHas('metatags', ['metatagable_id' => $product->id, 'meta_title' => 'Обновленный мета-тег']);
        $this->assertDatabaseHas('characteristics', ['title' => 'Вес', 'description' => '5 кг']);
    }

    public function testStoreRollsBackTransactionOnError()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->expectException(\Exception::class);

        ProductService::store(['product' => []]); // Ошибка: пустые данные
    }

    public function testUpdateRollsBackTransactionOnError()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $product = Product::factory()->create();

        $this->expectException(\Exception::class);

        ProductService::update(['product' => []], $product); // Ошибка: пустые данные
    }


}
