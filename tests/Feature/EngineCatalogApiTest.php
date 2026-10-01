<?php

namespace Tests\Feature;

use App\Models\Engine;
use App\Models\Product;
use Database\Seeders\EngineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EngineSeeder::class);
    }

    public function test_list_filters_manufacturers_and_paginates_without_using_products(): void
    {
        $this->assertSame(13, Engine::count());
        $this->assertSame(0, Product::count());

        $this->getJson('/api/v1/client/engines?manufacturer=MAN')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/client/engines?manufacturer=Volvo%20Penta')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/client/engines?manufacturer=Scania')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.manufacturer', 'Scania');
        $this->getJson('/api/v1/client/engines?manufacturer=Scania%20Marine')
            ->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('data.0.manufacturer', 'Scania Marine');
        $this->getJson('/api/v1/client/engines?limit=5&page=2')
            ->assertOk()->assertJsonCount(5, 'data')->assertJsonPath('meta.current_page', 2);
        $this->getJson('/api/v1/client/products')->assertOk()->assertJsonStructure(['data', 'meta']);
    }

    public function test_searches_title_manufacturer_engine_family_model_and_sku(): void
    {
        $this->getJson('/api/v1/client/engines?search=D2676')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'man-d2676-long-block');
        $this->getJson('/api/v1/client/engines?search=DI16-MAR')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'scania-di16-marine-engine');
        $this->getJson('/api/v1/client/engines?search=Scania%20DC13')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.slug', 'scania-dc13-engine');

        Engine::create([
            'slug' => 'volvo-family-search',
            'title' => 'Test engine',
            'manufacturer' => 'Volvo',
            'engine_family' => 'D99',
            'model' => 'M99',
            'type' => 'engine',
            'sku' => 'OEM-99',
        ]);
        foreach (['D99', 'M99', 'OEM-99'] as $term) {
            $this->getJson('/api/v1/client/engines?search='.$term)
                ->assertOk()->assertJsonPath('meta.total', 1)
                ->assertJsonPath('data.0.slug', 'volvo-family-search');
        }
    }

    public function test_show_uses_slug_and_missing_slug_returns_404(): void
    {
        $this->getJson('/api/v1/client/engines/man-d2676-long-block')
            ->assertOk()
            ->assertJsonPath('type', 'long_block')
            ->assertJsonPath('manufacturer', 'MAN')
            ->assertJsonPath('model', 'D2676')
            ->assertJsonPath('engine_family', 'D26')
            ->assertJsonStructure(['manufacturer', 'engine_family', 'model', 'type', 'sku', 'description']);

        $this->getJson('/api/v1/client/engines/unknown-engine')->assertNotFound();
    }
}
