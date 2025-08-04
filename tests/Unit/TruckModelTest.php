<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Metatag;
use App\Models\TruckModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TruckModelTest extends TestCase
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
     * #[test]
     * Проверяет, что TruckModel принадлежит бренду.
     */
    public function testTruckModelBelongsToBrand()
    {
        $brand = Brand::factory()->create();
        $truckModel = TruckModel::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $truckModel->brand);
        $this->assertEquals($brand->id, $truckModel->brand->id);
    }

    /**
     * #[test]
     * Проверяет, что TruckModel может иметь один метатег.
     */
    public function testTruckModelHasOneMetatag()
    {
        $truckModel = TruckModel::factory()->create();
        $metatag = Metatag::factory()->create([
            'metatagable_id' => $truckModel->id,
            'metatagable_type' => TruckModel::class,
        ]);

        $this->assertInstanceOf(Metatag::class, $truckModel->metatags);
        $this->assertEquals($metatag->id, $truckModel->metatags->id);
    }

    /**
     * #[test]
     * Проверяет, что TruckModel удаляет связанный метатег при удалении.
     */
    public function testTruckModelDeletesAssociatedMetatagOnDeletion()
    {
        $truckModel = TruckModel::factory()->create();
        Metatag::factory()->create([
            'metatagable_id' => $truckModel->id,
            'metatagable_type' => TruckModel::class,
        ]);

        $this->assertDatabaseHas('metatags', ['metatagable_id' => $truckModel->id, 'metatagable_type' => TruckModel::class]);

        $truckModel->delete();

        $this->assertDatabaseMissing('metatags', ['metatagable_id' => $truckModel->id, 'metatagable_type' => TruckModel::class]);
    }

    /**
     * #[test]
     * Проверяет, что TruckModel удаляет изображение при удалении.
     */
    public function testTruckModelDeletesImageOnDeletion()
    {
        $imageName = 'test_truck_model_image.jpg';
        Storage::disk('public')->put('images/truck_models/' . $imageName, 'dummy content');

        $truckModel = TruckModel::factory()->create([
            'image_path' => 'images/truck_models/' . $imageName,
        ]);

        Storage::disk('public')->assertExists('images/truck_models/' . $imageName);

        $truckModel->delete();

        Storage::disk('public')->assertMissing('images/truck_models/' . $imageName);
    }

    /**
     * #[test]
     * Проверяет, что аксессор getImageUrlAttribute возвращает правильный URL.
     */
    public function testGetImageUrlAttributeReturnsCorrectUrl()
    {
        $truckModel = TruckModel::factory()->create(['image_path' => 'test/path/truck_model_image.jpg']);
        $expectedUrl = Storage::disk('public')->url('test/path/truck_model_image.jpg');
        $this->assertEquals($expectedUrl, $truckModel->image_url);

        $truckModelWithoutImage = TruckModel::factory()->create(['image_path' => null]);
        $this->assertNull($truckModelWithoutImage->image_url);
    }
}
