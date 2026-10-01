<?php

namespace Database\Seeders;

use App\Models\Engine;
use Illuminate\Database\Seeder;

class EngineSeeder extends Seeder
{
    public function run(): void
    {
        $image = '/images/product-image.jpg';
        $items = [
            ['man-d2066-engine', 'MAN D2066 Engine', 'MAN', 'D2066', 'engine', 'D2066-ENG', 'D20'],
            ['man-d2676-long-block', 'MAN D2676 Long Block', 'MAN', 'D2676', 'long_block', 'D2676-LB', 'D26'],
            ['man-d2862-marine-engine', 'MAN D2862 Marine Engine', 'MAN Marine', 'D2862', 'engine', 'D2862-MAR', 'D28'],
            ['man-d2842-short-block', 'MAN D2842 Short Block', 'MAN Marine', 'D2842', 'short_block', 'D2842-SB', 'D28'],
            ['volvo-d13-engine', 'Volvo D13 Engine', 'Volvo', 'D13', 'engine', 'D13-ENG', 'D13'],
            ['volvo-d13-long-block', 'Volvo D13 Long Block', 'Volvo', 'D13', 'long_block', 'D13-LB', 'D13'],
            ['volvo-d16-short-block', 'Volvo D16 Short Block', 'Volvo', 'D16', 'short_block', 'D16-SB', 'D16'],
            ['volvo-penta-d8', 'Volvo Penta D8', 'Volvo Penta', 'D8', 'engine', 'D8-PENTA', 'D8'],
            ['volvo-penta-d13', 'Volvo Penta D13', 'Volvo Penta', 'D13', 'engine', 'D13-PENTA', 'D13'],
            ['scania-dc13-engine', 'Scania DC13 Engine', 'Scania', 'DC13', 'engine', 'DC13-ENG', 'DC13'],
            ['scania-di13-marine-engine', 'Scania DI13 Marine Engine', 'Scania Marine', 'DI13', 'engine', 'DI13-MAR', 'DI13'],
            ['scania-di16-marine-engine', 'Scania DI16 Marine Engine', 'Scania Marine', 'DI16', 'engine', 'DI16-MAR', 'DI16'],
            ['scania-di13-long-block', 'Scania DI13 Long Block', 'Scania Marine', 'DI13', 'long_block', 'DI13-LB', 'DI13'],
        ];

        foreach ($items as [$slug, $title, $manufacturer, $model, $type, $sku, $engineFamily]) {
            Engine::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'manufacturer' => $manufacturer,
                'engine_family' => $engineFamily,
                'model' => $model,
                'type' => $type,
                'sku' => $sku,
                'price' => null,
                'currency' => 'RUB',
                'availability' => null,
                'image' => $image,
                'description' => null,
            ]);
        }
    }
}
