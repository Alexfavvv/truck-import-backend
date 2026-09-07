<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a listing of managed pages.
     */
    public function index(): View
    {
        $pages = Page::where('slug', '!=', 'site_settings')->orderBy('name', 'asc')->get();
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page): View
    {
        if ($page->slug === 'site_settings') {
            $settings = $page->settings ?? [];
            if (!isset($settings['messengers'])) {
                $settings['messengers'] = [
                    'max_link' => '',
                    'whatsapp_link' => '',
                    'telegram_link' => '',
                ];
                $page->settings = $settings;
                $page->save();
            }
            return view('admin.pages.site_settings', compact('page'));
        }

        // If settings or banners do not exist, initialize defaults
        $settings = $page->settings ?? [];
        if (!isset($settings['banners'])) {
            $settings['banners'] = [
                [
                    'id' => 1,
                    'name' => 'Левый главный баннер (большой)',
                    'image_pc' => null,
                    'image_tablet' => null,
                    'image_mobile' => null,
                    'link_type' => 'callback',
                    'link_url' => '',
                ],
                [
                    'id' => 2,
                    'name' => 'Правый верхний баннер',
                    'image_pc' => null,
                    'image_tablet' => null,
                    'image_mobile' => null,
                    'link_type' => 'link',
                    'link_url' => '/catalog',
                ],
                [
                    'id' => 3,
                    'name' => 'Правый нижний баннер',
                    'image_pc' => null,
                    'image_tablet' => null,
                    'image_mobile' => null,
                    'link_type' => 'link',
                    'link_url' => '/contacts',
                ]
            ];
            $page->settings = $settings;
            $page->save();
        }

        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the page settings (banners, files, links).
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        if ($page->slug === 'site_settings') {
            $settings = $page->settings ?? [];
            $settings['messengers'] = [
                'max_link' => $request->input('messengers.max_link'),
                'whatsapp_link' => $request->input('messengers.whatsapp_link'),
                'telegram_link' => $request->input('messengers.telegram_link'),
            ];
            $page->settings = $settings;
            $page->save();

            session()->flash('success', 'Настройки сайта успешно сохранены!');
            return redirect()->route('admin.pages.edit', $page);
        }

        $settings = $page->settings ?? ['banners' => []];
        $banners = $settings['banners'] ?? [];
        
        $inputBanners = $request->input('banners', []);

        foreach ($banners as $index => &$banner) {
            $bid = $banner['id'];
            
            if (isset($inputBanners[$bid])) {
                $banner['link_type'] = $inputBanners[$bid]['link_type'] ?? 'link';
                $banner['link_url'] = $inputBanners[$bid]['link_url'] ?? '';
            }

            // Uploads and deletion for each device size
            foreach (['image_pc', 'image_tablet', 'image_mobile'] as $device) {
                // Delete image if requested
                if ($request->has("banners.{$bid}.remove_{$device}") && $request->boolean("banners.{$bid}.remove_{$device}")) {
                    if (!empty($banner[$device]) && Storage::disk('public')->exists($banner[$device])) {
                        Storage::disk('public')->delete($banner[$device]);
                    }
                    $banner[$device] = null;
                }

                // Upload new image if file exists
                if ($request->hasFile("banners.{$bid}.{$device}")) {
                    // Remove old image
                    if (!empty($banner[$device]) && Storage::disk('public')->exists($banner[$device])) {
                        Storage::disk('public')->delete($banner[$device]);
                    }
                    // Save new
                    $path = $request->file("banners.{$bid}.{$device}")->store('banners', 'public');
                    $banner[$device] = $path;
                }
            }
        }

        $settings['banners'] = $banners;
        $page->settings = $settings;
        $page->save();

        session()->flash('success', 'Настройки страницы успешно сохранены!');
        return redirect()->route('admin.pages.edit', $page);
    }
}
