<?php

namespace Tests\Unit;

use App\Models\Metatag;
use App\Models\Product;
use App\Models\TruckManufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TruckManufacturerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * #[test]
     * Проверяет, что TruckManufacturer может иметь много продуктов.
     */
    public function testTruckManufacturerBelongsToManyProducts()
    {
        $truckManufacturer = TruckManufacturer::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $truckManufacturer->products()->attach([$product1->id, $product2->id]);

        $this->assertCount(2, $truckManufacturer->products);
        $this->assertInstanceOf(Product::class, $truckManufacturer->products->first());
        $this->assertTrue($truckManufacturer->products->contains($product1));
        $this->assertTrue($truckManufacturer->products->contains($product2));
    }

    /**
     * #[test]
     * Проверяет, что TruckManufacturer может иметь один метатег.
     */
    public function testTruckManufacturerHasOneMetatag()
    {
        $truckManufacturer = TruckManufacturer::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $truckManufacturer->id,
            'metatagable_type' => TruckManufacturer::class,
        ]);

        $this->assertInstanceOf(Metatag::class, $truckManufacturer->metatags);
        $this->assertEquals($metatag->id, $truckManufacturer->metatags->id);
    }
}
