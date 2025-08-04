<?php

namespace Tests\Feature\Requests\Admin\PartType;

use App\Http\Requests\Admin\PartType\UpdateRequest;
use App\Models\PartType;
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
        $partType = PartType::factory()->create(); // Нужен существующий PartType для unique rule exclusion

        // Создаем экземпляр запроса
        $request = new UpdateRequest();
        $request->setContainer($this->app); // Устанавливаем контейнер для разрешения сервисов

        // Устанавливаем resolver для маршрута, чтобы $this->route('part_type') работал
        $request->setRouteResolver(function () use ($partType) {
            $route = new Route('PUT', 'admin/part-types/{part_type}', []);
            $route->parameters = ['part_type' => $partType]; // Устанавливаем параметры напрямую
            return $route;
        });

        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertStringContainsString('required', $rules['title']);
        $this->assertStringContainsString('string', $rules['title']);
        $this->assertStringContainsString('min:3', $rules['title']);
        $this->assertStringContainsString('max:255', $rules['title']);
        $this->assertStringContainsString('unique:part_types,title,' . $partType->id, $rules['title']);

        $this->assertArrayHasKey('slug', $rules);
        $this->assertStringContainsString('required', $rules['slug']);
        $this->assertStringContainsString('string', $rules['slug']);
        $this->assertStringContainsString('min:3', $rules['slug']);
        $this->assertStringContainsString('max:255', $rules['slug']);
        $this->assertStringContainsString('unique:part_types,slug,' . $partType->id, $rules['slug']);
    }

    #[Test]
    public function validDataPassesValidation()
    {
        $partType = PartType::factory()->create();

        $data = [
            'title' => 'Updated Part Type',
            'slug' => 'updated-part-type',
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($partType) {
            $route = new Route('PUT', 'admin/part-types/{part_type}', []);
            $route->parameters = ['part_type' => $partType];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        $existingPartType = PartType::factory()->create(['title' => 'Existing Type', 'slug' => 'existing-type']);
        $partTypeToUpdate = PartType::factory()->create(['title' => 'Type To Update', 'slug' => 'type-to-update']);

        $data = [
            'title' => 'Existing Type', // Not unique (taken by existingPartType)
            'slug' => 'existing-type',   // Not unique (taken by existingPartType)
        ];

        // Создаем экземпляр запроса и вручную устанавливаем данные
        $request = new UpdateRequest();
        $request->setContainer($this->app);
        $request->merge($data);
        $request->setRouteResolver(function () use ($partTypeToUpdate) {
            $route = new Route('PUT', 'admin/part-types/{part_type}', []);
            $route->parameters = ['part_type' => $partTypeToUpdate];
            return $route;
        });

        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('slug', $errors);
    }
}
