<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanAccessProductIndexPage()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Product::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product.index');
        $response->assertViewHas('products', fn ($prods) => $prods->every(fn ($prod) => !empty($prod->title)));

    }

    public function testAdminCanAccessProductCreatePage()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product.create');
    }

    public function testAdminCanStoreProduct()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'product' => [
                'sku' => 'ABC123',
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'title' => 'Тестовый продукт',
                'description' => 'Описание',
                'price' => '1000',
                'quantity' => 10,
                'image_path' => 'image.jpg',
            ],
            'meta' => [],
            'characteristics' => [],
        ]);

        $response->assertRedirect(route('admin.products.create'));
        $this->assertDatabaseHas('products', ['title' => 'Тестовый продукт']);
    }

    public function testAdminCanAccessProductEditPage()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.product.edit');
        $response->assertViewHas('product', $product);
    }

    public function testAdminCanUpdateProduct()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Старый продукт',
            'sku' => 'ABC123',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'product' => [
                'sku' => 'ABC123',
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'title' => 'Обновленный продукт',
                'description' => 'Новое описание',
                'price' => '2000',
                'quantity' => 5,
            ],
            'meta' => [],
            'characteristics' => [],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'title' => 'Обновленный продукт']);
    }

    public function testAdminCanDestroyProduct()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

}
