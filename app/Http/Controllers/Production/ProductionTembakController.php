<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreTembakBatchRequest;
use App\Http\Requests\Production\StoreTembakDryingLogRequest;
use App\Http\Requests\Production\StoreTembakReportRequest;
use App\Models\Material;
use App\Models\ProductionTembakBatch;
use App\Models\ProductionTembakMaterial;
use App\Services\Production\ProductionTembakService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class ProductionTembakController extends Controller
{
    /**
     * Tampilkan riwayat dan daftar proses tembak kayu.
     */
    public function index(Request $request): View
    {
        $query = ProductionTembakBatch::with([
            'materials.material',
            'residualResinMaterial',
            'outputMaterial',
            'latestDryingLog',
        ])
            ->latest('tembak_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($query) use ($search) {
                $query->where('tembak_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('report_pic_name', 'like', "%{$search}%")
                    ->orWhereHas('materials.material', fn ($materialQuery) => $materialQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('outputMaterial', fn ($outputQuery) => $outputQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $tembakBatches = $query->paginate(15)->withQueryString();
        $totalBatches = ProductionTembakBatch::count();
        $totalWoodProcessed = ProductionTembakMaterial::where('type', 'wood')->sum('weight');
        $totalResinUsed = ProductionTembakMaterial::where('type', 'resin')->sum('weight');
        $totalDriedProduced = ProductionTembakBatch::where('status', ProductionTembakBatch::STATUS_COMPLETED)
            ->sum('dried_result_weight');

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
        $materials = Material::whereRaw('LOWER(unit) = ?', ['kg'])
            ->orderBy('name')
            ->get();

        return view('production.tembaks.create', compact('materials'));
    }

    /**
     * Simpan inisiasi sesi tembak dan alokasikan bahan dari gudang.
     */
    public function store(StoreTembakBatchRequest $request, ProductionTembakService $service): RedirectResponse
    {
        try {
            $batch = $service->initiateTembakBatch($request->validated());

            return redirect()
                ->route('production.tembaks.show', $batch)
                ->with('success', "Inisiasi tembak #{$batch->tembak_code} berhasil dibuat dan bahan telah dialokasikan.");
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['tembak' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Gagal memproses inisiasi tembak. Silakan coba kembali.');
        }
    }

    /**
     * Tampilkan detail sesi tembak, laporan hasil, dan riwayat jemur.
     */
    public function show(ProductionTembakBatch $tembakBatch): View
    {
        $tembakBatch->load([
            'materials.material',
            'residualResinMaterial',
            'outputMaterial',
            'dryingLogs',
        ]);
        $residualMaterials = Material::whereRaw('LOWER(category) = ?', ['getah'])
            ->whereRaw('LOWER(unit) = ?', ['kg'])
            ->orderBy('name')
            ->get();
        $outputMaterials = Material::whereRaw('LOWER(unit) = ?', ['kg'])
            ->orderBy('name')
            ->get();

        return view('production.tembaks.show', compact(
            'tembakBatch',
            'residualMaterials',
            'outputMaterials'
        ));
    }

    /**
     * Simpan laporan hasil tembak dan pindahkan batch ke tahap jemur.
     */
    public function storeReport(
        StoreTembakReportRequest $request,
        ProductionTembakBatch $tembakBatch,
        ProductionTembakService $service
    ): RedirectResponse {
        try {
            $service->submitTembakReport($tembakBatch, $request->validated());

            return redirect()
                ->route('production.tembaks.show', $tembakBatch)
                ->with('success', "Laporan hasil tembak #{$tembakBatch->tembak_code} tersimpan. Status otomatis berubah menjadi Jemur.");
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['report' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan laporan hasil tembak. Silakan coba kembali.');
        }
    }

    /**
     * Simpan timbang jemur dan opsional selesaikan hasil ke stok gudang.
     */
    public function storeDryingLog(
        StoreTembakDryingLogRequest $request,
        ProductionTembakBatch $tembakBatch,
        ProductionTembakService $service
    ): RedirectResponse {
        try {
            $dryingLog = $service->recordDryingLog($tembakBatch, $request->validated());
            $message = $dryingLog->is_final
                ? "Proses jemur #{$tembakBatch->tembak_code} selesai dan hasilnya telah masuk stok gudang."
                : "Timbang jemur #{$tembakBatch->tembak_code} berhasil dicatat.";

            return redirect()
                ->route('production.tembaks.show', $tembakBatch)
                ->with('success', $message);
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['drying' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan laporan jemur. Silakan coba kembali.');
        }
    }
}
