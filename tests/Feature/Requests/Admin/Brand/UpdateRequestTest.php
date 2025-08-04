<?php

namespace Tests\Feature\Requests\Admin\Brand;

use App\Http\Requests\Admin\Brand\UpdateRequest;
use App\Models\Brand;
use App\Models\PartType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Routing\Route; // Импортируем Route


class UpdateRequestTest extends TestCase
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
        $brand = Brand::factory()->create(); // Нужен существующий бренд для unique rule exclusion

        // Создаем экземпляр запроса
        $request = new UpdateRequest();
        $request->setContainer($this->app); // Устанавливаем контейнер для разрешения сервисов

        // Устанавливаем resolver для маршрута, чтобы $this->route('brand') работал
        $request->setRouteResolver(function () use ($brand) {
            $route = new Route('PUT', 'admin/brands/{brand}', []);
            $route->parameters = ['brand' => $brand]; // Устанавливаем параметры напрямую
            return $route;
        });

        $rules = $request->rules();

        $this->assertArrayHasKey('brand.title', $rules);
        $this->assertStringContainsString('required', $rules['brand.title']);
        $this->assertStringContainsString('string', $rules['brand.title']);
        $this->assertStringContainsString('min:3', $rules['brand.title']);
        $this->assertStringContainsString('max:255', $rules['brand.title']);
        $this->assertStringContainsString('unique:brands,title,' . $brand->id, $rules['brand.title']);

        $this->assertArrayHasKey('brand.slug', $rules);
        $this->assertStringContainsString('required', $rules['brand.slug']);
        $this->assertStringContainsString('string', $rules['brand.slug']);
        $this->assertStringContainsString('min:3', $rules['brand.slug']);
        $this->assertStringContainsString('max:255', $rules['brand.slug']);
        $this->assertStringContainsString('unique:brands,slug,' . $brand->id, $rules['brand.slug']);

        $this->assertArrayHasKey('brand.description', $rules);
        $this->assertStringContainsString('nullable', $rules['brand.description']);
        $this->assertStringContainsString('string', $rules['brand.description']);

        $this->assertArrayHasKey('brand.content', $rules);
        $this->assertStringContainsString('nullable', $rules['brand.content']);
        $this->assertStringContainsString('string', $rules['brand.content']);

        $this->assertArrayHasKey('brand.image', $rules);
        $this->assertStringContainsString('nullable', $rules['brand.image']);
        $this->assertStringContainsString('image', $rules['brand.image']);
        $this->assertStringContainsString('mimes:jpg,jpeg,png,gif,svg,webp', $rules['brand.image']);

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
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $file = UploadedFile::fake()->image('updated_brand.jpg');

        $data = [
            'brand' => [
                'title' => 'Updated Brand Name',
                'slug' => 'updated-brand-name',
                'description' => 'Updated description',
                'content' => 'Updated content',
            ],
            'meta' => [
                'meta_h1' => 'Updated Meta H1',
                'meta_title' => 'Updated Meta Title',
                'meta_description' => 'Updated Meta Description',
            ],
            'partTypes' => [$partType->id],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        // Правильная структура для вложенных файлов
        $request->files->set('brand', ['image' => $file]);
        $request->setRouteResolver(function () use ($brand) {
            $route = new Route('PUT', 'admin/brands/{brand}', []);
            $route->parameters = ['brand' => $brand];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        $existingBrand = Brand::factory()->create(['title' => 'Existing Brand', 'slug' => 'existing-slug']);
        $brandToUpdate = Brand::factory()->create(['title' => 'Brand To Update', 'slug' => 'brand-to-update']);
        PartType::factory()->create(); // Create some part types

        $data = [
            'brand' => [
                'title' => 'Existing Brand', // Not unique (taken by existingBrand)
                'slug' => 'existing-slug',   // Not unique (taken by existingBrand)
                'description' => 123,         // Invalid type
            ],
            'meta' => [
                'meta_h1' => str_repeat('a', 256), // Too long
            ],
            'partTypes' => [99999], // Non-existent PartType ID
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        // Правильная структура для вложенных файлов
        $request->files->set('brand', ['image' => $invalidFile]);
        $request->setRouteResolver(function () use ($brandToUpdate) {
            $route = new Route('PUT', 'admin/brands/{brand}', []);
            $route->parameters = ['brand' => $brandToUpdate];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('brand.title', $errors);
        $this->assertArrayHasKey('brand.slug', $errors);
        $this->assertArrayHasKey('brand.description', $errors);
        $this->assertArrayHasKey('brand.image', $errors);
        $this->assertArrayHasKey('meta.meta_h1', $errors);
        $this->assertArrayHasKey('partTypes.0', $errors);
    }

    #[Test]
    public function passedValidationProcessesImageAndMergesDataWithNewImage()
    {
        $oldFile = UploadedFile::fake()->image('old_brand.jpg')->size(100);
        // Явно формируем oldPath
        $oldPath = 'images/' . $oldFile->hashName();
        Storage::disk('public')->put($oldPath, 'dummy content'); // Сохраняем файл по этому пути
        $brand = Brand::factory()->create(['image_path' => $oldPath]); // Убеждаемся, что модель имеет этот путь

        $newFile = UploadedFile::fake()->image('new_brand.png')->size(200);
        $partType = PartType::factory()->create();

        $data = [
            'brand' => [
                'title' => 'Updated Brand',
                'slug' => 'updated-brand',
            ],
            'meta' => [
                'meta_title' => 'Updated Meta Title',
            ],
            'partTypes' => [$partType->id],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('brand', ['image' => $newFile]); // Правильная структура для вложенных файлов
        $request->setRouteResolver(function () use ($brand) {
            $route = new Route('PUT', 'admin/brands/{brand}', []);
            $route->parameters = ['brand' => $brand];
            return $route;
        });

        $request->validateResolved();

        Storage::disk('public')->assertExists($request->input('brand.image_path'));
        $this->assertStringContainsString('images/', $request->input('brand.image_path'));
        $this->assertStringContainsString($newFile->hashName(), $request->input('brand.image_path'));
        Storage::disk('public')->assertMissing($oldPath); // Проверяем, что старый файл удален

        $this->assertEquals('Updated Meta Title', $request->input('meta.meta_title'));
        $this->assertEquals([$partType->id], $request->input('partTypes'));
    }

    #[Test]
    public function passedValidationHandlesNullImageAndKeepsOldImageIfNoNewImageUploaded()
    {
        $oldFile = UploadedFile::fake()->image('old_brand.jpg')->size(100);
        // Явно формируем oldPath
        $oldPath = 'images/' . $oldFile->hashName();
        Storage::disk('public')->put($oldPath, 'dummy content'); // Сохраняем файл по этому пути
        $brand = Brand::factory()->create(['image_path' => $oldPath]); // Убеждаемся, что модель имеет этот путь

        $partType = PartType::factory()->create();

        $data = [
            'brand' => [
                'title' => 'Brand Without New Image',
                'slug' => 'brand-without-new-image',
            ],
            'meta' => [],
            'partTypes' => [$partType->id],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($brand) {
            $route = new Route('PUT', 'admin/brands/{brand}', []);
            $route->parameters = ['brand' => $brand];
            return $route;
        });

        $request->validateResolved();

        Storage::disk('public')->assertExists($oldPath); // Проверяем, что старый файл НЕ удален
        $this->assertEquals($oldPath, $request->input('brand.image_path'));
    }

    #[Test]
    public function passedValidationHandlesNullImageAndOldImageIfNoNewImageUploadedAndNoOldImage()
    {
        $brand = Brand::factory()->create(['image_path' => null]);
        $partType = PartType::factory()->create();

        $data = [
            'brand' => [
                'title' => 'Brand Without Image',
                'slug' => 'brand-without-image',
            ],
            'meta' => [],
            'partTypes' => [$partType->id],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($brand) {
            $route = new Route('PUT', 'admin/brands/{brand}', []);
            $route->parameters = ['brand' => $brand];
            return $route;
        });

        $request->validateResolved();

        $this->assertNull($request->input('brand.image_path'));
        Storage::disk('public')->assertDirectoryEmpty('images');
    }
}
