<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportService
{
    /**
     * @throws Exception
     */
    public static function handleFileAndDispatchJob(UploadedFile $file, string $jobClass): void
    {
        $fileName = uniqid('import_') . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('imports', $fileName);

        if (!class_exists($jobClass)) {
            Log::error("Job-класс для импорта '{$jobClass}' не найден.");
            throw new Exception("Job-класс для импорта '{$jobClass}' не найден.");
        }

        $jobClass::dispatch($filePath, auth()->user()->id);
        Log::info("Файл '{$filePath}' успешно сохранен и Job '{$jobClass}' отправлен в очередь.");
    }

    /**
     * Получает текущий статус импорта по User ID.
     */
    public static function getImportStatus(int $userId): array
    {
        // Формируем ключ кэша, используя ID пользователя
        $cacheKey = "import_status:user_{$userId}";

        $statusData = Cache::get($cacheKey);

        if ($statusData) {
            return $statusData;
        }

        // Если статус не найден (возможно, Job еще не начался, или уже завершен и удален из кэша,
        return [
            'status' => 'not_found',
            'http_status' => 404,
            'message' => 'Статус импорта не найден или еще не доступен.',
            'userId' => $userId,
        ];
    }
}
