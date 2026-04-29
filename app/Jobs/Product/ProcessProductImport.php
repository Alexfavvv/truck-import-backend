<?php

namespace App\Jobs\Product;

use App\Imports\ProductsImport;
use App\Services\ImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;


class ProcessProductImport implements ShouldQueue
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;

    private const STATUS_TTL_HOURS = 12;

    private string $filePath;
    private int $userId;
    public int $timeout = 3600;
    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobId = $this->job->getJobId();
        // Ключ для кэша Redis
        $cacheKey = "import_status:user_{$this->userId}";

        Log::info("Начат Job импорта продуктов: {$this->filePath} для пользователя ID: {$this->userId}, Job ID: {$jobId}");

        $status = 'processing';
        $message = 'Импорт файла начат.';
        $failures = [];

        try {
            ImportService::updateImportJob($this->filePath, [
                'status' => 'processing',
                'job_id' => $jobId,
                'started_at' => now()->toDateTimeString(),
            ]);

            Cache::put($cacheKey, [
                'status' => $status,
                'message' => $message,
                'failures' => [],
                'jobId' => $jobId,
                'timestamp' => now()->timestamp,
                'jobs' => ImportService::getImportJobsForUser($this->userId),
            ], now()->addHours(self::STATUS_TTL_HOURS));

            $fullPath = Storage::path($this->filePath);

            $productsImport = new ProductsImport();
            Excel::import($productsImport, $fullPath);

            $failures = $productsImport->getFailures();
            if (empty($failures)) {
                $status = 'success';
                $message = 'Импорт завершен успешно!';
            } else {
                $status = 'warning';
                $message = 'Импорт завершен с предупреждениями.';
            }

            Log::info("Завершен Job импорта продуктов: {$this->filePath} для пользователя ID: {$this->userId}, Job ID: {$jobId}. Статус: {$status}");

        } catch (\Exception $e) {
            $status = 'error';
            $message = 'Произошла критическая ошибка при импорте: ' . $e->getMessage();
            Log::error($message, [
                'file_path' => $this->filePath,
                'job_id' => $jobId,
                'user_id' => $this->userId,
                'exception' => $e->getTraceAsString(),
            ]);
            $failures[] = [
                'row' => 0,
                'attribute' => 'system',
                'errors' => [$e->getMessage()],
                'values' => [],
            ];
        } finally {
            ImportService::updateImportJob($this->filePath, [
                'status' => $status,
                'job_id' => $jobId,
                'finished_at' => now()->toDateTimeString(),
                'failures_count' => count($failures),
            ]);

            // Сохраняем финальный статус в Redis
            Cache::put($cacheKey, [
                'status' => $status,
                'message' => $message,
                'failures' => $failures,
                'jobId' => $jobId,
                'timestamp' => now()->timestamp,
                'jobs' => ImportService::getImportJobsForUser($this->userId),
            ], now()->addHours(self::STATUS_TTL_HOURS));

            Storage::delete($this->filePath);
        }
    }

    public function failed(Throwable $exception): void
    {
        $cacheKey = "import_status:user_{$this->userId}";
        $jobId = $this->job?->getJobId();

        ImportService::updateImportJob($this->filePath, [
            'status' => 'error',
            'job_id' => $jobId,
            'finished_at' => now()->toDateTimeString(),
            'failures_count' => 1,
        ]);

        Cache::put($cacheKey, [
            'status' => 'error',
            'message' => 'Импорт прерван: задача очереди завершилась с ошибкой или по таймауту.',
            'failures' => [[
                'row' => 0,
                'attribute' => 'system',
                'errors' => [$exception->getMessage()],
                'values' => [],
            ]],
            'jobId' => $jobId,
            'timestamp' => now()->timestamp,
            'jobs' => ImportService::getImportJobsForUser($this->userId),
        ], now()->addHours(self::STATUS_TTL_HOURS));
    }
}
