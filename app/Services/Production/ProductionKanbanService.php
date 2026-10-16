<?php

namespace App\Services\Production;

use App\Enums\ProductionRequestStatus;
use App\Models\MaterialCarveBatch;
use App\Models\MaterialMeltBatch;
use App\Models\MaterialSortBatch;
use App\Models\ProductionFinishingSession;
use App\Models\ProductionRequest;
use App\Models\ProductionTembakBatch;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ProductionKanbanService
{
    /**
     * Susun seluruh pekerjaan yang sedang berjalan menjadi kolom papan kanban
     * berurutan mengikuti alur pabrik.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildColumns(): array
    {
        return [
            $this->salesQueueColumn(),
            $this->sortColumn(),
            $this->carveColumn(),
            $this->meltColumn(),
            $this->tembakColumn(),
            $this->dryingColumn(),
            $this->finishingColumn(),
        ];
    }

    /**
     * Ringkasan singkat untuk kartu statistik di atas papan.
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    public function summarize(array $columns): array
    {
        $cards = collect($columns)->flatMap(fn (array $column): array => $column['cards']);
        $activeColumns = collect($columns)->filter(fn (array $column): bool => $column['cards'] !== []);

        return [
            'total_active' => $cards->count(),
            'active_stage_count' => $activeColumns->count(),
            'stage_count' => count($columns),
            'longest_running' => $cards->sortByDesc('age_days')->first(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function salesQueueColumn(): array
    {
        $requests = ProductionRequest::with('salesOrder.customer')
            ->where('status', ProductionRequestStatus::PENDING)
            ->orderBy('requested_date')
            ->get();

        return $this->column(
            key: 'sales-queue',
            title: 'Antrean Sales',
            subtitle: 'Permintaan menunggu dikerjakan',
            accent: 'slate',
            indexUrl: route('production-requests.index'),
            cards: $requests->map(fn (ProductionRequest $request): array => $this->card(
                code: $request->request_number,
                title: $request->salesOrder?->customer?->name ?? 'Pelanggan tidak tercatat',
                badge: 'Menunggu',
                url: route('production-requests.show', $request),
                startedAt: $request->requested_date,
                picName: null,
                meta: [
                    'Sales Order' => $request->salesOrder?->order_number ?? '-',
                ],
            )),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function sortColumn(): array
    {
        $batches = MaterialSortBatch::with('sourceMaterial')
            ->where('status', '!=', 'completed')
            ->orderBy('sort_date')
            ->get();

        return $this->column(
            key: 'sortir',
            title: 'Sortir Kayu',
            subtitle: 'Bahan mentah dijemur lalu disortir',
            accent: 'amber',
            indexUrl: route('production.sorts.index'),
            cards: $batches->map(fn (MaterialSortBatch $batch): array => $this->card(
                code: $batch->sort_code,
                title: $batch->sourceMaterial?->name ?? 'Bahan tidak tercatat',
                badge: $batch->status_label,
                url: route('production.sorts.show', $batch),
                startedAt: $batch->sort_date,
                picName: $batch->pic_name,
                meta: [
                    'Berat Awal' => $this->formatWeight($batch->initial_weight),
                ],
            )),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function carveColumn(): array
    {
        $batches = MaterialCarveBatch::with('sourceMaterial')
            ->where('status', '!=', 'completed')
            ->orderBy('carve_date')
            ->get();

        return $this->column(
            key: 'potong-ukir',
            title: 'Potong Ukir',
            subtitle: 'Kayu dipotong dan dibentuk',
            accent: 'amber',
            indexUrl: route('production.carves.index'),
            cards: $batches->map(fn (MaterialCarveBatch $batch): array => $this->card(
                code: $batch->carve_code,
                title: $batch->sourceMaterial?->name ?? 'Bahan tidak tercatat',
                badge: $batch->status_label,
                url: route('production.carves.show', $batch),
                startedAt: $batch->carve_date,
                picName: $batch->pic_name,
                meta: [
                    'Berat Awal' => $this->formatWeight($batch->initial_weight),
                ],
            )),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function meltColumn(): array
    {
        $batches = MaterialMeltBatch::with(['sourceMaterial', 'targetMaterial'])
            ->whereIn('status', ['in_progress', 'monitoring'])
            ->orderBy('melt_date')
            ->get();

        return $this->column(
            key: 'pencairan',
            title: 'Pencairan Getah',
            subtitle: 'Getah dicairkan lalu dipantau penguapannya',
            accent: 'sky',
            indexUrl: route('production.melts.index'),
            cards: $batches->map(function (MaterialMeltBatch $batch): array {
                $isMonitoring = $batch->status === 'monitoring';
                $meta = ['Berat Awal' => $this->formatWeight($batch->initial_weight)];

                if ($isMonitoring) {
                    $meta['Cairan Terkini'] = $this->formatWeight($batch->current_stock);
                    $meta['Menguap'] = $this->formatWeight($batch->total_evaporated_weight);
                }

                return $this->card(
                    code: $batch->melt_code,
                    title: $batch->sourceMaterial?->name ?? 'Bahan tidak tercatat',
                    badge: $isMonitoring ? 'Pemantauan Uap' : 'Menunggu Laporan',
                    url: route('production.melts.show', $batch),
                    startedAt: $batch->melt_date,
                    picName: $batch->pic_name,
                    meta: $meta,
                );
            }),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function tembakColumn(): array
    {
        $batches = ProductionTembakBatch::with('materials')
            ->where('status', ProductionTembakBatch::STATUS_IN_PROGRESS)
            ->orderBy('tembak_date')
            ->get();

        return $this->column(
            key: 'tembak',
            title: 'Tembak',
            subtitle: 'Kayu dan getah diinjeksi',
            accent: 'indigo',
            indexUrl: route('production.tembaks.index'),
            cards: $batches->map(fn (ProductionTembakBatch $batch): array => $this->card(
                code: $batch->tembak_code,
                title: 'Menunggu laporan hasil tembak',
                badge: $batch->status_label,
                url: route('production.tembaks.show', $batch),
                startedAt: $batch->tembak_date,
                picName: $batch->pic_name,
                branchCode: $batch->cook_code,
                meta: [
                    'Bahan Kayu' => $this->formatWeight($batch->materials->where('type', 'wood')->sum('weight')),
                    'Getah' => $this->formatWeight($batch->materials->where('type', 'resin')->sum('weight')),
                ],
            )),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dryingColumn(): array
    {
        $batches = ProductionTembakBatch::with('dryingLogs')
            ->where('status', ProductionTembakBatch::STATUS_DRYING)
            ->orderBy('report_date')
            ->get();

        return $this->column(
            key: 'jemur',
            title: 'Jemur',
            subtitle: 'Hasil tembak dijemur sampai kering',
            accent: 'orange',
            indexUrl: route('production.tembaks.index'),
            cards: $batches->map(fn (ProductionTembakBatch $batch): array => $this->card(
                code: $batch->tembak_code,
                title: $batch->dryingLogs->count().' sesi timbang tercatat',
                badge: $batch->status_label,
                url: route('production.tembaks.show', $batch),
                startedAt: $batch->report_date,
                picName: $batch->report_pic_name,
                branchCode: $batch->cook_code,
                meta: [
                    'Berat Awal' => $this->formatWeight($batch->wet_result_weight),
                    'Berat Terkini' => $this->formatWeight($batch->current_drying_weight),
                    'Total Susut' => $this->formatWeight($batch->total_shrinkage_weight),
                ],
            )),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function finishingColumn(): array
    {
        $sessions = ProductionFinishingSession::with(['sourceMaterial', 'latestStep'])
            ->where('status', ProductionFinishingSession::STATUS_IN_PROGRESS)
            ->orderBy('finishing_date')
            ->get();

        return $this->column(
            key: 'finishing',
            title: 'Finishing',
            subtitle: 'Celup, molen, kerok, dan bor',
            accent: 'violet',
            indexUrl: route('production.finishings.index'),
            cards: $sessions->map(fn (ProductionFinishingSession $session): array => $this->card(
                code: $session->finishing_code,
                title: $session->sourceMaterial?->name ?? 'Bahan tidak tercatat',
                badge: $session->latestStep?->process_type->label() ?? 'Belum ada proses',
                url: route('production.finishings.show', $session),
                startedAt: $session->finishing_date,
                picName: $session->pic_name,
                branchCode: $session->branch_label,
                meta: [
                    'Berat Masuk' => $this->formatWeight($session->initial_weight),
                    'Berat Terkini' => $this->formatWeight($session->current_weight),
                    'Total Susut' => $this->formatWeight($session->total_weight_loss),
                ],
            )),
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $cards
     * @return array<string, mixed>
     * @return array<string, mixed>
     */
    private function column(
        string $key,
        string $title,
        string $subtitle,
        string $accent,
        string $indexUrl,
        Collection $cards,
    ): array {
        return [
            'key' => $key,
            'title' => $title,
            'subtitle' => $subtitle,
            'accent' => $accent,
            'index_url' => $indexUrl,
            'cards' => $cards->all(),
        ];
    }

    /**
     * @param  array<string, string>  $meta
     * @return array<string, mixed>
     */
    private function card(
        string $code,
        string $title,
        string $badge,
        string $url,
        mixed $startedAt,
        ?string $picName,
        array $meta = [],
        ?string $branchCode = null,
    ): array {
        $startedDate = $startedAt ? CarbonImmutable::parse($startedAt)->startOfDay() : null;

        return [
            'code' => $code,
            'title' => $title,
            'badge' => $badge,
            'url' => $url,
            'pic_name' => $picName,
            'branch_code' => $branchCode,
            'started_at' => $startedDate,
            'age_days' => $startedDate
                ? (int) $startedDate->diffInDays(CarbonImmutable::now()->startOfDay())
                : 0,
            'meta' => $meta,
        ];
    }

    private function formatWeight(mixed $weight): string
    {
        return number_format((float) $weight, 2, ',', '.').' kg';
    }
}
