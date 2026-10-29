<?php

namespace App\Http\Controllers;

use App\Enums\WarehouseLoanStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductionRequest;
use App\Models\SalesOrder;
use App\Models\WarehouseItem;
use App\Models\WarehouseLoan;
use App\Models\WarehouseLoanItem;
use App\Models\WarehouseStockMovement;
use Illuminate\View\View;

class StinController extends Controller
{
    /**
     * Dashboard Utama STIN.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        // 1. Data Ringkasan Utama & Operasional
        $totalItems = WarehouseItem::count();
        $totalStockUnits = (int) WarehouseItem::sum('current_stock');
        $totalInventoryValue = (float) (WarehouseItem::selectRaw('COALESCE(SUM(current_stock * unit_price), 0) as val')->value('val') ?? 0);
        $lowStockCount = WarehouseItem::whereColumn('current_stock', '<=', 'min_stock')->count();

        $stats = [
            'total_sales_orders' => SalesOrder::count(),
            'total_customers' => Customer::count(),
            'total_products' => Product::count(),
            'total_production_requests' => ProductionRequest::count(),
            'total_items' => $totalItems,
            'total_stock_units' => $totalStockUnits,
            'total_inventory_value' => $totalInventoryValue,
            'low_stock_count' => $lowStockCount,
            'mutations_today' => WarehouseStockMovement::whereDate('created_at', today())->count(),
            'mutations_in_today' => (int) WarehouseStockMovement::whereDate('created_at', today())->where('type', 'in')->sum('quantity'),
            'mutations_out_today' => (int) WarehouseStockMovement::whereDate('created_at', today())->where('type', 'out')->sum('quantity'),
            'mutations_this_month' => WarehouseStockMovement::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'active_loans_count' => WarehouseLoan::whereIn('status', [WarehouseLoanStatus::BORROWED, WarehouseLoanStatus::PARTIAL])->count(),
            'loaned_items_count' => (int) WarehouseLoanItem::whereHas('loan', function ($q) {
                $q->whereIn('status', [WarehouseLoanStatus::BORROWED, WarehouseLoanStatus::PARTIAL]);
            })->selectRaw('COALESCE(SUM(quantity_loaned - quantity_returned), 0) as remaining')->value('remaining'),
        ];

        // 2. Riwayat Mutasi Terkini
        $recentMovements = WarehouseStockMovement::with('item')
            ->latest('id')
            ->take(8)
            ->get();

        // 3. Stok Menipis & Pantauan Inventaris
        $criticalStockItems = WarehouseItem::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock', 'asc')
            ->take(5)
            ->get();

        $lowestStockItems = WarehouseItem::where('is_active', true)
            ->orderBy('current_stock', 'asc')
            ->take(5)
            ->get();

        // 4. Distribusi Kategori
        $categoriesDistribution = WarehouseItem::where('is_active', true)
            ->select('category')
            ->selectRaw('COUNT(*) as item_count, COALESCE(SUM(current_stock), 0) as stock_count')
            ->groupBy('category')
            ->orderByDesc('stock_count')
            ->take(5)
            ->get()
            ->map(function ($cat) use ($totalStockUnits) {
                $name = ! empty($cat->category) ? $cat->category : 'Umum / Lainnya';
                $pct = $totalStockUnits > 0 ? round(($cat->stock_count / $totalStockUnits) * 100, 1) : 0;

                return [
                    'name' => $name,
                    'item_count' => (int) $cat->item_count,
                    'stock_count' => (int) $cat->stock_count,
                    'percentage' => $pct,
                ];
            });

        // 5. Tren Mutasi 7 Hari Terakhir
        $weeklyTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            $inQty = (int) WarehouseStockMovement::whereDate('created_at', $dateStr)->where('type', 'in')->sum('quantity');
            $outQty = (int) WarehouseStockMovement::whereDate('created_at', $dateStr)->where('type', 'out')->sum('quantity');

            $weeklyTrend->push([
                'date' => $dateStr,
                'label' => $date->translatedFormat('d M'),
                'day_name' => $date->translatedFormat('D'),
                'in_qty' => $inQty,
                'out_qty' => $outQty,
            ]);
        }

        // 6. Item Stok Terbanyak di Gudang (Valuasi Tertinggi)
        $topStockItems = WarehouseItem::where('is_active', true)
            ->orderByDesc('current_stock')
            ->take(5)
            ->get();

        // 7. Peminjaman Barang Terkini
        $recentLoans = WarehouseLoan::with(['items.item', 'user'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('stin.dashboard', compact(
            'user',
            'stats',
            'recentMovements',
            'criticalStockItems',
            'lowestStockItems',
            'topStockItems',
            'categoriesDistribution',
            'weeklyTrend',
            'recentLoans'
        ));
    }
}
