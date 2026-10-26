<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostRelatedProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function searchRelatedProducts(Request $request, PostRelatedProductService $relatedProducts): JsonResponse
    {
        $data = $request->validate(['q' => 'required|string|min:2|max:100']);

        return response()->json(['data' => $relatedProducts->search($data['q'])]);
    }

    public function index(Request $request): View
    {
        $query = Post::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'published') {
                $query->where('is_published', true);
            } elseif ($request->input('status') === 'draft') {
                $query->where('is_published', false);
            }
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create', ['relatedProduct' => null]);
    }

    public function store(Request $request, PostRelatedProductService $relatedProducts): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:posts,slug',
            'excerpt'          => 'nullable|string|max:1000',
            'content'          => 'required|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_published'     => 'nullable|boolean',
            'published_at'     => 'nullable|date',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:255',
            'related_product_type' => 'nullable|in:engine,engine_block,gearbox|required_with:related_product_id',
            'related_product_id' => 'nullable|integer|min:1|required_with:related_product_type',
        ]);

        $relatedProduct = $relatedProducts->find(
            $validated['related_product_type'] ?? null,
            isset($validated['related_product_id']) ? (int) $validated['related_product_id'] : null,
        );
        if (($validated['related_product_type'] ?? null) && !$relatedProduct) {
            return back()->withErrors(['related_product_id' => 'Выбранная запчасть не найдена.'])->withInput();
        }

        $slug = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        
        // Ensure slug uniqueness
        $originalSlug = $slug;
        $count = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('posts', 'public');
        }

        $isPublished = $request->has('is_published');

        Post::create([
            'title'            => $validated['title'],
            'slug'             => $slug,
            'excerpt'          => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 200),
            'content'          => $validated['content'],
            'cover_image'      => $coverImagePath,
            'is_published'     => $isPublished,
            'published_at'     => $isPublished ? ($validated['published_at'] ?? now()) : null,
            'meta_title'       => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? ($validated['excerpt'] ?? null),
            'meta_keywords'    => $validated['meta_keywords'] ?? null,
            'user_id'          => auth()->id(),
            'related_product_type' => $relatedProduct ? $validated['related_product_type'] : null,
            'related_product_id' => $relatedProduct?->getKey(),
        ]);

        session()->flash('success', 'Статья успешно создана!');
        return redirect()->route('admin.posts.index');
    }

    public function edit(Post $post, PostRelatedProductService $relatedProducts): View
    {
        $relatedProduct = $relatedProducts->find($post->related_product_type, $post->related_product_id);

        return view('admin.posts.edit', compact('post', 'relatedProduct'));
    }

    public function update(Request $request, Post $post, PostRelatedProductService $relatedProducts): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:posts,slug,' . $post->id,
            'excerpt'          => 'nullable|string|max:1000',
            'content'          => 'required|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_published'     => 'nullable|boolean',
            'published_at'     => 'nullable|date',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:255',
            'remove_image'     => 'nullable|boolean',
            'related_product_type' => 'nullable|in:engine,engine_block,gearbox|required_with:related_product_id',
            'related_product_id' => 'nullable|integer|min:1|required_with:related_product_type',
        ]);

        $relatedProduct = $relatedProducts->find(
            $validated['related_product_type'] ?? null,
            isset($validated['related_product_id']) ? (int) $validated['related_product_id'] : null,
        );
        if (($validated['related_product_type'] ?? null) && !$relatedProduct) {
            return back()->withErrors(['related_product_id' => 'Выбранная запчасть не найдена.'])->withInput();
        }

        $slug = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['title']);

        // Unique slug check excluding self
        $originalSlug = $slug;
        $count = 1;
        while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $coverImagePath = $post->cover_image;

        if ($request->has('remove_image') && $request->boolean('remove_image')) {
            if ($coverImagePath && Storage::disk('public')->exists($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
            }
            $coverImagePath = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($coverImagePath && Storage::disk('public')->exists($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
            }
            $coverImagePath = $request->file('cover_image')->store('posts', 'public');
        }

        $isPublished = $request->has('is_published');
        $publishedAt = $post->published_at;
        if ($isPublished && !$publishedAt) {
            $publishedAt = $validated['published_at'] ?? now();
        } elseif (!$isPublished) {
            $publishedAt = null;
        }

        $post->update([
            'title'            => $validated['title'],
            'slug'             => $slug,
            'excerpt'          => $validated['excerpt'] ?? Str::limit(strip_tags($validated['content']), 200),
            'content'          => $validated['content'],
            'cover_image'      => $coverImagePath,
            'is_published'     => $isPublished,
            'published_at'     => $publishedAt,
            'meta_title'       => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? ($validated['excerpt'] ?? null),
            'meta_keywords'    => $validated['meta_keywords'] ?? null,
            'related_product_type' => $relatedProduct ? $validated['related_product_type'] : null,
            'related_product_id' => $relatedProduct?->getKey(),
        ]);

        session()->flash('success', 'Статья успешно обновлена!');
        return redirect()->route('admin.posts.index');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();
        session()->flash('success', 'Статья успешно удалена!');
        return redirect()->route('admin.posts.index');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        $post->is_published = !$post->is_published;
        if ($post->is_published && !$post->published_at) {
            $post->published_at = now();
        }
        $post->save();

        $statusText = $post->is_published ? 'опубликована' : 'снята с публикации';
        session()->flash('success', "Статья {$statusText}!");
        return redirect()->back();
    }
}
