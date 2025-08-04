<?php

namespace Tests\Feature\Requests\Admin\TruckManufacturer;

use App\Http\Requests\Admin\TruckManufacturer\StoreRequest;
use App\Models\TruckManufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreRequestTest extends TestCase
{
    use RefreshDatabase;

    #[test]
    public function rulesAreCorrect()
    {
        $rules = (new StoreRequest())->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertStringContainsString('required', $rules['title']);
        $this->assertStringContainsString('string', $rules['title']);
        $this->assertStringContainsString('min:3', $rules['title']);
        $this->assertStringContainsString('max:255', $rules['title']);
        $this->assertStringContainsString('unique:truck_manufacturers,title', $rules['title']);

        $this->assertArrayHasKey('slug', $rules);
        $this->assertStringContainsString('required', $rules['slug']);
        $this->assertStringContainsString('string', $rules['slug']);
        $this->assertStringContainsString('min:3', $rules['slug']);
        $this->assertStringContainsString('max:255', $rules['slug']);
        $this->assertStringContainsString('unique:truck_manufacturers,slug', $rules['slug']);
    }

    #[test]
    public function validDataPassesValidation()
    {
        $data = [
            'title' => 'New Truck Manufacturer',
            'slug' => 'new-truck-manufacturer',
        ];

        $request = StoreRequest::create('/admin/truck-manufacturers', 'POST', $data);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[test]
    public function invalidDataFailsValidation()
    {
        TruckManufacturer::factory()->create(['title' => 'Existing Manufacturer', 'slug' => 'existing-manufacturer']);

        $data = [
            'title' => 'Existing Manufacturer', // Not unique
            'slug' => 'existing-manufacturer',   // Not unique
        ];

        $request = StoreRequest::create('/admin/truck-manufacturers', 'POST', $data);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('slug', $errors);
    }
}
