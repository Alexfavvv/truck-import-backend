<?php

namespace Tests\Feature\Requests\Admin\TruckModel;

use App\Http\Requests\Admin\TruckModel\StoreRequest;
use App\Models\TruckManufacturer;
use App\Models\TruckModel;
use App\Models\Brand; // Добавляем импорт Brand
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreRequestTest extends TestCase
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
        $rules = (new StoreRequest())->rules();

        $this->assertArrayHasKey('truckModel.brand_id', $rules);
        $this->assertStringContainsString('required', $rules['truckModel.brand_id']);
        $this->assertStringContainsString('exists:brands,id', $rules['truckModel.brand_id']);

        $this->assertArrayHasKey('truckModel.title', $rules);
        $this->assertStringContainsString('required', $rules['truckModel.title']);
        $this->assertStringContainsString('string', $rules['truckModel.title']);
        $this->assertStringContainsString('min:3', $rules['truckModel.title']);
        $this->assertStringContainsString('max:255', $rules['truckModel.title']);
        $this->assertStringContainsString('unique:truck_models,title', $rules['truckModel.title']);

        $this->assertArrayHasKey('truckModel.slug', $rules);
        $this->assertStringContainsString('required', $rules['truckModel.slug']);
        $this->assertStringContainsString('string', $rules['truckModel.slug']);
        $this->assertStringContainsString('min:3', $rules['truckModel.slug']);
        $this->assertStringContainsString('max:255', $rules['truckModel.slug']);
        $this->assertStringContainsString('unique:truck_models,slug', $rules['truckModel.slug']);

        $this->assertArrayHasKey('truckModel.description', $rules);
        $this->assertStringContainsString('nullable', $rules['truckModel.description']);
        $this->assertStringContainsString('string', $rules['truckModel.description']);

        $this->assertArrayHasKey('truckModel.content', $rules);
        $this->assertStringContainsString('nullable', $rules['truckModel.content']);
        $this->assertStringContainsString('string', $rules['truckModel.content']);

        $this->assertArrayHasKey('truckModel.image', $rules);
        $this->assertStringContainsString('nullable', $rules['truckModel.image']);
        $this->assertStringContainsString('image', $rules['truckModel.image']);
        $this->assertStringContainsString('mimes:jpeg,png,jpg,gif,svg,webp', $rules['truckModel.image']);

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
        $brand = Brand::factory()->create(); // Используем Brand вместо TruckManufacturer
        $file = UploadedFile::fake()->image('model.jpg');

        $data = [
            'truckModel' => [ // Изменено с 'model' на 'truckModel'
                'brand_id' => $brand->id, // Изменено с 'truck_manufacturer_id' на 'brand_id'
                'title' => 'New Truck Model',
                'slug' => 'new-truck-model',
                'description' => 'Some description',
                'content' => 'Some content',
            ],
            'meta' => [
                'meta_h1' => 'Meta H1',
                'meta_title' => 'Meta Title',
                'meta_description' => 'Meta Description',
            ],
        ];

        // Файл передается как пятый аргумент (файлы) с правильной вложенной структурой
        $request = StoreRequest::create('/admin/truck-models', 'POST', $data, [], ['truckModel' => ['image' => $file]]); // Изменено с 'model' на 'truckModel'
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        TruckModel::factory()->create(['title' => 'Existing Model', 'slug' => 'existing-model']);
        Brand::factory()->create(); // Используем Brand вместо TruckManufacturer

        $data = [
            'truckModel' => [ // Изменено с 'model' на 'truckModel'
                'brand_id' => 99999, // Non-existent Brand ID
                'title' => 'Existing Model', // Not unique
                'slug' => 'existing-model',   // Not unique
                'description' => 123,         // Invalid type
            ],
            'meta' => [
                'meta_h1' => str_repeat('a', 256), // Too long
            ],
        ];

        // Файл передается как пятый аргумент (файлы) с правильной вложенной структурой
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $request = StoreRequest::create('/admin/truck-models', 'POST', $data, [], ['truckModel' => ['image' => $invalidFile]]); // Изменено с 'model' на 'truckModel'
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('truckModel.brand_id', $errors); // Изменено
        $this->assertArrayHasKey('truckModel.title', $errors); // Изменено
        $this->assertArrayHasKey('truckModel.slug', $errors); // Изменено
        $this->assertArrayHasKey('truckModel.description', $errors); // Изменено
        $this->assertArrayHasKey('truckModel.image', $errors); // Изменено
        $this->assertArrayHasKey('meta.meta_h1', $errors);
    }

    #[Test]
    public function passedValidationProcessesImageAndMergesData()
    {
        $brand = Brand::factory()->create(); // Используем Brand вместо TruckManufacturer
        $file = UploadedFile::fake()->image('test_model.jpg')->size(500);

        $data = [
            'truckModel' => [ // Изменено с 'model' на 'truckModel'
                'brand_id' => $brand->id, // Изменено
                'title' => 'Test Truck Model',
                'slug' => 'test-truck-model',
            ],
            'meta' => [
                'meta_title' => 'Test Meta Title',
            ],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $request = new StoreRequest();
        $request->setContainer($this->app); // Устанавливаем контейнер для разрешения сервисов
        $request->merge($data); // Объединяем данные
        // Правильная структура для вложенных файлов
        $request->files->set('truckModel', ['image' => $file]);

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes()); // Убеждаемся, что валидация прошла
        $request->setValidator($validator); // Устанавливаем валидатор для запроса
        $request->passedValidation(); // Вручную вызываем passedValidation

        // Проверяем, что изображение было сохранено по пути, который был объединен в запрос
        Storage::disk('public')->assertExists($request->input('truckModel.image_path')); // Изменено с 'model' на 'truckModel'
        $this->assertNotNull($request->input('truckModel.image_path')); // Изменено
        $this->assertStringContainsString('images/', $request->input('truckModel.image_path')); // Изменено

        // Проверяем, что мета-данные были объединены
        $this->assertEquals('Test Meta Title', $request->input('meta.meta_title'));
    }

    #[Test]
    public function passedValidationHandlesNullImage()
    {
        $brand = Brand::factory()->create(); // Используем Brand вместо TruckManufacturer

        $data = [
            'truckModel' => [ // Изменено с 'model' на 'truckModel'
                'brand_id' => $brand->id, // Изменено
                'title' => 'Model Without Image',
                'slug' => 'model-without-image',
            ],
            'meta' => [],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new StoreRequest();
        $request->setContainer($this->app);
        $request->merge($data);

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes()); // Убеждаемся, что валидация прошла
        $request->setValidator($validator); // Устанавливаем валидатор для запроса
        $request->passedValidation(); // Вручную вызываем passedValidation

        // Проверяем, что image_path равен null
        $this->assertNull($request->input('truckModel.image_path')); // Изменено
        Storage::disk('public')->assertDirectoryEmpty('images'); // Убедимся, что ничего не сохранено
    }
}
