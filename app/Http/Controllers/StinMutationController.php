<?php

namespace App\Http\Controllers;

use App\Models\WarehouseItem;
use App\Models\WarehouseStockMovement;
use App\Services\ExcelImportExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StinMutationController extends Controller
{
    /**
     * Tampilkan riwayat mutasi stok barang gudang dengan filter pergantian bulan & pencarian.
     */
    public function index(Request $request): View
    {
        // 1. Ambil daftar bulan yang tersedia dari riwayat pergerakan stok
        $driver = DB::connection()->getDriverName();
        $datePeriodExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m', COALESCE(movement_date, created_at))",
            default => "DATE_FORMAT(COALESCE(movement_date, created_at), '%Y-%m')",
        };

        $availableMonths = WarehouseStockMovement::select(
            DB::raw("{$datePeriodExpr} as period"),
            DB::raw('COUNT(*) as total_count')
        )
            ->groupBy('period')
            ->orderByDesc('period')
            ->get()
            ->map(function ($row) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $row->period);
                    $label = $date->translatedFormat('F Y');
                } catch (\Throwable $e) {
                    $label = $row->period;
                }

                return (object) [
                    'period' => $row->period,
                    'label' => $label,
                    'count' => (int) $row->total_count,
                ];
            });

        // 2. Tentukan bulan yang sedang dipilih
        $currentMonth = now()->format('Y-m');
        $selectedMonth = $request->input('month');

        if (! $selectedMonth) {
            // Default ke bulan berjalan jika ada data, atau periode mutasi terakhir yang ada
            $hasCurrent = $availableMonths->contains('period', $currentMonth);
            $selectedMonth = $hasCurrent ? $currentMonth : ($availableMonths->first()->period ?? $currentMonth);
        }

        // 3. Query data mutasi
        $query = WarehouseStockMovement::with(['item', 'branch', 'user'])
            ->latest('movement_date')
            ->latest('id');

        // Filter periode bulan
        if ($selectedMonth !== 'all') {
            try {
                $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
                $startMonth = $monthDate->copy()->startOfMonth();
                $endMonth = $monthDate->copy()->endOfMonth();
                $query->whereBetween(DB::raw('COALESCE(movement_date, created_at)'), [$startMonth, $endMonth]);
                $selectedMonthLabel = $monthDate->translatedFormat('F Y');
            } catch (\Throwable $e) {
                $selectedMonthLabel = $selectedMonth;
            }
        } else {
            $selectedMonthLabel = 'Semua Bulan';
        }

        // Filter tipe mutasi (in, out, adjustment, transfer)
        $selectedType = $request->input('type');
        if ($selectedType && $selectedType !== 'all') {
            $query->where('type', $selectedType);
        }

        // Filter kategori barang
        $selectedCategory = $request->input('category');
        if ($selectedCategory && $selectedCategory !== 'all') {
            $query->whereHas('item', fn ($q) => $q->where('category', $selectedCategory));
        }

        // Pencarian teks (SKU, nama barang, PIC, catatan)
        $search = $request->filled('search') ? $request->string('search')->trim()->value() : null;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        // 4. Daftar kategori untuk opsi dropdown
        $categories = WarehouseItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // 5. Pagination hasil mutasi
        $movements = $query->paginate(20)->withQueryString();

        return view('stin.mutations.index', compact(
            'movements',
            'availableMonths',
            'selectedMonth',
            'selectedMonthLabel',
            'selectedType',
            'selectedCategory',
            'search',
            'categories'
        ));
    }

    /**
     * Endpoint API rincian mutasi tunggal (jika diperlukan untuk AJAX).
     */
    public function show(WarehouseStockMovement $movement): JsonResponse
    {
        $movement->load(['item', 'branch', 'user']);

        return response()->json([
            'id' => $movement->id,
            'type' => $movement->type,
            'type_label' => $movement->getMovementTypeLabel(),
            'quantity' => $movement->quantity,
            'stock_before' => $movement->stock_before,
            'stock_after' => $movement->stock_after,
            'difference' => $movement->movement_difference,
            'item' => [
                'id' => $movement->item?->id,
                'name' => $movement->item?->name ?? 'Item Terhapus',
                'sku' => $movement->item?->sku ?? '-',
                'category' => $movement->item?->category ?? '-',
                'unit' => $movement->item?->unit ?? 'pcs',
                'unit_price' => $movement->item?->unit_price ?? 0,
            ],
            'pic_name' => $movement->pic_name ?: ($movement->user?->name ?? '-'),
            'user_name' => $movement->user?->name ?? '-',
            'notes' => $movement->notes ?: '-',
            'reference_type' => $movement->reference_type,
            'reference_id' => $movement->reference_id,
            'movement_date' => ($movement->movement_date ?? $movement->created_at)?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i:s').' WIB',
            'signature_url' => $movement->signature_path ? asset('storage/'.$movement->signature_path) : null,
        ]);
    }

    /**
     * Ekspor daftar mutasi ke Excel (CSV UTF-8 BOM).
     */
    public function export(Request $request, ExcelImportExportService $excelService): StreamedResponse
    {
        return $excelService->exportWarehouseMutations(
            item: null,
            type: $request->input('type'),
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            month: $request->input('month'),
            search: $request->input('search'),
            category: $request->input('category')
        );
    }
}
