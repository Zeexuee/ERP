<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        auth()->logout();

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('Email');
        $response->assertSee('Password');
        $response->assertSee('Masuk');
        $response->assertDontSee('Akun:');
    }

    public function test_sales_user_can_login_and_redirects_to_sales_dashboard(): void
    {
        auth()->logout();

        $user = User::factory()->create([
            'email' => 'salesman@erp.com',
            'role' => UserRole::SALES,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'salesman@erp.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_production_user_can_login_and_redirects_to_production_dashboard(): void
    {
        auth()->logout();

        $user = User::factory()->create([
            'email' => 'factory@erp.com',
            'role' => UserRole::PRODUCTION,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'factory@erp.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('production.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_super_role_user_login_redirects_to_sales_dashboard_as_first_page(): void
    {
        auth()->logout();

        $user = User::factory()->create([
            'email' => 'superboss@erp.com',
            'role' => UserRole::SUPER_ROLE,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'superboss@erp.com',
            'password' => 'password123',
        ]);

        // Halaman pertama super admin adalah sales (route dashboard)
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_credentials_fail(): void
    {
        auth()->logout();

        User::factory()->create([
            'email' => 'valid@erp.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'valid@erp.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
