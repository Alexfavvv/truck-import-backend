<?php

namespace Tests\Feature\Requests\Admin\Brand;

use App\Http\Requests\Admin\Brand\StoreRequest;
use App\Models\Brand;
use App\Models\PartType;
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

    #[Test]
    public function rulesAreCorrect()
    {
        $rules = (new StoreRequest())->rules();

        $this->assertArrayHasKey('brand.title', $rules);
        $this->assertStringContainsString('required', $rules['brand.title']);
        $this->assertStringContainsString('string', $rules['brand.title']);
        $this->assertStringContainsString('min:3', $rules['brand.title']);
        $this->assertStringContainsString('max:255', $rules['brand.title']);
        $this->assertStringContainsString('unique:brands,title', $rules['brand.title']);

        $this->assertArrayHasKey('brand.slug', $rules);
        $this->assertStringContainsString('required', $rules['brand.slug']);
        $this->assertStringContainsString('string', $rules['brand.slug']);
        $this->assertStringContainsString('min:3', $rules['brand.slug']);
        $this->assertStringContainsString('max:255', $rules['brand.slug']);
        $this->assertStringContainsString('unique:brands,slug', $rules['brand.slug']);

        $this->assertArrayHasKey('brand.description', $rules);
        $this->assertStringContainsString('nullable', $rules['brand.description']);
        $this->assertStringContainsString('string', $rules['brand.description']);

        $this->assertArrayHasKey('brand.content', $rules);
        $this->assertStringContainsString('nullable', $rules['brand.content']);
        $this->assertStringContainsString('string', $rules['brand.content']);

        $this->assertArrayHasKey('brand.image', $rules);
        $this->assertStringContainsString('nullable', $rules['brand.image']);
        $this->assertStringContainsString('image', $rules['brand.image']);
        $this->assertStringContainsString('mimes:jpeg,png,jpg,gif,svg,webp', $rules['brand.image']);

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

        $this->assertArrayHasKey('partTypes', $rules);
        $this->assertStringContainsString('nullable', $rules['partTypes']);
        $this->assertStringContainsString('array', $rules['partTypes']);

        $this->assertArrayHasKey('partTypes.*', $rules);
        $this->assertStringContainsString('integer', $rules['partTypes.*']);
        $this->assertStringContainsString('exists:part_types,id', $rules['partTypes.*']);
    }

    #[Test]
    public function validDataPassesValidation()
    {
        $partType = PartType::factory()->create();
        $file = UploadedFile::fake()->image('brand.jpg');

        $data = [
            'brand' => [
                'title' => 'New Brand Name',
                'slug' => 'new-brand-name',
                'description' => 'Some description',
                'content' => 'Some content',
            ],
            'meta' => [
                'meta_h1' => 'Meta H1',
                'meta_title' => 'Meta Title',
                'meta_description' => 'Meta Description',
            ],
            'partTypes' => [$partType->id],
        ];

        // Файл передается как пятый аргумент (файлы)
        $request = StoreRequest::create('/admin/brands', 'POST', $data, [], ['brand' => ['image' => $file]]);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        Brand::factory()->create(['title' => 'Existing Brand', 'slug' => 'existing-brand']);
        PartType::factory()->create();

        $data = [
            'brand' => [
                'title' => 'Existing Brand', // Not unique
                'slug' => 'existing-brand',   // Not unique
                'description' => 123,         // Invalid type
            ],
            'meta' => [
                'meta_h1' => str_repeat('a', 256), // Too long
            ],
            'partTypes' => [99999], // Non-existent PartType ID
        ];

        // Файл передается как пятый аргумент (файлы)
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $request = StoreRequest::create('/admin/brands', 'POST', $data, [], ['brand' => ['image' => $invalidFile]]);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('brand.title', $errors);
        $this->assertArrayHasKey('brand.slug', $errors);
        $this->assertArrayHasKey('brand.description', $errors);
        $this->assertArrayHasKey('brand.image', $errors); // Теперь этот ключ должен быть
        $this->assertArrayHasKey('meta.meta_h1', $errors);
        $this->assertArrayHasKey('partTypes.0', $errors);
    }

    #[Test]
    public function passedValidationProcessesImageAndMergesDataOnPassedValidation()
    {
        $partType = PartType::factory()->create();
        $file = UploadedFile::fake()->image('test_brand.jpg')->size(500);

        $data = [
            'brand' => [
                'title' => 'Test Brand',
                'slug' => 'test-brand',
            ],
            'meta' => [
                'meta_title' => 'Test Meta Title',
            ],
            'partTypes' => [$partType->id],
        ];

        // Файл передается как пятый аргумент (файлы)
        $request = StoreRequest::create('/admin/brands', 'POST', $data, [], ['brand' => ['image' => $file]]);

        // Вызываем passedValidation напрямую, так как в тестах валидатор не вызывает его автоматически
        // В реальном приложении это делает Laravel после успешной валидации
        $request->setValidator(\Validator::make($request->all(), $request->rules()));
        $request->passedValidation();

        // Проверяем, что изображение было сохранено по пути, который был объединен в запрос
        // Теперь $request->input('brand.image_path') должен содержать корректный путь
        Storage::disk('public')->assertExists($request->input('brand.image_path'));
        $this->assertNotNull($request->input('brand.image_path'));
        $this->assertStringContainsString('images/', $request->input('brand.image_path'));

        // Проверяем, что мета-данные были объединены
        $this->assertEquals('Test Meta Title', $request->input('meta.meta_title'));

        // Проверяем, что partTypes были объединены
        $this->assertEquals([$partType->id], $request->input('partTypes'));
    }

    #[Test]
    public function passedValidationHandlesNullImageOnPassedValidation()
    {
        $partType = PartType::factory()->create();

        $data = [
            'brand' => [
                'title' => 'Brand Without Image',
                'slug' => 'brand-without-image',
            ],
            'meta' => [],
            'partTypes' => [$partType->id],
        ];

        $request = StoreRequest::create('/admin/brands', 'POST', $data);

        $request->setValidator(\Validator::make($request->all(), $request->rules()));
        $request->passedValidation();

        // Проверяем, что image_path равен null
        $this->assertNull($request->input('brand.image_path'));
        Storage::disk('public')->assertDirectoryEmpty('images'); // Убедимся, что ничего не сохранено
    }
}
