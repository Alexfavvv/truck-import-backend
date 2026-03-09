<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomAuthenticateMiddlewareTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Гость пробует зайти в админку
    public function testGuestGetsRedirectedToLogin()
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    // Админ пробует зайти в админку
    public function testAuthenticatedAdminCanAccessAdminPanel()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }


}
