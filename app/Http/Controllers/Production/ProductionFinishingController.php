<?php

namespace App\Http\Controllers\Production;

use App\Enums\FinishingProcessType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\CompleteFinishingSessionRequest;
use App\Http\Requests\Production\StoreFinishingSessionRequest;
use App\Http\Requests\Production\StoreFinishingStepRequest;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionFinishingSession;
use App\Models\ProductionFinishingStep;
use App\Services\Production\ProductionFinishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class ProductionFinishingController extends Controller
{
    /**
     * Tampilkan riwayat dan daftar sesi finishing.
     */
    public function index(Request $request): View
    {
        $query = ProductionFinishingSession::with([
            'sourceMaterial',
            'product',
            'latestStep',
        ])
            ->latest('finishing_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($query) use ($search) {
                $query->where('finishing_code', 'like', "%{$search}%")
                    ->orWhere('branch_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('completion_pic_name', 'like', "%{$search}%")
                    ->orWhereHas('sourceMaterial', fn ($materialQuery) => $materialQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($productQuery) => $productQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        $finishingSessions = $query->paginate(15)->withQueryString();
        $totalSessions = ProductionFinishingSession::count();
        $activeSessions = ProductionFinishingSession::where('status', ProductionFinishingSession::STATUS_IN_PROGRESS)->count();
        $totalSteps = ProductionFinishingStep::count();
        $totalOutputWeight = ProductionFinishingSession::where('status', ProductionFinishingSession::STATUS_COMPLETED)
            ->sum('output_weight');

        return view('production.finishings.index', compact(
            'finishingSessions',
            'totalSessions',
            'activeSessions',
            'totalSteps',
            'totalOutputWeight'
        ));
    }

    /**
     * Formulir inisiasi sesi finishing baru.
     */
    public function create(): View
    {
        $materials = Material::with('branches')
            ->whereRaw('LOWER(unit) = ?', ['kg'])
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        return view('production.finishings.create', compact('materials'));
    }

    /**
     * Simpan inisiasi finishing dan keluarkan bahan dari gudang.
     */
    public function store(StoreFinishingSessionRequest $request, ProductionFinishingService $service): RedirectResponse
    {
        try {
            $session = $service->initiateFinishing($request->validated());

            return redirect()
                ->route('production.finishings.show', $session)
                ->with('success', "Sesi finishing #{$session->finishing_code} dimulai dengan identitas branch {$session->branch_label}.");
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['finishing' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Gagal memulai sesi finishing. Silakan coba kembali.');
        }
    }

    /**
     * Tampilkan detail sesi finishing beserta riwayat prosesnya.
     */
    public function show(ProductionFinishingSession $finishingSession): View
    {
        $finishingSession->load([
            'sourceMaterial',
            'steps.stepMaterials.material',
            'steps.wasteMaterial',
            'product',
            'productBranch',
        ]);
        $supportMaterials = Material::where('stock_quantity', '>', 0)
            ->whereKeyNot($finishingSession->source_material_id)
            ->orderBy('name')
            ->get();
        $wasteMaterials = Material::whereRaw('LOWER(unit) = ?', ['kg'])
            ->orderBy('name')
            ->get();
        $products = Product::orderBy('name')->get();
        $processTypes = FinishingProcessType::cases();

        return view('production.finishings.show', compact(
            'finishingSession',
            'supportMaterials',
            'wasteMaterials',
            'products',
            'processTypes'
        ));
    }

    /**
     * Catat satu proses finishing pada sesi yang sedang berjalan.
     */
    public function storeStep(
        StoreFinishingStepRequest $request,
        ProductionFinishingSession $finishingSession,
        ProductionFinishingService $service
    ): RedirectResponse {
        try {
            $step = $service->recordStep($finishingSession, $request->validated());

            return redirect()
                ->route('production.finishings.show', $finishingSession)
                ->with('success', "Proses {$step->process_type->label()} pada sesi #{$finishingSession->finishing_code} berhasil dicatat.");
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['step' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan proses finishing. Silakan coba kembali.');
        }
    }

    /**
     * Selesaikan finishing dan masukkan hasilnya ke stok barang jadi.
     */
    public function complete(
        CompleteFinishingSessionRequest $request,
        ProductionFinishingSession $finishingSession,
        ProductionFinishingService $service
    ): RedirectResponse {
        try {
            $session = $service->completeFinishing($finishingSession, $request->validated());

            return redirect()
                ->route('production.finishings.show', $session)
                ->with('success', "Finishing #{$session->finishing_code} selesai. {$session->product?->name} branch {$session->branch_label} masuk stok barang jadi.");
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['completion' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyelesaikan sesi finishing. Silakan coba kembali.');
        }
    }
}
