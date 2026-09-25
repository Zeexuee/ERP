<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\RecountMaterialRequest;
use App\Http\Requests\Production\SortMaterialRequest;
use App\Http\Requests\Production\StoreMaterialReceiptRequest;
use App\Http\Requests\Production\StoreMaterialRequest;
use App\Http\Requests\Production\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\MaterialReceipt;
use App\Services\ExcelImportExportService;
use App\Services\Production\MaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    /**
     * Tampilkan daftar barang gudang (bahan baku) dan ringkasan stok.
     */
    public function index(Request $request): View
    {
        $query = Material::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $materials = $query->with('branches')->orderBy('name')->paginate(15)->withQueryString();

        $categories = Material::getAllCategories();

        $totalMaterials = Material::count();
        $totalStockValue = Material::all()->sum(fn ($m) => (float) $m->stock_quantity * (float) $m->unit_cost);
        $lowStockCount = Material::whereColumn('stock_quantity', '<=', 'minimum_stock')->count();

        $recentLogs = MaterialLog::with('material')
            ->latest('id')
            ->take(60)
            ->get();

        $allMaterials = Material::orderBy('name')->get(['id', 'code', 'name', 'unit', 'stock_quantity']);

        return view('production.materials.index', compact(
            'materials',
            'categories',
            'totalMaterials',
            'totalStockValue',
            'lowStockCount',
            'recentLogs',
            'allMaterials'
        ));
    }

    /**
     * Formulir pencatatan barang masuk (Stock In).
     */
    public function createReceipt(): View
    {
        $materials = Material::orderBy('name')->get();
        $categories = Material::getAllCategories();

        $latestReceipt = MaterialReceipt::latest('id')->first();
        $nextNumber = 'IN-'.date('Y').'-'.str_pad(($latestReceipt ? $latestReceipt->id + 1 : 1), 4, '0', STR_PAD_LEFT);

        return view('production.materials.create-receipt', compact('materials', 'categories', 'nextNumber'));
    }

    /**
     * Simpan data barang masuk & update stok bahan gudang secara otomatis.
     */
    public function storeReceipt(StoreMaterialReceiptRequest $request, MaterialService $materialService): RedirectResponse
    {
        $receipt = $materialService->recordReceipt(
            $request->validated(),
            $request->file('image')
        );

        return redirect()
            ->route('production.materials.index')
            ->with('success', "Barang masuk #{$receipt->receipt_number} ({$receipt->quantity} {$receipt->unit} {$receipt->material->name}) berhasil dicatat dan stok gudang telah diperbarui.");
    }

    /**
     * Tambah jenis barang / bahan baku baru ke katalog gudang.
     */
    public function storeMaterial(StoreMaterialRequest $request, MaterialService $materialService): RedirectResponse|JsonResponse
    {
        $material = $materialService->storeMaterial($request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Bahan baku [{$material->code}] {$material->name} berhasil ditambahkan ke daftar barang gudang.",
                'material' => $material,
            ], 201);
        }

        return back()->with('success', "Bahan baku [{$material->code}] {$material->name} berhasil ditambahkan ke daftar barang gudang.");
    }

    /**
     * Perbarui data bahan baku (Nama, Kategori, Minimal Alert).
     */
    public function updateMaterial(UpdateMaterialRequest $request, Material $material, MaterialService $materialService): RedirectResponse
    {
        $updatedMaterial = $materialService->updateMaterial($material, $request->validated());

        return back()->with('success', "Data bahan baku [{$updatedMaterial->code}] {$updatedMaterial->name} berhasil diperbarui.");
    }

    /**
     * Hitung ulang stok fisik (Opname / Timbang Ulang).
     */
    public function recountMaterial(RecountMaterialRequest $request, Material $material, MaterialService $materialService): RedirectResponse
    {
        $previousStock = $material->stock_quantity;
        $recountedMaterial = $materialService->recountStock(
            $material,
            (float) $request->validated('actual_stock'),
            $request->validated('weighed_by'),
            $request->validated('notes')
        );

        $weighedTime = $recountedMaterial->last_weighed_at ? $recountedMaterial->last_weighed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
        $weighedBy = $recountedMaterial->last_weighed_by ? " oleh {$recountedMaterial->last_weighed_by}" : '';

        return back()->with('success', "Stok [{$recountedMaterial->code}] {$recountedMaterial->name} berhasil ditimbang ulang dari {$previousStock} {$recountedMaterial->unit} menjadi {$recountedMaterial->stock_quantity} {$recountedMaterial->unit} (Timbang Terakhir: {$weighedTime}{$weighedBy}).");
    }

    /**
     * Prosedur split bahan baku (menjadi bahan baku baru atau digabung ke bahan baku existing).
     */
    public function sortMaterial(SortMaterialRequest $request, Material $material, MaterialService $materialService): RedirectResponse
    {
        try {
            $result = $materialService->sortMaterial($material, $request->validated());
            $target = $result['target'];
            $qty = $result['sorted_quantity'];

            return back()->with('success', "Proses split bahan baku [{$material->code}] {$material->name} sebanyak {$qty} {$material->unit} berhasil. Dialokasikan ke [{$target->code}] {$target->name}.");
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['sorted_quantity' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Ekspor data Riwayat Alur Barang Gudang (60 log terakhir) ke berkas Excel CSV.
     */
    public function exportLogs(ExcelImportExportService $excelService): StreamedResponse
    {
        return $excelService->exportMaterialLogs();
    }
}
