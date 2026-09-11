<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialSortBatch;
use App\Services\Production\MaterialSortService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialSortController extends Controller
{
    /**
     * Tampilkan riwayat proses sortir kayu mandiri.
     */
    public function index(Request $request): View
    {
        $query = MaterialSortBatch::with(['sourceMaterial', 'items.targetMaterial'])
            ->latest('sort_date')
            ->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sort_code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhereHas('sourceMaterial', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        $sortBatches = $query->paginate(15)->withQueryString();

        $totalBatches = MaterialSortBatch::count();
        $totalRawProcessed = MaterialSortBatch::sum('initial_weight');
        $totalDriedProduced = MaterialSortBatch::sum('dried_weight');
        $totalLoss = MaterialSortBatch::sum('drying_loss_weight');

        return view('production.sorts.index', compact(
            'sortBatches',
            'totalBatches',
            'totalRawProcessed',
            'totalDriedProduced',
            'totalLoss'
        ));
    }

    /**
     * Formulir pencatatan proses sortir mandiri baru.
     */
    public function create(): View
    {
        // Ambil semua bahan baku untuk pilihan bahan mentah dan bahan target hasil sortir
        $materials = Material::orderBy('name')->get();

        return view('production.sorts.create', compact('materials'));
    }

    /**
     * Simpan proses sortir, mutasikan stok gudang, dan catat log.
     */
    public function store(Request $request, MaterialSortService $service): RedirectResponse
    {
        $validated = $request->validate([
            'source_material_id' => ['required', 'exists:materials,id'],
            'initial_weight' => ['required', 'numeric', 'min:0.01'],
            'dried_weight' => ['required', 'numeric', 'min:0.01'],
            'sort_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.target_material_id' => ['required', 'exists:materials,id'],
            'items.*.result_weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'source_material_id.required' => 'Pilih bahan baku mentah yang akan dijemur & disortir.',
            'initial_weight.required' => 'Masukkan berat awal bahan baku.',
            'dried_weight.required' => 'Masukkan berat hasil setelah penjemuran.',
            'items.required' => 'Masukkan minimal satu jenis bahan hasil sortir kayu tembak.',
            'items.*.target_material_id.required' => 'Pilih jenis bahan hasil sortir.',
            'items.*.result_weight.required' => 'Masukkan kuantitas hasil sortir (kg).',
        ]);

        try {
            $batch = $service->createSortBatch($validated);

            return redirect()
                ->route('production.sorts.show', $batch)
                ->with('success', "Proses sortir {$batch->sort_code} berhasil disimpan dan stok bahan tembak telah diperbarui.");
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['sort' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses sortir bahan: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail batch sortir dan rincian alokasi hasil sortir.
     */
    public function show(MaterialSortBatch $sortBatch): View
    {
        $sortBatch->load(['sourceMaterial', 'items.targetMaterial']);

        return view('production.sorts.show', compact('sortBatch'));
    }
}
