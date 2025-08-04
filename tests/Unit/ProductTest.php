<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Characteristic;
use App\Models\Metatag;
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckManufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Убедимся, что директория для хранения изображений создана
     * и очищена перед каждым тестом, использующим Storage.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); // Используем фейковый диск для тестов
    }

    /**
     * @test
     * Проверяет, что продукт принадлежит бренду.
     */
    public function testProductBelongsToBrand()
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertEquals($brand->id, $product->brand->id);
    }

    /**
     * @test
     * Проверяет, что продукт принадлежит типу запчасти.
     */
    public function testProductBelongsToPartType()
    {
        $partType = PartType::factory()->create();
        $product = Product::factory()->create(['part_type_id' => $partType->id]);

        $this->assertInstanceOf(PartType::class, $product->partType);
        $this->assertEquals($partType->id, $product->partType->id);
    }

    /**
     * @test
     * Проверяет, что продукт может иметь много совместимых производителей грузовиков.
     */
    public function testProductBelongsToManyTruckManufacturers()
    {
        $product = Product::factory()->create();
        $manufacturer1 = TruckManufacturer::factory()->create();
        $manufacturer2 = TruckManufacturer::factory()->create();

        $product->truckManufacturers()->attach([$manufacturer1->id, $manufacturer2->id]);

        $this->assertCount(2, $product->truckManufacturers);
        $this->assertInstanceOf(TruckManufacturer::class, $product->truckManufacturers->first());
        $this->assertTrue($product->truckManufacturers->contains($manufacturer1));
        $this->assertTrue($product->truckManufacturers->contains($manufacturer2));
    }

    /**
     * @test
     * Проверяет, что продукт может иметь много характеристик.
     */
    public function testProductHasManyCharacteristics()
    {
        $product = Product::factory()->create();
        Characteristic::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->characteristics);
        $this->assertInstanceOf(Characteristic::class, $product->characteristics->first());
    }

    /**
     * @test
     * Проверяет, что продукт может иметь один метатег.
     */
    public function testProductHasOneMetatag()
    {
        $product = Product::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $product->id,
            'metatagable_type' => Product::class,
        ]);

        $this->assertInstanceOf(Metatag::class, $product->metatags);
        $this->assertEquals($metatag->id, $product->metatags->id);
    }

    /**
     * @test
     * Проверяет, что продукт удаляет связанные характеристики при удалении.
     */
    public function testProductDeletesAssociatedCharacteristicsOnDeletion()
    {
        $product = Product::factory()->create();
        Characteristic::factory()->count(2)->create(['product_id' => $product->id]);

        $this->assertDatabaseCount('characteristics', 2);

        $product->delete();

        $this->assertDatabaseCount('characteristics', 0);
    }

    /**
     * @test
     * Проверяет, что продукт удаляет связанный метатег при удалении.
     */
    public function testProductDeletesAssociatedMetatagOnDeletion()
    {
        $product = Product::factory()->create();
        Metatag::factory()->create([
            'metatagable_id' => $product->id,
            'metatagable_type' => Product::class,
        ]);

        $this->assertDatabaseHas('metatags', ['metatagable_id' => $product->id, 'metatagable_type' => Product::class]);

        $product->delete();

        $this->assertDatabaseMissing('metatags', ['metatagable_id' => $product->id, 'metatagable_type' => Product::class]);
    }

    /**
     * @test
     * Проверяет, что продукт удаляет изображение при удалении.
     */
    public function testProductDeletesImageOnDeletion()
    {
        $imageName = 'test_product_image.jpg';
        Storage::disk('public')->put('images/products/' . $imageName, 'dummy content');

        $product = Product::factory()->create([
            'image_path' => 'images/products/' . $imageName,
        ]);

        Storage::disk('public')->assertExists('images/products/' . $imageName);

        $product->delete();

        Storage::disk('public')->assertMissing('images/products/' . $imageName);
    }

    /**
     * @test
     * Проверяет, что аксессор getImageUrlAttribute возвращает правильный URL.
     */
    public function testGetImageUrlAttributeReturnsCorrectUrl()
    {
        $product = Product::factory()->create(['image_path' => 'test/path/product_image.jpg']);
        $expectedUrl = Storage::disk('public')->url('test/path/product_image.jpg');
        $this->assertEquals($expectedUrl, $product->image_url);

        $productWithoutImage = Product::factory()->create(['image_path' => null]);
        $this->assertNull($productWithoutImage->image_url);
    }
}
