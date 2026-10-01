<?php

namespace App\Jobs\Product;

use App\Console\Commands\ImportReadyPricelists;
use App\Models\ProductImport;
use App\Services\ProductImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessReadyProductImport implements ShouldQueue
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        private string $path,
        private string $filename,
        private array $pricing,
        private int $importId,
        private int $userId,
    ) {}

    public function handle(ImportReadyPricelists $ready, ProductImportService $runs): void
    {
        $run = $runs->start('ready_import', $this->filename, $this->userId, id: $this->importId);
        try {
            $ready->importFile(Storage::disk('local')->path($this->path), $this->filename, $this->pricing, $run);
            $runs->finish($run);
        } catch (Throwable $error) {
            $runs->finish($run, $error);
            throw $error;
        } finally {
            Storage::disk('local')->delete($this->path);
        }
    }

    public function failed(Throwable $error): void
    {
        $run = ProductImport::find($this->importId);
        if ($run && $run->finished_at === null) app(ProductImportService::class)->finish($run, $error);
        Storage::disk('local')->delete($this->path);
    }
}
