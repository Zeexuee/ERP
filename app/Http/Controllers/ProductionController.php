<?php

namespace App\Http\Controllers;

use App\Enums\ProductionRequestStatus;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductionRequest;
use App\Services\Production\ProductionKanbanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProductionController extends Controller
{
    /**
     * Halaman Dashboard Utama Divisi Produksi.
     */
    public function dashboard(): View
    {
        $requests = ProductionRequest::with(['salesOrder.customer', 'salesOrder.items.product'])
            ->latest()
            ->paginate(15);

        $totalPending = ProductionRequest::where('status', ProductionRequestStatus::PENDING)->count();
        $totalInProduction = ProductionRequest::where('status', ProductionRequestStatus::IN_PRODUCTION)->count();
        $totalFinished = ProductionRequest::where('status', ProductionRequestStatus::FINISHED)->count();

        $products = Product::with('branches')->latest()->take(10)->get();
        $branches = ProductBranch::select('branch_code')->distinct()->get();

        $totalMaterials = Material::count();
        $lowStockMaterialsCount = Material::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();
        $warehouseMaterials = Material::orderByRaw('stock_quantity <= minimum_stock DESC')
            ->orderBy('name')
            ->take(8)
            ->get();
        $recentMaterialLogs = MaterialLog::with('material')
            ->latest('id')
            ->take(5)
            ->get();

        return view('production.dashboard', compact(
            'requests',
            'totalPending',
            'totalInProduction',
            'totalFinished',
            'products',
            'branches',
            'totalMaterials',
            'lowStockMaterialsCount',
            'warehouseMaterials',
            'recentMaterialLogs'
        ));
    }

    /**
     * Papan kanban seluruh proses produksi yang sedang berjalan.
     */
    public function kanban(ProductionKanbanService $kanbanService): View
    {
        $columns = $kanbanService->buildColumns();
        $summary = $kanbanService->summarize($columns);

        return view('production.kanban', compact('columns', 'summary'));
    }

    /**
     * Memperbarui status pengerjaan permintaan produksi oleh tim Pabrik.
     */
    public function updateStatus(Request $request, ProductionRequest $productionRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,in_production,finished'],
        ]);

        $newStatus = ProductionRequestStatus::from($validated['status']);
        $productionRequest->status = $newStatus;

        if ($newStatus === ProductionRequestStatus::FINISHED && ! $productionRequest->completed_date) {
            $productionRequest->completed_date = Carbon::now();
        }

        $productionRequest->save();

        return back()->with('success', "Status Permintaan Produksi #{$productionRequest->request_number} berhasil diperbarui menjadi {$newStatus->value}.");
    }
}
