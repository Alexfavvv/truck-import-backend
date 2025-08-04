<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Product;
use App\Models\TruckModel;
use App\Models\PartType;
use App\Models\Metatag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandTest extends TestCase
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

    /** #[test] */
    public function testBrandHasManyProducts()
    {
        $brand = Brand::factory()->create();
        Product::factory()->count(2)->create(['brand_id' => $brand->id]);

        $this->assertCount(2, $brand->products);
        $this->assertInstanceOf(Product::class, $brand->products->first());
    }

    /** #[test] */
    public function testBrandHasManyTruckModels()
    {
        $brand = Brand::factory()->create();
        TruckModel::factory()->count(3)->create(['brand_id' => $brand->id]);

        $this->assertCount(3, $brand->truckModels);
        $this->assertInstanceOf(TruckModel::class, $brand->truckModels->first());
    }

    /** #[test] */
    public function testBrandBelongsToManyPartTypes()
    {
        $brand = Brand::factory()->create();
        $partType1 = PartType::factory()->create();
        $partType2 = PartType::factory()->create();

        $brand->partTypes()->attach([$partType1->id, $partType2->id]);

        $this->assertCount(2, $brand->partTypes);
        $this->assertInstanceOf(PartType::class, $brand->partTypes->first());
        $this->assertTrue($brand->partTypes->contains($partType1));
        $this->assertTrue($brand->partTypes->contains($partType2));
    }

    /** #[test] */
    public function testBrandHasOneMetatag()
    {
        $brand = Brand::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $brand->id,
            'metatagable_type' => Brand::class,
        ]);

        $this->assertInstanceOf(Metatag::class, $brand->metatags);
        $this->assertEquals($metatag->id, $brand->metatags->id);
    }

    /** #[test] */
    public function testBrandDeletesAssociatedMetatagOnDeletion()
    {
        $brand = Brand::factory()->create();
        Metatag::factory()->create([
            'metatagable_id' => $brand->id,
            'metatagable_type' => Brand::class,
        ]);

        $this->assertDatabaseHas('metatags', ['metatagable_id' => $brand->id, 'metatagable_type' => Brand::class]);

        $brand->delete();

        $this->assertDatabaseMissing('metatags', ['metatagable_id' => $brand->id, 'metatagable_type' => Brand::class]);
    }

    /** #[test] */
    public function testBrandDeletesImageOnDeletion()
    {
        // Создаем фейковый файл изображения
        $imageName = 'test_brand_image.jpg';
        Storage::disk('public')->put('images/brands/' . $imageName, 'dummy content');

        $brand = Brand::factory()->create([
            'image_path' => 'images/brands/' . $imageName,
        ]);

        // Убедимся, что файл существует
        Storage::disk('public')->assertExists('images/brands/' . $imageName);

        $brand->delete();

        // Убедимся, что файл был удален
        Storage::disk('public')->assertMissing('images/brands/' . $imageName);
    }

    /** #[test] */
    public function testGetImageUrlAttributeReturnsCorrectUrl()
    {
        $brand = Brand::factory()->create(['image_path' => 'test/path/image.jpg']);
        $expectedUrl = Storage::disk('public')->url('test/path/image.jpg');
        $this->assertEquals($expectedUrl, $brand->image_url);

        $brandWithoutImage = Brand::factory()->create(['image_path' => null]);
        $this->assertNull($brandWithoutImage->image_url);
    }
}
