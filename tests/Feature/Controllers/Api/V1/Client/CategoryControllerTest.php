<?php

namespace Tests\Feature\Controllers\Api\V1\Client;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testShowBrandReturnsCorrectCategory()
    {
        $brand = Category::factory()->create(['type' => 'manufacturer', 'slug' => 'bmw']);
        $model = Category::factory()->create(['type' => 'model', 'parent_id' => $brand->id, 'slug' => 'bmw-x5']);

        $response = $this->getJson(route('v1.client.categories.brand.show', ['brandSlug' => 'bmw']));

        $response->assertStatus(200);
        $response->assertJson([
            'slug' => 'bmw',
            'children' => [['slug' => 'bmw-x5']],
        ]);
    }

    public function testShowModelReturnsCorrectCategory()
    {
        $brand = Category::factory()->create(['type' => 'manufacturer', 'slug' => 'bmw']);
        $model = Category::factory()->create(['type' => 'model', 'parent_id' => $brand->id, 'slug' => 'bmw-x5']);
        $part = Category::factory()->create(['type' => 'part', 'parent_id' => $brand->id, 'slug' => 'tormaza']);


        $response = $this->getJson(route('v1.client.categories.model.show', ['brandSlug' => 'bmw', 'modelSlug' => 'bmw-x5']));

        $response->assertStatus(200);
        $response->assertJson([
            'slug' => 'bmw-x5',
            'parts' => [
                ['slug' => 'tormaza']
            ],
        ]);
    }

    public function testShowPartReturnsCorrectCategory()
    {
        $parent = Category::factory()->create(['type' => 'manufacturer', 'slug' => 'bmw']);
        $part = Category::factory()->create(['type' => 'part', 'parent_id' => $parent->id, 'slug' => 'tormaza']);
        $product = Product::factory()->create(['category_id' => $part->id, 'sku' => 1345]);

        $response = $this->getJson(route('v1.client.categories.part.show', ['brandSlug' => 'bmw',  'partSlug' => 'tormaza']));
        $response->assertStatus(200);
        $response->assertJson([
            'slug' => 'tormaza',
            'products' => [
                ['sku' => 1345]
            ]
        ]);
    }

    public function testShowBrandReturns404ForInvalidSlug()
    {
        $brand = Category::factory()->create(['type' => 'manufacturer', 'slug' => 'bmw']);
        $response = $this->getJson(route('v1.client.categories.brand.show', ['brandSlug' => 'another']));
        $response->assertStatus(404);
    }
    public function testShowModelReturns404ForInvalidSlug()
    {
        $brand = Category::factory()->create(['type' => 'manufacturer', 'slug' => 'bmw']);
        $model = Category::factory()->create(['type' => 'model', 'parent_id' => $brand->id, 'slug' => 'bmw-x5']);

        $response = $this->getJson(route('v1.client.categories.model.show', ['brandSlug' => 'another', 'modelSlug' => 'bmw-x5']));
        $response->assertStatus(404);

        $response = $this->getJson(route('v1.client.categories.model.show', ['brandSlug' => 'bmw', 'modelSlug' => 'another']));
        $response->assertStatus(404);

        $response = $this->getJson(route('v1.client.categories.model.show', ['brandSlug' => 'another', 'modelSlug' => 'another']));
        $response->assertStatus(404);
    }

    public function testShowPartReturns404ForInvalidSlug()
    {
        $parent = Category::factory()->create(['type' => 'manufacturer', 'slug' => 'bmw']);
        $part = Category::factory()->create(['type' => 'part', 'parent_id' => $parent->id, 'slug' => 'tormaza']);


        $response = $this->getJson(route('v1.client.categories.part.show', ['brandSlug' => 'another',  'partSlug' => 'tormaza']));
        $response->assertStatus(404);

        $response = $this->getJson(route('v1.client.categories.part.show', ['brandSlug' => 'bmw',  'partSlug' => 'another']));
        $response->assertStatus(404);

        $response = $this->getJson(route('v1.client.categories.part.show', ['brandSlug' => 'another',  'partSlug' => 'another']));
        $response->assertStatus(404);
    }
}
