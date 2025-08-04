<?php

namespace Tests\Feature\Requests\Admin\Product;

use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Models\Brand;
use App\Models\Characteristic; // Assuming Characteristic model exists for product characteristics
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckManufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Routing\Route;

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
        $product = Product::factory()->create(); // Нужен существующий продукт для unique rule exclusion

        // Создаем экземпляр запроса
        $request = new UpdateRequest();
        $request->setContainer($this->app); // Устанавливаем контейнер для разрешения сервисов

        // Устанавливаем resolver для маршрута, чтобы $this->route('product') работал
        $request->setRouteResolver(function () use ($product) {
            $route = new Route('PUT', 'admin/products/{product}', []);
            $route->parameters = ['product' => $product]; // Устанавливаем параметры напрямую
            return $route;
        });

        $rules = $request->rules();

        $this->assertArrayHasKey('product.brand_id', $rules);
        $this->assertStringContainsString('required', $rules['product.brand_id']);
        $this->assertStringContainsString('integer', $rules['product.brand_id']);
        $this->assertStringContainsString('exists:brands,id', $rules['product.brand_id']);

        $this->assertArrayHasKey('product.part_type_id', $rules);
        $this->assertStringContainsString('required', $rules['product.part_type_id']);
        $this->assertStringContainsString('integer', $rules['product.part_type_id']);
        $this->assertStringContainsString('exists:part_types,id', $rules['product.part_type_id']);

        $this->assertArrayHasKey('product.sku', $rules);
        $this->assertStringContainsString('required', $rules['product.sku']);
        $this->assertStringContainsString('string', $rules['product.sku']);
        $this->assertStringContainsString('unique:products,sku,' . $product->id, $rules['product.sku']);
        $this->assertStringContainsString('max:255', $rules['product.sku']);

        $this->assertArrayHasKey('product.title', $rules);
        $this->assertStringContainsString('required', $rules['product.title']);
        $this->assertStringContainsString('string', $rules['product.title']);
        $this->assertStringContainsString('max:255', $rules['product.title']);

        $this->assertArrayHasKey('product.description', $rules);
        $this->assertStringContainsString('nullable', $rules['product.description']);
        $this->assertStringContainsString('string', $rules['product.description']);

        $this->assertArrayHasKey('product.price', $rules);
        $this->assertStringContainsString('required', $rules['product.price']);
        $this->assertStringContainsString('numeric', $rules['product.price']);
        $this->assertStringContainsString('min:0', $rules['product.price']);

        $this->assertArrayHasKey('product.quantity', $rules);
        $this->assertStringContainsString('required', $rules['product.quantity']);
        $this->assertStringContainsString('integer', $rules['product.quantity']);
        $this->assertStringContainsString('min:0', $rules['product.quantity']);

        $this->assertArrayHasKey('product.delivery', $rules);
        $this->assertStringContainsString('nullable', $rules['product.delivery']);
        $this->assertStringContainsString('string', $rules['product.delivery']);
        $this->assertStringContainsString('max:255', $rules['product.delivery']);

        $this->assertArrayHasKey('product.image', $rules);
        $this->assertStringContainsString('nullable', $rules['product.image']);
        $this->assertStringContainsString('image', $rules['product.image']);
        $this->assertStringContainsString('mimes:jpeg,png,jpg,gif,svg,webp', $rules['product.image']);
        $this->assertStringContainsString('max:2048', $rules['product.image']);

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

        $this->assertArrayHasKey('characteristics', $rules);
        $this->assertStringContainsString('nullable', $rules['characteristics']);
        $this->assertStringContainsString('array', $rules['characteristics']);

        $this->assertArrayHasKey('characteristics.*.id', $rules);
        $this->assertStringContainsString('nullable', $rules['characteristics.*.id']);
        $this->assertStringContainsString('integer', $rules['characteristics.*.id']);
        $this->assertStringContainsString('exists:characteristics,id', $rules['characteristics.*.id']);

        $this->assertArrayHasKey('characteristics.*.title', $rules);
        $this->assertStringContainsString('nullable', $rules['characteristics.*.title']);
        $this->assertStringContainsString('string', $rules['characteristics.*.title']);
        $this->assertStringContainsString('max:255', $rules['characteristics.*.title']);

        $this->assertArrayHasKey('characteristics.*.description', $rules);
        $this->assertStringContainsString('nullable', $rules['characteristics.*.description']);
        $this->assertStringContainsString('string', $rules['characteristics.*.description']);
        $this->assertStringContainsString('max:255', $rules['characteristics.*.description']);

        $this->assertArrayHasKey('truckManufacturers', $rules);
        $this->assertStringContainsString('nullable', $rules['truckManufacturers']);
        $this->assertStringContainsString('array', $rules['truckManufacturers']);

        $this->assertArrayHasKey('truckManufacturers.*', $rules);
        $this->assertStringContainsString('integer', $rules['truckManufacturers.*']);
        $this->assertStringContainsString('exists:truck_manufacturers,id', $rules['truckManufacturers.*']);
    }

    #[Test]
    public function validDataPassesValidation()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'part_type_id' => $partType->id,
            'sku' => 'OLD-SKU-001',
            'title' => 'Old Product Title',
            'price' => 100.00,
            'quantity' => 10,
        ]);
        $characteristic = Characteristic::factory()->create(); // Assuming a Characteristic factory exists

        $file = UploadedFile::fake()->image('updated_product.jpg');

        $data = [
            'product' => [
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
                'sku' => 'UPDATED-SKU-001',
                'title' => 'Updated Product Title',
                'description' => 'Updated description',
                'price' => 150.75,
                'quantity' => 20,
                'delivery' => '2-4 days',
            ],
            'meta' => [
                'meta_h1' => 'Updated Meta H1',
                'meta_title' => 'Updated Meta Title',
                'meta_description' => 'Updated Meta Description',
            ],
            'characteristics' => [
                ['id' => $characteristic->id, 'title' => 'Updated Color', 'description' => 'Blue'],
                ['title' => 'New Char', 'description' => 'New Value'], // New characteristic
            ],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('product', ['image' => $file]);
        $request->setRouteResolver(function () use ($product) {
            $route = new Route('PUT', 'admin/products/{product}', []);
            $route->parameters = ['product' => $product];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();

        $existingProduct = Product::factory()->create([
            'brand_id' => $brand->id,
            'part_type_id' => $partType->id,
            'sku' => 'EXISTING-SKU',
            'title' => 'Existing Product',
            'price' => 10.00,
            'quantity' => 1,
        ]);

        $productToUpdate = Product::factory()->create([
            'brand_id' => $brand->id,
            'part_type_id' => $partType->id,
            'sku' => 'PRODUCT-TO-UPDATE-SKU',
            'title' => 'Product To Update',
            'price' => 20.00,
            'quantity' => 2,
        ]);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $data = [
            'product' => [
                'brand_id' => 99999, // Non-existent brand
                'part_type_id' => 99999, // Non-existent part type
                'sku' => 'EXISTING-SKU', // Not unique (taken by existingProduct)
                'title' => '', // Required but empty
                'description' => 123, // Invalid type
                'price' => 'abc', // Invalid type
                'quantity' => -10, // Invalid value
                'delivery' => str_repeat('a', 256), // Too long
                'image' => $invalidFile, // Invalid mime type
            ],
            'meta' => [
                'meta_h1' => str_repeat('b', 256), // Too long
                'meta_title' => str_repeat('c', 256), // Too long
                'meta_description' => str_repeat('d', 501), // Too long
            ],
            'characteristics' => [
                ['id' => 99999, 'title' => 'Invalid Char ID'], // Non-existent characteristic ID
                ['title' => str_repeat('e', 256)], // Title too long
                ['description' => str_repeat('f', 256)], // Description too long
            ],
            'truckManufacturers' => [99999], // Non-existent TruckManufacturer ID
        ];

        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('product', ['image' => $data['product']['image']]);
        $request->setRouteResolver(function () use ($productToUpdate) {
            $route = new Route('PUT', 'admin/products/{product}', []);
            $route->parameters = ['product' => $productToUpdate];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('product.brand_id', $errors);
        $this->assertArrayHasKey('product.part_type_id', $errors);
        $this->assertArrayHasKey('product.sku', $errors);
        $this->assertArrayHasKey('product.title', $errors);
        $this->assertArrayHasKey('product.description', $errors);
        $this->assertArrayHasKey('product.price', $errors);
        $this->assertArrayHasKey('product.quantity', $errors);
        $this->assertArrayHasKey('product.delivery', $errors);
        $this->assertArrayHasKey('product.image', $errors);
        $this->assertArrayHasKey('meta.meta_h1', $errors);
        $this->assertArrayHasKey('meta.meta_title', $errors);
        $this->assertArrayHasKey('meta.meta_description', $errors);
        $this->assertArrayHasKey('characteristics.0.id', $errors);
        $this->assertArrayHasKey('characteristics.1.title', $errors);
        $this->assertArrayHasKey('characteristics.2.description', $errors);
        $this->assertArrayHasKey('truckManufacturers.0', $errors);
    }

    #[Test]
    public function passedValidationProcessesImageAndMergesDataWithNewImage()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();
        $oldFile = UploadedFile::fake()->image('old_product.jpg')->size(100);
        $oldPath = 'images/' . $oldFile->hashName();
        Storage::disk('public')->put($oldPath, 'dummy content');

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'part_type_id' => $partType->id,
            'sku' => 'INITIAL-SKU',
            'title' => 'Initial Product',
            'price' => 10.00,
            'quantity' => 1,
            'image_path' => $oldPath,
        ]);
        $characteristic = Characteristic::factory()->create();

        $newFile = UploadedFile::fake()->image('new_product.png')->size(200);

        $data = [
            'product' => [
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
                'sku' => 'NEW-SKU-FOR-UPDATE',
                'title' => 'Updated Product',
                'price' => 20.00,
                'quantity' => 2,
            ],
            'meta' => [
                'meta_title' => 'Updated Meta Title',
            ],
            'characteristics' => [
                ['id' => $characteristic->id, 'title' => 'Updated Char'],
            ],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('product', ['image' => $newFile]);
        $request->setRouteResolver(function () use ($product) {
            $route = new Route('PUT', 'admin/products/{product}', []);
            $route->parameters = ['product' => $product];
            return $route;
        });

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());
        $request->setValidator($validator);
        $request->passedValidation();

        Storage::disk('public')->assertExists($request->input('product.image_path'));
        $this->assertStringContainsString('images/', $request->input('product.image_path'));
        $this->assertStringContainsString($newFile->hashName(), $request->input('product.image_path'));
        Storage::disk('public')->assertMissing($oldPath); // Проверяем, что старый файл удален

        $this->assertEquals('Updated Meta Title', $request->input('meta.meta_title'));
        $this->assertEquals([['id' => $characteristic->id, 'title' => 'Updated Char']], $request->input('characteristics'));
        $this->assertEquals([$truckManufacturer->id], $request->input('truckManufacturers'));
    }

    #[Test]
    public function passedValidationHandlesNullImageAndKeepsOldImageIfNoNewImageUploaded()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();
        $oldFile = UploadedFile::fake()->image('old_product_to_keep.jpg')->size(100);
        $oldPath = 'images/' . $oldFile->hashName();
        Storage::disk('public')->put($oldPath, 'dummy content');

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'part_type_id' => $partType->id,
            'sku' => 'SKU-TO-KEEP-IMAGE',
            'title' => 'Product With Old Image',
            'price' => 30.00,
            'quantity' => 3,
            'image_path' => $oldPath,
        ]);
        $characteristic = Characteristic::factory()->create();

        $data = [
            'product' => [
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
                'sku' => 'SKU-TO-KEEP-IMAGE', // SKU не меняется
                'title' => 'Product With Old Image Updated',
                'price' => 35.00,
                'quantity' => 4,
            ],
            'meta' => [],
            'characteristics' => [
                ['id' => $characteristic->id, 'title' => 'Another Char'],
            ],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        // Не устанавливаем новый файл изображения
        $request->setRouteResolver(function () use ($product) {
            $route = new Route('PUT', 'admin/products/{product}', []);
            $route->parameters = ['product' => $product];
            return $route;
        });

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());
        $request->setValidator($validator);
        $request->passedValidation();

        Storage::disk('public')->assertExists($oldPath); // Проверяем, что старый файл НЕ удален
        $this->assertEquals($oldPath, $request->input('product.image_path')); // Проверяем, что путь остался прежним
    }

    #[Test]
    public function passedValidationHandlesNullImageAndOldImageIfNoNewImageUploadedAndNoOldImage()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'part_type_id' => $partType->id,
            'sku' => 'SKU-NO-IMAGE',
            'title' => 'Product Without Any Image',
            'price' => 40.00,
            'quantity' => 5,
            'image_path' => null, // Изначально нет изображения
        ]);
        $characteristic = Characteristic::factory()->create();

        $data = [
            'product' => [
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
                'sku' => 'SKU-NO-IMAGE-UPDATED',
                'title' => 'Product Without Any Image Updated',
                'price' => 45.00,
                'quantity' => 6,
            ],
            'meta' => [],
            'characteristics' => [
                ['id' => $characteristic->id, 'title' => 'Yet Another Char'],
            ],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        // Не устанавливаем новый файл изображения
        $request->setRouteResolver(function () use ($product) {
            $route = new Route('PUT', 'admin/products/{product}', []);
            $route->parameters = ['product' => $product];
            return $route;
        });

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());
        $request->setValidator($validator);
        $request->passedValidation();

        $this->assertNull($request->input('product.image_path')); // Проверяем, что image_path остался null
        Storage::disk('public')->assertDirectoryEmpty('images'); // Убедимся, что ничего не сохранено
    }
}
