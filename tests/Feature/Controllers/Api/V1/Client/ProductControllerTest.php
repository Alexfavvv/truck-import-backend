<?php

namespace Tests\Feature\Controllers\Api\V1\Client;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testShowProductBySkuIsCorrect()
    {
        $product = Product::factory()->create(['sku' => 1345]);
        $response = $this->getJson(route('v1.client.products.show', ['sku' => 1345]));
        $response->assertStatus(200);
        $response->assertJson([
            'sku' => 1345
        ]);
    }

    public function testShowProductReturns404ForInvalidSku()
    {
        $product = Product::factory()->create(['sku' => 1345]);
        $response = $this->getJson(route('v1.client.products.show', ['sku' => 1234]));
        $response->assertStatus(404);
    }
}
