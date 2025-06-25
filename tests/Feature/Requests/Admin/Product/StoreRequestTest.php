<?php

namespace Tests\Feature\Requests\Admin\Product;

use App\Http\Requests\Admin\Product\StoreRequest;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testBrandIdIsRequired()
    {
        $request = new StoreRequest();
        $validator = validator(
            ['product.brand_id' => null,],
            ['product.brand_id' => $request->rules()['product.brand_id']]
        );

        $this->assertTrue($validator->fails());
    }

    public function testSkuIsRequired()
    {
        $request = new StoreRequest();
        $validator = validator(
            ['product.sku' => null,],
            ['product.sku' => $request->rules()['product.sku']]
        );

        $this->assertTrue($validator->fails());
    }

    public function testTitleIsRequired()
    {
        $request = new StoreRequest();
        $validator = validator(
            ['product.title' => null,],
            ['product.title' => $request->rules()['product.title']]
        );

        $this->assertTrue($validator->fails());
    }
    public function testPriceIsRequired()
    {
        $request = new StoreRequest();
        $validator = validator(
            ['product.price' => null,],
            ['product.price' => $request->rules()['product.price']]
        );

        $this->assertTrue($validator->fails());
    }

    public function testQuantityIsRequired()
    {
        $request = new StoreRequest();
        $validator = validator(
            ['product.quantity' => null,],
            ['product.quantity' => $request->rules()['product.quantity']]
        );

        $this->assertTrue($validator->fails());
    }

    public function testCategoryIdIsRequired()
    {
        $request = new StoreRequest();
        $validator = validator(
            ['product.category_id' => null,],
            ['product.category_id' => $request->rules()['product.category_id']]
        );

        $this->assertTrue($validator->fails());
    }

    public function testSkuMustBeUnique()
    {
        $request = new StoreRequest();
        Product::factory()->create(['sku' => 'test']);

        $request->merge(['product.sku' => 'test']);
        $validator = validator($request->all(), ['product.sku' => $request->rules()['product.sku']]);
        $this->assertTrue($validator->fails());

        $request->merge(['product.sku' => 'test2']);
        $validator = validator($request->all(), ['product.sku' => $request->rules()['product.sku']]);
        $this->assertFalse($validator->fails());
    }
}
