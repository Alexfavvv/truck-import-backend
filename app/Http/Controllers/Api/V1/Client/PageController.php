<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    /**
     * Get a page by its slug (e.g. 'home') with full image URLs for banners.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $settings = $page->settings ?? [];

        if ($slug === 'site_settings') {
            return response()->json([
                'data' => [
                    'id' => $page->id,
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'messengers' => $settings['messengers'] ?? [],
                ]
            ]);
        }

        $banners = $settings['banners'] ?? [];

        $formattedBanners = collect($banners)->map(function ($banner) {
            return [
                'id' => $banner['id'],
                'name' => $banner['name'] ?? '',
                'image_pc' => !empty($banner['image_pc']) ? Storage::disk('public')->url($banner['image_pc']) : null,
                'image_tablet' => !empty($banner['image_tablet']) ? Storage::disk('public')->url($banner['image_tablet']) : null,
                'image_mobile' => !empty($banner['image_mobile']) ? Storage::disk('public')->url($banner['image_mobile']) : null,
                'link_type' => $banner['link_type'] ?? 'link',
                'link_url' => $banner['link_url'] ?? '',
            ];
        });

        return response()->json([
            'data' => [
                'id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'banners' => $formattedBanners,
            ]
        ]);
    }
}
