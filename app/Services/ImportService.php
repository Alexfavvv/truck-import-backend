<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ImportService
{
    private const IMPORT_JOBS_CACHE_KEY = 'import_jobs:list';
    private const IMPORT_JOBS_TTL_HOURS = 12;
    private const IMPORT_JOBS_MAX = 50;

    /**
     * @throws Exception
     */
    public static function handleFileAndDispatchJob(UploadedFile $file, string $jobClass, ?int $importId = null): void
    {
        $userId = auth()->id();
        $fileName = uniqid('import_') . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('imports', $fileName);
        $cacheKey = "import_status:user_{$userId}";

        Cache::put($cacheKey, [
            'status' => 'processing',
            'message' => 'Файл загружен. Ожидание старта задачи импорта...',
            'failures' => [],
            'timestamp' => now()->timestamp,
            'jobs' => self::getImportJobsForUser($userId),
        ], now()->addHours(self::IMPORT_JOBS_TTL_HOURS));

        if (!class_exists($jobClass)) {
            Log::error("Job-класс для импорта '{$jobClass}' не найден.");
            throw new Exception("Job-класс для импорта '{$jobClass}' не найден.");
        }

        self::pushImportJob([
            'import_key' => $filePath,
            'user_id' => $userId,
            'file_name' => $file->getClientOriginalName(),
            'status' => 'queued',
            'job_id' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        if ($importId === null) {
            $jobClass::dispatch($filePath, $userId);
        } else {
            $jobClass::dispatch($filePath, $userId, $importId);
        }
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
            $statusData['jobs'] = self::getImportJobsForUser($userId);
            return $statusData;
        }

        if (Queue::size() > 0) {
            return [
                'status' => 'queued',
                'http_status' => 200,
                'message' => 'Задача импорта в очереди. Ожидание запуска воркером...',
                'userId' => $userId,
                'jobs' => self::getImportJobsForUser($userId),
            ];
        }

        // Если статус не найден (возможно, Job еще не начался, или уже завершен и удален из кэша,
        return [
            'status' => 'not_found',
            'http_status' => 404,
            'message' => 'Статус импорта не найден или еще не доступен.',
            'userId' => $userId,
            'jobs' => self::getImportJobsForUser($userId),
        ];
    }

    public static function getImportJobsForUser(int $userId): array
    {
        $jobs = Cache::get(self::IMPORT_JOBS_CACHE_KEY, []);

        return array_values(array_filter($jobs, static fn(array $job): bool => (int) ($job['user_id'] ?? 0) === $userId));
    }

    public static function updateImportJob(string $importKey, array $payload): void
    {
        $jobs = Cache::get(self::IMPORT_JOBS_CACHE_KEY, []);
        foreach ($jobs as &$job) {
            if (($job['import_key'] ?? null) === $importKey) {
                $job = array_merge($job, $payload, ['updated_at' => now()->toDateTimeString()]);
                break;
            }
        }
        unset($job);

        Cache::put(self::IMPORT_JOBS_CACHE_KEY, array_slice($jobs, 0, self::IMPORT_JOBS_MAX), now()->addHours(self::IMPORT_JOBS_TTL_HOURS));
    }

    private static function pushImportJob(array $job): void
    {
        $jobs = Cache::get(self::IMPORT_JOBS_CACHE_KEY, []);
        array_unshift($jobs, $job);
        Cache::put(self::IMPORT_JOBS_CACHE_KEY, array_slice($jobs, 0, self::IMPORT_JOBS_MAX), now()->addHours(self::IMPORT_JOBS_TTL_HOURS));
    }
}
