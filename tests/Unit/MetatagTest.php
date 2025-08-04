<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Metatag;
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckManufacturer;
use App\Models\TruckModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetatagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Проверяет, что Metatag может принадлежать Brand.
     */
    public function testMetatagMorphsToBrand()
    {
        $brand = Brand::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $brand->id,
            'metatagable_type' => Brand::class,
        ]);

        $this->assertInstanceOf(Brand::class, $metatag->metatagable);
        $this->assertEquals($brand->id, $metatag->metatagable->id);
        $this->assertEquals(Brand::class, $metatag->metatagable_type);
    }

    /**
     * @test
     * Проверяет, что Metatag может принадлежать Product.
     */
    public function testMetatagMorphsToProduct()
    {
        $product = Product::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $product->id,
            'metatagable_type' => Product::class,
        ]);

        $this->assertInstanceOf(Product::class, $metatag->metatagable);
        $this->assertEquals($product->id, $metatag->metatagable->id);
        $this->assertEquals(Product::class, $metatag->metatagable_type);
    }

    /**
     * @test
     * Проверяет, что Metatag может принадлежать PartType.
     */
    public function testMetatagMorphsToPartType()
    {
        $partType = PartType::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $partType->id,
            'metatagable_type' => PartType::class,
        ]);

        $this->assertInstanceOf(PartType::class, $metatag->metatagable);
        $this->assertEquals($partType->id, $metatag->metatagable->id);
        $this->assertEquals(PartType::class, $metatag->metatagable_type);
    }

    /**
     * @test
     * Проверяет, что Metatag может принадлежать TruckManufacturer.
     */
    public function testMetatagMorphsToTruckManufacturer()
    {
        $truckManufacturer = TruckManufacturer::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $truckManufacturer->id,
            'metatagable_type' => TruckManufacturer::class,
        ]);

        $this->assertInstanceOf(TruckManufacturer::class, $metatag->metatagable);
        $this->assertEquals($truckManufacturer->id, $metatag->metatagable->id);
        $this->assertEquals(TruckManufacturer::class, $metatag->metatagable_type);
    }

    /**
     * @test
     * Проверяет, что Metatag может принадлежать TruckModel.
     */
    public function testMetatagMorphsToTruckModel()
    {
        $truckModel = TruckModel::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $truckModel->id,
            'metatagable_type' => TruckModel::class,
        ]);

        $this->assertInstanceOf(TruckModel::class, $metatag->metatagable);
        $this->assertEquals($truckModel->id, $metatag->metatagable->id);
        $this->assertEquals(TruckModel::class, $metatag->metatagable_type);
    }
}
