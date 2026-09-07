<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::published()->orderBy('published_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 9), 30);
        $posts = $query->paginate($perPage);

        $formattedPosts = collect($posts->items())->map(function ($post) {
            return [
                'id'               => $post->id,
                'title'            => $post->title,
                'slug'             => $post->slug,
                'excerpt'          => $post->excerpt,
                'content'          => $post->content,
                'cover_image'      => $post->image_url ?: $post->cover_image,
                'published_at'     => $post->published_at ? $post->published_at->toIso8601String() : null,
                'views_count'      => $post->views_count,
                'meta_title'       => $post->meta_title ?: $post->title,
                'meta_description' => $post->meta_description ?: $post->excerpt,
                'meta_keywords'    => $post->meta_keywords,
                'author'           => $post->user ? $post->user->name : 'Администрация',
            ];
        });

        return response()->json([
            'data' => $formattedPosts,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'per_page'     => $posts->perPage(),
                'total'        => $posts->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment views count
        $post->increment('views_count');

        // Recent posts for sidebar / recommendations
        $recentPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at', 'views_count']);

        return response()->json([
            'data' => [
                'id'               => $post->id,
                'title'            => $post->title,
                'slug'             => $post->slug,
                'excerpt'          => $post->excerpt,
                'content'          => $post->content,
                'cover_image'      => $post->image_url,
                'published_at'     => $post->published_at ? $post->published_at->toIso8601String() : null,
                'views_count'      => $post->views_count,
                'meta_title'       => $post->meta_title ?: $post->title,
                'meta_description' => $post->meta_description ?: $post->excerpt,
                'meta_keywords'    => $post->meta_keywords,
                'author'           => $post->user ? $post->user->name : 'Администрация',
            ],
            'recent' => $recentPosts,
        ]);
    }
}
