<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Metatag;
use App\Models\PartType;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * #[test]
     * Проверяет, что PartType может иметь много брендов.
     */
    public function testPartTypeBelongsToManyBrands()
    {
        $partType = PartType::factory()->create();
        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        $partType->brands()->attach([$brand1->id, $brand2->id]);

        $this->assertCount(2, $partType->brands);
        $this->assertInstanceOf(Brand::class, $partType->brands->first());
        $this->assertTrue($partType->brands->contains($brand1));
        $this->assertTrue($partType->brands->contains($brand2));
    }

    /**
     * #[test]
     * Проверяет, что PartType может иметь много продуктов.
     */
    public function testPartTypeHasManyProducts()
    {
        $partType = PartType::factory()->create();
        Product::factory()->count(2)->create(['part_type_id' => $partType->id]);

        $this->assertCount(2, $partType->products);
        $this->assertInstanceOf(Product::class, $partType->products->first());
    }

    /**
     * #[test]
     * Проверяет, что PartType может иметь один метатег.
     */
    public function testPartTypeHasOneMetatag()
    {
        $partType = PartType::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $partType->id,
            'metatagable_type' => PartType::class,
        ]);

        $this->assertInstanceOf(Metatag::class, $partType->metatags);
        $this->assertEquals($metatag->id, $partType->metatags->id);
    }
}
