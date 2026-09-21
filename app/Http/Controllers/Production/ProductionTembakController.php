<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreTembakBatchRequest;
use App\Http\Requests\Production\StoreTembakReportRequest;
use App\Models\Material;
use App\Models\ProductionTembakBatch;
use App\Services\Production\ProductionTembakService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionTembakController extends Controller
{
    /**
     * Tampilkan riwayat dan daftar proses tembak kayu.
     */
    public function index(Request $request): View
    {
        $query = ProductionTembakBatch::with(['materials.material', 'residualResinMaterial', 'outputMaterial'])
            ->latest('tembak_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tembak_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('report_pic_name', 'like', "%{$search}%")
                    ->orWhereHas('materials.material', fn ($mq) => $mq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('outputMaterial', fn ($oq) => $oq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $tembakBatches = $query->paginate(15)->withQueryString();

        $totalBatches = ProductionTembakBatch::count();
        $totalWoodProcessed = \App\Models\ProductionTembakMaterial::where('type', 'wood')->sum('weight');
        $totalResinUsed = \App\Models\ProductionTembakMaterial::where('type', 'resin')->sum('weight');
        $totalDriedProduced = ProductionTembakBatch::sum('dried_result_weight');

        return view('production.tembaks.index', compact(
            'tembakBatches',
            'totalBatches',
            'totalWoodProcessed',
            'totalResinUsed',
            'totalDriedProduced'
        ));
    }

    /**
     * Formulir inisiasi sesi tembak baru.
     */
    public function create(): View
    {
        $materials = Material::orderBy('name')->get();

        return view('production.tembaks.create', compact('materials'));
    }

    /**
     * Simpan inisiasi sesi tembak kayu baru (potong stok kayu & resin dari gudang).
     */
    public function store(StoreTembakBatchRequest $request, ProductionTembakService $service): RedirectResponse
    {
        try {
            $batch = $service->initiateTembakBatch($request->validated());

            return redirect()
                ->route('production.tembaks.show', $batch)
                ->with('success', "Inisiasi tembak #{$batch->tembak_code} berhasil dibuat dan bahan telah dialokasikan.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['tembak' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses inisiasi tembak: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail sesi tembak & form laporan hasil tembak.
     */
    public function show(ProductionTembakBatch $tembakBatch): View
    {
        $tembakBatch->load(['materials.material', 'residualResinMaterial', 'outputMaterial']);
        $materials = Material::orderBy('name')->get();

        return view('production.tembaks.show', compact('tembakBatch', 'materials'));
    }

    /**
     * Simpan laporan hasil tembak (hasil basah, pengembalian sisa getah, & hasil kering).
     */
    public function storeReport(StoreTembakReportRequest $request, ProductionTembakBatch $tembakBatch, ProductionTembakService $service): RedirectResponse
    {
        try {
            $service->submitTembakReport($tembakBatch, $request->validated());

            return redirect()
                ->route('production.tembaks.show', $tembakBatch)
                ->with('success', "Laporan hasil tembak #{$tembakBatch->tembak_code} berhasil disimpan, sisa getah dan hasil kering telah dicatat ke stok gudang.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['report' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan laporan hasil tembak: '.$e->getMessage());
        }
    }
}
