<?php

namespace Tests\Feature\Requests\Admin\Product;

use App\Http\Requests\Admin\Product\StoreRequest;
use App\Models\Brand;
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckModel;
use App\Models\TruckManufacturer; // Добавлен импорт TruckManufacturer
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
        $this->assertStringContainsString('unique:products,sku', $rules['product.sku']);
        $this->assertStringContainsString('max:255', $rules['product.sku']);

        $this->assertArrayHasKey('product.title', $rules);
        $this->assertStringContainsString('required', $rules['product.title']);
        $this->assertStringContainsString('string', $rules['product.title']);
        $this->assertStringContainsString('max:255', $rules['product.title']);
        // Удалено: $this->assertStringContainsString('unique:products,title', $rules['product.title']); // Согласно вашему уточнению, title не уникален

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
        $file = UploadedFile::fake()->image('product.jpg');

        $data = [
            'product' => [
                'title' => 'New Product',
                'sku' => 'SKU-001',
                'price' => 123.45,
                'quantity' => 100,
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
                'description' => 'Some description',
                'delivery' => '3-5 days',
            ],
            'meta' => [
                'meta_h1' => 'Meta H1',
                'meta_title' => 'Meta Title',
                'meta_description' => 'Meta Description',
            ],
            'characteristics' => [
                ['title' => 'Color', 'description' => 'Red'],
                ['title' => 'Size', 'description' => 'L'],
            ],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        $request = StoreRequest::create('/admin/products', 'POST', $data, [], ['product' => ['image' => $file]]);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        // Используем SKU, а не Article
        Product::factory()->create(['title' => 'Existing Product Title', 'sku' => 'EXISTING-SKU']); // title теперь не уникален, используем другое значение
        Brand::factory()->create();
        PartType::factory()->create();
        TruckManufacturer::factory()->create();

        $data = [
            'product' => [
                'title' => 'Another Product Title', // Это поле не должно вызывать ошибку, если оно не уникально
                'sku' => 'EXISTING-SKU',       // Not unique - это должно вызвать ошибку
                'price' => 'abc',             // Invalid type
                'quantity' => -5,             // Invalid value
                'brand_id' => 99999,          // Non-existent
                'part_type_id' => 99999,      // Non-existent
                'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'), // Invalid mime type
            ],
            'meta' => [
                'meta_h1' => str_repeat('a', 256), // Too long
            ],
            'characteristics' => [
                ['title' => 'Only Title'],
                ['description' => 'Only Description'],
            ],
            'truckManufacturers' => [99999], // Non-existent TruckManufacturer ID
        ];

        $request = StoreRequest::create('/admin/products', 'POST', $data, [], ['product' => ['image' => $data['product']['image']]]);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        // Утверждение для product.title удалено, так как оно не должно вызывать ошибку уникальности
        $this->assertArrayHasKey('product.sku', $errors); // SKU должен быть уникальным
        $this->assertArrayHasKey('product.price', $errors);
        $this->assertArrayHasKey('product.quantity', $errors);
        $this->assertArrayHasKey('product.brand_id', $errors);
        $this->assertArrayHasKey('product.part_type_id', $errors);
        $this->assertArrayHasKey('product.image', $errors);
        $this->assertArrayHasKey('meta.meta_h1', $errors);
        // Утверждения для characteristics.0.description и characteristics.1.title удалены,
        // так как они теперь nullable и не должны вызывать ошибок валидации.
        $this->assertArrayHasKey('truckManufacturers.0', $errors);
    }

    #[Test]
    public function passedValidationProcessesImageAndMergesData()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();
        $file = UploadedFile::fake()->image('test_product.jpg')->size(500);

        $data = [
            'product' => [
                'title' => 'Test Product',
                'sku' => 'TEST-SKU-001',
                'price' => 10.50,
                'quantity' => 50,
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
            ],
            'meta' => [
                'meta_title' => 'Test Meta Title',
            ],
            'characteristics' => [
                ['title' => 'Weight', 'description' => '10kg'],
            ],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем файлы и данные
        $request = new StoreRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->files->set('product', ['image' => $file]);

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());
        $request->setValidator($validator);
        $request->passedValidation();

        // Проверяем, что изображение было сохранено по пути, который был объединен в запрос
        Storage::disk('public')->assertExists($request->input('product.image_path'));
        $this->assertNotNull($request->input('product.image_path'));
        $this->assertStringContainsString('images/', $request->input('product.image_path'));

        // Проверяем, что мета-данные были объединены
        $this->assertEquals('Test Meta Title', $request->input('meta.meta_title'));

        // Проверяем, что характеристики были объединены
        $this->assertEquals([['title' => 'Weight', 'description' => '10kg']], $request->input('characteristics'));

        // Проверяем, что truckManufacturers были объединены
        $this->assertEquals([$truckManufacturer->id], $request->input('truckManufacturers'));
    }

    #[Test]
    public function passedValidationHandlesNullImage()
    {
        $brand = Brand::factory()->create();
        $partType = PartType::factory()->create();
        $truckManufacturer = TruckManufacturer::factory()->create();

        $data = [
            'product' => [
                'title' => 'Product Without Image',
                'sku' => 'NO-IMG-SKU-001',
                'price' => 20.00,
                'quantity' => 200,
                'brand_id' => $brand->id,
                'part_type_id' => $partType->id,
            ],
            'meta' => [],
            'characteristics' => [],
            'truckManufacturers' => [$truckManufacturer->id],
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new StoreRequest();
        $request->setContainer($this->app);
        $request->merge($data);

        // Мануально запускаем валидацию и passedValidation
        $validator = \Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());
        $request->setValidator($validator);
        $request->passedValidation();

        // Проверяем, что image_path равен null
        $this->assertNull($request->input('product.image_path'));
        Storage::disk('public')->assertDirectoryEmpty('images');
    }
}
