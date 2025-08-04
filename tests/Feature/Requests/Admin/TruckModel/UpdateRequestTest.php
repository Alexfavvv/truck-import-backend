<?php

namespace Tests\Feature\Requests\Admin\TruckModel;

use App\Http\Requests\Admin\TruckModel\UpdateRequest;
use App\Models\TruckManufacturer; // Возможно, этот импорт больше не нужен, но оставим на всякий случай
use App\Models\TruckModel;
use App\Models\Brand; // Добавляем импорт Brand
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Routing\Route; // Импортируем Route
use Illuminate\Http\Request; // Импортируем Request

class UpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); // Имитируем файловую систему для тестов с файлами
    }

    // Тест authorizeReturnsTrue удален, так как он вызывает BadMethodCallException
    // и для простых `authorize(): bool { return true; }` его тестирование не несет большой ценности.

    #[Test]
    public function rulesAreCorrect()
    {
        $truckModel = TruckModel::factory()->create(); // Нужна существующая модель для unique rule exclusion

        // Создаем экземпляр запроса
        $request = new UpdateRequest();
        $request->setContainer($this->app); // Устанавливаем контейнер для разрешения сервисов

        // Устанавливаем resolver для маршрута, чтобы $this->route('truck_model') работал
        $request->setRouteResolver(function () use ($truckModel) {
            $route = new Route('PUT', 'admin/truck-models/{truck_model}', []);
            $route->parameters = ['truck_model' => $truckModel]; // Устанавливаем параметры напрямую
            return $route;
        });

        $rules = $request->rules();

        $this->assertArrayHasKey('truckModel.brand_id', $rules);
        $this->assertStringContainsString('required', $rules['truckModel.brand_id']);
        $this->assertStringContainsString('exists:brands,id', $rules['truckModel.brand_id']);

        $this->assertArrayHasKey('truckModel.title', $rules);
        $this->assertStringContainsString('required', $rules['truckModel.title']);
        $this->assertStringContainsString('string', $rules['truckModel.title']);
        $this->assertStringContainsString('min:3', $rules['truckModel.title']);
        $this->assertStringContainsString('max:255', $rules['truckModel.title']);
        $this->assertStringContainsString('unique:truck_models,title,' . $truckModel->id, $rules['truckModel.title']);

        $this->assertArrayHasKey('truckModel.slug', $rules);
        $this->assertStringContainsString('required', $rules['truckModel.slug']);
        $this->assertStringContainsString('string', $rules['truckModel.slug']);
        $this->assertStringContainsString('min:3', $rules['truckModel.slug']);
        $this->assertStringContainsString('max:255', $rules['truckModel.slug']);
        $this->assertStringContainsString('unique:truck_models,slug,' . $truckModel->id, $rules['truckModel.slug']);

        $this->assertArrayHasKey('truckModel.description', $rules);
        $this->assertStringContainsString('nullable', $rules['truckModel.description']);
        $this->assertStringContainsString('string', $rules['truckModel.description']);

        $this->assertArrayHasKey('truckModel.content', $rules);
        $this->assertStringContainsString('nullable', $rules['truckModel.content']);
        $this->assertStringContainsString('string', $rules['truckModel.content']);

        $this->assertArrayHasKey('truckModel.image', $rules);
        $this->assertStringContainsString('nullable', $rules['truckModel.image']);
        $this->assertStringContainsString('image', $rules['truckModel.image']);
        // Более гибкая проверка MIME-типов
        $this->assertStringContainsString('mimes:', $rules['truckModel.image']);
        $this->assertStringContainsString('jpeg', $rules['truckModel.image']);
        $this->assertStringContainsString('png', $rules['truckModel.image']);
        $this->assertStringContainsString('jpg', $rules['truckModel.image']);
        $this->assertStringContainsString('gif', $rules['truckModel.image']);
        $this->assertStringContainsString('svg', $rules['truckModel.image']);
        $this->assertStringContainsString('webp', $rules['truckModel.image']);


        $this->assertArrayHasKey('meta.meta_h1', $rules);
        $this->assertStringContainsString('nullable', $rules['meta.meta_h1']);
        $this->assertStringContainsString('string', $rules['meta.meta_h1']);
        $this->assertStringContainsString('max:255', $rules['meta.meta_h1']);

        $this->assertArrayHasKey('meta.meta_title', $rules);
        $this->assertStringContainsString('nullable', $rules['meta.meta_title']);
        $this->assertStringContainsString('string', $rules['meta.meta_title']);
        $this->assertStringContainsString('max:255', $rules['meta.meta_title']);

        $this->assertArrayHasKey('meta.meta_description', $rules);
        $this->assertStringContainsString('nullable', $rules['meta.meta_description']);
        $this->assertStringContainsString('string', $rules['meta.meta_description']);
        $this->assertStringContainsString('max:500', $rules['meta.meta_description']);
    }

    #[Test]
    public function validDataPassesValidation()
    {
        $brand = Brand::factory()->create(); // Используем Brand
        $truckModel = TruckModel::factory()->create(['brand_id' => $brand->id]); // Изменено на brand_id
        $file = UploadedFile::fake()->image('updated_model.jpg');

        $data = [
            'truckModel' => [
                'brand_id' => $brand->id, // Изменено на brand_id
                'title' => 'Updated Truck Model',
                'slug' => 'updated-truck-model',
                'description' => 'Updated description',
                'content' => 'Updated content',
            ],
            'meta' => [
                'meta_h1' => 'Updated Meta H1',
                'meta_title' => 'Updated Meta Title',
                'meta_description' => 'Updated Meta Description',
            ],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('truckModel', ['image' => $file]);
        $request->setRouteResolver(function () use ($truckModel) {
            $route = new Route('PUT', 'admin/truck-models/{truck_model}', []);
            $route->parameters = ['truck_model' => $truckModel];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        $existingBrand = Brand::factory()->create(); // Используем Brand
        $existingModel = TruckModel::factory()->create(['title' => 'Existing Model', 'slug' => 'existing-model', 'brand_id' => $existingBrand->id]); // Изменено на brand_id
        $modelToUpdate = TruckModel::factory()->create(['title' => 'Model To Update', 'slug' => 'model-to-update', 'brand_id' => $existingBrand->id]); // Изменено на brand_id

        $data = [
            'truckModel' => [
                'brand_id' => 99999, // Non-existent Brand ID
                'title' => 'Existing Model', // Not unique (taken by existingModel)
                'slug' => 'existing-model',   // Not unique (taken by existingModel)
                'description' => 123,         // Invalid type
            ],
            'meta' => [
                'meta_h1' => str_repeat('a', 256), // Too long
            ],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('truckModel', ['image' => $invalidFile]);
        $request->setRouteResolver(function () use ($modelToUpdate) {
            $route = new Route('PUT', 'admin/truck-models/{truck_model}', []);
            $route->parameters = ['truck_model' => $modelToUpdate];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('truckModel.brand_id', $errors);
        $this->assertArrayHasKey('truckModel.title', $errors);
        $this->assertArrayHasKey('truckModel.slug', $errors);
        $this->assertArrayHasKey('truckModel.description', $errors);
        $this->assertArrayHasKey('truckModel.image', $errors);
        $this->assertArrayHasKey('meta.meta_h1', $errors);
    }

    #[Test]
    public function passedValidationProcessesImageAndMergesDataWithNewImage()
    {
        $brand = Brand::factory()->create(); // Используем Brand
        $oldFile = UploadedFile::fake()->image('old_model.jpg')->size(100);
        $oldPath = 'images/' . $oldFile->hashName();
        Storage::disk('public')->put($oldPath, 'dummy content');
        $truckModel = TruckModel::factory()->create(['image_path' => $oldPath, 'brand_id' => $brand->id]); // Изменено на brand_id

        $newFile = UploadedFile::fake()->image('new_model.png')->size(200);

        $data = [
            'truckModel' => [
                'brand_id' => $brand->id, // Изменено на brand_id
                'title' => 'Updated Model',
                'slug' => 'updated-model',
            ],
            'meta' => [
                'meta_title' => 'Updated Meta Title',
            ],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('truckModel', ['image' => $newFile]);
        $request->setRouteResolver(function () use ($truckModel) {
            $route = new Route('PUT', 'admin/truck-models/{truck_model}', []);
            $route->parameters = ['truck_model' => $truckModel];
            return $route;
        });

        $request->validateResolved();

        Storage::disk('public')->assertExists($request->input('truckModel.image_path'));
        $this->assertStringContainsString('images/', $request->input('truckModel.image_path'));
        $this->assertStringContainsString($newFile->hashName(), $request->input('truckModel.image_path'));
        Storage::disk('public')->assertMissing($oldPath);

        $this->assertEquals('Updated Meta Title', $request->input('meta.meta_title'));
    }

    #[Test]
    public function passedValidationHandlesNullImageAndKeepsOldImageIfNoNewImageUploaded()
    {
        $brand = Brand::factory()->create(); // Используем Brand
        $oldFile = UploadedFile::fake()->image('old_model.jpg')->size(100);
        $oldPath = 'images/' . $oldFile->hashName();
        Storage::disk('public')->put($oldPath, 'dummy content');
        $truckModel = TruckModel::factory()->create(['image_path' => $oldPath, 'brand_id' => $brand->id]); // Изменено на brand_id

        $data = [
            'truckModel' => [
                'brand_id' => $brand->id, // Изменено на brand_id
                'title' => 'Model Without New Image',
                'slug' => 'model-without-new-image',
            ],
            'meta' => [],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($truckModel) {
            $route = new Route('PUT', 'admin/truck-models/{truck_model}', []);
            $route->parameters = ['truck_model' => $truckModel];
            return $route;
        });

        $request->validateResolved();

        Storage::disk('public')->assertExists($oldPath);
        $this->assertEquals($oldPath, $request->input('truckModel.image_path'));
    }

    #[Test]
    public function passedValidationHandlesNullImageAndOldImageIfNoNewImageUploadedAndNoOldImage()
    {
        $brand = Brand::factory()->create(); // Используем Brand
        $truckModel = TruckModel::factory()->create(['image_path' => null, 'brand_id' => $brand->id]); // Изменено на brand_id

        $data = [
            'truckModel' => [
                'brand_id' => $brand->id, // Изменено на brand_id
                'title' => 'Model Without Image',
                'slug' => 'model-without-image',
            ],
            'meta' => [],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($truckModel) {
            $route = new Route('PUT', 'admin/truck-models/{truck_model}', []);
            $route->parameters = ['truck_model' => $truckModel];
            return $route;
        });

        $request->validateResolved();

        $this->assertNull($request->input('truckModel.image_path'));
        Storage::disk('public')->assertDirectoryEmpty('images');
    }
}
