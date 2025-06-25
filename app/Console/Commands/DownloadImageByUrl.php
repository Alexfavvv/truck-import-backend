<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadImageByUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скачивает изображение по URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = "http://moregroup.pro/wp-content/uploads/2025/02/image-3.png";
        $contents = Http::get($url);
        $name = substr($url, strrpos($url, '/') + 1);
        $fileName = Str::random(40) . '_' . time() . $name;
        Storage::disk('public')->put('/product-images/' . $fileName, $contents);
        $url = Storage::disk('public')->url('/product-images/' . $fileName);
        $this->info($url);
    }
}
