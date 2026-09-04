<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuickSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_switch_authenticates_target_user_without_logging_out_manually(): void
    {
        $salesUser = User::factory()->create([
            'email' => 'sales1@erp.com',
            'role' => UserRole::SALES,
        ]);

        $prodUser = User::factory()->create([
            'email' => 'pabrik1@erp.com',
            'role' => UserRole::PRODUCTION,
            'password' => Hash::make('secret123'),
        ]);

        // User currently logged in as Sales
        $this->actingAs($salesUser);
        $this->assertAuthenticatedAs($salesUser);

        // Sales attempts quick switch to production by supplying production credentials
        $response = $this->postJson('/quick-switch', [
            'email' => 'pabrik1@erp.com',
            'password' => 'secret123',
            'target_module' => 'production',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('production.dashboard'),
        ]);

        // Sesi sekarang beralih ke prodUser
        $this->assertAuthenticatedAs($prodUser);
    }

    public function test_quick_switch_fails_if_credentials_wrong(): void
    {
        $salesUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);
        $this->actingAs($salesUser);

        User::factory()->create([
            'email' => 'pabrik2@erp.com',
            'role' => UserRole::PRODUCTION,
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/quick-switch', [
            'email' => 'pabrik2@erp.com',
            'password' => 'wrongpassword',
            'target_module' => 'production',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_quick_switch_fails_if_account_does_not_have_role_for_target_module(): void
    {
        $salesUser1 = User::factory()->create([
            'email' => 'sales1@erp.com',
            'role' => UserRole::SALES,
        ]);
        $this->actingAs($salesUser1);

        // Akun lain yang juga hanya sales
        User::factory()->create([
            'email' => 'sales2@erp.com',
            'role' => UserRole::SALES,
            'password' => Hash::make('secret123'),
        ]);

        // Mencoba masuk ke modul produksi menggunakan akun sales lain
        $response = $this->postJson('/quick-switch', [
            'email' => 'sales2@erp.com',
            'password' => 'secret123',
            'target_module' => 'production',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
