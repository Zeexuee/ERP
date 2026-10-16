<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\MaterialSortBatch;
use App\Models\ProductionFinishingSession;
use App\Models\ProductionTembakBatch;
use App\Models\User;
use App\Services\Production\ProductionKanbanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionKanbanTest extends TestCase
{
    use RefreshDatabase;

    protected User $productionUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productionUser = User::factory()->create([
            'role' => UserRole::PRODUCTION,
        ]);
    }

    public function test_kanban_board_renders_every_stage_column_even_when_empty(): void
    {
        $response = $this->actingAs($this->productionUser)->get(route('production.kanban'));

        $response->assertOk();
        $response->assertSee('Papan Proses');
        $response->assertSee('Antrean Sales');
        $response->assertSee('Sortir Kayu');
        $response->assertSee('Potong Ukir');
        $response->assertSee('Pencairan Getah');
        $response->assertSee('Tembak');
        $response->assertSee('Jemur');
        $response->assertSee('Finishing');
        $response->assertSee('Tidak ada proses yang sedang berjalan');
    }

    public function test_kanban_board_places_active_work_in_the_matching_stage_column(): void
    {
        $sourceMaterial = Material::create([
            'code' => 'MAT-KNB-01',
            'name' => 'Kayu Mentah Kanban',
            'category' => 'Bahan Sortir',
            'unit' => 'kg',
            'stock_quantity' => 50.00,
            'unit_cost' => 150000,
        ]);

        $sortBatch = MaterialSortBatch::create([
            'sort_code' => 'SRT-KNB-0001',
            'status' => 'in_progress',
            'source_material_id' => $sourceMaterial->id,
            'initial_weight' => 20.00,
            'sort_date' => now()->subDays(3)->toDateString(),
            'pic_name' => 'Operator Sortir',
        ]);

        $tembakBatch = ProductionTembakBatch::create([
            'tembak_code' => 'TBK-KNB-0001',
            'cook_code' => 'MSK-202609-9001',
            'status' => ProductionTembakBatch::STATUS_IN_PROGRESS,
            'tembak_date' => now()->subDays(2)->toDateString(),
            'pic_name' => 'Operator Tembak',
        ]);

        $dryingBatch = ProductionTembakBatch::create([
            'tembak_code' => 'TBK-KNB-0002',
            'cook_code' => 'MSK-202609-9002',
            'status' => ProductionTembakBatch::STATUS_DRYING,
            'tembak_date' => now()->subDays(8)->toDateString(),
            'report_date' => now()->subDays(6)->toDateString(),
            'pic_name' => 'Operator Tembak',
            'report_pic_name' => 'Mandor Jemur',
            'wet_result_weight' => 18.00,
        ]);

        $completedBatch = ProductionTembakBatch::create([
            'tembak_code' => 'TBK-KNB-0003',
            'status' => ProductionTembakBatch::STATUS_COMPLETED,
            'tembak_date' => now()->subDays(20)->toDateString(),
            'pic_name' => 'Operator Tembak',
        ]);

        $finishingSession = ProductionFinishingSession::create([
            'finishing_code' => 'FIN-KNB-0001',
            'branch_code' => 'MSK-202609-9003',
            'source_material_id' => $sourceMaterial->id,
            'initial_weight' => 12.00,
            'current_weight' => 11.00,
            'status' => ProductionFinishingSession::STATUS_IN_PROGRESS,
            'finishing_date' => now()->subDay()->toDateString(),
            'pic_name' => 'Operator Finishing',
        ]);

        $response = $this->actingAs($this->productionUser)->get(route('production.kanban'));

        $response->assertOk();
        $response->assertSee($sortBatch->sort_code);
        $response->assertSee($tembakBatch->tembak_code);
        $response->assertSee($dryingBatch->tembak_code);
        $response->assertSee($finishingSession->finishing_code);
        // Pekerjaan yang sudah selesai tidak ikut tampil di papan.
        $response->assertDontSee($completedBatch->tembak_code);
        $response->assertDontSee('Tidak ada proses yang sedang berjalan');

        // Kode masak terlihat langsung di kartu.
        $response->assertSee('MSK-202609-9001');
        $response->assertSee('MSK-202609-9002');
        $response->assertSee('Kode Masak');

        // Pekerjaan terlama diringkas dari tanggal mulai tahapnya.
        $response->assertSee('Paling Lama Berjalan');
        $response->assertSee($dryingBatch->tembak_code);
    }

    public function test_kanban_service_groups_cards_into_the_correct_columns(): void
    {
        $material = Material::create([
            'code' => 'MAT-KNB-02',
            'name' => 'Kayu Kanban Service',
            'category' => 'Bahan Sortir',
            'unit' => 'kg',
            'stock_quantity' => 40.00,
            'unit_cost' => 120000,
        ]);

        ProductionTembakBatch::create([
            'tembak_code' => 'TBK-KNB-0010',
            'status' => ProductionTembakBatch::STATUS_DRYING,
            'tembak_date' => now()->subDays(5)->toDateString(),
            'report_date' => now()->subDays(4)->toDateString(),
            'pic_name' => 'Operator Tembak',
            'report_pic_name' => 'Mandor Jemur',
            'wet_result_weight' => 20.00,
        ]);

        MaterialSortBatch::create([
            'sort_code' => 'SRT-KNB-0010',
            'status' => 'in_progress',
            'source_material_id' => $material->id,
            'initial_weight' => 15.00,
            'sort_date' => now()->subDay()->toDateString(),
            'pic_name' => 'Operator Sortir',
        ]);

        $columns = app(ProductionKanbanService::class)->buildColumns();
        $byKey = collect($columns)->keyBy('key');

        $this->assertSame(
            ['sales-queue', 'sortir', 'potong-ukir', 'pencairan', 'tembak', 'jemur', 'finishing'],
            collect($columns)->pluck('key')->all()
        );
        $this->assertCount(1, $byKey['sortir']['cards']);
        $this->assertCount(1, $byKey['jemur']['cards']);
        $this->assertCount(0, $byKey['tembak']['cards']);
        $this->assertCount(0, $byKey['finishing']['cards']);
        $this->assertSame('SRT-KNB-0010', $byKey['sortir']['cards'][0]['code']);
        $this->assertSame('TBK-KNB-0010', $byKey['jemur']['cards'][0]['code']);
        $this->assertSame(4, $byKey['jemur']['cards'][0]['age_days']);

        $summary = app(ProductionKanbanService::class)->summarize($columns);
        $this->assertSame(2, $summary['total_active']);
        $this->assertSame(2, $summary['active_stage_count']);
        $this->assertSame(7, $summary['stage_count']);
        $this->assertSame('TBK-KNB-0010', $summary['longest_running']['code']);
    }

    public function test_non_production_role_cannot_open_the_kanban_board(): void
    {
        $salesUser = User::factory()->create(['role' => UserRole::SALES]);

        $this->actingAs($salesUser)
            ->get(route('production.kanban'))
            ->assertForbidden();
    }
}
