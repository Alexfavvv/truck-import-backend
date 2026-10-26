<?php

namespace Tests\Feature\Api\Client;

use App\Enums\UserRole;
use App\Models\Engine;
use App\Models\EngineBlock;
use App\Models\Gearbox;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PostRelatedProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::store('array')->flush();
        $this->createCatalogTables();
    }

    public function test_article_without_a_related_product_returns_null(): void
    {
        $post = $this->publishedPost();

        $this->getJson('/api/v1/client/posts/'.$post->slug)
            ->assertOk()
            ->assertJsonPath('data.related_product', null);
    }

    public function test_article_can_be_linked_to_an_engine(): void
    {
        $product = Engine::query()->create(['title' => 'Двигатель Volvo D9A', 'slug' => 'volvo-d9a', 'manufacturer' => 'Volvo', 'model' => 'D9A', 'sku' => 'D9A-01']);
        $post = $this->publishedPost(['related_product_type' => 'engine', 'related_product_id' => $product->id]);

        $this->getJson('/api/v1/client/posts/'.$post->slug)
            ->assertOk()
            ->assertJsonPath('data.related_product.type', 'engine')
            ->assertJsonPath('data.related_product.title', 'Двигатель Volvo D9A')
            ->assertJsonPath('data.related_product.path', '/engines/volvo-d9a');
    }

    public function test_article_can_be_linked_to_an_engine_block(): void
    {
        $product = EngineBlock::query()->create(['title' => 'Блок MAN D2066', 'slug' => 'man-d2066', 'manufacturer' => 'MAN', 'model' => 'D2066', 'oem_code' => '51.01100']);
        $post = $this->publishedPost(['related_product_type' => 'engine_block', 'related_product_id' => $product->id]);

        $this->getJson('/api/v1/client/posts/'.$post->slug)
            ->assertOk()
            ->assertJsonPath('data.related_product.type', 'engine_block')
            ->assertJsonPath('data.related_product.sku', '51.01100')
            ->assertJsonPath('data.related_product.path', null);
    }

    public function test_article_can_be_linked_to_a_gearbox(): void
    {
        $product = Gearbox::query()->create(['title' => 'КПП ZF', 'slug' => 'zf-gearbox', 'manufacturer' => 'ZF', 'model' => '16S']);
        $post = $this->publishedPost(['related_product_type' => 'gearbox', 'related_product_id' => $product->id]);

        $this->getJson('/api/v1/client/posts/'.$post->slug)
            ->assertOk()
            ->assertJsonPath('data.related_product.type', 'gearbox')
            ->assertJsonPath('data.related_product.path', null);
    }

    public function test_missing_related_product_does_not_break_article_response(): void
    {
        $post = $this->publishedPost(['related_product_type' => 'engine', 'related_product_id' => 999999]);

        $this->getJson('/api/v1/client/posts/'.$post->slug)
            ->assertOk()
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonPath('data.related_product', null);
    }

    public function test_admin_can_save_a_manually_selected_product(): void
    {
        $product = Engine::query()->create(['title' => 'Двигатель Volvo D9A', 'slug' => 'volvo-d9a']);

        $this->actingAs($this->admin())->post(route('admin.posts.store'), [
            'title' => 'Статья с двигателем',
            'slug' => 'article-with-engine',
            'content' => '<p>Текст статьи</p>',
            'related_product_type' => 'engine',
            'related_product_id' => $product->id,
        ])->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('posts', [
            'slug' => 'article-with-engine',
            'related_product_type' => 'engine',
            'related_product_id' => $product->id,
        ]);
    }

    public function test_admin_can_remove_a_product_link(): void
    {
        $product = Engine::query()->create(['title' => 'Двигатель Volvo D9A', 'slug' => 'volvo-d9a']);
        $post = $this->publishedPost(['related_product_type' => 'engine', 'related_product_id' => $product->id]);

        $this->actingAs($this->admin())->put(route('admin.posts.update', $post), [
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => '<p>Текст статьи</p>',
            'is_published' => 1,
            'related_product_type' => '',
            'related_product_id' => '',
        ])->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'related_product_type' => null,
            'related_product_id' => null,
        ]);
    }

    public function test_admin_picker_searches_oem_without_loading_the_whole_catalog(): void
    {
        EngineBlock::query()->create([
            'title' => 'Блок MAN D2066',
            'slug' => 'man-d2066',
            'oem_code' => '51.01100',
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('admin.posts.related-products', ['q' => '51.01100']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'engine_block')
            ->assertJsonPath('data.0.sku', '51.01100');
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::AdminTruck,
            'is_admin' => true,
            'is_active' => true,
            'login' => fake()->unique()->userName(),
        ]);
    }

    private function publishedPost(array $attributes = []): Post
    {
        return Post::query()->create(array_merge([
            'title' => 'Тестовая статья',
            'slug' => 'related-product-'.fake()->unique()->numberBetween(1000, 999999),
            'is_published' => true,
            'published_at' => now(),
            'views_count' => 0,
        ], $attributes));
    }

    private function createCatalogTables(): void
    {
        foreach ([
            'engines' => ['type', 'sku', 'code_variants'],
            'engine_blocks' => ['type', 'sku', 'oem_code', 'oem_code_compact', 'code_variants'],
            'gearboxes' => ['type', 'sku', 'code_variants'],
        ] as $tableName => $extraColumns) {
            Schema::create($tableName, function (Blueprint $table) use ($extraColumns): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('manufacturer')->nullable();
                $table->string('model')->nullable();
                foreach ($extraColumns as $column) {
                    $table->text($column)->nullable();
                }
                $table->decimal('price', 12, 2)->nullable();
                $table->string('price_text')->nullable();
                $table->string('currency')->nullable();
                $table->string('image')->nullable();
                $table->timestamps();
            });
        }
    }
}
