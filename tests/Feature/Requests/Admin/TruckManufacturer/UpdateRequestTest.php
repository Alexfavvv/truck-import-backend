<?php

namespace Tests\Feature\Requests\Admin\TruckManufacturer;

use App\Http\Requests\Admin\TruckManufacturer\UpdateRequest;
use App\Models\TruckManufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Routing\Route; // Импортируем Route

class UpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function rulesAreCorrect()
    {
        $truckManufacturer = TruckManufacturer::factory()->create(); // Нужен существующий Manufacturer для unique rule exclusion

        // Создаем экземпляр запроса
        $request = new UpdateRequest();
        $request->setContainer($this->app); // Устанавливаем контейнер для разрешения сервисов

        // Устанавливаем resolver для маршрута, чтобы $this->route('truck_manufacturer') работал
        $request->setRouteResolver(function () use ($truckManufacturer) {
            $route = new Route('PUT', 'admin/truck-manufacturers/{truck_manufacturer}', []);
            $route->parameters = ['truck_manufacturer' => $truckManufacturer]; // Устанавливаем параметры напрямую
            return $route;
        });

        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertStringContainsString('required', $rules['title']);
        $this->assertStringContainsString('string', $rules['title']);
        $this->assertStringContainsString('min:3', $rules['title']);
        $this->assertStringContainsString('max:255', $rules['title']);
        $this->assertStringContainsString('unique:truck_manufacturers,title,' . $truckManufacturer->id, $rules['title']);

        $this->assertArrayHasKey('slug', $rules);
        $this->assertStringContainsString('required', $rules['slug']);
        $this->assertStringContainsString('string', $rules['slug']);
        $this->assertStringContainsString('min:3', $rules['slug']);
        $this->assertStringContainsString('max:255', $rules['slug']);
        $this->assertStringContainsString('unique:truck_manufacturers,slug,' . $truckManufacturer->id, $rules['slug']);
    }

    #[Test]
    public function validDataPassesValidation()
    {
        $truckManufacturer = TruckManufacturer::factory()->create();

        $data = [
            'title' => 'Updated Truck Manufacturer',
            'slug' => 'updated-truck-manufacturer',
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($truckManufacturer) {
            $route = new Route('PUT', 'admin/truck-manufacturers/{truck_manufacturer}', []);
            $route->parameters = ['truck_manufacturer' => $truckManufacturer];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        $existingManufacturer = TruckManufacturer::factory()->create(['title' => 'Existing Manuf', 'slug' => 'existing-manuf']);
        $manufacturerToUpdate = TruckManufacturer::factory()->create(['title' => 'Manuf To Update', 'slug' => 'manuf-to-update']);

        $data = [
            'title' => 'Existing Manuf', // Not unique
            'slug' => 'existing-manuf',   // Not unique
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($manufacturerToUpdate) {
            $route = new Route('PUT', 'admin/truck-manufacturers/{truck_manufacturer}', []);
            $route->parameters = ['truck_manufacturer' => $manufacturerToUpdate];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('slug', $errors);
    }
}
