<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostRelatedProductService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

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

    public function show(Request $request, string $slug, PostRelatedProductService $relatedProducts): JsonResponse
    {
        $post = Post::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $this->recordUniqueView($request, $post);

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
                'related_product'  => $relatedProducts->payload($post->related_product_type, $post->related_product_id),
            ],
            'recent' => $recentPosts,
        ]);
    }

    private function recordUniqueView(Request $request, Post $post): void
    {
        $userAgent = $request->userAgent() ?? '';

        if (preg_match('/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|telegrambot|yandex|googlebot/i', $userAgent)) {
            return;
        }

        $identity = ($request->ip() ?? '')."\n".$userAgent;
        $key = 'post_view:'.$post->id.':'.hash('sha256', $identity);

        try {
            if (!Cache::has($key)) {
                $post->increment('views_count');
                Cache::put($key, true, now()->addHour());
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
