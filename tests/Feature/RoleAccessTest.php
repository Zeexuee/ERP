<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_role_can_access_sales_dashboard_but_forbidden_from_production(): void
    {
        $salesUser = User::factory()->create([
            'role' => UserRole::SALES,
        ]);

        $this->actingAs($salesUser);

        // Can access sales dashboard
        $this->get('/')->assertStatus(200);

        // Cannot access production dashboard
        $this->get('/production')->assertStatus(403);

        // Cannot access STIN dashboard
        $this->get('/stin')->assertStatus(403);
    }

    public function test_production_role_can_access_production_dashboard_but_forbidden_from_sales(): void
    {
        $prodUser = User::factory()->create([
            'role' => UserRole::PRODUCTION,
        ]);

        $this->actingAs($prodUser);

        // Can access production dashboard
        $this->get('/production')->assertStatus(200);

        // Root route smoothly redirects to production dashboard
        $this->get('/')->assertRedirect(route('production.dashboard'));

        // Cannot access sales modules
        $this->get('/customers')->assertStatus(403);
        $this->get('/sales-orders')->assertStatus(403);
        $this->get('/invoices')->assertStatus(403);
    }

    public function test_super_role_can_access_all_modules(): void
    {
        $superUser = User::factory()->create([
            'role' => UserRole::SUPER_ROLE,
        ]);

        $this->actingAs($superUser);

        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertSee('EK.DIV SALES');
        $res->assertSee('Sales Orders');
        $res->assertDontSee('EK.DIV SUPER ROLE');

        $this->get('/production')->assertStatus(200);
        $this->get('/stin')->assertStatus(200);
        $this->get('/crm')->assertStatus(200);
        $this->get('/ecommerce')->assertStatus(200);
    }
}
