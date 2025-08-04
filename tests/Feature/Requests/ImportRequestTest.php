<?php

namespace Tests\Feature\Requests; // Обратите внимание: не Admin namespace

use App\Http\Requests\ImportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local'); // Имитируем файловую систему для тестов с файлами
    }

    #[test]
    public function rulesAreCorrect()
    {
        $rules = (new ImportRequest())->rules();

        $this->assertArrayHasKey('file', $rules);
        $this->assertStringContainsString('required', $rules['file']);
        $this->assertStringContainsString('mimes:xlsx,xls,csv', $rules['file']);
        $this->assertStringContainsString('max:10240', $rules['file']);
    }

    #[test]
    public function validFilePassesValidation()
    {
        $file = UploadedFile::fake()->create('products.xlsx', 5000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $data = ['file' => $file];

        $request = ImportRequest::create('/import', 'POST', $data, [], ['file' => $file]);
        $validator = \Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[test]
    public function invalidFileFailsValidation()
    {
        // Неправильный тип файла
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        // Слишком большой файл
        $largeFile = UploadedFile::fake()->create('large.xlsx', 15000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $data1 = ['file' => $invalidFile];
        $request1 = ImportRequest::create('/import', 'POST', $data1, [], ['file' => $invalidFile]);
        $validator1 = \Validator::make($request1->all(), $request1->rules());
        $this->assertFalse($validator1->passes());
        $this->assertArrayHasKey('file', $validator1->errors()->toArray());

        $data2 = ['file' => $largeFile];
        $request2 = ImportRequest::create('/import', 'POST', $data2, [], ['file' => $largeFile]);
        $validator2 = \Validator::make($request2->all(), $request2->rules());
        $this->assertFalse($validator2->passes());
        $this->assertArrayHasKey('file', $validator2->errors()->toArray());

        // Отсутствует файл
        $data3 = ['file' => null];
        $request3 = ImportRequest::create('/import', 'POST', $data3);
        $validator3 = \Validator::make($request3->all(), $request3->rules());
        $this->assertFalse($validator3->passes());
        $this->assertArrayHasKey('file', $validator3->errors()->toArray());
    }
}
