<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreColoringBatchRequest;
use App\Http\Requests\Production\StoreColoringReportRequest;
use App\Models\Material;
use App\Models\MaterialBranch;
use App\Models\ProductionColoringBatch;
use App\Services\Production\ProductionColoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductionColoringController extends Controller
{
    public function index(): View
    {
        $batches = ProductionColoringBatch::with('branch.material')
            ->latest('coloring_date')
            ->latest('id')
            ->get();

        return view('production.colorings.index', compact('batches'));
    }

    public function create(): View
    {
        $branches = MaterialBranch::with('material')
            ->whereHas('material', function ($query) {
                $query->whereIn('category', [
                    Material::CATEGORY_TEMBAK_RESULT,
                    'Bahan Finishing',
                ]);
            })
            ->where('quantity', '>', 0)
            ->get();

        $supportMaterials = Material::where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('production.colorings.create', compact('branches', 'supportMaterials'));
    }

    public function store(StoreColoringBatchRequest $request, ProductionColoringService $service): RedirectResponse
    {
        $batch = $service->startColoringBatch($request->validated());

        return redirect()->route('production.colorings.index')
            ->with('success', 'Berhasil memulai proses celup & warna untuk lot '.$batch->batch_code);
    }

    public function show(ProductionColoringBatch $coloringBatch): View
    {
        $coloringBatch->load(['branch.material', 'materials.material']);

        $supportMaterials = Material::where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('production.colorings.show', compact('coloringBatch', 'supportMaterials'));
    }

    public function storeReport(StoreColoringReportRequest $request, ProductionColoringBatch $coloringBatch, ProductionColoringService $service): RedirectResponse
    {
        $service->reportColoringBatch($coloringBatch, $request->validated());

        return redirect()->route('production.colorings.index')
            ->with('success', 'Berhasil menyimpan laporan celup/warna untuk lot '.$coloringBatch->batch_code);
    }
}
