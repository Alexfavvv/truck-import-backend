<?php

namespace Tests\Feature\Requests\Admin\PartType;

use App\Http\Requests\Admin\PartType\StoreRequest;
use App\Models\PartType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function rulesAreCorrect()
    {
        $rules = (new StoreRequest())->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertStringContainsString('required', $rules['title']);
        $this->assertStringContainsString('string', $rules['title']);
        $this->assertStringContainsString('min:3', $rules['title']);
        $this->assertStringContainsString('max:255', $rules['title']);
        $this->assertStringContainsString('unique:part_types,title', $rules['title']);

        $this->assertArrayHasKey('slug', $rules);
        $this->assertStringContainsString('required', $rules['slug']);
        $this->assertStringContainsString('string', $rules['slug']);
        $this->assertStringContainsString('min:3', $rules['slug']);
        $this->assertStringContainsString('max:255', $rules['slug']);
        $this->assertStringContainsString('unique:part_types,slug', $rules['slug']);
    }

    #[Test]
    public function validDataPassesValidation()
    {
        $data = [
            'title' => 'New Part Type',
            'slug' => 'new-part-type',
        ];

        $request = StoreRequest::create('/admin/part-types', 'POST', $data);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function invalidDataFailsValidation()
    {
        PartType::factory()->create(['title' => 'Existing Part Type', 'slug' => 'existing-part-type']);

        $data = [
            'title' => 'Existing Part Type', // Not unique
            'slug' => 'existing-part-type',   // Not unique
        ];

        $request = StoreRequest::create('/admin/part-types', 'POST', $data);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('slug', $errors);
    }
}
